<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\DeliveryServer;
use App\Models\Setting;
use App\Mail\DeliveryServerVerificationMailable;
use App\Services\ZeptoMailApiService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;

class DeliveryServerService
{
    protected function selectableDeliveryServersQueryForCustomer(Customer $customer, bool $mustAddDelivery = false, bool $canUseSystem = false): Builder
    {
        $directAllocatedIds = collect();
        if (Schema::hasTable('customer_delivery_server')) {
            $directAllocatedIds = $customer->allocatedDeliveryServers()->pluck('delivery_servers.id')
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values();
        }

        if ($directAllocatedIds->isNotEmpty()) {
            return DeliveryServer::query()
                ->whereKey($directAllocatedIds->all())
                ->where('status', 'active')
                ->where('use_for', true);
        }

        $groupIds = collect();
        if (Schema::hasTable('customer_customer_group') || method_exists($customer, 'customerGroups')) {
            $groupIds = $customer->customerGroups()->pluck('customer_groups.id')
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values();
        }

        if ($groupIds->isNotEmpty() && Schema::hasTable('customer_group_delivery_server')) {
            $groupAllocatedIds = DB::table('customer_group_delivery_server')
                ->whereIn('customer_group_id', $groupIds->all())
                ->pluck('delivery_server_id')
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values();

            if ($groupAllocatedIds->isNotEmpty()) {
                return DeliveryServer::query()
                    ->whereKey($groupAllocatedIds->all())
                    ->where('status', 'active')
                    ->where('use_for', true);
            }
        }

        return DeliveryServer::query()
            ->where('status', 'active')
            ->where('use_for', true)
            ->when($mustAddDelivery, function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            }, function ($q) use ($customer, $canUseSystem) {
                $q->where(function ($sub) use ($customer, $canUseSystem) {
                    $sub->where('customer_id', $customer->id);
                    if ($canUseSystem) {
                        $sub->orWhereNull('customer_id');
                    }
                });
            });
    }

    public function querySelectableDeliveryServersForCustomer(Customer $customer, bool $mustAddDelivery = false, bool $canUseSystem = false): Builder
    {
        return $this->selectableDeliveryServersQueryForCustomer($customer, $mustAddDelivery, $canUseSystem);
    }

    public function getSelectableDeliveryServersForCustomer(Customer $customer, bool $mustAddDelivery = false, bool $canUseSystem = false): Collection
    {
        return $this->selectableDeliveryServersQueryForCustomer($customer, $mustAddDelivery, $canUseSystem)
            ->orderBy('name')
            ->get();
    }

    public function getSelectableDeliveryServerIdsForCustomer(Customer $customer, bool $mustAddDelivery = false, bool $canUseSystem = false): array
    {
        return $this->selectableDeliveryServersQueryForCustomer($customer, $mustAddDelivery, $canUseSystem)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values()
            ->all();
    }

    public function resolveDeliveryServerForCustomer(Customer $customer, ?int $requestedServerId, bool $mustAddDelivery = false, bool $canUseSystem = false): ?DeliveryServer
    {
        if ($requestedServerId) {
            $requested = $this->selectableDeliveryServersQueryForCustomer($customer, $mustAddDelivery, $canUseSystem)
                ->with('bounceServer')
                ->whereKey($requestedServerId)
                ->first();
            if ($requested) {
                return $requested;
            }
        }

        return $this->selectableDeliveryServersQueryForCustomer($customer, $mustAddDelivery, $canUseSystem)
            ->with('bounceServer')
            ->orderBy('id')
            ->first();
    }

    protected function ensureProviderWebhooksStored(DeliveryServer $deliveryServer): void
    {
        try {
            $type = $deliveryServer->type;
            if (!in_array($type, ['mailgun', 'sendgrid', 'amazon-ses'], true)) {
                return;
            }

            $base = rtrim((string) config('app.url'), '/');
            $urls = match ($type) {
                'mailgun' => [
                    'bounce' => $base . '/webhooks/mailgun/bounce',
                    'open' => $base . '/webhooks/mailgun/open',
                    'click' => $base . '/webhooks/mailgun/click',
                ],
                'sendgrid' => [
                    'bounce' => $base . '/webhooks/sendgrid/bounce',
                    'open' => $base . '/webhooks/sendgrid/open',
                    'click' => $base . '/webhooks/sendgrid/click',
                ],
                'amazon-ses' => [
                    'sns' => $base . '/ses/sns',
                ],
            };

            $settings = $deliveryServer->settings ?? [];
            if ($type === 'amazon-ses') {
                $existing = is_array($settings['webhooks'] ?? null) ? $settings['webhooks'] : [];
                unset($existing['bounce'], $existing['open'], $existing['click']);
                $settings['webhooks'] = array_merge($existing, $urls);
            } else {
                $settings['webhooks'] = array_merge($settings['webhooks'] ?? [], $urls);
            }
            $deliveryServer->update(['settings' => $settings]);
        } catch (\Throwable $e) {
        }
    }

    /**
     * Get paginated list of delivery servers.
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = DeliveryServer::query();

        if (array_key_exists('customer_id', $filters)) {
            if ($filters['customer_id'] === null) {
                $query->whereNull('customer_id');
            } else {
                $query->where('customer_id', $filters['customer_id']);
            }
        }

        // Apply filters
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('hostname', 'like', "%{$search}%")
                    ->orWhere('from_email', 'like', "%{$search}%");
            });
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new delivery server.
     */
    public function create(array $data): DeliveryServer
    {
        $settings = $data['settings'] ?? [];

        $type = $data['type'] ?? 'smtp';
        $isSmtpLike = in_array($type, ['smtp', 'zeptomail'], true);

        $port = array_key_exists('port', $data) ? $data['port'] : null;
        if ($port === null && $isSmtpLike) {
            $port = 587;
        }

        $encryption = array_key_exists('encryption', $data) ? $data['encryption'] : null;
        if ($encryption === 'none') {
            $encryption = null;
        }
        if ($encryption === null && $isSmtpLike) {
            $encryption = 'tls';
        }

        $attributes = [
            'customer_id' => $data['customer_id'] ?? null,
            'name' => $data['name'],
            'type' => $type,
            'status' => $data['status'] ?? 'pending',
            'hostname' => $data['hostname'] ?? null,
            'username' => $data['username'] ?? null,
            'password' => $data['password'] ?? null,
            'from_email' => $data['from_email'] ?? null,
            'from_name' => $data['from_name'] ?? null,
            'reply_to_email' => $data['reply_to_email'] ?? null,
            'timeout' => $data['timeout'] ?? 30,
            'max_connection_messages' => $data['max_connection_messages'] ?? 100,
            'second_quota' => $data['second_quota'] ?? 0,
            'minute_quota' => $data['minute_quota'] ?? 0,
            'hourly_quota' => $data['hourly_quota'] ?? 0,
            'daily_quota' => $data['daily_quota'] ?? 0,
            'monthly_quota' => $data['monthly_quota'] ?? 0,
            'pause_after_send' => $data['pause_after_send'] ?? 0,
            'settings' => $settings,
            'locked' => $data['locked'] ?? false,
            'use_for' => $data['use_for'] ?? true,
            'use_for_email_to_list' => $data['use_for_email_to_list'] ?? false,
            'use_for_transactional' => $data['use_for_transactional'] ?? false,
            'bounce_server_id' => $data['bounce_server_id'] ?? null,
            'tracking_domain_id' => $data['tracking_domain_id'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];

        if ($port !== null) {
            $attributes['port'] = $port;
        }

        if ($encryption !== null) {
            $attributes['encryption'] = $encryption;
        }

        // Create the delivery server
        $deliveryServer = DeliveryServer::create($attributes);

        // Setup webhook for Mailgun servers
        if ($deliveryServer->type === 'mailgun') {
            $this->setupMailgunWebhook($deliveryServer, $data);
        }

        $this->ensureProviderWebhooksStored($deliveryServer);

        // Only SMTP servers require verification
        if (
            $deliveryServer->type === 'smtp'
            && !empty($deliveryServer->username)
            && filter_var($deliveryServer->username, FILTER_VALIDATE_EMAIL)
        ) {
            $this->sendVerificationEmail($deliveryServer);
        }

        return $deliveryServer;
    }

    /**
     * Update an existing delivery server.
     */
    public function update(DeliveryServer $deliveryServer, array $data): DeliveryServer
    {
        if (array_key_exists('encryption', $data) && $data['encryption'] === 'none') {
            $data['encryption'] = null;
        }

        if (array_key_exists('port', $data) && $data['port'] === null) {
            unset($data['port']);
        }

        if (array_key_exists('encryption', $data) && $data['encryption'] === null) {
            unset($data['encryption']);
        }

        $deliveryServer->update($data);
        $deliveryServer->refresh();

        // Setup/update webhook for Mailgun servers
        if ($deliveryServer->type === 'mailgun') {
            $this->setupMailgunWebhook($deliveryServer, $data);
        }

        $this->ensureProviderWebhooksStored($deliveryServer);

        return $deliveryServer->fresh();
    }

    public function setPrimary(DeliveryServer $deliveryServer): DeliveryServer
    {
        return DB::transaction(function () use ($deliveryServer) {
            DeliveryServer::query()
                ->when($deliveryServer->customer_id === null, function ($q) {
                    $q->whereNull('customer_id');
                }, function ($q) use ($deliveryServer) {
                    $q->where('customer_id', $deliveryServer->customer_id);
                })
                ->where('id', '!=', $deliveryServer->id)
                ->update(['is_primary' => false]);

            $deliveryServer->update(['is_primary' => true]);

            return $deliveryServer->fresh();
        });
    }

    /**
     * Delete a delivery server.
     */
    public function delete(DeliveryServer $deliveryServer): bool
    {
        // Delete webhook if it's a Mailgun server
        if ($deliveryServer->type === 'mailgun') {
            $this->deleteMailgunWebhook($deliveryServer);
        }

        return $deliveryServer->delete();
    }

    /**
     * Setup Mailgun webhook for a delivery server.
     */
    protected function setupMailgunWebhook(DeliveryServer $deliveryServer, array $data): void
    {
        try {
            $webhookService = app(MailgunWebhookService::class);
            $settings = $deliveryServer->settings ?? [];

            // Get domain and API key from settings or data
            $domain = $settings['domain'] ?? $data['settings']['domain'] ?? null;
            $apiKey = $settings['secret'] ?? $data['settings']['secret'] ?? config('services.mailgun.secret');

            // If using SMTP, try to extract domain from hostname or from_email
            if (!$domain && !empty($deliveryServer->hostname)) {
                // Mailgun SMTP hostname is usually smtp.mailgun.org or smtp.eu.mailgun.org
                // Domain should be in settings or from_email
                $domain = $settings['domain'] ?? null;
            }

            if (!$domain && !empty($deliveryServer->from_email)) {
                // Extract domain from from_email as fallback
                $domain = substr(strrchr($deliveryServer->from_email, "@"), 1);
            }

            if (!$domain || !$apiKey) {
                Log::warning("Cannot setup Mailgun webhook: missing domain or API key", [
                    'server_id' => $deliveryServer->id,
                    'has_domain' => !empty($domain),
                    'has_api_key' => !empty($apiKey),
                ]);
                return;
            }

            // Generate webhook ID (unique per server)
            $baseUrl = rtrim((string) config('app.url'), '/');
            $webhookMap = [
                'bounced' => $baseUrl . '/webhooks/mailgun/bounce',
                'failed' => $baseUrl . '/webhooks/mailgun/bounce',
                'opened' => $baseUrl . '/webhooks/mailgun/open',
                'clicked' => $baseUrl . '/webhooks/mailgun/click',
            ];

            $results = [];
            foreach ($webhookMap as $eventName => $url) {
                $results[$eventName] = $webhookService->createOrUpdateWebhook(
                    $domain,
                    $apiKey,
                    $eventName,
                    $url
                );
            }

            $settings['mailgun_webhooks'] = [
                'domain' => $domain,
                'configured_at' => now()->toIso8601String(),
                'events' => array_keys($webhookMap),
                'urls' => $webhookMap,
            ];
            $deliveryServer->update(['settings' => $settings]);

            Log::info("Mailgun webhooks configured", [
                'server_id' => $deliveryServer->id,
                'domain' => $domain,
                'success' => collect($results)->every(fn ($r) => ($r['success'] ?? false) === true),
            ]);
        } catch (\Exception $e) {
            Log::error("Exception setting up Mailgun webhook: " . $e->getMessage(), [
                'server_id' => $deliveryServer->id,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Delete Mailgun webhook when server is deleted.
     */
    protected function deleteMailgunWebhook(DeliveryServer $deliveryServer): void
    {
        try {
            $settings = $deliveryServer->settings ?? [];
            $webhookInfo = $settings['mailgun_webhooks'] ?? null;

            $webhookService = app(MailgunWebhookService::class);
            $domain = $webhookInfo['domain'] ?? $settings['domain'] ?? null;
            $apiKey = $settings['secret'] ?? config('services.mailgun.secret');

            if (!$domain || !$apiKey) {
                return;
            }

            $events = $webhookInfo['events'] ?? ['bounced', 'failed', 'opened', 'clicked'];
            foreach ($events as $eventName) {
                if (!is_string($eventName) || trim($eventName) === '') {
                    continue;
                }
                $webhookService->deleteWebhook($domain, $apiKey, trim($eventName));
            }

            Log::info("Mailgun webhooks deleted", [
                'server_id' => $deliveryServer->id,
                'domain' => $domain,
            ]);
        } catch (\Exception $e) {
            Log::error("Exception deleting Mailgun webhook: " . $e->getMessage(), [
                'server_id' => $deliveryServer->id,
            ]);
        }
    }

    /**
     * Test SMTP/API connection and send test email.
     */
    public function testConnection(array $data): array
    {
        Log::info('Starting SMTP/API connection test', [
            'test_type' => $data['test_type'] ?? 'unknown',
            'server_id' => $data['server_id'] ?? null,
            'type' => $data['type'] ?? null,
        ]);

        $testTimeoutSeconds = 8;
        $previousSocketTimeout = ini_get('default_socket_timeout');
        if ($previousSocketTimeout !== false) {
            @ini_set('default_socket_timeout', (string) $testTimeoutSeconds);
        }

        $server = null;
        
        try {
            if ($data['test_type'] === 'server') {
                $server = DeliveryServer::findOrFail($data['server_id']);
                Log::info('Using existing delivery server', [
                    'server_id' => $server->id,
                    'server_name' => $server->name,
                    'server_type' => $server->type,
                    'hostname' => $server->hostname,
                    'port' => $server->port,
                ]);
            } else {
                Log::info('Using manual configuration', [
                    'type' => $data['type'] ?? 'unknown',
                    'hostname' => $data['hostname'] ?? null,
                    'port' => $data['port'] ?? null,
                ]);
            }

            // Configure mail based on server or manual settings
            if ($server) {
                Log::info('Configuring mail from server');
                $fromEmail = $server->from_email ?? config('mail.from.address');
                $fromName = $server->from_name ?? config('mail.from.name');
            } else {
                Log::info('Configuring mail from manual settings');
                $this->configureMailFromManual($data);
                $fromEmail = $data['from_email'] ?? config('mail.from.address');
                $fromName = $data['from_name'] ?? config('mail.from.name');
            }

            if (!is_string($fromEmail) || trim($fromEmail) === '') {
                throw new \Exception('Sender email is not configured. Please set MAIL_FROM_ADDRESS (or configure From Email on the selected delivery server).');
            }

            // Log mail configuration (without sensitive data)
            Log::info('Mail configuration set', [
                'default_mailer' => config('mail.default'),
                'from_email' => $fromEmail,
                'from_name' => $fromName,
                'to_email' => $data['to_email'],
                'subject' => $data['subject'],
            ]);

            try {
                if (config('mail.default') === 'smtp') {
                    $smtpHost = (string) config('mail.mailers.smtp.host');
                    $smtpPort = (int) config('mail.mailers.smtp.port');

                    $resolved = @gethostbynamel($smtpHost);
                    $resolved = is_array($resolved) ? $resolved : [];

                    $fp = @fsockopen($smtpHost, $smtpPort > 0 ? $smtpPort : 587, $errno, $errstr, $testTimeoutSeconds);
                    if (is_resource($fp)) {
                        fclose($fp);
                    }

                    Log::info('SMTP preflight check for test email', [
                        'host' => $smtpHost,
                        'port' => $smtpPort,
                        'dns_a' => $resolved,
                        'tcp_connect_ok' => is_resource($fp),
                        'tcp_errno' => $errno ?? null,
                        'tcp_errstr' => $errstr ?? null,
                    ]);

                    if (!is_resource($fp)) {
                        throw new \Exception('SMTP preflight TCP connect failed: ' . ($errstr ?? 'unknown error') . ' (errno ' . ($errno ?? 'n/a') . ')');
                    }
                }
            } catch (\Throwable $e) {
                Log::error('SMTP preflight check failed', [
                    'error' => $e->getMessage(),
                    'smtp_host' => config('mail.mailers.smtp.host'),
                    'smtp_port' => config('mail.mailers.smtp.port'),
                ]);

                throw $e;
            }

            // Send test email
            Log::info('Attempting to send test email');

            if ($server && $server->type === 'zeptomail-api') {
                $rawBody = (string) ($data['message'] ?? '');
                $looksLikeHtml = preg_match('/<\s*\w+[^>]*>/', $rawBody) === 1;
                $htmlBody = $looksLikeHtml
                    ? $rawBody
                    : '<div style="white-space: pre-wrap;">' . nl2br(htmlspecialchars($rawBody, ENT_QUOTES, 'UTF-8')) . '</div>';
                $textBody = trim(preg_replace('/\s+/', ' ', strip_tags($rawBody)));

                $zepto = app(ZeptoMailApiService::class);
                $message = [
                    'from_email' => (string) $fromEmail,
                    'from_name' => (string) $fromName,
                    'to_email' => (string) $data['to_email'],
                    'subject' => (string) $data['subject'],
                    'htmlbody' => $htmlBody,
                    'textbody' => $textBody,
                    'client_reference' => 'delivery-server-test-' . $server->id,
                ];

                $server->loadMissing('bounceServer');
                if (empty(($server->settings ?? [])['bounce_address']) && !empty($server->bounceServer?->username)) {
                    $message['bounce_address'] = (string) $server->bounceServer->username;
                }

                $zepto->sendRaw($server, $message);

                Log::info('Test email sent successfully (ZeptoMail API)', [
                    'from' => $fromEmail,
                    'to' => $data['to_email'],
                    'subject' => $data['subject'],
                ]);

                return [
                    'from' => $fromEmail,
                    'to' => $data['to_email'],
                    'subject' => $data['subject'],
                    'server_type' => $server->type,
                ];
            }

            if ($server) {
                $this->configureMailFromServer($server);
            }

            // Ensure test email fails fast on network issues (avoid hitting PHP max_execution_time)
            try {
                $smtpMailer = (array) (config('mail.mailers.smtp') ?? []);
                $smtpMailer['timeout'] = $testTimeoutSeconds;
                \Illuminate\Support\Facades\Config::set('mail.mailers.smtp', $smtpMailer);
            } catch (\Throwable $e) {
                // ignore
            }
            
            // Load bounce server if server exists and has bounce_server_id
            $bounceServer = null;
            if ($server && $server->bounce_server_id) {
                $server->load('bounceServer');
                $bounceServer = $server->bounceServer;
                
                // Only use bounce server if it's active and has a username (email address)
                if ($bounceServer && $bounceServer->isActive() && !empty($bounceServer->username)) {
                    Log::info('Bounce server found for test email', [
                        'bounce_server_id' => $bounceServer->id,
                        'bounce_server_name' => $bounceServer->name,
                        'bounce_email' => $bounceServer->username,
                    ]);
                } else {
                    $bounceServer = null; // Reset if not active or missing username
                }
            }

            $rawBody = (string) ($data['message'] ?? '');
            $looksLikeHtml = preg_match('/<\s*\w+[^>]*>/', $rawBody) === 1;
            $htmlBody = $looksLikeHtml
                ? $rawBody
                : '<div style="white-space: pre-wrap;">' . nl2br(htmlspecialchars($rawBody, ENT_QUOTES, 'UTF-8')) . '</div>';
            $textBody = trim(preg_replace('/\s+/', ' ', strip_tags($rawBody)));

            Mail::send([], [], function ($message) use ($data, $fromEmail, $fromName, $bounceServer, $htmlBody, $textBody) {
                $message->to($data['to_email'])
                    ->subject($data['subject'])
                    ->from($fromEmail, $fromName);

                // Set Return-Path header if bounce server is configured and active
                if ($bounceServer && !empty($bounceServer->username)) {
                    $message->returnPath($bounceServer->username);
                    Log::info('Added Return-Path header to test email', [
                        'return_path' => $bounceServer->username,
                    ]);
                }

                if (!empty($htmlBody)) {
                    $message->html($htmlBody);
                }

                if (!empty($textBody)) {
                    $message->text($textBody);
                }
            });

            Log::info('Test email sent successfully', [
                'from' => $fromEmail,
                'to' => $data['to_email'],
                'subject' => $data['subject'],
            ]);

            return [
                'from' => $fromEmail,
                'to' => $data['to_email'],
                'subject' => $data['subject'],
                'server_type' => $server ? $server->type : $data['type'],
                'test_type' => $data['test_type'],
            ];
        } catch (\Swift_TransportException $e) {
            Log::error('Swift Transport Exception during email test', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \Exception('SMTP Connection Error: ' . $e->getMessage(), 0, $e);
        } catch (\Exception $e) {
            Log::error('Exception during email test', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'mail_config' => [
                    'default' => config('mail.default'),
                    'smtp_host' => config('mail.mailers.smtp.host'),
                    'smtp_port' => config('mail.mailers.smtp.port'),
                    'smtp_encryption' => config('mail.mailers.smtp.encryption'),
                    'smtp_username' => config('mail.mailers.smtp.username') ? '***' : null,
                ],
            ]);
            throw $e;
        } finally {
            if ($previousSocketTimeout !== false) {
                @ini_set('default_socket_timeout', (string) $previousSocketTimeout);
            }
        }
    }

    /**
     * Configure mail settings from delivery server.
     */
    public function configureMailFromServer(DeliveryServer $server, ?string $sendingDomainOverride = null): void
    {
        Log::info('Configuring mail from server', [
            'server_type' => $server->type,
            'server_id' => $server->id,
            'sending_domain_override' => $sendingDomainOverride,
        ]);

        try {
            $systemId = Setting::get('system_smtp_delivery_server_id');
            if ($systemId && (int) $systemId === (int) $server->id) {
                $this->configureMailFromSystemSmtp();
                return;
            }
        } catch (\Throwable $e) {
            // ignore
        }

        switch ($server->type) {
            case 'smtp':
            case 'zeptomail':
                Log::info('Configuring SMTP settings', [
                    'host' => $server->hostname,
                    'port' => $server->port ?? 587,
                    'encryption' => $server->encryption ?? 'tls',
                    'username' => $server->username,
                    'has_password' => !empty($server->password),
                ]);
                Config::set('mail.mailers.smtp', [
                    'transport' => 'smtp',
                    'host' => $server->hostname,
                    'port' => $server->port ?? 587,
                    'encryption' => $server->encryption ?? 'tls',
                    'username' => $server->username,
                    'password' => $server->password,
                    'timeout' => $server->timeout ?? 30,
                ]);
                Config::set('mail.default', 'smtp');
                break;

            case 'mailgun':
                $settings = $server->settings ?? [];
                
                // Check if Mailgun is configured as SMTP (has hostname/username/password)
                if (!empty($server->hostname) && !empty($server->username) && !empty($server->password)) {
                    // Use SMTP configuration for Mailgun
                    Log::info('Configuring Mailgun SMTP settings', [
                        'host' => $server->hostname,
                        'port' => $server->port ?? 587,
                        'encryption' => $server->encryption ?? 'tls',
                        'username' => $server->username,
                        'has_password' => !empty($server->password),
                    ]);
                    Config::set('mail.mailers.smtp', [
                        'transport' => 'smtp',
                        'host' => $server->hostname,
                        'port' => $server->port ?? 587,
                        'encryption' => $server->encryption ?? 'tls',
                        'username' => $server->username,
                        'password' => $server->password,
                        'timeout' => $server->timeout ?? 30,
                    ]);
                    Config::set('mail.default', 'smtp');
                } else {
                    // Use API configuration for Mailgun
                    if (!class_exists('Symfony\\Component\\Mailer\\Bridge\\Mailgun\\Transport\\MailgunTransportFactory')) {
                        throw new \Exception('Mailgun mail transport is not installed. Install symfony/mailgun-mailer and symfony/http-client to send via Mailgun API.');
                    }

                    $domain = $sendingDomainOverride ?: ($settings['domain'] ?? config('services.mailgun.domain'));
                    $secret = $settings['secret'] ?? config('services.mailgun.secret');
                    Log::info('Configuring Mailgun API settings', [
                        'domain' => $domain,
                        'has_secret' => !empty($secret),
                        'endpoint' => $settings['endpoint'] ?? config('services.mailgun.endpoint', 'api.mailgun.net'),
                    ]);
                    
                    if (empty($domain) || empty($secret)) {
                        Log::warning('Mailgun configuration incomplete', [
                            'has_domain' => !empty($domain),
                            'has_secret' => !empty($secret),
                        ]);
                        throw new \Exception('Mailgun configuration incomplete: Domain and API Secret are required.');
                    }
                    
                    Config::set('services.mailgun', [
                        'domain' => $domain,
                        'secret' => $secret,
                        'endpoint' => $settings['endpoint'] ?? config('services.mailgun.endpoint', 'api.mailgun.net'),
                    ]);
                    Config::set('mail.default', 'mailgun');
                }
                break;

            case 'sendgrid':
                $settings = $server->settings ?? [];
                Config::set('services.sendgrid', [
                    'api_key' => $settings['api_key'] ?? config('services.sendgrid.api_key'),
                ]);
                Config::set('mail.default', 'sendgrid');
                break;

            case 'postmark':
                $settings = $server->settings ?? [];
                Config::set('services.postmark', [
                    'token' => $settings['token'] ?? config('services.postmark.token'),
                ]);
                Config::set('mail.default', 'postmark');
                break;

            case 'amazon-ses':
                $settings = $server->settings ?? [];
                Config::set('services.ses', [
                    'key' => $settings['key'] ?? config('services.ses.key'),
                    'secret' => $settings['secret'] ?? config('services.ses.secret'),
                    'region' => $settings['region'] ?? config('services.ses.region', 'us-east-1'),
                ]);
                
                // Use custom SES transport that supports ReplyToAddresses
                Config::set('mail.mailers.ses-reply-to', [
                    'transport' => 'ses-reply-to',
                    'key' => $settings['key'] ?? config('services.ses.key'),
                    'secret' => $settings['secret'] ?? config('services.ses.secret'),
                    'region' => $settings['region'] ?? config('services.ses.region', 'us-east-1'),
                    'options' => $settings['options'] ?? [],
                ]);
                
                Config::set('mail.default', 'ses-reply-to');
                break;

            case 'sparkpost':
                $settings = $server->settings ?? [];
                Log::info('Configuring SparkPost settings', [
                    'has_secret' => !empty($settings['secret'] ?? config('services.sparkpost.secret')),
                ]);
                Config::set('services.sparkpost', [
                    'secret' => $settings['secret'] ?? config('services.sparkpost.secret'),
                ]);
                Config::set('mail.default', 'sparkpost');
                break;

            case 'mailjet':
                $settings = $server->settings ?? [];
                $apiKey = $settings['key'] ?? $server->username;
                $apiSecret = $settings['secret'] ?? $server->password;
                
                Log::info('Configuring Mailjet settings from server', [
                    'host' => $server->hostname ?? 'in-v3.mailjet.com',
                    'port' => $server->port ?? 587,
                    'encryption' => $server->encryption ?? 'tls',
                    'has_key' => !empty($apiKey),
                    'has_secret' => !empty($apiSecret),
                ]);
                
                if (empty($apiKey) || empty($apiSecret)) {
                    Log::warning('Mailjet configuration incomplete', [
                        'has_key' => !empty($apiKey),
                        'has_secret' => !empty($apiSecret),
                    ]);
                    throw new \Exception('Mailjet configuration incomplete: API Key and API Secret are required.');
                }
                
                // Mailjet uses SMTP with API key/secret as username/password
                Config::set('mail.mailers.smtp', [
                    'transport' => 'smtp',
                    'host' => $server->hostname ?? 'in-v3.mailjet.com',
                    'port' => $server->port ?? 587,
                    'encryption' => $server->encryption ?? 'tls',
                    'username' => $apiKey,
                    'password' => $apiSecret,
                    'timeout' => $server->timeout ?? 30,
                ]);
                Config::set('mail.default', 'smtp');
                break;

            case 'sendmail':
                Log::info('Configuring Sendmail');
                Config::set('mail.default', 'sendmail');
                break;
        }
        
        // Log final mail configuration
        Log::info('Mail configuration completed', [
            'default_mailer' => config('mail.default'),
            'smtp_host' => config('mail.mailers.smtp.host'),
            'smtp_port' => config('mail.mailers.smtp.port'),
        ]);
    }

    public function hasSystemSmtpConfigured(): bool
    {
        $host = env('MAIL_HOST');
        if (!is_string($host)) {
            return false;
        }

        return trim($host) !== '';
    }

    public function getSystemSmtpServerStub(): object
    {
        $systemServer = $this->getOrCreateSystemSmtpDeliveryServer();

        $host = (string) env('MAIL_HOST', '');
        $port = (int) env('MAIL_PORT', 587);
        $encryption = (string) env('MAIL_ENCRYPTION', 'tls');
        $username = env('MAIL_USERNAME');

        $fromEmail = Setting::get('from_email', env('MAIL_FROM_ADDRESS'));
        $fromName = Setting::get('from_name', env('MAIL_FROM_NAME', env('APP_NAME', 'MailZen')));

        return (object) [
            'is_system' => true,
            'id' => $systemServer?->id,
            'name' => 'System (SMTP)',
            'type' => 'smtp',
            'status' => 'active',
            'hostname' => $host,
            'port' => $port > 0 ? $port : 587,
            'encryption' => $encryption,
            'username' => is_string($username) ? $username : null,
            'from_email' => is_string($fromEmail) ? $fromEmail : null,
            'from_name' => is_string($fromName) ? $fromName : null,
            'monthly_quota' => 0,
            'is_primary' => false,
            'locked' => true,
            'use_for' => (bool) ($systemServer?->use_for ?? false),
            'use_for_transactional' => (bool) ($systemServer?->use_for_transactional ?? false),
            'use_for_email_to_list' => (bool) ($systemServer?->use_for_email_to_list ?? false),
        ];
    }

    public function getOrCreateSystemSmtpDeliveryServer(): ?DeliveryServer
    {
        if (!$this->hasSystemSmtpConfigured()) {
            return null;
        }

        try {
            $existingId = Setting::get('system_smtp_delivery_server_id');
            if ($existingId && DeliveryServer::query()->whereKey((int) $existingId)->exists()) {
                return DeliveryServer::query()->whereKey((int) $existingId)->first();
            }
        } catch (\Throwable $e) {
            // ignore
        }

        $server = DeliveryServer::query()->create([
            'customer_id' => null,
            'name' => 'System (SMTP)',
            'type' => 'smtp',
            'status' => 'active',
            'hostname' => null,
            'port' => 587,
            'username' => null,
            'password' => null,
            'encryption' => 'tls',
            'from_email' => null,
            'from_name' => null,
            'timeout' => 30,
            'max_connection_messages' => 100,
            'second_quota' => 0,
            'minute_quota' => 0,
            'hourly_quota' => 0,
            'daily_quota' => 0,
            'monthly_quota' => 0,
            'pause_after_send' => 0,
            'settings' => ['system_env' => true],
            'locked' => true,
            'use_for' => false,
            'use_for_email_to_list' => false,
            'use_for_transactional' => false,
        ]);

        try {
            Setting::set('system_smtp_delivery_server_id', (int) $server->id, 'email', 'integer');
        } catch (\Throwable $e) {
            // ignore
        }

        return $server;
    }

    /**
     * Update system server configuration from environment variables.
     */
    public function updateSystemServerFromEnv(DeliveryServer $server): void
    {
        if (!$this->hasSystemSmtpConfigured()) {
            return;
        }

        $host = (string) env('MAIL_HOST', '');
        $port = (int) env('MAIL_PORT', 587);
        $encryption = (string) env('MAIL_ENCRYPTION', 'tls');
        $username = env('MAIL_USERNAME');

        $server->update([
            'hostname' => $host,
            'port' => $port > 0 ? $port : 587,
            'encryption' => $encryption,
            'username' => is_string($username) ? $username : null,
        ]);
    }

    public function configureMailFromSystemSmtp(): void
    {
        $host = env('MAIL_HOST');
        if (!is_string($host) || trim($host) === '') {
            return;
        }

        $encryption = env('MAIL_ENCRYPTION', 'tls');
        if (!is_string($encryption) || trim($encryption) === '' || strtolower(trim($encryption)) === 'none') {
            $encryption = null;
        }

        Config::set('mail.mailers.smtp', [
            'transport' => 'smtp',
            'host' => (string) $host,
            'port' => (int) env('MAIL_PORT', 587),
            'encryption' => $encryption,
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => (int) env('MAIL_TIMEOUT', 30),
        ]);
        Config::set('mail.default', 'smtp');
    }

    /**
     * Configure mail settings from manual input.
     */
    protected function configureMailFromManual(array $data): void
    {
        Log::info('Configuring mail from manual input', [
            'type' => $data['type'] ?? 'unknown',
        ]);

        switch ($data['type']) {
            case 'smtp':
            case 'zeptomail':
                Log::info('Configuring manual SMTP settings', [
                    'host' => $data['hostname'] ?? null,
                    'port' => $data['port'] ?? 587,
                    'encryption' => $data['encryption'] ?? 'tls',
                    'username' => $data['username'] ?? null,
                    'has_password' => !empty($data['password']),
                ]);
                Config::set('mail.mailers.smtp', [
                    'transport' => 'smtp',
                    'host' => $data['hostname'],
                    'port' => $data['port'] ?? 587,
                    'encryption' => $data['encryption'] ?? 'tls',
                    'username' => $data['username'],
                    'password' => $data['password'],
                    'timeout' => 30,
                ]);
                Config::set('mail.default', 'smtp');
                break;

            case 'mailgun':
                // Check if SMTP credentials are provided (hostname, username, password)
                // or API credentials (domain, api_key)
                $hasSmtpCredentials = !empty($data['hostname']) && !empty($data['username']) && !empty($data['password']);
                $hasApiCredentials = (!empty($data['hostname']) || !empty($data['api_hostname'])) && !empty($data['api_key']);
                
                if ($hasSmtpCredentials) {
                    // Use SMTP configuration for Mailgun
                    Log::info('Configuring Mailgun via SMTP', [
                        'host' => $data['hostname'],
                        'port' => $data['port'] ?? 587,
                        'username' => $data['username'],
                        'has_password' => !empty($data['password']),
                    ]);
                    
                    Config::set('mail.mailers.smtp', [
                        'transport' => 'smtp',
                        'host' => $data['hostname'],
                        'port' => $data['port'] ?? 587,
                        'encryption' => $data['encryption'] ?? 'tls',
                        'username' => $data['username'],
                        'password' => $data['password'],
                        'timeout' => 30,
                    ]);
                    Config::set('mail.default', 'smtp');
                } elseif ($hasApiCredentials) {
                    // Use API configuration for Mailgun
                    $domain = $data['hostname'] ?? $data['api_hostname'] ?? config('services.mailgun.domain');
                    $secret = $data['api_key'] ?? config('services.mailgun.secret');
                    
                    Log::info('Configuring Mailgun via API', [
                        'domain' => $domain,
                        'has_secret' => !empty($secret),
                    ]);
                    
                    Config::set('services.mailgun', [
                        'domain' => $domain,
                        'secret' => $secret,
                    ]);
                    Config::set('mail.default', 'mailgun');
                } else {
                    Log::warning('Mailgun configuration incomplete', [
                        'has_smtp' => $hasSmtpCredentials,
                        'has_api' => $hasApiCredentials,
                    ]);
                    throw new \Exception('Mailgun configuration incomplete. Provide either SMTP credentials (hostname, username, password) or API credentials (domain, API key).');
                }
                break;

            case 'sendgrid':
                Config::set('services.sendgrid', [
                    'api_key' => $data['api_key'] ?? config('services.sendgrid.api_key'),
                ]);
                Config::set('mail.default', 'sendgrid');
                break;

            case 'postmark':
                Config::set('services.postmark', [
                    'token' => $data['api_key'] ?? config('services.postmark.token'),
                ]);
                Config::set('mail.default', 'postmark');
                break;

            case 'amazon-ses':
                Config::set('services.ses', [
                    'key' => $data['api_key'] ?? config('services.ses.key'),
                    'secret' => $data['api_secret'] ?? config('services.ses.secret'),
                    'region' => $data['hostname'] ?? $data['api_hostname'] ?? config('services.ses.region', 'us-east-1'),
                ]);
                Config::set('mail.default', 'ses');
                break;

            case 'sparkpost':
                $secret = $data['api_key'] ?? config('services.sparkpost.secret');
                Log::info('Configuring manual SparkPost settings', [
                    'has_secret' => !empty($secret),
                ]);
                Config::set('services.sparkpost', [
                    'secret' => $secret,
                ]);
                Config::set('mail.default', 'sparkpost');
                break;

            case 'mailjet':
                Log::info('Configuring manual Mailjet settings', [
                    'host' => $data['hostname'] ?? 'in-v3.mailjet.com',
                    'port' => $data['port'] ?? 587,
                    'has_key' => !empty($data['api_key']),
                    'has_secret' => !empty($data['api_secret']),
                ]);
                // Mailjet uses SMTP with API key/secret as username/password
                Config::set('mail.mailers.smtp', [
                    'transport' => 'smtp',
                    'host' => $data['hostname'] ?? 'in-v3.mailjet.com',
                    'port' => $data['port'] ?? 587,
                    'encryption' => $data['encryption'] ?? 'tls',
                    'username' => $data['api_key'] ?? $data['username'],
                    'password' => $data['api_secret'] ?? $data['password'],
                    'timeout' => 30,
                ]);
                Config::set('mail.default', 'smtp');
                break;

            case 'sendmail':
                Log::info('Configuring manual Sendmail');
                Config::set('mail.default', 'sendmail');
                break;
        }
    }

    /**
     * Send verification email to delivery server's SMTP account email (username).
     * Uses the delivery server's own SMTP configuration to verify it can send emails.
     */
    public function sendVerificationEmail(DeliveryServer $deliveryServer): bool
    {
        try {
            // Use username as the SMTP account email
            $smtpEmail = $deliveryServer->username;
            
            // Only send if username is a valid email address
            if (empty($smtpEmail) || !filter_var($smtpEmail, FILTER_VALIDATE_EMAIL)) {
                Log::warning("Cannot send verification email: username is not a valid email", [
                    'server_id' => $deliveryServer->id,
                    'username' => $smtpEmail,
                ]);
                return false;
            }

            // Only send for SMTP servers (not sendmail, API-based servers, etc.)
            if ($deliveryServer->type !== 'smtp') {
                Log::info("Skipping verification email for non-SMTP server", [
                    'server_id' => $deliveryServer->id,
                    'type' => $deliveryServer->type,
                ]);
                return false;
            }

            // Validate required SMTP settings
            if (empty($deliveryServer->hostname) || empty($deliveryServer->username) || empty($deliveryServer->password)) {
                Log::warning("Cannot send verification email: missing SMTP configuration", [
                    'server_id' => $deliveryServer->id,
                    'has_hostname' => !empty($deliveryServer->hostname),
                    'has_username' => !empty($deliveryServer->username),
                    'has_password' => !empty($deliveryServer->password),
                ]);
                return false;
            }

            // Generate verification token if not exists
            if (!$deliveryServer->verification_token) {
                $deliveryServer->generateVerificationToken();
                $deliveryServer->refresh();
            }

            // Refresh to get the latest token
            $deliveryServer->refresh();

            // Configure mailer using THIS delivery server's settings
            $mailerName = 'verification_' . $deliveryServer->id;

            $port = $deliveryServer->port ?? 587;
            $encryption = $deliveryServer->encryption ?? 'tls';
            $scheme = null;

            if ($encryption === 'none') {
                $encryption = null;
                $scheme = 'smtp';
            }
            
            Config::set("mail.mailers.{$mailerName}", [
                'transport' => 'smtp',
                'scheme' => $scheme,
                'host' => $deliveryServer->hostname,
                'port' => $port,
                'encryption' => $encryption,
                'username' => $deliveryServer->username,
                'password' => $deliveryServer->password,
                'timeout' => $deliveryServer->timeout ?? 30,
            ]);

            Log::info("Attempting to send verification email using delivery server's own SMTP config", [
                'server_id' => $deliveryServer->id,
                'email' => $smtpEmail,
                'hostname' => $deliveryServer->hostname,
                'port' => $deliveryServer->port,
                'encryption' => $deliveryServer->encryption,
                'token' => substr($deliveryServer->verification_token, 0, 10) . '...',
            ]);

            try {
                $host = (string) $deliveryServer->hostname;
                $portForLog = (int) ($deliveryServer->port ?? 0);
                $resolved = @gethostbynamel($host);
                $resolved = is_array($resolved) ? $resolved : [];
                $fp = @fsockopen($host, $portForLog > 0 ? $portForLog : 587, $errno, $errstr, 6);
                if (is_resource($fp)) {
                    fclose($fp);
                }

                Log::info('SMTP preflight check for verification email', [
                    'server_id' => $deliveryServer->id,
                    'host' => $host,
                    'port' => $portForLog,
                    'dns_a' => $resolved,
                    'tcp_connect_ok' => is_resource($fp),
                    'tcp_errno' => $errno ?? null,
                    'tcp_errstr' => $errstr ?? null,
                ]);
            } catch (\Throwable $e) {
                Log::info('SMTP preflight check failed (non-fatal)', [
                    'server_id' => $deliveryServer->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Use the delivery server's own mailer configuration
            Mail::mailer($mailerName)
                ->to($smtpEmail)
                ->send(new DeliveryServerVerificationMailable($deliveryServer));

            Log::info("Verification email sent successfully using delivery server's SMTP", [
                'server_id' => $deliveryServer->id,
                'email' => $smtpEmail,
                'hostname' => $deliveryServer->hostname,
            ]);

            return true;
        } catch (\Swift_TransportException $e) {
            Log::error("SMTP Transport error sending verification email: " . $e->getMessage(), [
                'server_id' => $deliveryServer->id,
                'email' => $deliveryServer->username,
                'hostname' => $deliveryServer->hostname,
                'error_code' => $e->getCode(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error("Failed to send verification email for delivery server: " . $e->getMessage(), [
                'server_id' => $deliveryServer->id,
                'email' => $deliveryServer->username,
                'hostname' => $deliveryServer->hostname,
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return false;
        }
    }

    /**
     * Verify delivery server using token.
     */
    public function verify(string $token): ?DeliveryServer
    {
        $deliveryServer = DeliveryServer::where('verification_token', $token)
            ->whereNull('verified_at')
            ->first();

        if (!$deliveryServer) {
            return null;
        }

        // Check if token is expired (7 days)
        $createdAt = $deliveryServer->created_at;
        if ($createdAt && $createdAt->addDays(7)->isPast()) {
            Log::warning("Verification token expired", [
                'server_id' => $deliveryServer->id,
            ]);
            return null;
        }

        $deliveryServer->markAsVerified();

        Log::info("Delivery server verified", [
            'server_id' => $deliveryServer->id,
        ]);

        return $deliveryServer;
    }
}

