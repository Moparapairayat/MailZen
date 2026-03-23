<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Translation\LocaleJsonService;
use App\Translation\TranslationKeyScanner;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class TranslationJsonBulkController extends Controller
{
    public function edit(Request $request, string $locale, LocaleJsonService $locales)
    {
        $locale = trim($locale);
        if ($locale === '' || !$locales->validateLocaleCode($locale) || !$locales->localeExists($locale)) {
            abort(404);
        }

        $search = trim((string) $request->query('q', ''));
        $section = trim((string) $request->query('section', ''));
        $refresh = (bool) $request->boolean('refresh');

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

        $sourceMap = $locales->readLocaleMap('en');
        $items = [];
        foreach ($slice as $rawKey) {
            $items[] = [
                'rawKey' => $rawKey,
                'source' => (string) ($sourceMap[$rawKey] ?? $rawKey),
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

        $targetMap = $locales->readLocaleMap($locale);

        return view('admin.translations.bulk.edit', [
            'translation_locale' => (object) ['code' => $locale, 'name' => $locale],
            'sourceLocale' => (object) ['code' => 'en', 'name' => 'English'],
            'rows' => $paginator,
            'targetLines' => collect($targetMap),
            'search' => $search,
            'sections' => $sections,
            'section' => $section,
        ]);
    }

    public function update(Request $request, string $locale, LocaleJsonService $locales)
    {
        $locale = trim($locale);
        if ($locale === '' || !$locales->validateLocaleCode($locale) || !$locales->localeExists($locale)) {
            abort(404);
        }

        $validated = $request->validate([
            'translations' => ['nullable', 'array'],
            'translations.*' => ['nullable', 'string'],
        ]);

        $translations = (array) ($validated['translations'] ?? []);

        $existing = $locales->readLocaleMap($locale);

        foreach ($translations as $key => $value) {
            if (!is_string($key) || trim($key) === '') {
                continue;
            }

            $key = trim($key);
            $value = is_string($value) ? trim($value) : '';

            if ($value === '') {
                unset($existing[$key]);
                continue;
            }

            $existing[$key] = $value;
        }

        $ok = $locales->writeLocaleMap($locale, $existing);
        if (!$ok) {
            return back()->with('error', __('Failed to save file.'));
        }

        $redirectQuery = array_filter([
            'section' => $request->input('section'),
            'q' => $request->input('q'),
        ], fn ($v) => is_string($v) && trim($v) !== '');

        return redirect()
            ->route('admin.translations.bulk.edit', ['locale' => $locale] + $redirectQuery)
            ->with('success', __('Translations saved.'));
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
