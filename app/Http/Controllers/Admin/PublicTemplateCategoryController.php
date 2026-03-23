<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PublicTemplateCategory;
use Illuminate\Http\Request;

class PublicTemplateCategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('q', ''));

        $categories = PublicTemplateCategory::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%');
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.public-template-categories.index', compact('categories', 'search'));
    }

    public function create()
    {
        return view('admin.public-template-categories.create', [
            'category' => new PublicTemplateCategory(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $category = PublicTemplateCategory::create($data);

        return redirect()
            ->route('admin.public-template-categories.edit', $category)
            ->with('success', __('Category created.'));
    }

    public function edit(PublicTemplateCategory $public_template_category)
    {
        return view('admin.public-template-categories.edit', [
            'category' => $public_template_category,
        ]);
    }

    public function update(Request $request, PublicTemplateCategory $public_template_category)
    {
        $data = $this->validateData($request);
        $public_template_category->update($data);

        return redirect()
            ->route('admin.public-template-categories.edit', $public_template_category)
            ->with('success', __('Category updated.'));
    }

    public function destroy(PublicTemplateCategory $public_template_category)
    {
        $public_template_category->delete();

        return redirect()
            ->route('admin.public-template-categories.index')
            ->with('success', __('Category deleted.'));
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $data['sort_order'] = is_numeric($data['sort_order'] ?? null) ? (int) $data['sort_order'] : 0;
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        return $data;
    }
}
