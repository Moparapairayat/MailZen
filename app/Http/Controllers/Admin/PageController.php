<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    private const RESERVED_PAGE_SLUGS = [
        'admin',
        'login',
        'logout',
        'register',
        'openapi',
        'openapi.json',
        'blog',
        'subscribe',
        'unsubscribe',
        'track',
        't',
        'webhooks',
        'storage',
        'pages',
    ];

    public function index(Request $request)
    {
        $this->ensureDefaultHomepages();

        $search = trim((string) $request->get('q', ''));

        $pages = Page::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%');
            })
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.pages.index', compact('pages', 'search'));
    }

    public function create(Request $request)
    {
        $type = trim((string) $request->query('type', 'page'));
        if (!in_array($type, ['page', 'homepage'], true)) {
            $type = 'page';
        }

        $page = new Page([
            'type' => $type,
            'status' => 'draft',
        ]);

        return view('admin.pages.edit', [
            'page' => $page,
            'unlayerProjectId' => config('services.unlayer.project_id'),
            'unlayerDesign' => null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $page = Page::create($data);

        return redirect()->route('admin.pages.edit', $page)->with('success', __('Page created.'));
    }

    public function edit(Page $page)
    {
        return view('admin.pages.edit', [
            'page' => $page,
            'unlayerProjectId' => config('services.unlayer.project_id'),
            'unlayerDesign' => is_array($page->builder_data) ? $page->builder_data : null,
        ]);
    }

    public function update(Request $request, Page $page)
    {
        $data = $this->validateData($request, $page->id);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $page->update($data);

        return redirect()->route('admin.pages.edit', $page)->with('success', __('Page updated.'));
    }

    public function destroy(Page $page)
    {
        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', __('Page deleted.'));
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'image', 'max:5120'],
        ]);

        $path = $request->file('file')->store('pages/uploads', 'public');

        return response()->json([
            'filelink' => asset('storage/' . ltrim($path, '/')),
        ]);
    }

    private function validateData(Request $request, ?int $pageId = null): array
    {
        $builderData = $request->input('builder_data');
        $decodedBuilder = null;
        if (is_string($builderData) && trim($builderData) !== '') {
            $decodedBuilder = json_decode($builderData, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $decodedBuilder = null;
            }
        } elseif (is_array($builderData)) {
            $decodedBuilder = $builderData;
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9\-]+$/', Rule::unique('pages', 'slug')->ignore($pageId)],
            'type' => ['required', 'string', Rule::in(['page', 'homepage'])],
            'variant_key' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'string', Rule::in(['draft', 'publish'])],
            'html_content' => ['nullable', 'string'],
        ]);

        if (($data['type'] ?? 'page') === 'page' && !empty($data['slug'])) {
            $slug = trim((string) $data['slug']);
            $data['slug'] = $slug;

            if (in_array($slug, self::RESERVED_PAGE_SLUGS, true)) {
                throw ValidationException::withMessages([
                    'slug' => __('This slug is reserved. Please choose another.'),
                ]);
            }
        }

        if ($data['type'] === 'homepage') {
            $data['variant_key'] = is_string($data['variant_key'] ?? null) ? trim((string) $data['variant_key']) : null;
            if (($data['variant_key'] ?? '') === '') {
                $data['variant_key'] = null;
            }
        } else {
            $data['variant_key'] = null;
        }

        $data['builder_data'] = $decodedBuilder;

        return $data;
    }

    private function ensureDefaultHomepages(): void
    {
        $defaults = [
            ['variant_key' => '1', 'title' => 'Homepage Variant 1', 'slug' => 'home-variant-1'],
            ['variant_key' => 'dark', 'title' => 'Homepage Variant Dark', 'slug' => 'home-variant-dark'],
            ['variant_key' => '2', 'title' => 'Homepage Variant 2', 'slug' => 'home-variant-2'],
            ['variant_key' => '3', 'title' => 'Homepage Variant 3', 'slug' => 'home-variant-3'],
        ];

        foreach ($defaults as $row) {
            Page::firstOrCreate(
                ['type' => 'homepage', 'variant_key' => $row['variant_key']],
                [
                    'title' => $row['title'],
                    'slug' => $row['slug'],
                    'status' => 'draft',
                ]
            );
        }
    }
}
