<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Database\UniqueConstraintViolationException;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Setting;
use App\Services\AffiliateService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GoogleAuthController extends Controller
{
    private function hydrateGoogleConfigFromSettings(): void
    {
        $enabled = filter_var(Setting::get('google_enabled', false), FILTER_VALIDATE_BOOLEAN);

        if (!$enabled) {
            return;
        }

        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');
        $redirectUri = config('services.google.redirect');

        $dbClientId = Setting::get('google_client_id');
        $dbClientSecret = Setting::get('google_client_secret');
        $dbRedirectUri = Setting::get('google_redirect_uri');

        $expectedRedirectUri = route('customer.auth.google.callback');

        if (is_string($dbClientId)) {
            $dbClientId = trim($dbClientId);
        }
        if (is_string($dbClientSecret)) {
            $dbClientSecret = trim($dbClientSecret);
        }
        if (is_string($dbRedirectUri)) {
            $dbRedirectUri = trim($dbRedirectUri);
        }

        $redirectToUse = $expectedRedirectUri;
        if (is_string($dbRedirectUri) && $dbRedirectUri !== '' && $dbRedirectUri === $expectedRedirectUri) {
            $redirectToUse = $dbRedirectUri;
        }

        config([
            'services.google.client_id' => (is_string($dbClientId) && $dbClientId !== '') ? $dbClientId : $clientId,
            'services.google.client_secret' => (is_string($dbClientSecret) && $dbClientSecret !== '') ? $dbClientSecret : $clientSecret,
            'services.google.redirect' => $redirectToUse ?: $redirectUri,
        ]);
    }

    public function redirect(Request $request): RedirectResponse
    {
        if (!(bool) Setting::get('google_enabled', false)) {
            return redirect()
                ->route('login')
                ->with('error', 'Google authentication is disabled.');
        }

        if (!class_exists('Laravel\\Socialite\\Facades\\Socialite')) {
            return redirect()
                ->route('login')
                ->with('error', 'Google authentication is not available. Please install Laravel Socialite.');
        }

        $this->hydrateGoogleConfigFromSettings();

        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');
        $redirectUri = config('services.google.redirect');

        if (!$clientId || !$clientSecret || !$redirectUri) {
            return redirect()
                ->route('login')
                ->with('error', 'Google authentication is not configured.');
        }

        return \Laravel\Socialite\Facades\Socialite::driver('google')
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        if (!(bool) Setting::get('google_enabled', false)) {
            return redirect()
                ->route('login')
                ->with('error', 'Google authentication is disabled.');
        }

        if (!class_exists('Laravel\\Socialite\\Facades\\Socialite')) {
            return redirect()
                ->route('login')
                ->with('error', 'Google authentication is not available. Please install Laravel Socialite.');
        }

        $this->hydrateGoogleConfigFromSettings();

        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');
        $redirectUri = config('services.google.redirect');

        if (!$clientId || !$clientSecret || !$redirectUri) {
            return redirect()
                ->route('login')
                ->with('error', 'Google authentication is not configured.');
        }

        try {
            $googleUser = \Laravel\Socialite\Facades\Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect()
                ->route('login')
                ->with('error', 'Google authentication failed.');
        }

        $email = $googleUser->getEmail();
        $googleId = $googleUser->getId();
        $name = (string) ($googleUser->getName() ?? '');

        if (!$email || !$googleId) {
            return redirect()
                ->route('login')
                ->with('error', 'Google did not return required user information.');
        }

        [$firstName, $lastName] = array_pad(preg_split('/\s+/', trim($name), 2) ?: [], 2, null);

        $customer = Customer::withTrashed()
            ->where(function ($q) use ($googleId, $email) {
                $q->where('google_id', $googleId)
                    ->orWhere('email', $email);
            })
            ->first();

        $wasCreated = false;
        if (!$customer) {
            if (!(bool) Setting::get('registration_enabled', true)) {
                return redirect()
                    ->route('login')
                    ->with('error', __('Registration is disabled.'));
            }

            try {
                $customer = Customer::create([
                    'first_name' => $firstName ?: 'Customer',
                    'last_name' => $lastName ?: 'User',
                    'email' => $email,
                    'google_id' => $googleId,
                    'password' => Hash::make(str()->random(32)),
                    'status' => 'active',
                ]);

                app(AffiliateService::class)->attributeCustomerFromRequest($customer, $request);

                $wasCreated = true;
            } catch (UniqueConstraintViolationException $e) {
                $customer = Customer::withTrashed()->where('email', $email)->first();
                if (!$customer) {
                    throw $e;
                }
            }
        } else {
            if ($customer->google_id && $customer->google_id !== $googleId) {
                return redirect()
                    ->route('login')
                    ->with('error', 'This email is already linked to a different Google account. Please log in with your password.');
            }

            $customer->google_id = $customer->google_id ?: $googleId;
            if (!$customer->email) {
                $customer->email = $email;
            }
            if (!$customer->first_name && $firstName) {
                $customer->first_name = $firstName;
            }
            if (!$customer->last_name && $lastName) {
                $customer->last_name = $lastName;
            }
            $customer->save();
        }

        if ($customer->trashed()) {
            $customer->restore();
        }

        if ($wasCreated) {
            $newRegisteredGroupId = Setting::get('new_registered_customer_group_id');
            $fallbackGroupId = Setting::get('default_customer_group_id');
            $groupId = $newRegisteredGroupId ?: $fallbackGroupId;

            if ($groupId && CustomerGroup::query()->whereKey((int) $groupId)->exists()) {
                $customer->customerGroups()->syncWithoutDetaching([(int) $groupId]);
            }
        }

        if (method_exists($customer, 'markEmailAsVerified') && method_exists($customer, 'hasVerifiedEmail') && !$customer->hasVerifiedEmail()) {
            $customer->markEmailAsVerified();
        }

        Auth::shouldUse('customer');
        Auth::guard('customer')->login($customer, true);
        $request->session()->regenerate();

        return redirect()->intended(route('customer.dashboard'));
    }
}
