<?php

namespace App\Services\Billing;

use App\Models\Customer;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;

class PaystackPaymentService implements PaymentProviderInterface
{
    public function createCheckoutSession(Customer $customer, Plan $plan, array $options = []): string
    {
        throw new \RuntimeException('Paystack provider not implemented yet');
    }

    public function createCustomerPortal(Customer $customer, array $options = []): string
    {
        throw new \RuntimeException('Paystack provider not implemented yet');
    }

    public function cancelAtPeriodEnd(Subscription $subscription): void
    {
        throw new \RuntimeException('Paystack provider not implemented yet');
    }

    public function resume(Subscription $subscription): void
    {
        throw new \RuntimeException('Paystack provider not implemented yet');
    }

    public function handleWebhook(Request $request): void
    {
        throw new \RuntimeException('Paystack provider not implemented yet');
    }
}
