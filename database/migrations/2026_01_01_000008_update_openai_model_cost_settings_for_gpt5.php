<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $oldOpenAiPrefixes = [
            'openai_cost_per_1k_cents_gpt_4o',
            'openai_cost_per_1k_cents_gpt_4o_mini',
            'openai_cost_per_1k_cents_gpt_4_turbo',
            'openai_cost_per_1k_cents_gpt_4',
            'openai_cost_per_1k_cents_gpt_3_5_turbo',
        ];

        Setting::query()->whereIn('key', $oldOpenAiPrefixes)->delete();

        $openAiModels = [
            'gpt-5',
            'gpt-5-mini',
            'gpt-5.2',
            'gpt-5-nano',
            'gpt-4.1',
        ];

        foreach ($openAiModels as $m) {
            $key = 'openai_cost_per_1k_cents_' . $this->modelKeySlug($m);

            Setting::firstOrCreate(
                ['key' => $key],
                [
                    'category' => 'ai',
                    'value' => 0,
                    'type' => 'integer',
                    'description' => 'Estimated cost per 1K tokens for OpenAI model ' . $m . ' (in cents). Used for dashboard estimates.',
                    'is_public' => false,
                ]
            );
        }
    }

    public function down(): void
    {
        // Keep any user-configured keys; do not delete on rollback.
    }

    private function modelKeySlug(string $model): string
    {
        $model = strtolower(trim($model));
        $model = preg_replace('/[^a-z0-9]+/', '_', $model) ?? $model;
        $model = trim($model, '_');
        return $model;
    }
};
