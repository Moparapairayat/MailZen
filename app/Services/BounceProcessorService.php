<?php

namespace App\Services;

use App\Models\BouncedEmail;
use App\Models\BounceLog;
use App\Models\BounceServer;
use App\Models\Campaign;
use App\Models\CampaignLog;
use App\Models\CampaignRecipient;
use App\Models\DeliveryServer;
use App\Models\EmailList;
use App\Models\ListSubscriber;
use App\Models\SuppressionList;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BounceProcessorService
{
    /**
     * Process  bounces for a specific bounce server.
     */
    public function processBounces(BounceServer $bounceServer): int
    {
        if (!$bounceServer->isActive()) {
            Log::info("Bounce server {$bounceServer->id} is not active. Skipping.");
            return 0;
        }

        // Check if IMAP extension is available
        if (!function_exists('imap_open')) {
            Log::error("IMAP extension is not available. Please install php-imap extension.");
            throw new \Exception("IMAP extension is not available. Please install php-imap extension.");
        }

        try {
            $connection = $this->connectToServer($bounceServer);
            $processedCount = 0;

            // Get unread messages
            $messages = imap_search($connection, 'UNSEEN');
            
            if (!$messages) {
                imap_close($connection);
                return 0;
            }

            // Limit to max emails per batch
            $messages = array_slice($messages, 0, $bounceServer->max_emails_per_batch);

            foreach ($messages as $messageNumber) {
                try {
                    $rawMessage = imap_fetchbody($connection, $messageNumber, "");
                    $this->processBounceMessage($bounceServer, $rawMessage, $messageNumber, $connection);
                    $processedCount++;

                    // Mark as read
                    imap_setflag_full($connection, $messageNumber, "\\Seen");

                    // Delete if configured
                    if ($bounceServer->delete_after_processing) {
                        imap_delete($connection, $messageNumber);
                    }
                } catch (\Exception $e) {
                    Log::error("Error processing bounce message: " . $e->getMessage(), [
                        'bounce_server_id' => $bounceServer->id,
                        'message_number' => $messageNumber,
                    ]);
                }
            }

            // Expunge deleted messages
            if ($bounceServer->delete_after_processing) {
                imap_expunge($connection);
            }

            imap_close($connection);

            return $processedCount;
        } catch (\Exception $e) {
            Log::error("Error connecting to bounce server {$bounceServer->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Connect to IMAP/POP3 server using PHP's built-in IMAP functions.
     */
    protected function connectToServer(BounceServer $bounceServer)
    {
        $hostname = trim((string) ($bounceServer->hostname ?? ''));
        $port = $bounceServer->port;
        $encryption = $bounceServer->encryption;
        $mailbox = trim((string) ($bounceServer->mailbox ?? 'INBOX'));
        $protocol = strtolower((string) ($bounceServer->protocol ?? 'imap'));
        if (!in_array($protocol, ['imap', 'pop3'], true)) {
            $protocol = 'imap';
        }

        // Build connection string
        $connectionString = "{{$hostname}:{$port}";

        if ($protocol === 'pop3') {
            $connectionString .= '/pop3';
        } else {
            $connectionString .= '/imap';
        }
        
        if ($encryption === 'ssl') {
            $connectionString .= '/ssl';
        } elseif ($encryption === 'tls') {
            $connectionString .= '/tls';
        }

        // Add novalidate-cert option if SSL validation is disabled
        if ($encryption === 'ssl' || $encryption === 'tls') {
            $connectionString .= '/novalidate-cert';
        }
        
        $connectionString .= '}';
        $connectionString .= $mailbox;

        $connection = @imap_open($connectionString, trim((string) ($bounceServer->username ?? '')), (string) ($bounceServer->password ?? ''), NULL, 1);

        if (!$connection) {
            throw new \Exception("Failed to connect to bounce server: " . imap_last_error());
        }

        return $connection;
    }

    /**
     * Process a single bounce message.
     */
    protected function processBounceMessage(BounceServer $bounceServer, string $rawMessage, int $messageNumber, $connection): void
    {
        $parsedBounce = $this->parseBounceMessage($rawMessage);

        if (!$parsedBounce) {
            Log::warning("Could not parse bounce message", [
                'bounce_server_id' => $bounceServer->id,
                'message_number' => $messageNumber,
            ]);
            return;
        }

        // Extract campaign tracking headers from bounce email
        $campaignId = $this->extractHeader($rawMessage, 'X-Campaign-ID');
        $listId = $this->extractHeader($rawMessage, 'X-List-ID');
        $recipientUuid = $this->extractHeader($rawMessage, 'X-Recipient-UUID');

        if (!$recipientUuid) {
            $recipientUuid = $this->extractRecipientUuidFromRawMessage($rawMessage);
        }

        // Find campaign, list, and recipient using extracted headers
        $campaign = $campaignId ? Campaign::find($campaignId) : null;
        $emailList = $listId ? EmailList::find($listId) : null;
        $recipient = $recipientUuid ? CampaignRecipient::where('uuid', $recipientUuid)->first() : null;

        if ($recipient) {
            $recipient->loadMissing('campaign');
            if (!$campaign) {
                $campaign = $recipient->campaign;
            }
            if (!$emailList && $campaign?->list_id) {
                $emailList = EmailList::find($campaign->list_id);
            }
        }

        // Fallback: Try to find subscriber by email if recipient not found
        $subscriber = null;
        if ($recipient) {
            // Find subscriber from recipient email and list
            $subscriber = ListSubscriber::where('email', $recipient->email)
                ->when($emailList, function ($query) use ($emailList) {
                    return $query->where('list_id', $emailList->id);
                })
                ->first();
        } else {
            // Fallback to finding subscriber by email only
        $subscriber = ListSubscriber::where('email', $parsedBounce['email'])->first();
        
            // Try to find campaign by message ID if not found via header
            if (!$campaign && $parsedBounce['message_id']) {
            $campaign = $this->findCampaignByMessageId($parsedBounce['message_id']);
            }
        }

        DB::transaction(function () use ($bounceServer, $subscriber, $campaign, $emailList, $recipient, $parsedBounce, $rawMessage) {
            $deliveryServerId = null;
            if ($campaign && $campaign->delivery_server_id) {
                $deliveryServerId = $campaign->delivery_server_id;
            } else {
                $deliveryServerId = DeliveryServer::where('bounce_server_id', $bounceServer->id)
                    ->orderByDesc('id')
                    ->value('id');
            }

            // Create bounce log
            $bounceLog = BounceLog::create([
                'bounce_server_id' => $bounceServer->id,
                'subscriber_id' => $subscriber?->id,
                'campaign_id' => $campaign?->id,
                'list_id' => $emailList?->id,
                'recipient_id' => $recipient?->id,
                'email' => $parsedBounce['email'],
                'bounce_type' => $parsedBounce['type'],
                'bounce_code' => $parsedBounce['code'],
                'diagnostic_code' => $parsedBounce['diagnostic'],
                'reason' => $parsedBounce['reason'] ?? null,
                'raw_message' => $rawMessage,
                'message_id' => $parsedBounce['message_id'],
                'bounced_at' => now(),
            ]);

            BouncedEmail::updateOrCreate(
                [
                    'campaign_id' => $campaign?->id,
                    'list_id' => $emailList?->id,
                    'email' => $parsedBounce['email'],
                ],
                [
                    'bounce_server_id' => $bounceServer->id,
                    'delivery_server_id' => $deliveryServerId,
                    'subscriber_id' => $subscriber?->id,
                    'recipient_id' => $recipient?->id,
                    'bounce_server_username' => $bounceServer->username,
                    'bounce_server_mailbox' => $bounceServer->mailbox,
                    'bounce_type' => $parsedBounce['type'],
                    'bounce_code' => $parsedBounce['code'],
                    'diagnostic_code' => $parsedBounce['diagnostic'],
                    'reason' => $parsedBounce['reason'] ?? null,
                    'raw_message' => $rawMessage,
                    'last_bounced_at' => now(),
                    'meta' => [
                        'bounce_log_id' => $bounceLog->id,
                        'message_id' => $parsedBounce['message_id'],
                    ],
                ]
            );

            // Update campaign recipient status if found
            if ($recipient) {
                $recipient->markAsBounced();

                if ($campaign) {
                    CampaignLog::logEvent(
                        $campaign->id,
                        'bounced',
                        $recipient->id,
                        [
                            'email' => $parsedBounce['email'],
                            'bounce_type' => $parsedBounce['type'],
                            'bounce_code' => $parsedBounce['code'],
                            'reason' => $parsedBounce['reason'] ?? null,
                            'bounce_server_id' => $bounceServer->id,
                            'bounce_server_username' => $bounceServer->username,
                            'bounce_server_mailbox' => $bounceServer->mailbox,
                        ]
                    );
                }
            }

            // Handle bounce based on type
            if ($parsedBounce['type'] === 'hard') {
                $this->handleHardBounce($subscriber, $bounceLog, $campaign, $recipient);
            } else {
                $this->handleSoftBounce($subscriber, $bounceLog, $campaign, $recipient);
            }
        });
    }

    protected function extractRecipientUuidFromRawMessage(string $rawMessage): ?string
    {
        $patterns = [
            '/\/unsubscribe\/([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})/i',
            '/\/track\/open\/([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})/i',
            '/\/track\/click\/([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})/i',
            '/\b([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})\b/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $rawMessage, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Parse bounce message (DSN / RFC 3464).
     */
    protected function parseBounceMessage(string $rawMessage): ?array
    {
        // Extract email from bounce message
        $email = $this->extractEmailFromBounce($rawMessage);
        if (!$email) {
            return null;
        }

        // Determine bounce type
        $bounceType = $this->determineBounceType($rawMessage);
        
        // Extract bounce code (SMTP code)
        $bounceCode = $this->extractBounceCode($rawMessage);
        
        // Extract diagnostic code
        $diagnosticCode = $this->extractDiagnosticCode($rawMessage);
        
        // Extract original message ID
        $messageId = $this->extractMessageId($rawMessage);
        
        // Extract human-readable bounce reason
        $reason = $this->extractBounceReason($rawMessage, $diagnosticCode);

        return [
            'email' => $email,
            'type' => $bounceType,
            'code' => $bounceCode,
            'diagnostic' => $diagnosticCode,
            'reason' => $reason,
            'message_id' => $messageId,
        ];
    }

    /**
     * Extract email address from bounce message.
     */
    protected function extractEmailFromBounce(string $rawMessage): ?string
    {
        // Try to find email in various patterns
        $patterns = [
            '/Final-Recipient:\s*rfc822;\s*([^\s]+)/i',
            '/Original-Recipient:\s*rfc822;\s*([^\s]+)/i',
            '/To:\s*<([^>]+)>/i',
            '/<([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})>/',
            '/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $rawMessage, $matches)) {
                $email = trim($matches[1]);
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    return $email;
                }
            }
        }

        return null;
    }

    /**
     * Determine bounce type (hard or soft).
     */
    protected function determineBounceType(string $rawMessage): string
    {
        $hardBounceIndicators = [
            '550', '551', '552', '553', '554', // SMTP permanent failure codes
            'user unknown', 'mailbox not found', 'no such user',
            'invalid recipient', 'address rejected', 'domain not found',
            'permanent failure', 'permanent error',
        ];

        $message = strtolower($rawMessage);

        foreach ($hardBounceIndicators as $indicator) {
            if (strpos($message, strtolower($indicator)) !== false) {
                return 'hard';
            }
        }

        // Default to soft bounce
        return 'soft';
    }

    /**
     * Extract SMTP bounce code.
     */
    protected function extractBounceCode(string $rawMessage): ?string
    {
        if (preg_match('/\b(5\d{2}|4\d{2})\b/', $rawMessage, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Extract diagnostic code.
     */
    protected function extractDiagnosticCode(string $rawMessage): ?string
    {
        $patterns = [
            '/Diagnostic-Code:\s*(.+?)(?:\n|$)/i',
            '/Status:\s*(.+?)(?:\n|$)/i',
            '/Message:\s*(.+?)(?:\n|$)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $rawMessage, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    /**
     * Extract original message ID.
     */
    protected function extractMessageId(string $rawMessage): ?string
    {
        if (preg_match('/Message-ID:\s*<([^>]+)>/i', $rawMessage, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Extract human-readable bounce reason.
     */
    protected function extractBounceReason(string $rawMessage, ?string $diagnosticCode): ?string
    {
        // Use diagnostic code if available
        if ($diagnosticCode) {
            // Clean up diagnostic code - take first line or first 200 chars
            $reason = trim(explode("\n", $diagnosticCode)[0]);
            if (strlen($reason) > 200) {
                $reason = substr($reason, 0, 197) . '...';
            }
            if (!empty($reason)) {
                return $reason;
            }
        }

        // Try to extract from common bounce message patterns
        $patterns = [
            '/Status:\s*([^\r\n]+)/i',
            '/Action:\s*([^\r\n]+)/i',
            '/Diagnostic-Code:\s*([^\r\n]+)/i',
            '/Remote-MTA:\s*([^\r\n]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $rawMessage, $matches)) {
                $reason = trim($matches[1]);
                if (strlen($reason) > 200) {
                    $reason = substr($reason, 0, 197) . '...';
                }
                if (!empty($reason)) {
                    return $reason;
                }
            }
        }

        // Fallback to generic reason based on bounce type
        if (preg_match('/\b(550|551|552|553|554)\b/', $rawMessage)) {
            return 'Permanent failure - Mailbox unavailable';
        } elseif (preg_match('/\b(450|451|452)\b/', $rawMessage)) {
            return 'Temporary failure - Mailbox temporarily unavailable';
        }

        return 'Email bounced - Reason unknown';
    }

    /**
     * Extract custom header value from bounce message.
     */
    protected function extractHeader(string $rawMessage, string $headerName): ?string
    {
        // Look for header in the original message headers (usually in the bounce body)
        $patterns = [
            "/{$headerName}:\s*([^\r\n]+)/i",
            "/{$headerName}:\s*([^\r\n]+)/im",
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $rawMessage, $matches)) {
                $value = trim($matches[1]);
                return !empty($value) ? $value : null;
            }
        }

        return null;
    }

    /**
     * Find campaign by message ID.
     */
    protected function findCampaignByMessageId(string $messageId): ?Campaign
    {
        // Extract campaign ID from message ID if you embed it
        // Example: <campaign-123-uuid@domain.com>
        if (preg_match('/campaign-(\d+)/', $messageId, $matches)) {
            return Campaign::find($matches[1]);
        }

        return null;
    }

    /**
     * Handle hard bounce.
     */
    protected function handleHardBounce(?ListSubscriber $subscriber, BounceLog $bounceLog, ?Campaign $campaign = null, ?CampaignRecipient $recipient = null): void
    {
        if (!$subscriber) {
            return;
        }

        // Mark subscriber as bounced
        $subscriber->update([
            'status' => 'bounced',
            'is_bounced' => true,
            'bounced_at' => now(),
            'suppressed_at' => now(),
        ]);

        // Add to suppression list
        $this->addToSuppressionList($subscriber, 'bounce', $bounceLog->diagnostic_code, $bounceLog->campaign_id);

        // Update campaign bounce count
        if ($campaign) {
            $campaign->incrementBouncedCount();
        } elseif ($bounceLog->campaign_id) {
            Campaign::where('id', $bounceLog->campaign_id)->increment('bounced_count');
        }

        Log::info("Hard bounce processed for subscriber {$subscriber->email}", [
            'campaign_id' => $campaign?->id ?? $bounceLog->campaign_id,
            'list_id' => $bounceLog->list_id,
            'recipient_id' => $recipient?->id,
        ]);
    }

    /**
     * Handle soft bounce.
     */
    protected function handleSoftBounce(?ListSubscriber $subscriber, BounceLog $bounceLog, ?Campaign $campaign = null, ?CampaignRecipient $recipient = null): void
    {
        if (!$subscriber) {
            return;
        }

        $softBounceThreshold = 3; // Configurable threshold

        // Increment soft bounce count
        $subscriber->increment('soft_bounce_count');
        $subscriber->refresh();

        // If threshold exceeded, treat as hard bounce
        if ($subscriber->soft_bounce_count >= $softBounceThreshold) {
            $this->handleHardBounce($subscriber, $bounceLog, $campaign, $recipient);
            Log::info("Soft bounce threshold exceeded for subscriber {$subscriber->email}. Marking as hard bounce.");
        } else {
            // Update campaign bounce count for soft bounces too
            if ($campaign) {
                $campaign->incrementBouncedCount();
            } elseif ($bounceLog->campaign_id) {
                Campaign::where('id', $bounceLog->campaign_id)->increment('bounced_count');
            }
            
            Log::info("Soft bounce recorded for subscriber {$subscriber->email} (count: {$subscriber->soft_bounce_count})", [
                'campaign_id' => $campaign?->id ?? $bounceLog->campaign_id,
                'list_id' => $bounceLog->list_id,
                'recipient_id' => $recipient?->id,
            ]);
        }
    }

    /**
     * Add email to suppression list.
     */
    protected function addToSuppressionList(
        ListSubscriber $subscriber,
        string $reason,
        ?string $reasonDescription = null,
        ?int $campaignId = null
    ): void {
        SuppressionList::firstOrCreate(
            [
                'customer_id' => $subscriber->list->customer_id,
                'email' => $subscriber->email,
            ],
            [
                'reason' => $reason,
                'reason_description' => $reasonDescription,
                'subscriber_id' => $subscriber->id,
                'campaign_id' => $campaignId,
                'suppressed_at' => now(),
            ]
        );
    }
}

