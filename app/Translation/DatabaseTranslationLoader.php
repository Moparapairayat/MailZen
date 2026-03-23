<?php

namespace App\Translation;

use App\Models\TranslationLine;
use App\Models\TranslationLocale;
use Illuminate\Contracts\Translation\Loader as LoaderContract;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class DatabaseTranslationLoader implements LoaderContract
{
    public function __construct(
        protected LoaderContract $fallbackLoader
    ) {}

    public function load($locale, $group, $namespace = null)
    {
        $fallback = $this->fallbackLoader->load($locale, $group, $namespace);

        if ($namespace !== null && $namespace !== '*') {
            return $fallback;
        }

        $db = $this->loadFromDatabase((string) $locale, (string) $group);

        if (!is_array($db) || $db === []) {
            return $fallback;
        }

        return array_replace_recursive($fallback, $db);
    }

    public function addNamespace($namespace, $hint)
    {
        $this->fallbackLoader->addNamespace($namespace, $hint);
    }

    public function addJsonPath($path)
    {
        $this->fallbackLoader->addJsonPath($path);
    }

    public function namespaces()
    {
        return $this->fallbackLoader->namespaces();
    }

    protected function loadFromDatabase(string $localeCode, string $group): array
    {
        $group = trim($group);
        if ($group === '') {
            $group = '*';
        }

        $cacheKey = $this->cacheKey($localeCode, $group);

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($localeCode, $group) {
            try {
                $locale = TranslationLocale::query()
                    ->where('code', $localeCode)
                    ->where('is_active', true)
                    ->first();

                if (!$locale) {
                    return [];
                }

                $lines = TranslationLine::query()
                    ->where('translation_locale_id', $locale->id)
                    ->where('group', $group)
                    ->get(['key', 'text']);

                if ($lines->isEmpty()) {
                    return [];
                }

                $payload = [];

                foreach ($lines as $line) {
                    $key = (string) $line->key;
                    $text = $line->text;

                    if ($text === null || (is_string($text) && trim($text) === '')) {
                        continue;
                    }

                    if ($group === '*') {
                        $payload[$key] = $text;
                        continue;
                    }

                    Arr::set($payload, $key, $text);
                }

                return $payload;
            } catch (\Throwable $e) {
                return [];
            }
        });
    }

    protected function cacheKey(string $localeCode, string $group): string
    {
        $version = null;

        try {
            $version = TranslationLocale::query()
                ->where('code', $localeCode)
                ->value('updated_at');
        } catch (\Throwable $e) {
            $version = null;
        }

        $v = $version ? (string) $version : '0';

        return 'db_translations:' . $localeCode . ':' . $group . ':' . $v;
    }
}
