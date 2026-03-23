<?php

namespace App\Services\Billing;

use App\Models\Customer;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;

interface PaymentProviderInterface
{
    public function createCheckoutSession(Customer $customer, Plan $plan, array $options = []): string;

    public function createCustomerPortal(Customer $customer, array $options = []): string;

    public function cancelAtPeriodEnd(Subscription $subscription): void;

    public function resume(Subscription $subscription): void;

    public function handleWebhook(Request $request): void;
}

