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

class PayPalPaymentService implements PaymentProviderInterface
{
    public function createCheckoutSession(Customer $customer, Plan $plan, array $options = []): string
    {
        $clientId = $this->getConfigValue('client_id');
        $clientSecret = $this->getConfigValue('client_secret');

        if (!is_string($clientId) || trim($clientId) === '' || !is_string($clientSecret) || trim($clientSecret) === '') {
            throw new \RuntimeException('PayPal is not configured. Set PAYPAL_CLIENT_ID and PAYPAL_CLIENT_SECRET (or configure via Payment Methods).');
        }

        $currency = strtoupper((string) ($plan->currency ?: ($customer->currency ?: 'USD')));
        $amount = (float) $plan->price;
        if ($amount < 0) {
            $amount = 0;
        }

        $localSubscriptionId = (int) ($options['local_subscription_id'] ?? 0);

        $referenceId = sprintf(
            'sub_%d_%s',
            $localSubscriptionId,
            Str::lower(Str::random(12))
        );

        if ($localSubscriptionId > 0) {
            Subscription::whereKey($localSubscriptionId)->update([
                'payment_reference' => $referenceId,
            ]);
        }

        $returnUrl = $options['redirect_url'] ?? route('billing.paypal.callback');
        $cancelUrl = $options['cancel_url'] ?? route('customer.billing.index');

        $order = $this->createOrder($clientId, $clientSecret, [
            'currency' => $currency,
            'amount' => number_format($amount, 2, '.', ''),
            'description' => (string) ($plan->name ?: (config('app.name') . ' Subscription')),
            'custom_id' => (string) ($localSubscriptionId > 0 ? $localSubscriptionId : ''),
            'reference_id' => $referenceId,
            'return_url' => (string) $returnUrl,
            'cancel_url' => (string) $cancelUrl,
        ]);

        $orderId = $order['id'] ?? null;
        if (!is_string($orderId) || trim($orderId) === '') {
            throw new \RuntimeException('PayPal checkout failed: missing order id.');
        }

        if ($localSubscriptionId > 0) {
            Subscription::whereKey($localSubscriptionId)->update([
                'stripe_checkout_session_id' => $orderId,
            ]);
        }

        $approveUrl = $this->extractApproveUrl($order);
        if (!is_string($approveUrl) || trim($approveUrl) === '') {
            throw new \RuntimeException('PayPal checkout failed: missing approval link.');
        }

        return $approveUrl;
    }

    public function createCustomerPortal(Customer $customer, array $options = []): string
    {
        return (string) ($options['return_url'] ?? url('/customer/billing'));
    }

    public function cancelAtPeriodEnd(Subscription $subscription): void
    {
        return;
    }

    public function resume(Subscription $subscription): void
    {
        return;
    }

    public function handleWebhook(Request $request): void
    {
        $payload = $request->json()->all();
        if (!is_array($payload)) {
            return;
        }

        $eventType = $payload['event_type'] ?? $payload['eventType'] ?? null;
        $eventId = $payload['id'] ?? null;

        if (!is_string($eventType) || trim($eventType) === '') {
            return;
        }

        $eventId = is_string($eventId) ? trim($eventId) : null;
        if ($eventId === null || $eventId === '') {
            $eventId = (string) Str::uuid();
        }

        if (WebhookEvent::where('provider', 'paypal')->where('event_id', $eventId)->exists()) {
            return;
        }

        WebhookEvent::create([
            'provider' => 'paypal',
            'event_id' => $eventId,
            'type' => (string) $eventType,
            'payload' => $payload,
            'processed_at' => now(),
        ]);

        $eventType = (string) $eventType;
        if ($eventType !== 'PAYMENT.CAPTURE.COMPLETED') {
            return;
        }

        $orderId = data_get($payload, 'resource.supplementary_data.related_ids.order_id');
        if (!is_string($orderId) || trim($orderId) === '') {
            $orderId = data_get($payload, 'resource.id');
        }

        if (!is_string($orderId) || trim($orderId) === '') {
            return;
        }

        $subscription = Subscription::query()
            ->where('provider', 'paypal')
            ->where('stripe_checkout_session_id', $orderId)
            ->latest()
            ->first();

        if (!$subscription) {
            return;
        }

        $amount = data_get($payload, 'resource.amount.value');
        $currency = data_get($payload, 'resource.amount.currency_code');

        $amount = is_numeric($amount) ? (float) $amount : null;
        $currency = is_string($currency) ? strtoupper(trim($currency)) : null;

        $this->activateSubscriptionFromPayment($subscription, $orderId, $amount, $currency);
    }

    public function captureOrderAndSync(string $orderId): array
    {
        $clientId = $this->getConfigValue('client_id');
        $clientSecret = $this->getConfigValue('client_secret');

        if (!is_string($clientId) || trim($clientId) === '' || !is_string($clientSecret) || trim($clientSecret) === '') {
            throw new \RuntimeException('PayPal is not configured.');
        }

        return $this->captureOrder($clientId, $clientSecret, $orderId);
    }

    public function applyCapturedOrderData(array $captureData): ?Subscription
    {
        $orderId = $captureData['id'] ?? null;
        if (!is_string($orderId) || trim($orderId) === '') {
            return null;
        }

        $status = (string) ($captureData['status'] ?? '');
        $isCompleted = $status === 'COMPLETED' || $status === 'APPROVED';

        $customId = data_get($captureData, 'purchase_units.0.custom_id');
        $subscription = null;

        if (is_string($customId) && is_numeric($customId)) {
            $subscription = Subscription::query()->whereKey((int) $customId)->first();
        }

        if (!$subscription) {
            $subscription = Subscription::query()
                ->where('provider', 'paypal')
                ->where('stripe_checkout_session_id', $orderId)
                ->latest()
                ->first();
        }

        if (!$subscription) {
            return null;
        }

        if (!$isCompleted) {
            $subscription->update([
                'status' => 'pending',
                'last_payment_status' => $status !== '' ? strtolower($status) : $subscription->last_payment_status,
                'stripe_checkout_session_id' => $orderId,
            ]);

            return $subscription;
        }

        $amount = data_get($captureData, 'purchase_units.0.payments.captures.0.amount.value');
        $currency = data_get($captureData, 'purchase_units.0.payments.captures.0.amount.currency_code');

        $amount = is_numeric($amount) ? (float) $amount : null;
        $currency = is_string($currency) ? strtoupper(trim($currency)) : null;

        $this->activateSubscriptionFromPayment($subscription, $orderId, $amount, $currency);

        return $subscription;
    }

    private function activateSubscriptionFromPayment(Subscription $subscription, string $orderId, ?float $amount, ?string $currency): void
    {
        $plan = $subscription->plan;
        if (!$plan && $subscription->plan_db_id) {
            $plan = Plan::query()->with('customerGroup')->find($subscription->plan_db_id);
        }

        $billingCycle = $plan?->billing_cycle ?? $subscription->billing_cycle;
        $periodStart = now();
        $periodEnd = $billingCycle === 'yearly' ? now()->addYear() : now()->addMonth();

        $subscription->update([
            'provider' => 'paypal',
            'payment_gateway' => 'paypal',
            'status' => $subscription->trial_ends_at && $subscription->trial_ends_at->isFuture() ? 'trialing' : 'active',
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'cancel_at_period_end' => false,
            'auto_renew' => true,
            'last_payment_status' => 'succeeded',
            'stripe_checkout_session_id' => $orderId,
        ]);

        if ($plan && $plan->customerGroup) {
            $limits = [
                'emails_sent_this_month' => $plan->customerGroup?->quota,
                'subscribers_count' => $plan->customerGroup?->max_subscribers,
                'campaigns_count' => $plan->customerGroup?->max_campaigns,
            ];

            $subscription->update([
                'features' => $plan->customerGroup?->permissions,
                'limits' => array_filter($limits, fn ($v) => $v !== null),
            ]);

            $customer = $subscription->customer;
            if ($customer && $plan->customer_group_id) {
                $customer->customerGroups()->syncWithoutDetaching([(int) $plan->customer_group_id]);
            }
        }

        $eventKey = 'paypal:order:' . $orderId;

        app(AffiliateCommissionService::class)->createCommissionForSubscriptionPayment(
            $subscription,
            'paypal',
            $eventKey,
            $amount,
            $currency
        );
    }

    private function createOrder(string $clientId, string $clientSecret, array $payload): array
    {
        $token = $this->getAccessToken($clientId, $clientSecret);

        $baseUrl = $this->getBaseUrl();

        $response = Http::withToken($token)
            ->acceptJson()
            ->post($baseUrl . '/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => (string) ($payload['reference_id'] ?? ''),
                        'description' => (string) ($payload['description'] ?? ''),
                        'custom_id' => (string) ($payload['custom_id'] ?? ''),
                        'amount' => [
                            'currency_code' => (string) ($payload['currency'] ?? 'USD'),
                            'value' => (string) ($payload['amount'] ?? '0.00'),
                        ],
                    ],
                ],
                'application_context' => [
                    'return_url' => (string) ($payload['return_url'] ?? route('billing.paypal.callback')),
                    'cancel_url' => (string) ($payload['cancel_url'] ?? route('customer.billing.index')),
                    'brand_name' => (string) config('app.name'),
                    'shipping_preference' => 'NO_SHIPPING',
                    'user_action' => 'PAY_NOW',
                ],
            ]);

        if (!$response->ok()) {
            Log::warning('PayPal order creation failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            throw new \RuntimeException('PayPal checkout failed.');
        }

        $data = $response->json();
        return is_array($data) ? $data : [];
    }

    private function captureOrder(string $clientId, string $clientSecret, string $orderId): array
    {
        $token = $this->getAccessToken($clientId, $clientSecret);
        $baseUrl = $this->getBaseUrl();

        $response = Http::withToken($token)
            ->acceptJson()
            ->post($baseUrl . '/v2/checkout/orders/' . urlencode($orderId) . '/capture');

        if (!$response->ok()) {
            Log::warning('PayPal order capture failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            throw new \RuntimeException('Unable to capture PayPal payment.');
        }

        $data = $response->json();
        return is_array($data) ? $data : [];
    }

    private function extractApproveUrl(array $order): ?string
    {
        $links = $order['links'] ?? null;
        if (!is_array($links)) {
            return null;
        }

        foreach ($links as $link) {
            if (!is_array($link)) {
                continue;
            }

            $rel = $link['rel'] ?? null;
            $href = $link['href'] ?? null;

            if (is_string($rel) && strtolower($rel) === 'approve' && is_string($href) && trim($href) !== '') {
                return trim($href);
            }
        }

        return null;
    }

    private function getAccessToken(string $clientId, string $clientSecret): string
    {
        $baseUrl = $this->getBaseUrl();

        $response = Http::withBasicAuth($clientId, $clientSecret)
            ->asForm()
            ->acceptJson()
            ->post($baseUrl . '/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ]);

        if (!$response->ok()) {
            Log::warning('PayPal token request failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            throw new \RuntimeException('PayPal authentication failed.');
        }

        $token = $response->json('access_token');
        if (!is_string($token) || trim($token) === '') {
            throw new \RuntimeException('PayPal authentication failed: missing access token.');
        }

        return $token;
    }

    private function getBaseUrl(): string
    {
        return $this->getMode() === 'sandbox'
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';
    }

    private function getMode(): string
    {
        $billingProviders = Setting::get('billing_providers');
        $mode = is_array($billingProviders) ? data_get($billingProviders, 'paypal.mode') : null;

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
            $value = data_get($billingProviders, 'paypal.' . $mode . '.' . $key);
        }

        if (!is_string($value) || trim($value) === '') {
            $fallbackKey = 'paypal_' . $key;
            $value = Setting::get($fallbackKey, config('billing.paypal.' . $key));
        }

        return is_string($value) ? trim($value) : null;
    }
}

