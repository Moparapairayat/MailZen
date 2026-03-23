<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Setting::firstOrCreate(
            ['key' => 'new_register_requires_email_verification'],
            [
                'category' => 'account',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Require email verification for new registrations.',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'default_customer_group_id'],
            [
                'category' => 'account',
                'value' => null,
                'type' => 'integer',
                'description' => 'Default customer group for newly registered customers.',
                'is_public' => false,
            ]
        );

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
        Setting::whereIn('key', [
            'new_register_requires_email_verification',
            'default_customer_group_id',
            'new_registered_customer_group_id',
        ])->delete();
    }
};
