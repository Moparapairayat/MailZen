<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BuiltInTemplateSetting;
use App\Models\CustomerGroup;
use App\Models\PublicTemplate;
use App\Models\PublicTemplateCategory;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class PublicTemplateController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('q', ''));

        $templates = PublicTemplate::query()
            ->with(['category'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%');
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $systemTemplates = Template::query()
            ->where('is_system', true)
            ->when($search !== '', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%');
            })
            ->orderBy('name')
            ->get();

        $fileTemplates = $this->fileGalleryItems('unlayer', 'templates', $search);

        return view('admin.public-templates.index', compact('templates', 'systemTemplates', 'fileTemplates', 'search'));
    }

    private function fileGalleryRoot(string $builder): string
    {
        $builder = trim($builder);
        if ($builder === '') {
            $builder = 'unlayer';
        }

        return resource_path('template-gallery/' . $builder);
    }

    private function fileGalleryItems(string $builder, string $tab, string $search = ''): array
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

        $search = trim($search);

        $items = [];
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

            if ($search !== '') {
                $haystack = strtolower($name . ' ' . ($description ?? '') . ' ' . $category);
                if (!str_contains($haystack, strtolower($search))) {
                    continue;
                }
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

            $items[] = [
                'id' => $key,
                'setting_id' => $setting->id,
                'name' => $name,
                'description' => $description,
                'thumbnail' => $thumbnail,
                'category' => $category,
                'builder' => 'unlayer',
                'source' => 'file',
                'content_url' => route('admin.templates.import.file.content', ['key' => $key]),
                'is_active' => (bool) $setting->is_active,
                'edit_url' => route('admin.built-in-templates.edit', $setting),
            ];
        }

        usort($items, function ($a, $b) {
            return strcmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
        });

        return $items;
    }

    public function create()
    {
        return view('admin.public-templates.create', [
            'template' => new PublicTemplate(),
            'categories' => PublicTemplateCategory::query()->orderBy('sort_order')->orderBy('name')->get(),
            'customerGroups' => CustomerGroup::query()->orderBy('name')->get(),
            'unlayerProjectId' => config('services.unlayer.project_id'),
            'unlayerDesign' => null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $adminUser = $request->user('admin');

        $builderData = $this->normalizeBuilderData($request->input('grapesjs_data'));

        $template = PublicTemplate::create([
            'category_id' => $data['category_id'] ?? null,
            'created_by_admin_user_id' => $adminUser?->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'] ?? 'email',
            'builder' => 'unlayer',
            'html_content' => $data['html_content'] ?? null,
            'plain_text_content' => $data['plain_text_content'] ?? null,
            'builder_data' => $builderData,
            'settings' => $data['settings'] ?? [],
            'thumbnail' => $data['thumbnail'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        $this->syncCustomerGroups($template, $data['customer_group_ids'] ?? null);

        return redirect()
            ->route('admin.public-templates.edit', $template)
            ->with('success', __('Template created.'));
    }

    public function edit(PublicTemplate $public_template)
    {
        return view('admin.public-templates.edit', [
            'template' => $public_template->loadMissing('customerGroups'),
            'categories' => PublicTemplateCategory::query()->orderBy('sort_order')->orderBy('name')->get(),
            'customerGroups' => CustomerGroup::query()->orderBy('name')->get(),
            'unlayerProjectId' => config('services.unlayer.project_id'),
            'unlayerDesign' => $public_template->builder_data,
        ]);
    }

    public function update(Request $request, PublicTemplate $public_template)
    {
        $data = $this->validateData($request);

        $builderData = $this->normalizeBuilderData($request->input('grapesjs_data'));

        $public_template->update([
            'category_id' => $data['category_id'] ?? null,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'] ?? 'email',
            'builder' => 'unlayer',
            'html_content' => $data['html_content'] ?? null,
            'plain_text_content' => $data['plain_text_content'] ?? null,
            'builder_data' => $builderData,
            'settings' => $data['settings'] ?? (is_array($public_template->settings) ? $public_template->settings : []),
            'thumbnail' => $data['thumbnail'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        $this->syncCustomerGroups($public_template, $data['customer_group_ids'] ?? null);

        return redirect()
            ->route('admin.public-templates.edit', $public_template)
            ->with('success', __('Template updated.'));
    }

    public function destroy(PublicTemplate $public_template)
    {
        $public_template->delete();

        return redirect()
            ->route('admin.public-templates.index')
            ->with('success', __('Template deleted.'));
    }

    private function validateData(Request $request): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['nullable', 'in:email,campaign,transactional,autoresponder'],
            'category_id' => ['nullable', 'integer', 'exists:public_template_categories,id'],
            'html_content' => ['nullable', 'string'],
            'plain_text_content' => ['nullable', 'string'],
            'grapesjs_data' => ['nullable'],
            'settings' => ['nullable', 'array'],
            'thumbnail' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'customer_group_ids' => ['nullable', 'array'],
            'customer_group_ids.*' => ['integer', 'exists:customer_groups,id'],
        ];

        $data = $request->validate($rules);

        if (empty($data['plain_text_content']) && !empty($data['html_content'])) {
            $data['plain_text_content'] = trim(preg_replace('/\s+/', ' ', strip_tags((string) $data['html_content'])));
        }

        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        return $data;
    }

    private function normalizeBuilderData(mixed $value): ?array
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

    private function syncCustomerGroups(PublicTemplate $template, mixed $groupIds): void
    {
        if ($groupIds === null) {
            return;
        }

        $ids = [];
        if (is_array($groupIds)) {
            $ids = array_values(array_unique(array_map(fn ($v) => (int) $v, $groupIds)));
        }

        if (empty($ids)) {
            $template->customerGroups()->detach();
            return;
        }

        $template->customerGroups()->sync($ids);
    }
}
