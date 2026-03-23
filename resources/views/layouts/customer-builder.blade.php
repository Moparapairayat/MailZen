<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app('locale.direction')->dir() }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-bind:class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $pageTitle = trim($__env->yieldContent('title', 'Builder'));
    @endphp

    @php
        try {
            $siteTitle = \App\Models\Setting::get('site_title', \App\Models\Setting::get('app_name', config('app.name', 'MailZen')));
            $faviconPath = \App\Models\Setting::get('site_favicon');
            $siteMeta = \App\Models\Setting::get('site_meta');
        } catch (\Throwable $e) {
            $siteTitle = config('app.name', 'MailZen');
            $faviconPath = null;
            $siteMeta = null;
        }

        if (!is_string($siteTitle) || trim($siteTitle) === '') {
            $siteTitle = config('app.name', 'MailZen');
        }

        $brandingDisk = (string) config('filesystems.branding_disk', 'public');
        $faviconUrl = null;
        if (is_string($faviconPath) && trim($faviconPath) !== '') {
            $faviconUrl = $brandingDisk === 'public'
                ? \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($faviconPath, '/'))
                : \Illuminate\Support\Facades\Storage::disk($brandingDisk)->url($faviconPath);
        }
    @endphp

    @if(is_string($faviconUrl) && trim($faviconUrl) !== '')
        <link rel="icon" href="{{ $faviconUrl }}">
    @endif

    @if(is_string($siteMeta) && trim($siteMeta) !== '' && preg_match('/<\s*(meta|link|script|style|base|noscript)\b/i', $siteMeta))
        @php
            $siteMetaSafe = '';
            if (preg_match_all('/<\s*(meta|link|base)\b[^>]*\/?>/i', $siteMeta, $m1)) {
                $siteMetaSafe .= implode("\n", $m1[0]) . "\n";
            }
            if (preg_match_all('/<\s*(script|style|noscript)\b[^>]*>.*?<\s*\/\s*\\1\s*>/is', $siteMeta, $m2)) {
                $siteMetaSafe .= implode("\n", $m2[0]) . "\n";
            }
            $siteMetaSafe = trim($siteMetaSafe);
        @endphp
        @if($siteMetaSafe !== '')
            {!! $siteMetaSafe !!}
        @endif
    @endif

    <title>{{ __($pageTitle) }} - {{ $siteTitle }}</title>

    @php
        $fontFamily = \App\Models\Setting::get('admin_font_family', 'Inter');
        $fontWeights = \App\Models\Setting::get('admin_font_weights', '400,500,600,700');
        $fontWeightsUrl = preg_replace('/\s*,\s*/', ';', $fontWeights);
        $fontFamilyUrl = str_replace(' ', '+', $fontFamily);
        $googleFontsUrl = "https://fonts.googleapis.com/css2?family={$fontFamilyUrl}:wght@{$fontWeightsUrl}&display=swap";
    @endphp

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{ $googleFontsUrl }}" rel="stylesheet" />

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

        body {
            font-family: '{{ $fontFamily }}', sans-serif;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="customer-theme font-sans antialiased bg-admin-main text-admin-text-primary h-screen overflow-hidden" style="--app-font-family: '{{ $fontFamily }}', sans-serif; font-family: var(--app-font-family);">
    <main class="h-screen overflow-hidden">
        @php
            $toastPosition = \App\Models\Setting::get('toast_position', 'top_right');
            $allowedToastPositions = ['top_left', 'top_center', 'top_right', 'bottom_left', 'bottom_center', 'bottom_right'];
            $toastPosition = is_string($toastPosition) ? trim($toastPosition) : 'top_right';
            if (!in_array($toastPosition, $allowedToastPositions, true)) {
                $toastPosition = 'top_right';
            }

            $toastRootClass = match ($toastPosition) {
                'top_left' => 'fixed top-4 left-4 items-start',
                'top_center' => 'fixed top-4 left-1/2 -translate-x-1/2 items-center',
                'top_right' => 'fixed top-4 right-4 items-end',
                'bottom_left' => 'fixed bottom-4 left-4 items-start',
                'bottom_center' => 'fixed bottom-4 left-1/2 -translate-x-1/2 items-center',
                'bottom_right' => 'fixed bottom-4 right-4 items-end',
                default => 'fixed top-4 right-4 items-end',
            };
            $toastRootClass .= ' z-[9999] flex flex-col gap-3 w-full max-w-sm';

            $toasts = [];

            $success = session()->pull('success');
            if (is_string($success) && trim($success) !== '') {
                $toasts[] = ['type' => 'success', 'title' => __('You\'re all set'), 'message' => $success];
            }

            $info = session()->pull('info');
            if (is_string($info) && trim($info) !== '') {
                $toasts[] = ['type' => 'info', 'title' => __('Just so you know'), 'message' => $info];
            }

            $warning = session()->pull('warning');
            if (is_string($warning) && trim($warning) !== '') {
                $toasts[] = ['type' => 'warning', 'title' => __('Take a quick look'), 'message' => $warning];
            }

            $error = session()->pull('error');
            if (is_string($error) && trim($error) !== '') {
                $toasts[] = ['type' => 'error', 'title' => __('Something went wrong'), 'message' => $error];
            }

            if ($errors->any()) {
                $toasts[] = ['type' => 'error', 'title' => __('Something went wrong'), 'message' => implode("\n", $errors->all())];
            }
        @endphp

        <div data-toast-root data-toast-position="{{ $toastPosition }}" class="{{ $toastRootClass }}"></div>
        <script>
            window.__mailpursePageToasts = @json($toasts);
        </script>

        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
