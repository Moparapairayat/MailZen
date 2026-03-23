<?php

namespace App\Providers;

use App\Models\Setting;
use App\Services\Billing\PaymentProviderInterface;
use App\Services\Billing\FlutterwavePaymentService;
use App\Services\Billing\ManualPaymentService;
use App\Services\Billing\PaystackPaymentService;
use App\Services\Billing\PayPalPaymentService;
use App\Services\Billing\RazorpayPaymentService;
use App\Services\Billing\StripePaymentService;
use Illuminate\Support\ServiceProvider;
use Stripe\StripeClient;

class BillingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(StripeClient::class, function () {
            $secret = config('billing.stripe.secret');

            if ($this->isInstalled()) {
                try {
                    $billingProviders = Setting::get('billing_providers');
                    $stripeMode = is_array($billingProviders) ? (data_get($billingProviders, 'stripe.mode', 'live')) : 'live';
                    if (!in_array($stripeMode, ['live', 'sandbox'], true)) {
                        $stripeMode = 'live';
                    }

                    $candidate = is_array($billingProviders)
                        ? data_get($billingProviders, 'stripe.' . $stripeMode . '.secret')
                        : null;

                    if (!is_string($candidate) && !is_array($candidate)) {
                        $candidate = Setting::get('stripe_secret', $secret);
                    }

                    if (is_string($candidate)) {
                        $candidate = trim($candidate);
                    }

                    if (is_string($candidate) && $candidate !== '') {
                        $secret = $candidate;
                    }
                } catch (\Throwable $e) {
                    // Ignore DB/settings failures during early bootstrap.
                }
            }

            if (!is_string($secret) || trim($secret) === '') {
                $secret = 'sk_test_not_configured';
            }

            return new StripeClient($secret);
        });

        $this->app->bind(PaymentProviderInterface::class, function ($app) {
            if (!$this->isInstalled()) {
                return $this->unconfiguredProvider('Billing is not available until after installation is completed.');
            }

            $provider = config('billing.provider', 'stripe');
            $billingProviders = null;

            try {
                $legacyProvider = Setting::get('billing_provider', $provider);
                $defaultProvider = Setting::get('billing_default_provider', $legacyProvider);
                $billingProviders = Setting::get('billing_providers');

                if (!is_array($billingProviders)) {
                    $provider = $defaultProvider;
                } else {
                    $provider = $defaultProvider;
                    if (!(bool) data_get($billingProviders, $provider . '.enabled', false)) {
                        $provider = collect(['stripe', 'paypal', 'razorpay', 'paystack', 'flutterwave', 'manual'])
                            ->first(fn ($key) => (bool) data_get($billingProviders, $key . '.enabled', false))
                            ?? $legacyProvider;
                    }
                }
            } catch (\Throwable $e) {
                // Ignore DB/settings failures; use config fallback.
            }

            if ($provider === 'stripe') {
                $secret = config('billing.stripe.secret');
                try {
                    $candidate = Setting::get('stripe_secret', $secret);
                    if (is_string($candidate)) {
                        $candidate = trim($candidate);
                    }
                    if (is_string($candidate) && $candidate !== '') {
                        $secret = $candidate;
                    }
                } catch (\Throwable $e) {
                    // Ignore DB/settings failures.
                }

                if (!is_string($secret) || trim($secret) === '') {
                    return $this->unconfiguredProvider('Stripe is not configured. Set STRIPE_SECRET (or the "stripe_secret" setting) to use billing features.');
                }

                return new StripePaymentService($app->make(StripeClient::class));
            }

            return match ($provider) {
                'paypal' => new PayPalPaymentService(),
                'razorpay' => new RazorpayPaymentService(),
                'paystack' => new PaystackPaymentService(),
                'flutterwave' => new FlutterwavePaymentService(),
                'manual' => new ManualPaymentService(),
                default => $this->unconfiguredProvider('Billing provider is not configured.'),
            };
        });
    }

    private function isInstalled(): bool
    {
        return (bool) config('mailzen.skip_install_wizard', false)
            || is_file(storage_path('app/private/installed.json'));
    }

    private function unconfiguredProvider(string $message): PaymentProviderInterface
    {
        return new class($message) implements PaymentProviderInterface {
            public function __construct(private readonly string $message) {}

            public function createCheckoutSession(\App\Models\Customer $customer, \App\Models\Plan $plan, array $options = []): string
            {
                throw new \RuntimeException($this->message);
            }

            public function createCustomerPortal(\App\Models\Customer $customer, array $options = []): string
            {
                throw new \RuntimeException($this->message);
            }

            public function cancelAtPeriodEnd(\App\Models\Subscription $subscription): void
            {
                throw new \RuntimeException($this->message);
            }

            public function resume(\App\Models\Subscription $subscription): void
            {
                throw new \RuntimeException($this->message);
            }

            public function handleWebhook(\Illuminate\Http\Request $request): void
            {
                throw new \RuntimeException($this->message);
            }
        };
    }
}

