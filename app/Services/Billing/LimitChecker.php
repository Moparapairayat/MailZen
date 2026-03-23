<?php

namespace App\Services\Billing;

use App\Models\Customer;
use App\Models\Subscription;

class LimitChecker
{
    public function ensureWithinLimits(Customer $customer, string $metric, int $value): void
    {
        $subscription = $customer->subscriptions()->where('status', 'active')->latest()->first();

        if (!$subscription) {
            abort(402, 'No active subscription');
        }

        $limits = $subscription->limits ?? [];
        $limitValue = $limits[$metric] ?? null;

        if ($limitValue !== null && $value > $limitValue) {
            abort(429, 'Usage limit exceeded');
        }

        // Soft warning could be emitted by caller based on percentage.
    }

    public function usagePercentage(Subscription $subscription, string $metric, int $currentValue): ?float
    {
        $limits = $subscription->limits ?? [];
        $limitValue = $limits[$metric] ?? null;

        if (!$limitValue) {
            return null;
        }

        return ($currentValue / $limitValue) * 100;
    }
}

