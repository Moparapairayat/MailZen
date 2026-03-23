<?php

namespace App\Providers;

use App\Models\Setting;
use App\Models\Subscription;
use App\Policies\SubscriptionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Subscription::class => SubscriptionPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Blade::if('admincan', function (string $ability): bool {
            $user = auth('admin')->user();
            if (!$user) {
                return false;
            }

            if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                return true;
            }

            return method_exists($user, 'hasAdminAbility')
                ? (bool) $user->hasAdminAbility($ability)
                : false;
        });

        Blade::if('customercan', function (string $ability): bool {
            $customer = auth('customer')->user();
            if (!$customer) {
                return false;
            }

            return method_exists($customer, 'groupAllows')
                ? (bool) $customer->groupAllows($ability)
                : false;
        });

        try {
            $enabled = Setting::get('google_enabled', false);
            $enabled = filter_var($enabled, FILTER_VALIDATE_BOOLEAN);

            if (!$enabled) {
                config([
                    'services.google.client_id' => null,
                    'services.google.client_secret' => null,
                    'services.google.redirect' => null,
                ]);
                return;
            }

            $clientId = Setting::get('google_client_id', config('services.google.client_id'));
            $clientSecret = Setting::get('google_client_secret', config('services.google.client_secret'));
            $redirectUri = Setting::get('google_redirect_uri', config('services.google.redirect'));

            if (is_string($clientId)) {
                $clientId = trim($clientId);
            }
            if (is_string($clientSecret)) {
                $clientSecret = trim($clientSecret);
            }
            if (is_string($redirectUri)) {
                $redirectUri = trim($redirectUri);
            }

            config([
                'services.google.client_id' => (is_string($clientId) && $clientId !== '') ? $clientId : config('services.google.client_id'),
                'services.google.client_secret' => (is_string($clientSecret) && $clientSecret !== '') ? $clientSecret : config('services.google.client_secret'),
                'services.google.redirect' => (is_string($redirectUri) && $redirectUri !== '') ? $redirectUri : config('services.google.redirect'),
            ]);
        } catch (\Throwable $e) {
            // Ignore settings failures during early bootstrap.
        }
    }
}

