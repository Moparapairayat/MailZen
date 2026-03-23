<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Setting::firstOrCreate(
            ['key' => 'app_version'],
            [
                'category' => 'updates',
                'value' => env('APP_VERSION', '1.0.0'),
                'type' => 'string',
                'description' => 'Installed application version.',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'update_api_base_url'],
            [
                'category' => 'updates',
                'value' => env('UPDATE_API_BASE_URL', 'https://api.getmailpurse.com'),
                'type' => 'url',
                'description' => 'Update server base URL (WordPress site).',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'update_product_id'],
            [
                'category' => 'updates',
                'value' => env('UPDATE_PRODUCT_ID'),
                'type' => 'integer',
                'description' => 'Update server product ID.',
                'is_public' => false,
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'update_product_name'],
            [
                'category' => 'updates',
                'value' => 'MailZen',
                'type' => 'string',
                'description' => 'Product name (must match update server product name).',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'update_product_secret'],
            [
                'category' => 'updates',
                'value' => env('UPDATE_PRODUCT_SECRET'),
                'type' => 'string',
                'description' => 'Product secret used by update server.',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'update_license_key'],
            [
                'category' => 'updates',
                'value' => env('UPDATE_LICENSE_KEY'),
                'type' => 'string',
                'description' => 'Purchase code / license key for updates.',
                'is_public' => false,
            ]
        );
    }

    public function down(): void
    {
        Setting::whereIn('key', [
            'app_version',
            'update_api_base_url',
            'update_product_id',
            'update_product_name',
            'update_product_secret',
            'update_license_key',
        ])->delete();
    }
};
