<?php

namespace App\Console\Commands;

use App\Translation\TranslationKeyScanner;
use Illuminate\Console\Command;

class ExportLanguageJs extends Command
{
    protected $signature = 'translations:export {--locale=en} {--dir=language} {--out=} {--format=json} {--force}';

    protected $description = 'Export translations into a single file (JSON preferred).';

    public function handle(): int
    {
        $locale = trim((string) $this->option('locale'));
        if ($locale === '') {
            $locale = 'en';
        }

        $format = strtolower(trim((string) $this->option('format')));
        if ($format === '') {
            $format = 'json';
        }

        if (!in_array($format, ['json', 'js'], true)) {
            $this->error("Invalid --format={$format}. Supported: json, js.");
            return Command::FAILURE;
        }

        $out = trim((string) $this->option('out'));
        if ($out === '') {
            $dir = trim((string) $this->option('dir'));
            if ($dir === '') {
                $dir = 'language';
            }

            $filename = $format === 'js'
                ? ($locale . '.js')
                : ($locale . '.json');

            $out = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
        }

        $outPath = str_starts_with($out, DIRECTORY_SEPARATOR) ? $out : base_path($out);

        if (is_file($outPath) && !(bool) $this->option('force')) {
            $this->error("File already exists: {$outPath}. Use --force to overwrite.");
            return Command::FAILURE;
        }

        $scanner = app(TranslationKeyScanner::class);
        $keys = $scanner->scan(true);
        $keys = array_values(array_unique(array_filter($keys, fn ($k) => is_string($k) && trim($k) !== '')));

        $map = [];

        foreach ($keys as $key) {
            $value = trans($key, [], $locale);

            if (!is_string($value)) {
                $value = $key;
            }

            $map[$key] = $value;
        }

        ksort($map, SORT_NATURAL | SORT_FLAG_CASE);

        $json = json_encode($map, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($json)) {
            $this->error('Failed to encode translations as JSON.');
            return Command::FAILURE;
        }

        $payload = $format === 'js'
            ? ('export default ' . $json . ";\n")
            : ($json . "\n");

        $dir = dirname($outPath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        if (!is_dir($dir)) {
            $this->error("Failed to create output directory: {$dir}");
            return Command::FAILURE;
        }

        $bytes = @file_put_contents($outPath, $payload);
        if (!is_int($bytes) || $bytes <= 0) {
            $this->error("Failed to write file: {$outPath}");
            return Command::FAILURE;
        }

        $this->info("Wrote {$outPath}");

        return Command::SUCCESS;
    }
}
