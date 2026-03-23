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

class SendGridWebhookController extends Controller
{
    public function __construct(
        protected ComplaintService $complaintService
    ) {}

    /**
     * Handle SendGrid webhook events.
     */
    public function handle(Request $request)
    {
        // SendGrid sends an array of events
        $events = $request->input();

        if (!is_array($events) || empty($events)) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        $processed = 0;

        foreach ($events as $event) {
            if (!is_array($event)) {
                continue;
            }

            $eventType = $event['event'] ?? null;

            // Handle spam report (complaint)
            if ($eventType === 'spamreport') {
                $this->handleComplaint($event);
                $processed++;
            }

            // Handle bounce (optional)
            if ($eventType === 'bounce' || $eventType === 'dropped') {
                $this->handleBounce($request, $event);
                $processed++;
            }

            if ($eventType === 'open') {
                $this->handleOpen($request, $event);
                $processed++;
            }

            if ($eventType === 'click') {
                $this->handleClick($request, $event);
                $processed++;
            }
        }

        return response()->json(['status' => 'processed', 'count' => $processed], 200);
    }

    /**
     * Handle spam complaint event.
     */
    protected function handleComplaint(array $event): void
    {
        $email = $event['email'] ?? null;
        $messageId = $event['sg_message_id'] ?? null;

        if (!$email) {
            Log::warning('SendGrid complaint event missing email', ['event' => $event]);
            return;
        }

        try {
            $this->complaintService->processComplaint(
                email: $email,
                provider: 'sendgrid',
                providerMessageId: $messageId,
                feedbackId: $event['sg_event_id'] ?? null,
                rawData: json_encode($event),
                meta: $event
            );
        } catch (\Exception $e) {
            Log::error('Error processing SendGrid complaint: ' . $e->getMessage(), [
                'event' => $event,
            ]);
        }
    }

    /**
     * Handle bounce event (optional).
     */
    protected function handleBounce(Request $request, array $event): void
    {
        $eventType = $event['event'] ?? null;
        $recipient = $this->resolveRecipientFromEvent($event);
        if (!$recipient) {
            return;
        }

        $campaign = $recipient->campaign;
        $email = (string) ($recipient->email ?? ($event['email'] ?? ''));

        $reason = (string) (
            $event['reason']
            ?? $event['response']
            ?? $event['smtp-id']
            ?? 'SendGrid reported delivery failure'
        );

        $shouldMarkAsBounce = $eventType === 'bounce';
        $isHardBounce = $shouldMarkAsBounce;

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
                        'email' => $email,
                        'provider' => 'sendgrid',
                        'reason' => $reason,
                    ],
                    $request->ip(),
                    $request->userAgent(),
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
                        'email' => $email,
                        'provider' => 'sendgrid',
                        'reason' => $reason,
                    ],
                    $request->ip(),
                    $request->userAgent(),
                    null,
                    $reason
                );
            }
        }

        $subscriber = null;
        if (is_string($email) && trim($email) !== '') {
            $subscriber = ListSubscriber::query()
                ->when($campaign?->list_id, fn ($q) => $q->where('list_id', $campaign->list_id))
                ->where('email', strtolower(trim($email)))
                ->first();
        }

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

        Log::info('SendGrid bounce processed', [
            'event' => $eventType,
            'email' => $email,
            'reason' => $reason,
            'campaign_id' => $campaign?->id,
            'recipient_id' => $recipient->id,
        ]);
    }

    protected function handleOpen(Request $request, array $event): void
    {
        $recipient = $this->resolveRecipientFromEvent($event);
        if (!$recipient) {
            return;
        }

        $campaign = $recipient->campaign;
        if ($campaign && $campaign->track_opens && !$recipient->isOpened()) {
            $recipient->markAsOpened();
            $campaign->incrementOpenedCount();

            CampaignLog::logEvent(
                $campaign->id,
                'opened',
                $recipient->id,
                ['email' => $recipient->email, 'provider' => 'sendgrid'],
                $request->ip(),
                $request->userAgent()
            );
        }
    }

    protected function handleClick(Request $request, array $event): void
    {
        $recipient = $this->resolveRecipientFromEvent($event);
        if (!$recipient) {
            return;
        }

        $campaign = $recipient->campaign;
        $url = $event['url'] ?? null;

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
                ['email' => $recipient->email, 'provider' => 'sendgrid'],
                $request->ip(),
                $request->userAgent(),
                is_string($url) ? $url : null
            );
        }
    }

    protected function resolveRecipientFromEvent(array $event): ?CampaignRecipient
    {
        $uuid = $event['X-Recipient-UUID'] ?? $event['x-recipient-uuid'] ?? $event['recipient_uuid'] ?? null;
        if (is_string($uuid) && trim($uuid) !== '') {
            $found = CampaignRecipient::where('uuid', trim($uuid))->first();
            if ($found) {
                return $found;
            }
        }

        $email = $event['email'] ?? null;
        if (!is_string($email) || trim($email) === '') {
            return null;
        }

        return CampaignRecipient::where('email', trim($email))
            ->latest('id')
            ->first();
    }
}
