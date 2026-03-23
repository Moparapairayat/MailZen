<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $openAiModels = [
            'gpt-4o',
            'gpt-4o-mini',
            'gpt-4-turbo',
            'gpt-4',
            'gpt-3.5-turbo',
        ];

        $geminiModels = [
            'gemini-1.5-flash',
            'gemini-1.5-pro',
            'gemini-1.0-pro',
        ];

        $claudeModels = [
            'claude-3-5-sonnet-20241022',
            'claude-3-5-haiku-20241022',
            'claude-3-opus-20240229',
        ];

        $items = [];

        foreach ($openAiModels as $m) {
            $items[] = [
                'key' => 'openai_cost_per_1k_cents_' . $this->modelKeySlug($m),
                'category' => 'ai',
                'value' => 0,
                'type' => 'integer',
                'description' => 'Estimated cost per 1K tokens for OpenAI model ' . $m . ' (in cents). Used for dashboard estimates.',
                'is_public' => false,
            ];
        }

        foreach ($geminiModels as $m) {
            $items[] = [
                'key' => 'gemini_cost_per_1k_cents_' . $this->modelKeySlug($m),
                'category' => 'ai',
                'value' => 0,
                'type' => 'integer',
                'description' => 'Estimated cost per 1K tokens for Gemini model ' . $m . ' (in cents). Used for dashboard estimates.',
                'is_public' => false,
            ];
        }

        foreach ($claudeModels as $m) {
            $items[] = [
                'key' => 'claude_cost_per_1k_cents_' . $this->modelKeySlug($m),
                'category' => 'ai',
                'value' => 0,
                'type' => 'integer',
                'description' => 'Estimated cost per 1K tokens for Claude model ' . $m . ' (in cents). Used for dashboard estimates.',
                'is_public' => false,
            ];
        }

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
        $prefixes = [
            'openai_cost_per_1k_cents_',
            'gemini_cost_per_1k_cents_',
            'claude_cost_per_1k_cents_',
        ];

        foreach ($prefixes as $prefix) {
            Setting::query()->where('key', 'like', $prefix . '%')->delete();
        }
    }

    private function modelKeySlug(string $model): string
    {
        $model = strtolower(trim($model));
        $model = preg_replace('/[^a-z0-9]+/', '_', $model) ?? $model;
        $model = trim($model, '_');
        return $model;
    }
};
