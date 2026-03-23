<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Setting::firstOrCreate(
            ['key' => 'google_enabled'],
            [
                'category' => 'auth',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Enable Google login/register.',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'google_client_id'],
            [
                'category' => 'auth',
                'value' => env('GOOGLE_CLIENT_ID'),
                'type' => 'string',
                'description' => 'Google OAuth client ID.',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'google_client_secret'],
            [
                'category' => 'auth',
                'value' => env('GOOGLE_CLIENT_SECRET'),
                'type' => 'string',
                'description' => 'Google OAuth client secret. Leave blank when saving to keep existing.',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'google_redirect_uri'],
            [
                'category' => 'auth',
                'value' => env('GOOGLE_REDIRECT_URI'),
                'type' => 'url',
                'description' => 'Google OAuth redirect URI.',
                'is_public' => false,
            ]
        );
    }

    public function down(): void
    {
        Setting::whereIn('key', [
            'google_enabled',
            'google_client_id',
            'google_client_secret',
            'google_redirect_uri',
        ])->delete();
    }
};
