<?php

namespace App\Http\Middleware;

use App\Models\Plan;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerHasGroupAccess
{
    public function handle(Request $request, Closure $next, string $settingPath): Response
    {
        $customer = Auth::guard('customer')->user();

        if (!$customer) {
            Auth::guard('customer')->logout();
            return redirect()->route('login');
        }

        $subscription = $customer->subscriptions()->latest()->first();

        if (
            $subscription &&
            in_array($subscription->status, ['active', 'trialing', 'past_due'], true)
        ) {
            $plan = $subscription->plan;

            if (!$plan && $subscription->stripe_price_id) {
                $plan = Plan::query()->where('stripe_price_id', $subscription->stripe_price_id)->first();
            }

            if ($plan && $plan->customer_group_id) {
                $customer->customerGroups()->syncWithoutDetaching([(int) $plan->customer_group_id]);
            }
        }

        $allowedBySettings = $customer->groupAllows($settingPath);
        $allowedByLegacyPermissions = $customer->hasPermission($settingPath);
        $allowedBySubscriptionFeatures = $subscription
            && in_array($subscription->status, ['active', 'trialing', 'past_due'], true)
            && in_array(
                $settingPath,
                (array) ($subscription->features ?? []),
                true
            );

        if (!$allowedBySettings && !$allowedByLegacyPermissions && !$allowedBySubscriptionFeatures) {
            $message = 'You do not have access to this feature.';

            $customMessage = $customer->groupSetting("messages.access.{$settingPath}");
            if (!is_string($customMessage) || trim($customMessage) === '') {
                $customMessage = $customer->groupSetting('messages.access.default');
            }

            if (is_string($customMessage) && trim($customMessage) !== '') {
                $message = $customMessage;
            }

            abort(403, $message);
        }

        return $next($request);
    }
}
