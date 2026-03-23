<?php

namespace App\Services;

use App\Jobs\StartCampaignJob;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Customer;
use App\Models\DeliveryServer;
use App\Models\EmailList;
use App\Models\BounceServer;
use App\Models\SendingDomain;
use App\Models\TrackingDomain;
use App\Models\ListSubscriber;
use App\Models\Template;
use App\Services\AutomationTriggerService;
use App\Services\Tracking\TrackingHasher;
use App\Services\Billing\UsageService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CampaignService
{
    public function __construct(
        private readonly UsageService $usageService,
        private readonly TrackingHasher $trackingHasher,
        private readonly DeliveryServerService $deliveryServerService,
    ) {
    }

    public function ensureCanRun(Campaign $campaign): void
    {
        $campaign->loadMissing('customer', 'deliveryServer', 'sendingDomain', 'trackingDomain');
        $customer = $campaign->customer;

        if (!$customer) {
            throw new \RuntimeException('Campaign customer not found.');
        }

        $mustAddDelivery = (bool) $customer->groupSetting('servers.permissions.must_add_delivery_server', false);
        $mustAddBounce = (bool) $customer->groupSetting('servers.permissions.must_add_bounce_server', false);
        $mustAddSending = (bool) $customer->groupSetting('domains.sending_domains.must_add', false);
        $mustAddTracking = (bool) $customer->groupSetting('domains.tracking_domains.must_add', false);
        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);

        if ($mustAddDelivery) {
            $hasOwnDelivery = DeliveryServer::query()
                ->where('customer_id', $customer->id)
                ->where('status', 'active')
                ->where('use_for', true)
                ->exists();

            if (!$hasOwnDelivery) {
                throw new \RuntimeException('You must add a delivery server before running a campaign.');
            }

            if (!$campaign->delivery_server_id) {
                throw new \RuntimeException('You must select a delivery server before running a campaign.');
            }

            $deliveryServer = DeliveryServer::query()
                ->whereKey($campaign->delivery_server_id)
                ->where('customer_id', $customer->id)
                ->where('status', 'active')
                ->where('use_for', true)
                ->first();

            if (!$deliveryServer) {
                throw new \RuntimeException('Selected delivery server is not available.');
            }
        } else {
            if ($campaign->delivery_server_id) {
                $deliveryServer = DeliveryServer::query()
                    ->whereKey($campaign->delivery_server_id)
                    ->where(function ($q) use ($customer, $canUseSystem) {
                        $q->where('customer_id', $customer->id);
                        if ($canUseSystem) {
                            $q->orWhereNull('customer_id');
                        }
                    })
                    ->where('status', 'active')
                    ->where('use_for', true)
                    ->first();

                if (!$deliveryServer) {
                    throw new \RuntimeException('Selected delivery server is not available.');
                }
            }
        }

        if ($mustAddBounce) {
            $hasOwnBounce = BounceServer::query()
                ->where('customer_id', $customer->id)
                ->where('active', true)
                ->exists();

            if (!$hasOwnBounce) {
                $hasSystemBounce = BounceServer::query()
                    ->whereNull('customer_id')
                    ->where('active', true)
                    ->exists();

                if (!$hasSystemBounce) {
                    throw new \RuntimeException('You must add a bounce server before running a campaign.');
                }
            }

            if (!$campaign->delivery_server_id) {
                throw new \RuntimeException('You must select a delivery server with a bounce server before running a campaign.');
            }

            $deliveryServerWithBounce = DeliveryServer::query()
                ->with('bounceServer')
                ->whereKey($campaign->delivery_server_id)
                ->first();

            $bounceServer = $deliveryServerWithBounce?->bounceServer;
            if (!$bounceServer || !$bounceServer->active) {
                throw new \RuntimeException('You must select a delivery server that uses an active bounce server.');
            }

            $isCustomerBounce = (int) $bounceServer->customer_id === (int) $customer->id;
            $isSystemBounce = $bounceServer->customer_id === null;
            if (!$isCustomerBounce && (!$canUseSystem || !$isSystemBounce)) {
                throw new \RuntimeException('Selected delivery server bounce server is not available.');
            }
        }

        if ($mustAddSending) {
            $hasOwnSending = SendingDomain::query()
                ->where('customer_id', $customer->id)
                ->where('status', 'verified')
                ->exists();

            if (!$hasOwnSending) {
                throw new \RuntimeException('You must add and verify a sending domain before running a campaign.');
            }

            if (!$campaign->sending_domain_id) {
                throw new \RuntimeException('You must select a sending domain before running a campaign.');
            }

            $sendingDomain = SendingDomain::query()
                ->whereKey($campaign->sending_domain_id)
                ->where('customer_id', $customer->id)
                ->where('status', 'verified')
                ->first();

            if (!$sendingDomain) {
                throw new \RuntimeException('Selected sending domain is not available.');
            }
        } else {
            if ($campaign->sending_domain_id) {
                $sendingDomain = SendingDomain::query()
                    ->whereKey($campaign->sending_domain_id)
                    ->where(function ($q) use ($customer, $canUseSystem) {
                        $q->where('customer_id', $customer->id);
                        if ($canUseSystem) {
                            $q->orWhereNull('customer_id');
                        }
                    })
                    ->where('status', 'verified')
                    ->first();

                if (!$sendingDomain) {
                    throw new \RuntimeException('Selected sending domain is not available.');
                }
            }
        }

        if ($mustAddTracking) {
            $hasOwnTracking = TrackingDomain::query()
                ->where('customer_id', $customer->id)
                ->where('status', 'verified')
                ->exists();

            if (!$hasOwnTracking) {
                throw new \RuntimeException('You must add and verify a tracking domain before running a campaign.');
            }

            if (!$campaign->tracking_domain_id) {
                throw new \RuntimeException('You must select a tracking domain before running a campaign.');
            }

            $trackingDomain = TrackingDomain::query()
                ->whereKey($campaign->tracking_domain_id)
                ->where('customer_id', $customer->id)
                ->where('status', 'verified')
                ->first();

            if (!$trackingDomain) {
                throw new \RuntimeException('Selected tracking domain is not available.');
            }
        } else {
            if ($campaign->tracking_domain_id) {
                $trackingDomain = TrackingDomain::query()
                    ->whereKey($campaign->tracking_domain_id)
                    ->where(function ($q) use ($customer, $canUseSystem) {
                        $q->where('customer_id', $customer->id);
                        if ($canUseSystem) {
                            $q->orWhereNull('customer_id');
                        }
                    })
                    ->where('status', 'verified')
                    ->first();

                if (!$trackingDomain) {
                    throw new \RuntimeException('Selected tracking domain is not available.');
                }
            }
        }
    }
    /**
     * Get paginated list of campaigns for a customer.
     */
    public function getPaginated(Customer $customer, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Campaign::where('customer_id', $customer->id)
            ->with(['emailList']);

        // Apply filters
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new campaign.
     */
    public function create(Customer $customer, array $data): Campaign
    {
        // If template_id is provided, load template content
        $htmlContent = $data['html_content'] ?? null;
        $plainTextContent = $data['plain_text_content'] ?? null;
        $templateId = $data['template_id'] ?? null;

        if ($templateId && !$htmlContent) {
            $template = Template::find($templateId);
            if ($template) {
                $htmlContent = $template->html_content;
                $plainTextContent = $template->plain_text_content;
                // Increment template usage
                $template->incrementUsage();
            }
        }

        $campaign = Campaign::create([
            'customer_id' => $customer->id,
            'list_id' => $data['list_id'] ?? null,
            'template_id' => $templateId,
            'delivery_server_id' => $data['delivery_server_id'] ?? null,
            'reply_server_id' => $data['reply_server_id'] ?? null,
            'sending_domain_id' => $data['sending_domain_id'] ?? null,
            'tracking_domain_id' => $data['tracking_domain_id'] ?? null,
            'name' => $data['name'],
            'subject' => $data['subject'],
            'from_name' => $data['from_name'] ?? null,
            'from_email' => $data['from_email'] ?? $customer->email,
            'reply_to' => $data['reply_to'] ?? null,
            'type' => $data['type'] ?? 'regular',
            'status' => $data['status'] ?? 'draft',
            'html_content' => $htmlContent,
            'plain_text_content' => $plainTextContent,
            'template_data' => $data['template_data'] ?? [],
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'send_at' => $data['send_at'] ?? null,
            'track_opens' => $data['track_opens'] ?? true,
            'track_clicks' => $data['track_clicks'] ?? true,
            'segments' => $data['segments'] ?? [],
            'settings' => $data['settings'] ?? [],
        ]);

        $this->dispatchScheduledStartJobIfNeeded($campaign);

        return $campaign;
    }

    /**
     * Update an existing campaign.
     */
    public function update(Campaign $campaign, array $data): Campaign
    {
        // If template_id is provided and changed, load template content
        $templateId = $data['template_id'] ?? null;
        if ($templateId && $templateId != $campaign->template_id && empty($data['html_content'])) {
            $template = Template::find($templateId);
            if ($template) {
                $data['html_content'] = $template->html_content;
                $data['plain_text_content'] = $template->plain_text_content;
                // Increment template usage
                $template->incrementUsage();
            }
        }

        $campaign->update($data);
        $campaign = $campaign->fresh();

        $this->dispatchScheduledStartJobIfNeeded($campaign);

        return $campaign;
    }

    private function dispatchScheduledStartJobIfNeeded(Campaign $campaign): void
    {
        if ($campaign->status !== 'scheduled') {
            return;
        }

        if (!$campaign->scheduled_at) {
            return;
        }

        $queueConnection = config('queue.default', 'sync');
        if ($queueConnection === 'sync') {
            Log::warning(
                "Campaign {$campaign->id} is scheduled but queue is set to sync. " .
                "Auto-start requires a non-sync queue or the Laravel scheduler (campaigns:start-scheduled)."
            );
            return;
        }

        StartCampaignJob::dispatch($campaign)
            ->delay($campaign->scheduled_at)
            ->onQueue('campaigns');
    }

    /**
     * Delete a campaign.
     */
    public function delete(Campaign $campaign): bool
    {
        return $campaign->delete();
    }

    /**
     * Send a campaign.
     */
    public function send(Campaign $campaign): void
    {
        $this->ensureCanRun($campaign);
        // Check if this is an A/B test campaign
        if ($campaign->hasAbTest()) {
            $this->sendAbTest($campaign);
            return;
        }

        // Eager load required relations
        $campaign->loadMissing('customer', 'sendingDomain', 'emailList.sendingDomain');

        // Determine the effective sending domain for this campaign (if verified)
        $sendingDomainOverride = null;
        if ($campaign->sendingDomain && $campaign->sendingDomain->isVerified()) {
            $sendingDomainOverride = $campaign->sendingDomain->domain;
        } elseif ($campaign->emailList && $campaign->emailList->sendingDomain && $campaign->emailList->sendingDomain->isVerified()) {
            $sendingDomainOverride = $campaign->emailList->sendingDomain->domain;
        }

        // From email priority is handled at send-time without rewriting the domain.
        // Priority: campaign from_email > delivery server from_email > email list from_email > config default.
        $effectiveFromEmail = (string) ($campaign->from_email
            ?: ($campaign->deliveryServer?->from_email
                ?: ($campaign->emailList?->from_email
                    ?: (string) config('mail.from.address'))));

        // Load delivery server with bounce server relationship
        $deliveryServer = $campaign->deliveryServer;
        
        // Configure mail system to use the selected delivery server
        if ($deliveryServer) {
            $deliveryServer->load('bounceServer');
            // Configure mail settings based on delivery server type
            $this->deliveryServerService->configureMailFromServer($deliveryServer, $sendingDomainOverride);
        } else {
            // If no delivery server is selected, log a warning
            \Log::warning('Campaign has no delivery server selected', [
                'campaign_id' => $campaign->id,
            ]);
        }

        // Get subscribers from the email list (confirmed status means subscribed)
        $subscribers = ListSubscriber::where('list_id', $campaign->list_id)
            ->where('status', 'confirmed')
            ->get();

        $totalRecipients = $subscribers->count();

        // Update campaign status
        $campaign->update([
            'status' => 'sending',
            'started_at' => now(),
            'total_recipients' => $totalRecipients,
            'sent_count' => 0,
        ]);

        // Send emails to each subscriber
        // Note: In production, this should be done via a queue job to handle large lists
        $sentCount = 0;
        foreach ($subscribers as $subscriber) {
            try {
                // Create recipient record for tracking
                $recipient = CampaignRecipient::create([
                    'campaign_id' => $campaign->id,
                    'email' => $subscriber->email,
                    'first_name' => $subscriber->first_name,
                    'last_name' => $subscriber->last_name,
                    'meta' => [
                        'custom_fields' => is_array($subscriber->custom_fields) ? $subscriber->custom_fields : [],
                    ],
                    'status' => 'pending',
                ]);

                $personalization = app(PersonalizationService::class);

                // Prepare email content with personalization
                $htmlContent = $this->personalizeContent($campaign->html_content, $subscriber);
                $plainTextContent = $this->personalizeContent($campaign->plain_text_content, $subscriber);
                $subject = $personalization->personalizeForSubscriber((string) ($campaign->subject ?? ''), $subscriber);

                // Add unsubscribe link if HTML content exists
                if ($htmlContent) {
                    $htmlContent = $this->addUnsubscribeLink($htmlContent, $subscriber, $campaign);
                }

                // Inject tracking pixel and rewrite links for tracking using hash-based identifiers
                if ($htmlContent && $campaign->track_opens) {
                    $htmlContent = $this->addOpenTrackingPixel($htmlContent, $campaign, $recipient);
                }

                if ($htmlContent && $campaign->track_clicks) {
                    $htmlContent = $this->rewriteClickTrackingLinks($htmlContent, $campaign, $recipient);
                }

                // Send email
                $effectiveReplyTo = app(ReplyTrackingAddressService::class)
                    ->effectiveReplyTo($campaign, $recipient, $effectiveFromEmail);

                Mail::raw($plainTextContent ?: strip_tags($htmlContent), function ($message) use ($campaign, $subscriber, $htmlContent, $deliveryServer, $recipient, $effectiveFromEmail, $effectiveReplyTo, $subject) {
                    $message->to($subscriber->email, $subscriber->first_name . ' ' . $subscriber->last_name)
                        ->subject($subject)
                        ->from($effectiveFromEmail, $campaign->from_name);

                    if ($effectiveReplyTo !== '') {
                        $message->replyTo($effectiveReplyTo);
                    }

                    // logs if there is bounce server
                    Log::info('Bounce server', [
                        'bounce_server' => $deliveryServer->bounceServer->username,
                    ]);

                    Log::info('Delivery server', [
                        'delivery_server' => $deliveryServer,
                    ]);

                    // Set Return-Path to bounce server email if configured
                    // This ensures bounced emails go to the bounce server's inbox
                    if ($deliveryServer && $deliveryServer->bounceServer && $deliveryServer->bounceServer->username) {
                        $message->returnPath($deliveryServer->bounceServer->username);

                        Log::info('Added Return-Path header to test email', [
                            'return_path' => $deliveryServer->bounceServer->username,
                        ]);
                    }

                    if ($htmlContent) {
                        $message->html($htmlContent);
                    }

                    // Add headers for DKIM signing plugin and bounce tracking
                    $message->getHeaders()->addTextHeader('X-Customer-ID', $campaign->customer_id);
                    if ($campaign->delivery_server_id ?? null) {
                        $message->getHeaders()->addTextHeader('X-Delivery-Server-ID', $campaign->delivery_server_id);
                    }
                    if ($deliveryServer && $deliveryServer->bounce_server_id) {
                        $message->getHeaders()->addTextHeader('X-Bounce-Server-ID', $deliveryServer->bounce_server_id);
                    }
                    
                    // Add campaign tracking headers for bounce processing
                    $message->getHeaders()->addTextHeader('X-Campaign-ID', $campaign->id);
                    if ($campaign->list_id) {
                        $message->getHeaders()->addTextHeader('X-List-ID', $campaign->list_id);
                    }
                    if ($recipient->uuid) {
                        $message->getHeaders()->addTextHeader('X-Recipient-UUID', $recipient->uuid);
                    }
                });

                // Mark recipient and campaign stats
                $recipient->markAsSent();
                $campaign->incrementSentCount();

                try {
                    app(AutomationTriggerService::class)->scheduleNegativeCampaignTriggersForRecipient($campaign, $recipient);
                } catch (\Throwable $e) {
                    Log::warning('Failed scheduling negative campaign automation triggers', [
                        'campaign_id' => $campaign->id,
                        'recipient_id' => $recipient->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                // Log usage for billing (emails sent this month)
                if ($campaign->customer) {
                    $this->usageService->log(
                        $campaign->customer,
                        'emails_sent_this_month',
                        1,
                        ['campaign_id' => $campaign->id]
                    );
                }

                $sentCount++;
            } catch (\Exception $e) {
                // Log error but continue sending to other subscribers
                \Log::error('Failed to send campaign email', [
                    'campaign_id' => $campaign->id,
                    'subscriber_id' => $subscriber->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Update campaign with final status
        $campaign->update([
            'status' => 'sent',
            'finished_at' => now(),
            'sent_count' => $sentCount,
            'delivered_count' => $sentCount, // Assuming all sent emails are delivered for now
        ]);

        // Check for completion based on recipients
        $campaign->checkCompletion();
    }

    /**
     * Personalize email content with subscriber data.
     */
    protected function personalizeContent(?string $content, ListSubscriber $subscriber): ?string
    {
        if (!$content) {
            return null;
        }

        return app(PersonalizationService::class)->personalizeForSubscriber($content, $subscriber);
    }

    /**
     * Add unsubscribe link to email content.
     */
    protected function addUnsubscribeLink(string $htmlContent, ListSubscriber $subscriber, Campaign $campaign): string
    {
        // Generate token using the same method as PublicSubscriptionController
        $token = hash('sha256', $subscriber->email . $subscriber->list_id . config('app.key'));
        
        // Generate unsubscribe URL
        $unsubscribeUrl = route('public.unsubscribe', [
            'list' => $subscriber->list_id,
            'email' => $subscriber->email,
            'token' => $token,
        ]);

        $unsubscribeLink = '<p style="font-size: 12px; color: #666; margin-top: 20px; text-align: center;">';
        $unsubscribeLink .= '<a href="' . $unsubscribeUrl . '" style="color: #666; text-decoration: underline;">Unsubscribe from this list</a>';
        $unsubscribeLink .= '</p>';

        // Replace unsubscribe placeholder if it exists, otherwise add at the end
        if (
            stripos($htmlContent, '{unsubscribe_url}') !== false
            || stripos($htmlContent, '{{unsubscribe_url}}') !== false
            || preg_match('/(?<![a-zA-Z0-9_])unsubscribe_url(?![a-zA-Z0-9_])/i', $htmlContent)
        ) {
            $htmlContent = str_ireplace(['{{unsubscribe_url}}', '{unsubscribe_url}'], $unsubscribeUrl, $htmlContent);
            $htmlContent = (string) preg_replace('/(?<![a-zA-Z0-9_])unsubscribe_url(?![a-zA-Z0-9_])/i', $unsubscribeUrl, $htmlContent);
        } else {
            // Add unsubscribe link before closing body tag, or at the end if no body tag
            if (stripos($htmlContent, '</body>') !== false) {
                $htmlContent = str_ireplace('</body>', $unsubscribeLink . '</body>', $htmlContent);
            } else {
                $htmlContent .= $unsubscribeLink;
            }
        }

        return $htmlContent;
    }

    /**
     * Add open tracking pixel to HTML content.
     *
     * For now we use the legacy tracking routes that work with the
     * recipient UUID stored on `CampaignRecipient`. The v2 hash-based
     * routes are used elsewhere (e.g. in the queued sending pipeline).
     */
    protected function addOpenTrackingPixel(string $htmlContent, Campaign $campaign, CampaignRecipient $recipient): string
    {
        $url = route('track.open.legacy', [
            'uuid' => $recipient->uuid,
        ]);

        $pixel = '<img src="' . $url . '" alt="" width="1" height="1" style="display:none;" />';

        if (stripos($htmlContent, '</body>') !== false) {
            return str_ireplace('</body>', $pixel . '</body>', $htmlContent);
        }

        return $htmlContent . $pixel;
    }

    /**
     * Rewrite links in HTML content for click tracking.
     *
     * Same as above, we target the legacy click tracking route which
     * accepts the recipient UUID and encoded URL.
     */
    protected function rewriteClickTrackingLinks(string $htmlContent, Campaign $campaign, CampaignRecipient $recipient): string
    {
        $campaign->loadMissing('trackingDomain');
        $trackingHost = null;
        if ($campaign->trackingDomain && $campaign->trackingDomain->isVerified()) {
            $trackingHost = $campaign->trackingDomain->domain;
        }

        return preg_replace_callback(
            '/<a\s+[^>]*href="([^"]+)"([^>]*)>/i',
            static function (array $matches) use ($recipient, $trackingHost) {
                $originalUrl = $matches[1];

                // Skip if already a tracking URL
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
                $trackingUrl = route('track.click.legacy', [
                    'uuid' => $recipient->uuid,
                    'url' => $encodedUrl,
                ]);

                return str_replace($originalUrl, e($trackingUrl), $matches[0]);
            },
            $htmlContent
        ) ?? $htmlContent;
    }
}

