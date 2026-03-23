<?php

namespace App\Translation;

use Illuminate\Support\Arr;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class LocaleJsonService
{
    private function blockedLocaleBases(): array
    {
        return ['ar'];
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

    private function isBlockedLocale(?string $locale): bool
    {
        $base = $this->normalizeLocaleBase($locale);

        return $base !== '' && in_array($base, $this->blockedLocaleBases(), true);
    }

    private function activeLocalesSettingKey(): string
    {
        return 'translation_active_locales';
    }

    private function localeMetaSettingKey(): string
    {
        return 'translation_locale_meta';
    }

    private function localeMetaMap(): array
    {
        return Cache::remember('translation_locales:meta', now()->addMinutes(10), function () {
            $raw = Setting::get($this->localeMetaSettingKey());

            if (!is_string($raw) || trim($raw) === '') {
                return [];
            }

            $decoded = json_decode($raw, true);
            if (!is_array($decoded)) {
                return [];
            }

            $out = [];
            foreach ($decoded as $code => $meta) {
                if (!is_string($code) || trim($code) === '' || !$this->validateLocaleCode($code)) {
                    continue;
                }
                if ($this->isBlockedLocale($code)) {
                    continue;
                }
                if (!is_array($meta)) {
                    continue;
                }

                $name = isset($meta['name']) && is_string($meta['name']) ? trim($meta['name']) : '';
                $flag = isset($meta['flag']) && is_string($meta['flag']) ? trim($meta['flag']) : '';

                $clean = [];
                if ($name !== '') {
                    $clean['name'] = $name;
                }
                if ($flag !== '') {
                    $clean['flag'] = $flag;
                }

                if ($clean !== []) {
                    $out[$code] = $clean;
                }
            }

            return $out;
        });
    }

    public function localeMeta(string $locale): array
    {
        $locale = trim($locale);
        if ($locale === '' || !$this->validateLocaleCode($locale) || $this->isBlockedLocale($locale)) {
            return [];
        }

        $map = $this->localeMetaMap();
        return isset($map[$locale]) && is_array($map[$locale]) ? $map[$locale] : [];
    }

    public function setLocaleMeta(string $locale, array $meta): bool
    {
        $locale = trim($locale);
        if ($locale === '' || !$this->validateLocaleCode($locale) || $this->isBlockedLocale($locale) || !$this->localeExists($locale)) {
            return false;
        }

        $name = isset($meta['name']) && is_string($meta['name']) ? trim($meta['name']) : '';
        $flag = isset($meta['flag']) && is_string($meta['flag']) ? trim($meta['flag']) : '';

        $map = $this->localeMetaMap();

        $clean = [];
        if ($name !== '') {
            $clean['name'] = $name;
        }
        if ($flag !== '') {
            $clean['flag'] = $flag;
        }

        if ($clean === []) {
            unset($map[$locale]);
        } else {
            $map[$locale] = $clean;
        }

        Setting::set($this->localeMetaSettingKey(), $map, 'general', 'json');

        Cache::forget('translation_locales:meta');

        return true;
    }

    public function activeLocales(): ?array
    {
        return Cache::remember('translation_locales:active_list', now()->addMinutes(10), function () {
            $raw = Setting::get($this->activeLocalesSettingKey());

            if (!is_string($raw) || trim($raw) === '') {
                return null;
            }

            $decoded = json_decode($raw, true);
            if (!is_array($decoded)) {
                return null;
            }

            $out = [];
            foreach ($decoded as $v) {
                if (!is_string($v)) {
                    continue;
                }
                $code = trim($v);
                if ($code === '' || !$this->validateLocaleCode($code) || $this->isBlockedLocale($code)) {
                    continue;
                }
                $out[] = $code;
            }

            $out = array_values(array_unique($out));

            return $out === [] ? null : $out;
        });
    }

    public function isLocaleActive(string $locale): bool
    {
        $locale = trim($locale);
        if ($locale === '') {
            return false;
        }

        if ($locale === 'en') {
            return true;
        }

        if (!$this->validateLocaleCode($locale) || $this->isBlockedLocale($locale) || !$this->localeExists($locale)) {
            return false;
        }

        $active = $this->activeLocales();
        if ($active === null) {
            return true;
        }

        return in_array($locale, $active, true);
    }

    public function setLocaleActive(string $locale, bool $active): bool
    {
        $locale = trim($locale);
        if ($locale === '' || $locale === 'en' || !$this->validateLocaleCode($locale) || $this->isBlockedLocale($locale) || !$this->localeExists($locale)) {
            return false;
        }

        $current = $this->activeLocales();
        if ($current === null) {
            $current = array_values(array_map(fn ($l) => is_object($l) && is_string($l->code ?? null) ? (string) $l->code : '', $this->listLocales()));
            $current = array_values(array_filter($current, fn ($c) => is_string($c) && trim($c) !== ''));
            $current = array_values(array_unique($current));
        }

        if ($active) {
            $current[] = $locale;
            $current = array_values(array_unique($current));
        } else {
            $current = array_values(array_filter($current, fn ($c) => $c !== $locale));
        }

        Setting::set($this->activeLocalesSettingKey(), $current, 'general', 'json');

        Cache::forget('translation_locales:active_list');
        Cache::forget('translation_locales:active:' . $locale);
        Cache::forget('translation_locales:site');

        return true;
    }

    public function localePath(string $locale): string
    {
        $locale = trim($locale);
        $locale = $locale !== '' ? $locale : 'en';

        return resource_path('lang/' . $locale . '.json');
    }

    public function localeExists(string $locale): bool
    {
        if ($this->isBlockedLocale($locale)) {
            return false;
        }

        $path = $this->localePath($locale);

        return is_file($path);
    }

    public function listLocales(): array
    {
        $paths = glob(resource_path('lang/*.json')) ?: [];
        $out = [];

        $metaMap = $this->localeMetaMap();

        foreach ($paths as $p) {
            if (!is_string($p) || $p === '' || !is_file($p)) {
                continue;
            }

            $base = basename($p);
            if (!is_string($base) || $base === '') {
                continue;
            }

            if (!str_ends_with($base, '.json')) {
                continue;
            }

            $code = substr($base, 0, -5);
            $code = is_string($code) ? trim($code) : '';
            if ($code === '') {
                continue;
            }
            if ($this->isBlockedLocale($code)) {
                continue;
            }

            $name = $code;
            if (isset($metaMap[$code]) && is_array($metaMap[$code]) && is_string($metaMap[$code]['name'] ?? null)) {
                $customName = trim((string) $metaMap[$code]['name']);
                if ($customName !== '') {
                    $name = $customName;
                }
            }

            $flag = null;
            if (isset($metaMap[$code]) && is_array($metaMap[$code]) && is_string($metaMap[$code]['flag'] ?? null)) {
                $customFlag = trim((string) $metaMap[$code]['flag']);
                if ($customFlag !== '') {
                    $flag = $customFlag;
                }
            }

            $out[] = (object) [
                'code' => $code,
                'name' => $name,
                'flag' => $flag,
            ];
        }

        usort($out, function ($a, $b) {
            $ac = is_object($a) && is_string($a->code ?? null) ? (string) $a->code : '';
            $bc = is_object($b) && is_string($b->code ?? null) ? (string) $b->code : '';
            return strcasecmp($ac, $bc);
        });

        return $out;
    }

    public function readLocaleMap(string $locale): array
    {
        if ($this->isBlockedLocale($locale)) {
            return [];
        }

        $path = $this->localePath($locale);
        if (!is_file($path)) {
            return [];
        }

        $raw = @file_get_contents($path);
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [];
        }

        $map = [];
        foreach ($decoded as $k => $v) {
            if (!is_string($k) || $k === '') {
                continue;
            }

            if (!is_string($v)) {
                continue;
            }

            $map[$k] = $v;
        }

        return $map;
    }

    public function writeLocaleMap(string $locale, array $map): bool
    {
        if ($this->isBlockedLocale($locale)) {
            return false;
        }

        $path = $this->localePath($locale);

        $clean = [];
        foreach ($map as $k => $v) {
            if (!is_string($k) || trim($k) === '') {
                continue;
            }

            if (!is_string($v)) {
                continue;
            }

            $v = trim($v);
            if ($v === '') {
                continue;
            }

            $clean[trim($k)] = $v;
        }

        ksort($clean, SORT_NATURAL | SORT_FLAG_CASE);

        $json = json_encode($clean, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($json)) {
            return false;
        }

        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        if (!is_dir($dir)) {
            return false;
        }

        $bytes = @file_put_contents($path, $json . "\n");

        return is_int($bytes) && $bytes > 0;
    }

    public function validateLocaleCode(string $code): bool
    {
        $code = trim($code);
        if ($code === '') {
            return false;
        }

        return preg_match('/^[A-Za-z0-9_-]+$/', $code) === 1;
    }

    public function parseUploadedLocaleFilename(string $filename): ?string
    {
        $filename = trim($filename);
        if ($filename === '') {
            return null;
        }

        $filename = basename($filename);

        if (!str_ends_with(strtolower($filename), '.json')) {
            return null;
        }

        $code = substr($filename, 0, -5);
        $code = is_string($code) ? trim($code) : '';

        if ($code === '' || !$this->validateLocaleCode($code) || $this->isBlockedLocale($code)) {
            return null;
        }

        return $code;
    }

    public function normalizeUploadJson(string $raw): ?array
    {
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return null;
        }

        $out = [];
        foreach ($decoded as $k => $v) {
            if (!is_string($k) || trim($k) === '') {
                continue;
            }

            if (!is_string($v)) {
                continue;
            }

            $out[trim($k)] = $v;
        }

        return $out;
    }
}
