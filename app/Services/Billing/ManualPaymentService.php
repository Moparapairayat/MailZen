<?php

namespace App\Services\Billing;

use App\Models\Customer;
use App\Models\ManualPayment;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;

class ManualPaymentService implements PaymentProviderInterface
{
    public function createCheckoutSession(Customer $customer, Plan $plan, array $options = []): string
    {
        $localSubscriptionId = (int) ($options['local_subscription_id'] ?? 0);
        if ($localSubscriptionId <= 0) {
            throw new \RuntimeException('Unable to create manual payment: missing subscription.');
        }

        $subscription = Subscription::query()->with('customer')->findOrFail($localSubscriptionId);

        if ((int) $subscription->customer_id !== (int) $customer->id) {
            throw new \RuntimeException('Unable to create manual payment.');
        }

        try {
            ManualPayment::query()->firstOrCreate(
                ['subscription_id' => $subscription->id],
                [
                    'customer_id' => $customer->id,
                    'plan_id' => $plan->id,
                    'amount' => (float) $plan->price,
                    'currency' => (string) ($plan->currency ?: ($customer->currency ?: 'USD')),
                    'status' => 'initiated',
                ]
            );
        } catch (\Throwable $e) {
            \Log::error('Failed to create manual payment record', [
                'customer_id' => $customer->id,
                'subscription_id' => $subscription->id,
                'plan_id' => $plan->id,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException('Unable to create manual payment.');
        }

        return route('customer.billing.manual.show', $subscription, false);
    }

    public function createCustomerPortal(Customer $customer, array $options = []): string
    {
        return (string) ($options['return_url'] ?? route('customer.billing.index'));
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
        return;
    }
}
