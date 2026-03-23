<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Setting::where('key', 'billing_tax_rate')->delete();
    }

    public function down(): void
    {
        Setting::firstOrCreate(
            ['key' => 'billing_tax_rate'],
            [
                'category' => 'billing',
                'value' => '0',
                'type' => 'integer',
                'description' => 'Tax rate percentage applied to subscription plans (e.g., 20 for 20%).',
                'is_public' => false,
            ]
        );
    }
};
