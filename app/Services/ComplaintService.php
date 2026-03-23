<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\ListSubscriber;
use App\Models\SuppressionList;
use App\Models\Campaign;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ComplaintService
{
    /**
     * Process a spam complaint from webhook.
     */
    public function processComplaint(
        string $email,
        string $provider,
        ?string $providerMessageId = null,
        ?string $feedbackId = null,
        ?string $rawData = null,
        ?array $meta = null
    ): Complaint {
        return DB::transaction(function () use ($email, $provider, $providerMessageId, $feedbackId, $rawData, $meta) {
            // Check if complaint already exists (prevent duplicates)
            $existingComplaint = Complaint::where('email', $email)
                ->where('provider_message_id', $providerMessageId)
                ->first();

            if ($existingComplaint) {
                Log::info("Complaint already processed for email {$email} with message ID {$providerMessageId}");
                return $existingComplaint;
            }

            // Find subscriber
            $subscriber = ListSubscriber::where('email', $email)->first();
            
            // Try to find campaign from message ID
            $campaign = null;
            if ($providerMessageId) {
                $campaign = $this->findCampaignByMessageId($providerMessageId);
            }

            // Create complaint record
            $complaint = Complaint::create([
                'subscriber_id' => $subscriber?->id,
                'campaign_id' => $campaign?->id,
                'email' => $email,
                'source' => 'webhook',
                'provider' => $provider,
                'provider_message_id' => $providerMessageId,
                'feedback_id' => $feedbackId,
                'complained_at' => now(),
                'raw_data' => $rawData,
                'meta' => $meta,
            ]);

            // Mark subscriber as complained and suppress
            if ($subscriber) {
                $this->suppressSubscriber($subscriber, $complaint, $campaign);
            } else {
                // Add to suppression list even if subscriber not found
                $this->addToSuppressionList($email, 'complaint', "Spam complaint from {$provider}", null, $campaign?->id);
            }

            // Update campaign complaint count
            if ($campaign) {
                $campaign->increment('complained_count');
            }

            Log::info("Complaint processed for email {$email} from provider {$provider}");

            return $complaint;
        });
    }

    /**
     * Process complaint from email (ARF format).
     */
    public function processArfComplaint(string $rawEmail): ?Complaint
    {
        $parsed = $this->parseArfEmail($rawEmail);
        
        if (!$parsed || !isset($parsed['email'])) {
            Log::warning("Could not parse ARF complaint email");
            return null;
        }

        return $this->processComplaint(
            $parsed['email'],
            'email',
            $parsed['message_id'] ?? null,
            $parsed['feedback_id'] ?? null,
            $rawEmail,
            $parsed
        );
    }

    /**
     * Parse ARF (Abuse Report Format) email.
     */
    protected function parseArfEmail(string $rawEmail): ?array
    {
        // ARF emails typically have multiple parts
        // Extract email from Original-Recipient or Final-Recipient headers
        
        $email = null;
        $messageId = null;
        $feedbackId = null;

        // Extract email
        $patterns = [
            '/Original-Recipient:\s*rfc822;\s*([^\s]+)/i',
            '/Final-Recipient:\s*rfc822;\s*([^\s]+)/i',
            '/<([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})>/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $rawEmail, $matches)) {
                $email = trim($matches[1]);
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    break;
                }
            }
        }

        // Extract message ID
        if (preg_match('/Message-ID:\s*<([^>]+)>/i', $rawEmail, $matches)) {
            $messageId = $matches[1];
        }

        // Extract feedback ID
        if (preg_match('/Feedback-ID:\s*([^\s]+)/i', $rawEmail, $matches)) {
            $feedbackId = $matches[1];
        }

        if (!$email) {
            return null;
        }

        return [
            'email' => $email,
            'message_id' => $messageId,
            'feedback_id' => $feedbackId,
        ];
    }

    /**
     * Suppress subscriber due to complaint.
     */
    protected function suppressSubscriber(
        ListSubscriber $subscriber,
        Complaint $complaint,
        ?Campaign $campaign = null
    ): void {
        // Mark as complained and unsubscribed
        $subscriber->update([
            'status' => 'unsubscribed',
            'is_complained' => true,
            'suppressed_at' => now(),
            'unsubscribed_at' => now(),
        ]);

        // Add to suppression list
        $this->addToSuppressionList(
            $subscriber->email,
            'complaint',
            "Spam complaint from {$complaint->provider}",
            $subscriber->id,
            $campaign?->id
        );
    }

    /**
     * Add email to suppression list.
     */
    protected function addToSuppressionList(
        string $email,
        string $reason,
        string $reasonDescription,
        ?int $subscriberId = null,
        ?int $campaignId = null
    ): void {
        // Get customer ID from subscriber if available
        $customerId = null;
        if ($subscriberId) {
            $subscriber = ListSubscriber::find($subscriberId);
            $customerId = $subscriber?->list->customer_id;
        }

        SuppressionList::firstOrCreate(
            [
                'customer_id' => $customerId,
                'email' => $email,
            ],
            [
                'reason' => $reason,
                'reason_description' => $reasonDescription,
                'subscriber_id' => $subscriberId,
                'campaign_id' => $campaignId,
                'suppressed_at' => now(),
            ]
        );
    }

    /**
     * Find campaign by message ID.
     */
    protected function findCampaignByMessageId(string $messageId): ?Campaign
    {
        // Extract campaign ID from message ID if you embed it
        if (preg_match('/campaign-(\d+)/', $messageId, $matches)) {
            return Campaign::find($matches[1]);
        }

        return null;
    }
}

