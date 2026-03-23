<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $items = [
            [
                'key' => 'openai_token_limit_monthly',
                'category' => 'ai',
                'value' => 0,
                'type' => 'integer',
                'description' => 'Monthly token limit for admin OpenAI usage (0 = unlimited).',
                'is_public' => false,
            ],
            [
                'key' => 'gemini_token_limit_monthly',
                'category' => 'ai',
                'value' => 0,
                'type' => 'integer',
                'description' => 'Monthly token limit for admin Gemini usage (0 = unlimited).',
                'is_public' => false,
            ],
            [
                'key' => 'claude_token_limit_monthly',
                'category' => 'ai',
                'value' => 0,
                'type' => 'integer',
                'description' => 'Monthly token limit for admin Claude usage (0 = unlimited).',
                'is_public' => false,
            ],
            [
                'key' => 'openai_cost_per_1k_cents',
                'category' => 'ai',
                'value' => 0,
                'type' => 'integer',
                'description' => 'Estimated cost per 1K tokens for OpenAI (in cents). Used for dashboard estimates.',
                'is_public' => false,
            ],
            [
                'key' => 'gemini_cost_per_1k_cents',
                'category' => 'ai',
                'value' => 0,
                'type' => 'integer',
                'description' => 'Estimated cost per 1K tokens for Gemini (in cents). Used for dashboard estimates.',
                'is_public' => false,
            ],
            [
                'key' => 'claude_cost_per_1k_cents',
                'category' => 'ai',
                'value' => 0,
                'type' => 'integer',
                'description' => 'Estimated cost per 1K tokens for Claude (in cents). Used for dashboard estimates.',
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
            'openai_token_limit_monthly',
            'gemini_token_limit_monthly',
            'claude_token_limit_monthly',
            'openai_cost_per_1k_cents',
            'gemini_cost_per_1k_cents',
            'claude_cost_per_1k_cents',
        ])->delete();
    }
};
