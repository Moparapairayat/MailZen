<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TranslationLineStoreRequest;
use App\Http\Requests\Admin\TranslationLineUpdateRequest;
use App\Models\TranslationLine;
use App\Models\TranslationLocale;
use Illuminate\Http\Request;

class TranslationLineController extends Controller
{
    public function index(Request $request, TranslationLocale $translation_locale)
    {
        $search = trim((string) $request->query('q', ''));

        return redirect()->route('admin.translations.bulk.edit', array_filter([
            $translation_locale,
            'q' => $search !== '' ? $search : null,
        ], fn ($v) => !is_null($v)));
    }

    public function create(TranslationLocale $translation_locale)
    {
        return view('admin.translations.lines.create', [
            'translation_locale' => $translation_locale,
            'line' => new TranslationLine(['group' => '*']),
        ]);
    }

    public function store(TranslationLineStoreRequest $request, TranslationLocale $translation_locale)
    {
        $data = $request->validated();

        $group = trim((string) ($data['group'] ?? '*'));
        $group = $group === '' ? '*' : $group;

        TranslationLine::create([
            'translation_locale_id' => $translation_locale->id,
            'group' => $group,
            'key' => $data['key'],
            'text' => $data['text'] ?? null,
        ]);

        $translation_locale->touch();

        return redirect()
            ->route('admin.translations.bulk.edit', $translation_locale)
            ->with('success', 'Translation added.');
    }

    public function edit(TranslationLocale $translation_locale, TranslationLine $line)
    {
        if ((int) $line->translation_locale_id !== (int) $translation_locale->id) {
            abort(404);
        }

        return view('admin.translations.lines.edit', compact('translation_locale', 'line'));
    }

    public function update(TranslationLineUpdateRequest $request, TranslationLocale $translation_locale, TranslationLine $line)
    {
        if ((int) $line->translation_locale_id !== (int) $translation_locale->id) {
            abort(404);
        }

        $data = $request->validated();

        $group = trim((string) ($data['group'] ?? '*'));
        $group = $group === '' ? '*' : $group;

        $line->update([
            'group' => $group,
            'key' => $data['key'],
            'text' => $data['text'] ?? null,
        ]);

        $translation_locale->touch();

        return redirect()
            ->route('admin.translations.bulk.edit', $translation_locale)
            ->with('success', 'Translation updated.');
    }

    public function destroy(TranslationLocale $translation_locale, TranslationLine $line)
    {
        if ((int) $line->translation_locale_id !== (int) $translation_locale->id) {
            abort(404);
        }

        $line->delete();

        $translation_locale->touch();

        return redirect()
            ->route('admin.translations.bulk.edit', $translation_locale)
            ->with('success', 'Translation deleted.');
    }
}
