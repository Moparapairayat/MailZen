<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Models\BounceServer;
use App\Models\DeliveryServer;
use App\Models\TrackingDomain;
use App\Services\DeliveryServerService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DeliveryServerController extends Controller
{
    public function __construct(
        protected DeliveryServerService $deliveryServerService
    ) {
    }

    protected function customer(Request $request)
    {
        return $request->user('sanctum');
    }

    protected function authorizeManage(Request $request, DeliveryServer $deliveryServer): DeliveryServer
    {
        $customer = $this->customer($request);

        if (!$customer || (int) $deliveryServer->customer_id !== (int) $customer->id) {
            abort(404);
        }

        return $deliveryServer;
    }

    protected function authorizeView(Request $request, DeliveryServer $deliveryServer): DeliveryServer
    {
        $customer = $this->customer($request);
        if (!$customer) {
            abort(404);
        }

        $mustAddOwn = (bool) $customer->groupSetting('servers.permissions.must_add_delivery_server', false);
        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);

        $allowed = $this->deliveryServerService
            ->querySelectableDeliveryServersForCustomer($customer, $mustAddOwn, $canUseSystem)
            ->whereKey($deliveryServer->id)
            ->exists();

        if ($allowed) {
            return $deliveryServer;
        }

        abort(404);
    }

    protected function ensureCanSelectTrackingDomain(Request $request, ?int $trackingDomainId): void
    {
        if ($trackingDomainId === null) {
            return;
        }

        $customer = $this->customer($request);
        if (!$customer) {
            abort(404);
        }

        $canSelect = (bool) $customer->groupSetting('domains.tracking_domains.select_for_servers', false);
        if (!$canSelect) {
            throw ValidationException::withMessages([
                'tracking_domain_id' => 'Tracking domain selection is not allowed for your account.',
            ]);
        }

        $trackingDomain = TrackingDomain::find($trackingDomainId);
        if (!$trackingDomain || $trackingDomain->status !== 'verified') {
            throw ValidationException::withMessages([
                'tracking_domain_id' => 'Selected tracking domain is invalid.',
            ]);
        }

        if ((int) $trackingDomain->customer_id === (int) $customer->id) {
            return;
        }

        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);
        $mustAddOwn = (bool) $customer->groupSetting('domains.tracking_domains.must_add', false);

        if (!$mustAddOwn && $canUseSystem && $trackingDomain->customer_id === null) {
            return;
        }

        throw ValidationException::withMessages([
            'tracking_domain_id' => 'Selected tracking domain is not available for your account.',
        ]);
    }

    protected function ensureCanSelectBounceServer(Request $request, ?int $bounceServerId): void
    {
        if ($bounceServerId === null) {
            return;
        }

        $customer = $this->customer($request);
        if (!$customer) {
            abort(404);
        }

        $bounceServer = BounceServer::find($bounceServerId);
        if (!$bounceServer || !$bounceServer->isActive()) {
            throw ValidationException::withMessages([
                'bounce_server_id' => 'Selected bounce server is invalid.',
            ]);
        }

        if ((int) $bounceServer->customer_id === (int) $customer->id) {
            return;
        }

        $mustAddOwn = (bool) $customer->groupSetting('servers.permissions.must_add_bounce_server', false);

        if (!$mustAddOwn && $bounceServer->customer_id === null) {
            return;
        }

        throw ValidationException::withMessages([
            'bounce_server_id' => 'Selected bounce server is not available for your account.',
        ]);
    }

    protected function sanitizeDeliveryServer(DeliveryServer $deliveryServer): array
    {
        $deliveryServer->makeHidden(['password', 'verification_token']);
        if ($deliveryServer->relationLoaded('bounceServer') && $deliveryServer->bounceServer) {
            $deliveryServer->bounceServer->makeHidden(['password']);
        }

        $data = $deliveryServer->toArray();

        if (isset($data['settings']) && is_array($data['settings'])) {
            foreach (['secret', 'api_key', 'token', 'key'] as $k) {
                if (array_key_exists($k, $data['settings'])) {
                    unset($data['settings'][$k]);
                }
            }
        }

        return $data;
    }

    public function index(Request $request)
    {
        $customer = $this->customer($request);
        $filters = $request->only(['search', 'type', 'status']);

        $mustAddOwn = (bool) $customer->groupSetting('servers.permissions.must_add_delivery_server', false);
        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);

        $deliveryServers = $this->deliveryServerService
            ->querySelectableDeliveryServersForCustomer($customer, $mustAddOwn, $canUseSystem)
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('hostname', 'like', "%{$search}%")
                        ->orWhere('from_email', 'like', "%{$search}%");
                });
            })
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('id')
            ->paginate(15);

        $items = array_map(function ($row) {
            $server = $row instanceof DeliveryServer ? $row : null;
            if (!$server) {
                return $row;
            }
            return $this->sanitizeDeliveryServer($server);
        }, $deliveryServers->items());

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $deliveryServers->currentPage(),
                'per_page' => $deliveryServers->perPage(),
                'total' => $deliveryServers->total(),
                'last_page' => $deliveryServers->lastPage(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $customer = $this->customer($request);
        $customer->enforceGroupLimit('servers.limits.max_delivery_servers', $customer->deliveryServers()->count(), 'Delivery server limit reached.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:smtp,sendmail,amazon-ses,mailgun,sendgrid,postmark,sparkpost,zeptomail,zeptomail-api'],
            'status' => ['nullable', 'in:active,inactive,pending'],
            'hostname' => ['required_if:type,zeptomail', 'nullable', 'string', 'max:255'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'username' => ['required_if:type,zeptomail', 'nullable', 'string', 'max:255'],
            'password' => ['required_if:type,zeptomail', 'nullable', 'string', 'max:255'],
            'encryption' => ['nullable', 'in:ssl,tls,none'],
            'from_email' => ['nullable', 'email', 'max:255'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'reply_to_email' => ['nullable', 'email', 'max:255'],
            'timeout' => ['nullable', 'integer', 'min:1'],
            'max_connection_messages' => ['nullable', 'integer', 'min:1'],
            'second_quota' => ['nullable', 'integer', 'min:0'],
            'minute_quota' => ['nullable', 'integer', 'min:0'],
            'hourly_quota' => ['nullable', 'integer', 'min:0'],
            'daily_quota' => ['nullable', 'integer', 'min:0'],
            'monthly_quota' => ['nullable', 'integer', 'min:0'],
            'pause_after_send' => ['nullable', 'integer', 'min:0'],
            'locked' => ['nullable', 'boolean'],
            'use_for' => ['nullable', 'boolean'],
            'use_for_email_to_list' => ['nullable', 'boolean'],
            'use_for_transactional' => ['nullable', 'boolean'],
            'tracking_domain_id' => ['nullable', 'exists:tracking_domains,id'],
            'bounce_server_id' => ['nullable', 'exists:bounce_servers,id'],
            'notes' => ['nullable', 'string'],
            'settings.domain' => ['nullable', 'string', 'max:255'],
            'settings.secret' => ['nullable', 'string', 'max:255'],
            'settings.api_key' => ['nullable', 'string', 'max:255'],
            'settings.token' => ['nullable', 'string', 'max:255'],
            'settings.key' => ['nullable', 'string', 'max:255'],
            'settings.region' => ['nullable', 'string', 'max:255'],
            'settings.send_mail_token' => ['required_if:type,zeptomail-api', 'nullable', 'string', 'max:255'],
            'settings.mode' => ['nullable', 'in:raw,template'],
            'settings.template_key' => ['nullable', 'string', 'max:255'],
            'settings.template_alias' => ['nullable', 'string', 'max:255'],
            'settings.bounce_address' => ['nullable', 'email', 'max:255'],
        ]);

        $validated['locked'] = $request->boolean('locked');
        $validated['use_for'] = $request->boolean('use_for');
        $validated['use_for_email_to_list'] = $request->boolean('use_for_email_to_list');
        $validated['use_for_transactional'] = $request->boolean('use_for_transactional');

        $this->ensureCanSelectTrackingDomain($request, $validated['tracking_domain_id'] ?? null);
        $this->ensureCanSelectBounceServer($request, $validated['bounce_server_id'] ?? null);

        $settings = [];

        switch ($validated['type']) {
            case 'mailgun':
                if ($request->has('settings.domain')) {
                    $settings['domain'] = $request->input('settings.domain');
                }
                if ($request->has('settings.secret')) {
                    $secret = $request->input('settings.secret');
                    if (is_string($secret) && trim($secret) !== '') {
                        $settings['secret'] = $secret;
                    }
                }
                break;
            case 'sendgrid':
                if ($request->has('settings.api_key')) {
                    $apiKey = $request->input('settings.api_key');
                    if (is_string($apiKey) && trim($apiKey) !== '') {
                        $settings['api_key'] = $apiKey;
                    }
                }
                break;
            case 'postmark':
                if ($request->has('settings.token')) {
                    $token = $request->input('settings.token');
                    if (is_string($token) && trim($token) !== '') {
                        $settings['token'] = $token;
                    }
                }
                break;
            case 'sparkpost':
                if ($request->has('settings.secret')) {
                    $secret = $request->input('settings.secret');
                    if (is_string($secret) && trim($secret) !== '') {
                        $settings['secret'] = $secret;
                    }
                }
                break;
            case 'amazon-ses':
                if ($request->has('settings.key')) {
                    $key = $request->input('settings.key');
                    if (is_string($key) && trim($key) !== '') {
                        $settings['key'] = $key;
                    }
                }
                if ($request->has('settings.secret')) {
                    $secret = $request->input('settings.secret');
                    if (is_string($secret) && trim($secret) !== '') {
                        $settings['secret'] = $secret;
                    }
                }
                if ($request->has('settings.region')) {
                    $region = $request->input('settings.region');
                    if (is_string($region) && trim($region) !== '') {
                        $settings['region'] = $region;
                    }
                }
                break;

            case 'zeptomail-api':
                if ($request->has('settings.send_mail_token')) {
                    $token = $request->input('settings.send_mail_token');
                    if (is_string($token) && trim($token) !== '') {
                        $settings['send_mail_token'] = $token;
                    }
                }
                if ($request->has('settings.mode')) {
                    $mode = $request->input('settings.mode');
                    if (is_string($mode) && in_array($mode, ['raw', 'template'], true)) {
                        $settings['mode'] = $mode;
                    }
                }
                if ($request->has('settings.template_key')) {
                    $templateKey = $request->input('settings.template_key');
                    if (is_string($templateKey) && trim($templateKey) !== '') {
                        $settings['template_key'] = $templateKey;
                    }
                }
                if ($request->has('settings.template_alias')) {
                    $templateAlias = $request->input('settings.template_alias');
                    if (is_string($templateAlias) && trim($templateAlias) !== '') {
                        $settings['template_alias'] = $templateAlias;
                    }
                }
                if ($request->has('settings.bounce_address')) {
                    $bounceAddress = $request->input('settings.bounce_address');
                    if (is_string($bounceAddress) && trim($bounceAddress) !== '') {
                        $settings['bounce_address'] = $bounceAddress;
                    }
                }
                break;
        }

        $validated['settings'] = $settings;
        $validated['customer_id'] = $customer->id;

        $deliveryServer = $this->deliveryServerService->create($validated);

        return response()->json([
            'data' => $this->sanitizeDeliveryServer($deliveryServer),
        ], 201);
    }

    public function show(Request $request, DeliveryServer $deliveryServer)
    {
        $deliveryServer = $this->authorizeView($request, $deliveryServer);
        $deliveryServer->load(['trackingDomain', 'bounceServer']);

        return response()->json([
            'data' => $this->sanitizeDeliveryServer($deliveryServer),
        ]);
    }

    public function update(Request $request, DeliveryServer $deliveryServer)
    {
        $deliveryServer = $this->authorizeManage($request, $deliveryServer);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:smtp,sendmail,amazon-ses,mailgun,sendgrid,postmark,sparkpost,zeptomail,zeptomail-api'],
            'status' => ['nullable', 'in:active,inactive,pending'],
            'hostname' => ['nullable', 'string', 'max:255'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'encryption' => ['nullable', 'in:ssl,tls,none'],
            'from_email' => ['nullable', 'email', 'max:255'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'reply_to_email' => ['nullable', 'email', 'max:255'],
            'timeout' => ['nullable', 'integer', 'min:1'],
            'max_connection_messages' => ['nullable', 'integer', 'min:1'],
            'second_quota' => ['nullable', 'integer', 'min:0'],
            'minute_quota' => ['nullable', 'integer', 'min:0'],
            'hourly_quota' => ['nullable', 'integer', 'min:0'],
            'daily_quota' => ['nullable', 'integer', 'min:0'],
            'monthly_quota' => ['nullable', 'integer', 'min:0'],
            'pause_after_send' => ['nullable', 'integer', 'min:0'],
            'locked' => ['nullable', 'boolean'],
            'use_for' => ['nullable', 'boolean'],
            'use_for_email_to_list' => ['nullable', 'boolean'],
            'use_for_transactional' => ['nullable', 'boolean'],
            'tracking_domain_id' => ['nullable', 'exists:tracking_domains,id'],
            'bounce_server_id' => ['nullable', 'exists:bounce_servers,id'],
            'notes' => ['nullable', 'string'],
            'settings.domain' => ['nullable', 'string', 'max:255'],
            'settings.secret' => ['nullable', 'string', 'max:255'],
            'settings.api_key' => ['nullable', 'string', 'max:255'],
            'settings.token' => ['nullable', 'string', 'max:255'],
            'settings.key' => ['nullable', 'string', 'max:255'],
            'settings.region' => ['nullable', 'string', 'max:255'],
            'settings.send_mail_token' => ['nullable', 'string', 'max:255'],
            'settings.mode' => ['nullable', 'in:raw,template'],
            'settings.template_key' => ['nullable', 'string', 'max:255'],
            'settings.template_alias' => ['nullable', 'string', 'max:255'],
            'settings.bounce_address' => ['nullable', 'email', 'max:255'],
        ]);

        if (array_key_exists('password', $validated)) {
            $password = $validated['password'];
            if (!is_string($password) || trim($password) === '') {
                unset($validated['password']);
            }
        }

        $validated['locked'] = $request->boolean('locked');
        $validated['use_for'] = $request->boolean('use_for');
        $validated['use_for_email_to_list'] = $request->boolean('use_for_email_to_list');
        $validated['use_for_transactional'] = $request->boolean('use_for_transactional');

        $this->ensureCanSelectTrackingDomain($request, $validated['tracking_domain_id'] ?? null);
        $this->ensureCanSelectBounceServer($request, $validated['bounce_server_id'] ?? null);

        $settings = $deliveryServer->settings ?? [];

        if ($validated['type'] === 'mailgun') {
            if ($request->has('settings.domain')) {
                $settings['domain'] = $request->input('settings.domain');
            }
            if ($request->has('settings.secret')) {
                $secret = $request->input('settings.secret');
                if (is_string($secret) && trim($secret) !== '') {
                    $settings['secret'] = $secret;
                }
            }
        }

        if ($validated['type'] === 'sendgrid') {
            if ($request->has('settings.api_key')) {
                $apiKey = $request->input('settings.api_key');
                if (is_string($apiKey) && trim($apiKey) !== '') {
                    $settings['api_key'] = $apiKey;
                }
            }
        }

        if ($validated['type'] === 'postmark') {
            if ($request->has('settings.token')) {
                $token = $request->input('settings.token');
                if (is_string($token) && trim($token) !== '') {
                    $settings['token'] = $token;
                }
            }
        }

        if ($validated['type'] === 'sparkpost') {
            if ($request->has('settings.secret')) {
                $secret = $request->input('settings.secret');
                if (is_string($secret) && trim($secret) !== '') {
                    $settings['secret'] = $secret;
                }
            }
        }

        if ($validated['type'] === 'amazon-ses') {
            if ($request->has('settings.key')) {
                $key = $request->input('settings.key');
                if (is_string($key) && trim($key) !== '') {
                    $settings['key'] = $key;
                }
            }
            if ($request->has('settings.secret')) {
                $secret = $request->input('settings.secret');
                if (is_string($secret) && trim($secret) !== '') {
                    $settings['secret'] = $secret;
                }
            }
            if ($request->has('settings.region')) {
                $region = $request->input('settings.region');
                if (is_string($region) && trim($region) !== '') {
                    $settings['region'] = $region;
                }
            }
        }

        if ($validated['type'] === 'zeptomail-api') {
            if ($request->has('settings.send_mail_token')) {
                $token = $request->input('settings.send_mail_token');
                if (is_string($token) && trim($token) !== '') {
                    $settings['send_mail_token'] = $token;
                }
            }

            if ($request->has('settings.mode')) {
                $mode = $request->input('settings.mode');
                if (is_string($mode) && in_array($mode, ['raw', 'template'], true)) {
                    $settings['mode'] = $mode;
                }
            }

            if ($request->has('settings.template_key')) {
                $templateKey = $request->input('settings.template_key');
                if (is_string($templateKey) && trim($templateKey) !== '') {
                    $settings['template_key'] = $templateKey;
                }
            }

            if ($request->has('settings.template_alias')) {
                $templateAlias = $request->input('settings.template_alias');
                if (is_string($templateAlias) && trim($templateAlias) !== '') {
                    $settings['template_alias'] = $templateAlias;
                }
            }

            if ($request->has('settings.bounce_address')) {
                $bounceAddress = $request->input('settings.bounce_address');
                if (is_string($bounceAddress) && trim($bounceAddress) !== '') {
                    $settings['bounce_address'] = $bounceAddress;
                }
            }
        }

        $validated['settings'] = $settings;

        $updated = $this->deliveryServerService->update($deliveryServer, $validated);

        return response()->json([
            'data' => $this->sanitizeDeliveryServer($updated),
        ]);
    }

    public function destroy(Request $request, DeliveryServer $deliveryServer)
    {
        $deliveryServer = $this->authorizeManage($request, $deliveryServer);
        $this->deliveryServerService->delete($deliveryServer);

        return response()->json(['success' => true]);
    }

    public function testEmail(Request $request, DeliveryServer $deliveryServer)
    {
        $deliveryServer = $this->authorizeView($request, $deliveryServer);

        $validated = $request->validate([
            'to_email' => ['required', 'email', 'max:255'],
        ]);

        try {
            $result = $this->deliveryServerService->testConnection([
                'test_type' => 'server',
                'server_id' => $deliveryServer->id,
                'to_email' => $validated['to_email'],
                'subject' => 'Test Email from MailZen',
                'message' => 'This is a test email from MailZen. If you received this email, your delivery server is working correctly!',
            ]);

            return response()->json([
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to send test email: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function verify(Request $request, DeliveryServer $deliveryServer)
    {
        $deliveryServer = $this->authorizeManage($request, $deliveryServer);

        $validated = $request->validate([
            'token' => ['required', 'string'],
        ]);

        if ($deliveryServer->type !== 'smtp') {
            return response()->json([
                'message' => 'Verification is only required for SMTP delivery servers.',
                'data' => $this->sanitizeDeliveryServer($deliveryServer),
            ]);
        }

        if ($deliveryServer->verification_token !== $validated['token']) {
            return response()->json([
                'message' => 'Invalid verification token.',
            ], 422);
        }

        if ($deliveryServer->isVerified()) {
            return response()->json([
                'message' => 'This delivery server is already verified.',
                'data' => $this->sanitizeDeliveryServer($deliveryServer),
            ]);
        }

        $verified = $this->deliveryServerService->verify($validated['token']);

        if (!$verified) {
            return response()->json([
                'message' => 'Invalid or expired verification token.',
            ], 422);
        }

        return response()->json([
            'message' => 'Delivery server verified successfully!',
            'data' => $this->sanitizeDeliveryServer($verified),
        ]);
    }

    public function resendVerification(Request $request, DeliveryServer $deliveryServer)
    {
        $deliveryServer = $this->authorizeManage($request, $deliveryServer);

        if ($deliveryServer->type !== 'smtp') {
            return response()->json([
                'message' => 'Verification is only required for SMTP delivery servers.',
                'data' => $this->sanitizeDeliveryServer($deliveryServer),
            ]);
        }

        if (empty($deliveryServer->username) || !filter_var($deliveryServer->username, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'message' => 'Cannot send verification email: SMTP account email (username) is not configured or invalid.',
            ], 422);
        }

        if ($deliveryServer->isVerified()) {
            return response()->json([
                'message' => 'This delivery server is already verified.',
                'data' => $this->sanitizeDeliveryServer($deliveryServer),
            ]);
        }

        $sent = $this->deliveryServerService->sendVerificationEmail($deliveryServer);

        if (!$sent) {
            return response()->json([
                'message' => 'Failed to send verification email. Please check your mail configuration and logs.',
            ], 422);
        }

        return response()->json([
            'message' => 'Verification email sent successfully! Check your inbox at ' . $deliveryServer->username,
        ]);
    }
}
