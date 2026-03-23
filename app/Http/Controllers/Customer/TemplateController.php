<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\BuiltInTemplateSetting;
use App\Models\DeliveryServer;
use App\Models\ListSubscriber;
use App\Models\Plan;
use App\Models\PublicTemplate;
use App\Models\PublicTemplateCategory;
use App\Models\Template;
use App\Services\AI\AiTemplateService;
use App\Services\DeliveryServerService;
use App\Services\ZeptoMailApiService;
use App\Services\TemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TemplateController extends Controller
{
    public function __construct(
        protected TemplateService $templateService,
        protected AiTemplateService $aiTemplateService
    ) {
        $this->middleware('customer.access:templates.permissions.can_access_templates')->only([
            'index',
            'show',
            'preview',
            'getContent',
            'sendTestEmail',
        ]);
        $this->middleware('customer.access:templates.permissions.can_create_templates')->only(['create', 'store', 'createUnlayer', 'storeUnlayer', 'duplicate']);
        $this->middleware('customer.access:templates.permissions.can_edit_templates')->only(['edit', 'update', 'editUnlayer', 'updateUnlayer']);
        $this->middleware('customer.access:templates.permissions.can_delete_templates')->only(['destroy']);
        $this->middleware('customer.access:templates.permissions.can_import_templates')->only(['importGallery', 'importContent', 'importFileContent']);
        $this->middleware('customer.access:templates.permissions.can_use_ai_creator')->only(['aiGenerate']);
    }

    private function getSelectableDeliveryServers(): \Illuminate\Support\Collection
    {
        $customer = auth('customer')->user();
        if (!$customer) {
            return collect();
        }

        $mustAddDelivery = (bool) $customer->groupSetting('servers.permissions.must_add_delivery_server', false);
        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);

        return app(DeliveryServerService::class)->getSelectableDeliveryServersForCustomer(
            $customer,
            $mustAddDelivery,
            $canUseSystem
        );
    }

    private function authorizeSelectableDeliveryServer(int $deliveryServerId): DeliveryServer
    {
        $customer = auth('customer')->user();
        if (!$customer) {
            abort(404);
        }

        $mustAddDelivery = (bool) $customer->groupSetting('servers.permissions.must_add_delivery_server', false);
        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);

        $allowed = app(DeliveryServerService::class)
            ->querySelectableDeliveryServersForCustomer($customer, $mustAddDelivery, $canUseSystem)
            ->whereKey($deliveryServerId)
            ->exists();

        if (!$allowed) {
            abort(404);
        }

        return DeliveryServer::query()->where('status', 'active')->where('use_for', true)->findOrFail($deliveryServerId);
    }

    private function normalizeGrapesJsData(mixed $value): ?array
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $decoded = json_decode($value, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

    private function buildUnlayerData(mixed $value): ?array
    {
        $decoded = $this->normalizeGrapesJsData($value);
        if ($decoded === null) {
            return null;
        }

        return [
            'builder' => 'unlayer',
            'unlayer' => $decoded,
        ];
    }

    private function unlayerDesignFromTemplate(Template $template): ?array
    {
        $data = $template->grapesjs_data;
        if (!is_array($data)) {
            return null;
        }

        if (($data['builder'] ?? null) !== 'unlayer') {
            return null;
        }

        $unlayer = $data['unlayer'] ?? null;
        return is_array($unlayer) ? $unlayer : null;
    }

    private function htmlDoesNotContainDataImageRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if (!is_string($value) || $value === '') {
                return;
            }

            if (stripos($value, 'data:image/') !== false) {
                $fail('Inline base64 images (data:image/...) are not allowed. Upload images and use image URLs instead.');
                return;
            }

            if (preg_match('/<img\b(?![^>]*\bsrc\s*=)[^>]*>/i', $value) === 1) {
                $fail('One or more images are missing a src attribute. Please upload the image and ensure it has a valid URL.');
                return;
            }

            if (preg_match('/<img\b[^>]*\bsrc\s*=\s*(["\"])\s*\1/i', $value) === 1) {
                $fail('One or more images have an empty src. Please upload the image and ensure it has a valid URL.');
                return;
            }

            if (preg_match_all('/<img\b[^>]*\bsrc\s*=\s*(["\"])\s*([^"\"]+)\s*\1/i', $value, $matches) > 0) {
                foreach ($matches[2] as $src) {
                    $src = trim((string) $src);
                    if ($src === '') {
                        $fail('One or more images have an empty src. Please upload the image and ensure it has a valid URL.');
                        return;
                    }

                    $isAbsoluteHttp = preg_match('/^https?:\/\//i', $src) === 1;
                    $isProtocolRelative = str_starts_with($src, '//');
                    $isCid = preg_match('/^cid:/i', $src) === 1;

                    if (!$isAbsoluteHttp && !$isProtocolRelative && !$isCid) {
                        $fail('Image src must be an absolute URL (https://...) that is publicly accessible.');
                        return;
                    }
                }
            }
        };
    }

    private function fileGalleryRoot(string $builder): string
    {
        $builder = trim($builder);
        if ($builder === '') {
            $builder = 'unlayer';
        }

        return resource_path('template-gallery/' . $builder);
    }

    private function fileGalleryItems(string $builder, string $tab): array
    {
        if ($builder !== 'unlayer') {
            return [];
        }

        $root = $this->fileGalleryRoot($builder);
        if (!is_dir($root)) {
            return [];
        }

        $dir = $tab === 'pro' ? ($root . DIRECTORY_SEPARATOR . 'pro') : $root;
        if (!is_dir($dir)) {
            return [];
        }

        $files = File::glob($dir . DIRECTORY_SEPARATOR . '*.json');
        if (!is_array($files)) {
            return [];
        }

        $items = [];
        $customer = auth('customer')->user();
        $groupIds = [];
        if ($customer) {
            $groupIds = $customer->customerGroups()
                ->pluck('customer_groups.id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();
        }

        foreach ($files as $path) {
            if (!is_string($path) || $path === '' || !is_file($path)) {
                continue;
            }

            $relative = ltrim(str_replace($root, '', $path), DIRECTORY_SEPARATOR);
            $key = sha1($builder . ':' . $relative);

            $name = pathinfo($path, PATHINFO_FILENAME);
            $description = null;
            $thumbnail = null;
            $category = 'other';

            try {
                $raw = File::get($path);
                if (is_string($raw) && $raw !== '') {
                    $decoded = json_decode($raw, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        if (is_string($decoded['name'] ?? null) && trim((string) $decoded['name']) !== '') {
                            $name = trim((string) $decoded['name']);
                        }
                        if (is_string($decoded['description'] ?? null) && trim((string) $decoded['description']) !== '') {
                            $description = trim((string) $decoded['description']);
                        }
                        if (is_string($decoded['thumbnail'] ?? null) && trim((string) $decoded['thumbnail']) !== '') {
                            $thumbnail = trim((string) $decoded['thumbnail']);
                        }
                        if (is_string($decoded['category'] ?? null) && trim((string) $decoded['category']) !== '') {
                            $category = trim((string) $decoded['category']);
                        }
                    }
                }
            } catch (\Throwable $e) {
            }

            $setting = BuiltInTemplateSetting::firstOrCreate(
                [
                    'builder' => $builder,
                    'template_key' => $key,
                ],
                [
                    'relative_path' => $relative,
                    'name' => $name,
                    'is_active' => true,
                ]
            );

            if (($setting->name ?? null) !== $name || ($setting->relative_path ?? null) !== $relative) {
                $setting->forceFill([
                    'name' => $name,
                    'relative_path' => $relative,
                ])->save();
            }

            if (!$setting->is_active) {
                continue;
            }

            $hasRestrictions = $setting->customerGroups()->exists();
            if ($hasRestrictions) {
                if (empty($groupIds)) {
                    continue;
                }
                $allowed = $setting->customerGroups()->whereIn('customer_groups.id', $groupIds)->exists();
                if (!$allowed) {
                    continue;
                }
            }

            $items[] = [
                'id' => $key,
                'name' => $name,
                'description' => $description,
                'thumbnail' => $thumbnail,
                'category' => $category,
                'builder' => 'unlayer',
                'source' => 'file',
                'is_ai' => false,
                'content_url' => route('customer.templates.import.file.content', ['key' => $key]),
            ];
        }

        usort($items, function ($a, $b) {
            return strcmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
        });

        return $items;
    }

    private function fileGalleryPayloadByKey(string $builder, string $key): ?array
    {
        if ($builder !== 'unlayer') {
            return null;
        }

        $root = $this->fileGalleryRoot($builder);
        if (!is_dir($root)) {
            return null;
        }

        $files = File::allFiles($root);
        foreach ($files as $file) {
            $path = $file->getPathname();
            if (!is_string($path) || !str_ends_with(strtolower($path), '.json')) {
                continue;
            }

            $relative = ltrim(str_replace($root, '', $path), DIRECTORY_SEPARATOR);
            $computed = sha1($builder . ':' . $relative);
            if (!hash_equals($computed, $key)) {
                continue;
            }

            $setting = BuiltInTemplateSetting::firstOrCreate(
                [
                    'builder' => $builder,
                    'template_key' => $key,
                ],
                [
                    'relative_path' => $relative,
                    'name' => pathinfo($path, PATHINFO_FILENAME),
                    'is_active' => true,
                ]
            );

            if (!$setting->is_active) {
                return null;
            }

            $customer = auth('customer')->user();
            if ($customer) {
                $groupIds = $customer->customerGroups()
                    ->pluck('customer_groups.id')
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->all();

                $hasRestrictions = $setting->customerGroups()->exists();
                if ($hasRestrictions) {
                    $allowed = !empty($groupIds) && $setting->customerGroups()->whereIn('customer_groups.id', $groupIds)->exists();
                    if (!$allowed) {
                        abort(403, 'You do not have permission to access this template.');
                    }
                }
            }

            $raw = File::get($path);
            $decoded = json_decode((string) $raw, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                return null;
            }

            $design = null;
            if (is_array($decoded['design'] ?? null)) {
                $design = $decoded['design'];
            } elseif (!isset($decoded['design'])) {
                $design = $decoded;
            }

            if (!is_array($design)) {
                return null;
            }

            $name = is_string($decoded['name'] ?? null) && trim((string) $decoded['name']) !== ''
                ? trim((string) $decoded['name'])
                : pathinfo($path, PATHINFO_FILENAME);

            if (($setting->name ?? null) !== $name || ($setting->relative_path ?? null) !== $relative) {
                $setting->forceFill([
                    'name' => $name,
                    'relative_path' => $relative,
                ])->save();
            }

            $description = is_string($decoded['description'] ?? null) && trim((string) $decoded['description']) !== ''
                ? trim((string) $decoded['description'])
                : null;

            $html = $this->unlayerPreviewHtmlFromDesign($design);

            $plain = is_string($decoded['plain_text_content'] ?? null) ? (string) $decoded['plain_text_content'] : null;
            if ($plain === null && is_string($decoded['plain'] ?? null)) {
                $plain = (string) $decoded['plain'];
            }

            return [
                'id' => $key,
                'name' => $name,
                'description' => $description,
                'html_content' => $html,
                'plain_text_content' => $plain,
                'builder' => 'unlayer',
                'builder_data' => $design,
            ];
        }

        return null;
    }

    private function unlayerPreviewHtmlFromDesign(array $design): string
    {
        $projectId = config('services.unlayer.project_id');
        $projectId = is_numeric($projectId) ? (int) $projectId : null;

        $designJson = json_encode(
            $design,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        );
        if (!is_string($designJson) || trim($designJson) === '') {
            $designJson = 'null';
        }

        $projectIdJson = $projectId !== null ? json_encode($projectId) : 'null';

        return <<<HTML
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    html, body { margin: 0; padding: 0; background: #f9fafb; height: 100%; }
    #status {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100%;
      min-height: 400px;
      font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial;
      font-size: 14px;
      font-weight: 500;
      color: #6b7280;
    }
    #status .spinner {
      width: 40px;
      height: 40px;
      border: 3px solid #e5e7eb;
      border-top-color: #4f46e5;
      border-radius: 50%;
      animation: spin 0.8s linear infinite;
      margin-bottom: 16px;
    }
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    #output { width: 100%; background: #ffffff; }
    #editor { width: 1px; height: 1px; overflow: hidden; position: absolute; left: -9999px; top: -9999px; }
  </style>
</head>
<body>
  <div id="status"><div class="spinner"></div>Rendering preview…</div>
  <div id="output"></div>
  <div id="editor"></div>

  <script src="https://editor.unlayer.com/embed.js"></script>
  <script>
    (function () {
      try {
        var design = $designJson;
        var projectId = $projectIdJson;

        if (!design || typeof design !== 'object') {
          document.getElementById('status').textContent = 'Preview not available: invalid Unlayer design JSON.';
          return;
        }

        var initOptions = { id: 'editor', displayMode: 'email' };
        if (projectId) {
          initOptions.projectId = Number(projectId);
        }

        unlayer.init(initOptions);

        unlayer.addEventListener('editor:ready', function () {
          unlayer.loadDesign(design);
          unlayer.exportHtml(function (data) {
            var html = (data && data.html) ? String(data.html) : '';
            if (!html.trim()) {
              document.getElementById('status').textContent = 'Preview not available.';
              return;
            }
            document.getElementById('status').remove();
            document.getElementById('output').innerHTML = html;
          });
        });
      } catch (e) {
        document.getElementById('status').textContent = 'Preview not available.';
      }
    })();
  </script>
</body>
</html>
HTML;
    }

    private function renderUnlayerDesignToHtml(array $design): ?string
    {
        $apiKey = (string) config('services.unlayer.api_key');
        $apiKey = trim($apiKey);
        if ($apiKey === '') {
            return null;
        }

        $cacheKey = 'unlayer:export_html:' . sha1(json_encode($design));

        return Cache::remember($cacheKey, now()->addDay(), function () use ($apiKey, $design) {
            try {
                $res = Http::withHeaders([
                    'Authorization' => 'Basic ' . base64_encode($apiKey),
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->post('https://api.unlayer.com/v2/export/html', [
                    'displayMode' => 'email',
                    'design' => $design,
                ]);

                if (!$res->ok()) {
                    return null;
                }

                $payload = $res->json();
                $html = $payload['html'] ?? null;
                if (!is_string($html) || trim($html) === '') {
                    return null;
                }

                return $html;
            } catch (\Throwable $e) {
                return null;
            }
        });
    }

    /**
     * Display a listing of templates.
     */
    public function index(Request $request)
    {
        $customer = auth('customer')->user();
        $filters = $request->only(['search', 'type']);
        $templates = $this->templateService->getPaginated($customer, $filters);

        return view('customer.templates.index', compact('templates', 'filters'));
    }

    /**
     * Show the form for creating a new template.
     */
    public function create(Request $request)
    {
        $type = $request->query('type');
        
        return view('customer.templates.create', [
            'type' => $type,
        ]);
    }

    public function createUnlayer(Request $request)
    {
        $type = $request->query('type');
        
        return view('customer.templates.unlayer.create', [
            'unlayerProjectId' => config('services.unlayer.project_id'),
            'type' => $type,
        ]);
    }

    /**
     * Store a newly created template.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['nullable', 'in:email,campaign,transactional,autoresponder,footer,signature'],
            'html_content' => ['nullable', 'string', $this->htmlDoesNotContainDataImageRule()],
            'plain_text_content' => ['nullable', 'string'],
            'grapesjs_data' => ['nullable'],
            'settings' => ['nullable', 'array'],
            'is_public' => ['nullable', 'boolean'],
        ]);

        $validated['grapesjs_data'] = $this->normalizeGrapesJsData($request->input('grapesjs_data'));

        $customer = auth('customer')->user();
        $template = $this->templateService->create($customer, $validated);

        return redirect()
            ->route('customer.templates.show', $template)
            ->with('success', 'Template created successfully.');
    }

    public function storeUnlayer(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['nullable', 'in:email,campaign,transactional,autoresponder,footer,signature'],
            'html_content' => ['nullable', 'string', $this->htmlDoesNotContainDataImageRule()],
            'plain_text_content' => ['nullable', 'string'],
            'grapesjs_data' => ['nullable'],
            'settings' => ['nullable', 'array'],
            'is_public' => ['nullable', 'boolean'],
        ]);

        $validated['grapesjs_data'] = $this->buildUnlayerData($request->input('grapesjs_data'));

        if (empty($validated['plain_text_content']) && !empty($validated['html_content'])) {
            $validated['plain_text_content'] = trim(preg_replace('/\s+/', ' ', strip_tags($validated['html_content'])));
        }

        $customer = auth('customer')->user();
        $template = $this->templateService->create($customer, $validated);

        return redirect()
            ->route('customer.templates.show', $template)
            ->with('success', 'Template created successfully.');
    }

    /**
     * Display the specified template.
     */
    public function show(Template $template)
    {
        $customer = auth('customer')->user();
        if (!$this->templateService->canAccessTemplate($template, $customer)) {
            abort(403, 'You do not have permission to view this template.');
        }

        $deliveryServers = $this->getSelectableDeliveryServers();

        return view('customer.templates.show', compact('template', 'deliveryServers'));
    }

    public function sendTestEmail(Request $request, Template $template)
    {
        $customer = auth('customer')->user();
        if (!$customer) {
            abort(404);
        }

        if ($template->customer_id !== $customer->id && !$template->is_public) {
            abort(403, 'You do not have permission to access this template.');
        }

        $validated = $request->validate([
            'delivery_server_id' => ['required', 'integer', 'exists:delivery_servers,id'],
            'to_email' => ['required', 'email', 'max:255'],
        ]);

        $deliveryServer = $this->authorizeSelectableDeliveryServer((int) $validated['delivery_server_id']);
        $deliveryServer->loadMissing('bounceServer');

        try {
            if ($deliveryServer->type === 'zeptomail-api') {
                $fromEmail = $deliveryServer->from_email ?? config('mail.from.address');
                $fromName = $deliveryServer->from_name ?? config('mail.from.name');

                $html = $template->html_content ?? '';
                $text = $template->plain_text_content ?? strip_tags($html);

                $message = [
                    'from_email' => (string) $fromEmail,
                    'from_name' => (string) $fromName,
                    'to_email' => (string) $validated['to_email'],
                    'subject' => 'Test: ' . ($template->name ?? 'Template'),
                    'htmlbody' => (string) $html,
                    'textbody' => (string) $text,
                    'client_reference' => 'template-test-' . $template->id,
                ];

                if (empty(($deliveryServer->settings ?? [])['bounce_address']) && !empty($deliveryServer->bounceServer?->username)) {
                    $message['bounce_address'] = (string) $deliveryServer->bounceServer->username;
                }

                app(ZeptoMailApiService::class)->sendRaw($deliveryServer, $message);

                return back()->with('success', 'Test email sent successfully!');
            }

            app(DeliveryServerService::class)->configureMailFromServer($deliveryServer);

            $fromEmail = $deliveryServer->from_email ?? config('mail.from.address');
            $fromName = $deliveryServer->from_name ?? config('mail.from.name');

            $html = $template->html_content ?? '';
            $text = $template->plain_text_content ?? strip_tags($html);

            Mail::send([], [], function ($message) use ($validated, $template, $fromEmail, $fromName, $html, $text, $deliveryServer) {
                $message->to($validated['to_email'])
                    ->subject('Test: ' . ($template->name ?? 'Template'))
                    ->from($fromEmail, $fromName);

                if ($deliveryServer->bounceServer && !empty($deliveryServer->bounceServer->username)) {
                    $message->returnPath($deliveryServer->bounceServer->username);
                }

                if (!empty($html)) {
                    $message->html($html);
                }

                if (!empty($text)) {
                    $message->text($text);
                }
            });

            return back()->with('success', 'Test email sent successfully!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified template.
     */
    public function edit(Template $template)
    {
        // Check if user owns the template or it's public and accessible
        $customer = auth('customer')->user();
        if ($template->customer_id !== $customer->id && !$this->templateService->canAccessTemplate($template, $customer)) {
            abort(403, 'You do not have permission to edit this template.');
        }

        return view('customer.templates.edit', compact('template'));
    }

    public function editUnlayer(Template $template)
    {
        $customer = auth('customer')->user();
        if ($template->customer_id !== $customer->id && !$this->templateService->canAccessTemplate($template, $customer)) {
            abort(403, 'You do not have permission to edit this template.');
        }

        return view('customer.templates.unlayer.edit', [
            'template' => $template,
            'unlayerProjectId' => config('services.unlayer.project_id'),
            'unlayerDesign' => $this->unlayerDesignFromTemplate($template),
        ]);
    }

    /**
     * Update the specified template.
     */
    public function update(Request $request, Template $template)
    {
        // Check if user owns the template
        $customer = auth('customer')->user();
        if ($template->customer_id !== $customer->id) {
            abort(403, 'You do not have permission to update this template.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['nullable', 'in:email,campaign,transactional,autoresponder,footer,signature'],
            'html_content' => ['nullable', 'string', $this->htmlDoesNotContainDataImageRule()],
            'plain_text_content' => ['nullable', 'string'],
            'grapesjs_data' => ['nullable'],
            'settings' => ['nullable', 'array'],
            'is_public' => ['nullable', 'boolean'],
        ]);

        $validated['grapesjs_data'] = $this->normalizeGrapesJsData($request->input('grapesjs_data'));

        $this->templateService->update($template, $validated);

        return redirect()
            ->route('customer.templates.show', $template)
            ->with('success', 'Template updated successfully.');
    }

    public function updateUnlayer(Request $request, Template $template)
    {
        $customer = auth('customer')->user();
        if ($template->customer_id !== $customer->id) {
            abort(403, 'You do not have permission to update this template.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['nullable', 'in:email,campaign,transactional,autoresponder,footer,signature'],
            'html_content' => ['nullable', 'string', $this->htmlDoesNotContainDataImageRule()],
            'plain_text_content' => ['nullable', 'string'],
            'grapesjs_data' => ['nullable'],
            'settings' => ['nullable', 'array'],
            'is_public' => ['nullable', 'boolean'],
        ]);

        $validated['grapesjs_data'] = $this->buildUnlayerData($request->input('grapesjs_data'));

        if (empty($validated['plain_text_content']) && !empty($validated['html_content'])) {
            $validated['plain_text_content'] = trim(preg_replace('/\s+/', ' ', strip_tags($validated['html_content'])));
        }

        $this->templateService->update($template, $validated);

        return redirect()
            ->route('customer.templates.show', $template)
            ->with('success', 'Template updated successfully.');
    }

    /**
     * Remove the specified template.
     */
    public function destroy(Template $template)
    {
        // Check if user owns the template
        $customer = auth('customer')->user();
        if ($template->customer_id !== $customer->id) {
            abort(403, 'You do not have permission to delete this template.');
        }

        try {
            $this->templateService->delete($template);
            return redirect()
                ->route('customer.templates.index')
                ->with('success', 'Template deleted successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->route('customer.templates.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Duplicate a template.
     */
    public function duplicate(Template $template)
    {
        $customer = auth('customer')->user();
        $newTemplate = $this->templateService->duplicate($template, $customer);

        $isUnlayer = is_array($newTemplate->grapesjs_data) && (($newTemplate->grapesjs_data['builder'] ?? null) === 'unlayer');

        return redirect()
            ->route($isUnlayer ? 'customer.templates.unlayer.edit' : 'customer.templates.edit', $newTemplate)
            ->with('success', 'Template duplicated successfully.');
    }

    /**
     * Preview template.
     */
    public function preview(Template $template)
    {
        $customer = auth('customer')->user();
        if (!$this->templateService->canAccessTemplate($template, $customer)) {
            abort(403, 'You do not have permission to preview this template.');
        }

        return view('customer.templates.preview', compact('template'));
    }

    /**
     * Get template content as JSON (for AJAX requests).
     */
    public function getContent(Template $template)
    {
        // Check if user has access to this template
        $customer = auth('customer')->user();
        if (!$this->templateService->canAccessTemplate($template, $customer)) {
            abort(403, 'You do not have permission to access this template.');
        }

        $rawBuilderData = $template->grapesjs_data;
        $builder = is_array($rawBuilderData) ? ($rawBuilderData['builder'] ?? 'grapesjs') : null;
        $builderData = $rawBuilderData;
        if (is_array($rawBuilderData) && ($rawBuilderData['builder'] ?? null) === 'unlayer' && is_array($rawBuilderData['unlayer'] ?? null)) {
            $builderData = $rawBuilderData['unlayer'];
            $builder = 'unlayer';
        }

        return response()->json([
            'html_content' => $template->html_content,
            'plain_text_content' => $template->plain_text_content,
            'builder' => $builder,
            'builder_data' => $builderData,
        ]);
    }

    public function importGallery(Request $request)
    {
        $customer = auth('customer')->user();
        if (!$customer) {
            abort(404);
        }

        $tab = (string) $request->query('tab', 'templates');
        if ($tab !== 'templates') {
            $tab = 'templates';
        }

        $builder = (string) $request->query('builder', 'unlayer');
        if (!in_array($builder, ['grapesjs', 'unlayer'], true)) {
            $builder = 'unlayer';
        }

        $systemTemplates = collect();
        $aiTemplates = collect();
        $publicTemplates = collect();
        $publicCategories = collect();

        try {
            $systemTemplates = Template::query()
                ->where('is_system', true)
                ->when($builder === 'unlayer', function ($q) {
                    $q->where('grapesjs_data->builder', 'unlayer');
                }, function ($q) {
                    $q->where(function ($sub) {
                        $sub->whereNull('grapesjs_data->builder');
                        $sub->orWhere('grapesjs_data->builder', 'grapesjs');
                    });
                })
                ->orderBy('name')
                ->get()
                ->map(function (Template $template) {
                    $rawBuilderData = $template->grapesjs_data;
                    $builder = is_array($rawBuilderData) ? ($rawBuilderData['builder'] ?? 'grapesjs') : 'grapesjs';
                    $settings = is_array($template->settings) ? $template->settings : [];
                    $category = $settings['category'] ?? 'other';

                    return [
                        'id' => $template->id,
                        'name' => $template->name,
                        'description' => $template->description,
                        'thumbnail' => $template->thumbnail,
                        'category' => $category,
                        'builder' => $builder,
                        'source' => 'db',
                        'is_ai' => false,
                    ];
                })
                ->values();

            $aiTemplates = Template::query()
                ->where('is_system', false)
                ->where('customer_id', $customer->id)
                ->where('settings->origin', 'ai')
                ->when($builder === 'unlayer', function ($q) {
                    $q->where('grapesjs_data->builder', 'unlayer');
                }, function ($q) {
                    $q->where(function ($sub) {
                        $sub->whereNull('grapesjs_data->builder');
                        $sub->orWhere('grapesjs_data->builder', 'grapesjs');
                    });
                })
                ->orderBy('name')
                ->get()
                ->map(function (Template $template) {
                    $rawBuilderData = $template->grapesjs_data;
                    $builder = is_array($rawBuilderData) ? ($rawBuilderData['builder'] ?? 'grapesjs') : 'grapesjs';

                    $settings = is_array($template->settings) ? $template->settings : [];
                    $origin = $settings['origin'] ?? null;
                    $isAi = $origin === 'ai' || ($origin === null && $template->name === 'AI Generated');
                    $category = $settings['category'] ?? 'ai';

                    return [
                        'id' => $template->id,
                        'name' => $template->name,
                        'description' => $template->description,
                        'thumbnail' => $template->thumbnail,
                        'category' => $category,
                        'builder' => $builder,
                        'source' => 'ai',
                        'is_ai' => $isAi,
                    ];
                })
                ->values();

            $groupIds = $customer->customerGroups()
                ->pluck('customer_groups.id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

            $publicCategories = PublicTemplateCategory::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            $publicTemplates = PublicTemplate::query()
                ->where('is_active', true)
                ->where('builder', $builder)
                ->where(function ($q) use ($groupIds) {
                    $q->whereDoesntHave('customerGroups');

                    if (!empty($groupIds)) {
                        $q->orWhereHas('customerGroups', function ($sub) use ($groupIds) {
                            $sub->whereIn('customer_groups.id', $groupIds);
                        });
                    }
                })
                ->with('category')
                ->orderBy('name')
                ->get()
                ->map(function (PublicTemplate $template) {
                    $category = $template->category?->slug ?: 'other';

                    return [
                        'id' => 'public-' . $template->id,
                        'name' => $template->name,
                        'description' => $template->description,
                        'thumbnail' => $template->thumbnail,
                        'category' => $category,
                        'builder' => $template->builder,
                        'source' => 'public',
                        'is_ai' => false,
                        'content_url' => route('customer.templates.import.public.content', $template),
                    ];
                })
                ->values();
        } catch (\Throwable $e) {
            Log::warning('Template gallery DB query failed', [
                'error' => $e->getMessage(),
                'builder' => $builder,
            ]);
            $systemTemplates = collect();
            $aiTemplates = collect();
            $publicTemplates = collect();
            $publicCategories = collect();
        }

        $fileItems = $this->fileGalleryItems($builder, $tab);
        $merged = array_values(array_merge($publicTemplates->all(), $systemTemplates->all(), $aiTemplates->all(), $fileItems));

        usort($merged, function ($a, $b) {
            return strcmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
        });

        $categories = [
            ['id' => 'all', 'name' => 'All Templates', 'icon' => 'grid'],
            ['id' => 'marketing', 'name' => 'Marketing & Promotional', 'icon' => 'megaphone'],
            ['id' => 'transactional', 'name' => 'Transactional', 'icon' => 'receipt'],
            ['id' => 'automation', 'name' => 'Automation & Drip', 'icon' => 'zap'],
            ['id' => 'cold', 'name' => 'Cold & Outreach', 'icon' => 'send'],
            ['id' => 'relationship', 'name' => 'Relationship & Engagement', 'icon' => 'heart'],
            ['id' => 'support', 'name' => 'Support & System', 'icon' => 'headphones'],
            ['id' => 'ecommerce', 'name' => 'E-commerce', 'icon' => 'shopping'],
            ['id' => 'ai', 'name' => 'AI Generated', 'icon' => 'sparkles'],
            ['id' => 'other', 'name' => 'Other', 'icon' => 'folder'],
        ];

        if ($publicCategories->isNotEmpty()) {
            $existingIds = collect($categories)->pluck('id')->map(fn ($id) => (string) $id)->all();
            foreach ($publicCategories as $cat) {
                $id = (string) ($cat->slug ?? '');
                if ($id === '' || in_array($id, $existingIds, true)) {
                    continue;
                }
                $categories[] = [
                    'id' => $id,
                    'name' => (string) ($cat->name ?? $id),
                    'icon' => (string) ($cat->icon ?? 'folder'),
                ];
                $existingIds[] = $id;
            }
        }

        return response()->json([
            'tab' => $tab,
            'templates' => $merged,
            'categories' => $categories,
        ]);
    }

    public function importFileContent(Request $request, string $key)
    {
        $customer = auth('customer')->user();
        if (!$customer) {
            abort(404);
        }

        $payload = $this->fileGalleryPayloadByKey('unlayer', $key);
        if ($payload === null) {
            abort(404);
        }

        return response()->json($payload);
    }

    public function importContent(Template $template)
    {
        $customer = auth('customer')->user();
        if (!$customer) {
            abort(404);
        }

        if (!$template->is_system && $template->customer_id !== $customer->id && !$template->is_public) {
            abort(403, 'You do not have permission to access this template.');
        }

        $rawBuilderData = $template->grapesjs_data;
        $builder = is_array($rawBuilderData) ? ($rawBuilderData['builder'] ?? 'grapesjs') : null;
        $builderData = $rawBuilderData;
        if (is_array($rawBuilderData) && ($rawBuilderData['builder'] ?? null) === 'unlayer' && is_array($rawBuilderData['unlayer'] ?? null)) {
            $builderData = $rawBuilderData['unlayer'];
            $builder = 'unlayer';
        }

        return response()->json([
            'id' => $template->id,
            'name' => $template->name,
            'description' => $template->description,
            'html_content' => $template->html_content,
            'plain_text_content' => $template->plain_text_content,
            'builder' => $builder,
            'builder_data' => $builderData,
        ]);
    }

    public function importPublicContent(PublicTemplate $publicTemplate)
    {
        $customer = auth('customer')->user();
        if (!$customer) {
            abort(404);
        }

        if (!$publicTemplate->is_active) {
            abort(404);
        }

        $groupIds = $customer->customerGroups()
            ->pluck('customer_groups.id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $hasRestrictions = $publicTemplate->customerGroups()->exists();
        if ($hasRestrictions) {
            $allowed = !empty($groupIds) && $publicTemplate->customerGroups()->whereIn('customer_groups.id', $groupIds)->exists();
            if (!$allowed) {
                abort(403, 'You do not have permission to access this template.');
            }
        }

        return response()->json([
            'id' => $publicTemplate->id,
            'name' => $publicTemplate->name,
            'description' => $publicTemplate->description,
            'html_content' => $publicTemplate->html_content,
            'plain_text_content' => $publicTemplate->plain_text_content,
            'builder' => $publicTemplate->builder,
            'builder_data' => $publicTemplate->builder_data,
        ]);
    }

    public function aiGenerate(Request $request)
    {
        $validated = $request->validate([
            'provider' => ['required', 'string', 'in:chatgpt,gemini,claude'],
            'model' => ['nullable', 'string', 'max:100'],
            'prompt' => ['required', 'string', 'max:5000'],
            'builder' => ['nullable', 'string', 'in:grapesjs,unlayer'],
        ]);

        $customer = auth('customer')->user();
        if (!$customer) {
            abort(404);
        }

        $provider = (string) ($validated['provider'] ?? '');
        $model = is_string($validated['model'] ?? null) && trim((string) $validated['model']) !== ''
            ? trim((string) $validated['model'])
            : null;
        $prompt = (string) ($validated['prompt'] ?? '');
        $builder = is_string($validated['builder'] ?? null) && trim((string) $validated['builder']) !== ''
            ? (string) $validated['builder']
            : 'grapesjs';

        $log = AiGeneration::create([
            'customer_id' => $customer->id,
            'admin_user_id' => null,
            'tool' => 'template_generator',
            'provider' => strtolower(trim($provider)),
            'model' => $model,
            'used_admin_keys' => false,
            'prompt' => (string) $prompt,
            'input' => [
                'provider' => $provider,
                'model' => $model,
                'builder' => $builder,
            ],
            'success' => false,
            'output' => null,
            'tokens_used' => null,
            'error_message' => null,
        ]);

        $result = $this->aiTemplateService->generate($customer, $provider, $prompt, $builder, $model);

        $data = is_array($result['data'] ?? null) ? $result['data'] : [];
        $log->used_admin_keys = (bool) ($data['used_admin_keys'] ?? false);
        $log->tokens_used = is_numeric($data['tokens_used'] ?? null) ? (int) $data['tokens_used'] : null;

        $rawHtml = is_string($data['html_content'] ?? null) ? (string) $data['html_content'] : null;

        if (!($result['success'] ?? false)) {
            $status = is_numeric($result['status'] ?? null) ? (int) $result['status'] : 500;
            $message = is_string($result['message'] ?? null) ? $result['message'] : 'Failed to generate template.';

            $log->success = false;
            $log->output = null;
            $log->error_message = $message;
            $log->save();

            return response()->json([
                'message' => $message,
            ], $status);
        }

        $log->success = true;
        $log->error_message = null;
        $log->output = $rawHtml;
        $log->save();

        if (($data['builder'] ?? null) === 'unlayer' && is_array($data['builder_data'] ?? null)) {
            $data['html_content'] = $this->unlayerPreviewHtmlFromDesign($data['builder_data']);
        }

        return response()->json($data, 200);
    }
}

