<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use App\Translation\LocaleJsonService;
use Illuminate\Support\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = null;

        try {
            $admin = auth('admin')->user();
            if ($admin && isset($admin->language) && is_string($admin->language) && trim($admin->language) !== '') {
                $locale = trim($admin->language);
            }
        } catch (\Throwable $e) {
            //
        }

        if ($locale === null) {
            try {
                $customer = auth('customer')->user();
                if ($customer && isset($customer->language) && is_string($customer->language) && trim($customer->language) !== '') {
                    $locale = trim($customer->language);
                }
            } catch (\Throwable $e) {
                //
            }
        }

        if ($locale === null) {
            try {
                $sessionLocale = $request->session()->get('locale');
                if (is_string($sessionLocale) && trim($sessionLocale) !== '') {
                    $locale = trim($sessionLocale);
                }
            } catch (\Throwable $e) {
                //
            }
        }

        if ($locale === null) {
            try {
                $cookieLocale = $request->cookie('locale');
                if (is_string($cookieLocale) && trim($cookieLocale) !== '') {
                    $locale = trim($cookieLocale);
                }
            } catch (\Throwable $e) {
                //
            }
        }

        $locale = $this->normalizeLocale($locale);

        if ($locale !== null) {
            app()->setLocale($locale);
            Carbon::setLocale($locale);
            \Carbon\Carbon::setLocale($locale);
        }

        return $next($request);
    }

    private function normalizeLocale(?string $candidate): ?string
    {
        $candidate = $candidate !== null ? trim($candidate) : '';
        $candidate = $candidate === '' ? null : $candidate;

        try {
            $default = $this->siteLocale();

            if ($candidate === null) {
                return $default;
            }

            $resolved = $this->resolveAvailableLocale($candidate);
            if ($resolved !== null) {
                return $resolved;
            }

            if ($this->isActiveLocale($candidate)) {
                return $candidate;
            }

            return $default;
        } catch (\Throwable $e) {
            return $candidate ?: 'en';
        }
    }

    private function defaultLocale(): string
    {
        return $this->siteLocale();
    }

    private function siteLocale(): string
    {
        return Cache::remember('translation_locales:site', now()->addMinutes(10), function () {
            $svc = app(LocaleJsonService::class);

            $candidate = Setting::get('site_language', 'en');
            $candidate = is_string($candidate) ? trim($candidate) : 'en';

            if ($candidate !== '') {
                $resolved = $this->resolveAvailableLocale($candidate);
                if ($resolved !== null) {
                    return $resolved;
                }

                if ($this->isActiveLocale($candidate)) {
                    return $candidate;
                }
            }

            $list = $svc->listLocales();
            $first = is_array($list) && isset($list[0]) && is_object($list[0]) && is_string($list[0]->code ?? null)
                ? trim((string) $list[0]->code)
                : '';

            return $first !== '' ? $first : 'en';
        });
    }

    private function resolveAvailableLocale(string $candidate): ?string
    {
        $candidate = trim($candidate);
        if ($candidate === '') {
            return null;
        }

        $svc = app(LocaleJsonService::class);
        $available = $svc->listLocales();

        $map = [];
        foreach ($available as $loc) {
            $code = is_object($loc) && is_string($loc->code ?? null) ? trim((string) $loc->code) : '';
            if ($code === '') {
                continue;
            }
            $map[strtolower($code)] = $code;
        }

        if ($map === []) {
            return null;
        }

        $normalized = strtolower(str_replace('-', '_', $candidate));
        if (isset($map[$normalized])) {
            return $map[$normalized];
        }

        if (str_contains($normalized, '_')) {
            $base = explode('_', $normalized, 2)[0] ?? '';
            $base = is_string($base) ? trim($base) : '';
            if ($base !== '' && isset($map[$base])) {
                return $map[$base];
            }
        }

        return null;
    }

    private function isActiveLocale(string $code): bool
    {
        $code = trim($code);
        if ($code === '') {
            return false;
        }

        $cacheKey = 'translation_locales:active:' . $code;

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($code) {
            $svc = app(LocaleJsonService::class);
            return $svc->isLocaleActive($code);
        });
    }
}
