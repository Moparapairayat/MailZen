<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Coupon;
use App\Models\ListSubscriber;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\WebhookEvent;
use App\Models\Setting;
use App\Services\Billing\FlutterwavePaymentService;
use App\Services\Billing\ManualPaymentService;
use App\Services\Billing\PayPalPaymentService;
use App\Services\Billing\PaymentProviderInterface;
use App\Services\Billing\RazorpayPaymentService;
use App\Services\Billing\StripePaymentService;
use App\Services\Billing\UsageService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Stripe\StripeClient;

class BillingController extends Controller
{
    public function __construct(
        private readonly UsageService $usageService,
    ) {
    }

    public function index(Request $request)
    {
        $customer = $request->user('customer');
        $subscription = $customer->subscriptions()
            ->whereIn('status', ['active', 'trialing'])
            ->latest()
            ->first();

        if (!$subscription) {
            $subscription = $customer->subscriptions()
                ->where('status', 'pending')
                ->latest()
                ->first();
        }

        if (!$subscription) {
            $subscription = $customer->subscriptions()->latest()->first();
        }

        if ($subscription && $subscription->status === 'pending' && $customer->stripe_customer_id) {
            try {
                $stripe = app(StripeClient::class);

                $stripeSubscriptionId = null;

                if ($subscription->stripe_checkout_session_id) {
                    $session = $stripe->checkout->sessions->retrieve($subscription->stripe_checkout_session_id, [
                        'expand' => ['subscription'],
                    ]);

                    if (isset($session->subscription) && is_string($session->subscription)) {
                        $stripeSubscriptionId = $session->subscription;
                    } elseif (isset($session->subscription->id) && is_string($session->subscription->id)) {
                        $stripeSubscriptionId = $session->subscription->id;
                    }
                }

                if (!$stripeSubscriptionId && $subscription->stripe_subscription_id) {
                    $stripeSubscriptionId = $subscription->stripe_subscription_id;
                }

                if ($stripeSubscriptionId) {
                    $stripeSubscription = $stripe->subscriptions->retrieve($stripeSubscriptionId, []);

                    $status = match ((string) ($stripeSubscription->status ?? '')) {
                        'trialing' => 'trialing',
                        'active' => 'active',
                        'past_due' => 'past_due',
                        'canceled' => 'cancelled',
                        'unpaid' => 'suspended',
                        default => 'pending',
                    };

                    $stripePriceId = $stripeSubscription->items->data[0]->price->id ?? null;

                    $planDbId = $subscription->plan_db_id;
                    if (isset($stripeSubscription->metadata->plan_id) && is_numeric($stripeSubscription->metadata->plan_id)) {
                        $planDbId = (int) $stripeSubscription->metadata->plan_id;
                    }
                    if (!$planDbId && $stripePriceId) {
                        $planDbId = Plan::query()->where('stripe_price_id', $stripePriceId)->value('id');
                    }

                    $subscription->update([
                        'status' => $status,
                        'stripe_subscription_id' => $stripeSubscriptionId,
                        'stripe_price_id' => $stripePriceId ?? $subscription->stripe_price_id,
                        'plan_id' => $stripePriceId ?? $subscription->plan_id,
                        'plan_db_id' => $planDbId ?: $subscription->plan_db_id,
                        'period_start' => isset($stripeSubscription->current_period_start) ? \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_start) : $subscription->period_start,
                        'period_end' => isset($stripeSubscription->current_period_end) ? \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end) : $subscription->period_end,
                        'trial_ends_at' => isset($stripeSubscription->trial_end) ? \Carbon\Carbon::createFromTimestamp($stripeSubscription->trial_end) : $subscription->trial_ends_at,
                        'cancel_at_period_end' => (bool) ($stripeSubscription->cancel_at_period_end ?? $subscription->cancel_at_period_end),
                        'auto_renew' => !((bool) ($stripeSubscription->cancel_at_period_end ?? $subscription->cancel_at_period_end)),
                        'last_payment_status' => (string) ($stripeSubscription->status ?? $subscription->last_payment_status),
                    ]);
                }
            } catch (\Throwable $e) {
                // Ignore Stripe refresh errors and fall back to local status
            }
        }
        $plans = Plan::with('customerGroup')->where('is_active', true)->orderBy('price')->get();
        $usage = $this->usageService->getUsage($customer);

        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth()->endOfDay();

        $usage['emails_sent_this_month'] = CampaignRecipient::query()
            ->whereNotNull('sent_at')
            ->whereBetween('sent_at', [$monthStart, $monthEnd])
            ->whereIn('status', ['sent', 'opened', 'clicked'])
            ->whereHas('campaign', function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            })
            ->count();

        $usage['subscribers_count'] = ListSubscriber::query()
            ->whereHas('list', function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            })
            ->where('status', 'confirmed')
            ->count();

        $usage['campaigns_count'] = Campaign::query()
            ->where('customer_id', $customer->id)
            ->count();

        $customerName = $customer->full_name ?? trim(($customer->first_name ?? '') . ' ' . ($customer->last_name ?? ''));

        $currentPlan = null;
        if ($subscription?->plan_db_id) {
            $currentPlan = Plan::with('customerGroup')->find($subscription->plan_db_id);
        }

        $portalUrl = null;
        try {
            $paymentProvider = app(PaymentProviderInterface::class);
            $portalUrl = $paymentProvider->createCustomerPortal($customer, [
                'return_url' => route('customer.billing.index'),
            ]);
        } catch (\Throwable $e) {
            $portalUrl = null;
        }

        $invoiceEvents = collect();
        if ($customer->stripe_customer_id) {
            $invoiceEvents = WebhookEvent::query()
                ->where('provider', 'stripe')
                ->where('type', 'like', 'invoice.%')
                ->where('payload->customer', $customer->stripe_customer_id)
                ->orderByDesc('processed_at')
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();
        }

        $stripeInvoices = collect();
        if ($customer->stripe_customer_id && $invoiceEvents->count() === 0) {
            try {
                $stripe = app(StripeClient::class);
                $invoices = $stripe->invoices->all([
                    'customer' => $customer->stripe_customer_id,
                    'limit' => 10,
                ]);

                $stripeInvoices = collect($invoices->data ?? []);
            } catch (\Throwable $e) {
                $stripeInvoices = collect();
            }
        }

        $latestInvoiceUrl = null;
        if ($invoiceEvents->count() > 0) {
            $latestInvoicePayload = $invoiceEvents->first()?->payload;
            $latestInvoiceUrl = data_get($latestInvoicePayload, 'hosted_invoice_url')
                ?: data_get($latestInvoicePayload, 'invoice_pdf');
        } elseif ($stripeInvoices->count() > 0) {
            $latestInvoice = $stripeInvoices->first();
            $latestInvoiceUrl = data_get($latestInvoice, 'hosted_invoice_url')
                ?: data_get($latestInvoice, 'invoice_pdf');
        }

        $paymentMethodCard = null;
        if ($customer->stripe_customer_id) {
            try {
                $stripe = app(StripeClient::class);
                $stripeCustomer = $stripe->customers->retrieve($customer->stripe_customer_id, []);
                $defaultPaymentMethodId = data_get($stripeCustomer, 'invoice_settings.default_payment_method');

                $paymentMethod = null;
                if (is_string($defaultPaymentMethodId) && trim($defaultPaymentMethodId) !== '') {
                    $paymentMethod = $stripe->paymentMethods->retrieve($defaultPaymentMethodId, []);
                } else {
                    $paymentMethods = $stripe->paymentMethods->all([
                        'customer' => $customer->stripe_customer_id,
                        'type' => 'card',
                        'limit' => 1,
                    ]);
                    $paymentMethod = $paymentMethods->data[0] ?? null;
                }

                if ($paymentMethod && isset($paymentMethod->card)) {
                    $paymentMethodCard = [
                        'brand' => (string) ($paymentMethod->card->brand ?? ''),
                        'last4' => (string) ($paymentMethod->card->last4 ?? ''),
                        'exp_month' => (int) ($paymentMethod->card->exp_month ?? 0),
                        'exp_year' => (int) ($paymentMethod->card->exp_year ?? 0),
                    ];
                }
            } catch (\Throwable $e) {
                $paymentMethodCard = null;
            }
        }

        return view('customer.billing.index', compact('subscription', 'plans', 'usage', 'currentPlan', 'portalUrl', 'invoiceEvents', 'stripeInvoices', 'latestInvoiceUrl', 'paymentMethodCard', 'customerName'));
    }

    public function showCheckout(Request $request, Plan $plan)
    {
        $customer = $request->user('customer');
        $plan->load('customerGroup');

        $enabled = $this->enabledProviderKeys();

        $legacyProvider = 'stripe';
        $defaultProvider = 'stripe';
        try {
            $legacyProvider = Setting::get('billing_provider', config('billing.provider', 'stripe'));
            $defaultProvider = Setting::get('billing_default_provider', $legacyProvider);
        } catch (\Throwable $e) {
            // Ignore DB/settings failures; use fallback.
        }

        if (!is_string($defaultProvider) || $defaultProvider === '' || !in_array($defaultProvider, $enabled, true)) {
            $defaultProvider = (string) ($enabled[0] ?? 'stripe');
        }

        $selectedProvider = $request->query('provider');
        if (is_string($selectedProvider)) {
            $selectedProvider = trim($selectedProvider);
        }

        if (!is_string($selectedProvider) || $selectedProvider === '' || !in_array($selectedProvider, $enabled, true)) {
            $selectedProvider = $defaultProvider;
        }

        $couponCode = $request->query('coupon_code');
        if (is_string($couponCode)) {
            $couponCode = trim($couponCode);
        }

        return view('customer.billing.checkout', [
            'customer' => $customer,
            'plan' => $plan,
            'enabledProviders' => $enabled,
            'defaultProvider' => $defaultProvider,
            'selectedProvider' => $selectedProvider,
            'couponCode' => is_string($couponCode) ? $couponCode : '',
        ]);
    }

    public function checkout(Request $request, Plan $plan)
    {
        $customer = $request->user('customer');
        $plan->load('customerGroup');

        $requestedProvider = $request->input('provider');
        if (is_string($requestedProvider)) {
            $requestedProvider = trim($requestedProvider);
        }

        $providerKey = is_string($requestedProvider) && $requestedProvider !== ''
            ? $requestedProvider
            : $this->resolveBillingProviderKey();

        $enabledProviderKeys = $this->enabledProviderKeys();
        if (!in_array($providerKey, $enabledProviderKeys, true)) {
            return back()->with('error', 'Selected payment method is not available.');
        }

        $redirectUrl = match ($providerKey) {
            'flutterwave' => route('billing.flutterwave.callback'),
            'razorpay' => route('billing.razorpay.callback'),
            'paypal' => route('billing.paypal.callback'),
            default => null,
        };

        $validated = $request->validate([
            'coupon_code' => ['nullable', 'string', 'max:64'],
        ]);

        $promotionCodeId = null;
        $couponCode = trim((string) ($validated['coupon_code'] ?? ''));
        if ($couponCode !== '') {
            $coupon = Coupon::where('code', strtoupper($couponCode))->first();
            if (!$coupon || !$coupon->isUsable()) {
                return back()->with('error', 'Invalid or expired coupon code.');
            }

            if ($providerKey !== 'stripe') {
                return back()->with('error', 'Coupon codes are only supported for Stripe checkout.');
            }

            if (!$coupon->stripe_promotion_code_id) {
                return back()->with('error', 'Coupon is not available for checkout.');
            }

            $promotionCodeId = $coupon->stripe_promotion_code_id;
        }

        $limits = [
            'emails_sent_this_month' => $plan->customerGroup?->limit('sending_quota.monthly_quota', 0),
            'subscribers_count' => $plan->customerGroup?->limit('lists.limits.max_subscribers', 0),
            'campaigns_count' => $plan->customerGroup?->limit('campaigns.limits.max_campaigns', 0),
        ];

        $subscription = Subscription::create([
            'customer_id' => $customer->id,
            'plan_id' => $providerKey === 'stripe' ? ($plan->stripe_price_id ?? (string) $plan->id) : (string) $plan->id,
            'plan_db_id' => $plan->id,
            'plan_name' => $plan->name,
            'status' => 'pending',
            'billing_cycle' => $plan->billing_cycle,
            'price' => $plan->price,
            'currency' => $plan->currency,
            'starts_at' => now(),
            'trial_ends_at' => $plan->trial_days ? now()->addDays($plan->trial_days) : null,
            'limits' => array_filter($limits, fn ($v) => $v !== null),
            'features' => $plan->customerGroup?->permissions,
            'payment_gateway' => $providerKey === 'stripe' ? 'stripe_checkout' : $providerKey,
            'provider' => $providerKey,
            'stripe_price_id' => $providerKey === 'stripe' ? $plan->stripe_price_id : null,
        ]);

        $successUrl = route('customer.billing.success', ['provider' => $providerKey]);
        if ($providerKey === 'stripe') {
            $successUrl .= (str_contains($successUrl, '?') ? '&' : '?') . 'session_id={CHECKOUT_SESSION_ID}';
        }

        $cancelUrl = route('customer.billing.checkout.show', ['plan' => $plan, 'provider' => $providerKey]);

        try {
            $paymentProvider = $this->resolvePaymentProvider($providerKey);
            $url = $paymentProvider->createCheckoutSession($customer, $plan, [
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'redirect_url' => $redirectUrl,
                'promotion_code' => $promotionCodeId,
                'local_subscription_id' => $subscription->id,
            ]);
        } catch (\Throwable $e) {
            $subscription->delete();
            return back()->with('error', $e->getMessage());
        }

        if ($providerKey === 'manual') {
            return redirect()->to($url);
        }

        return redirect()->away($url);
    }

    public function success(Request $request)
    {
        $customer = $request->user('customer');

        $provider = $request->query('provider');
        $provider = is_string($provider) ? trim($provider) : '';

        $sessionId = $request->query('session_id');
        $sessionId = is_string($sessionId) ? trim($sessionId) : '';

        $orderId = $request->query('order_id');
        $orderId = is_string($orderId) ? trim($orderId) : '';

        $subscription = null;

        if ($sessionId !== '') {
            $subscription = $customer->subscriptions()
                ->where('stripe_checkout_session_id', $sessionId)
                ->latest()
                ->first();
        }

        if (!$subscription && $orderId !== '') {
            $subscription = $customer->subscriptions()
                ->where('provider', 'paypal')
                ->where('stripe_checkout_session_id', $orderId)
                ->latest()
                ->first();
        }

        if (!$subscription) {
            $subscription = $customer->subscriptions()
                ->whereIn('status', ['pending', 'active', 'trialing'])
                ->latest()
                ->first();
        }

        if ($subscription) {
            $request->session()->flash('meta_pixel_purchase_subscription_id', (string) $subscription->id);
        }

        return view('customer.billing.success', [
            'customer' => $customer,
            'provider' => $provider,
            'subscription' => $subscription,
        ]);
    }

    private function resolveBillingProviderKey(): string
    {
        $provider = config('billing.provider', 'stripe');

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

        return is_string($provider) && $provider !== '' ? $provider : 'stripe';
    }

    private function enabledProviderKeys(): array
    {
        $billingProviders = [];

        try {
            $billingProviders = Setting::get('billing_providers');
        } catch (\Throwable $e) {
            $billingProviders = [];
        }

        if (!is_array($billingProviders)) {
            $billingProviders = [];
        }

        $supported = ['stripe', 'paypal', 'razorpay', 'flutterwave', 'manual'];

        $enabled = collect($supported)
            ->filter(fn ($key) => (bool) data_get($billingProviders, $key . '.enabled', false))
            ->values()
            ->all();

        return empty($enabled) ? ['stripe'] : $enabled;
    }

    private function resolvePaymentProvider(string $providerKey): PaymentProviderInterface
    {
        return match ($providerKey) {
            'paypal' => app(PayPalPaymentService::class),
            'razorpay' => app(RazorpayPaymentService::class),
            'flutterwave' => app(FlutterwavePaymentService::class),
            'manual' => app(ManualPaymentService::class),
            default => new StripePaymentService(app(StripeClient::class)),
        };
    }

    public function destroyInvoiceEvent(Request $request, WebhookEvent $event): RedirectResponse
    {
        $customer = $request->user('customer');

        if (!$customer->stripe_customer_id) {
            return back()->with('error', 'Billing history is not available.');
        }

        $belongsToCustomer = $event->provider === 'stripe'
            && is_string($event->type)
            && str_starts_with($event->type, 'invoice.')
            && data_get($event->payload, 'customer') === $customer->stripe_customer_id;

        abort_unless($belongsToCustomer, 403);

        $event->delete();

        return back()->with('success', 'Invoice entry deleted.');
    }
}
