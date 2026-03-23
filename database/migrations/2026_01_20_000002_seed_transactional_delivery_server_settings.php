<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Setting::firstOrCreate(
            ['key' => 'transactional_delivery_server_id'],
            [
                'category' => 'email',
                'value' => null,
                'type' => 'string',
                'description' => 'Default delivery server for system transactional emails (verification/password reset). Use "system" to use MAIL_* from .env.',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'verification_delivery_server_id'],
            [
                'category' => 'email',
                'value' => null,
                'type' => 'string',
                'description' => 'Delivery server for verification emails. Use "inherit" to use the transactional default, or "system" to use MAIL_* from .env.',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'password_reset_delivery_server_id'],
            [
                'category' => 'email',
                'value' => null,
                'type' => 'string',
                'description' => 'Delivery server for password reset emails. Use "inherit" to use the transactional default, or "system" to use MAIL_* from .env.',
                'is_public' => false,
            ]
        );
    }

    public function down(): void
    {
        Setting::whereIn('key', [
            'transactional_delivery_server_id',
            'verification_delivery_server_id',
            'password_reset_delivery_server_id',
        ])->delete();
    }
};
