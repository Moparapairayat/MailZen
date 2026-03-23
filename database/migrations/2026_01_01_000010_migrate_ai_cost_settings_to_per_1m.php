<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $settings = Setting::query()
            ->where('category', 'ai')
            ->where('key', 'like', '%_cost_per_1k_cents%')
            ->get();

        foreach ($settings as $setting) {
            $key = is_string($setting->key) ? (string) $setting->key : '';
            if ($key === '') {
                continue;
            }

            if (@preg_match('//u', $key) !== 1) {
                continue;
            }

            if (!preg_match('/^(openai|gemini|claude)_cost_per_1k_cents(?:_[a-z0-9_]+)?$/', $key)) {
                continue;
            }

            $newKey = str_replace('_cost_per_1k_cents', '_cost_per_1m_cents', $key);
            if ($newKey === $key) {
                continue;
            }

            if (@preg_match('//u', $newKey) !== 1) {
                continue;
            }

            if (!preg_match('/^(openai|gemini|claude)_cost_per_1m_cents(?:_[a-z0-9_]+)?$/', $newKey)) {
                continue;
            }

            $oldVal = is_numeric($setting->value) ? (int) $setting->value : 0;
            $migrated = $oldVal > 0 ? $oldVal * 1000 : 0;

            $desc = is_string($setting->description) ? (string) $setting->description : null;
            if (is_string($desc) && @preg_match('//u', $desc) !== 1) {
                $desc = null;
            }
            if (is_string($desc)) {
                $desc = str_replace('1K tokens', '1M tokens', $desc);
                $desc = str_replace('per 1K', 'per 1M', $desc);
            }

            $existing = Setting::query()->where('key', $newKey)->first();

            if (!$existing) {
                Setting::firstOrCreate(
                    ['key' => $newKey],
                    [
                        'category' => is_string($setting->category) && @preg_match('//u', (string) $setting->category) === 1 ? (string) $setting->category : 'ai',
                        'value' => $migrated,
                        'type' => is_string($setting->type) && @preg_match('//u', (string) $setting->type) === 1 ? (string) $setting->type : 'integer',
                        'description' => $desc,
                        'is_public' => (bool) ($setting->is_public ?? false),
                    ]
                );

                continue;
            }

            $dirty = false;

            if (((int) $existing->value) <= 0 && $migrated > 0) {
                $existing->value = $migrated;
                $dirty = true;
            }

            if (is_string($desc) && trim($desc) !== '' && $existing->description !== $desc) {
                $existing->description = $desc;
                $dirty = true;
            }

            if ($dirty) {
                $existing->save();
            }
        }

        Setting::query()
            ->where('category', 'ai')
            ->where('key', 'like', '%_cost_per_1k_cents%')
            ->delete();
    }

    public function down(): void
    {
    }
};
