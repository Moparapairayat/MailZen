<?php

namespace App\Jobs;

use App\Models\Setting;
use App\Services\UpdateServerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class InstallUpdateJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected string $targetVersion
    ) {}

    public function handle(UpdateServerService $updateServerService): void
    {
        $this->setInstallState([
            'in_progress' => true,
            'status' => 'running',
            'message' => 'Update started.',
            'version' => $this->targetVersion,
            'started_at' => now()->toIso8601String(),
        ]);

        $maintenanceEnabled = false;
        $backupDir = null;
        $workDir = null;
        $createdFiles = [];
        $overwrittenFiles = [];

        try {
            $licenseKey = Setting::get('update_license_key');
            $licenseKey = is_string($licenseKey) ? trim($licenseKey) : '';

            if ($licenseKey === '') {
                throw new \RuntimeException('License key is not set.');
            }

            $baseUrl = (string) config('services.update_server.base_url');
            $productSecret = (string) config('services.update_server.product_secret');
            $productName = (string) config('services.update_server.product_name');

            if (trim($baseUrl) === '' || trim($productSecret) === '' || trim($productName) === '') {
                throw new \RuntimeException('Update server configuration is missing.');
            }

            $appUrl = (string) config('app.url');
            $parsed = parse_url($appUrl);
            $domain = is_array($parsed) && is_string($parsed['host'] ?? null) && trim((string) $parsed['host']) !== ''
                ? (string) $parsed['host']
                : $appUrl;

            $licenseCheck = $updateServerService->licenseCheck($licenseKey, $domain, $productSecret, $productName);
            if (!($licenseCheck['valid'] ?? false)) {
                $licenseActivate = $updateServerService->licenseActivate($licenseKey, $domain, $productSecret, $productName);
                if (!($licenseActivate['valid'] ?? false)) {
                    $message = is_string($licenseActivate['message'] ?? null) && trim((string) $licenseActivate['message']) !== ''
                        ? (string) $licenseActivate['message']
                        : (is_string($licenseCheck['message'] ?? null) && trim((string) $licenseCheck['message']) !== ''
                            ? (string) $licenseCheck['message']
                            : 'License key is not valid.');
                    throw new \RuntimeException($message);
                }
            }

            $downloadResult = $updateServerService->requestDownloadUrl($baseUrl, $licenseKey, $domain, $productSecret, $productName);
            if (!($downloadResult['success'] ?? false)) {
                $msg = is_string($downloadResult['message'] ?? null) ? trim((string) $downloadResult['message']) : '';
                throw new \RuntimeException($msg !== '' ? $msg : 'Failed to get download URL.');
            }

            $downloadUrl = is_string($downloadResult['download_url'] ?? null) ? trim((string) $downloadResult['download_url']) : '';
            if ($downloadUrl === '') {
                throw new \RuntimeException('Download URL not available.');
            }

            $this->setInstallState([
                'message' => 'Putting site into maintenance mode…',
            ]);

            Artisan::call('down', [
                '--retry' => 60,
                '--refresh' => 15,
            ]);
            $maintenanceEnabled = true;

            $timestamp = now()->format('Ymd_His');
            $workDir = storage_path('app/private/updates/' . $timestamp . '_' . Str::random(6));
            if (!is_dir($workDir) && !mkdir($workDir, 0755, true) && !is_dir($workDir)) {
                throw new \RuntimeException('Unable to create work directory.');
            }

            $zipPath = $workDir . '/update.zip';

            $this->setInstallState([
                'message' => 'Downloading update package…',
            ]);

            $response = Http::timeout(120)->retry(2, 500)->sink($zipPath)->get($downloadUrl);
            if (!$response->successful()) {
                throw new \RuntimeException('Failed to download update package (HTTP ' . $response->status() . ').');
            }

            if (!file_exists($zipPath) || filesize($zipPath) === 0) {
                throw new \RuntimeException('Downloaded update package is empty.');
            }

            $this->setInstallState([
                'message' => 'Extracting update package…',
            ]);

            $extractDir = $workDir . '/extracted';
            if (!is_dir($extractDir) && !mkdir($extractDir, 0755, true) && !is_dir($extractDir)) {
                throw new \RuntimeException('Unable to create extract directory.');
            }

            $zip = new ZipArchive();
            if ($zip->open($zipPath) !== true) {
                throw new \RuntimeException('Unable to open update zip.');
            }
            $zip->extractTo($extractDir);
            $zip->close();

            $sourceRoot = $this->detectSourceRoot($extractDir);
            if ($sourceRoot === null) {
                throw new \RuntimeException('Update package content is not recognized.');
            }

            $backupDir = storage_path('app/private/update_backups/' . $timestamp);
            if (!is_dir($backupDir) && !mkdir($backupDir, 0755, true) && !is_dir($backupDir)) {
                throw new \RuntimeException('Unable to create backup directory.');
            }

            $this->setInstallState([
                'message' => 'Installing files…',
            ]);

            [$createdFiles, $overwrittenFiles] = $this->copyWithBackup($sourceRoot, base_path(), $backupDir);

            $this->setInstallState([
                'message' => 'Running migrations…',
            ]);

            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('optimize:clear');

            $this->setInstallState([
                'in_progress' => false,
                'status' => 'success',
                'message' => 'Update installed successfully.',
                'version' => $this->targetVersion,
                'finished_at' => now()->toIso8601String(),
            ]);

            Setting::set('update_last_success_version', $this->targetVersion, 'updates');
            Setting::set('update_last_success_at', now()->toIso8601String(), 'updates');
            Setting::set('update_last_failure_reason', null, 'updates');
            Setting::set('update_last_failure_at', null, 'updates');
            Setting::set('update_last_failure_version', null, 'updates');
        } catch (\Throwable $e) {
            $this->setInstallState([
                'in_progress' => false,
                'status' => 'failed',
                'message' => $e->getMessage(),
                'version' => $this->targetVersion,
                'finished_at' => now()->toIso8601String(),
            ]);

            Setting::set('update_last_failure_version', $this->targetVersion, 'updates');
            Setting::set('update_last_failure_at', now()->toIso8601String(), 'updates');
            Setting::set('update_last_failure_reason', $e->getMessage(), 'updates');

            if (is_string($backupDir) && is_dir($backupDir)) {
                $this->rollback(base_path(), $backupDir, $createdFiles, $overwrittenFiles);
            }

            throw $e;
        } finally {
            if ($maintenanceEnabled) {
                try {
                    Artisan::call('up');
                } catch (\Throwable $e) {
                    // ignore
                }
            }

            try {
                if (is_string($workDir) && is_dir($workDir)) {
                    File::deleteDirectory($workDir);
                }
            } catch (\Throwable $e) {
                // ignore
            }

            try {
                $this->pruneUpdateArtifacts();
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }

    private function pruneUpdateArtifacts(): void
    {
        $this->pruneDirectories(storage_path('app/private/update_backups'), 3);
        $this->pruneDirectories(storage_path('app/private/updates/update_backups'), 3);
        $this->pruneDirectories(storage_path('app/private/updates'), 3, ['update_backups']);
    }

    private function pruneDirectories(string $root, int $keep, array $excludeNames = []): void
    {
        if ($keep < 0 || !is_dir($root)) {
            return;
        }

        $items = array_values(array_filter(scandir($root) ?: [], function ($name) use ($excludeNames) {
            if (!is_string($name) || $name === '.' || $name === '..') {
                return false;
            }
            if (str_starts_with($name, '.')) {
                return false;
            }
            if (in_array($name, $excludeNames, true)) {
                return false;
            }
            return true;
        }));

        $dirs = [];
        foreach ($items as $name) {
            $path = $root . DIRECTORY_SEPARATOR . $name;
            if (is_dir($path)) {
                $mtime = @filemtime($path);
                $dirs[] = ['path' => $path, 'mtime' => is_int($mtime) ? $mtime : 0, 'name' => $name];
            }
        }

        usort($dirs, function (array $a, array $b) {
            if ($a['mtime'] === $b['mtime']) {
                return strcmp((string) $b['name'], (string) $a['name']);
            }
            return $b['mtime'] <=> $a['mtime'];
        });

        foreach (array_slice($dirs, $keep) as $dir) {
            File::deleteDirectory((string) $dir['path']);
        }
    }

    private function setInstallState(array $patch): void
    {
        $current = Setting::get('update_install_state');
        $current = is_array($current) ? $current : [];
        $next = array_merge($current, $patch);
        Setting::set('update_install_state', $next, 'updates', 'json');
    }

    private function detectSourceRoot(string $extractDir): ?string
    {
        $candidates = [];

        $items = array_values(array_filter(scandir($extractDir) ?: [], function ($name) {
            return $name !== '.' && $name !== '..';
        }));

        foreach ($items as $name) {
            $path = $extractDir . DIRECTORY_SEPARATOR . $name;
            if (is_dir($path)) {
                $candidates[] = $path;
            }
        }

        if (count($candidates) === 1) {
            $only = $candidates[0];
            if (file_exists($only . DIRECTORY_SEPARATOR . 'artisan')) {
                return $only;
            }
        }

        if (file_exists($extractDir . DIRECTORY_SEPARATOR . 'artisan')) {
            return $extractDir;
        }

        foreach ($candidates as $dir) {
            if (file_exists($dir . DIRECTORY_SEPARATOR . 'artisan')) {
                return $dir;
            }
        }

        return null;
    }

    private function shouldSkipRelativePath(string $relativePath): bool
    {
        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');

        if ($relativePath === '.env') {
            return true;
        }

        if (str_starts_with($relativePath, '.')) {
            return true;
        }

        if (str_starts_with($relativePath, 'storage/')) {
            return true;
        }

        if (str_starts_with($relativePath, 'bootstrap/cache/')) {
            return true;
        }

        if (str_starts_with($relativePath, 'public/')) {
            return !str_starts_with($relativePath, 'public/build/');
        }

        return false;
    }

    /**
     * @return array{0: array<int, string>, 1: array<int, string>} created, overwritten
     */
    private function copyWithBackup(string $sourceRoot, string $destRoot, string $backupRoot): array
    {
        $created = [];
        $overwritten = [];

        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceRoot, \FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($it as $file) {
            $srcPath = (string) $file->getPathname();
            $relative = ltrim(str_replace('\\', '/', substr($srcPath, strlen($sourceRoot))), '/');

            if ($relative === '' || $this->shouldSkipRelativePath($relative)) {
                continue;
            }

            $destPath = $destRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);

            if ($file->isDir()) {
                if (!is_dir($destPath) && !mkdir($destPath, 0755, true) && !is_dir($destPath)) {
                    throw new \RuntimeException('Unable to create directory: ' . $relative);
                }
                continue;
            }

            $destDir = dirname($destPath);
            if (!is_dir($destDir) && !mkdir($destDir, 0755, true) && !is_dir($destDir)) {
                throw new \RuntimeException('Unable to create directory: ' . $relative);
            }

            if (file_exists($destPath)) {
                $backupPath = $backupRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
                $backupDir = dirname($backupPath);
                if (!is_dir($backupDir) && !mkdir($backupDir, 0755, true) && !is_dir($backupDir)) {
                    throw new \RuntimeException('Unable to create backup directory: ' . $relative);
                }
                if (!copy($destPath, $backupPath)) {
                    throw new \RuntimeException('Unable to backup file: ' . $relative);
                }
                $overwritten[] = $relative;
            } else {
                $created[] = $relative;
            }

            if (!copy($srcPath, $destPath)) {
                throw new \RuntimeException('Unable to copy file: ' . $relative);
            }
        }

        return [$created, $overwritten];
    }

    private function rollback(string $destRoot, string $backupRoot, array $created, array $overwritten): void
    {
        foreach ($created as $relative) {
            $destPath = $destRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
            if (file_exists($destPath)) {
                @unlink($destPath);
            }
        }

        foreach ($overwritten as $relative) {
            $backupPath = $backupRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
            $destPath = $destRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
            $destDir = dirname($destPath);
            if (!is_dir($destDir) && !mkdir($destDir, 0755, true) && !is_dir($destDir)) {
                continue;
            }
            if (file_exists($backupPath)) {
                @copy($backupPath, $destPath);
            }
        }
    }
}
