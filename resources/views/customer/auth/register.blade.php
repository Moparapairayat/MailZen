<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app('locale.direction')->dir() }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-bind:class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('Customer Register') }} - {{ config('app.name', 'MailZen') }}</title>

    <!-- Fonts -->
    @php
        $fontFamily = \App\Models\Setting::get('admin_font_family', 'Inter');
        $fontWeights = \App\Models\Setting::get('admin_font_weights', '400,500,600,700');
        $fontWeightsUrl = preg_replace('/\s*,\s*/', ';', $fontWeights);
        $fontFamilyUrl = str_replace(' ', '+', $fontFamily);
        $googleFontsUrl = "https://fonts.googleapis.com/css2?family={$fontFamilyUrl}:wght@{$fontWeightsUrl}&display=swap";

        try {
            $googleEnabledRaw = \App\Models\Setting::get('google_enabled', false);
            $googleEnabled = in_array($googleEnabledRaw, [1, '1', true, 'true', 'on', 'yes'], true);
        } catch (\Throwable $e) {
            $googleEnabled = false;
        }

        $activeLocales = collect(app(\App\Translation\LocaleJsonService::class)->listLocales());
    @endphp
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{ $googleFontsUrl }}" rel="stylesheet" />
    <style>
        body {
            --app-font-family: '{{ $fontFamily }}', sans-serif;
            font-family: var(--app-font-family);
        }
    </style>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @php
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
    @endphp
    <style>
        :root {
            --brand-color: {{ $brandColor }};
            --brand-rgb: {{ $brandR }}, {{ $brandG }}, {{ $brandB }};
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900" style="--app-font-family: '{{ $fontFamily }}', sans-serif; font-family: var(--app-font-family);">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-gray-100">
                    {{ __('Customer Register') }}
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Create your account') }}
                </p>
            </div>

            <form class="mt-8 space-y-6" method="POST" action="/customer/register">
                @csrf

                @if($errors->any())
                    <div class="rounded-md bg-red-50 dark:bg-red-900/50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                    {{ __('There were errors with your submission') }}
                                </h3>
                                <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                    <ul class="list-disc pl-5 space-y-1">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="first_name" class="sr-only">{{ __('First name') }}</label>
                        <input
                            id="first_name"
                            name="first_name"
                            type="text"
                            autocomplete="given-name"
                            required
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 rounded-t-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm"
                            placeholder="{{ __('First name') }}"
                            value="{{ old('first_name') }}"
                        >
                    </div>
                    <div>
                        <label for="last_name" class="sr-only">{{ __('Last name') }}</label>
                        <input
                            id="last_name"
                            name="last_name"
                            type="text"
                            autocomplete="family-name"
                            required
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm"
                            placeholder="{{ __('Last name') }}"
                            value="{{ old('last_name') }}"
                        >
                    </div>
                    <div>
                        <label for="company_name" class="sr-only">{{ __('Company name') }}</label>
                        <input
                            id="company_name"
                            name="company_name"
                            type="text"
                            autocomplete="organization"
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm"
                            placeholder="{{ __('Company name (optional)') }}"
                            value="{{ old('company_name') }}"
                        >
                    </div>
                    <div>
                        <label for="email" class="sr-only">{{ __('Email address') }}</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            required
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm"
                            placeholder="{{ __('Email address') }}"
                            value="{{ old('email') }}"
                        >
                    </div>
                    <div>
                        <label for="password" class="sr-only">{{ __('Password') }}</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="new-password"
                            required
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm"
                            placeholder="{{ __('Password') }}"
                        >
                    </div>
                    <div>
                        <label for="password_confirmation" class="sr-only">{{ __('Confirm password') }}</label>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            required
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 rounded-b-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm"
                            placeholder="{{ __('Confirm password') }}"
                        >
                    </div>
                </div>

                <div>
                    <button
                        type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                    >
                        {{ __('Create account') }}
                    </button>
                </div>

                @if($googleEnabled)
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300 dark:border-gray-700"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400">{{ __('OR') }}</span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <a
                            href="{{ route('customer.auth.google.redirect') }}"
                            class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                        >
                            <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                            {{ __('Continue with Google') }}
                        </a>
                    </div>
                @endif

                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-sm text-primary-600 hover:text-primary-500 dark:text-primary-400">
                        {{ __('Already have an account? Sign in') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    @if(isset($activeLocales) && $activeLocales instanceof \Illuminate\Support\Collection && $activeLocales->count() > 0)
        <div class="fixed top-4 right-4 z-50">
            <details class="relative">
                <summary class="list-none cursor-pointer inline-flex items-center justify-center h-9 w-9 rounded-full border border-gray-300 bg-white text-gray-600 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700 [&::-webkit-details-marker]:hidden">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M2 12h20" />
                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
                    </svg>
                </summary>
                <div class="absolute right-0 top-full mt-2 w-44 rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-lg overflow-hidden">
                    @foreach($activeLocales as $loc)
                        @php
                            $locCode = is_string($loc->code ?? null) ? trim((string) $loc->code) : '';
                            $locName = is_string($loc->name ?? null) ? trim((string) $loc->name) : '';
                            $locLabel = $locName !== '' ? $locName : $locCode;
                        @endphp
                        @if($locCode !== '')
                            <form method="POST" action="{{ route('language.guest.update') }}">
                                @csrf
                                <input type="hidden" name="locale" value="{{ $locCode }}" />
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 {{ app()->getLocale() === $locCode ? 'font-semibold' : '' }}">
                                    {{ $locLabel }}
                                </button>
                            </form>
                        @endif
                    @endforeach
                </div>
            </details>
        </div>
    @endif
</body>
</html>
