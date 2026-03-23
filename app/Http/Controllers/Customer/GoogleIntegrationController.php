<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\GoogleIntegration;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GoogleIntegrationController extends Controller
{
    private const SERVICE_SHEETS = 'sheets';
    private const SERVICE_DRIVE = 'drive';

    private function resolveService(string $service): string
    {
        $service = strtolower(trim($service));

        if (!in_array($service, [self::SERVICE_SHEETS, self::SERVICE_DRIVE], true)) {
            abort(404);
        }

        return $service;
    }

    private function hydrateGoogleConfigForIntegration(string $service): void
    {
        $clientId = config('services.google.client_id') ?: env('GOOGLE_CLIENT_ID');
        $clientSecret = config('services.google.client_secret') ?: env('GOOGLE_CLIENT_SECRET');

        $dbClientId = Setting::get('google_client_id');
        $dbClientSecret = Setting::get('google_client_secret');

        if (is_string($dbClientId)) {
            $dbClientId = trim($dbClientId);
        }
        if (is_string($dbClientSecret)) {
            $dbClientSecret = trim($dbClientSecret);
        }

        $redirectToUse = route('customer.integrations.google.callback', ['service' => $service]);

        $resolvedClientId = (is_string($dbClientId) && $dbClientId !== '') ? $dbClientId : $clientId;
        $resolvedClientSecret = (is_string($dbClientSecret) && $dbClientSecret !== '') ? $dbClientSecret : $clientSecret;

        config([
            'services.google.client_id' => $resolvedClientId,
            'services.google.client_secret' => $resolvedClientSecret,
            'services.google.redirect' => $redirectToUse,
        ]);
    }

    private function scopesForService(string $service): array
    {
        return match ($service) {
            self::SERVICE_SHEETS => [
                'https://www.googleapis.com/auth/spreadsheets',
            ],
            self::SERVICE_DRIVE => [
                'https://www.googleapis.com/auth/drive.file',
            ],
            default => [],
        };
    }

    public function connect(Request $request, string $service): RedirectResponse
    {
        $service = $this->resolveService($service);

        if (!class_exists('Laravel\\Socialite\\Facades\\Socialite')) {
            return redirect()
                ->route('customer.integrations.index', ['tab' => 'google'])
                ->with('error', 'Google integration is not available. Please install Laravel Socialite.');
        }

        $this->hydrateGoogleConfigForIntegration($service);

        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');
        $redirectUri = config('services.google.redirect');

        if (!$clientId || !$clientSecret || !$redirectUri) {
            return redirect()
                ->route('customer.integrations.index', ['tab' => 'google'])
                ->with('error', 'Google OAuth is not configured. Please set Google Client ID and Client Secret in Admin → Settings → Auth.');
        }

        $scopes = $this->scopesForService($service);

        return \Laravel\Socialite\Facades\Socialite::driver('google')
            ->scopes($scopes)
            ->with([
                'access_type' => 'offline',
                'prompt' => 'consent',
                'include_granted_scopes' => 'true',
            ])
            ->redirect();
    }

    public function callback(Request $request, string $service): RedirectResponse
    {
        $service = $this->resolveService($service);

        if (!class_exists('Laravel\\Socialite\\Facades\\Socialite')) {
            return redirect()
                ->route('customer.integrations.index', ['tab' => 'google'])
                ->with('error', 'Google integration is not available. Please install Laravel Socialite.');
        }

        $this->hydrateGoogleConfigForIntegration($service);

        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');
        $redirectUri = config('services.google.redirect');

        if (!$clientId || !$clientSecret || !$redirectUri) {
            return redirect()
                ->route('customer.integrations.index', ['tab' => 'google'])
                ->with('error', 'Google OAuth is not configured. Please set Google Client ID and Client Secret in Admin → Settings → Auth.');
        }

        try {
            $googleUser = \Laravel\Socialite\Facades\Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect()
                ->route('customer.integrations.index', ['tab' => 'google'])
                ->with('error', 'Google connection failed. Please try again.');
        }

        $customer = Auth::guard('customer')->user();
        if (!$customer) {
            return redirect()->route('login');
        }

        $expiresIn = (int) ($googleUser->expiresIn ?? 0);
        $expiresAt = $expiresIn > 0 ? now()->addSeconds($expiresIn) : null;

        GoogleIntegration::query()->updateOrCreate(
            [
                'customer_id' => $customer->id,
                'service' => $service,
            ],
            [
                'google_account_email' => $googleUser->getEmail(),
                'access_token' => (string) ($googleUser->token ?? ''),
                'refresh_token' => $googleUser->refreshToken ? (string) $googleUser->refreshToken : null,
                'expires_at' => $expiresAt,
                'scopes' => $this->scopesForService($service),
            ]
        );

        return redirect()
            ->route('customer.integrations.index', ['tab' => 'google'])
            ->with('success', 'Google account connected successfully.');
    }

    public function disconnect(Request $request, string $service): RedirectResponse
    {
        $service = $this->resolveService($service);

        $customer = Auth::guard('customer')->user();
        if (!$customer) {
            return redirect()->route('login');
        }

        GoogleIntegration::query()
            ->where('customer_id', $customer->id)
            ->where('service', $service)
            ->delete();

        return redirect()
            ->route('customer.integrations.index', ['tab' => 'google'])
            ->with('success', 'Google account disconnected.');
    }
}
