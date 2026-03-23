<?php

namespace App\Providers;

use App\Models\Customer;
use App\Models\CustomerLoginEvent;
use App\Services\IpGeolocationService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class LoginTrackingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(IpGeolocationService::class);
    }

    public function boot(): void
    {
        Event::listen(Login::class, function (Login $event) {
            // Only track customer logins for the world visitors map
            if ($event->guard !== 'customer') {
                return;
            }

            if (!($event->user instanceof Customer)) {
                return;
            }

            $request = request();
            $ip = (string) ($request?->ip() ?? '');
            $userAgent = (string) ($request?->userAgent() ?? '');

            if ($ip === '') {
                return;
            }

            $customer = $event->user;
            if (Schema::hasColumns('customers', ['last_login_ip', 'last_login_at'])) {
                $customer->forceFill([
                    'last_login_ip' => $ip,
                    'last_login_at' => now(),
                ])->saveQuietly();
            }

            $geo = app(IpGeolocationService::class)->lookup($ip);

            CustomerLoginEvent::create([
                'customer_id' => $customer->id,
                'ip_geolocation_id' => $geo?->id,
                'ip' => $ip,
                'user_agent' => $userAgent !== '' ? $userAgent : null,
                'logged_in_at' => now(),
            ]);
        });
    }
}
