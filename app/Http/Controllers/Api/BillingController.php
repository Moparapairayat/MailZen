<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Plan;
use App\Models\Setting;
use App\Models\Subscription;
use App\Services\Billing\UsageService;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function __construct(
        private readonly UsageService $usageService,
    ) {
    }

    public function current(Request $request)
    {
        $customer = $request->user('sanctum') ?? $request->user('customer');
        $subscription = $customer?->subscriptions()->latest()->first();

        return response()->json([
            'subscription' => $subscription,
            'usage' => $this->usageService->getUsage($customer),
        ]);
    }

    public function checkout(Request $request, Plan $plan)
    {
        $customer = $request->user('sanctum') ?? $request->user('customer');

        $plan->load('customerGroup');

        $providerKey = $this->resolveBillingProviderKey();

        $redirectUrl = match ($providerKey) {
            'flutterwave' => route('billing.flutterwave.callback'),
            'razorpay' => route('billing.razorpay.callback'),
            default => null,
        };

        $validated = $request->validate([
            'success_url' => ['nullable', 'string'],
            'cancel_url' => ['nullable', 'string'],
            'coupon_code' => ['nullable', 'string', 'max:64'],
        ]);

        $promotionCodeId = null;
        $couponCode = trim((string) ($validated['coupon_code'] ?? ''));
        if ($couponCode !== '') {
            $coupon = Coupon::where('code', strtoupper($couponCode))->first();
            if (!$coupon || !$coupon->isUsable() || !$coupon->stripe_promotion_code_id) {
                return response()->json([
                    'message' => 'Invalid or expired coupon code.',
                ], 422);
            }

            if ($providerKey !== 'stripe') {
                return response()->json([
                    'message' => 'Coupon codes are only supported for Stripe checkout.',
                ], 422);
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

        try {
            $paymentProvider = app(\App\Services\Billing\PaymentProviderInterface::class);
            $url = $paymentProvider->createCheckoutSession($customer, $plan, [
                'success_url' => $validated['success_url'] ?? null,
                'cancel_url' => $validated['cancel_url'] ?? null,
                'redirect_url' => $redirectUrl,
                'promotion_code' => $promotionCodeId,
                'local_subscription_id' => $subscription->id,
            ]);
        } catch (\Throwable $e) {
            $subscription->delete();
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json(['checkout_url' => $url, 'subscription_id' => $subscription->id]);
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

    public function cancel(Request $request, Subscription $subscription)
    {
        $customer = $request->user('sanctum') ?? $request->user('customer');
        abort_if($subscription->customer_id !== $customer->id, 403);

        $paymentProvider = app(\App\Services\Billing\PaymentProviderInterface::class);
        $paymentProvider->cancelAtPeriodEnd($subscription);
        $subscription->update([
            'cancel_at_period_end' => true,
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return response()->json(['status' => 'cancelled']);
    }

    public function resume(Request $request, Subscription $subscription)
    {
        $customer = $request->user('sanctum') ?? $request->user('customer');
        abort_if($subscription->customer_id !== $customer->id, 403);

        $paymentProvider = app(\App\Services\Billing\PaymentProviderInterface::class);
        $paymentProvider->resume($subscription);
        $subscription->update([
            'cancel_at_period_end' => false,
            'status' => 'active',
        ]);

        return response()->json(['status' => 'active']);
    }

    public function portal(Request $request)
    {
        $customer = $request->user('sanctum') ?? $request->user('customer');
        $paymentProvider = app(\App\Services\Billing\PaymentProviderInterface::class);
        $url = $paymentProvider->createCustomerPortal($customer, [
            'return_url' => $request->input('return_url'),
        ]);

        return response()->json(['url' => $url]);
    }

    public function history(Request $request)
    {
        $customer = $request->user('sanctum') ?? $request->user('customer');
        $subscriptions = $customer->subscriptions()->latest()->get();

        return response()->json(['subscriptions' => $subscriptions]);
    }
}

