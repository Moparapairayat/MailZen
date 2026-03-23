<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Setting::firstOrCreate(
            ['key' => 'site_title'],
            [
                'category' => 'general',
                'value' => env('APP_NAME', 'MailZen'),
                'type' => 'string',
                'description' => 'Site title (used in browser title).',
                'is_public' => true,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'meta_keywords'],
            [
                'category' => 'general',
                'value' => null,
                'type' => 'string',
                'description' => 'Site meta keywords (comma-separated).',
                'is_public' => true,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'meta_description'],
            [
                'category' => 'general',
                'value' => null,
                'type' => 'string',
                'description' => 'Site meta description.',
                'is_public' => true,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'site_meta'],
            [
                'category' => 'general',
                'value' => '<meta name="keywords" content="MailZen, email marketing, automation">',
                'type' => 'string',
                'description' => 'Additional meta tags to inject into the <head> (HTML).',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'site_favicon'],
            [
                'category' => 'general',
                'value' => null,
                'type' => 'string',
                'description' => 'Site favicon path (stored in branding disk).',
                'is_public' => true,
            ]
        );
    }

    public function down(): void
    {
        Setting::whereIn('key', [
            'site_title',
            'meta_keywords',
            'meta_description',
            'site_meta',
            'site_favicon',
        ])->delete();
    }
};
