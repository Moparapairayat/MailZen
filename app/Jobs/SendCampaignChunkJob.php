<?php

namespace App\Jobs;

use App\Mail\CampaignMailable;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\CampaignLog;
use App\Models\DeliveryServer;
use App\Models\SuppressionList;
use App\Services\AutomationTriggerService;
use App\Services\DeliveryServerService;
use App\Services\CampaignService;
use App\Services\PersonalizationService;
use App\Services\SpamScoringService;
use App\Services\ZeptoMailApiService;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Swift_TransportException;

class SendCampaignChunkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30; // 30 seconds between retries
    public int $timeout = 300; // 5 minutes timeout

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Campaign $campaign,
        public array $recipientIds
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Refresh campaign to get latest status
        $this->campaign->refresh();

        // Check if campaign is still running
        if (!$this->campaign->isRunning()) {
            Log::info("Campaign {$this->campaign->id} is not running (status: {$this->campaign->status}). Skipping chunk.");
            return;
        }

        try {
            app(CampaignService::class)->ensureCanRun($this->campaign);
        } catch (\RuntimeException $e) {
            $this->campaign->update([
                'status' => 'failed',
                'finished_at' => now(),
                'failure_reason' => $e->getMessage(),
            ]);
            return;
        }

        // Get delivery server for campaign (we'll configure mailer before each send)
        $deliveryServer = null;
        $usingFallback = false;

        $this->campaign->loadMissing('customer');
        $customer = $this->campaign->customer;
        $mustAddDelivery = $customer ? (bool) $customer->groupSetting('servers.permissions.must_add_delivery_server', false) : false;
        $canUseSystem = $customer ? (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false) : false;

        $requestedServerId = $this->campaign->delivery_server_id ? (int) $this->campaign->delivery_server_id : null;
        if (!$requestedServerId) {
            Log::warning('Campaign has no delivery server configured, using fallback default', [
                'campaign_id' => $this->campaign->id,
                'campaign_name' => $this->campaign->name,
                'message' => 'Campaign delivery_server_id is null. Please select a delivery server in campaign settings.',
            ]);
            $usingFallback = true;
        }

        if ($customer) {
            $deliveryServer = app(DeliveryServerService::class)->resolveDeliveryServerForCustomer(
                $customer,
                $requestedServerId,
                $mustAddDelivery,
                $canUseSystem
            );
        } else {
            $deliveryServer = DeliveryServer::query()
                ->with('bounceServer')
                ->whereNull('customer_id')
                ->where('status', 'active')
                ->where('use_for', true)
                ->orderBy('id')
                ->first();
        }

        if (!$deliveryServer) {
            if ($mustAddDelivery) {
                $this->campaign->update([
                    'status' => 'failed',
                    'finished_at' => now(),
                    'failure_reason' => 'You must select a delivery server before running a campaign.',
                ]);
                return;
            }

            $this->campaign->update([
                'status' => 'failed',
                'finished_at' => now(),
                'failure_reason' => 'No available delivery server for this campaign.',
            ]);
            return;
        }

        if ($requestedServerId && (int) $deliveryServer->id !== (int) $requestedServerId) {
            Log::warning('Campaign has invalid or unavailable delivery server, falling back to default', [
                'campaign_id' => $this->campaign->id,
                'configured_delivery_server_id' => $requestedServerId,
                'fallback_delivery_server_id' => $deliveryServer->id,
            ]);
            $usingFallback = true;
        }

        if ($usingFallback) {
            Log::info('Using fallback delivery server for campaign', [
                'campaign_id' => $this->campaign->id,
                'fallback_delivery_server_id' => $deliveryServer->id,
                'fallback_delivery_server_name' => $deliveryServer->name,
                'configured_delivery_server_id' => $requestedServerId,
            ]);
        }

        if (
            $deliveryServer
            && $deliveryServer->type === 'amazon-ses'
            && (bool) config('services.clock_skew_check.auto_pause_campaigns', true)
            && $this->isClockSkewTooHigh()
        ) {
            $this->pauseCampaignForClockSkew((int) config('services.clock_skew_check.auto_pause_seconds', 300));
            return;
        }

        // Get recipients
        $recipients = CampaignRecipient::whereIn('id', $this->recipientIds)
            ->where('campaign_id', $this->campaign->id)
            ->where('status', 'pending')
            ->get();

        // Filter out suppressed recipients
        $suppressedEmails = SuppressionList::where('customer_id', $this->campaign->customer_id)
            ->pluck('email')
            ->toArray();

        $recipients = $recipients->reject(function ($recipient) use ($suppressedEmails) {
            return in_array($recipient->email, $suppressedEmails);
        });

        if ($recipients->isEmpty()) {
            Log::info("No pending recipients found for chunk in campaign {$this->campaign->id}");
            return;
        }

        $sentCount = 0;
        $failedCount = 0;
        $consecutiveTransportFailures = 0;
        $maxConsecutiveTransportFailures = 5;

        if ($deliveryServer && $deliveryServer->type === 'zeptomail-api') {
            $this->sendChunkViaZeptoBatch($deliveryServer, $recipients, $sentCount, $failedCount);

            Log::info("Campaign chunk processed for campaign {$this->campaign->id}: {$sentCount} sent, {$failedCount} failed");

            $this->campaign->refresh();
            $this->campaign->checkCompletion();
            return;
        }

        foreach ($recipients as $recipient) {
            // Double-check campaign status before each send
            $this->campaign->refresh();
            if (!$this->campaign->isRunning()) {
                Log::info("Campaign {$this->campaign->id} was paused/failed. Stopping chunk processing.");
                break;
            }

            // Bail early if the mail server appears to be down
            if ($consecutiveTransportFailures >= $maxConsecutiveTransportFailures) {
                Log::error("Campaign {$this->campaign->id}: {$consecutiveTransportFailures} consecutive transport failures. Stopping chunk to avoid further errors.", [
                    'campaign_id' => $this->campaign->id,
                    'consecutive_failures' => $consecutiveTransportFailures,
                ]);
                break;
            }

            try {
                if ($deliveryServer) {
                    $quotaPauseDelaySeconds = $this->consumeDeliveryServerQuotaOrReturnDelaySeconds($deliveryServer, 1);
                    if ($quotaPauseDelaySeconds !== null) {
                        $this->pauseCampaignForDeliveryServerQuota($deliveryServer, $quotaPauseDelaySeconds);
                        return;
                    }
                }

                // Check spam score before sending
                if ($this->shouldBlockDueToSpamScore($recipient, $deliveryServer)) {
                    $failedCount++;
                    continue;
                }

                // Configure mailer before each send to ensure fresh configuration
                $mailerName = $this->configureMailer($deliveryServer);
                
                // Rate limiting: Adjust based on delivery server type to avoid provider limits
                $perMinute = $this->getRateLimitForServer($deliveryServer);
                
                RateLimiter::attempt(
                    'campaign-send:' . $this->campaign->id,
                    $perMinute,
                    function () use ($recipient, &$sentCount, $mailerName, $deliveryServer) {
                        // Log bounce server info before sending
                        $this->campaign->loadMissing('bounceServer');
                        $bounceServer = $this->campaign->bounceServer ?: $deliveryServer?->bounceServer;
                        Log::info('Sending campaign email', [
                            'campaign_id' => $this->campaign->id,
                            'recipient_email' => $recipient->email,
                            'recipient_id' => $recipient->id,
                            'delivery_server_id' => $deliveryServer?->id,
                            'delivery_server_name' => $deliveryServer?->name,
                            'bounce_server_id' => $bounceServer?->id,
                            'bounce_server_username' => $bounceServer?->username,
                        ]);
                        
                        // Explicitly use the configured mailer to ensure correct delivery server is used
                        $mailer = $mailerName ? Mail::mailer($mailerName) : Mail::mailer();
                        $mailer->to($recipient->email)
                            ->send(new CampaignMailable($this->campaign, $recipient));

                        // Mark as accepted (email was accepted by mail server)
                        // Actual delivery status will be updated via webhooks or later checks
                        DB::transaction(function () use ($recipient) {
                            $recipient->update([
                                'status' => 'sent',
                                'sent_at' => now(),
                            ]);
                            $this->campaign->incrementSentCount();
                            
                            CampaignLog::logEvent(
                                $this->campaign->id,
                                'accepted',
                                $recipient->id,
                                ['email' => $recipient->email, 'message' => 'Email accepted by mail server']
                            );
                        });

                        try {
                            app(AutomationTriggerService::class)->scheduleNegativeCampaignTriggersForRecipient($this->campaign, $recipient);
                        } catch (\Throwable $e) {
                            Log::warning('Failed scheduling negative campaign automation triggers', [
                                'campaign_id' => $this->campaign->id,
                                'recipient_id' => $recipient->id,
                                'error' => $e->getMessage(),
                            ]);
                        }

                        $sentCount++;
                    }
                );

                // Reset consecutive transport failure counter on success
                $consecutiveTransportFailures = 0;

            } catch (\Exception $e) {
                $failedCount++;

                // Check if this is a rate limit/SMTP quota issue
                $isRateLimitError = $this->isRateLimitError($e);
                
                // Track consecutive transport failures to detect server-down scenarios
                if ($e instanceof Swift_TransportException || $e instanceof \Symfony\Component\Mailer\Exception\TransportExceptionInterface) {
                    $consecutiveTransportFailures++;
                    Log::error("Transport error sending to {$recipient->email} (consecutive: {$consecutiveTransportFailures}): " . $e->getMessage());
                    
                    // If this is a rate limit error, pause the campaign
                    if ($isRateLimitError) {
                        $this->handleRateLimitError($e);
                        return; // Stop processing this chunk
                    }
                } else {
                    Log::error("Failed to send campaign email to {$recipient->email}: " . $e->getMessage());
                    
                    // Also check for rate limit in non-transport exceptions
                    if ($isRateLimitError) {
                        $this->handleRateLimitError($e);
                        return;
                    }
                }

                // Mark recipient as failed but continue processing the rest of the chunk
                try {
                    $recipient->refresh();
                    if ($recipient->status !== 'failed') {
                        DB::transaction(function () use ($recipient, $e) {
                            $recipient->markAsFailed($e->getMessage());
                            $this->campaign->incrementFailedCount();
                            
                            CampaignLog::logEvent(
                                $this->campaign->id,
                                'failed',
                                $recipient->id,
                                ['email' => $recipient->email],
                                null,
                                null,
                                null,
                                $e->getMessage()
                            );
                        });
                    }
                } catch (\Throwable $markError) {
                    Log::error("Failed to mark recipient {$recipient->id} as failed: " . $markError->getMessage());
                }
            }

            // Small delay to respect rate limits
            usleep(500000); // 0.5 seconds between emails
        }

        Log::info("Campaign chunk processed for campaign {$this->campaign->id}: {$sentCount} sent, {$failedCount} failed");

        // Check for bounces after chunk completion if bounce server is configured
        $this->campaign->loadMissing('bounceServer');
        $bounceServer = $this->campaign->bounceServer ?: $deliveryServer?->bounceServer;
        if ($bounceServer && $bounceServer->isActive()) {
            $this->checkBounces($bounceServer);
        }

        // Check if campaign is complete
        $this->campaign->refresh();
        $this->campaign->checkCompletion();
    }

    /**
     * Check for bounced emails from the bounce server.
     */
    protected function checkBounces($bounceServer): void
    {
        try {
            // Check if IMAP extension is available
            if (!function_exists('imap_open')) {
                Log::warning("IMAP extension not available. Bounce checking skipped for campaign {$this->campaign->id}. Please install php-imap extension.", [
                    'campaign_id' => $this->campaign->id,
                    'bounce_server_id' => $bounceServer->id,
                    'message' => 'Install php-imap extension to enable automatic bounce processing. You can manually process bounces using: php artisan bounces:process',
                ]);
                return;
            }
            
            $bounceProcessor = app(\App\Services\BounceProcessorService::class);
            $processedCount = $bounceProcessor->processBounces($bounceServer);
            
            if ($processedCount > 0) {
                Log::info("Processed {$processedCount} bounce(s) for campaign {$this->campaign->id}", [
                    'campaign_id' => $this->campaign->id,
                    'bounce_server_id' => $bounceServer->id,
                    'processed_count' => $processedCount,
                ]);
                
                // Refresh campaign to get updated bounce count
                $this->campaign->refresh();
                
                // Sync stats to ensure accuracy
                $this->campaign->syncStats();
            }
        } catch (\Exception $e) {
            // Log error but don't fail the chunk job
            Log::error("Failed to check bounces for campaign {$this->campaign->id}: " . $e->getMessage(), [
                'campaign_id' => $this->campaign->id,
                'bounce_server_id' => $bounceServer->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Consume delivery server quotas for a number of messages.
     *
     * Returns null if allowed, or the number of seconds until the earliest quota window resets.
     */
    protected function consumeDeliveryServerQuotaOrReturnDelaySeconds(DeliveryServer $deliveryServer, int $tokens): ?int
    {
        if ($tokens <= 0) {
            return null;
        }

        $limits = [
            ['suffix' => 'second', 'max' => (int) ($deliveryServer->second_quota ?? 0), 'decay' => 1],
            ['suffix' => 'minute', 'max' => (int) ($deliveryServer->minute_quota ?? 0), 'decay' => 60],
            ['suffix' => 'hour', 'max' => (int) ($deliveryServer->hourly_quota ?? 0), 'decay' => 3600],
            ['suffix' => 'day', 'max' => (int) ($deliveryServer->daily_quota ?? 0), 'decay' => 86400],
            ['suffix' => 'month', 'max' => (int) ($deliveryServer->monthly_quota ?? 0), 'decay' => 2592000],
        ];

        // First check all quotas without consuming.
        $maxWait = 0;
        foreach ($limits as $limit) {
            if ($limit['max'] <= 0) {
                continue;
            }

            $key = $this->deliveryServerQuotaKey($deliveryServer, $limit['suffix']);
            if (RateLimiter::remaining($key, $limit['max']) < $tokens) {
                $maxWait = max($maxWait, RateLimiter::availableIn($key));
            }
        }

        if ($maxWait > 0) {
            return $maxWait;
        }

        // Consume tokens for all quotas.
        foreach ($limits as $limit) {
            if ($limit['max'] <= 0) {
                continue;
            }

            $key = $this->deliveryServerQuotaKey($deliveryServer, $limit['suffix']);
            for ($i = 0; $i < $tokens; $i++) {
                RateLimiter::hit($key, $limit['decay']);
            }
        }

        return null;
    }

    protected function deliveryServerQuotaKey(DeliveryServer $deliveryServer, string $suffix): string
    {
        return 'delivery-server-quota:' . $deliveryServer->id . ':' . $suffix;
    }

    protected function pauseCampaignForDeliveryServerQuota(DeliveryServer $deliveryServer, int $waitSeconds): void
    {
        $waitSeconds = max(1, $waitSeconds);
        $resumeAt = now()->addSeconds($waitSeconds + 10);

        Log::warning('Delivery server quota reached. Pausing campaign.', [
            'campaign_id' => $this->campaign->id,
            'delivery_server_id' => $deliveryServer->id,
            'delivery_server_name' => $deliveryServer->name,
            'wait_seconds' => $waitSeconds,
            'resume_at' => $resumeAt->toIso8601String(),
            'hourly_quota' => (int) ($deliveryServer->hourly_quota ?? 0),
            'daily_quota' => (int) ($deliveryServer->daily_quota ?? 0),
            'monthly_quota' => (int) ($deliveryServer->monthly_quota ?? 0),
        ]);

        $this->campaign->update([
            'status' => 'paused',
            'failure_reason' => 'Delivery server quota reached. Auto-resume scheduled.',
        ]);

        \App\Jobs\ResumeCampaignJob::dispatch($this->campaign)
            ->delay($resumeAt)
            ->onQueue('campaigns');
    }

    protected function isClockSkewTooHigh(): bool
    {
        $cacheSeconds = max(0, (int) config('services.clock_skew_check.cache_seconds', 60));
        $cacheKey = 'system:clock_skew_status';

        $status = Cache::remember($cacheKey, now()->addSeconds($cacheSeconds), function () {
            $url = (string) config('services.clock_skew_check.url', 'https://www.google.com');
            $thresholdSeconds = (int) config('services.clock_skew_check.threshold_seconds', 120);
            $timeoutSeconds = (int) config('services.clock_skew_check.timeout_seconds', 5);

            try {
                $response = Http::timeout($timeoutSeconds)
                    ->withHeaders([
                        'Cache-Control' => 'no-cache',
                        'Pragma' => 'no-cache',
                    ])
                    ->head($url);

                $dateHeader = $response->header('Date');
                if (!is_string($dateHeader) || trim($dateHeader) === '') {
                    throw new \RuntimeException('No Date header returned by time-check endpoint.');
                }

                $remoteUtc = CarbonImmutable::parse($dateHeader, 'UTC');
                $localUtc = CarbonImmutable::now('UTC');
                $skewSeconds = abs($localUtc->diffInSeconds($remoteUtc, false));

                return [
                    'checked_at' => CarbonImmutable::now()->toIso8601String(),
                    'url' => $url,
                    'threshold_seconds' => $thresholdSeconds,
                    'timeout_seconds' => $timeoutSeconds,
                    'skew_seconds' => $skewSeconds,
                    'server_utc' => $localUtc->toIso8601String(),
                    'remote_utc' => $remoteUtc->toIso8601String(),
                    'ok' => $skewSeconds <= $thresholdSeconds,
                ];
            } catch (\Throwable $e) {
                return [
                    'checked_at' => CarbonImmutable::now()->toIso8601String(),
                    'url' => $url,
                    'threshold_seconds' => $thresholdSeconds,
                    'timeout_seconds' => $timeoutSeconds,
                    'ok' => true,
                    'error' => $e->getMessage(),
                ];
            }
        });

        if (!is_array($status)) {
            return false;
        }

        if (!empty($status['error'] ?? null)) {
            Log::warning('Clock skew check failed (campaign send will continue)', [
                'campaign_id' => $this->campaign->id,
                'error' => $status['error'],
            ]);
        }

        return ($status['ok'] ?? true) === false;
    }

    protected function pauseCampaignForClockSkew(int $waitSeconds): void
    {
        $waitSeconds = max(60, $waitSeconds);
        $resumeAt = now()->addSeconds($waitSeconds);

        Log::warning('Clock skew too high. Pausing campaign to avoid AWS signature errors.', [
            'campaign_id' => $this->campaign->id,
            'wait_seconds' => $waitSeconds,
            'resume_at' => $resumeAt->toIso8601String(),
        ]);

        $this->campaign->update([
            'status' => 'paused',
            'failure_reason' => 'Server time appears out of sync. Campaign paused to avoid AWS SES signature errors. Auto-resume scheduled.',
        ]);

        \App\Jobs\ResumeCampaignJob::dispatch($this->campaign)
            ->delay($resumeAt)
            ->onQueue('campaigns');
    }

    /**
     * Configure mailer based on delivery server.
     * Returns the mailer name to use, or null if using default.
     */
    protected function configureMailer(?DeliveryServer $deliveryServer): ?string
    {
        try {
            if (!$deliveryServer || !$deliveryServer->isActive()) {
                Log::warning("No active delivery server found for campaign {$this->campaign->id}. Using default mailer.");
                return null;
            }

            // Ensure bounce server relation is loaded so we can log its email
            $deliveryServer->loadMissing('bounceServer');

            // Determine the effective sending domain for this campaign (if verified)
            $this->campaign->loadMissing('sendingDomain', 'emailList.sendingDomain');
            $sendingDomainOverride = null;
            if ($this->campaign->sendingDomain && $this->campaign->sendingDomain->isVerified()) {
                $sendingDomainOverride = $this->campaign->sendingDomain->domain;
            } elseif ($this->campaign->emailList && $this->campaign->emailList->sendingDomain && $this->campaign->emailList->sendingDomain->isVerified()) {
                $sendingDomainOverride = $this->campaign->emailList->sendingDomain->domain;
            }

            $bounceServer = $deliveryServer->bounceServer;
            $bounceServerId = $bounceServer?->id;
            $bounceEmail = $bounceServer?->username;

            // Log which bounce email is configured for this delivery server
            Log::info('Configuring mailer with delivery/bounce server for campaign', [
                'campaign_id' => $this->campaign->id,
                'delivery_server_id' => $deliveryServer->id,
                'delivery_server_name' => $deliveryServer->name,
                'delivery_server_type' => $deliveryServer->type,
                'delivery_server_from_email' => $this->campaign->from_email,
                'bounce_server_id' => $bounceServerId,
                'bounce_email' => $bounceEmail,
                'has_bounce_server' => $bounceServer !== null,
            ]);

            // Warn if no bounce server is configured
            if (!$bounceServer) {
                Log::warning('Delivery server has no bounce server configured', [
                    'campaign_id' => $this->campaign->id,
                    'delivery_server_id' => $deliveryServer->id,
                    'delivery_server_name' => $deliveryServer->name,
                    'message' => 'Bounced emails will not be properly tracked. Consider configuring a bounce server for this delivery server.',
                ]);
            }

            // Configure mailer using DeliveryServerService
            $deliveryServerService = app(DeliveryServerService::class);
            $deliveryServerService->configureMailFromServer($deliveryServer, $sendingDomainOverride);
            
            // Get the configured mailer name
            $mailerName = config('mail.default', 'smtp');
            
            Log::debug("Configured mailer for campaign {$this->campaign->id} using delivery server: {$deliveryServer->name} ({$deliveryServer->type}), mailer: {$mailerName}");
            
            return $mailerName;
        } catch (\Exception $e) {
            Log::error("Failed to configure mailer for campaign {$this->campaign->id}: " . $e->getMessage());
            // Continue anyway - might use default mailer
            return null;
        }
    }

    protected function sendChunkViaZeptoBatch(DeliveryServer $deliveryServer, $recipients, int &$sentCount, int &$failedCount): void
    {
        $this->campaign->loadMissing('trackingDomain');

        $trackingBase = null;
        $hasCustomTrackingDomain = false;
        if ($this->campaign->trackingDomain && $this->campaign->trackingDomain->isVerified()) {
            $trackingBase = 'https://' . $this->campaign->trackingDomain->domain;
            $hasCustomTrackingDomain = true;
        }
        $trackingBase = $trackingBase ?: rtrim((string) config('app.url'), '/');
        $fallbackTrackingBase = rtrim((string) config('app.url'), '/');

        $subject = $this->convertPlaceholdersToZeptoMergeTags((string) ($this->campaign->subject ?? ''));

        $htmlBody = $this->campaign->html_content ?? null;
        $textBody = $this->campaign->plain_text_content ?? null;

        $htmlBody = is_string($htmlBody) ? $this->convertPlaceholdersToZeptoMergeTags($htmlBody) : null;
        $textBody = is_string($textBody) ? $this->convertPlaceholdersToZeptoMergeTags($textBody) : null;

        // Append footer template to Zepto HTML if configured
        $footerTemplateId = $this->campaign->settings['footer_template_id'] ?? null;
        if ($footerTemplateId && is_string($htmlBody)) {
            $footerTemplate = \App\Models\Template::find($footerTemplateId);
            if ($footerTemplate && $footerTemplate->type === 'footer') {
                $footerHtml = $this->convertPlaceholdersToZeptoMergeTags($footerTemplate->html_content ?? '');
                if (stripos($htmlBody, '</body>') !== false) {
                    $htmlBody = str_ireplace('</body>', $footerHtml . '</body>', $htmlBody);
                } else {
                    $htmlBody .= $footerHtml;
                }
            }
        }

        // Append signature template to Zepto HTML if configured
        $signatureTemplateId = $this->campaign->settings['signature_template_id'] ?? null;
        if ($signatureTemplateId && is_string($htmlBody)) {
            $signatureTemplate = \App\Models\Template::find($signatureTemplateId);
            if ($signatureTemplate && $signatureTemplate->type === 'signature') {
                $signatureHtml = $this->convertPlaceholdersToZeptoMergeTags($signatureTemplate->html_content ?? '');
                if (stripos($htmlBody, '</body>') !== false) {
                    $htmlBody = str_ireplace('</body>', $signatureHtml . '</body>', $htmlBody);
                } else {
                    $htmlBody .= $signatureHtml;
                }
            }
        }

        // Append footer and signature to Zepto plain text if configured
        if ($footerTemplateId && is_string($textBody)) {
            $footerTemplate = \App\Models\Template::find($footerTemplateId);
            if ($footerTemplate && $footerTemplate->type === 'footer') {
                $footerText = $this->convertPlaceholdersToZeptoMergeTags($footerTemplate->plain_text_content ?? '');
                $textBody .= "\n\n" . $footerText;
            }
        }
        if ($signatureTemplateId && is_string($textBody)) {
            $signatureTemplate = \App\Models\Template::find($signatureTemplateId);
            if ($signatureTemplate && $signatureTemplate->type === 'signature') {
                $signatureText = $this->convertPlaceholdersToZeptoMergeTags($signatureTemplate->plain_text_content ?? '');
                $textBody .= "\n\n" . $signatureText;
            }
        }

        $unsubscribeUrl = $trackingBase . '/unsubscribe/{{recipient_uuid}}';
        $trackOpenUrl = $trackingBase . '/track/open/{{recipient_uuid}}';

        if (is_string($htmlBody) && $htmlBody !== '') {
            if ($this->campaign->track_opens) {
                $trackPixel = '<img src="' . $trackOpenUrl . '" width="1" height="1" style="display:none;" alt="" />';

                if ($hasCustomTrackingDomain) {
                    $trackHost = parse_url($trackingBase, PHP_URL_HOST);
                    $fallbackHost = parse_url($fallbackTrackingBase, PHP_URL_HOST);
                    if (is_string($trackHost) && $trackHost !== '' && is_string($fallbackHost) && $fallbackHost !== '' && strcasecmp($trackHost, $fallbackHost) !== 0) {
                        $fallbackOpenUrl = $fallbackTrackingBase . '/track/open/{{recipient_uuid}}';
                        $trackPixel .= '<img src="' . $fallbackOpenUrl . '" width="1" height="1" style="display:none;" alt="" />';
                    }
                }
                if (stripos($htmlBody, '</body>') !== false) {
                    $htmlBody = str_ireplace('</body>', $trackPixel . '</body>', $htmlBody);
                } else {
                    $htmlBody .= $trackPixel;
                }
            }

            if ($this->campaign->track_clicks) {
                $htmlBody = $this->wrapLinksWithZeptoTracking($htmlBody, $trackingBase);
            }

            $unsubscribeLink = '<p style="font-size: 12px; color: #999; margin-top: 20px; text-align:center;"><a href="' . $unsubscribeUrl . '" style="color: #999;">Unsubscribe</a></p>';
            if (
                stripos($htmlBody, '{unsubscribe_url}') !== false
                || stripos($htmlBody, '{{unsubscribe_url}}') !== false
                || preg_match('/(?<![a-zA-Z0-9_])unsubscribe_url(?![a-zA-Z0-9_])/i', $htmlBody)
            ) {
                $htmlBody = str_ireplace(['{{unsubscribe_url}}', '{unsubscribe_url}'], $unsubscribeUrl, $htmlBody);
                $htmlBody = (string) preg_replace('/(?<![a-zA-Z0-9_])unsubscribe_url(?![a-zA-Z0-9_])/i', $unsubscribeUrl, $htmlBody);
            } elseif (stripos($htmlBody, '</body>') !== false) {
                $htmlBody = str_ireplace('</body>', $unsubscribeLink . '</body>', $htmlBody);
            } else {
                $htmlBody .= $unsubscribeLink;
            }
        }

        if (!is_string($textBody) || trim($textBody) === '') {
            $textBody = is_string($htmlBody) ? strip_tags($htmlBody) : '';
        }
        $textBody = str_ireplace(['{{unsubscribe_url}}', '{unsubscribe_url}'], $unsubscribeUrl, (string) $textBody);
        $textBody = (string) preg_replace('/(?<![a-zA-Z0-9_])unsubscribe_url(?![a-zA-Z0-9_])/i', $unsubscribeUrl, $textBody);
        $textBody = rtrim((string) $textBody) . "\n\n---\nUnsubscribe: " . $unsubscribeUrl;

        $recipientByEmail = method_exists($recipients, 'keyBy') ? $recipients->keyBy('email') : null;

        $to = [];
        foreach ($recipients as $recipient) {
            $toName = trim((string) (($recipient->first_name ?? '') . ' ' . ($recipient->last_name ?? '')));

            $meta = is_array($recipient->meta) ? $recipient->meta : [];
            $custom = (isset($meta['custom_fields']) && is_array($meta['custom_fields'])) ? $meta['custom_fields'] : [];

            $customMerge = [];
            foreach ($custom as $k => $v) {
                if (!is_string($k) || $k === '') {
                    continue;
                }
                if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $k)) {
                    continue;
                }
                if ($v === null) {
                    $customMerge['cf_' . $k] = '';
                    continue;
                }
                if (is_scalar($v)) {
                    $customMerge['cf_' . $k] = (string) $v;
                }
            }

            $to[] = [
                'email_address' => [
                    'address' => (string) $recipient->email,
                    'name' => $toName,
                ],
                'merge_info' => [
                    'first_name' => (string) ($recipient->first_name ?? ''),
                    'last_name' => (string) ($recipient->last_name ?? ''),
                    'email' => (string) $recipient->email,
                    'full_name' => $toName,
                    'recipient_uuid' => (string) ($recipient->uuid ?? ''),
                ] + $customMerge,
            ];
        }

        $message = [
            'from_email' => (string) ($this->campaign->from_email ?? ''),
            'from_name' => (string) ($this->campaign->from_name ?? ''),
            'subject' => $subject,
            'htmlbody' => $htmlBody,
            'textbody' => $textBody,
            'track_clicks' => (bool) ($this->campaign->track_clicks ?? false),
            'track_opens' => (bool) ($this->campaign->track_opens ?? false),
            'client_reference' => 'campaign-' . $this->campaign->id . '-{{recipient_uuid}}',
            'headers' => [
                'X-Campaign-ID' => (string) $this->campaign->id,
                'X-List-ID' => (string) ($this->campaign->list_id ?? ''),
                'X-Delivery-Server-ID' => (string) ($deliveryServer->id ?? ''),
            ],
        ];

        $deliveryServer->loadMissing('bounceServer');
        if (empty(($deliveryServer->settings ?? [])['bounce_address']) && !empty($deliveryServer->bounceServer?->username)) {
            $message['bounce_address'] = (string) $deliveryServer->bounceServer->username;
        }

        $replyTrackingEnabled = (bool) config('mailzen.reply_tracking.enabled', false);

        $this->campaign->loadMissing('replyServer');
        $replyDomain = trim((string) ($this->campaign->replyServer?->reply_domain ?? config('mailzen.reply_tracking.reply_domain', '')));
        if ($replyTrackingEnabled && $replyDomain !== '') {
            $message['headers']['Reply-To'] = 'reply+{{recipient_uuid}}@' . $replyDomain;
        }

        $service = app(ZeptoMailApiService::class);
        $chunks = array_chunk($to, 500);

        foreach ($chunks as $chunk) {
            try {
                $quotaPauseDelaySeconds = $this->consumeDeliveryServerQuotaOrReturnDelaySeconds($deliveryServer, count($chunk));
                if ($quotaPauseDelaySeconds !== null) {
                    $this->pauseCampaignForDeliveryServerQuota($deliveryServer, $quotaPauseDelaySeconds);
                    return;
                }

                $service->sendBatch($deliveryServer, $message, $chunk);

                DB::transaction(function () use ($chunk, $recipientByEmail, &$sentCount) {
                    foreach ($chunk as $toItem) {
                        $email = $toItem['email_address']['address'] ?? null;
                        if (!is_string($email) || $email === '') {
                            continue;
                        }

                        $recipient = null;
                        if ($recipientByEmail) {
                            $recipient = $recipientByEmail->get($email);
                        }

                        if (!$recipient) {
                            $recipient = CampaignRecipient::where('campaign_id', $this->campaign->id)
                                ->where('email', $email)
                                ->where('status', 'pending')
                                ->first();
                        }

                        if (!$recipient || $recipient->status !== 'pending') {
                            continue;
                        }

                        $recipient->update([
                            'status' => 'sent',
                            'sent_at' => now(),
                        ]);
                        $this->campaign->incrementSentCount();

                        CampaignLog::logEvent(
                            $this->campaign->id,
                            'accepted',
                            $recipient->id,
                            ['email' => $recipient->email, 'message' => 'Email accepted by ZeptoMail API']
                        );

                        $sentCount++;

                        try {
                            app(AutomationTriggerService::class)->scheduleNegativeCampaignTriggersForRecipient($this->campaign, $recipient);
                        } catch (\Throwable $e) {
                            Log::warning('Failed scheduling negative campaign automation triggers', [
                                'campaign_id' => $this->campaign->id,
                                'recipient_id' => $recipient->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                });
            } catch (\Exception $e) {
                $failedCount += count($chunk);
                Log::error('ZeptoMail batch send failed: ' . $e->getMessage(), [
                    'campaign_id' => $this->campaign->id,
                    'delivery_server_id' => $deliveryServer->id,
                ]);

                DB::transaction(function () use ($chunk, $e) {
                    foreach ($chunk as $toItem) {
                        $email = $toItem['email_address']['address'] ?? null;
                        if (!is_string($email) || $email === '') {
                            continue;
                        }

                        $recipient = CampaignRecipient::where('campaign_id', $this->campaign->id)
                            ->where('email', $email)
                            ->where('status', 'pending')
                            ->first();

                        if (!$recipient) {
                            continue;
                        }

                        $recipient->markAsFailed($e->getMessage());
                        $this->campaign->incrementFailedCount();

                        CampaignLog::logEvent(
                            $this->campaign->id,
                            'failed',
                            $recipient->id,
                            ['email' => $recipient->email],
                            null,
                            null,
                            null,
                            $e->getMessage()
                        );
                    }
                });
            }
        }
    }

    protected function convertPlaceholdersToZeptoMergeTags(string $content): string
    {
        return app(PersonalizationService::class)->convertPlaceholdersToZeptoMergeTags($content);
    }

    protected function wrapLinksWithZeptoTracking(string $content, string $trackingBase): string
    {
        $trackingHost = parse_url($trackingBase, PHP_URL_HOST);

        return preg_replace_callback(
            '/<a\s+([^>]*\s+)?href=["\']([^"\']+)["\']([^>]*)>/i',
            function ($matches) use ($trackingBase, $trackingHost) {
                $originalUrl = $matches[2];

                if (str_contains($originalUrl, '/track/click/') || str_contains($originalUrl, '/t/click/')) {
                    return $matches[0];
                }

                if (is_string($trackingHost) && $trackingHost !== '') {
                    $host = parse_url($originalUrl, PHP_URL_HOST);
                    if (is_string($host) && $host !== '' && strcasecmp($trackingHost, $host) === 0) {
                        $path = (string) (parse_url($originalUrl, PHP_URL_PATH) ?? '');
                        if (str_starts_with($path, '/track/click/') || str_starts_with($path, '/t/click/')) {
                            return $matches[0];
                        }
                    }
                }

                $encodedUrl = rtrim(strtr(base64_encode($originalUrl), '+/', '-_'), '=');
                $trackUrl = rtrim($trackingBase, '/') . '/track/click/{{recipient_uuid}}/' . $encodedUrl;
                $attributes = $matches[1] . $matches[3];
                return '<a ' . trim($attributes) . ' href="' . $trackUrl . '">';
            },
            $content
        );
    }

    /**
     * Check if an exception is related to rate limiting/SMTP quotas.
     */
    protected function isRateLimitError(\Exception $e): bool
    {
        $message = strtolower($e->getMessage());
        
        // Common rate limit error patterns
        $rateLimitPatterns = [
            'message rejected',
            'already sent',
            'messages for',
            'hour',
            'limit',
            'quota',
            'throttl',
            'too many',
            'rate limit',
            'exceeded',
            'suspended',
            'temporarily',
            'try again later',
            '550', // SMTP error code for policy violations
            '421', // SMTP error code for service not available
            '451', // SMTP error code for local error in processing
        ];
        
        foreach ($rateLimitPatterns as $pattern) {
            if (str_contains($message, $pattern)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Handle rate limit errors by pausing the campaign and scheduling resume.
     */
    protected function handleRateLimitError(\Exception $e): void
    {
        $errorMessage = $e->getMessage();
        
        Log::warning("Rate limit detected for campaign {$this->campaign->id}. Pausing campaign.", [
            'campaign_id' => $this->campaign->id,
            'error_message' => $errorMessage,
            'delivery_server_id' => $this->campaign->delivery_server_id,
        ]);
        
        // Pause the campaign
        $this->campaign->update([
            'status' => 'paused',
            'failure_reason' => 'Rate limit reached: ' . substr($errorMessage, 0, 200),
        ]);
        
        // Calculate resume time based on the error message
        $resumeDelay = $this->calculateResumeDelay($errorMessage);
        $resumeAt = now()->addMinutes($resumeDelay);
        
        Log::info("Campaign {$this->campaign->id} will resume automatically at {$resumeAt->toIso8601String()} ({$resumeDelay} minutes)");
        
        // Schedule automatic resume
        \App\Jobs\ResumeCampaignJob::dispatch($this->campaign)
            ->delay($resumeAt)
            ->onQueue('campaigns');
    }
    
    /**
     * Calculate how long to wait before resuming based on the error message.
     */
    protected function calculateResumeDelay(string $errorMessage): int
    {
        $message = strtolower($errorMessage);
        
        // Extract time from error messages
        if (preg_match('/(\d+)\s*hour/', $message, $matches)) {
            return (int) $matches[1] * 60 + 5; // Add 5 minute buffer
        }
        
        if (preg_match('/(\d+)\s*minute/', $message, $matches)) {
            return (int) $matches[1] + 5; // Add 5 minute buffer
        }
        
        // Default delays based on common patterns
        if (str_contains($message, 'hour')) {
            return 65; // 1 hour + 5 minutes
        }
        
        if (str_contains($message, 'minute')) {
            return 10; // Assume 5 minutes + buffer
        }
        
        return 30; // Default 30 minutes for unknown rate limits
    }

    /**
     * Get appropriate rate limit based on delivery server type.
     */
    protected function getRateLimitForServer(?DeliveryServer $deliveryServer): int
    {
        // Default rate limits per minute for different server types
        $rateLimits = [
            'smtp' => 20, // Conservative for shared hosting SMTP
            'amazon-ses' => 60, // SES allows higher rates
            'mailgun' => 60, // Mailgun typical limits
            'sendgrid' => 60, // SendGrid typical limits
            'postmark' => 60, // Postmark typical limits
            'zeptomail-api' => 100, // API-based services allow higher rates
        ];

        $serverType = $deliveryServer?->type ?? 'smtp';
        
        // Special handling for known hosting providers with strict limits
        if ($serverType === 'smtp' && $deliveryServer) {
            $host = strtolower((string) ($deliveryServer->hostname ?? ''));
            if (str_contains($host, 'siteground') || str_contains($host, 'sg')) {
                return 15; // Very conservative for SiteGround (900/hour)
            }
        }

        return $rateLimits[$serverType] ?? 20;
    }

    /**
     * Check if email should be blocked due to spam score.
     */
    protected function shouldBlockDueToSpamScore(CampaignRecipient $recipient, ?DeliveryServer $deliveryServer): bool
    {
        // Check if spam scoring is enabled for this campaign/customer
        $spamScoringEnabled = $this->campaign->settings['spam_scoring_enabled'] ?? 
                              $this->campaign->customer?->groupSetting('spam_scoring.enabled', false);
        
        if (!$spamScoringEnabled) {
            return false;
        }

        try {
            $spamScoringService = app(SpamScoringService::class);
            
            // Get sender info for scoring
            $fromEmail = $this->campaign->from_email ?? 
                        $deliveryServer?->from_email ?? 
                        $this->campaign->emailList?->from_email ?? 
                        config('mail.from.address');
            
            $replyToEmail = $this->campaign->reply_to ?? $fromEmail;

            $senderOptions = [
                'from_email' => $fromEmail,
                'reply_to' => $replyToEmail,
            ];

            // Calculate spam score
            $spamResult = $spamScoringService->calculateSpamScore(
                $this->campaign->subject,
                $this->campaign->html_content ?? '',
                $this->campaign->plain_text_content ?? '',
                $senderOptions
            );

            // Log spam score for analytics
            Log::info('Spam score calculated for campaign email', [
                'campaign_id' => $this->campaign->id,
                'recipient_id' => $recipient->id,
                'recipient_email' => $recipient->email,
                'spam_score' => $spamResult['score'],
                'assessment' => $spamResult['assessment'],
                'should_block' => $spamResult['should_block'],
            ]);

            // Block if score is too high
            if ($spamResult['should_block']) {
                DB::transaction(function () use ($recipient, $spamResult) {
                    $recipient->update([
                        'status' => 'failed',
                        'sent_at' => now(),
                    ]);
                    $this->campaign->incrementFailedCount();
                    
                    CampaignLog::logEvent(
                        $this->campaign->id,
                        'blocked_by_spam_filter',
                        $recipient->id,
                        ['email' => $recipient->email],
                        null,
                        null,
                        null,
                        'Email blocked due to high spam score: ' . $spamResult['score'] . ' (' . $spamResult['assessment'] . ')'
                    );
                });

                Log::warning('Email blocked due to high spam score', [
                    'campaign_id' => $this->campaign->id,
                    'recipient_email' => $recipient->email,
                    'spam_score' => $spamResult['score'],
                    'assessment' => $spamResult['assessment'],
                    'issues' => $spamResult['issues'],
                ]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to calculate spam score', [
                'campaign_id' => $this->campaign->id,
                'recipient_email' => $recipient->email,
                'error' => $e->getMessage(),
            ]);
            
            // Don't block on error, but log it
            return false;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("SendCampaignChunkJob failed for campaign {$this->campaign->id}: " . $exception->getMessage());
        
        // Mark recipients as failed
        CampaignRecipient::whereIn('id', $this->recipientIds)
            ->where('status', 'pending')
            ->update([
                'status' => 'failed',
                'failed_at' => now(),
                'failure_reason' => $exception->getMessage(),
            ]);
    }
}

