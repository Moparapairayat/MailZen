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

class RazorpayPaymentService implements PaymentProviderInterface
{
    private const BASE_URL = 'https://api.razorpay.com/v1';

    public function createCheckoutSession(Customer $customer, Plan $plan, array $options = []): string
    {
        $keyId = $this->getConfigValue('key_id');
        $keySecret = $this->getConfigValue('key_secret');

        if (!is_string($keyId) || trim($keyId) === '' || !is_string($keySecret) || trim($keySecret) === '') {
            throw new \RuntimeException('Razorpay is not configured. Set RAZORPAY_KEY_ID and RAZORPAY_KEY_SECRET (or configure via Payment Methods).');
        }

        $amount = (int) round(((float) $plan->price) * 100);
        if ($amount < 0) {
            $amount = 0;
        }

        $currency = strtoupper((string) ($plan->currency ?: ($customer->currency ?: 'USD')));

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

        $callbackUrl = $options['redirect_url'] ?? route('billing.razorpay.callback');

        $payload = [
            'amount' => $amount,
            'currency' => $currency,
            'description' => (string) ($plan->name ?: (config('app.name') . ' Subscription')),
            'reference_id' => $referenceId,
            'customer' => [
                'name' => (string) ($customer->full_name ?: $customer->email),
                'email' => (string) $customer->email,
            ],
            'notify' => [
                'email' => true,
            ],
            'callback_url' => (string) $callbackUrl,
            'callback_method' => 'get',
            'notes' => [
                'local_subscription_id' => (string) $localSubscriptionId,
                'plan_id' => (string) $plan->id,
                'plan_slug' => (string) ($plan->slug ?? ''),
                'success_url' => (string) ($options['success_url'] ?? ''),
                'cancel_url' => (string) ($options['cancel_url'] ?? ''),
            ],
        ];

        $response = Http::withBasicAuth($keyId, $keySecret)
            ->acceptJson()
            ->post(self::BASE_URL . '/payment_links', $payload);

        if (!$response->ok()) {
            Log::warning('Razorpay payment link creation failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            throw new \RuntimeException('Razorpay checkout failed.');
        }

        $shortUrl = $response->json('short_url');
        if (!is_string($shortUrl) || trim($shortUrl) === '') {
            throw new \RuntimeException('Razorpay checkout failed: missing payment link URL.');
        }

        return $shortUrl;
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
        $signature = $request->header('X-Razorpay-Signature');
        $payloadRaw = $request->getContent();

        $webhookSecret = $this->getConfigValue('webhook_secret');
        $webhookSecret = is_string($webhookSecret) ? trim($webhookSecret) : null;
        $hasSecret = is_string($webhookSecret) && $webhookSecret !== '';

        if (!$hasSecret || !is_string($signature) || trim($signature) === '') {
            if (app()->environment('production')) {
                abort(400, 'Webhook verification failed');
            }
        } else {
            $expected = hash_hmac('sha256', $payloadRaw, $webhookSecret);
            if (!hash_equals($expected, (string) $signature)) {
                abort(400, 'Invalid signature');
            }
        }

        $payload = $request->json()->all();
        if (!is_array($payload)) {
            abort(400, 'Invalid payload');
        }

        $eventType = $payload['event'] ?? null;
        if (!is_string($eventType) || trim($eventType) === '') {
            abort(400, 'Invalid payload');
        }

        $eventId = $payload['id'] ?? null;
        if (!is_string($eventId) || trim($eventId) === '') {
            $entityId = data_get($payload, 'payload.payment_link.entity.id');
            if (is_string($entityId) && trim($entityId) !== '') {
                $eventId = $entityId . ':' . (string) (data_get($payload, 'created_at') ?? Str::uuid());
            } else {
                $eventId = (string) Str::uuid();
            }
        }

        if (WebhookEvent::where('provider', 'razorpay')->where('event_id', $eventId)->exists()) {
            return;
        }

        WebhookEvent::create([
            'provider' => 'razorpay',
            'event_id' => $eventId,
            'type' => (string) $eventType,
            'payload' => $payload,
            'processed_at' => now(),
        ]);

        if ($eventType === 'payment_link.paid') {
            $subscription = $this->applyPaymentLinkPaid($payload);

            if ($subscription) {
                $amountMinor = data_get($payload, 'payload.payment.entity.amount');
                $amount = is_numeric($amountMinor) ? ((float) $amountMinor) / 100.0 : null;
                $currency = data_get($payload, 'payload.payment.entity.currency');
                $currency = is_string($currency) ? strtoupper(trim($currency)) : null;

                app(AffiliateCommissionService::class)->createCommissionForSubscriptionPayment(
                    $subscription,
                    'razorpay',
                    'razorpay:' . (string) $eventId,
                    $amount,
                    $currency
                );
            }
        }
    }

    private function applyPaymentLinkPaid(array $payload): ?Subscription
    {
        $referenceId = data_get($payload, 'payload.payment_link.entity.reference_id');
        $notesLocalId = data_get($payload, 'payload.payment_link.entity.notes.local_subscription_id');

        $subscription = null;

        if (is_string($notesLocalId) && is_numeric($notesLocalId)) {
            $subscription = Subscription::query()->whereKey((int) $notesLocalId)->first();
        }

        if (!$subscription && is_string($referenceId) && trim($referenceId) !== '') {
            $subscription = Subscription::query()
                ->where('provider', 'razorpay')
                ->where('payment_reference', $referenceId)
                ->latest()
                ->first();

            if (!$subscription) {
                $subscription = Subscription::query()
                    ->where('payment_reference', $referenceId)
                    ->latest()
                    ->first();
            }
        }

        if (!$subscription) {
            return null;
        }

        $plan = $subscription->plan;
        if (!$plan && $subscription->plan_db_id) {
            $plan = Plan::query()->with('customerGroup')->find($subscription->plan_db_id);
        }

        $periodStart = now();
        $periodEnd = null;

        $billingCycle = $plan?->billing_cycle ?? $subscription->billing_cycle;
        if ($billingCycle === 'yearly') {
            $periodEnd = now()->addYear();
        } else {
            $periodEnd = now()->addMonth();
        }

        $paymentLinkId = data_get($payload, 'payload.payment_link.entity.id');
        $paymentId = data_get($payload, 'payload.payment.entity.id');

        $subscription->update([
            'provider' => 'razorpay',
            'payment_gateway' => 'razorpay',
            'status' => $subscription->trial_ends_at && $subscription->trial_ends_at->isFuture() ? 'trialing' : 'active',
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'cancel_at_period_end' => false,
            'auto_renew' => true,
            'payment_reference' => is_string($referenceId) && trim($referenceId) !== '' ? $referenceId : $subscription->payment_reference,
            'last_payment_status' => 'succeeded',
            'stripe_subscription_id' => is_string($paymentLinkId) && trim($paymentLinkId) !== '' ? $paymentLinkId : $subscription->stripe_subscription_id,
            'stripe_checkout_session_id' => is_string($paymentId) && trim($paymentId) !== '' ? $paymentId : $subscription->stripe_checkout_session_id,
        ]);

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

            $customer = $subscription->customer;
            if ($customer && $plan->customer_group_id) {
                $customer->customerGroups()->syncWithoutDetaching([(int) $plan->customer_group_id]);
            }
        }

        return $subscription;
    }

    private function getMode(): string
    {
        $billingProviders = Setting::get('billing_providers');
        $mode = is_array($billingProviders) ? data_get($billingProviders, 'razorpay.mode') : null;

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
            $value = data_get($billingProviders, 'razorpay.' . $mode . '.' . $key);
        }

        if (!is_string($value) || trim($value) === '') {
            $fallbackKey = 'razorpay_' . $key;
            $value = Setting::get($fallbackKey, config('billing.razorpay.' . $key));
        }

        return is_string($value) ? trim($value) : null;
    }
}
