<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $items = [
            [
                'key' => 'openai_token_limit_daily',
                'category' => 'ai',
                'value' => 0,
                'type' => 'integer',
                'description' => 'Daily token limit for admin OpenAI usage (0 = unlimited).',
                'is_public' => false,
            ],
            [
                'key' => 'gemini_token_limit_daily',
                'category' => 'ai',
                'value' => 0,
                'type' => 'integer',
                'description' => 'Daily token limit for admin Gemini usage (0 = unlimited).',
                'is_public' => false,
            ],
            [
                'key' => 'claude_token_limit_daily',
                'category' => 'ai',
                'value' => 0,
                'type' => 'integer',
                'description' => 'Daily token limit for admin Claude usage (0 = unlimited).',
                'is_public' => false,
            ],
        ];

        foreach ($items as $row) {
            Setting::firstOrCreate(
                ['key' => $row['key']],
                [
                    'category' => $row['category'],
                    'value' => $row['value'],
                    'type' => $row['type'],
                    'description' => $row['description'],
                    'is_public' => $row['is_public'],
                ]
            );
        }
    }

    public function down(): void
    {
        Setting::whereIn('key', [
            'openai_token_limit_daily',
            'gemini_token_limit_daily',
            'claude_token_limit_daily',
        ])->delete();
    }
};
