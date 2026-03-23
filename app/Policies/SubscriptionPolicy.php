<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\Subscription;

class SubscriptionPolicy
{
    public function manage(Customer $customer, Subscription $subscription): bool
    {
        return $subscription->customer_id === $customer->id && !in_array($subscription->status, ['cancelled', 'expired']);
    }
}

