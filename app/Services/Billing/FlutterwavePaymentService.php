<?php

namespace App\Services\Billing;

use App\Models\Customer;
use App\Models\Plan;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\WebhookEvent;
use App\Services\AffiliateCommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FlutterwavePaymentService implements PaymentProviderInterface
{
    private const BASE_URL = 'https://api.flutterwave.com/v3';

    public function createCheckoutSession(Customer $customer, Plan $plan, array $options = []): string
    {
        $secret = $this->getConfigValue('secret');

        if (!is_string($secret) || trim($secret) === '') {
            throw new \RuntimeException('Flutterwave is not configured. Set FLUTTERWAVE_SECRET (or the "flutterwave_secret" setting).');
        }

        $currency = strtoupper((string) ($plan->currency ?: ($customer->currency ?: 'USD')));

        $amount = (float) $plan->price;
        if ($amount < 0) {
            $amount = 0;
        }

        $localSubscriptionId = (int) ($options['local_subscription_id'] ?? 0);

        $txRef = sprintf(
            'sub_%d_%s',
            $localSubscriptionId,
            Str::lower(Str::random(12))
        );

        if ($localSubscriptionId > 0) {
            Subscription::whereKey($localSubscriptionId)->update([
                'payment_reference' => $txRef,
            ]);
        }

        $redirectUrl = $options['redirect_url'] ?? route('billing.flutterwave.callback');

        $paymentPlanId = $this->getOrCreatePaymentPlanId($plan);

        $payload = [
            'tx_ref' => $txRef,
            'amount' => $amount,
            'currency' => $currency,
            'redirect_url' => $redirectUrl,
            'customer' => [
                'email' => $customer->email,
                'name' => $customer->full_name,
            ],
            'customizations' => [
                'title' => config('app.name') . ' Subscription',
                'description' => $plan->name,
            ],
            'meta' => [
                'local_subscription_id' => (string) $localSubscriptionId,
                'plan_id' => (string) $plan->id,
                'plan_slug' => (string) ($plan->slug ?? ''),
                'success_url' => (string) ($options['success_url'] ?? ''),
                'cancel_url' => (string) ($options['cancel_url'] ?? ''),
            ],
        ];

        if ($paymentPlanId) {
            $payload['payment_plan'] = $paymentPlanId;
        }

        $response = Http::withToken($secret)
            ->acceptJson()
            ->post(self::BASE_URL . '/payments', $payload);

        if (!$response->ok()) {
            throw new \RuntimeException('Flutterwave checkout failed.');
        }

        $link = $response->json('data.link');
        if (!is_string($link) || trim($link) === '') {
            throw new \RuntimeException('Flutterwave checkout failed: missing payment link.');
        }

        return $link;
    }

    public function createCustomerPortal(Customer $customer, array $options = []): string
    {
        return (string) ($options['return_url'] ?? url('/customer/billing'));
    }

    public function cancelAtPeriodEnd(Subscription $subscription): void
    {
        $subscriptionId = $subscription->stripe_subscription_id;
        if (!is_string($subscriptionId) || trim($subscriptionId) === '') {
            return;
        }

        $secret = $this->getConfigValue('secret');
        if (!is_string($secret) || trim($secret) === '') {
            return;
        }

        try {
            Http::withToken($secret)
                ->acceptJson()
                ->post(self::BASE_URL . '/subscriptions/' . urlencode($subscriptionId) . '/cancel');
        } catch (\Throwable $e) {
            Log::warning('Flutterwave subscription cancel call failed', [
                'subscription_id' => $subscription->id,
                'flutterwave_subscription_id' => $subscriptionId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function resume(Subscription $subscription): void
    {
        // Flutterwave does not provide a reliable universal "resume" API for subscriptions.
        // We keep this as a no-op and rely on local state changes.
    }

    public function handleWebhook(Request $request): void
    {
        $verifHash = $request->header('verif-hash');
        $payload = $request->json()->all();

        $expected = $this->getConfigValue('webhook_secret');
        $expected = is_string($expected) ? trim($expected) : null;
        $hasExpected = is_string($expected) && $expected !== '';

        if (!$hasExpected || !is_string($verifHash) || trim($verifHash) === '') {
            if (app()->environment('production')) {
                abort(400, 'Webhook verification failed');
            }
        } else {
            if (!hash_equals($expected, (string) $verifHash)) {
                abort(400, 'Invalid signature');
            }
        }

        $eventType = $payload['event'] ?? $payload['type'] ?? null;
        $eventId = data_get($payload, 'data.id') ?? $payload['id'] ?? null;

        if (!is_string($eventType) || trim($eventType) === '') {
            abort(400, 'Invalid payload');
        }

        $eventId = is_string($eventId) ? trim($eventId) : null;
        if ($eventId === null || $eventId === '') {
            $eventId = (string) Str::uuid();
        }

        if (WebhookEvent::where('provider', 'flutterwave')->where('event_id', $eventId)->exists()) {
            return;
        }

        WebhookEvent::create([
            'provider' => 'flutterwave',
            'event_id' => $eventId,
            'type' => (string) $eventType,
            'payload' => $payload,
            'processed_at' => now(),
        ]);

        if ((string) $eventType === 'charge.completed') {
            $transactionId = data_get($payload, 'data.id');
            if ($transactionId) {
                $this->applyTransactionToSubscription((string) $transactionId);
            }
        }

        if (in_array((string) $eventType, ['subscription.cancelled', 'subscription.canceled'], true)) {
            $subscriptionId = data_get($payload, 'data.id') ?? data_get($payload, 'data.subscription_id');
            if (is_string($subscriptionId) && trim($subscriptionId) !== '') {
                Subscription::query()
                    ->where('provider', 'flutterwave')
                    ->where('stripe_subscription_id', $subscriptionId)
                    ->update([
                        'status' => 'cancelled',
                        'cancel_at_period_end' => true,
                        'auto_renew' => false,
                        'cancelled_at' => now(),
                    ]);
            }
        }
    }

    public function verifyTransactionAndSync(string $transactionId): array
    {
        $secret = $this->getConfigValue('secret');

        if (!is_string($secret) || trim($secret) === '') {
            throw new \RuntimeException('Flutterwave is not configured.');
        }

        $response = Http::withToken($secret)
            ->acceptJson()
            ->get(self::BASE_URL . '/transactions/' . urlencode($transactionId) . '/verify');

        if (!$response->ok()) {
            throw new \RuntimeException('Unable to verify Flutterwave transaction.');
        }

        $data = $response->json('data');
        if (!is_array($data)) {
            throw new \RuntimeException('Invalid Flutterwave verification response.');
        }

        return $data;
    }

    public function applyTransactionToSubscription(string $transactionId): ?Subscription
    {
        $data = $this->verifyTransactionAndSync($transactionId);

        return $this->applyVerifiedTransactionData($data);
    }

    public function applyVerifiedTransactionData(array $data): ?Subscription
    {
        $status = (string) ($data['status'] ?? '');
        $txRef = (string) ($data['tx_ref'] ?? '');

        if ($txRef === '') {
            return null;
        }

        $meta = $this->normalizeMeta($data['meta'] ?? null);
        $localSubscriptionId = (int) ($meta['local_subscription_id'] ?? 0);

        $subscription = null;
        if ($localSubscriptionId > 0) {
            $subscription = Subscription::query()->whereKey($localSubscriptionId)->first();
        }

        if (!$subscription) {
            $subscription = Subscription::query()
                ->where('provider', 'flutterwave')
                ->where('payment_reference', $txRef)
                ->latest()
                ->first();
        }

        if (!$subscription) {
            return null;
        }

        $plan = $subscription->plan;

        if ($status !== 'successful') {
            $subscription->update([
                'status' => 'pending',
                'last_payment_status' => $status,
                'payment_reference' => $txRef,
            ]);

            return $subscription;
        }

        $periodStart = now();
        $periodEnd = null;

        $billingCycle = $plan?->billing_cycle ?? $subscription->billing_cycle;
        if ($billingCycle === 'yearly') {
            $periodEnd = now()->addYear();
        } else {
            $periodEnd = now()->addMonth();
        }

        $planDbId = $subscription->plan_db_id;
        if (isset($meta['plan_id']) && is_numeric($meta['plan_id'])) {
            $planDbId = (int) $meta['plan_id'];
        }

        $subscriptionId = $data['subscription_id'] ?? $data['subscription'] ?? null;
        if (!is_string($subscriptionId)) {
            $subscriptionId = null;
        }

        $subscription->update([
            'provider' => 'flutterwave',
            'payment_gateway' => 'flutterwave',
            'status' => $subscription->trial_ends_at && $subscription->trial_ends_at->isFuture() ? 'trialing' : 'active',
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'cancel_at_period_end' => false,
            'auto_renew' => true,
            'plan_db_id' => $planDbId,
            'payment_reference' => $txRef,
            'last_payment_status' => 'succeeded',
            'stripe_subscription_id' => $subscriptionId ?: $subscription->stripe_subscription_id,
        ]);

        if ($planDbId && (!$plan || $plan->id !== $planDbId)) {
            $plan = Plan::query()->with('customerGroup')->find($planDbId);
        }

        if ($plan && $plan->customerGroup) {
            $limits = [
                'emails_sent_this_month' => $plan->customerGroup?->limit('sending_quota.monthly_quota', 0),
                'subscribers_count' => $plan->customerGroup?->limit('lists.limits.max_subscribers', 0),
                'campaigns_count' => $plan->customerGroup?->limit('campaigns.limits.max_campaigns', 0),
            ];

            $subscription->update([
                'features' => $plan->customerGroup?->permissions,
                'limits' => array_filter($limits, fn ($v) => $v !== null),
            ]);
        }

        $this->syncCustomerGroupAccessForSubscription($subscription);

        $txId = $data['id'] ?? null;
        $txId = is_string($txId) ? trim($txId) : (is_numeric($txId) ? (string) $txId : '');
        $eventKey = 'flutterwave:tx:' . ($txId !== '' ? $txId : $txRef);

        $amount = $data['amount'] ?? null;
        $amount = is_numeric($amount) ? (float) $amount : null;
        $currency = $data['currency'] ?? null;
        $currency = is_string($currency) ? strtoupper(trim($currency)) : null;

        app(AffiliateCommissionService::class)->createCommissionForSubscriptionPayment(
            $subscription,
            'flutterwave',
            $eventKey,
            $amount,
            $currency
        );

        return $subscription;
    }

    private function getOrCreatePaymentPlanId(Plan $plan): ?int
    {
        $mode = $this->getMode();

        $key = 'flutterwave_payment_plans_' . $mode;
        $mapping = Setting::get($key, []);
        if (!is_array($mapping)) {
            $mapping = [];
        }

        $existing = $mapping[(string) $plan->id] ?? null;
        if (is_numeric($existing)) {
            return (int) $existing;
        }

        $secret = $this->getConfigValue('secret');
        if (!is_string($secret) || trim($secret) === '') {
            return null;
        }

        $interval = $plan->billing_cycle === 'yearly' ? 'yearly' : 'monthly';

        $response = Http::withToken($secret)
            ->acceptJson()
            ->post(self::BASE_URL . '/payment-plans', [
                'amount' => (float) $plan->price,
                'name' => $plan->name,
                'interval' => $interval,
            ]);

        if (!$response->ok()) {
            return null;
        }

        $planId = $response->json('data.id');
        if (!is_numeric($planId)) {
            return null;
        }

        $mapping[(string) $plan->id] = (int) $planId;
        Setting::set($key, $mapping, 'billing', 'array');

        return (int) $planId;
    }

    private function getMode(): string
    {
        $billingProviders = Setting::get('billing_providers');
        $mode = is_array($billingProviders) ? data_get($billingProviders, 'flutterwave.mode') : null;

        if (!is_string($mode) || !in_array($mode, ['live', 'sandbox'], true)) {
            $mode = 'live';
        }

        return $mode;
    }

    private function getConfigValue(string $key): ?string
    {
        $billingProviders = Setting::get('billing_providers');
        $mode = $this->getMode();

        $value = null;
        if (is_array($billingProviders)) {
            $value = data_get($billingProviders, 'flutterwave.' . $mode . '.' . $key);
        }

        if (!is_string($value) || trim($value) === '') {
            $fallbackKey = 'flutterwave_' . $key;
            $value = Setting::get($fallbackKey, config('billing.flutterwave.' . $key));
        }

        return is_string($value) ? trim($value) : null;
    }

    private function normalizeMeta(mixed $meta): array
    {
        if (is_array($meta)) {
            $allStringKeys = true;
            foreach (array_keys($meta) as $k) {
                if (!is_string($k)) {
                    $allStringKeys = false;
                    break;
                }
            }

            if ($allStringKeys) {
                return $meta;
            }

            $normalized = [];
            foreach ($meta as $item) {
                if (is_array($item) && isset($item['name'], $item['value'])) {
                    $normalized[(string) $item['name']] = $item['value'];
                }
            }

            return $normalized;
        }

        return [];
    }

    private function syncCustomerGroupAccessForSubscription(Subscription $subscription): void
    {
        if (!in_array($subscription->status, ['active', 'trialing', 'past_due'], true)) {
            return;
        }

        $plan = $subscription->plan;
        if (!$plan || !$plan->customer_group_id) {
            return;
        }

        $customer = $subscription->customer;
        if (!$customer) {
            return;
        }

        $customer->customerGroups()->syncWithoutDetaching([(int) $plan->customer_group_id]);

        Log::info('Customer group synced from subscription plan', [
            'customer_id' => $customer->id,
            'subscription_id' => $subscription->id,
            'plan_id' => $plan->id,
            'customer_group_id' => (int) $plan->customer_group_id,
        ]);
    }
}
