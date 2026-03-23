<?php

namespace App\Http\Middleware;

use App\Models\Customer;
use App\Services\Billing\UsageService;
use Closure;
use Illuminate\Http\Request;

class SubscriptionGate
{
    public function __construct(private readonly UsageService $usageService)
    {
    }

    public function handle(Request $request, Closure $next, string $metric = null, int $amount = 0)
    {
        $customer = $request->user('customer');

        if (!$customer) {
            $tokenUser = $request->user('sanctum');
            if ($tokenUser instanceof Customer) {
                $customer = $tokenUser;
            }
        }

        if (!$customer) {
            abort(401);
        }

        $subscription = $customer->subscriptions()->latest()->first();

        if (!$subscription || !in_array($subscription->status, ['active', 'trialing', 'past_due'])) {
            abort(402, 'Subscription inactive');
        }

        if ($subscription->isOnTrial() === false && $subscription->isExpired()) {
            abort(402, 'Subscription expired');
        }

        if ($metric) {
            $usage = $this->usageService->getUsage($customer);
            $current = $usage[$metric] ?? 0;
            $limit = $subscription->limits[$metric] ?? null;

            if ($limit !== null && ($current + $amount) > $limit) {
                abort(429, 'Usage limit exceeded');
            }

            if ($limit && ($current / $limit) >= 0.8) {
                $request->attributes->set('usage_warning', sprintf('%s usage above 80%%', $metric));
            }
        }

        return $next($request);
    }
}

