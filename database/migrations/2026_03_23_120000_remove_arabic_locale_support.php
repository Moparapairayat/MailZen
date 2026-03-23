<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->cleanupSettings();
        $this->cleanupUserLanguages();
        $this->cleanupCustomerLanguages();
        $this->cleanupLegacyTranslationLocales();
    }

    public function down(): void
    {
        //
    }

    private function cleanupSettings(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('settings')) {
            return;
        }

        $settings = DB::table('settings')
            ->whereIn('key', ['site_language', 'translation_active_locales', 'translation_locale_meta'])
            ->get(['id', 'key', 'value']);

        foreach ($settings as $setting) {
            $key = is_string($setting->key ?? null) ? $setting->key : '';
            $value = is_string($setting->value ?? null) ? $setting->value : '';

            if ($key === 'site_language' && $this->normalizeLocaleBase($value) === 'ar') {
                DB::table('settings')->where('key', 'site_language')->update(['value' => 'en']);
                continue;
            }

            if ($key === 'translation_active_locales') {
                $decoded = json_decode($value, true);
                if (!is_array($decoded)) {
                    continue;
                }

                $clean = [];
                foreach ($decoded as $locale) {
                    if (!is_string($locale) || trim($locale) === '') {
                        continue;
                    }

                    if ($this->normalizeLocaleBase($locale) === 'ar') {
                        continue;
                    }

                    $clean[] = trim($locale);
                }

                $clean = array_values(array_unique($clean));
                if ($clean === []) {
                    $clean = ['en'];
                }

                DB::table('settings')->where('id', $setting->id)->update([
                    'value' => json_encode($clean, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);

                continue;
            }

            if ($key === 'translation_locale_meta') {
                $decoded = json_decode($value, true);
                if (!is_array($decoded)) {
                    continue;
                }

                foreach (array_keys($decoded) as $locale) {
                    if ($this->normalizeLocaleBase((string) $locale) === 'ar') {
                        unset($decoded[$locale]);
                    }
                }

                DB::table('settings')->where('id', $setting->id)->update([
                    'value' => json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);
            }
        }
    }

    private function cleanupUserLanguages(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('users')) {
            return;
        }

        $users = DB::table('users')->select('id', 'language')->get();

        foreach ($users as $user) {
            $language = is_string($user->language ?? null) ? trim($user->language) : '';
            if ($this->normalizeLocaleBase($language) !== 'ar') {
                continue;
            }

            DB::table('users')->where('id', $user->id)->update(['language' => 'en']);
        }
    }

    private function cleanupCustomerLanguages(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('customers')) {
            return;
        }

        $customers = DB::table('customers')->select('id', 'language')->get();

        foreach ($customers as $customer) {
            $language = is_string($customer->language ?? null) ? trim($customer->language) : '';
            if ($this->normalizeLocaleBase($language) !== 'ar') {
                continue;
            }

            DB::table('customers')->where('id', $customer->id)->update(['language' => 'en']);
        }
    }

    private function cleanupLegacyTranslationLocales(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('translation_locales')) {
            return;
        }

        $locales = DB::table('translation_locales')->select('id', 'code', 'name')->get();

        foreach ($locales as $locale) {
            $code = is_string($locale->code ?? null) ? trim($locale->code) : '';
            $name = is_string($locale->name ?? null) ? trim($locale->name) : '';

            if ($this->normalizeLocaleBase($code) !== 'ar' && strtolower($name) !== 'arabic') {
                continue;
            }

            DB::table('translation_locales')->where('id', $locale->id)->delete();
        }
    }

    private function normalizeLocaleBase(?string $locale): string
    {
        $locale = is_string($locale) ? trim($locale) : '';
        $locale = strtolower($locale);

        if ($locale === '') {
            return '';
        }

        $locale = str_replace('_', '-', $locale);
        $parts = explode('-', $locale);

        return trim((string) ($parts[0] ?? ''));
    }
};
