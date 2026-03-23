<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Translation\LocaleJsonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TranslationJsonLocaleController extends Controller
{
    public function index(Request $request, LocaleJsonService $locales)
    {
        $search = trim((string) $request->query('q', ''));

        $items = $locales->listLocales();

        if ($search !== '') {
            $items = array_values(array_filter($items, function ($l) use ($search) {
                $code = is_object($l) && is_string($l->code ?? null) ? (string) $l->code : '';
                $name = is_object($l) && is_string($l->name ?? null) ? (string) $l->name : '';

                $hay = mb_strtolower($code . ' ' . $name);
                $needle = mb_strtolower($search);

                return str_contains($hay, $needle);
            }));
        }

        $localesCollection = collect($items);

        $active = $locales->activeLocales();
        $activeSet = $active === null ? null : array_fill_keys($active, true);

        return view('admin.translations.locales.index', [
            'locales' => $localesCollection,
            'search' => $search,
            'activeLocalesSet' => $activeSet,
        ]);
    }

    public function downloadMain(LocaleJsonService $locales)
    {
        $path = $locales->localePath('en');

        if (!is_file($path)) {
            abort(404);
        }

        return response()->download($path, 'en.json', [
            'Content-Type' => 'application/json; charset=utf-8',
        ]);
    }

    public function downloadLocale(string $locale, LocaleJsonService $locales)
    {
        $locale = trim($locale);

        if ($locale === '' || !$locales->validateLocaleCode($locale) || !$locales->localeExists($locale)) {
            abort(404);
        }

        $path = $locales->localePath($locale);

        if (!is_file($path)) {
            abort(404);
        }

        return response()->download($path, $locale . '.json', [
            'Content-Type' => 'application/json; charset=utf-8',
        ]);
    }

    public function setActive(Request $request, string $locale, LocaleJsonService $locales)
    {
        $locale = trim($locale);

        if ($locale === '' || $locale === 'en' || !$locales->validateLocaleCode($locale) || !$locales->localeExists($locale)) {
            return back()->with('error', __('Invalid language.'));
        }

        $validated = $request->validate([
            'active' => ['required', 'boolean'],
        ]);

        $ok = $locales->setLocaleActive($locale, (bool) $validated['active']);
        if (!$ok) {
            return back()->with('error', __('Failed to save.'));
        }

        return back()->with('success', __('Saved.'));
    }

    public function edit(string $locale, LocaleJsonService $locales)
    {
        $locale = trim($locale);

        if ($locale === '' || !$locales->validateLocaleCode($locale) || !$locales->localeExists($locale)) {
            abort(404);
        }

        $meta = $locales->localeMeta($locale);

        $name = is_string($meta['name'] ?? null) && trim((string) $meta['name']) !== ''
            ? trim((string) $meta['name'])
            : $locale;

        $flag = is_string($meta['flag'] ?? null) && trim((string) $meta['flag']) !== ''
            ? trim((string) $meta['flag'])
            : null;

        return view('admin.translations.locales.edit', [
            'locale' => (object) [
                'code' => $locale,
                'name' => $name,
                'flag' => $flag,
            ],
        ]);
    }

    public function update(Request $request, string $locale, LocaleJsonService $locales)
    {
        $locale = trim($locale);

        if ($locale === '' || !$locales->validateLocaleCode($locale) || !$locales->localeExists($locale)) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'flag' => ['nullable', 'file', 'mimes:png', 'max:5120'],
        ]);

        $meta = $locales->localeMeta($locale);
        $existingFlag = is_string($meta['flag'] ?? null) ? trim((string) $meta['flag']) : '';
        $flagPath = $existingFlag !== '' ? $existingFlag : '';

        if ($request->hasFile('flag')) {
            if ($flagPath !== '') {
                try {
                    Storage::disk('public')->delete($flagPath);
                } catch (\Throwable $e) {
                    //
                }
            }

            $upload = $request->file('flag');
            $flagPath = $upload ? (string) $upload->store('translations/flags', 'public') : '';
        }

        $name = isset($validated['name']) && is_string($validated['name']) ? trim((string) $validated['name']) : '';

        $ok = $locales->setLocaleMeta($locale, [
            'name' => $name,
            'flag' => $flagPath,
        ]);

        if (!$ok) {
            return back()->with('error', __('Failed to save.'));
        }

        return redirect()
            ->route('admin.translations.locales.edit', ['locale' => $locale])
            ->with('success', __('Saved.'));
    }

    public function upload(Request $request, LocaleJsonService $locales)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimetypes:application/json,text/plain,text/json', 'max:20480'],
        ]);

        $file = $validated['file'];

        $originalName = method_exists($file, 'getClientOriginalName') ? (string) $file->getClientOriginalName() : '';
        $localeCode = $locales->parseUploadedLocaleFilename($originalName);

        if (!$localeCode) {
            return back()->with('error', __('Invalid language file name. The file must be named like locale_code.json (example: en_EN.json).'));
        }

        $raw = @file_get_contents($file->getRealPath());
        if (!is_string($raw) || trim($raw) === '') {
            return back()->with('error', __('Invalid JSON file.'));
        }

        $map = $locales->normalizeUploadJson($raw);
        if (!is_array($map)) {
            return back()->with('error', __('Invalid JSON file.'));
        }

        $ok = $locales->writeLocaleMap($localeCode, $map);
        if (!$ok) {
            return back()->with('error', __('Failed to save file.'));
        }

        return redirect()
            ->route('admin.translations.bulk.edit', ['locale' => $localeCode])
            ->with('success', __('Language file uploaded.'));
    }
}
