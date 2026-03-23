<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\User;
use App\Notifications\UpdateAvailableNotification;
use App\Services\UpdateServerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CheckForUpdates extends Command
{
    protected $signature = 'updates:check {--force : Bypass caches and force re-check}';

    protected $description = 'Check update server for a newer version and notify admins if an update is available.';

    public function __construct(protected UpdateServerService $updateServerService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $force = (bool) $this->option('force');

        $baseUrl = (string) config('services.update_server.base_url');
        $productId = config('services.update_server.product_id');

        $installedVersion = trim((string) config('mailzen.version', ''));
        if ($installedVersion === '') {
            $installedVersion = Setting::get('app_version');
            $installedVersion = is_string($installedVersion) ? trim($installedVersion) : '';
        }

        if (!is_string($baseUrl) || trim($baseUrl) === '' || !is_numeric($productId) || (int) $productId <= 0) {
            $this->storeStatus([
                'success' => false,
                'message' => 'Update server is not configured.',
                'installed_version' => $installedVersion,
            ]);
            return Command::FAILURE;
        }

        if ($installedVersion === '') {
            $this->storeStatus([
                'success' => false,
                'message' => 'Installed version is not set.',
                'installed_version' => $installedVersion,
            ]);
            return Command::FAILURE;
        }

        $product = $this->updateServerService->getProduct((int) $productId, $baseUrl, $force);
        $changelogs = $this->updateServerService->getChangelogs((int) $productId, $baseUrl, $force);

        $latestVersion = $this->extractLatestVersion($product, $changelogs);

        if (!is_string($latestVersion) || trim($latestVersion) === '') {
            $message = is_string($product['message'] ?? null) && trim((string) $product['message']) !== ''
                ? (string) $product['message']
                : 'Unable to determine latest version.';

            $this->storeStatus([
                'success' => false,
                'message' => $message,
                'installed_version' => $installedVersion,
                'latest_version' => null,
            ]);

            return Command::FAILURE;
        }

        $updateAvailable = version_compare($latestVersion, $installedVersion, '>');

        $this->storeStatus([
            'success' => true,
            'message' => $updateAvailable ? 'Update available.' : 'Up to date.',
            'installed_version' => $installedVersion,
            'latest_version' => $latestVersion,
            'update_available' => $updateAvailable,
        ]);

        if ($updateAvailable) {
            $this->notifyAdminsIfNeeded($installedVersion, $latestVersion);
        }

        return Command::SUCCESS;
    }

    private function storeStatus(array $status): void
    {
        $status['checked_at'] = now()->toIso8601String();
        Cache::put('update_server:update_status', $status, now()->addMinutes(20));
    }

    private function notifyAdminsIfNeeded(string $installedVersion, string $latestVersion): void
    {
        $notifiedVersion = Cache::get('update_server:update_notified_version');
        if (is_string($notifiedVersion) && trim($notifiedVersion) === $latestVersion) {
            return;
        }

        $admins = User::query()
            ->whereHas('userGroups', function ($q) {
                $q->whereRaw('LOWER(name) = ?', ['admin'])
                    ->orWhereRaw('LOWER(name) = ?', ['superadmin'])
                    ->orWhereRaw('LOWER(name) = ?', ['super admin']);
            })
            ->get();

        if ($admins->isEmpty()) {
            return;
        }

        foreach ($admins as $admin) {
            $admin->notify(new UpdateAvailableNotification($installedVersion, $latestVersion));
        }

        Cache::put('update_server:update_notified_version', $latestVersion, now()->addDays(30));
    }

    private function extractLatestVersion(?array $productResult, ?array $changelogResult): ?string
    {
        $latest = null;

        $payload = is_array($productResult) ? ($productResult['data'] ?? null) : null;
        if (is_array($payload)) {
            $productData = null;
            if (is_array($payload['data'] ?? null)) {
                $productData = $payload['data'];
            } elseif (is_array($payload['product'] ?? null)) {
                $productData = $payload['product'];
            } else {
                $productData = $payload;
            }

            if (is_array($productData)) {
                $latest = $productData['latest_version'] ?? ($productData['latestVersion'] ?? ($productData['latest'] ?? null));
                if (!is_string($latest) || trim((string) $latest) === '') {
                    $latest = $productData['version'] ?? null;
                }
            }
        }

        if (is_string($latest) && trim((string) $latest) !== '') {
            return trim((string) $latest);
        }

        $changelogData = is_array($changelogResult) ? ($changelogResult['data'] ?? null) : null;
        if (!is_array($changelogData)) {
            return null;
        }

        $versionMap = null;
        if (is_array($changelogData['releases'] ?? null)) {
            $versionMap = $changelogData['releases'];
        } elseif (is_array($changelogData['changelog'] ?? null)) {
            $versionMap = $changelogData['changelog'];
        }

        if (!is_array($versionMap) || empty($versionMap)) {
            return null;
        }

        $versions = array_keys($versionMap);
        usort($versions, function ($a, $b) {
            return version_compare((string) $b, (string) $a);
        });

        $top = $versions[0] ?? null;
        return is_string($top) && trim($top) !== '' ? trim($top) : null;
    }
}
