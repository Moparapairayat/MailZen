<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Setting::updateOrCreate(
            ['key' => 'billing_currency'],
            [
                'category' => 'billing',
                'value' => 'USD',
                'type' => 'string',
                'description' => 'Currency code used for pricing and billing (e.g., USD, EUR, BDT).',
                'is_public' => false,
            ]
        );
    }

    public function down(): void
    {
        Setting::whereIn('key', ['billing_currency'])->delete();
    }
};

