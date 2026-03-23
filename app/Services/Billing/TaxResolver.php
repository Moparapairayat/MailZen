<?php

namespace App\Services\Billing;

use App\Models\Customer;
use App\Models\Setting;

class TaxResolver
{
    public function resolveForCustomer(Customer $customer): ?array
    {
        $address = is_array($customer->billing_address) ? $customer->billing_address : [];

        $country = $this->normalizeCountry((string) ($address['country'] ?? $customer->country ?? ''));
        $state = $this->normalizeState((string) ($address['state'] ?? $customer->state ?? ''));

        if ($country === '') {
            return null;
        }

        if ($country === 'US') {
            $rates = Setting::get('us_sales_tax_rates', []);
            if (!is_array($rates)) {
                $rates = [];
            }

            $rate = null;
            if ($state !== '') {
                $rate = $rates[$state] ?? $rates[strtoupper($state)] ?? null;
            }

            if (is_numeric($rate)) {
                $percentage = (float) $rate;
                if ($percentage > 0) {
                    return [
                        'type' => 'sales_tax',
                        'percentage' => $percentage,
                        'country' => 'US',
                        'state' => $state !== '' ? $state : null,
                        'display_name' => 'Sales Tax',
                        'inclusive' => false,
                    ];
                }
            }

            return null;
        }

        $vatEnabled = (bool) Setting::get('vat_enabled', false);
        $vatRate = Setting::get('vat_rate', null);

        if (!$vatEnabled || !is_numeric($vatRate)) {
            return null;
        }

        $percentage = (float) $vatRate;
        if ($percentage <= 0) {
            return null;
        }

        return [
            'type' => 'vat',
            'percentage' => $percentage,
            'country' => $country,
            'state' => null,
            'display_name' => 'VAT',
            'inclusive' => false,
        ];
    }

    private function normalizeCountry(string $country): string
    {
        $country = strtoupper(trim($country));
        if ($country === '') {
            return '';
        }

        if (in_array($country, ['US', 'USA', 'UNITED STATES', 'UNITED STATES OF AMERICA'], true)) {
            return 'US';
        }

        if (strlen($country) === 2) {
            return $country;
        }

        return '';
    }

    private function normalizeState(string $state): string
    {
        $state = strtoupper(trim($state));
        if ($state === '') {
            return '';
        }

        return $state;
    }
}
