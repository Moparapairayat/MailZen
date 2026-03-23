<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryServer;
use App\Models\Setting;
use Illuminate\Http\Request;
use ZipArchive;

class IntegrationController extends Controller
{
    public function index(Request $request)
    {
        $tab = (string) $request->query('tab', 'delivery-servers');
        if (!in_array($tab, ['delivery-servers', 'google', 'wordpress'], true)) {
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

        $supportedTypes = collect($providers)
            ->filter(fn ($p) => (bool) ($p['supported'] ?? false))
            ->pluck('type')
            ->values()
            ->all();

        $configured = DeliveryServer::query()
            ->whereIn('type', $supportedTypes)
            ->whereNull('customer_id')
            ->selectRaw('type, COUNT(*) as cnt')
            ->groupBy('type')
            ->pluck('cnt', 'type')
            ->map(fn ($v) => ((int) $v) > 0)
            ->toArray();

        $serversByType = DeliveryServer::query()
            ->whereIn('type', $supportedTypes)
            ->whereNull('customer_id')
            ->orderByDesc('id')
            ->get()
            ->groupBy('type')
            ->map(fn ($items) => $items->first())
            ->all();

        $googleSocialiteAvailable = class_exists('Laravel\\Socialite\\Facades\\Socialite');

        $clientId = config('services.google.client_id') ?: env('GOOGLE_CLIENT_ID');
        $clientSecret = config('services.google.client_secret') ?: env('GOOGLE_CLIENT_SECRET');

        $dbClientId = Setting::get('google_client_id');
        $dbClientSecret = Setting::get('google_client_secret');

        if (is_string($dbClientId)) {
            $dbClientId = trim($dbClientId);
        }
        if (is_string($dbClientSecret)) {
            $dbClientSecret = trim($dbClientSecret);
        }

        $googleClientId = (is_string($dbClientId) && $dbClientId !== '') ? $dbClientId : $clientId;
        $googleClientSecret = (is_string($dbClientSecret) && $dbClientSecret !== '') ? $dbClientSecret : $clientSecret;

        $googleOAuthConfigured = (bool) ($googleSocialiteAvailable && $googleClientId && $googleClientSecret);

        $googleRedirectSheets = route('customer.integrations.google.callback', ['service' => 'sheets']);
        $googleRedirectDrive = route('customer.integrations.google.callback', ['service' => 'drive']);

        return view('admin.integrations.index', compact(
            'tab',
            'providers',
            'configured',
            'serversByType',
            'googleSocialiteAvailable',
            'googleOAuthConfigured',
            'googleClientId',
            'googleClientSecret',
            'googleRedirectSheets',
            'googleRedirectDrive'
        ));
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
