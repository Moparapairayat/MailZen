<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\DeliveryServer;
use App\Models\GoogleIntegration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use ZipArchive;

class IntegrationController extends Controller
{
    public function __construct()
    {
        $this->middleware('customer.access:servers.permissions.can_access_delivery_servers')->only(['index']);
    }

    public function index(Request $request)
    {
        $customer = auth('customer')->user();
        $canAccessGoogle = $customer ? (bool) $customer->groupAllows('integrations.permissions.can_access_google') : false;

        $allowedTabs = ['delivery-servers', 'wordpress'];
        if ($canAccessGoogle) {
            $allowedTabs[] = 'google';
        }

        $tab = (string) $request->query('tab', 'delivery-servers');
        if (!in_array($tab, $allowedTabs, true)) {
            $tab = 'delivery-servers';
        }

        $providers = [
            [
                'key' => 'mailgun',
                'label' => 'Mailgun',
                'type' => 'mailgun',
                'description' => 'Send emails using Mailgun API and webhooks.',
                'supported' => true,
            ],
            [
                'key' => 'sendgrid',
                'label' => 'SendGrid',
                'type' => 'sendgrid',
                'description' => 'Send emails with SendGrid API and event webhooks.',
                'supported' => true,
            ],
            [
                'key' => 'postmark',
                'label' => 'Postmark',
                'type' => 'postmark',
                'description' => 'Deliver transactional emails through Postmark.',
                'supported' => true,
            ],
            [
                'key' => 'sparkpost',
                'label' => 'SparkPost',
                'type' => 'sparkpost',
                'description' => 'Deliver emails using SparkPost API.',
                'supported' => true,
            ],
            [
                'key' => 'amazon-ses',
                'label' => 'Amazon SES',
                'type' => 'amazon-ses',
                'description' => 'Send emails through Amazon SES (API).',
                'supported' => true,
            ],
            [
                'key' => 'zeptomail-api',
                'label' => 'ZeptoMail API',
                'type' => 'zeptomail-api',
                'description' => 'Send emails via ZeptoMail Email API (templates or raw HTML/text).',
                'supported' => true,
            ],
        ];

        $mustAddOwn = $customer ? (bool) $customer->groupSetting('servers.permissions.must_add_delivery_server', false) : false;
        $canUseSystem = $customer ? (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false) : false;

        $supportedTypes = collect($providers)
            ->filter(fn ($p) => (bool) ($p['supported'] ?? false))
            ->pluck('type')
            ->values()
            ->all();

        $configured = DeliveryServer::query()
            ->when($customer, function ($q) use ($customer, $mustAddOwn, $canUseSystem) {
                $q->when($mustAddOwn, function ($inner) use ($customer) {
                    $inner->where('customer_id', $customer->id);
                }, function ($inner) use ($customer, $canUseSystem) {
                    $inner->where(function ($sub) use ($customer, $canUseSystem) {
                        $sub->where('customer_id', $customer->id);
                        if ($canUseSystem) {
                            $sub->orWhere(function ($sys) {
                                $sys->whereNull('customer_id')->where('status', 'active');
                            });
                        }
                    });
                });
            }, function ($q) {
                $q->whereRaw('1 = 0');
            })
            ->whereIn('type', $supportedTypes)
            ->selectRaw('type, COUNT(*) as cnt')
            ->groupBy('type')
            ->pluck('cnt', 'type')
            ->map(fn ($v) => ((int) $v) > 0)
            ->toArray();

        $serversByType = DeliveryServer::query()
            ->when($customer, fn ($q) => $q->where('customer_id', $customer->id), fn ($q) => $q->whereRaw('1 = 0'))
            ->whereIn('type', $supportedTypes)
            ->orderByDesc('id')
            ->get()
            ->groupBy('type')
            ->map(fn ($items) => $items->first())
            ->all();

        $googleIntegrations = collect();
        if ($customer && $canAccessGoogle && Schema::hasTable('google_integrations')) {
            $googleIntegrations = GoogleIntegration::query()
                ->where('customer_id', $customer->id)
                ->whereIn('service', ['sheets', 'drive'])
                ->get()
                ->keyBy('service');
        }

        return view('customer.integrations.index', compact('tab', 'providers', 'configured', 'serversByType', 'canAccessGoogle', 'googleIntegrations'));
    }

    public function downloadWordpressPlugin(Request $request)
    {
        $zipPath = base_path('wordpress-plugin/mailzen-integration.zip');
        if (!is_file($zipPath)) {
            $zipPath = base_path('wordpress-plugin/mailzen-integration.zip');
        }
        if (is_file($zipPath)) {
            return response()->download($zipPath, 'wp-mailzen-integration.zip');
        }

        $pluginDir = base_path('wordpress-plugin/mailzen-integration');
        abort_unless(is_dir($pluginDir), 404);
        abort_unless(class_exists(ZipArchive::class), 500);

        $tmpDir = storage_path('app/tmp');
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $tmpZipPath = $tmpDir . '/mailzen-integration-' . bin2hex(random_bytes(12)) . '.zip';

        $zip = new ZipArchive();
        $opened = $zip->open($tmpZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        abort_unless($opened === true, 500);

        $zipRoot = 'mailzen-integration/';
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($pluginDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $filePath = $file->getPathname();
            $relativePath = substr($filePath, strlen($pluginDir) + 1);
            $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);

            $zip->addFile($filePath, $zipRoot . $relativePath);
        }

        $zip->close();

        return response()->download($tmpZipPath, 'wp-mailzen-integration.zip')->deleteFileAfterSend(true);
    }
}
