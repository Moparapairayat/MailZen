<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Collection;

class SettingService
{
    /**
     * Get all settings grouped by category.
     */
    public function getSettingsByCategory(): Collection
    {
        return Setting::orderBy('category')
            ->orderBy('key')
            ->get()
            ->groupBy('category');
    }

    /**
     * Get settings for a specific category.
     */
    public function getSettingsForCategory(string $category): Collection
    {
        return Setting::where('category', $category)
            ->orderBy('key')
            ->get();
    }

    /**
     * Update multiple settings.
     */
    public function updateSettings(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $setting = Setting::where('key', $key)->first();
            if (!$setting) {
                continue;
            }

            $setting->value = $value;
            $setting->save();
        }
    }

    /**
     * Get a setting value.
     */
    public function get(string $key, $default = null)
    {
        return Setting::get($key, $default);
    }

    /**
     * Set a setting value.
     */
    public function set(string $key, $value, string $category = 'general', string $type = 'string'): Setting
    {
        return Setting::set($key, $value, $category, $type);
    }

    /**
     * Get available categories.
     */
    public function getCategories(): array
    {
        $categories = Setting::distinct()
            ->pluck('category')
            ->filter(fn ($c) => is_string($c) && trim($c) !== '')
            ->map(fn ($c) => trim((string) $c))
            ->reject(fn ($c) => strtolower((string) $c) === 'accessibility')
            ->unique()
            ->values()
            ->toArray();

        foreach (['templates', 'updates', 'changelogs'] as $extraCategory) {
            if (!in_array($extraCategory, $categories, true)) {
                $categories[] = $extraCategory;
            }
        }

        $preferred = ['general', 'appearance', 'templates', 'privacy', 'email', 'auth', 'cron', 'storage', 'billing', 'updates', 'changelogs'];
        $ordered = [];

        foreach ($preferred as $cat) {
            if (in_array($cat, $categories, true)) {
                $ordered[] = $cat;
            }
        }

        $remaining = array_values(array_diff($categories, $ordered));
        sort($remaining, SORT_NATURAL | SORT_FLAG_CASE);

        return array_values(array_merge($ordered, $remaining));
    }
}

