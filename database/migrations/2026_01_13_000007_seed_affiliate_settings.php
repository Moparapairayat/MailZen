<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Setting::firstOrCreate(
            ['key' => 'affiliate_enabled'],
            [
                'category' => 'affiliate',
                'value' => 1,
                'type' => 'boolean',
                'description' => 'Enable affiliate programme and referral tracking.',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'affiliate_cookie_days'],
            [
                'category' => 'affiliate',
                'value' => 30,
                'type' => 'integer',
                'description' => 'Referral cookie duration in days.',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'affiliate_commission_scope'],
            [
                'category' => 'affiliate',
                'value' => 'first_payment',
                'type' => 'string',
                'description' => 'Commission scope: first_payment or recurring.',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'affiliate_commission_type'],
            [
                'category' => 'affiliate',
                'value' => 'percent',
                'type' => 'string',
                'description' => 'Commission type: percent or fixed.',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'affiliate_commission_rate_percent'],
            [
                'category' => 'affiliate',
                'value' => 20,
                'type' => 'integer',
                'description' => 'Commission rate (percent) when commission type is percent.',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'affiliate_commission_fixed_amount'],
            [
                'category' => 'affiliate',
                'value' => '10.00',
                'type' => 'string',
                'description' => 'Commission amount when commission type is fixed (stored as string to allow decimals).',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'affiliate_min_payout_amount'],
            [
                'category' => 'affiliate',
                'value' => 50,
                'type' => 'integer',
                'description' => 'Minimum payout amount before an affiliate can request withdrawal.',
                'is_public' => false,
            ]
        );
    }

    public function down(): void
    {
        Setting::whereIn('key', [
            'affiliate_enabled',
            'affiliate_cookie_days',
            'affiliate_commission_scope',
            'affiliate_commission_type',
            'affiliate_commission_rate_percent',
            'affiliate_commission_fixed_amount',
            'affiliate_min_payout_amount',
        ])->delete();
    }
};
