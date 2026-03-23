<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app('locale.direction')->dir() }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-bind:class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('Register') }} - {{ config('app.name', 'MailZen') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

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

        $clamp = static fn (int $v): int => max(0, min(255, $v));

        $toHex = static function (int $r, int $g, int $b) use ($clamp): string {
            return sprintf('#%02X%02X%02X', $clamp($r), $clamp($g), $clamp($b));
        };

        $authGradientFrom = $brandColor;
        $authGradientTo = $toHex((int) round($brandR * 0.55), (int) round($brandG * 0.55), (int) round($brandB * 0.55));
    @endphp
    <style>
        :root {
            --brand-color: {{ $brandColor }};
            --brand-rgb: {{ $brandR }}, {{ $brandG }}, {{ $brandB }};
            --auth-gradient-from: {{ $authGradientFrom }};
            --auth-gradient-to: {{ $authGradientTo }};
        }
    </style>
    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);

            if (!input || !icon) {
                return;
            }

            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />';
            } else {
                input.type = 'password';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
            }
        }
    </script>
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen flex">
        <div class="flex-1 flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-20 xl:px-24 bg-white">
            <div class="mx-auto w-full max-w-sm lg:w-96">
                <div class="flex items-center mb-8">
                    @php
                        use Illuminate\Support\Facades\Storage;

                        try {
                            $appLogo = \App\Models\Setting::get('app_logo');
                            $appLogoDark = \App\Models\Setting::get('app_logo_dark');
                        } catch (\Throwable $e) {
                            $appLogo = null;
                            $appLogoDark = null;
                        }

                        $hasLogo = is_string($appLogo) && trim($appLogo) !== '';
                        $hasLogoDark = is_string($appLogoDark) && trim($appLogoDark) !== '';

                        try {
                            $googleEnabled = (bool) \App\Models\Setting::get('google_enabled', false);
                        } catch (\Throwable $e) {
                            $googleEnabled = false;
                        }

                        $activeLocales = collect(app(\App\Translation\LocaleJsonService::class)->listLocales());

                        $registerDefaults = [
                            'welcome_title' => 'Create Account',
                            'welcome_subtitle' => 'Create your account to access your dashboard.',
                            'button_register' => 'Register',
                            'or_label' => 'OR',
                            'google_button' => 'Register with Google',
                            'have_account' => 'Already have an Account?',
                            'sign_in' => 'Sign In',
                            'promo_title' => 'Revolutionize Email Marketing with Smarter Automation',
                            'testimonial_quote' => '"MailZen has completely transformed our email marketing process. It\'s reliable, efficient, and ensures our campaigns are always top-notch."',
                            'testimonial_name' => 'Michael Carter',
                            'testimonial_role' => 'Marketing Director at TechCorp',
                            'partners_title' => 'JOIN 1K+ TEAMS',
                            'partner_1' => 'Discord',
                            'partner_2' => 'Mailchimp',
                            'partner_3' => 'Grammarly',
                            'partner_4' => 'Attentive',
                            'partner_5' => 'Hellosign',
                            'partner_6' => 'Intercom',
                            'partner_7' => 'Square',
                            'partner_8' => 'Dropbox',
                        ];

                        $registerStrings = [];
                        foreach ($registerDefaults as $k => $d) {
                            try {
                                $val = \App\Models\Setting::get('register_' . $k, $d);
                            } catch (\Throwable $e) {
                                $val = $d;
                            }
                            $registerStrings[$k] = is_string($val) ? $val : (string) $d;
                        }
                    @endphp

                    @if($hasLogo)
                        <img
                            src="{{ (string) config('filesystems.branding_disk', 'public') === 'public' ? \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($appLogo, '/')) : Storage::disk((string) config('filesystems.branding_disk', 'public'))->url($appLogo) }}"
                            alt="{{ __('App Logo') }}"
                            class="block dark:hidden h-10 mr-3 object-contain w-36"
                        />

                        @if($hasLogoDark)
                            <img
                                src="{{ (string) config('filesystems.branding_disk', 'public') === 'public' ? \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($appLogoDark, '/')) : Storage::disk((string) config('filesystems.branding_disk', 'public'))->url($appLogoDark) }}"
                                alt="{{ __('App Logo') }}"
                                class="hidden dark:block h-10 mr-3 object-contain w-36"
                            />
                        @endif
                    @else
                        <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-primary-600 mr-3">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                    @endif
                </div>

                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">{{ $registerStrings['welcome_title'] ?? '' }}</h2>
                    <p class="text-sm text-gray-600">
                        {{ $registerStrings['welcome_subtitle'] ?? '' }}
                    </p>
                </div>

                @if(session('error'))
                    <div class="mb-6 rounded-md bg-red-50 p-4 border border-red-200">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">
                                    {{ session('error') }}
                                </h3>
                            </div>
                        </div>
                    </div>
                @endif

                @if(session('success'))
                    <div class="mb-6 rounded-md bg-green-50 p-4 border border-green-200">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.707a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-green-800">
                                    {{ session('success') }}
                                </h3>
                            </div>
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 rounded-md bg-red-50 p-4 border border-red-200">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">
                                    {{ __('There were errors with your submission') }}
                                </h3>
                                <div class="mt-2 text-sm text-red-700">
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

                <form method="POST" action="{{ url('/register') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Email') }}</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                autocomplete="email"
                                required
                                value="{{ old('email') }}"
                                class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus-visible:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900 placeholder-gray-400 sm:text-sm"
                                placeholder="{{ __('Enter your email') }}"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Password') }}</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                autocomplete="new-password"
                                required
                                class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:outline-none focus-visible:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900 placeholder-gray-400 sm:text-sm"
                                placeholder="{{ __('Enter your password') }}"
                            >
                            <button
                                type="button"
                                onclick="togglePassword('password', 'eye-icon-password')"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                            >
                                <svg id="eye-icon-password" class="h-5 w-5 text-gray-400 hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Repeat password') }}</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                autocomplete="new-password"
                                required
                                class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:outline-none focus-visible:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900 placeholder-gray-400 sm:text-sm"
                                placeholder="{{ __('Repeat your password') }}"
                            >
                            <button
                                type="button"
                                onclick="togglePassword('password_confirmation', 'eye-icon-password-confirm')"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                            >
                                <svg id="eye-icon-password-confirm" class="h-5 w-5 text-gray-400 hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <button
                            type="submit"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors"
                        >
                            {{ $registerStrings['button_register'] ?? '' }}
                        </button>
                    </div>

                    @if($googleEnabled)
                        <div class="relative">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-gray-300"></div>
                            </div>
                            <div class="relative flex justify-center text-sm">
                                <span class="px-2 bg-white text-gray-500">{{ $registerStrings['or_label'] ?? '' }}</span>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <a
                                href="{{ route('customer.auth.google.redirect') }}"
                                class="w-full flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors"
                            >
                                <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24">
                                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                </svg>
                                {{ $registerStrings['google_button'] ?? '' }}
                            </a>
                        </div>
                    @endif

                    <div class="text-center">
                        <p class="text-sm text-gray-600">
                            {{ $registerStrings['have_account'] ?? '' }}
                            <a href="{{ route('login') }}" class="font-medium text-primary-600 hover:text-primary-700">
                                {{ $registerStrings['sign_in'] ?? '' }}
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>

        <div class="hidden lg:block relative w-0 flex-1" style="background-image: linear-gradient(to bottom right, var(--auth-gradient-from), var(--auth-gradient-to));">
            <div class="absolute inset-0 flex flex-col justify-between p-12">
                <div class="flex-1 flex flex-col justify-center max-w-lg">
                    <h1 class="text-4xl font-bold text-white mb-8">
                        {{ $registerStrings['promo_title'] ?? '' }}
                    </h1>

                    <div class="mt-8">
                        <div class="relative">
                            <svg class="absolute -top-4 -left-4 w-16 h-16 text-white/40" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.984zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
                            </svg>
                            <blockquote class="text-lg text-white leading-relaxed pl-8">
                                {{ $registerStrings['testimonial_quote'] ?? '' }}
                            </blockquote>
                        </div>
                        <div class="mt-6 flex items-center">
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 rounded-full bg-white/20 flex items-center justify-center">
                                    <span class="text-xl font-semibold text-white">MC</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-base font-medium text-white">{{ $registerStrings['testimonial_name'] ?? '' }}</div>
                                <div class="text-sm text-white/70">{{ $registerStrings['testimonial_role'] ?? '' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-12">
                    <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-6">{{ $registerStrings['partners_title'] ?? '' }}</h3>
                    <div class="grid grid-cols-4 gap-8 opacity-80">
                        <div class="text-white text-sm font-semibold">{{ $registerStrings['partner_1'] ?? '' }}</div>
                        <div class="text-white text-sm font-semibold">{{ $registerStrings['partner_2'] ?? '' }}</div>
                        <div class="text-white text-sm font-semibold">{{ $registerStrings['partner_3'] ?? '' }}</div>
                        <div class="text-white text-sm font-semibold">{{ $registerStrings['partner_4'] ?? '' }}</div>
                        <div class="text-white text-sm font-semibold">{{ $registerStrings['partner_5'] ?? '' }}</div>
                        <div class="text-white text-sm font-semibold">{{ $registerStrings['partner_6'] ?? '' }}</div>
                        <div class="text-white text-sm font-semibold">{{ $registerStrings['partner_7'] ?? '' }}</div>
                        <div class="text-white text-sm font-semibold">{{ $registerStrings['partner_8'] ?? '' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(isset($activeLocales) && $activeLocales instanceof \Illuminate\Support\Collection && $activeLocales->count() > 0)
        <div class="fixed top-4 right-4 z-50">
            <details class="relative">
                <summary class="list-none cursor-pointer inline-flex items-center justify-center h-9 w-9 rounded-full border border-gray-300 bg-white text-gray-600 shadow-sm hover:bg-gray-50 [&::-webkit-details-marker]:hidden">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M2 12h20" />
                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
                    </svg>
                </summary>
                <div class="absolute right-0 top-full mt-2 w-44 rounded-md border border-gray-200 bg-white shadow-lg overflow-hidden">
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
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 {{ app()->getLocale() === $locCode ? 'font-semibold' : '' }}">
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
