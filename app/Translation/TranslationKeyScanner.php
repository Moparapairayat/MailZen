<?php

namespace App\Translation;

use Illuminate\Support\Facades\Cache;

class TranslationKeyScanner
{
    public function scan(bool $force = false): array
    {
        $cacheKey = 'translation_keys:scan:v3';

        if ($force) {
            return $this->scanDetailed(true)['keys'];
        }

        return Cache::remember($cacheKey, now()->addMinutes(10), function () {
            return $this->scanDetailed(true)['keys'];
        });
    }

    public function scanDetailed(bool $force = false): array
    {
        $cacheKey = 'translation_keys:scan_detailed:v3';

        if ($force) {
            return $this->computeDetailed();
        }

        return Cache::remember($cacheKey, now()->addMinutes(10), function () {
            return $this->computeDetailed();
        });
    }

    private function computeDetailed(): array
    {
        return (function () {
            $filesByKey = [];

            $paths = [
                resource_path('views'),
                app_path(),
            ];

            foreach ($paths as $path) {
                if (!is_string($path) || $path === '' || !is_dir($path)) {
                    continue;
                }

                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
                );

                foreach ($iterator as $file) {
                    if (!$file instanceof \SplFileInfo || !$file->isFile()) {
                        continue;
                    }

                    $filePath = $file->getPathname();
                    if (!is_string($filePath) || $filePath === '') {
                        continue;
                    }

                    if (!str_ends_with($filePath, '.php') && !str_ends_with($filePath, '.blade.php')) {
                        continue;
                    }

                    $contents = null;
                    try {
                        $contents = @file_get_contents($filePath);
                    } catch (\Throwable $e) {
                        $contents = null;
                    }

                    if (!is_string($contents) || $contents === '') {
                        continue;
                    }

                    foreach ($this->extractKeys($contents) as $k) {
                        $relative = $filePath;
                        $base = base_path();
                        if (is_string($base) && $base !== '' && str_starts_with($relative, $base)) {
                            $relative = ltrim(substr($relative, strlen($base)), DIRECTORY_SEPARATOR);
                        }

                        $filesByKey[$k][$relative] = true;
                    }
                }
            }

            $keys = array_keys($filesByKey);
            $keys = array_values(array_unique(array_filter($keys, fn ($k) => is_string($k) && trim($k) !== '')));
            sort($keys, SORT_NATURAL | SORT_FLAG_CASE);

            $filesByKey = array_map(function ($paths) {
                $paths = is_array($paths) ? array_keys($paths) : [];
                $paths = array_values(array_unique(array_filter($paths, fn ($p) => is_string($p) && trim($p) !== '')));
                sort($paths, SORT_NATURAL | SORT_FLAG_CASE);
                return $paths;
            }, $filesByKey);

            return [
                'keys' => $keys,
                'filesByKey' => $filesByKey,
            ];
        })();
    }

    private function extractKeys(string $contents): array
    {
        $results = [];

        $patterns = [
            // __('Some text')
            '/__\(\s*([\'\"])((?:\\\\.|(?!\1).)*?)\1\s*(?:\)|,)/s',
            // @lang('Some text')
            '/@lang\(\s*([\'\"])((?:\\\\.|(?!\1).)*?)\1\s*\)/s',
            // trans('Some text')
            '/trans\(\s*([\'\"])((?:\\\\.|(?!\1).)*?)\1\s*(?:\)|,)/s',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $contents, $m)) {
                foreach (($m[2] ?? []) as $raw) {
                    if (!is_string($raw)) {
                        continue;
                    }

                    $raw = trim($raw);
                    $raw = stripcslashes($raw);
                    if ($raw === '') {
                        continue;
                    }

                    // Ignore parameterized keys like "Hello :name" for now (still can be translated, but tends to be noisy)
                    $results[] = $raw;
                }
            }
        }

        return $results;
    }
}
