<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app('locale.direction')->dir() }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-bind:class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $fontFamily = \App\Models\Setting::get('admin_font_family', 'Inter');
        $fontWeights = \App\Models\Setting::get('admin_font_weights', '400,500,600,700');
        $fontWeightsUrl = preg_replace('/\s*,\s*/', ';', $fontWeights);
        $fontFamilyUrl = str_replace(' ', '+', $fontFamily);
        $googleFontsUrl = "https://fonts.googleapis.com/css2?family={$fontFamilyUrl}:wght@{$fontWeightsUrl}&display=swap";

        try {
            $appName = \App\Models\Setting::get('app_name', config('app.name', 'MailZen'));
        } catch (\Throwable $e) {
            $appName = config('app.name', 'MailZen');
        }

        if (!is_string($appName) || trim($appName) === '') {
            $appName = config('app.name', 'MailZen');
        }

        try {
            $appLogo = \App\Models\Setting::get('app_logo');
            $appLogoDark = \App\Models\Setting::get('app_logo_dark');
        } catch (\Throwable $e) {
            $appLogo = null;
            $appLogoDark = null;
        }

        $brandingDisk = (string) config('filesystems.branding_disk', 'public');
        $resolveLogoUrl = static function ($path) use ($brandingDisk): ?string {
            if (!is_string($path) || trim($path) === '') {
                return null;
            }

            $path = ltrim(trim($path), '/');

            return $brandingDisk === 'public'
                ? \Illuminate\Support\Facades\Storage::disk('public')->url($path)
                : \Illuminate\Support\Facades\Storage::disk($brandingDisk)->url($path);
        };

        $logoUrl = $resolveLogoUrl($appLogo);
        $logoDarkUrl = $resolveLogoUrl($appLogoDark);

        try {
            $brandColor = \App\Models\Setting::get('brand_color', '#3b82f6');
        } catch (\Throwable $e) {
            $brandColor = '#3b82f6';
        }

        $brandColor = is_string($brandColor) ? trim($brandColor) : '#3b82f6';
        if ($brandColor === '' || !preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $brandColor)) {
            $brandColor = '#3b82f6';
        }

        $brandHex = ltrim($brandColor, '#');
        if (strlen($brandHex) === 3) {
            $brandHex = $brandHex[0] . $brandHex[0] . $brandHex[1] . $brandHex[1] . $brandHex[2] . $brandHex[2];
        }

        $brandR = hexdec(substr($brandHex, 0, 2));
        $brandG = hexdec(substr($brandHex, 2, 2));
        $brandB = hexdec(substr($brandHex, 4, 2));

        $clamp = static fn (int $value): int => max(0, min(255, $value));
        $toHex = static function (int $r, int $g, int $b) use ($clamp): string {
            return sprintf('#%02X%02X%02X', $clamp($r), $clamp($g), $clamp($b));
        };

        $panelGradientFrom = $toHex(
            (int) round($brandR * 0.85),
            (int) round($brandG * 0.88),
            (int) round($brandB * 0.95)
        );
        $panelGradientTo = $toHex(
            (int) round($brandR * 0.42),
            (int) round($brandG * 0.46),
            (int) round($brandB * 0.62)
        );

        $highlights = [
            __('Campaign health and delivery visibility'),
            __('Customer billing, plans, and permissions'),
            __('Infrastructure controls in one secure workspace'),
        ];
    @endphp

    <title>{{ __('Admin Login') }} - {{ $appName }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{ $googleFontsUrl }}" rel="stylesheet" />
    <style>
        :root {
            --brand-color: {{ $brandColor }};
            --brand-rgb: {{ $brandR }}, {{ $brandG }}, {{ $brandB }};
            --panel-gradient-from: {{ $panelGradientFrom }};
            --panel-gradient-to: {{ $panelGradientTo }};
        }

        body {
            --app-font-family: '{{ $fontFamily }}', sans-serif;
            font-family: var(--app-font-family);
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        function toggleAdminPassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('password-visibility-icon');

            if (!input || !icon) {
                return;
            }

            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';

            icon.innerHTML = isHidden
                ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3l18 18" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.584 10.587a2 2 0 102.828 2.829" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.878 5.091A9.955 9.955 0 0112 4.875c4.478 0 8.268 2.943 9.542 7a9.97 9.97 0 01-2.31 3.95M6.228 6.231A9.97 9.97 0 002.458 11.875c1.274 4.057 5.064 7 9.542 7a9.96 9.96 0 005.045-1.371" />'
                : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15a3 3 0 100-6 3 3 0 000 6z" />';
        }
    </script>
</head>
<body class="admin-theme antialiased text-slate-900 dark:text-slate-100">
    <div class="relative min-h-screen overflow-hidden bg-[radial-gradient(circle_at_top_left,_rgba(var(--brand-rgb),0.14),_transparent_32%),linear-gradient(180deg,_#f7f9fc_0%,_#eef3f8_48%,_#e7eef7_100%)] dark:bg-[radial-gradient(circle_at_top_left,_rgba(var(--brand-rgb),0.22),_transparent_28%),linear-gradient(180deg,_#050b16_0%,_#08101d_48%,_#0b1426_100%)]">
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -left-20 top-20 h-64 w-64 rounded-full bg-[rgba(var(--brand-rgb),0.18)] blur-3xl dark:bg-[rgba(var(--brand-rgb),0.2)]"></div>
            <div class="absolute right-[-4rem] top-12 h-72 w-72 rounded-full bg-sky-200/40 blur-3xl dark:bg-sky-400/10"></div>
            <div class="absolute bottom-[-4rem] left-1/3 h-72 w-72 rounded-full bg-indigo-200/30 blur-3xl dark:bg-indigo-500/10"></div>
            <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.18)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.18)_1px,transparent_1px)] bg-[size:72px_72px] opacity-[0.18] dark:opacity-[0.06]"></div>
        </div>

        <button
            type="button"
            class="absolute right-4 top-4 z-20 inline-flex items-center gap-2 rounded-full border border-white/60 bg-white/80 px-3 py-2 text-xs font-semibold text-slate-700 shadow-lg shadow-slate-900/5 backdrop-blur transition hover:bg-white dark:border-white/10 dark:bg-slate-900/70 dark:text-slate-200 dark:hover:bg-slate-900"
            @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)"
        >
            <svg x-show="!darkMode" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v2.25M12 18.75V21M4.97 4.97l1.59 1.59M17.44 17.44l1.59 1.59M3 12h2.25M18.75 12H21M4.97 19.03l1.59-1.59M17.44 6.56l1.59-1.59M15.75 12A3.75 3.75 0 1112 8.25 3.75 3.75 0 0115.75 12z" />
            </svg>
            <svg x-show="darkMode" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 12.79A9 9 0 1111.21 3c-.02.25-.03.5-.03.75A7.5 7.5 0 0018.75 11.25c.26 0 .51-.01.76-.03A9.03 9.03 0 0121 12.79z" />
            </svg>
            <span x-text="darkMode ? '{{ __('Dark') }}' : '{{ __('Light') }}'"></span>
        </button>

        <div class="relative mx-auto flex min-h-screen w-full max-w-6xl items-center px-4 py-10 sm:px-6 lg:px-8">
            <div class="grid w-full overflow-hidden rounded-[30px] border border-white/60 bg-white/75 shadow-[0_25px_80px_-24px_rgba(15,23,42,0.45)] backdrop-blur-xl dark:border-white/10 dark:bg-slate-950/70 lg:grid-cols-[1.1fr,0.9fr]">
                <section class="relative hidden overflow-hidden px-10 py-10 text-white lg:flex lg:flex-col" style="background-image: linear-gradient(155deg, var(--panel-gradient-from) 0%, #0f172a 58%, var(--panel-gradient-to) 100%);">
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(255,255,255,0.22),_transparent_28%),radial-gradient(circle_at_bottom_left,_rgba(255,255,255,0.12),_transparent_22%)]"></div>
                    <div class="relative flex h-full flex-col">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                @if($logoUrl)
                                    <img
                                        src="{{ $logoUrl }}"
                                        alt="{{ __('App Logo') }}"
                                        class="h-10 w-auto max-w-[9.5rem] rounded-lg object-contain ring-1 ring-white/10 dark:hidden"
                                    >
                                    @if($logoDarkUrl)
                                        <img
                                            src="{{ $logoDarkUrl }}"
                                            alt="{{ __('App Logo') }}"
                                            class="hidden h-10 w-auto max-w-[9.5rem] rounded-lg object-contain ring-1 ring-white/10 dark:block"
                                        >
                                    @endif
                                @else
                                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl border border-white/15 bg-white/10 shadow-inner shadow-white/10">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7.75A2.75 2.75 0 016.75 5h10.5A2.75 2.75 0 0120 7.75v8.5A2.75 2.75 0 0117.25 19H6.75A2.75 2.75 0 014 16.25v-8.5z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.75 7l7.25 5 7.25-5" />
                                        </svg>
                                    </div>
                                @endif
                                <div>
                                    <p class="text-sm font-semibold tracking-[0.24em] text-white/70">{{ __('ADMIN') }}</p>
                                    <p class="text-base font-semibold">{{ $appName }}</p>
                                </div>
                            </div>
                            <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-white/80">
                                {{ __('Secure Console') }}
                            </span>
                        </div>

                        <div class="mt-16">
                            <p class="text-sm font-semibold uppercase tracking-[0.28em] text-white/60">{{ __('Control Center') }}</p>
                            <h1 class="mt-5 max-w-md text-4xl font-semibold leading-tight">
                                {{ __('Run the platform from one refined command room.') }}
                            </h1>
                            <p class="mt-5 max-w-lg text-base leading-7 text-white/74">
                                {{ __('Monitor customers, campaigns, billing, and infrastructure from a login experience that feels intentional from the first screen.') }}
                            </p>
                        </div>

                        <div class="mt-10 space-y-4">
                            @foreach($highlights as $highlight)
                                <div class="flex items-start gap-3 rounded-2xl border border-white/10 bg-white/8 px-4 py-4 backdrop-blur-sm">
                                    <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-white/12 ring-1 ring-white/10">
                                        <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <p class="text-sm leading-6 text-white/80">{{ $highlight }}</p>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-auto grid grid-cols-3 gap-4 pt-10">
                            <div class="rounded-2xl border border-white/10 bg-white/8 px-4 py-4 backdrop-blur-sm">
                                <p class="text-2xl font-semibold">24/7</p>
                                <p class="mt-1 text-xs uppercase tracking-[0.2em] text-white/55">{{ __('Visibility') }}</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/8 px-4 py-4 backdrop-blur-sm">
                                <p class="text-2xl font-semibold">360°</p>
                                <p class="mt-1 text-xs uppercase tracking-[0.2em] text-white/55">{{ __('Oversight') }}</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/8 px-4 py-4 backdrop-blur-sm">
                                <p class="text-2xl font-semibold">1</p>
                                <p class="mt-1 text-xs uppercase tracking-[0.2em] text-white/55">{{ __('Workspace') }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="relative px-5 py-6 sm:px-8 sm:py-8 lg:px-10 lg:py-10">
                    <div class="mx-auto w-full max-w-md">
                        <div class="flex items-start justify-between gap-4 lg:hidden">
                            <div class="flex items-center gap-3">
                                @if($logoUrl)
                                    <img
                                        src="{{ $logoUrl }}"
                                        alt="{{ __('App Logo') }}"
                                        class="h-10 w-auto max-w-[8rem] object-contain dark:hidden"
                                    >
                                    @if($logoDarkUrl)
                                        <img
                                            src="{{ $logoDarkUrl }}"
                                            alt="{{ __('App Logo') }}"
                                            class="hidden h-10 w-auto max-w-[8rem] object-contain dark:block"
                                        >
                                    @endif
                                @else
                                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-primary-600 text-white shadow-lg shadow-primary-600/20">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7.75A2.75 2.75 0 016.75 5h10.5A2.75 2.75 0 0120 7.75v8.5A2.75 2.75 0 0117.25 19H6.75A2.75 2.75 0 014 16.25v-8.5z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.75 7l7.25 5 7.25-5" />
                                        </svg>
                                    </div>
                                @endif
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $appName }}</p>
                                    <p class="text-xs uppercase tracking-[0.24em] text-slate-500 dark:text-slate-400">{{ __('Admin') }}</p>
                                </div>
                            </div>
                            <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                                {{ __('Private') }}
                            </span>
                        </div>

                        <div class="mt-8">
                            <span class="inline-flex items-center gap-2 rounded-full border border-primary-500/20 bg-primary-500/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-primary-600 dark:text-primary-500">
                                <span class="h-1.5 w-1.5 rounded-full bg-primary-600"></span>
                                {{ __('Secure access') }}
                            </span>
                            <h2 class="mt-5 text-3xl font-semibold tracking-tight text-slate-950 dark:text-white sm:text-[2rem]">
                                {{ __('Admin Login') }}
                            </h2>
                            <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-400">
                                {{ __('Sign in to manage customers, performance, and platform settings with elevated control.') }}
                            </p>
                        </div>

                        @if($errors->any())
                            <div class="mt-8 rounded-2xl border border-rose-200 bg-rose-50/90 p-4 shadow-sm dark:border-rose-500/20 dark:bg-rose-500/10">
                                <div class="flex items-start gap-3">
                                    <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-500/10 text-rose-600 dark:bg-rose-500/15 dark:text-rose-300">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v4m0 4h.01M10.29 3.86l-7.55 13.08A1 1 0 003.61 18.5h16.78a1 1 0 00.87-1.56L13.71 3.86a1 1 0 00-1.73 0z" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="text-sm font-semibold text-rose-700 dark:text-rose-200">
                                            {{ __('There were errors with your submission') }}
                                        </h3>
                                        <ul class="mt-2 space-y-1 text-sm text-rose-700/90 dark:text-rose-100/80">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <form class="mt-8 space-y-5" method="POST" action="{{ route('admin.login') }}" data-turbo="false">
                            @csrf

                            <div class="space-y-2">
                                <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">
                                    {{ __('Email address') }}
                                </label>
                                <div class="group relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 transition group-focus-within:text-primary-600 dark:text-slate-500 dark:group-focus-within:text-primary-400">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 7.5h16.5M5.25 6h13.5A1.5 1.5 0 0120.25 7.5v9A1.5 1.5 0 0118.75 18H5.25a1.5 1.5 0 01-1.5-1.5v-9A1.5 1.5 0 015.25 6z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7l8 6 8-6" />
                                        </svg>
                                    </span>
                                    <input
                                        id="email"
                                        name="email"
                                        type="email"
                                        autocomplete="email"
                                        required
                                        value="{{ old('email') }}"
                                        class="block w-full rounded-2xl border border-slate-200 bg-white/90 py-3.5 pl-12 pr-4 text-sm text-slate-900 shadow-sm shadow-slate-950/5 transition placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900/80 dark:text-white dark:placeholder:text-slate-500"
                                        placeholder="{{ __('you@company.com') }}"
                                    >
                                </div>
                            </div>

                            <div class="space-y-2">
                                <div class="flex items-center justify-between gap-4">
                                    <label for="password" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">
                                        {{ __('Password') }}
                                    </label>
                                    <span class="text-xs font-medium uppercase tracking-[0.22em] text-slate-400 dark:text-slate-500">
                                        {{ __('Protected') }}
                                    </span>
                                </div>
                                <div class="group relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 transition group-focus-within:text-primary-600 dark:text-slate-500 dark:group-focus-within:text-primary-400">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16.5 10.5V7.875a4.5 4.5 0 10-9 0V10.5M6.75 10.5h10.5A1.5 1.5 0 0118.75 12v6A1.5 1.5 0 0117.25 19.5H6.75A1.5 1.5 0 015.25 18v-6a1.5 1.5 0 011.5-1.5z" />
                                        </svg>
                                    </span>
                                    <input
                                        id="password"
                                        name="password"
                                        type="password"
                                        autocomplete="current-password"
                                        required
                                        class="block w-full rounded-2xl border border-slate-200 bg-white/90 py-3.5 pl-12 pr-12 text-sm text-slate-900 shadow-sm shadow-slate-950/5 transition placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900/80 dark:text-white dark:placeholder:text-slate-500"
                                        placeholder="{{ __('Enter your password') }}"
                                    >
                                    <button
                                        type="button"
                                        onclick="toggleAdminPassword()"
                                        class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 transition hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300"
                                        aria-label="{{ __('Toggle password visibility') }}"
                                    >
                                        <svg id="password-visibility-icon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15a3 3 0 100-6 3 3 0 000 6z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200/80 bg-slate-50/80 px-4 py-3 dark:border-slate-800 dark:bg-slate-900/70">
                                <label for="remember" class="flex cursor-pointer items-center gap-3">
                                    <input
                                        id="remember"
                                        name="remember"
                                        type="checkbox"
                                        class="h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600 dark:bg-slate-800"
                                    >
                                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                        {{ __('Remember me') }}
                                    </span>
                                </label>
                                <span class="text-xs uppercase tracking-[0.22em] text-slate-400 dark:text-slate-500">
                                    {{ __('Trusted device') }}
                                </span>
                            </div>

                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-primary-600 px-4 py-3.5 text-sm font-semibold text-white shadow-[0_18px_40px_-18px_rgba(var(--brand-rgb),0.9)] transition hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-slate-950"
                            >
                                <span>{{ __('Sign in to admin') }}</span>
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 12h14M13 6l6 6-6 6" />
                                </svg>
                            </button>
                        </form>

                        <div class="mt-8 flex flex-col gap-3 border-t border-slate-200/80 pt-6 dark:border-slate-800">
                            <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                                <span class="inline-block h-2 w-2 rounded-full bg-emerald-500"></span>
                                {{ __('Authorized staff only. Activity is protected and monitored.') }}
                            </div>
                            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-sm font-medium text-slate-600 transition hover:text-primary-600 dark:text-slate-300 dark:hover:text-primary-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.5 6L4.5 12l6 6M5.25 12h14.25" />
                                </svg>
                                {{ __('Need customer access instead?') }}
                            </a>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</body>
</html>
