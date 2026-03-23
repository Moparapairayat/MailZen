<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Setting::firstOrCreate(
            ['key' => 'openai_api_key'],
            [
                'category' => 'ai',
                'value' => env('OPENAI_API_KEY'),
                'type' => 'string',
                'description' => 'Admin OpenAI API key (used when customers do not provide their own keys).',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'gemini_api_key'],
            [
                'category' => 'ai',
                'value' => env('GEMINI_API_KEY'),
                'type' => 'string',
                'description' => 'Admin Gemini API key (used when customers do not provide their own keys).',
                'is_public' => false,
            ]
        );
    }

    public function down(): void
    {
        Setting::whereIn('key', [
            'openai_api_key',
            'gemini_api_key',
        ])->delete();
    }
};
