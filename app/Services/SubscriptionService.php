<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\Customer;
use App\Models\Setting;
use Illuminate\Pagination\LengthAwarePaginator;

class SubscriptionService
{
    /**
     * Get paginated list of subscriptions for a customer.
     */
    public function getPaginated(Customer $customer, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Subscription::where('customer_id', $customer->id);

        // Apply filters
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('plan_name', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new subscription.
     */
    public function create(Customer $customer, array $data): Subscription
    {
        return Subscription::create([
            'customer_id' => $customer->id,
            'plan_id' => $data['plan_id'],
            'plan_name' => $data['plan_name'],
            'status' => $data['status'] ?? 'active',
            'billing_cycle' => $data['billing_cycle'] ?? 'monthly',
            'price' => $data['price'] ?? 0,
            'currency' => $data['currency'] ?? Setting::get('billing_currency', 'USD'),
            'starts_at' => $data['starts_at'] ?? now(),
            'ends_at' => $data['ends_at'] ?? null,
            'trial_ends_at' => $data['trial_ends_at'] ?? null,
            'features' => $data['features'] ?? [],
            'limits' => $data['limits'] ?? [],
            'payment_method' => $data['payment_method'] ?? null,
            'payment_gateway' => $data['payment_gateway'] ?? null,
            'auto_renew' => $data['auto_renew'] ?? true,
        ]);
    }

    /**
     * Update an existing subscription.
     */
    public function update(Subscription $subscription, array $data): Subscription
    {
        $subscription->update($data);
        return $subscription->fresh();
    }

    /**
     * Delete a subscription.
     */
    public function delete(Subscription $subscription): bool
    {
        return $subscription->delete();
    }

    /**
     * Cancel a subscription.
     */
    public function cancel(Subscription $subscription, string $reason = null): Subscription
    {
        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
            'auto_renew' => false,
        ]);

        return $subscription->fresh();
    }

    /**
     * Renew a subscription.
     */
    public function renew(Subscription $subscription): Subscription
    {
        $subscription->increment('renewal_count');
        
        // Calculate new end date based on billing cycle
        $endsAt = $subscription->ends_at ?? now();
        if ($subscription->billing_cycle === 'monthly') {
            $endsAt = $endsAt->addMonth();
        } else {
            $endsAt = $endsAt->addYear();
        }

        $subscription->update([
            'status' => 'active',
            'ends_at' => $endsAt,
        ]);

        return $subscription->fresh();
    }
}

