<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingUpdateRequest;
use App\Models\DeliveryServer;
use App\Models\CustomerGroup;
use App\Models\ExternalTemplate;
use App\Models\Setting;
use App\Translation\LocaleJsonService;
use App\Services\DeliveryServerService;
use App\Services\SettingService;
use App\Services\UpdateServerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use ZipArchive;

class SettingController extends Controller
{
    public function __construct(
        protected SettingService $settingService,
        protected UpdateServerService $updateServerService
    ) {}

    private function billingKeysToHide(): array
    {
        return [
            'billing_provider',
            'billing_default_provider',
            'billing_providers',

            'stripe_public_key',
            'stripe_secret',
            'stripe_webhook_secret',

            'paypal_client_id',
            'paypal_client_secret',

            'razorpay_key_id',
            'razorpay_key_secret',
            'razorpay_webhook_secret',

            'paystack_public_key',
            'paystack_secret',

            'flutterwave_public_key',
            'flutterwave_secret',
            'flutterwave_encryption_key',
            'flutterwave_webhook_secret',

            'manual_bank_name',
            'manual_account_name',
            'manual_account_number',
            'manual_instructions',
            'manual_qr_image_path',
        ];
    }

    private function normalizePurchaseCode(?string $code): string
    {
        $code = is_string($code) ? $code : '';
        $code = trim($code);

        $code = str_replace([
            "\u{2010}",
            "\u{2011}",
            "\u{2012}",
            "\u{2013}",
            "\u{2014}",
            "\u{2015}",
            "\u{2212}",
            "\u{FE63}",
            "\u{FF0D}",
        ], '-', $code);

        $code = preg_replace('/[\s\x{00A0}\x{200B}\x{200C}\x{200D}\x{FEFF}]+/u', '', $code) ?? '';

        if ($code !== '' && preg_match('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/i', $code, $m)) {
            return $m[0];
        }

        $code = preg_replace('/[^0-9a-f\-]/i', '', $code) ?? '';

        if ($code !== '' && preg_match('/^[0-9a-f]{32}$/i', $code)) {
            return substr($code, 0, 8)
                . '-' . substr($code, 8, 4)
                . '-' . substr($code, 12, 4)
                . '-' . substr($code, 16, 4)
                . '-' . substr($code, 20, 12);
        }

        if ($code !== '' && preg_match('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/i', $code, $m2)) {
            return $m2[0];
        }

        return $code;
    }

    private function templateDefinitions(): array
    {
        return [
            'home-5' => [
                'id' => 'home-5',
                'name' => 'Modern Landing',
                'variant' => '5',
                'is_pro' => false,
            ],
            'home-1-pro' => [
                'id' => 'home-1-pro',
                'name' => 'Classic SaaS',
                'variant' => '1',
                'is_pro' => true,
            ],
            'home-2-pro' => [
                'id' => 'home-2-pro',
                'name' => 'Minimal Startup',
                'variant' => '2',
                'is_pro' => true,
            ],
        ];
    }

    private function getTemplateOrAbort(string $templateId): array
    {
        $templateId = trim($templateId);
        $templates = $this->templateDefinitions();
        if (!array_key_exists($templateId, $templates)) {
            abort(404);
        }

        $template = $templates[$templateId];
        if (!is_array($template)) {
            abort(404);
        }

        return $template;
    }

    private function ensureTemplateUnlocked(array $template): void
    {
        $isPro = (bool) ($template['is_pro'] ?? false);
        if ($isPro) {
            abort(403);
        }
    }

    private function templateEditableKeys(string $templateId): array
    {
        if ($templateId === 'home-5') {
            return [
                'brand_color',
                'hero_title',
                'hero_subtitle',
                'cta_text',
                'cta_secondary_text',
                'stat_emails_sent',
                'stat_users',
                'stat_uptime',
                'integration_1_icon',
                'integration_2_icon',
                'integration_3_icon',
                'integration_4_icon',
                'integration_5_icon',
                'integration_6_icon',
            ];
        }

        return ['brand_color'];
    }

    private function templateValidationRules(string $templateId): array
    {
        $rules = [];
        foreach ($this->templateEditableKeys($templateId) as $key) {
            $rules[$key] = ['nullable', 'string', 'max:500'];
        }

        if (array_key_exists('brand_color', $rules)) {
            $rules['brand_color'] = ['nullable', 'string', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'];
        }

        foreach (['integration_1_icon', 'integration_2_icon', 'integration_3_icon', 'integration_4_icon', 'integration_5_icon', 'integration_6_icon'] as $k) {
            if (array_key_exists($k, $rules)) {
                $rules[$k] = ['nullable', 'string', 'max:60', 'regex:/^[a-z0-9\-]+$/'];
            }
        }

        return $rules;
    }

    private function templateDefaultValues(string $templateId): array
    {
        if ($templateId === 'home-5') {
            return [
                'brand_color' => '#3b82f6',
                'hero_title' => 'The Future of Email Marketing',
                'hero_subtitle' => 'Launch powerful email campaigns with our self-hosted platform. Complete control, no limits, maximum deliverability.',
                'cta_text' => 'Start Free Trial',
                'cta_secondary_text' => 'Watch Demo',
                'stat_emails_sent' => '50M+',
                'stat_users' => '10K+',
                'stat_uptime' => '99.9%',
                'integration_1_icon' => 'zap',
                'integration_2_icon' => 'message-square',
                'integration_3_icon' => 'globe',
                'integration_4_icon' => 'shopping-bag',
                'integration_5_icon' => 'credit-card',
                'integration_6_icon' => 'target',
            ];
        }

        return [
            'brand_color' => '#3b82f6',
        ];
    }

    private function templateSettingKey(string $templateId, string $key): string
    {
        return 'template_' . $templateId . '_' . $key;
    }

    private function loadTemplateValues(string $templateId, array $overrides = []): array
    {
        $defaults = $this->templateDefaultValues($templateId);
        $values = [];

        foreach ($this->templateEditableKeys($templateId) as $key) {
            $settingKey = $this->templateSettingKey($templateId, $key);
            $fallback = $defaults[$key] ?? null;

            try {
                $stored = Setting::get($settingKey, $fallback);
            } catch (\Throwable $e) {
                $stored = $fallback;
            }

            $values[$key] = is_string($stored) ? (string) $stored : (is_null($stored) ? null : (string) $stored);
        }

        foreach ($overrides as $k => $v) {
            if (!in_array($k, $this->templateEditableKeys($templateId), true)) {
                continue;
            }
            $values[$k] = is_string($v) ? $v : (is_null($v) ? null : (string) $v);
        }

        if (array_key_exists('brand_color', $values)) {
            $bc = $values['brand_color'];
            if (!is_string($bc) || trim($bc) === '' || !preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', trim($bc))) {
                $values['brand_color'] = $defaults['brand_color'] ?? '#3b82f6';
            }
        }

        return $values;
    }

    /**
     * Display the settings page.
     */
    public function index(Request $request)
    {
        Setting::firstOrCreate(
            ['key' => 'site_language'],
            [
                'category' => 'general',
                'value' => 'en',
                'type' => 'string',
                'description' => 'Site language used as default locale.',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'brand_color'],
            [
                'category' => 'appearance',
                'value' => '#3b82f6',
                'type' => 'string',
                'description' => 'Primary brand color used across the public website (buttons, links, borders).',
                'is_public' => true,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'external_templates_api_base_url'],
            [
                'category' => 'templates',
                'value' => '',
                'type' => 'string',
                'description' => 'Base URL for the external templates API (e.g. https://example.com).',
                'is_public' => false,
            ]
        );
        Setting::firstOrCreate(
            ['key' => 'external_templates_api_product_id'],
            [
                'category' => 'templates',
                'value' => '',
                'type' => 'string',
                'description' => 'External templates product_id used when syncing templates.',
                'is_public' => false,
            ]
        );
        Setting::firstOrCreate(
            ['key' => 'external_templates_api_resource_type'],
            [
                'category' => 'templates',
                'value' => 'Post',
                'type' => 'string',
                'description' => 'External templates resource_type used when syncing templates (e.g. Post).',
                'is_public' => false,
            ]
        );
        Setting::firstOrCreate(
            ['key' => 'external_templates_license_key'],
            [
                'category' => 'templates',
                'value' => '',
                'type' => 'string',
                'description' => 'License key for unlocking Pro external templates.',
                'is_public' => false,
            ]
        );
        Setting::firstOrCreate(
            ['key' => 'external_templates_license_active'],
            [
                'category' => 'templates',
                'value' => 0,
                'type' => 'boolean',
                'description' => 'Whether the external templates license is active.',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'toast_position'],
            [
                'category' => 'appearance',
                'value' => 'top_right',
                'type' => 'string',
                'description' => 'Where toast notifications appear across the dashboard (top/bottom + left/center/right).',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'public_meta_image'],
            [
                'category' => 'appearance',
                'value' => null,
                'type' => 'string',
                'description' => 'Default meta/OG image used for social sharing (Open Graph / Twitter) on the public website.',
                'is_public' => true,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'cron_web_enabled'],
            [
                'category' => 'cron',
                'value' => 0,
                'type' => 'boolean',
                'description' => 'Enable/disable running the Laravel scheduler via the Web Cron URL.',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'cron_web_token'],
            [
                'category' => 'cron',
                'value' => Str::random(40),
                'type' => 'string',
                'description' => 'Security token for the Web Cron URL. Regenerate if it leaks.',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'cron_last_run_at'],
            [
                'category' => 'cron',
                'value' => null,
                'type' => 'string',
                'description' => 'Last time the scheduler was triggered by Web Cron or Run Now.',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'home_page_variant'],
            [
                'category' => 'appearance',
                'value' => '1',
                'type' => 'string',
                'description' => 'Select which homepage design to use for the public website.',
                'is_public' => true,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'public_header_sticky'],
            [
                'category' => 'appearance',
                'value' => 0,
                'type' => 'boolean',
                'description' => 'Make the public header sticky (fixed to top while scrolling).',
                'is_public' => true,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'home_redirect_enabled'],
            [
                'category' => 'appearance',
                'value' => 0,
                'type' => 'boolean',
                'description' => 'When enabled, visitors to the home page (/) will be redirected to the URL below.',
                'is_public' => true,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'home_redirect_url'],
            [
                'category' => 'appearance',
                'value' => '',
                'type' => 'string',
                'description' => 'Redirect destination. Use a full URL (https://...) or a path starting with /.',
                'is_public' => true,
            ]
        );

        foreach (['1', '2', '3', '4'] as $homeVariant) {
            Setting::firstOrCreate(
                ['key' => 'home_' . $homeVariant . '_text_map'],
                [
                    'category' => 'homepage',
                    'value' => json_encode([]),
                    'type' => 'json',
                    'description' => 'JSON map of {"original text": "replacement"} for Home ' . $homeVariant . '. Exact match required.',
                    'is_public' => true,
                ]
            );
        }

        $navDefaults = [
            'nav_show_features' => 1,
            'nav_show_pricing' => 1,
            'nav_show_blog' => 1,
            'nav_show_roadmap' => 1,
            'nav_show_docs' => 1,
            'nav_show_api_docs' => 1,
        ];

        foreach ($navDefaults as $key => $defaultValue) {
            Setting::firstOrCreate(
                ['key' => $key],
                [
                    'category' => 'navigation',
                    'value' => $defaultValue,
                    'type' => 'boolean',
                    'description' => 'Show/hide this item in the public navbar.',
                    'is_public' => true,
                ]
            );
        }

        Setting::firstOrCreate(
            ['key' => 'google_analytics_tracking_id'],
            [
                'category' => 'general',
                'value' => null,
                'type' => 'string',
                'description' => 'Google Analytics Measurement ID (e.g. G-XXXXXXXXXX) or UA Tracking ID (e.g. UA-XXXXXXXX-X).',
                'is_public' => true,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'meta_pixel_id'],
            [
                'category' => 'general',
                'value' => null,
                'type' => 'string',
                'description' => 'Meta Pixel ID (numbers only) for Facebook/Instagram conversion tracking.',
                'is_public' => true,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'transactional_delivery_server_id'],
            [
                'category' => 'email',
                'value' => null,
                'type' => 'string',
                'description' => 'Default delivery server for system transactional emails (verification/password reset). Use "system" to use MAIL_* from .env.',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'verification_delivery_server_id'],
            [
                'category' => 'email',
                'value' => null,
                'type' => 'string',
                'description' => 'Delivery server for verification emails. Use "inherit" to use the transactional default, or "system" to use MAIL_* from .env.',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'password_reset_delivery_server_id'],
            [
                'category' => 'email',
                'value' => null,
                'type' => 'string',
                'description' => 'Delivery server for password reset emails. Use "inherit" to use the transactional default, or "system" to use MAIL_* from .env.',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'gdpr_notice_title'],
            [
                'category' => 'privacy',
                'value' => 'We value your privacy',
                'type' => 'string',
                'description' => 'GDPR notice title shown on the public website.',
                'is_public' => true,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'gdpr_notice_description'],
            [
                'category' => 'privacy',
                'value' => 'We use cookies and similar technologies to improve your experience. You can accept or decline.',
                'type' => 'string',
                'description' => 'GDPR notice description shown on the public website.',
                'is_public' => true,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'gdpr_notice_accept_text'],
            [
                'category' => 'privacy',
                'value' => 'Accept',
                'type' => 'string',
                'description' => 'Text for the accept button.',
                'is_public' => true,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'gdpr_notice_decline_text'],
            [
                'category' => 'privacy',
                'value' => 'Decline',
                'type' => 'string',
                'description' => 'Text for the decline button.',
                'is_public' => true,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'gdpr_notice_position'],
            [
                'category' => 'privacy',
                'value' => 'bottom_full_width',
                'type' => 'string',
                'description' => 'Banner position on the public website.',
                'is_public' => true,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'gdpr_notice_delay_seconds'],
            [
                'category' => 'privacy',
                'value' => 0,
                'type' => 'integer',
                'description' => 'Delay (in seconds) before showing the GDPR notice.',
                'is_public' => true,
            ]
        );

        $category = $request->get('category', 'general');
        $settings = $this->settingService->getSettingsByCategory();
        $categories = $this->settingService->getCategories();

        $categories = array_values(array_filter($categories, fn ($cat) => !in_array($cat, ['homepage', 'templates'], true)));

        if ($settings->has('billing')) {
            $keysToHide = $this->billingKeysToHide();
            $settings['billing'] = $settings['billing']->reject(fn ($s) => in_array((string) ($s->key ?? ''), $keysToHide, true));
        }

        if (!in_array($category, $categories, true)) {
            $category = $categories[0] ?? 'general';
        }

        $customerGroups = [];
        if ($category === 'account') {
            $customerGroups = CustomerGroup::orderBy('name')->pluck('name', 'id')->toArray();
        }

        $activeLocales = [];
        if ($category === 'general') {
            $activeLocales = collect(app(LocaleJsonService::class)->listLocales());
        }

        $deliveryServerOptions = [];
        if ($category === 'email') {
            $deliveryServerOptions[''] = 'Auto';

            try {
                $deliveryServerService = app(DeliveryServerService::class);
                if ($deliveryServerService->hasSystemSmtpConfigured()) {
                    $deliveryServerOptions['system'] = 'System (SMTP)';
                }
            } catch (\Throwable $e) {
                // ignore
            }

            $activeServers = DeliveryServer::query()
                ->where('status', 'active')
                ->whereNull('customer_id') // Only show system-wide servers
                ->orderByDesc('is_primary')
                ->orderBy('name')
                ->get(['id', 'name', 'type']);

            foreach ($activeServers as $server) {
                $label = $server->name;
                if (is_string($server->type) && trim($server->type) !== '') {
                    $label .= ' (' . strtoupper(str_replace('-', ' ', $server->type)) . ')';
                }

                $deliveryServerOptions[(string) $server->id] = $label;
            }
        }

        $updateProduct = null;
        $updateChangelogs = null;
        $updateDownloadUrl = null;
        $updateConfig = null;
        $updateLicenseCheck = null;
        $updateStatus = null;
        $updateInstallState = null;
        $updateLastFailureAt = null;
        $updateLastFailureVersion = null;
        $updateLastFailureReason = null;

        $updateStatus = Cache::get('update_server:update_status');
        $updateInstallState = Setting::get('update_install_state');
        $updateLastFailureAt = Setting::get('update_last_failure_at');
        $updateLastFailureVersion = Setting::get('update_last_failure_version');
        $updateLastFailureReason = Setting::get('update_last_failure_reason');

        if (in_array($category, ['updates', 'changelogs'], true)) {
            $refresh = $request->boolean('refresh');
            $refreshUpdates = $category === 'updates' ? true : $refresh;
            $refreshChangelogs = $category === 'changelogs' ? true : $refresh;

            if ($category === 'updates' && $refreshUpdates) {
                try {
                    Artisan::call('updates:check', ['--force' => true]);
                } catch (\Throwable $e) {
                    // ignore
                }

                $updateStatus = Cache::get('update_server:update_status');
            }

            $baseUrl = (string) config('services.update_server.base_url', Setting::get('update_api_base_url'));
            $productId = config('services.update_server.product_id', Setting::get('update_product_id'));
            $productName = (string) config('services.update_server.product_name', Setting::get('update_product_name'));
            $productSecret = (string) config('services.update_server.product_secret', Setting::get('update_product_secret'));

            $appUrl = (string) config('app.url');
            $parsed = parse_url($appUrl);
            $domain = is_array($parsed) && is_string($parsed['host'] ?? null) && trim((string) $parsed['host']) !== ''
                ? (string) $parsed['host']
                : $appUrl;

            $licenseKey = Setting::get('update_license_key');
            $licenseKey = $this->normalizePurchaseCode(is_string($licenseKey) ? $licenseKey : null);

            if (is_string($licenseKey) && trim($licenseKey) !== '' && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $licenseKey)) {
                $cacheKey = 'update_server:license_check:' . md5((string) $baseUrl) . ':' . md5((string) $licenseKey) . ':' . md5((string) $domain);
                if ($refresh) {
                    Cache::forget($cacheKey);
                }

                $updateLicenseCheck = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($licenseKey, $domain, $productSecret, $productName) {
                    return $this->updateServerService->licenseCheck(
                        (string) $licenseKey,
                        $domain,
                        (string) $productSecret,
                        (string) $productName
                    );
                });
            }

            $updateConfig = [
                'base_url' => $baseUrl,
                'product_id' => $productId,
                'product_name' => $productName,
                'has_product_secret' => is_string($productSecret) && trim($productSecret) !== '',
            ];

            $productIdToUse = is_numeric($productId) ? (int) $productId : null;

            $baseUrlOk = is_string($baseUrl) && trim($baseUrl) !== '';
            $productIdOk = is_numeric($productIdToUse) && (int) $productIdToUse > 0;

            if ($baseUrlOk && $productIdOk) {
                if ($updateProduct === null) {
                    $updateProduct = $this->updateServerService->getProduct((int) $productIdToUse, (string) $baseUrl, $refreshUpdates);
                }

                if ($category !== 'changelogs' || ($updateChangelogs === null)) {
                    $updateChangelogs = $this->updateServerService->getChangelogs((int) $productIdToUse, (string) $baseUrl, $refreshChangelogs);
                }
            } else {
                $updateProduct = [
                    'success' => false,
                    'data' => null,
                    'message' => 'Update server settings are not configured yet.',
                    'status' => null,
                ];
                $updateChangelogs = $updateProduct;
            }

            $updateDownloadUrl = session()->pull('update_download_url');
        }

        return view('admin.settings.index', compact(
            'settings',
            'categories',
            'category',
            'customerGroups',
            'activeLocales',
            'deliveryServerOptions',
            'updateProduct',
            'updateChangelogs',
            'updateDownloadUrl',
            'updateConfig',
            'updateLicenseCheck',
            'updateStatus',
            'updateInstallState',
            'updateLastFailureAt',
            'updateLastFailureVersion',
            'updateLastFailureReason'
        ));
    }

    public function logs(Request $request)
    {
        $perPage = (int) $request->query('per_page', 20);
        if (!in_array($perPage, [10, 20, 30, 50], true)) {
            $perPage = 20;
        }

        $page = max(1, (int) $request->query('page', 1));

        $logPath = storage_path('logs/laravel.log');
        if (!is_file($logPath) || !is_readable($logPath)) {
            $logs = new LengthAwarePaginator([], 0, $perPage, $page, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);

            return view('admin.settings.logs', [
                'logs' => $logs,
                'perPage' => $perPage,
                'logFile' => null,
                'logFileSize' => null,
            ])->with('error', 'Log file not found.');
        }

        $fileSize = @filesize($logPath);
        $fileSize = is_int($fileSize) ? $fileSize : null;

        $bytesToRead = 2 * 1024 * 1024;
        $entries = [];

        try {
            $fp = fopen($logPath, 'rb');
            if ($fp !== false) {
                $seek = 0;
                if (is_int($fileSize)) {
                    $seek = max(0, $fileSize - $bytesToRead);
                }

                if ($seek > 0) {
                    fseek($fp, $seek);
                    fgets($fp);
                }

                $chunk = stream_get_contents($fp);
                fclose($fp);

                $entries = $this->parseLogEntries(is_string($chunk) ? $chunk : '');
            }
        } catch (\Throwable $e) {
            $entries = [];
        }

        $total = count($entries);
        $items = array_slice($entries, ($page - 1) * $perPage, $perPage);

        $logs = new LengthAwarePaginator($items, $total, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return view('admin.settings.logs', [
            'logs' => $logs,
            'perPage' => $perPage,
            'logFile' => $logPath,
            'logFileSize' => $fileSize,
        ]);
    }

    private function parseLogEntries(string $content): array
    {
        $content = str_replace("\r\n", "\n", $content);
        $lines = explode("\n", $content);

        $entries = [];
        $current = '';

        $isHeader = function (string $line): bool {
            return (bool) preg_match('/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]/', $line);
        };

        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            if ($isHeader($line)) {
                if ($current !== '') {
                    $entries[] = $current;
                }
                $current = $line;
                continue;
            }

            if ($current === '') {
                continue;
            }

            $current .= "\n" . $line;
        }

        if ($current !== '') {
            $entries[] = $current;
        }

        $entries = array_reverse($entries);

        return array_values($entries);
    }

    public function regenerateCronToken(Request $request)
    {
        Setting::set('cron_web_token', Str::random(40), 'cron', 'string');

        return redirect()
            ->route('admin.settings.index', ['category' => 'cron'])
            ->with('success', 'Cron token regenerated successfully. Update your cPanel cron job to use the new URL.');
    }

    public function runCronNow(Request $request)
    {
        $exitCode = 1;
        $output = '';

        try {
            $exitCode = Artisan::call('schedule:run');
            $output = (string) Artisan::output();
        } catch (\Throwable $e) {
            $output = $e->getMessage();
        }

        Setting::set('cron_last_run_at', now()->toIso8601String(), 'cron', 'string');

        return redirect()
            ->route('admin.settings.index', ['category' => 'cron'])
            ->with('success', 'Scheduler run finished (exit code: ' . (int) $exitCode . ').')
            ->with('cron_run_output', $output);
    }

    public function installUpdate(Request $request)
    {
        $targetVersion = $request->input('target_version');
        $targetVersion = is_string($targetVersion) ? trim($targetVersion) : '';

        if ($targetVersion === '') {
            return redirect()
                ->route('admin.settings.index', ['category' => 'updates'])
                ->with('error', 'Latest version is not available to install yet. Please refresh and try again.');
        }

        $installedVersion = trim((string) config('mailzen.version', ''));
        if ($installedVersion === '') {
            $installedVersion = Setting::get('app_version');
            $installedVersion = is_string($installedVersion) ? trim($installedVersion) : '';
        }
        if ($installedVersion !== '' && version_compare($targetVersion, $installedVersion, '<=')) {
            return redirect()
                ->route('admin.settings.index', ['category' => 'updates'])
                ->with('error', 'No update available.');
        }

        $state = Setting::get('update_install_state');
        if (
            is_array($state)
            && (
                (bool) ($state['in_progress'] ?? false)
                || in_array((string) ($state['status'] ?? ''), ['queued', 'running'], true)
            )
        ) {
            return redirect()
                ->route('admin.settings.index', ['category' => 'updates'])
                ->with('error', 'An update is already running. Please wait.');
        }

        // Set initial state for UI display
        Setting::set('update_install_state', [
            'in_progress' => true,
            'status' => 'running',
            'message' => 'Update started.',
            'version' => $targetVersion,
            'started_at' => now()->toIso8601String(),
        ], 'updates', 'json');

        // Dispatch update job immediately instead of queuing for cron
        \App\Jobs\InstallUpdateJob::dispatch($targetVersion);

        return redirect()
            ->route('admin.settings.index', ['category' => 'updates'])
            ->with('success', 'Update started. The site will enter maintenance mode during installation.');
    }

    public function requestUpdateDownload(Request $request)
    {
        $baseUrl = (string) config('services.update_server.base_url', Setting::get('update_api_base_url'));
        $licenseKey = $request->input('update_license_key');
        if (!is_string($licenseKey) || trim($licenseKey) === '') {
            $licenseKey = Setting::get('update_license_key');
        }
        $productSecret = (string) config('services.update_server.product_secret', Setting::get('update_product_secret'));
        $productName = (string) config('services.update_server.product_name', Setting::get('update_product_name'));

        $missing = [];

        if (!is_string($baseUrl) || trim($baseUrl) === '') {
            $missing[] = 'Update API Base URL';
        }
        if (!is_string($licenseKey) || trim($licenseKey) === '') {
            $missing[] = 'License Key';
        }
        if (!is_string($productSecret) || trim($productSecret) === '') {
            $missing[] = 'Product Secret';
        }
        if (!is_string($productName) || trim($productName) === '') {
            $missing[] = 'Product Name';
        }

        if (!empty($missing)) {
            return redirect()
                ->route('admin.settings.index', ['category' => 'updates'])
                ->with('error', 'Missing required update settings: ' . implode(', ', $missing) . '.');
        }

        $licenseKey = $this->normalizePurchaseCode(is_string($licenseKey) ? $licenseKey : null);
        if ($licenseKey === '' || !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $licenseKey)) {
            return redirect()
                ->route('admin.settings.index', ['category' => 'updates'])
                ->with('error', 'Invalid purchase code format. Please use the Envato/ThemeForest purchase code (e.g. xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx).');
        }

        $appUrl = (string) config('app.url');
        $parsed = parse_url($appUrl);
        $domain = is_array($parsed) && is_string($parsed['host'] ?? null) && trim((string) $parsed['host']) !== ''
            ? (string) $parsed['host']
            : $appUrl;

        $licenseCheck = $this->updateServerService->licenseCheck(
            (string) $licenseKey,
            $domain,
            (string) $productSecret,
            (string) $productName
        );

        if (!($licenseCheck['valid'] ?? false)) {
            $licenseActivate = $this->updateServerService->licenseActivate(
                (string) $licenseKey,
                $domain,
                (string) $productSecret,
                (string) $productName
            );

            if (!($licenseActivate['valid'] ?? false)) {
                $message = is_string($licenseActivate['message'] ?? null) && trim((string) $licenseActivate['message']) !== ''
                    ? (string) $licenseActivate['message']
                    : (is_string($licenseCheck['message'] ?? null) && trim((string) $licenseCheck['message']) !== ''
                        ? (string) $licenseCheck['message']
                        : 'License key is not valid.');

                return redirect()
                    ->route('admin.settings.index', ['category' => 'updates'])
                    ->with('error', $message);
            }
        }

        $result = $this->updateServerService->requestDownloadUrl(
            (string) $baseUrl,
            (string) $licenseKey,
            $domain,
            (string) $productSecret,
            (string) $productName
        );

        if (!($result['success'] ?? false)) {
            $message = is_string($result['message'] ?? null) && trim((string) $result['message']) !== ''
                ? (string) $result['message']
                : 'Failed to get download link.';

            if (is_numeric($result['status'] ?? null)) {
                $message .= ' (HTTP ' . (int) $result['status'] . ')';
            }
            if (array_key_exists('raw', $result) && $result['raw'] !== null) {
                $raw = is_string($result['raw']) ? $result['raw'] : json_encode($result['raw']);
                if (is_string($raw) && trim($raw) !== '') {
                    $message .= ': ' . $raw;
                }
            }

            return redirect()
                ->route('admin.settings.index', ['category' => 'updates'])
                ->with('error', $message);
        }

        $downloadUrl = is_string($result['download_url'] ?? null) ? $result['download_url'] : null;

        if (!is_string($downloadUrl) || trim($downloadUrl) === '') {
            return redirect()
                ->route('admin.settings.index', ['category' => 'updates'])
                ->with('error', 'Download URL not available.');
        }

        return redirect()
            ->route('admin.settings.index', ['category' => 'updates'])
            ->with('success', 'Download link generated.')
            ->with('update_download_url', $downloadUrl);
    }

    public function activateLicense(Request $request)
    {
        $baseUrl = (string) config('services.update_server.base_url', Setting::get('update_api_base_url'));
        $licenseKey = $request->input('update_license_key');
        if (!is_string($licenseKey) || trim($licenseKey) === '') {
            $licenseKey = Setting::get('update_license_key');
        }
        $productSecret = (string) config('services.update_server.product_secret', Setting::get('update_product_secret'));
        $productName = (string) config('services.update_server.product_name', Setting::get('update_product_name'));

        $missing = [];

        if (!is_string($licenseKey) || trim($licenseKey) === '') {
            $missing[] = 'License Key';
        }
        if (!is_string($productSecret) || trim($productSecret) === '') {
            $missing[] = 'Product Secret';
        }

        if (!empty($missing)) {
            return redirect()
                ->route('admin.settings.index', ['category' => 'updates'])
                ->with('error', 'Missing required settings: ' . implode(', ', $missing) . '.');
        }

        $licenseKey = $this->normalizePurchaseCode(is_string($licenseKey) ? $licenseKey : null);
        if ($licenseKey === '' || !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $licenseKey)) {
            return redirect()
                ->route('admin.settings.index', ['category' => 'updates'])
                ->with('error', 'Invalid purchase code format. Please use the Envato/ThemeForest purchase code (e.g. xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx).');
        }

        $appUrl = (string) config('app.url');
        $parsed = parse_url($appUrl);
        $domain = is_array($parsed) && is_string($parsed['host'] ?? null) && trim((string) $parsed['host']) !== ''
            ? (string) $parsed['host']
            : $appUrl;

        $result = $this->updateServerService->licenseActivate(
            (string) $licenseKey,
            $domain,
            (string) $productSecret,
            (string) $productName
        );

        $cacheKey = 'update_server:license_check:' . md5((string) $baseUrl) . ':' . md5((string) $licenseKey) . ':' . md5((string) $domain);
        Cache::forget($cacheKey);

        if (!($result['valid'] ?? false)) {
            $message = is_string($result['message'] ?? null) && trim((string) $result['message']) !== ''
                ? (string) $result['message']
                : 'Failed to activate license.';

            return redirect()
                ->route('admin.settings.index', ['category' => 'updates'])
                ->with('error', $message);
        }

        Setting::set('update_license_key', $licenseKey);

        $message = null;
        if (is_array($result['data'] ?? null) && is_string($result['data']['message'] ?? null)) {
            $message = trim((string) $result['data']['message']);
        }
        if (!is_string($message) || $message === '') {
            $message = 'License activated successfully.';
        }

        return redirect()
            ->route('admin.settings.index', ['category' => 'updates', 'refresh' => 1])
            ->with('success', $message);
    }

    public function deactivateLicense(Request $request)
    {
        $baseUrl = (string) config('services.update_server.base_url', Setting::get('update_api_base_url'));
        $licenseKey = $request->input('update_license_key');
        if (!is_string($licenseKey) || trim($licenseKey) === '') {
            $licenseKey = Setting::get('update_license_key');
        }
        $productSecret = (string) config('services.update_server.product_secret', Setting::get('update_product_secret'));
        $productName = (string) config('services.update_server.product_name', Setting::get('update_product_name'));

        $missing = [];

        if (!is_string($baseUrl) || trim($baseUrl) === '') {
            $missing[] = 'Update API Base URL';
        }
        if (!is_string($licenseKey) || trim($licenseKey) === '') {
            $missing[] = 'License Key';
        }
        if (!is_string($productSecret) || trim($productSecret) === '') {
            $missing[] = 'Product Secret';
        }
        if (!is_string($productName) || trim($productName) === '') {
            $missing[] = 'Product Name';
        }

        if (!empty($missing)) {
            return redirect()
                ->route('admin.settings.index', ['category' => 'updates'])
                ->with('error', 'Missing required update settings: ' . implode(', ', $missing) . '.');
        }

        $licenseKey = $this->normalizePurchaseCode(is_string($licenseKey) ? $licenseKey : null);
        if ($licenseKey === '' || !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $licenseKey)) {
            return redirect()
                ->route('admin.settings.index', ['category' => 'updates'])
                ->with('error', 'Invalid purchase code format. Please use the Envato/ThemeForest purchase code (e.g. xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx).');
        }

        $appUrl = (string) config('app.url');
        $parsed = parse_url($appUrl);
        $domain = is_array($parsed) && is_string($parsed['host'] ?? null) && trim((string) $parsed['host']) !== ''
            ? (string) $parsed['host']
            : $appUrl;

        $result = $this->updateServerService->licenseDeactivate(
            (string) $licenseKey,
            $domain,
            (string) $productSecret,
            (string) $productName
        );

        $cacheKey = 'update_server:license_check:' . md5((string) $baseUrl) . ':' . md5((string) $licenseKey) . ':' . md5((string) $domain);
        Cache::forget($cacheKey);

        if (!($result['success'] ?? false) && !($result['valid'] ?? false)) {
            $data = $result['data'] ?? null;
            $status = is_array($data) ? ($data['status'] ?? null) : null;
            if (!is_string($status) || !in_array(strtolower(trim($status)), ['success', 'deactivated'], true)) {
                $message = is_string($result['message'] ?? null) && trim((string) $result['message']) !== ''
                    ? (string) $result['message']
                    : 'Failed to deactivate license.';

                return redirect()
                    ->route('admin.settings.index', ['category' => 'updates'])
                    ->with('error', $message);
            }
        }

        Setting::set('update_license_key', null);

        $message = null;
        if (is_array($result['data'] ?? null) && is_string($result['data']['message'] ?? null)) {
            $message = trim((string) $result['data']['message']);
        }
        if (!is_string($message) || $message === '') {
            $message = 'License deactivated.';
        }

        return redirect()
            ->route('admin.settings.index', ['category' => 'updates', 'refresh' => 1])
            ->with('success', $message);
    }

    /**
     * Update settings.
     */
    public function update(SettingUpdateRequest $request)
    {
        $category = $request->input('category', 'general');
        $categories = $this->settingService->getCategories();

        $categories = array_values(array_filter($categories, fn ($cat) => $cat !== 'homepage'));

        $brandingDisk = (string) config('filesystems.branding_disk', 'public');

        if (!in_array($category, $categories, true)) {
            $category = $categories[0] ?? 'general';
        }
        $data = $request->except(['_token', '_method', 'category']);

        if ($category === 'billing') {
            foreach ($this->billingKeysToHide() as $key) {
                unset($data[$key]);
            }
        }

        if ($request->hasFile('app_logo')) {
            $file = $request->file('app_logo');

            if ($file && $file->isValid()) {
                $oldPath = Setting::get('app_logo');
                if (is_string($oldPath) && $oldPath !== '') {
                    Storage::disk($brandingDisk)->delete($oldPath);
                }

                $path = $file->storePublicly('branding', $brandingDisk);
                $data['app_logo'] = $path;
            }
        }

        if ($request->hasFile('app_logo_dark')) {
            $file = $request->file('app_logo_dark');

            if ($file && $file->isValid()) {
                $oldPath = Setting::get('app_logo_dark');
                if (is_string($oldPath) && $oldPath !== '') {
                    Storage::disk($brandingDisk)->delete($oldPath);
                }

                $path = $file->storePublicly('branding', $brandingDisk);
                $data['app_logo_dark'] = $path;
            }
        }

        if ($request->hasFile('site_favicon')) {
            $file = $request->file('site_favicon');

            if ($file && $file->isValid()) {
                $oldPath = Setting::get('site_favicon');
                if (is_string($oldPath) && $oldPath !== '') {
                    Storage::disk($brandingDisk)->delete($oldPath);
                }

                $path = $file->storePublicly('branding', $brandingDisk);
                $data['site_favicon'] = $path;
            }
        }

        if ($request->hasFile('public_meta_image')) {
            $file = $request->file('public_meta_image');

            if ($file && $file->isValid()) {
                $oldPath = Setting::get('public_meta_image');
                if (is_string($oldPath) && $oldPath !== '') {
                    Storage::disk($brandingDisk)->delete($oldPath);
                }

                $path = $file->storePublicly('branding', $brandingDisk);
                $data['public_meta_image'] = $path;
            }
        }

        $booleanKeys = Setting::query()
            ->where('type', 'boolean')
            ->where('category', $category)
            ->pluck('key');

        foreach ($booleanKeys as $key) {
            if (!array_key_exists($key, $data)) {
                $data[$key] = 0;
            }
        }

        foreach (['google_client_secret', 's3_secret', 'wasabi_secret', 'update_product_secret', 'update_license_key', 'openai_api_key', 'gemini_api_key'] as $secretKey) {
            if (!array_key_exists($secretKey, $data)) {
                continue;
            }

            $secret = $data[$secretKey];
            if (!is_string($secret) || trim($secret) === '' || trim($secret) === '********') {
                unset($data[$secretKey]);
            }
        }

        if ($category === 'updates' && array_key_exists('update_license_key', $data)) {
            $licenseKey = $data['update_license_key'];

            if (is_string($licenseKey) && trim($licenseKey) !== '') {
                $licenseKey = $this->normalizePurchaseCode($licenseKey);
                if ($licenseKey === '' || !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $licenseKey)) {
                    return redirect()
                        ->route('admin.settings.index', ['category' => $category])
                        ->with('error', 'Invalid purchase code format. Please use the Envato/ThemeForest purchase code (e.g. xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx).');
                }

                $data['update_license_key'] = $licenseKey;
            }
        }

        if (array_key_exists('site_language', $data)) {
            Cache::forget('translation_locales:site');
        }

        $this->settingService->updateSettings($data);

        return redirect()
            ->route('admin.settings.index', ['category' => $category])
            ->with('success', 'Settings updated successfully.');
    }

    public function revealSecret(Request $request, string $key)
    {
        $allowed = [
            'google_client_secret',
            's3_secret',
            'wasabi_secret',
            'update_product_secret',
            'update_license_key',
            'openai_api_key',
            'gemini_api_key',
        ];

        if (!in_array($key, $allowed, true)) {
            abort(404);
        }

        $value = Setting::get($key);
        $value = is_string($value) ? trim($value) : '';

        return response()->json([
            'success' => true,
            'value' => $value,
        ]);
    }

    public function activateTemplate(Request $request, string $template)
    {
        $tpl = $this->getTemplateOrAbort($template);
        $this->ensureTemplateUnlocked($tpl);

        $variant = is_string($tpl['variant'] ?? null) ? trim((string) $tpl['variant']) : '1';
        if ($variant === '' || !in_array($variant, ['1', '2', '3', '4', '5'], true)) {
            $variant = '1';
        }

        Setting::set('home_page_variant', $variant, 'appearance', 'string');
        Setting::set('active_public_template', (string) ($tpl['id'] ?? $template), 'templates', 'string');

        $values = $this->loadTemplateValues($template);
        if (is_string($values['brand_color'] ?? null)) {
            Setting::set('brand_color', (string) $values['brand_color'], 'appearance', 'string');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'template_id' => (string) ($tpl['id'] ?? $template),
                'variant' => $variant,
            ]);
        }

        return redirect()
            ->route('admin.settings.index', ['category' => 'templates'])
            ->with('success', 'Template activated.');
    }

    public function editTemplate(Request $request, string $template)
    {
        $tpl = $this->getTemplateOrAbort($template);
        $this->ensureTemplateUnlocked($tpl);

        $values = $this->loadTemplateValues($template);

        return view('admin.settings.template-editor', [
            'templateId' => $template,
            'template' => $tpl,
            'values' => $values,
        ]);
    }

    public function templateValues(Request $request, string $template): JsonResponse
    {
        $tpl = $this->getTemplateOrAbort($template);
        $this->ensureTemplateUnlocked($tpl);

        $values = $this->loadTemplateValues($template);

        return response()->json([
            'template_id' => (string) ($tpl['id'] ?? $template),
            'template' => $tpl,
            'editable_keys' => $this->templateEditableKeys($template),
            'values' => $values,
        ]);
    }

    public function updateTemplate(Request $request, string $template)
    {
        $tpl = $this->getTemplateOrAbort($template);
        $this->ensureTemplateUnlocked($tpl);

        $normalized = $request->all();
        foreach ($this->templateEditableKeys($template) as $key) {
            if (!array_key_exists($key, $normalized)) {
                continue;
            }
            if (is_string($normalized[$key]) && trim($normalized[$key]) === '') {
                $normalized[$key] = null;
            }
        }
        $request->replace($normalized);

        $data = $request->validate($this->templateValidationRules($template));

        foreach ($this->templateEditableKeys($template) as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }
            $val = $data[$key];
            $settingKey = $this->templateSettingKey($template, $key);
            Setting::set($settingKey, $val, 'templates', 'string');
        }

        Setting::set('active_public_template', (string) ($tpl['id'] ?? $template), 'templates', 'string');

        try {
            $activeVariant = Setting::get('home_page_variant', '1');
        } catch (\Throwable $e) {
            $activeVariant = '1';
        }
        $activeVariant = is_string($activeVariant) ? trim($activeVariant) : '1';

        if ((string) ($tpl['variant'] ?? '') === (string) $activeVariant && is_string($data['brand_color'] ?? null) && $data['brand_color'] !== '') {
            Setting::set('brand_color', (string) $data['brand_color'], 'appearance', 'string');
        }

        if ($request->expectsJson()) {
            $values = $this->loadTemplateValues($template);
            return response()->json([
                'success' => true,
                'template_id' => (string) ($tpl['id'] ?? $template),
                'values' => $values,
            ]);
        }

        return redirect()
            ->route('admin.settings.templates.edit', ['template' => $template])
            ->with('success', 'Template updated.');
    }

    public function previewTemplate(Request $request, string $template)
    {
        $tpl = $this->getTemplateOrAbort($template);

        $normalized = $request->all();
        foreach ($this->templateEditableKeys($template) as $key) {
            if (!array_key_exists($key, $normalized)) {
                continue;
            }
            if (is_string($normalized[$key]) && trim($normalized[$key]) === '') {
                $normalized[$key] = null;
            }
        }
        $request->replace($normalized);

        $validated = $request->validate($this->templateValidationRules($template));
        $values = $this->loadTemplateValues($template, $validated);

        $variant = is_string($tpl['variant'] ?? null) ? trim((string) $tpl['variant']) : '1';
        if ($variant === '' || !in_array($variant, ['1', '2', '3', '4', '5'], true)) {
            $variant = '1';
        }

        $view = match ($variant) {
            '2' => 'public.home-2',
            '3' => 'public.home-3',
            '4' => 'public.home-v2',
            '5' => 'public.home-5',
            default => 'public.home',
        };

        $payload = [];
        if ($variant === '5') {
            $payload['templateOverrides'] = $values;
        }
        if (is_string($values['brand_color'] ?? null) && preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', (string) $values['brand_color'])) {
            $payload['brandColorOverride'] = (string) $values['brand_color'];
        }

        $html = view($view, $payload)->render();
        return response($html);
    }

    private function externalTemplatesConfig(Request $request): array
    {
        $baseUrl = $request->input('external_templates_api_base_url', Setting::get('external_templates_api_base_url', ''));
        $productId = $request->input('external_templates_api_product_id', Setting::get('external_templates_api_product_id', ''));
        $resourceType = $request->input('external_templates_api_resource_type', Setting::get('external_templates_api_resource_type', 'Post'));

        $baseUrl = is_string($baseUrl) ? trim($baseUrl) : '';
        $productId = is_string($productId) ? trim($productId) : (is_numeric($productId) ? (string) $productId : '');
        $resourceType = is_string($resourceType) ? trim($resourceType) : '';

        return [
            'base_url' => rtrim($baseUrl, '/'),
            'product_id' => $productId,
            'resource_type' => $resourceType,
        ];
    }

    public function syncExternalTemplates(Request $request)
    {
        $cfg = $this->externalTemplatesConfig($request);

        $baseUrl = $cfg['base_url'];
        $productId = $cfg['product_id'];
        $resourceType = $cfg['resource_type'];

        if ($baseUrl !== '' && filter_var($baseUrl, FILTER_VALIDATE_URL)) {
            Setting::set('external_templates_api_base_url', $baseUrl, 'templates', 'string');
        }
        if ($productId !== '') {
            Setting::set('external_templates_api_product_id', $productId, 'templates', 'string');
        }
        if ($resourceType !== '') {
            Setting::set('external_templates_api_resource_type', $resourceType, 'templates', 'string');
        }

        if ($baseUrl === '' || !filter_var($baseUrl, FILTER_VALIDATE_URL)) {
            $msg = 'External templates API base URL is missing or invalid.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->route('admin.settings.index', ['category' => 'templates'])->with('error', $msg);
        }
        if ($resourceType === '') {
            $msg = 'External templates resource type is required.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->route('admin.settings.index', ['category' => 'templates'])->with('error', $msg);
        }
        if ($productId === '' || !ctype_digit($productId)) {
            $msg = 'External templates product id is required.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->route('admin.settings.index', ['category' => 'templates'])->with('error', $msg);
        }

        $url = $baseUrl . '/wp-json/v1/templates/' . rawurlencode($resourceType);

        try {
            $resp = Http::timeout(20)->acceptJson()->get($url, [
                'product_id' => (int) $productId,
            ]);
        } catch (\Throwable $e) {
            Log::warning('External templates sync failed: ' . $e->getMessage());
            $msg = 'Failed to reach external templates API.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 502);
            }
            return redirect()->route('admin.settings.index', ['category' => 'templates'])->with('error', $msg);
        }

        if (!$resp->successful()) {
            $msg = 'External templates API returned an error.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 502);
            }
            return redirect()->route('admin.settings.index', ['category' => 'templates'])->with('error', $msg);
        }

        $payload = $resp->json();
        $templates = is_array($payload) && is_array($payload['templates'] ?? null) ? $payload['templates'] : [];

        $synced = 0;
        foreach ($templates as $t) {
            if (!is_array($t)) {
                continue;
            }

            $externalId = $t['id'] ?? null;
            if (!is_int($externalId) && !(is_string($externalId) && ctype_digit($externalId))) {
                continue;
            }
            $externalId = (int) $externalId;

            $meta = is_array($t['meta'] ?? null) ? $t['meta'] : [];
            $requiresLicense = false;
            if (array_key_exists('requires_license', $meta)) {
                $requiresLicense = (string) $meta['requires_license'] === '1' || $meta['requires_license'] === 1 || $meta['requires_license'] === true;
            }

            $createdAt = is_string($t['created_at'] ?? null) ? $t['created_at'] : null;
            $updatedAt = is_string($t['updated_at'] ?? null) ? $t['updated_at'] : null;

            ExternalTemplate::updateOrCreate(
                ['external_id' => $externalId],
                [
                    'name' => is_string($t['name'] ?? null) ? $t['name'] : null,
                    'resource_type' => is_string($payload['resource_type'] ?? null) ? $payload['resource_type'] : $resourceType,
                    'template_type' => is_string($t['type'] ?? null) ? $t['type'] : null,
                    'product_id' => is_int($t['product_id'] ?? null) ? $t['product_id'] : (is_string($t['product_id'] ?? null) && ctype_digit($t['product_id']) ? (int) $t['product_id'] : (int) $productId),
                    'preview_image' => is_string($t['preview_image'] ?? null) ? $t['preview_image'] : null,
                    'preview_url' => is_string($t['preview_url'] ?? null) ? $t['preview_url'] : null,
                    'requires_license' => $requiresLicense,
                    'plan' => is_string($meta['plan'] ?? null) ? $meta['plan'] : null,
                    'builder' => is_string($meta['builder'] ?? null) ? $meta['builder'] : null,
                    'categories' => is_array($t['categories'] ?? null) ? $t['categories'] : null,
                    'meta' => $meta,
                    'external_created_at' => is_string($createdAt) ? Carbon::parse($createdAt) : null,
                    'external_updated_at' => is_string($updatedAt) ? Carbon::parse($updatedAt) : null,
                ]
            );

            $synced++;
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'synced' => $synced]);
        }

        return redirect()->route('admin.settings.index', ['category' => 'templates'])->with('success', 'Synced external templates: ' . $synced);
    }

    public function fetchExternalTemplateJson(Request $request, string $externalId)
    {
        $tpl = ExternalTemplate::query()->where('external_id', (int) $externalId)->first();
        if (!$tpl) {
            abort(404);
        }

        $licenseActive = (bool) Setting::get('external_templates_license_active', 0);
        if ($tpl->requires_license && !$licenseActive) {
            $msg = 'This template requires an active license.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return redirect()->route('admin.settings.index', ['category' => 'templates'])->with('error', $msg);
        }

        $baseUrl = (string) Setting::get('external_templates_api_base_url', '');
        $baseUrl = trim($baseUrl);
        $baseUrl = rtrim($baseUrl, '/');

        if ($baseUrl === '' || !filter_var($baseUrl, FILTER_VALIDATE_URL)) {
            $msg = 'External templates API base URL is missing or invalid.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->route('admin.settings.index', ['category' => 'templates'])->with('error', $msg);
        }

        $url = $baseUrl . '/wp-json/v1/templates/' . rawurlencode((string) $tpl->external_id);

        try {
            $resp = Http::timeout(25)->acceptJson()->post($url, []);
        } catch (\Throwable $e) {
            Log::warning('External template json fetch failed: ' . $e->getMessage());
            $msg = 'Failed to fetch external template JSON.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 502);
            }
            return redirect()->route('admin.settings.index', ['category' => 'templates'])->with('error', $msg);
        }

        if (!$resp->successful()) {
            $msg = 'External templates API returned an error.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 502);
            }
            return redirect()->route('admin.settings.index', ['category' => 'templates'])->with('error', $msg);
        }

        $payload = $resp->json();
        $jsonCode = is_array($payload) ? ($payload['json_code'] ?? null) : null;

        if (!is_string($jsonCode) || trim($jsonCode) === '') {
            $msg = 'External templates API did not return json_code.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->route('admin.settings.index', ['category' => 'templates'])->with('error', $msg);
        }

        $tpl->json_code = $jsonCode;
        $tpl->json_fetched_at = now();
        $tpl->save();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.settings.index', ['category' => 'templates'])->with('success', 'Fetched template JSON.');
    }

    public function activateExternalTemplatesLicense(Request $request)
    {
        $licenseKey = $request->input('license_key');
        $licenseKey = is_string($licenseKey) ? trim($licenseKey) : '';

        if ($licenseKey === '') {
            $msg = 'License key is required.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->route('admin.settings.index', ['category' => 'templates'])->with('error', $msg);
        }

        Setting::set('external_templates_license_key', $licenseKey, 'templates', 'string');
        Setting::set('external_templates_license_active', 1, 'templates', 'boolean');

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.settings.index', ['category' => 'templates'])->with('success', 'License activated.');
    }

    public function deactivateExternalTemplatesLicense(Request $request)
    {
        Setting::set('external_templates_license_active', 0, 'templates', 'boolean');

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.settings.index', ['category' => 'templates'])->with('success', 'License deactivated.');
    }
}

