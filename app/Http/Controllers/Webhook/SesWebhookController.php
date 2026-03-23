<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\CampaignLog;
use App\Models\CampaignRecipient;
use App\Models\EmailProviderEvent;
use App\Models\ListSubscriber;
use App\Models\SuppressionList;
use App\Services\ComplaintService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SesWebhookController extends Controller
{
    public function __construct(
        protected ComplaintService $complaintService
    ) {}

    /**
     * Handle Amazon SES webhook (SNS notification).
     */
    public function handle(Request $request)
    {
        $envelope = $this->parseSnsEnvelope($request);
        if (!$envelope) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        $messageType = $request->header('x-amz-sns-message-type')
            ?: ($envelope['Type'] ?? null);

        if (!is_string($messageType) || $messageType === '') {
            return response()->json(['status' => 'ignored'], 200);
        }

        if (!$this->validateSnsSignature($envelope)) {
            Log::warning('Invalid SNS signature for SES webhook', [
                'type' => $messageType,
                'topic_arn' => $envelope['TopicArn'] ?? null,
                'message_id' => $envelope['MessageId'] ?? null,
            ]);

            return response()->json(['error' => 'Invalid signature'], 401);
        }

        if ($messageType === 'SubscriptionConfirmation' || $messageType === 'UnsubscribeConfirmation') {
            return $this->confirmSubscription($envelope);
        }

        if ($messageType === 'Notification') {
            return $this->handleNotification($request, $envelope);
        }

        return response()->json(['status' => 'ignored'], 200);
    }

    /**
     * Confirm SNS subscription.
     */
    protected function confirmSubscription(array $envelope): \Illuminate\Http\JsonResponse
    {
        $subscribeUrl = $envelope['SubscribeURL'] ?? null;
        $token = $envelope['Token'] ?? null;

        if (!is_string($subscribeUrl) || trim($subscribeUrl) === '') {
            return response()->json(['status' => 'ignored'], 200);
        }

        if (!$this->isAllowedSnsSubscribeUrl($subscribeUrl)) {
            Log::warning('SNS subscription confirmation has invalid SubscribeURL', [
                'url' => $subscribeUrl,
                'message_id' => $envelope['MessageId'] ?? null,
            ]);
            return response()->json(['error' => 'Invalid SubscribeURL'], 400);
        }

        try {
            $response = Http::timeout(10)->get($subscribeUrl);

            Log::info('SNS subscription confirmation requested', [
                'url' => $subscribeUrl,
                'token' => is_string($token) ? $token : null,
                'status' => $response->status(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed confirming SNS subscription', [
                'url' => $subscribeUrl,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Confirmation failed'], 500);
        }

        return response()->json(['status' => 'confirmed'], 200);
    }

    /**
     * Handle SNS notification.
     */
    protected function handleNotification(Request $request, array $envelope): \Illuminate\Http\JsonResponse
    {
        $snsMessage = $envelope['Message'] ?? null;
        if (!is_string($snsMessage) || trim($snsMessage) === '') {
            return response()->json(['error' => 'Invalid message'], 400);
        }

        $message = json_decode($snsMessage, true);
        if (!is_array($message)) {
            return response()->json(['error' => 'Invalid message'], 400);
        }

        $snsMessageId = is_string($envelope['MessageId'] ?? null) ? $envelope['MessageId'] : null;
        if ($snsMessageId) {
            $alreadyProcessed = EmailProviderEvent::query()
                ->where('provider', 'ses')
                ->where('sns_message_id', $snsMessageId)
                ->exists();

            if ($alreadyProcessed) {
                return response()->json(['status' => 'duplicate'], 200);
            }
        }

        // Support SES Event Publishing style payloads (eventType: Open/Click/etc.)
        $eventType = $message['eventType'] ?? null;
        if (is_string($eventType) && $eventType !== '') {
            $eventTypeLower = strtolower($eventType);

            if ($eventTypeLower === 'open') {
                $this->handleOpen($request, $message, $envelope);
                return response()->json(['status' => 'processed'], 200);
            }

            if ($eventTypeLower === 'click') {
                $this->handleClick($request, $message, $envelope);
                return response()->json(['status' => 'processed'], 200);
            }

            if ($eventTypeLower === 'delivery') {
                return $this->handleDelivery($request, $message, $envelope);
            }
        }

        $notificationType = $message['notificationType'] ?? null;

        // Handle complaint
        if ($notificationType === 'Complaint') {
            return $this->handleComplaint($message, $envelope);
        }

        // Handle bounce (optional)
        if ($notificationType === 'Bounce') {
            return $this->handleBounce($message, $envelope);
        }

        return response()->json(['status' => 'ignored'], 200);
    }

    /**
     * Handle complaint notification.
     */
    protected function handleComplaint(array $message, array $envelope): \Illuminate\Http\JsonResponse
    {
        $mail = $message['mail'] ?? [];
        $complaint = $message['complaint'] ?? [];

        $recipients = $complaint['complainedRecipients'] ?? [];
        
        if (empty($recipients)) {
            return response()->json(['error' => 'No recipients'], 400);
        }

        try {
            $resolvedRecipient = $this->resolveRecipientFromSnsMailPayload($message);
            $campaign = $resolvedRecipient?->campaign;

            foreach ($recipients as $recipient) {
                $email = $recipient['emailAddress'] ?? null;
                
                if (!$email) {
                    continue;
                }

                $complaintModel = $this->complaintService->processComplaint(
                    email: $email,
                    provider: 'ses',
                    providerMessageId: $mail['messageId'] ?? null,
                    feedbackId: $complaint['feedbackId'] ?? null,
                    rawData: json_encode($message),
                    meta: $message
                );

                if ($complaintModel->campaign_id === null && $resolvedRecipient && $campaign) {
                    $complaintModel->forceFill([
                        'campaign_id' => $campaign->id,
                        'subscriber_id' => $complaintModel->subscriber_id,
                    ])->save();

                    $campaign->increment('complained_count');
                }
            }

            if ($resolvedRecipient && $campaign) {
                CampaignLog::logEvent(
                    $campaign->id,
                    'complained',
                    $resolvedRecipient->id,
                    [
                        'email' => $resolvedRecipient->email,
                        'provider' => 'ses',
                        'feedback_id' => $complaint['feedbackId'] ?? null,
                    ]
                );
            }

            $this->persistProviderEvent(
                eventType: 'complaint',
                envelope: $envelope,
                message: $message,
                recipient: $resolvedRecipient
            );

            return response()->json(['status' => 'processed'], 200);
        } catch (\Exception $e) {
            Log::error('Error processing SES complaint: ' . $e->getMessage(), [
                'message' => $message,
            ]);

            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    /**
     * Handle bounce notification (optional).
     */
    protected function handleBounce(array $message, array $envelope): \Illuminate\Http\JsonResponse
    {
        $recipient = $this->resolveRecipientFromSnsBouncePayload($message);
        if (!$recipient) {
            Log::info('SES bounce event received but no matching recipient found');

            $this->persistProviderEvent(
                eventType: 'bounce',
                envelope: $envelope,
                message: $message,
                recipient: null
            );

            return response()->json(['status' => 'ignored'], 200);
        }

        $campaign = $recipient->campaign;
        $bounce = $message['bounce'] ?? [];

        $bounceType = strtolower((string) ($bounce['bounceType'] ?? ''));
        $isHardBounce = $bounceType === 'permanent' || $bounceType === 'undetermined';

        $bouncedRecipients = $bounce['bouncedRecipients'] ?? [];
        $reason = null;
        if (is_array($bouncedRecipients) && isset($bouncedRecipients[0]) && is_array($bouncedRecipients[0])) {
            $reason = $bouncedRecipients[0]['diagnosticCode'] ?? $bouncedRecipients[0]['status'] ?? null;
        }
        $reason = (string) ($reason ?? 'SES reported bounce');

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
                    'email' => $recipient->email,
                    'provider' => 'ses',
                    'bounce_type' => $bounceType,
                    'reason' => $reason,
                ],
                null,
                null,
                null,
                $reason
            );
        }

        $subscriber = null;
        $email = (string) ($recipient->email ?? '');
        if ($email !== '') {
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

        Log::info('SES bounce processed', [
            'email' => $email,
            'bounce_type' => $bounceType,
            'reason' => $reason,
            'campaign_id' => $campaign?->id,
            'recipient_id' => $recipient->id,
        ]);

        $this->persistProviderEvent(
            eventType: 'bounce',
            envelope: $envelope,
            message: $message,
            recipient: $recipient
        );

        return response()->json(['status' => 'processed'], 200);
    }

    protected function handleDelivery(Request $request, array $message, array $envelope): \Illuminate\Http\JsonResponse
    {
        $recipient = $this->resolveRecipientFromSnsMailPayload($message);

        $this->persistProviderEvent(
            eventType: 'delivery',
            envelope: $envelope,
            message: $message,
            recipient: $recipient
        );

        if (!$recipient) {
            return response()->json(['status' => 'ignored'], 200);
        }

        $campaign = $recipient->campaign;
        if (!$campaign) {
            return response()->json(['status' => 'processed'], 200);
        }

        $meta = is_array($recipient->meta) ? $recipient->meta : [];
        $alreadyDelivered = (bool) ($meta['ses_delivered'] ?? false);
        if ($alreadyDelivered) {
            return response()->json(['status' => 'processed'], 200);
        }

        $meta['ses_delivered'] = true;
        $meta['ses_delivered_at'] = data_get($message, 'delivery.timestamp')
            ?: data_get($message, 'mail.timestamp')
            ?: now()->toISOString();
        $meta['ses_message_id'] = data_get($message, 'mail.messageId');

        $recipient->update(['meta' => $meta]);
        if ((int) $campaign->delivered_count < (int) $campaign->sent_count) {
            $campaign->increment('delivered_count');
        }

        CampaignLog::logEvent(
            $campaign->id,
            'delivered',
            $recipient->id,
            [
                'email' => $recipient->email,
                'provider' => 'ses',
                'message_id' => data_get($message, 'mail.messageId'),
                'smtp_response' => data_get($message, 'delivery.smtpResponse'),
            ],
            $request->ip(),
            $request->userAgent()
        );

        return response()->json(['status' => 'processed'], 200);
    }

    protected function resolveRecipientFromSnsBouncePayload(array $message): ?CampaignRecipient
    {
        return $this->resolveRecipientFromSnsMailPayload($message);
    }

    protected function handleOpen(Request $request, array $message, array $envelope): void
    {
        $recipient = $this->resolveRecipientFromEventPublishingPayload($message);
        if (!$recipient) {
            $this->persistProviderEvent(
                eventType: 'open',
                envelope: $envelope,
                message: $message,
                recipient: null
            );
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
                ['email' => $recipient->email, 'provider' => 'ses'],
                $request->ip(),
                $request->userAgent()
            );
        }

        $this->persistProviderEvent(
            eventType: 'open',
            envelope: $envelope,
            message: $message,
            recipient: $recipient
        );
    }

    protected function handleClick(Request $request, array $message, array $envelope): void
    {
        $recipient = $this->resolveRecipientFromEventPublishingPayload($message);
        if (!$recipient) {
            $this->persistProviderEvent(
                eventType: 'click',
                envelope: $envelope,
                message: $message,
                recipient: null
            );
            return;
        }

        $campaign = $recipient->campaign;
        $url = data_get($message, 'click.link');

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
                ['email' => $recipient->email, 'provider' => 'ses'],
                $request->ip(),
                $request->userAgent(),
                is_string($url) ? $url : null
            );
        }

        $this->persistProviderEvent(
            eventType: 'click',
            envelope: $envelope,
            message: $message,
            recipient: $recipient
        );
    }

    protected function parseSnsEnvelope(Request $request): ?array
    {
        $content = (string) $request->getContent();
        if (trim($content) !== '') {
            $decoded = json_decode($content, true);
            if (is_array($decoded) && isset($decoded['Type'])) {
                return $decoded;
            }
        }

        $all = $request->all();
        if (is_array($all) && isset($all['Type'])) {
            return $all;
        }

        return null;
    }

    protected function validateSnsSignature(array $envelope): bool
    {
        $signatureVersion = $envelope['SignatureVersion'] ?? null;
        $signature = $envelope['Signature'] ?? null;
        $signingCertUrl = $envelope['SigningCertURL'] ?? null;

        if (!is_string($signatureVersion) || $signatureVersion !== '1') {
            return false;
        }

        if (!is_string($signature) || trim($signature) === '') {
            return false;
        }

        if (!is_string($signingCertUrl) || trim($signingCertUrl) === '') {
            return false;
        }

        if (!$this->isAllowedSnsCertUrl($signingCertUrl)) {
            return false;
        }

        $stringToSign = $this->buildSnsStringToSign($envelope);
        if ($stringToSign === '') {
            return false;
        }

        try {
            $certPem = Cache::remember('sns_cert:' . sha1($signingCertUrl), 3600, function () use ($signingCertUrl) {
                return Http::timeout(10)->get($signingCertUrl)->body();
            });

            if (!is_string($certPem) || trim($certPem) === '') {
                return false;
            }

            $publicKey = openssl_pkey_get_public($certPem);
            if ($publicKey === false) {
                return false;
            }

            $decodedSignature = base64_decode($signature, true);
            if ($decodedSignature === false) {
                return false;
            }

            $verified = openssl_verify($stringToSign, $decodedSignature, $publicKey, OPENSSL_ALGO_SHA1);
            if ($verified === 1) {
                return true;
            }

            $verifiedSha256 = openssl_verify($stringToSign, $decodedSignature, $publicKey, OPENSSL_ALGO_SHA256);
            return $verifiedSha256 === 1;
        } catch (\Throwable $e) {
            Log::warning('SNS signature verification failed', [
                'error' => $e->getMessage(),
                'message_id' => $envelope['MessageId'] ?? null,
            ]);
            return false;
        }
    }

    protected function buildSnsStringToSign(array $envelope): string
    {
        $type = $envelope['Type'] ?? null;
        if (!is_string($type) || $type === '') {
            return '';
        }

        $pairs = [];

        if ($type === 'Notification') {
            $pairs = [
                'Message',
                'MessageId',
                'Subject',
                'Timestamp',
                'TopicArn',
                'Type',
            ];
        } elseif ($type === 'SubscriptionConfirmation' || $type === 'UnsubscribeConfirmation') {
            $pairs = [
                'Message',
                'MessageId',
                'SubscribeURL',
                'Timestamp',
                'Token',
                'TopicArn',
                'Type',
            ];
        } else {
            return '';
        }

        $result = '';
        foreach ($pairs as $key) {
            if ($key === 'Subject' && !array_key_exists('Subject', $envelope)) {
                continue;
            }

            $value = $envelope[$key] ?? null;
            if (!is_string($value)) {
                continue;
            }

            $result .= $key . "\n" . $value . "\n";
        }

        return $result;
    }

    protected function isAllowedSnsCertUrl(string $url): bool
    {
        $parts = parse_url($url);
        if (!is_array($parts)) {
            return false;
        }

        if (($parts['scheme'] ?? null) !== 'https') {
            return false;
        }

        $host = $parts['host'] ?? '';
        $path = $parts['path'] ?? '';
        if (!is_string($host) || !is_string($path)) {
            return false;
        }

        if (!preg_match('/^sns\.[a-z0-9-]+\.amazonaws\.com$/i', $host)) {
            return false;
        }

        return str_ends_with(strtolower($path), '.pem');
    }

    protected function isAllowedSnsSubscribeUrl(string $url): bool
    {
        $parts = parse_url($url);
        if (!is_array($parts)) {
            return false;
        }

        if (($parts['scheme'] ?? null) !== 'https') {
            return false;
        }

        $host = $parts['host'] ?? '';
        return is_string($host) && preg_match('/\.amazonaws\.com$/i', $host) === 1;
    }

    protected function resolveRecipientFromSnsMailPayload(array $message): ?CampaignRecipient
    {
        $mail = $message['mail'] ?? [];

        $headers = $mail['headers'] ?? [];
        if (is_array($headers)) {
            foreach ($headers as $header) {
                if (!is_array($header)) {
                    continue;
                }
                $name = $header['name'] ?? null;
                $value = $header['value'] ?? null;
                if (is_string($name) && strtolower($name) === 'x-recipient-uuid' && is_string($value) && trim($value) !== '') {
                    $found = CampaignRecipient::where('uuid', trim($value))->first();
                    if ($found) {
                        return $found;
                    }
                }
            }
        }

        $destinations = $mail['destination'] ?? null;
        $email = null;
        if (is_array($destinations) && isset($destinations[0]) && is_string($destinations[0])) {
            $email = $destinations[0];
        }

        if (!is_string($email) || trim($email) === '') {
            return null;
        }

        return CampaignRecipient::where('email', trim($email))
            ->latest('id')
            ->first();
    }

    protected function persistProviderEvent(
        string $eventType,
        array $envelope,
        array $message,
        ?CampaignRecipient $recipient
    ): void {
        try {
            $snsMessageId = is_string($envelope['MessageId'] ?? null) ? $envelope['MessageId'] : null;
            $topicArn = is_string($envelope['TopicArn'] ?? null) ? $envelope['TopicArn'] : null;
            $sesMessageId = data_get($message, 'mail.messageId');
            $sesMessageId = is_string($sesMessageId) ? $sesMessageId : null;

            $email = null;
            $destinations = data_get($message, 'mail.destination');
            if (is_array($destinations) && isset($destinations[0]) && is_string($destinations[0])) {
                $email = $destinations[0];
            }
            if (!$email && $recipient) {
                $email = $recipient->email;
            }

            $subscriberId = null;
            if ($recipient && is_string($email) && trim($email) !== '') {
                $campaign = $recipient->campaign;
                if ($campaign?->list_id) {
                    $subscriberId = ListSubscriber::query()
                        ->where('list_id', $campaign->list_id)
                        ->where('email', strtolower(trim($email)))
                        ->value('id');
                }
            }

            $occurredAt = data_get($message, 'mail.timestamp')
                ?: data_get($message, 'delivery.timestamp')
                ?: data_get($message, 'bounce.timestamp')
                ?: data_get($message, 'complaint.timestamp');

            EmailProviderEvent::create([
                'provider' => 'ses',
                'event_type' => $eventType,
                'sns_message_id' => $snsMessageId,
                'ses_message_id' => $sesMessageId,
                'topic_arn' => $topicArn,
                'campaign_id' => $recipient?->campaign_id,
                'recipient_id' => $recipient?->id,
                'subscriber_id' => is_numeric($subscriberId) ? (int) $subscriberId : null,
                'email' => is_string($email) ? $email : null,
                'occurred_at' => is_string($occurredAt) ? $occurredAt : null,
                'payload' => [
                    'envelope' => $envelope,
                    'message' => $message,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed persisting SES provider event', [
                'event_type' => $eventType,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function resolveRecipientFromEventPublishingPayload(array $message): ?CampaignRecipient
    {
        $headers = data_get($message, 'mail.headers');
        $uuid = null;

        if (is_array($headers)) {
            foreach ($headers as $header) {
                $name = $header['name'] ?? null;
                if (!is_string($name)) {
                    continue;
                }
                if (strtolower($name) === 'x-recipient-uuid') {
                    $value = $header['value'] ?? null;
                    if (is_string($value) && trim($value) !== '') {
                        $uuid = trim($value);
                        break;
                    }
                }
            }
        }

        if (is_string($uuid) && $uuid !== '') {
            $found = CampaignRecipient::where('uuid', $uuid)->first();
            if ($found) {
                return $found;
            }
        }

        $destinations = data_get($message, 'mail.destination');
        $email = null;
        if (is_array($destinations) && isset($destinations[0]) && is_string($destinations[0])) {
            $email = $destinations[0];
        }

        if (!is_string($email) || trim($email) === '') {
            return null;
        }

        return CampaignRecipient::where('email', trim($email))
            ->latest('id')
            ->first();
    }
}
