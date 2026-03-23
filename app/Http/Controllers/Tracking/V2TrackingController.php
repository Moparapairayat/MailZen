<?php

namespace App\Http\Controllers\Tracking;

use App\Http\Controllers\Controller;
use App\Models\CampaignTracking;
use App\Models\SuppressionList;
use App\Services\AutoResponderTriggerService;
use App\Services\AutomationTriggerService;
use App\Services\Tracking\TrackingDomainResolver;
use App\Services\Tracking\TrackingHasher;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class V2TrackingController extends Controller
{
    public function __construct(
        private readonly TrackingDomainResolver $domainResolver,
        private readonly TrackingHasher $hasher,
    ) {
    }

    public function open(Request $request, string $campaignHash, string $subscriberHash): Response
    {
        $trackingDomain = $this->domainResolver->resolve($request);

        $campaign = $this->hasher->decodeCampaign($campaignHash);
        if (!$campaign) {
            abort(404);
        }

        $subscriber = $this->hasher->decodeSubscriber($subscriberHash, $campaign);
        if (!$subscriber) {
            abort(404);
        }

        // Suppression check
        if (SuppressionList::isSuppressed($subscriber->email, $campaign->customer_id)) {
            return $this->pixelResponse();
        }

        // Idempotent opens: check if already opened
        $existing = CampaignTracking::where('campaign_id', $campaign->id)
            ->where('subscriber_id', $subscriber->id)
            ->where('event_type', 'opened')
            ->first();

        if ($existing) {
            return $this->pixelResponse();
        }

        try {
            CampaignTracking::create([
                'campaign_id' => $campaign->id,
                'subscriber_id' => $subscriber->id,
                'email' => $subscriber->email,
                'event_type' => 'opened',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'event_at' => now(),
            ]);

            $campaign->incrementOpenedCount();

            try {
                app(AutoResponderTriggerService::class)->triggerSubscriberEvent('mail_opened', $subscriber);

                app(AutomationTriggerService::class)->triggerSubscriberEvent('campaign_opened', $subscriber, [
                    'campaign_id' => $campaign->id,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Failed to trigger autoresponder on mail_opened', [
                    'campaign_id' => $campaign->id,
                    'subscriber_id' => $subscriber->id,
                    'error' => $e->getMessage(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to log open event', [
                'campaign_id' => $campaign->id,
                'subscriber_id' => $subscriber->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $this->pixelResponse();
    }

    public function click(Request $request, string $campaignHash, string $subscriberHash)
    {
        $trackingDomain = $this->domainResolver->resolve($request);

        $campaign = $this->hasher->decodeCampaign($campaignHash);
        if (!$campaign) {
            abort(404);
        }

        $subscriber = $this->hasher->decodeSubscriber($subscriberHash, $campaign);
        if (!$subscriber) {
            abort(404);
        }

        $encodedUrl = $request->query('r');
        $originalUrl = $this->decodeRedirectUrl($encodedUrl);

        if (!$originalUrl) {
            $fallback = $campaign->settings['default_url'] ?? url('/');
            return redirect($fallback);
        }

        // Suppression check: redirect without logging
        if (SuppressionList::isSuppressed($subscriber->email, $campaign->customer_id)) {
            return redirect($originalUrl);
        }

        try {
            CampaignTracking::create([
                'campaign_id' => $campaign->id,
                'subscriber_id' => $subscriber->id,
                'email' => $subscriber->email,
                'event_type' => 'clicked',
                'url' => $originalUrl,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'event_at' => now(),
            ]);

            $campaign->incrementClickedCount();

            try {
                app(AutoResponderTriggerService::class)->triggerSubscriberEvent('mail_clicked', $subscriber, [
                    'url' => $originalUrl,
                    'campaign_id' => $campaign->id,
                ]);

                app(AutomationTriggerService::class)->triggerSubscriberEvent('campaign_clicked', $subscriber, [
                    'url' => $originalUrl,
                    'campaign_id' => $campaign->id,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Failed to trigger autoresponder on mail_clicked', [
                    'campaign_id' => $campaign->id,
                    'subscriber_id' => $subscriber->id,
                    'error' => $e->getMessage(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to log click event', [
                'campaign_id' => $campaign->id,
                'subscriber_id' => $subscriber->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect($originalUrl);
    }

    protected function decodeRedirectUrl(?string $encoded): ?string
    {
        if (!$encoded) {
            return null;
        }

        $decoded = base64_decode($encoded, true);
        if (!$decoded) {
            return null;
        }

        $decoded = filter_var($decoded, FILTER_VALIDATE_URL);
        if (!$decoded) {
            return null;
        }

        $scheme = parse_url($decoded, PHP_URL_SCHEME);
        if (!in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        return $decoded;
    }

    protected function pixelResponse(): Response
    {
        // 1x1 transparent PNG
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGMAAQAABQABDQottAAAAABJRU5ErkJggg=='
        );

        return response($png, 200)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}


