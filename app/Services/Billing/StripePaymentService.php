<?php

namespace App\Services\Billing;

use App\Models\Customer;
use App\Models\Plan;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\WebhookEvent;
use App\Services\AffiliateCommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;

class StripePaymentService implements PaymentProviderInterface
{
    public function __construct(private readonly StripeClient $stripe)
    {
        // StripeClient is configured in BillingServiceProvider (DB setting fallback to config)
    }

    public function createCheckoutSession(Customer $customer, Plan $plan, array $options = []): string
    {
        $stripeCustomerId = $customer->stripe_customer_id ?? $this->createStripeCustomer($customer);

        $stripePriceId = is_string($plan->stripe_price_id) ? trim($plan->stripe_price_id) : null;
        $stripeProductId = is_string($plan->stripe_product_id) ? trim($plan->stripe_product_id) : null;

        $lineItem = null;
        if ($stripePriceId && Str::startsWith($stripePriceId, 'price_')) {
            $lineItem = [
                'price' => $stripePriceId,
                'quantity' => 1,
            ];
        } else {
            $productId = null;
            if ($stripePriceId && Str::startsWith($stripePriceId, 'prod_')) {
                $productId = $stripePriceId;
            }
            if (!$productId && $stripeProductId && Str::startsWith($stripeProductId, 'prod_')) {
                $productId = $stripeProductId;
            }

            if (!$productId) {
                throw new \RuntimeException("Plan is missing a valid Stripe price ID (price_...) or product ID (prod_...).");
            }

            $unitAmount = (int) round(((float) $plan->price) * 100);
            if ($unitAmount < 0) {
                $unitAmount = 0;
            }

            $interval = ($plan->billing_cycle === 'yearly') ? 'year' : 'month';

            $lineItem = [
                'price_data' => [
                    'currency' => strtolower($plan->currency),
                    'product' => $productId,
                    'unit_amount' => $unitAmount,
                    'recurring' => [
                        'interval' => $interval,
                    ],
                ],
                'quantity' => 1,
            ];
        }

        $payload = [
            'mode' => 'subscription',
            'customer' => $stripeCustomerId,
            'line_items' => [
                $lineItem,
            ],
            'subscription_data' => [
                'metadata' => [
                    'plan_id' => $plan->id,
                    'plan_slug' => $plan->slug,
                    'local_subscription_id' => (string) ($options['local_subscription_id'] ?? ''),
                ],
            ],
            'billing_address_collection' => 'required',
            'customer_update' => [
                'address' => 'auto',
                'name' => 'auto',
            ],
            'allow_promotion_codes' => true,
            'success_url' => $options['success_url'] ?? url('/billing/success'),
            'cancel_url' => $options['cancel_url'] ?? url('/billing/cancel'),
        ];

        try {
            $taxRule = (new TaxResolver())->resolveForCustomer($customer);
            if ($taxRule) {
                $taxRateId = (new StripeTaxRateService($this->stripe))->getOrCreateTaxRateId($taxRule);
                if (is_string($taxRateId) && $taxRateId !== '') {
                    $payload['subscription_data']['default_tax_rates'] = [$taxRateId];

                    $payload['subscription_data']['metadata']['tax_type'] = (string) ($taxRule['type'] ?? '');
                    $payload['subscription_data']['metadata']['tax_percentage'] = (string) ($taxRule['percentage'] ?? '');
                    $payload['subscription_data']['metadata']['tax_country'] = (string) ($taxRule['country'] ?? '');
                    $payload['subscription_data']['metadata']['tax_state'] = (string) ($taxRule['state'] ?? '');
                }
            }
        } catch (\Throwable $e) {
            // Ignore tax resolution errors to avoid blocking checkout
        }

        $trialDays = (int) ($plan->trial_days ?? 0);
        if ($trialDays >= 1) {
            $payload['subscription_data']['trial_period_days'] = $trialDays;
        }

        if (!empty($options['promotion_code'])) {
            $payload['discounts'] = [
                ['promotion_code' => $options['promotion_code']],
            ];
        }

        $session = $this->stripe->checkout->sessions->create($payload);

        if (!empty($options['local_subscription_id'])) {
            Subscription::whereKey((int) $options['local_subscription_id'])->update([
                'stripe_checkout_session_id' => $session->id,
                'stripe_customer_id' => $stripeCustomerId,
            ]);
        }

        return $session->url;
    }

    public function createCustomerPortal(Customer $customer, array $options = []): string
    {
        $stripeCustomerId = $customer->stripe_customer_id ?? $this->createStripeCustomer($customer);

        $portalSession = $this->stripe->billingPortal->sessions->create([
            'customer' => $stripeCustomerId,
            'return_url' => $options['return_url'] ?? url('/billing'),
        ]);

        return $portalSession->url;
    }

    public function cancelAtPeriodEnd(Subscription $subscription): void
    {
        if (!$subscription->stripe_subscription_id) {
            return;
        }

        $this->stripe->subscriptions->update($subscription->stripe_subscription_id, [
            'cancel_at_period_end' => true,
        ]);
    }

    public function resume(Subscription $subscription): void
    {
        if (!$subscription->stripe_subscription_id) {
            return;
        }

        $this->stripe->subscriptions->update($subscription->stripe_subscription_id, [
            'cancel_at_period_end' => false,
        ]);
    }

    public function handleWebhook(Request $request): void
    {
        $signature = $request->header('Stripe-Signature');
        $payload = $request->getContent();

        $webhookSecret = Setting::get('stripe_webhook_secret', config('billing.stripe.webhook_secret'));
        if (is_string($webhookSecret)) {
            $webhookSecret = trim($webhookSecret);
            if ($webhookSecret === '') {
                $webhookSecret = null;
            }
        }

        $event = null;
        $decodedEvent = null;
        $shouldVerifySignature = $webhookSecret !== null && is_string($signature) && trim($signature) !== '';

        if (!$shouldVerifySignature) {
            if (app()->environment('production')) {
                Log::error('Stripe webhook verification not possible', [
                    'has_signature_header' => is_string($signature) && trim($signature) !== '',
                    'has_webhook_secret' => $webhookSecret !== null,
                ]);

                abort(400, 'Webhook verification failed');
            }

            if ($webhookSecret === null) {
                Log::warning('Stripe webhook secret is not configured; accepting webhook without signature verification in non-production.');
            } else {
                Log::warning('Stripe webhook signature header missing; accepting webhook without signature verification in non-production.');
            }

            $decodedEvent = json_decode($payload);
            if (!is_object($decodedEvent) || !isset($decodedEvent->id) || !isset($decodedEvent->type)) {
                Log::warning('Stripe webhook payload invalid (non-production fallback)');
                abort(400, 'Invalid payload');
            }
        }

        try {
            if ($shouldVerifySignature) {
                $event = Webhook::constructEvent(
                    $payload,
                    $signature,
                    $webhookSecret
                );
            }
        } catch (SignatureVerificationException $exception) {
            Log::warning('Stripe webhook signature verification failed', ['error' => $exception->getMessage()]);
            abort(400, 'Invalid signature');
        } catch (\UnexpectedValueException $exception) {
            Log::warning('Stripe webhook payload invalid', ['error' => $exception->getMessage()]);
            abort(400, 'Invalid payload');
        }

        $eventId = $event?->id ?? $decodedEvent?->id;
        $eventType = $event?->type ?? $decodedEvent?->type;
        $eventObject = $event?->data?->object ?? ($decodedEvent->data->object ?? null);

        if (!is_string($eventId) || trim($eventId) === '' || !is_string($eventType) || trim($eventType) === '') {
            Log::warning('Stripe webhook missing event id/type');
            abort(400, 'Invalid payload');
        }

        if (WebhookEvent::where('event_id', $eventId)->exists()) {
            return;
        }

        WebhookEvent::create([
            'provider' => 'stripe',
            'event_id' => $eventId,
            'type' => $eventType,
            'payload' => $eventObject,
            'processed_at' => now(),
        ]);

        $type = $eventType;
        $data = $eventObject;

        match ($type) {
            'customer.subscription.created',
            'customer.subscription.updated' => $this->handleSubscriptionUpdated($data),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($data),
            'invoice.payment_succeeded' => $this->handleInvoicePaymentSucceeded($data),
            'invoice.payment_failed' => $this->handleInvoicePaymentFailed($data),
            default => null,
        };
    }

    private function handleSubscriptionUpdated(object $data): void
    {
        $subscription = Subscription::where('stripe_subscription_id', $data->id)->first();

        if (!$subscription) {
            $localSubscriptionId = $data->metadata->local_subscription_id ?? null;

            if ($localSubscriptionId) {
                $subscription = Subscription::whereKey((int) $localSubscriptionId)->first();
            }

            if (!$subscription && isset($data->customer) && is_string($data->customer)) {
                $customer = Customer::query()->where('stripe_customer_id', $data->customer)->first();
                if ($customer) {
                    $subscription = $customer->subscriptions()
                        ->where('provider', 'stripe')
                        ->where(function ($q) use ($data) {
                            $q->whereNull('stripe_subscription_id')
                                ->orWhere('stripe_subscription_id', $data->id);
                        })
                        ->latest()
                        ->first();
                }
            }

            if (!$subscription) {
                return;
            }
        }

        $status = match ($data->status) {
            'trialing' => 'trialing',
            'active' => 'active',
            'past_due' => 'past_due',
            'canceled' => 'cancelled',
            'unpaid' => 'suspended',
            default => 'pending',
        };

        $stripePriceId = $data->items->data[0]->price->id ?? $subscription->stripe_price_id;
        $stripeProductId = $data->items->data[0]->price->product ?? null;

        $planDbId = null;
        if (isset($data->metadata->plan_id) && is_numeric($data->metadata->plan_id)) {
            $planDbId = (int) $data->metadata->plan_id;
        }

        if (!$planDbId && $stripePriceId) {
            $planDbId = Plan::query()->where('stripe_price_id', $stripePriceId)->value('id');
        }

        if (!$planDbId && $stripeProductId) {
            $planDbId = Plan::query()->where('stripe_product_id', $stripeProductId)->value('id');
        }

        $plan = $planDbId ? Plan::query()->with('customerGroup')->find($planDbId) : null;

        $subscription->update([
            'status' => $status,
            'stripe_subscription_id' => $data->id,
            'period_start' => isset($data->current_period_start) ? \Carbon\Carbon::createFromTimestamp($data->current_period_start) : null,
            'period_end' => isset($data->current_period_end) ? \Carbon\Carbon::createFromTimestamp($data->current_period_end) : null,
            'cancel_at_period_end' => $data->cancel_at_period_end ?? false,
            'auto_renew' => !($data->cancel_at_period_end ?? false),
            'plan_name' => $data->items->data[0]->price->nickname ?? $subscription->plan_name,
            'plan_id' => $stripePriceId ?? $subscription->plan_id,
            'plan_db_id' => $planDbId ?: $subscription->plan_db_id,
            'stripe_price_id' => $stripePriceId ?? $subscription->stripe_price_id,
            'last_payment_status' => $data->status,
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
        }

        $this->syncCustomerGroupAccessForSubscription($subscription);
    }

    private function handleSubscriptionDeleted(object $data): void
    {
        Subscription::where('stripe_subscription_id', $data->id)
            ->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'auto_renew' => false,
            ]);
    }

    private function handleInvoicePaymentSucceeded(object $data): void
    {
        $subscriptionId = $data->subscription ?? null;
        if (!$subscriptionId) {
            return;
        }

        $subscription = Subscription::where('stripe_subscription_id', $subscriptionId)->first();

        if (!$subscription && isset($data->customer) && is_string($data->customer)) {
            $customer = Customer::query()->where('stripe_customer_id', $data->customer)->first();
            if ($customer) {
                $subscription = $customer->subscriptions()
                    ->where('provider', 'stripe')
                    ->where(function ($q) {
                        $q->whereNull('stripe_subscription_id')
                            ->orWhereIn('status', ['pending', 'active', 'trialing', 'past_due']);
                    })
                    ->latest()
                    ->first();
            }
        }

        if (!$subscription) {
            return;
        }

        if ($subscription->stripe_subscription_id !== $subscriptionId) {
            $subscription->update(['stripe_subscription_id' => $subscriptionId]);
        }

        $subscription->update([
            'status' => 'active',
            'last_payment_status' => 'succeeded',
        ]);

        $this->syncCustomerGroupAccessForSubscription($subscription);

        $invoiceId = isset($data->id) && is_string($data->id) ? trim($data->id) : '';
        $eventKey = 'stripe:invoice:' . ($invoiceId !== '' ? $invoiceId : (string) $subscriptionId);

        $amountPaid = null;
        if (isset($data->amount_paid) && is_numeric($data->amount_paid)) {
            $amountPaid = ((float) $data->amount_paid) / 100.0;
        } elseif (isset($data->amount_due) && is_numeric($data->amount_due)) {
            $amountPaid = ((float) $data->amount_due) / 100.0;
        }

        $currency = isset($data->currency) && is_string($data->currency) ? strtoupper((string) $data->currency) : null;

        app(AffiliateCommissionService::class)->createCommissionForSubscriptionPayment(
            $subscription,
            'stripe',
            $eventKey,
            $amountPaid,
            $currency
        );
    }

    private function handleInvoicePaymentFailed(object $data): void
    {
        $subscriptionId = $data->subscription ?? null;
        if (!$subscriptionId) {
            return;
        }

        $subscription = Subscription::where('stripe_subscription_id', $subscriptionId)->first();

        if (!$subscription && isset($data->customer) && is_string($data->customer)) {
            $customer = Customer::query()->where('stripe_customer_id', $data->customer)->first();
            if ($customer) {
                $subscription = $customer->subscriptions()
                    ->where('provider', 'stripe')
                    ->where(function ($q) {
                        $q->whereNull('stripe_subscription_id')
                            ->orWhereIn('status', ['pending', 'active', 'trialing', 'past_due']);
                    })
                    ->latest()
                    ->first();
            }
        }

        if (!$subscription) {
            return;
        }

        if ($subscription->stripe_subscription_id !== $subscriptionId) {
            $subscription->update(['stripe_subscription_id' => $subscriptionId]);
        }

        $subscription->update([
            'status' => 'past_due',
            'last_payment_status' => 'failed',
        ]);

        $this->syncCustomerGroupAccessForSubscription($subscription);
    }

    private function syncCustomerGroupAccessForSubscription(Subscription $subscription): void
    {
        if (!in_array($subscription->status, ['active', 'trialing', 'past_due'], true)) {
            return;
        }

        $plan = $subscription->plan;
        if (!$plan) {
            if ($subscription->stripe_price_id) {
                $plan = Plan::query()->where('stripe_price_id', $subscription->stripe_price_id)->first();
            }

            if (!$plan && is_string($subscription->plan_id) && Str::startsWith($subscription->plan_id, 'prod_')) {
                $plan = Plan::query()->where('stripe_product_id', $subscription->plan_id)->first();
            }
        }

        if (!$plan || !$plan->customer_group_id) {
            return;
        }

        if ($subscription->plan_db_id !== $plan->id) {
            $subscription->update(['plan_db_id' => $plan->id]);
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

    private function createStripeCustomer(Customer $customer): string
    {
        $stripeCustomer = $this->stripe->customers->create([
            'email' => $customer->email,
            'name' => $customer->full_name,
            'metadata' => [
                'customer_id' => $customer->id,
            ],
        ]);

        $customer->update(['stripe_customer_id' => $stripeCustomer->id]);

        return $stripeCustomer->id;
    }
}

