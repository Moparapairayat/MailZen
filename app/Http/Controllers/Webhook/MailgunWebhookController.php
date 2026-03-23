<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\CampaignLog;
use App\Models\CampaignRecipient;
use App\Models\ListSubscriber;
use App\Models\SuppressionList;
use App\Services\ComplaintService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MailgunWebhookController extends Controller
{
    public function __construct(
        protected ComplaintService $complaintService
    ) {}

    /**
     * Handle Mailgun webhook events.
     */
    public function handle(Request $request)
    {
        // Validate webhook signature
        if (!$this->validateSignature($request)) {
            Log::warning('Invalid Mailgun webhook signature', [
                'ip' => $request->ip(),
            ]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $eventData = $request->input('event-data');
        $event = $eventData['event'] ?? null;

        // Handle complaint events
        if ($event === 'complained') {
            return $this->handleComplaint($eventData);
        }

        // Handle bounce events (optional - can also use bounce server)
        if ($event === 'bounced' || $event === 'failed') {
            return $this->handleBounce($eventData);
        }

        if ($event === 'opened') {
            return $this->handleOpen($request, $eventData);
        }

        if ($event === 'clicked') {
            return $this->handleClick($request, $eventData);
        }

        return response()->json(['status' => 'ignored'], 200);
    }

    protected function handleOpen(Request $request, array $eventData): \Illuminate\Http\JsonResponse
    {
        $recipient = $this->resolveRecipientFromEvent($eventData);

        if (!$recipient) {
            return response()->json(['status' => 'ignored'], 200);
        }

        $campaign = $recipient->campaign;
        if ($campaign && $campaign->track_opens && !$recipient->isOpened()) {
            $recipient->markAsOpened();
            $campaign->incrementOpenedCount();

            CampaignLog::logEvent(
                $campaign->id,
                'opened',
                $recipient->id,
                ['email' => $recipient->email, 'provider' => 'mailgun'],
                $request->ip(),
                $request->userAgent()
            );
        }

        return response()->json(['status' => 'processed'], 200);
    }

    protected function handleClick(Request $request, array $eventData): \Illuminate\Http\JsonResponse
    {
        $recipient = $this->resolveRecipientFromEvent($eventData);

        if (!$recipient) {
            return response()->json(['status' => 'ignored'], 200);
        }

        $campaign = $recipient->campaign;
        $url = $eventData['url'] ?? data_get($eventData, 'url');

        if ($campaign && $campaign->track_clicks) {
            $wasOpened = $recipient->isOpened();
            $wasClicked = $recipient->isClicked();

            if ($campaign->track_opens && !$wasOpened) {
                $recipient->markAsOpened();
                $campaign->incrementOpenedCount();
            }

            if (!$wasClicked) {
                $recipient->markAsClicked();
                $campaign->incrementClickedCount();
            }

            CampaignLog::logEvent(
                $campaign->id,
                'clicked',
                $recipient->id,
                ['email' => $recipient->email, 'provider' => 'mailgun'],
                $request->ip(),
                $request->userAgent(),
                is_string($url) ? $url : null
            );
        }

        return response()->json(['status' => 'processed'], 200);
    }

    protected function resolveRecipientFromEvent(array $eventData): ?CampaignRecipient
    {
        $uuid = data_get($eventData, 'message.headers.X-Recipient-UUID')
            ?? data_get($eventData, 'message.headers.x-recipient-uuid');

        if (is_string($uuid) && trim($uuid) !== '') {
            $found = CampaignRecipient::where('uuid', trim($uuid))->first();
            if ($found) {
                return $found;
            }
        }

        $email = $eventData['recipient'] ?? null;
        if (!is_string($email) || trim($email) === '') {
            return null;
        }

        return CampaignRecipient::where('email', trim($email))
            ->latest('id')
            ->first();
    }

    /**
     * Handle spam complaint event.
     */
    protected function handleComplaint(array $eventData): \Illuminate\Http\JsonResponse
    {
        $recipient = $eventData['recipient'] ?? null;
        $messageId = $eventData['message']['headers']['message-id'] ?? null;

        if (!$recipient) {
            return response()->json(['error' => 'Missing recipient'], 400);
        }

        try {
            $this->complaintService->processComplaint(
                email: $recipient,
                provider: 'mailgun',
                providerMessageId: $messageId,
                feedbackId: $eventData['id'] ?? null,
                rawData: json_encode($eventData),
                meta: $eventData
            );

            return response()->json(['status' => 'processed'], 200);
        } catch (\Exception $e) {
            Log::error('Error processing Mailgun complaint: ' . $e->getMessage(), [
                'event_data' => $eventData,
            ]);

            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    /**
     * Handle bounce event (optional - can also use bounce server).
     */
    protected function handleBounce(array $eventData): \Illuminate\Http\JsonResponse
    {
        $recipientEmail = $eventData['recipient'] ?? null;
        if (!$recipientEmail) {
            return response()->json(['error' => 'Missing recipient'], 400);
        }

        $eventType = $eventData['event'] ?? 'bounced';
        $messageId = data_get($eventData, 'message.headers.message-id');
        $severity = $eventData['severity'] ?? null;
        $statusCode = data_get($eventData, 'delivery-status.code');
        $bounceType = data_get($eventData, 'delivery-status.bounce-type');
        $reason = data_get($eventData, 'delivery-status.description')
            ?? $eventData['reason']
            ?? 'Mailgun reported delivery failure';

        // Treat "permanent" severity, "hard" bounce-type, or 5xx codes as hard bounce
        $isHardBounce = $severity === 'permanent'
            || $bounceType === 'hard'
            || (is_numeric($statusCode) && (int) $statusCode >= 500);

        // Find the most recent recipient record for this email that was sent/pending
        $recipient = CampaignRecipient::where('email', $recipientEmail)
            ->whereIn('status', ['sent', 'pending'])
            ->latest('id')
            ->first();

        if (!$recipient) {
            Log::info('Mailgun bounce event received but no matching recipient found', [
                'email' => $recipientEmail,
                'message_id' => $messageId,
                'event' => $eventType,
            ]);
            return response()->json(['status' => 'ignored'], 200);
        }

        $campaign = $recipient->campaign;

        // Decide whether to mark as bounced or failed.
        // For Mailgun, delivery failures typically come as:
        // - event=bounced
        // - event=failed with bounce-type set or severity=permanent
        $shouldMarkAsBounce = $eventType === 'bounced'
            || ($eventType === 'failed' && (
                $bounceType !== null
                || $severity === 'permanent'
                || ($eventData['reason'] ?? '') === 'bounce'
            ));

        if ($shouldMarkAsBounce) {
            $alreadyBounced = $recipient->status === 'bounced';
            if (!$alreadyBounced) {
                $recipient->markAsBounced();
                if ($campaign) {
                    $campaign->incrementBouncedCount();
                }
                CampaignLog::logEvent(
                    $campaign?->id ?? 0,
                    'bounced',
                    $recipient->id,
                    [
                        'email' => $recipientEmail,
                        'provider' => 'mailgun',
                        'severity' => $severity,
                        'code' => $statusCode,
                        'reason' => $reason,
                    ],
                    null,
                    null,
                    null,
                    $reason
                );
            }
        } else {
            $alreadyFailed = $recipient->status === 'failed';
            if (!$alreadyFailed) {
                $recipient->markAsFailed($reason);
                if ($campaign) {
                    $campaign->incrementFailedCount();
                }
                CampaignLog::logEvent(
                    $campaign?->id ?? 0,
                    'failed',
                    $recipient->id,
                    [
                        'email' => $recipientEmail,
                        'provider' => 'mailgun',
                        'severity' => $severity,
                        'code' => $statusCode,
                        'reason' => $reason,
                    ],
                    null,
                    null,
                    null,
                    $reason
                );
            }
        }

        // Suppress subscriber on hard bounce
        $subscriber = ListSubscriber::where('email', $recipientEmail)->first();
        if ($subscriber && $isHardBounce) {
            $subscriber->update([
                'status' => 'bounced',
                'is_bounced' => true,
                'bounced_at' => now(),
                'suppressed_at' => now(),
            ]);

            SuppressionList::firstOrCreate(
                [
                    'customer_id' => $subscriber->list?->customer_id,
                    'email' => $subscriber->email,
                ],
                [
                    'reason' => 'bounce',
                    'reason_description' => $reason,
                    'subscriber_id' => $subscriber->id,
                    'campaign_id' => $campaign?->id,
                    'suppressed_at' => now(),
                ]
            );
        }

        Log::info('Mailgun bounce processed', [
            'email' => $recipientEmail,
            'event' => $eventType,
            'severity' => $severity,
            'code' => $statusCode,
            'reason' => $reason,
            'campaign_id' => $campaign?->id,
            'recipient_id' => $recipient->id,
        ]);

        return response()->json(['status' => 'logged'], 200);
    }

    /**
     * Validate Mailgun webhook signature.
     */
    protected function validateSignature(Request $request): bool
    {
        $signature = $request->input('signature');
        $token = $request->input('token');
        $timestamp = $request->input('timestamp');

        if (!$signature || !$token || !$timestamp) {
            return false;
        }

        $apiKey = config('services.mailgun.secret');
        if (!$apiKey) {
            return false;
        }

        // Validate timestamp (prevent replay attacks)
        if (abs(time() - $timestamp) > 15) {
            return false;
        }

        // Validate signature
        $hmac = hash_hmac('sha256', $timestamp . $token, $apiKey);

        return hash_equals($signature, $hmac);
    }
}
