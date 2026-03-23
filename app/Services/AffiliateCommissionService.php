<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\AffiliateCommission;
use App\Models\Setting;
use App\Models\Subscription;

class AffiliateCommissionService
{
    public function createCommissionForSubscriptionPayment(
        Subscription $subscription,
        string $provider,
        string $eventKey,
        ?float $baseAmount = null,
        ?string $currency = null,
        ?int $manualPaymentId = null
    ): ?AffiliateCommission {
        $enabled = filter_var(Setting::get('affiliate_enabled', false), FILTER_VALIDATE_BOOLEAN);
        if (!$enabled) {
            return null;
        }

        $eventKey = trim($eventKey);
        if ($eventKey === '') {
            return null;
        }

        $existing = AffiliateCommission::query()->where('event_key', $eventKey)->first();
        if ($existing) {
            return $existing;
        }

        $customer = $subscription->customer;
        if (!$customer || !$customer->referred_by_affiliate_id) {
            return null;
        }

        $affiliate = Affiliate::query()
            ->whereKey((int) $customer->referred_by_affiliate_id)
            ->where('status', 'approved')
            ->first();

        if (!$affiliate) {
            return null;
        }

        $scope = Setting::get('affiliate_commission_scope', 'first_payment');
        $scope = is_string($scope) ? trim($scope) : 'first_payment';

        if ($scope === 'first_payment') {
            $alreadyAwarded = AffiliateCommission::query()
                ->where('subscription_id', $subscription->id)
                ->exists();

            if ($alreadyAwarded) {
                return null;
            }
        }

        if ($baseAmount === null) {
            $baseAmount = is_numeric($subscription->price) ? (float) $subscription->price : 0.0;
        }

        if (!is_finite($baseAmount) || $baseAmount < 0) {
            $baseAmount = 0.0;
        }

        if (!is_string($currency) || trim($currency) === '') {
            $currency = $subscription->currency;
        }

        $currency = is_string($currency) ? strtoupper(trim($currency)) : null;

        $commissionType = Setting::get('affiliate_commission_type', 'percent');
        $commissionType = is_string($commissionType) ? trim($commissionType) : 'percent';

        $commissionRate = null;
        $commissionAmount = 0.0;

        if ($commissionType === 'fixed') {
            $fixed = Setting::get('affiliate_commission_fixed_amount', '10.00');
            $fixed = is_string($fixed) ? trim($fixed) : (string) $fixed;
            $fixedAmount = is_numeric($fixed) ? (float) $fixed : 0.0;

            if (!is_finite($fixedAmount) || $fixedAmount < 0) {
                $fixedAmount = 0.0;
            }

            $commissionAmount = round($fixedAmount, 2);
        } else {
            $percent = $affiliate->commission_rate_percent !== null
                ? (float) $affiliate->commission_rate_percent
                : (float) (Setting::get('affiliate_commission_rate_percent', 20) ?? 20);
            $percent = is_numeric($percent) ? (float) $percent : 0.0;

            if (!is_finite($percent) || $percent < 0) {
                $percent = 0.0;
            }

            $commissionRate = $percent;
            $commissionAmount = round($baseAmount * ($percent / 100.0), 2);
            $commissionType = 'percent';
        }

        return AffiliateCommission::create([
            'affiliate_id' => $affiliate->id,
            'referred_customer_id' => $customer->id,
            'subscription_id' => $subscription->id,
            'manual_payment_id' => $manualPaymentId,
            'provider' => $provider,
            'event_key' => $eventKey,
            'base_amount' => round($baseAmount, 2),
            'base_currency' => $currency,
            'commission_type' => $commissionType,
            'commission_rate' => $commissionRate,
            'commission_amount' => $commissionAmount,
            'commission_currency' => $currency,
            'status' => 'pending',
        ]);
    }
}
