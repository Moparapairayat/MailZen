<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TranslationLine;
use App\Models\TranslationLocale;
use App\Translation\TranslationKeyScanner;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TranslationBulkController extends Controller
{
    public function edit(Request $request, TranslationLocale $translation_locale)
    {
        $search = trim((string) $request->query('q', ''));
        $section = trim((string) $request->query('section', ''));
        $refresh = (bool) $request->boolean('refresh');

        $activeLocales = TranslationLocale::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        $sourceLocale = $activeLocales->first(function ($locale) {
            $code = is_string($locale->code ?? null) ? trim((string) $locale->code) : '';
            return $code === 'en';
        });

        if (!$sourceLocale) {
            $sourceLocale = $activeLocales->first(function ($locale) {
                $code = is_string($locale->code ?? null) ? trim((string) $locale->code) : '';
                return str_starts_with($code, 'en_') || str_starts_with($code, 'en-');
            });
        }

        if (!$sourceLocale) {
            $sourceLocale = $activeLocales->first(function ($locale) {
                $name = is_string($locale->name ?? null) ? trim((string) $locale->name) : '';
                return $name !== '' && stripos($name, 'english') !== false;
            });
        }

        if (!$sourceLocale) {
            $sourceLocale = $activeLocales->first();
        }

        $scan = app(TranslationKeyScanner::class)->scanDetailed($refresh);
        $keys = (array) ($scan['keys'] ?? []);
        $filesByKey = (array) ($scan['filesByKey'] ?? []);

        if ($search !== '') {
            $keys = array_values(array_filter($keys, function ($k) use ($search) {
                return str_contains(mb_strtolower((string) $k), mb_strtolower($search));
            }));
        }

        $sectionsByKey = [];
        foreach ($keys as $rawKey) {
            $files = $filesByKey[$rawKey] ?? [];
            $files = is_array($files) ? $files : [];
            $sectionsByKey[$rawKey] = $this->determineSection($files);
        }

        $sectionOrder = ['Sidebar', 'Login page', 'Register page', 'Dashboard page', 'Users page', 'Invoices', 'Coupons', 'Plans', 'Payment Methods', 'Vat/Tax', 'Customers', 'Groups', 'Campaigns', 'Email Lists', 'Email Validation', 'Delivery Servers', 'Sending Domains', 'Tracking Domains', 'Bounce Servers', 'Bounced Emails', 'Other'];
        $sections = array_values(array_unique(array_values($sectionsByKey)));

        $requiredSections = ['Login page', 'Register page', 'Campaigns', 'Email Lists', 'Email Validation', 'Customers', 'Groups'];
        $sections = array_values(array_unique(array_merge($requiredSections, $sections)));
        usort($sections, function ($a, $b) use ($sectionOrder) {
            $ai = array_search($a, $sectionOrder, true);
            $bi = array_search($b, $sectionOrder, true);
            $ai = $ai === false ? PHP_INT_MAX : $ai;
            $bi = $bi === false ? PHP_INT_MAX : $bi;

            if ($ai === $bi) {
                return strcasecmp((string) $a, (string) $b);
            }

            return $ai <=> $bi;
        });

        if ($section === '') {
            if (in_array('Sidebar', $sections, true)) {
                $section = 'Sidebar';
            } else {
                $section = $sections[0] ?? 'Other';
            }
        }

        $keys = array_values(array_filter($keys, fn ($k) => ($sectionsByKey[$k] ?? 'Other') === $section));

        $perPage = 25;
        $page = max(1, (int) $request->query('page', 1));
        $slice = array_slice($keys, ($page - 1) * $perPage, $perPage);

        $sourceCode = $sourceLocale?->code;
        $items = [];

        foreach ($slice as $rawKey) {
            $parsed = $this->parseKey($rawKey);

            $items[] = [
                'rawKey' => $rawKey,
                'group' => $parsed['group'],
                'key' => $parsed['key'],
                'source' => $sourceCode ? trans($rawKey, [], $sourceCode) : $rawKey,
                'section' => $section,
            ];
        }

        $paginator = new LengthAwarePaginator(
            $items,
            count($keys),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $targetLines = TranslationLine::query()
            ->where('translation_locale_id', $translation_locale->id)
            ->where(function ($q) use ($items) {
                foreach ($items as $item) {
                    $q->orWhere(function ($sub) use ($item) {
                        $sub->where('group', $item['group'])
                            ->where('key', $item['key']);
                    });
                }
            })
            ->get(['group', 'key', 'text'])
            ->mapWithKeys(fn ($l) => [($l->group . '|' . $l->key) => $l->text]);

        return view('admin.translations.bulk.edit', [
            'translation_locale' => $translation_locale,
            'sourceLocale' => $sourceLocale,
            'rows' => $paginator,
            'targetLines' => $targetLines,
            'search' => $search,
            'sections' => $sections,
            'section' => $section,
        ]);
    }

    public function update(Request $request, TranslationLocale $translation_locale)
    {
        $validated = $request->validate([
            'translations' => ['nullable', 'array'],
            'translations.*' => ['nullable', 'string'],
        ]);

        $translations = (array) ($validated['translations'] ?? []);

        DB::transaction(function () use ($translations, $translation_locale) {
            foreach ($translations as $key => $value) {
                if (!is_string($key) || trim($key) === '') {
                    continue;
                }

                $parsed = $this->parseKey($key);
                $group = $parsed['group'];
                $innerKey = $parsed['key'];

                $value = is_string($value) ? trim($value) : '';

                if ($value === '') {
                    TranslationLine::query()
                        ->where('translation_locale_id', $translation_locale->id)
                        ->where('group', $group)
                        ->where('key', $innerKey)
                        ->delete();

                    continue;
                }

                TranslationLine::query()->updateOrCreate(
                    [
                        'translation_locale_id' => $translation_locale->id,
                        'group' => $group,
                        'key' => $innerKey,
                    ],
                    [
                        'text' => $value,
                    ]
                );
            }

            $translation_locale->touch();
        });

        $redirectQuery = array_filter([
            'section' => $request->input('section'),
            'q' => $request->input('q'),
        ], fn ($v) => is_string($v) && trim($v) !== '');

        return redirect()
            ->route('admin.translations.bulk.edit', [$translation_locale] + $redirectQuery)
            ->with('success', __('Translations saved.'));
    }

    private function parseKey(string $rawKey): array
    {
        $rawKey = trim($rawKey);

        if ($rawKey === '' || str_contains($rawKey, '::')) {
            return ['group' => '*', 'key' => $rawKey];
        }

        $parts = explode('.', $rawKey, 2);

        if (count($parts) === 2 && $parts[0] !== '' && $parts[1] !== '') {
            return ['group' => $parts[0], 'key' => $parts[1]];
        }

        return ['group' => '*', 'key' => $rawKey];
    }

    private function determineSection(array $files): string
    {
        $files = array_values(array_filter($files, fn ($p) => is_string($p) && trim($p) !== ''));

        foreach ($files as $p) {
            if (
                str_contains($p, 'resources/views/auth/login.blade.php') ||
                str_contains($p, 'resources/views/customer/auth/login.blade.php') ||
                str_contains($p, 'resources/views/admin/auth/login.blade.php')
            ) {
                return 'Login page';
            }
        }

        foreach ($files as $p) {
            if (
                str_contains($p, 'resources/views/auth/register.blade.php') ||
                str_contains($p, 'resources/views/customer/auth/register.blade.php')
            ) {
                return 'Register page';
            }
        }

        foreach ($files as $p) {
            if (str_contains($p, 'resources/views/components/sidebar.blade.php') || str_contains($p, 'resources/views/layouts/admin.blade.php')) {
                return 'Sidebar';
            }
        }

        foreach ($files as $p) {
            if (str_contains($p, 'resources/views/admin/dashboard')) {
                return 'Dashboard page';
            }
        }

        foreach ($files as $p) {
            if (str_contains($p, 'resources/views/admin/users')) {
                return 'Users page';
            }
        }

        foreach ($files as $p) {
            if (str_contains($p, 'resources/views/admin/invoices')) {
                return 'Invoices';
            }
        }

        foreach ($files as $p) {
            if (str_contains($p, 'resources/views/admin/coupons')) {
                return 'Coupons';
            }
        }

        foreach ($files as $p) {
            if (str_contains($p, 'resources/views/admin/plans')) {
                return 'Plans';
            }
        }

        foreach ($files as $p) {
            if (str_contains($p, 'resources/views/admin/payment-methods')) {
                return 'Payment Methods';
            }
        }

        foreach ($files as $p) {
            if (str_contains($p, 'resources/views/admin/vat-tax')) {
                return 'Vat/Tax';
            }
        }

        foreach ($files as $p) {
            if (str_contains($p, 'resources/views/admin/customers')) {
                return 'Customers';
            }
        }

        foreach ($files as $p) {
            if (str_contains($p, 'resources/views/admin/user-groups') || str_contains($p, 'resources/views/admin/customer-groups')) {
                return 'Groups';
            }
        }

        foreach ($files as $p) {
            if (str_contains($p, 'resources/views/admin/campaigns')) {
                return 'Campaigns';
            }
        }

        foreach ($files as $p) {
            if (str_contains($p, 'resources/views/admin/lists')) {
                return 'Email Lists';
            }
        }

        foreach ($files as $p) {
            if (str_contains($p, 'resources/views/admin/email-validation')) {
                return 'Email Validation';
            }
        }

        foreach ($files as $p) {
            if (str_contains($p, 'resources/views/admin/delivery-servers')) {
                return 'Delivery Servers';
            }
        }

        foreach ($files as $p) {
            if (str_contains($p, 'resources/views/admin/sending-domains')) {
                return 'Sending Domains';
            }
        }

        foreach ($files as $p) {
            if (str_contains($p, 'resources/views/admin/tracking-domains')) {
                return 'Tracking Domains';
            }
        }

        foreach ($files as $p) {
            if (str_contains($p, 'resources/views/admin/bounce-servers')) {
                return 'Bounce Servers';
            }
        }

        foreach ($files as $p) {
            if (str_contains($p, 'resources/views/admin/bounced-emails')) {
                return 'Bounced Emails';
            }
        }

        return 'Other';
    }
}
