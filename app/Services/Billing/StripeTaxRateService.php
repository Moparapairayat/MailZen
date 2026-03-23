<?php

namespace App\Services\Billing;

use App\Models\Setting;
use Stripe\StripeClient;

class StripeTaxRateService
{
    public function __construct(private readonly StripeClient $stripe)
    {
    }

    public function getOrCreateTaxRateId(array $taxRule): ?string
    {
        $type = (string) ($taxRule['type'] ?? '');
        $percentage = $taxRule['percentage'] ?? null;

        if ($type === '' || !is_numeric($percentage)) {
            return null;
        }

        $percentage = (float) $percentage;
        if ($percentage <= 0) {
            return null;
        }

        $country = isset($taxRule['country']) ? strtoupper(trim((string) $taxRule['country'])) : '';
        $state = isset($taxRule['state']) ? strtoupper(trim((string) $taxRule['state'])) : '';

        $cacheKey = $type . ':' . ($country !== '' ? $country : 'XX') . ':' . ($state !== '' ? $state : '-') . ':' . number_format($percentage, 4, '.', '');

        $cache = Setting::get('stripe_tax_rate_cache', []);
        if (!is_array($cache)) {
            $cache = [];
        }

        $existing = $cache[$cacheKey] ?? null;
        if (is_string($existing) && trim($existing) !== '') {
            return $existing;
        }

        $displayName = (string) ($taxRule['display_name'] ?? 'Tax');
        $inclusive = (bool) ($taxRule['inclusive'] ?? false);

        $payload = [
            'display_name' => $displayName,
            'inclusive' => $inclusive,
            'percentage' => $percentage,
        ];

        if ($country !== '' && strlen($country) === 2) {
            $payload['country'] = $country;
        }

        if ($country === 'US' && $state !== '') {
            $payload['state'] = $state;
        }

        $taxRate = $this->stripe->taxRates->create($payload);

        $cache[$cacheKey] = $taxRate->id;
        Setting::set('stripe_tax_rate_cache', $cache, 'tax', 'array');

        return $taxRate->id;
    }
}
