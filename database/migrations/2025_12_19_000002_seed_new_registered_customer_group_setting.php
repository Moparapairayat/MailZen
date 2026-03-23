<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Setting::firstOrCreate(
            ['key' => 'new_registered_customer_group_id'],
            [
                'category' => 'account',
                'value' => null,
                'type' => 'integer',
                'description' => 'Customer group for newly registered customers (applies to both email/password and Google sign-ups).',
                'is_public' => false,
            ]
        );
    }

    public function down(): void
    {
        Setting::where('key', 'new_registered_customer_group_id')->delete();
    }
};
