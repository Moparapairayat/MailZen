<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class TranslationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('locale.direction', function () {
            return new class {
                private array $rtlLocales = [
                    'dv',
                    'fa',
                    'he',
                    'ku',
                    'ps',
                    'syr',
                    'ug',
                    'ur',
                    'yi',
                ];

                public function isRtl(?string $locale = null): bool
                {
                    $locale = $this->normalizeLocale($locale ?? app()->getLocale());

                    return in_array($locale, $this->rtlLocales, true);
                }

                public function dir(?string $locale = null): string
                {
                    return $this->isRtl($locale) ? 'rtl' : 'ltr';
                }

                private function normalizeLocale(?string $locale): string
                {
                    $locale = is_string($locale) ? trim($locale) : '';
                    $locale = strtolower($locale);

                    if ($locale === '') {
                        return 'en';
                    }

                    $locale = str_replace('_', '-', $locale);
                    $parts = explode('-', $locale);

                    return trim((string) ($parts[0] ?? 'en')) ?: 'en';
                }
            };
        });
    }
}
