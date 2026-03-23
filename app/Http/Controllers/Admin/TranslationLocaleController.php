<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TranslationLocaleStoreRequest;
use App\Http\Requests\Admin\TranslationLocaleUpdateRequest;
use App\Models\TranslationLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TranslationLocaleController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $locales = TranslationLocale::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where('code', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%');
            })
            ->orderBy('code')
            ->paginate(15)
            ->withQueryString();

        return view('admin.translations.locales.index', compact('locales', 'search'));
    }

    public function create()
    {
        return view('admin.translations.locales.create', [
            'locale' => new TranslationLocale(),
        ]);
    }

    public function store(TranslationLocaleStoreRequest $request)
    {
        $data = $request->validated();

        $data['code'] = trim((string) ($data['code'] ?? ''));
        $data['name'] = trim((string) ($data['name'] ?? ''));

        $locale = TranslationLocale::create([
            'code' => $data['code'],
            'name' => $data['name'],
            'is_active' => (bool) ($data['is_active'] ?? true),
            'is_default' => false,
        ]);

        return redirect()
            ->route('admin.translations.locales.edit', $locale)
            ->with('success', 'Locale created.');
    }

    public function edit(TranslationLocale $translation_locale)
    {
        return view('admin.translations.locales.edit', [
            'locale' => $translation_locale,
        ]);
    }

    public function update(TranslationLocaleUpdateRequest $request, TranslationLocale $translation_locale)
    {
        $data = $request->validated();

        $data['code'] = trim((string) ($data['code'] ?? ''));
        $data['name'] = trim((string) ($data['name'] ?? ''));

        $translation_locale->update([
            'code' => $data['code'],
            'name' => $data['name'],
            'is_active' => (bool) ($data['is_active'] ?? false),
            'is_default' => false,
        ]);

        return redirect()
            ->route('admin.translations.locales.edit', $translation_locale)
            ->with('success', 'Locale updated.');
    }

    public function destroy(TranslationLocale $translation_locale)
    {
        $translation_locale->delete();

        return redirect()
            ->route('admin.translations.locales.index')
            ->with('success', 'Locale deleted.');
    }
}
