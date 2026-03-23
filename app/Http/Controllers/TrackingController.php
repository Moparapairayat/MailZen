<?php

namespace App\Http\Controllers;

use App\Models\CampaignRecipient;
use App\Models\CampaignLog;
use App\Models\ListSubscriber;
use App\Services\AutoResponderTriggerService;
use App\Services\AutomationTriggerService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class TrackingController extends Controller
{
    /**
     * Track email open.
     */
    public function trackOpen(Request $request, string $uuid): Response
    {
        try {
            $recipient = CampaignRecipient::where('uuid', $uuid)->firstOrFail();
            $campaign = $recipient->campaign;
            $campaign->refresh();

            // Respect campaign open tracking setting
            if ($campaign->track_opens && !$recipient->isOpened()) {
                $recipient->markAsOpened();
                
                $campaign->incrementOpenedCount();

                Log::info('Tracked campaign open', [
                    'campaign_id' => $campaign->id,
                    'recipient_id' => $recipient->id,
                    'recipient_uuid' => $recipient->uuid,
                    'email' => $recipient->email,
                    'ip' => $request->ip(),
                ]);

                CampaignLog::logEvent(
                    $campaign->id,
                    'opened',
                    $recipient->id,
                    ['email' => $recipient->email],
                    $request->ip(),
                    $request->userAgent()
                );

                $subscriber = ListSubscriber::query()
                    ->where('list_id', $campaign->list_id)
                    ->where('email', strtolower(trim($recipient->email)))
                    ->first();

                if ($subscriber) {
                    try {
                        app(AutoResponderTriggerService::class)->triggerSubscriberEvent('mail_opened', $subscriber);

                        app(AutomationTriggerService::class)->triggerSubscriberEvent('campaign_opened', $subscriber, [
                            'campaign_id' => $campaign->id,
                            'recipient_id' => $recipient->id,
                        ]);
                    } catch (\Throwable $e) {
                        Log::warning('Failed to trigger autoresponder on mail_opened', [
                            'campaign_id' => $campaign->id,
                            'subscriber_id' => $subscriber->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            // Return 1x1 transparent pixel
            $pixel = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
            
            return response($pixel, 200)
                ->header('Content-Type', 'image/gif')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');

        } catch (\Exception $e) {
            Log::error("Failed to track open for UUID {$uuid}: " . $e->getMessage());
            
            // Still return pixel to avoid broken images
            $pixel = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
            return response($pixel, 200)->header('Content-Type', 'image/gif');
        }
    }

    /**
     * Track link click.
     */
    public function trackClick(Request $request, string $uuid, string $url): \Illuminate\Http\RedirectResponse
    {
        try {
            $recipient = CampaignRecipient::where('uuid', $uuid)->firstOrFail();
            $campaign = $recipient->campaign;
            $campaign->refresh();
            $originalUrl = $this->decodeRedirectUrl($url);

            if (!$originalUrl) {
                $fallback = $campaign->settings['default_url'] ?? url('/');
                return redirect($fallback);
            }

            // Respect campaign click tracking setting
            if ($campaign->track_clicks) {
                $wasOpened = $recipient->isOpened();
                $wasClicked = $recipient->isClicked();

                // If a user clicks, treat it as an open for stats (many clients block pixels)
                if ($campaign->track_opens && !$wasOpened) {
                    $recipient->markAsOpened();
                    $campaign->incrementOpenedCount();

                    CampaignLog::logEvent(
                        $campaign->id,
                        'opened',
                        $recipient->id,
                        ['email' => $recipient->email],
                        $request->ip(),
                        $request->userAgent()
                    );
                }

                // Only increment unique click count once per recipient
                if (!$wasClicked) {
                    $recipient->markAsClicked();
                    $campaign->incrementClickedCount();
                }

                Log::info('Tracked campaign click', [
                    'campaign_id' => $campaign->id,
                    'recipient_id' => $recipient->id,
                    'recipient_uuid' => $recipient->uuid,
                    'email' => $recipient->email,
                    'ip' => $request->ip(),
                    'url' => $originalUrl,
                ]);

                // Always log the click URL (so you can show serial clicks per recipient)
                CampaignLog::logEvent(
                    $campaign->id,
                    'clicked',
                    $recipient->id,
                    ['email' => $recipient->email],
                    $request->ip(),
                    $request->userAgent(),
                    $originalUrl
                );

                $subscriber = ListSubscriber::query()
                    ->where('list_id', $campaign->list_id)
                    ->where('email', strtolower(trim($recipient->email)))
                    ->first();

                if ($subscriber && !$wasClicked) {
                    try {
                        app(AutoResponderTriggerService::class)->triggerSubscriberEvent('mail_clicked', $subscriber, [
                            'url' => $originalUrl,
                            'campaign_id' => $campaign->id,
                        ]);

                        app(AutomationTriggerService::class)->triggerSubscriberEvent('campaign_clicked', $subscriber, [
                            'url' => $originalUrl,
                            'campaign_id' => $campaign->id,
                            'recipient_id' => $recipient->id,
                        ]);
                    } catch (\Throwable $e) {
                        Log::warning('Failed to trigger autoresponder on mail_clicked', [
                            'campaign_id' => $campaign->id,
                            'subscriber_id' => $subscriber->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            // Redirect to original URL
            return redirect($originalUrl);

        } catch (\Exception $e) {
            Log::error("Failed to track click for UUID {$uuid}: " . $e->getMessage());
            
            // Try to decode and redirect anyway
            try {
                $originalUrl = $this->decodeRedirectUrl($url);
                if (!$originalUrl) {
                    return redirect()->route('home')->with('error', 'Invalid tracking link');
                }
                return redirect($originalUrl);
            } catch (\Exception $decodeException) {
                return redirect()->route('home')->with('error', 'Invalid tracking link');
            }
        }
    }

    protected function decodeRedirectUrl(?string $encoded): ?string
    {
        if (!$encoded) {
            return null;
        }

        // Support both standard base64 and URL-safe base64
        $encoded = strtr($encoded, '-_', '+/');
        $encoded = str_pad($encoded, (int) ceil(strlen($encoded) / 4) * 4, '=', STR_PAD_RIGHT);

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
}

