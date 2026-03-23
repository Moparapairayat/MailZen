<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app('locale.direction')->dir() }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-bind:class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        try {
            $siteTitle = \App\Models\Setting::get('site_title');
            $faviconPath = \App\Models\Setting::get('site_favicon');
            $metaDescription = \App\Models\Setting::get('meta_description');
            $metaKeywords = \App\Models\Setting::get('meta_keywords');
            $siteMeta = \App\Models\Setting::get('site_meta');
            $publicMetaImagePath = \App\Models\Setting::get('public_meta_image');
        } catch (\Throwable $e) {
            $siteTitle = config('app.name', 'MailZen');
            $faviconPath = null;
            $metaDescription = null;
            $metaKeywords = null;
            $siteMeta = null;
            $publicMetaImagePath = null;
        }

        if (!is_string($siteTitle) || trim($siteTitle) === '') {
            $siteTitle = config('app.name', 'MailZen');
        }

        $pageTitle = trim((string) $__env->yieldContent('title'));
        if ($pageTitle === '') {
            $pageTitle = 'Home';
        }
        $fullTitle = $pageTitle . ' - ' . $siteTitle;

        $brandingDisk = (string) config('filesystems.branding_disk', 'public');

        $faviconUrl = null;
        if (is_string($faviconPath) && trim($faviconPath) !== '') {
            $faviconUrl = $brandingDisk === 'public'
                ? \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($faviconPath, '/'))
                : \Illuminate\Support\Facades\Storage::disk($brandingDisk)->url($faviconPath);
        }

        $overrideMetaDescription = trim((string) $__env->yieldContent('metaDescription'));
        $pageMetaDescription = $overrideMetaDescription !== '' ? $overrideMetaDescription : $metaDescription;
        $pageMetaDescription = is_string($pageMetaDescription) ? trim((string) $pageMetaDescription) : null;

        $overrideMetaImage = trim((string) $__env->yieldContent('metaImage'));
        $metaImageUrl = null;
        if ($overrideMetaImage !== '') {
            $metaImageUrl = $overrideMetaImage;
        } elseif (is_string($publicMetaImagePath) && trim($publicMetaImagePath) !== '') {
            $metaImageUrl = $brandingDisk === 'public'
                ? \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($publicMetaImagePath, '/'))
                : \Illuminate\Support\Facades\Storage::disk($brandingDisk)->url($publicMetaImagePath);
        }

        $overrideOgType = trim((string) $__env->yieldContent('ogType'));
        $ogType = $overrideOgType !== '' ? $overrideOgType : 'website';

        $twitterCard = is_string($metaImageUrl) && trim($metaImageUrl) !== '' ? 'summary_large_image' : 'summary';
    @endphp

    @if(is_string($pageMetaDescription) && trim($pageMetaDescription) !== '')
        <meta name="description" content="{{ $pageMetaDescription }}">
    @endif
    @if(is_string($metaKeywords) && trim((string) $metaKeywords) !== '')
        <meta name="keywords" content="{{ $metaKeywords }}">
    @endif

    @if(is_string($faviconUrl) && trim($faviconUrl) !== '')
        <link rel="icon" href="{{ $faviconUrl }}">
    @endif

    <meta property="og:title" content="{{ $fullTitle }}">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="{{ $siteTitle }}">
    @if(is_string($pageMetaDescription) && trim($pageMetaDescription) !== '')
        <meta property="og:description" content="{{ $pageMetaDescription }}">
    @endif
    @if(is_string($metaImageUrl) && trim($metaImageUrl) !== '')
        <meta property="og:image" content="{{ $metaImageUrl }}">
        <meta name="twitter:image" content="{{ $metaImageUrl }}">
    @endif

    <meta name="twitter:card" content="{{ $twitterCard }}">
    <meta name="twitter:title" content="{{ $fullTitle }}">
    @if(is_string($pageMetaDescription) && trim($pageMetaDescription) !== '')
        <meta name="twitter:description" content="{{ $pageMetaDescription }}">
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

    <title>{{ $fullTitle }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=sora:400,500,600,700" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- GSAP -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollToPlugin.min.js"></script>
    
    @php
        $brandColor = null;
        if (isset($brandColorOverride) && is_string($brandColorOverride)) {
            $brandColor = trim((string) $brandColorOverride);
        }

        if (!is_string($brandColor) || $brandColor === '') {
            try {
                $brandColor = \App\Models\Setting::get('brand_color', '#3b82f6');
            } catch (\Throwable $e) {
                $brandColor = '#3b82f6';
            }
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

    <style>
        /* Initial states for GSAP animations */
        .gsap-fade-up { opacity: 0; transform: translateY(40px); }
        .gsap-fade-in { opacity: 0; }
        .gsap-scale-in { opacity: 0; transform: scale(0.9); }
        .gsap-slide-left { opacity: 0; transform: translateX(-40px); }
        .gsap-slide-right { opacity: 0; transform: translateX(40px); }
    </style>

    @php
        try {
            $metaPixelRaw = \App\Models\Setting::get('meta_pixel_id');
        } catch (\Throwable $e) {
            $metaPixelRaw = null;
        }

        $metaPixelId = is_string($metaPixelRaw) ? preg_replace('/\D+/', '', $metaPixelRaw) : null;
        $metaPixelId = is_string($metaPixelId) ? trim($metaPixelId) : null;
        if (!is_string($metaPixelId) || $metaPixelId === '') {
            $metaPixelId = null;
        }
    @endphp
    @if($metaPixelId)
        <script>
            (function (w, d, s, u, n, t, e) {
                if (w.fbq) return;
                n = w.fbq = function () {
                    n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments);
                };
                if (!w._fbq) w._fbq = n;
                n.push = n;
                n.loaded = true;
                n.version = '2.0';
                n.queue = [];
                t = d.createElement(s);
                t.async = true;
                t.src = u;
                e = d.getElementsByTagName(s)[0];
                e.parentNode.insertBefore(t, e);
            })(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');

            fbq('init', '{{ $metaPixelId }}');

            const mailpurseTrackMetaPageView = () => {
                if (typeof window.fbq === 'function') {
                    window.fbq('track', 'PageView');
                }
            };

            mailpurseTrackMetaPageView();
            document.addEventListener('turbo:load', mailpurseTrackMetaPageView);
        </script>
        <noscript>
            <img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{ $metaPixelId }}&ev=PageView&noscript=1" alt="" />
        </noscript>
    @endif
</head>
<body class="font-sans antialiased bg-white dark:bg-gray-900" data-mailpurse-page="@yield('pageId')">
    @if(empty($hidePublicNav))
    <!-- Navigation -->
    @php
        $navTheme = isset($navTheme) && is_string($navTheme) ? trim($navTheme) : 'light';
        $navTheme = in_array($navTheme, ['light', 'dark'], true) ? $navTheme : 'light';
        $navIsDark = $navTheme === 'dark';

        try {
            $publicHeaderSticky = (bool) \App\Models\Setting::get('public_header_sticky', 0);
        } catch (\Throwable $e) {
            $publicHeaderSticky = false;
        }

        try {
            $registrationEnabled = (bool) \App\Models\Setting::get('registration_enabled', true);
        } catch (\Throwable $e) {
            $registrationEnabled = true;
        }

        $navShellClass = $navIsDark
            ? 'bg-slate-950 border-slate-800'
            : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700';

        $navLinkClass = $navIsDark
            ? 'border-transparent text-slate-200 hover:border-slate-700 hover:text-white'
            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300';

        $navLinkActiveClass = $navIsDark
            ? 'border-primary-500 text-white'
            : 'border-primary-500 text-gray-900 dark:text-gray-100';

        $navDropdownClass = $navIsDark
            ? 'border-slate-800 bg-slate-950'
            : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800';

        $navDropdownItemClass = $navIsDark
            ? 'text-slate-200 hover:bg-slate-900'
            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700';

    @endphp
    <nav class="{{ $navShellClass }} border-b {{ $publicHeaderSticky ? 'sticky top-0 z-50' : '' }}">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{ route('home') }}" class="text-xl font-bold text-primary-600 dark:text-primary-400">
                            @php
                                try {
                                    $appLogo = \App\Models\Setting::get('app_logo');
                                    $appLogoDark = \App\Models\Setting::get('app_logo_dark');
                                } catch (\Throwable $e) {
                                    $appLogo = null;
                                    $appLogoDark = null;
                                }

                                $brandingDisk = (string) config('filesystems.branding_disk', 'public');
                            @endphp

                            @if(isset($appLogo) && is_string($appLogo) && trim($appLogo) !== '')
                                <img
                                    src="{{ $brandingDisk === 'public' ? \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($appLogo, '/')) : \Illuminate\Support\Facades\Storage::disk($brandingDisk)->url($appLogo) }}"
                                    alt="{{ __('App Logo') }}"
                                    class="block dark:hidden h-8 w-auto object-contain"
                                />

                                @if(isset($appLogoDark) && is_string($appLogoDark) && trim($appLogoDark) !== '')
                                    <img
                                        src="{{ $brandingDisk === 'public' ? \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($appLogoDark, '/')) : \Illuminate\Support\Facades\Storage::disk($brandingDisk)->url($appLogoDark) }}"
                                        alt="{{ __('App Logo') }}"
                                        class="hidden dark:block h-8 w-auto object-contain"
                                    />
                                @endif
                            @else
                                {{ config('app.name', 'MailZen') }}
                            @endif
                        </a>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        @php
                            try {
                                $homePageVariant = (string) \App\Models\Setting::get('home_page_variant', '1');
                                $navShowFeatures = (bool) \App\Models\Setting::get('nav_show_features', 1);
                                $navShowPricing = (bool) \App\Models\Setting::get('nav_show_pricing', 1);
                                $navShowBlog = (bool) \App\Models\Setting::get('nav_show_blog', 1);
                                $navShowRoadmap = (bool) \App\Models\Setting::get('nav_show_roadmap', 1);
                                $navShowDocs = (bool) \App\Models\Setting::get('nav_show_docs', 1);
                                $navShowApiDocs = (bool) \App\Models\Setting::get('nav_show_api_docs', 1);
                            } catch (\Throwable $e) {
                                $homePageVariant = '1';
                                $navShowFeatures = true;
                                $navShowPricing = true;
                                $navShowBlog = true;
                                $navShowRoadmap = true;
                                $navShowDocs = true;
                                $navShowApiDocs = true;
                            }

                            $homePageVariant = is_string($homePageVariant) ? trim($homePageVariant) : '1';
                            if (!in_array($homePageVariant, ['all', '1', '2', '3', '4', '5'], true)) {
                                $homePageVariant = '1';
                            }

                            $allowedVariants = $homePageVariant === 'all'
                                ? ['1', '2', '3', '4', '5']
                                : [$homePageVariant];

                            $navShowHomeDropdown = $homePageVariant === 'all';
                            $navShowHome1 = in_array('1', $allowedVariants, true);
                            $navShowHome2 = in_array('2', $allowedVariants, true);
                            $navShowHome3 = in_array('3', $allowedVariants, true);
                            $navShowHome4 = in_array('4', $allowedVariants, true);
                            $navShowHome5 = in_array('5', $allowedVariants, true);
                        @endphp

                        @if($navShowHomeDropdown && $homePageVariant === 'all')
                            <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                                @php
                                    $currentHomeVariant = null;
                                    if (request()->routeIs('home.variant')) {
                                        $routeVariant = (string) request()->route('variant');
                                        $routeVariant = trim($routeVariant);
                                        if (in_array($routeVariant, ['1', '2', '3', '4', '5'], true)) {
                                            $currentHomeVariant = $routeVariant;
                                        }
                                    }

                                    $homeTriggerLabel = $currentHomeVariant ? ('Home ' . $currentHomeVariant) : 'Home';
                                @endphp
                                <a href="{{ route('home') }}" class="{{ $navLinkClass }} h-[100%] inline-flex items-center gap-1 px-1 pt-1 border-b-2 text-sm font-medium focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-800 {{ request()->routeIs('home') || request()->routeIs('home.variant') ? $navLinkActiveClass : '' }}">
                                    {{ $homeTriggerLabel }}
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </a>
                                <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-1" class="absolute left-0 mt-2 w-40 rounded-md border {{ $navDropdownClass }} shadow-lg z-50" style="display: none;">
                                    <div class="py-2">
                                        @if($navShowHome1)
                                            <a href="{{ route('home.variant', ['variant' => 1]) }}" class="block px-4 py-2 text-sm {{ $navDropdownItemClass }}">Home 1</a>
                                        @endif
                                        @if($navShowHome2)
                                            <a href="{{ route('home.variant', ['variant' => 2]) }}" class="block px-4 py-2 text-sm {{ $navDropdownItemClass }}">Home 2</a>
                                        @endif
                                        @if($navShowHome3)
                                            <a href="{{ route('home.variant', ['variant' => 3]) }}" class="block px-4 py-2 text-sm {{ $navDropdownItemClass }}">Home 3</a>
                                        @endif
                                        @if($navShowHome4)
                                            <a href="{{ route('home.variant', ['variant' => 4]) }}" class="block px-4 py-2 text-sm {{ $navDropdownItemClass }}">Home 4</a>
                                        @endif
                                        {{-- @if($navShowHome5)
                                            <a href="{{ route('home.variant', ['variant' => 5]) }}" class="block px-4 py-2 text-sm {{ $navDropdownItemClass }}">Home 5</a>
                                        @endif --}}
                                    </div>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('home') }}" class="{{ $navLinkClass }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium {{ request()->routeIs('home') ? $navLinkActiveClass : '' }}">
                                Home
                            </a>
                        @endif

                        <!-- Solutions Mega Menu -->
                        {{-- <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                            <a href="#" class="{{ $navLinkClass }} h-[100%] inline-flex items-center gap-1 px-1 pt-1 border-b-2 text-sm font-medium focus:outline-none">
                                Solutions
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </a>
                            <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-1" class="absolute left-0 mt-2 w-[800px] rounded-2xl border {{ $navDropdownClass }} shadow-2xl z-50 overflow-hidden" style="display: none;">
                                <div class="p-8 bg-gradient-to-br {{ $navIsDark ? 'from-slate-900 to-slate-800' : 'from-white to-gray-50' }}">
                                    <!-- Header -->
                                    <div class="mb-6">
                                        <h3 class="text-lg font-bold {{ $navIsDark ? 'text-white' : 'text-gray-900' }} mb-2">Email Marketing Solutions</h3>
                                        <p class="text-sm {{ $navIsDark ? 'text-slate-300' : 'text-gray-600' }}">Powerful tools to automate and scale your email campaigns</p>
                                    </div>
                                    
                                    <div class="grid grid-cols-3 gap-6">
                                        <!-- Column 1: Automation -->
                                        <div>
                                            <div class="flex items-center gap-2 mb-4">
                                                <div class="w-2 h-2 rounded-full bg-[#84cc16]"></div>
                                                <span class="text-xs font-semibold {{ $navIsDark ? 'text-slate-400' : 'text-gray-500' }} uppercase tracking-wider">Automation</span>
                                            </div>
                                            <div class="space-y-3">
                                                <a href="#" class="group flex items-start gap-3 p-3 rounded-lg {{ $navIsDark ? 'hover:bg-slate-800' : 'hover:bg-white' }} transition-colors">
                                                    <div class="w-10 h-10 rounded-lg bg-[#84cc16]/10 flex items-center justify-center flex-shrink-0">
                                                        <svg class="w-5 h-5 text-[#84cc16]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                                    </div>
                                                    <div>
                                                        <div class="font-semibold {{ $navIsDark ? 'text-white' : 'text-gray-900' }} text-sm mb-1">Drip Campaigns</div>
                                                        <div class="text-xs {{ $navIsDark ? 'text-slate-400' : 'text-gray-600' }}">Automate email sequences for nurturing leads</div>
                                                    </div>
                                                </a>
                                                
                                                <a href="#" class="group flex items-start gap-3 p-3 rounded-lg {{ $navIsDark ? 'hover:bg-slate-800' : 'hover:bg-white' }} transition-colors">
                                                    <div class="w-10 h-10 rounded-lg bg-[#84cc16]/10 flex items-center justify-center flex-shrink-0">
                                                        <svg class="w-5 h-5 text-[#84cc16]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                                    </div>
                                                    <div>
                                                        <div class="font-semibold {{ $navIsDark ? 'text-white' : 'text-gray-900' }} text-sm mb-1">Trigger-Based</div>
                                                        <div class="text-xs {{ $navIsDark ? 'text-slate-400' : 'text-gray-600' }}">Send emails based on user actions and events</div>
                                                    </div>
                                                </a>
                                                
                                                <a href="#" class="group flex items-start gap-3 p-3 rounded-lg {{ $navIsDark ? 'hover:bg-slate-800' : 'hover:bg-white' }} transition-colors">
                                                    <div class="w-10 h-10 rounded-lg bg-[#84cc16]/10 flex items-center justify-center flex-shrink-0">
                                                        <svg class="w-5 h-5 text-[#84cc16]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    </div>
                                                    <div>
                                                        <div class="font-semibold {{ $navIsDark ? 'text-white' : 'text-gray-900' }} text-sm mb-1">Scheduled Sends</div>
                                                        <div class="text-xs {{ $navIsDark ? 'text-slate-400' : 'text-gray-600' }}">Plan and schedule campaigns in advance</div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                        
                                        <!-- Column 2: Segmentation -->
                                        <div>
                                            <div class="flex items-center gap-2 mb-4">
                                                <div class="w-2 h-2 rounded-full bg-[#0a3d3d]"></div>
                                                <span class="text-xs font-semibold {{ $navIsDark ? 'text-slate-400' : 'text-gray-500' }} uppercase tracking-wider">Segmentation</span>
                                            </div>
                                            <div class="space-y-3">
                                                <a href="#" class="group flex items-start gap-3 p-3 rounded-lg {{ $navIsDark ? 'hover:bg-slate-800' : 'hover:bg-white' }} transition-colors">
                                                    <div class="w-10 h-10 rounded-lg bg-[#0a3d3d]/10 flex items-center justify-center flex-shrink-0">
                                                        <svg class="w-5 h-5 text-[#0a3d3d]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                                    </div>
                                                    <div>
                                                        <div class="font-semibold {{ $navIsDark ? 'text-white' : 'text-gray-900' }} text-sm mb-1">List Management</div>
                                                        <div class="text-xs {{ $navIsDark ? 'text-slate-400' : 'text-gray-600' }}">Organize subscribers into targeted groups</div>
                                                    </div>
                                                </a>
                                                
                                                <a href="#" class="group flex items-start gap-3 p-3 rounded-lg {{ $navIsDark ? 'hover:bg-slate-800' : 'hover:bg-white' }} transition-colors">
                                                    <div class="w-10 h-10 rounded-lg bg-[#0a3d3d]/10 flex items-center justify-center flex-shrink-0">
                                                        <svg class="w-5 h-5 text-[#0a3d3d]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                                    </div>
                                                    <div>
                                                        <div class="font-semibold {{ $navIsDark ? 'text-white' : 'text-gray-900' }} text-sm mb-1">Smart Tags</div>
                                                        <div class="text-xs {{ $navIsDark ? 'text-slate-400' : 'text-gray-600' }}">Tag subscribers based on behavior</div>
                                                    </div>
                                                </a>
                                                
                                                <a href="#" class="group flex items-start gap-3 p-3 rounded-lg {{ $navIsDark ? 'hover:bg-slate-800' : 'hover:bg-white' }} transition-colors">
                                                    <div class="w-10 h-10 rounded-lg bg-[#0a3d3d]/10 flex items-center justify-center flex-shrink-0">
                                                        <svg class="w-5 h-5 text-[#0a3d3d]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                                    </div>
                                                    <div>
                                                        <div class="font-semibold {{ $navIsDark ? 'text-white' : 'text-gray-900' }} text-sm mb-1">Analytics</div>
                                                        <div class="text-xs {{ $navIsDark ? 'text-slate-400' : 'text-gray-600' }}">Track performance and engagement metrics</div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                        
                                        <!-- Column 3: Use Cases -->
                                        <div>
                                            <div class="flex items-center gap-2 mb-4">
                                                <div class="w-2 h-2 rounded-full bg-purple-500"></div>
                                                <span class="text-xs font-semibold {{ $navIsDark ? 'text-slate-400' : 'text-gray-500' }} uppercase tracking-wider">Use Cases</span>
                                            </div>
                                            <div class="space-y-3">
                                                <a href="#" class="group flex items-start gap-3 p-3 rounded-lg {{ $navIsDark ? 'hover:bg-slate-800' : 'hover:bg-white' }} transition-colors">
                                                    <div class="w-10 h-10 rounded-lg bg-purple-500/10 flex items-center justify-center flex-shrink-0">
                                                        <svg class="w-5 h-5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    </div>
                                                    <div>
                                                        <div class="font-semibold {{ $navIsDark ? 'text-white' : 'text-gray-900' }} text-sm mb-1">E-commerce</div>
                                                        <div class="text-xs {{ $navIsDark ? 'text-slate-400' : 'text-gray-600' }}">Cart abandonment and product updates</div>
                                                    </div>
                                                </a>
                                                
                                                <a href="#" class="group flex items-start gap-3 p-3 rounded-lg {{ $navIsDark ? 'hover:bg-slate-800' : 'hover:bg-white' }} transition-colors">
                                                    <div class="w-10 h-10 rounded-lg bg-purple-500/10 flex items-center justify-center flex-shrink-0">
                                                        <svg class="w-5 h-5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                                    </div>
                                                    <div>
                                                        <div class="font-semibold {{ $navIsDark ? 'text-white' : 'text-gray-900' }} text-sm mb-1">SaaS</div>
                                                        <div class="text-xs {{ $navIsDark ? 'text-slate-400' : 'text-gray-600' }}">Onboarding and feature announcements</div>
                                                    </div>
                                                </a>
                                                
                                                <a href="#" class="group flex items-start gap-3 p-3 rounded-lg {{ $navIsDark ? 'hover:bg-slate-800' : 'hover:bg-white' }} transition-colors">
                                                    <div class="w-10 h-10 rounded-lg bg-purple-500/10 flex items-center justify-center flex-shrink-0">
                                                        <svg class="w-5 h-5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                                    </div>
                                                    <div>
                                                        <div class="font-semibold {{ $navIsDark ? 'text-white' : 'text-gray-900' }} text-sm mb-1">Content</div>
                                                        <div class="text-xs {{ $navIsDark ? 'text-slate-400' : 'text-gray-600' }}">Newsletters and content distribution</div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Footer CTA -->
                                    <div class="mt-6 pt-6 border-t {{ $navIsDark ? 'border-slate-700' : 'border-gray-200' }}">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <div class="font-semibold {{ $navIsDark ? 'text-white' : 'text-gray-900' }} text-sm mb-1">Ready to get started?</div>
                                                <div class="text-xs {{ $navIsDark ? 'text-slate-400' : 'text-gray-600' }}">Start your free trial today</div>
                                            </div>
                                            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#84cc16] text-[#0a3d3d] font-semibold rounded-lg hover:bg-[#73b512] transition-colors text-sm">
                                                Get Started
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> --}}
                        
                        @if($navShowFeatures)
                            <a href="{{ route('features') }}" class="{{ $navLinkClass }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium {{ (request()->routeIs('features') || request()->is('features')) ? $navLinkActiveClass : '' }}">
                                Features
                            </a>
                        @endif
                        @if($navShowPricing)
                            <a href="{{ route('pricing') }}" class="{{ $navLinkClass }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium {{ request()->routeIs('pricing') ? $navLinkActiveClass : '' }}">
                                Pricing
                            </a>
                        @endif
                        @if($navShowBlog)
                            <a href="{{ route('blog.index') }}" class="{{ $navLinkClass }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium {{ request()->routeIs('blog.*') ? $navLinkActiveClass : '' }}">
                                Blog
                            </a>
                        @endif
                        @if ($navShowRoadmap && \Illuminate\Support\Facades\Route::has('roadmap'))
                            <a href="{{ route('roadmap') }}" class="{{ $navLinkClass }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium {{ request()->routeIs('roadmap') ? $navLinkActiveClass : '' }}">
                                Roadmap
                            </a>
                        @endif
                        @if ($navShowDocs && \Illuminate\Support\Facades\Route::has('docs'))
                            <a href="{{ route('docs') }}" class="{{ $navLinkClass }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium {{ request()->routeIs('docs') ? $navLinkActiveClass : '' }}">
                                Docs
                            </a>
                        @endif
                        @if ($navShowApiDocs && \Illuminate\Support\Facades\Route::has('api.docs.public'))
                            <a href="{{ route('api.docs.public') }}" class="{{ $navLinkClass }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium {{ request()->routeIs('api.docs.public') ? $navLinkActiveClass : '' }}">
                                API Docs
                            </a>
                        @endif
                    </div>
                </div>
                <div class="hidden sm:flex items-center">
                    @auth('customer')
                        <a href="{{ route('customer.dashboard') }}" class="{{ $navIsDark ? 'text-slate-200 hover:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }} px-3 py-2 text-sm font-medium">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" target="_blank" rel="noopener noreferrer" class="{{ $navIsDark ? 'text-slate-200 hover:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }} px-3 py-2 text-sm font-medium">
                            Login
                        </a>
                        @if($registrationEnabled)
                            <a href="{{ route('register') }}" class="ml-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700">
                                Sign Up
                            </a>
                        @endif
                    @endauth
                </div>
                
                <!-- Mobile menu button -->
                <div class="sm:hidden flex items-center">
                    <button type="button" x-data="" @click="$dispatch('toggle-mobile-menu')" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500">
                        <span class="sr-only">Open main menu</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Mobile Menu -->
    <div x-data="{ open: false }" @toggle-mobile-menu.window="open = !open" class="sm:hidden">
        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/50 z-40" @click="open = false"></div>
        
        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="fixed top-0 right-0 w-72 h-full {{ $navIsDark ? 'bg-slate-950' : 'bg-white dark:bg-gray-800' }} shadow-xl z-50 overflow-y-auto">
            <div class="flex items-center justify-between p-4 border-b {{ $navIsDark ? 'border-slate-800' : 'border-gray-200 dark:border-gray-700' }}">
                <span class="text-lg font-semibold {{ $navIsDark ? 'text-white' : 'text-gray-900 dark:text-white' }}">Menu</span>
                <button @click="open = false" class="p-2 rounded-md text-gray-400 hover:text-gray-500 {{ $navIsDark ? 'hover:bg-slate-900' : 'hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <div class="py-4">
                @if(isset($navShowHomeDropdown) && $navShowHomeDropdown && isset($homePageVariant) && $homePageVariant === 'all')
                    <div class="px-4 py-2 text-xs font-semibold {{ $navIsDark ? 'text-slate-400' : 'text-gray-500 dark:text-gray-400' }}">Home</div>
                    @if(isset($navShowHome1) && $navShowHome1)
                        <a href="{{ route('home.variant', ['variant' => 1]) }}" class="block px-4 py-3 text-base font-medium {{ $navIsDark ? 'text-slate-200 hover:bg-slate-900' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">Home 1</a>
                    @endif
                    @if(isset($navShowHome2) && $navShowHome2)
                        <a href="{{ route('home.variant', ['variant' => 2]) }}" class="block px-4 py-3 text-base font-medium {{ $navIsDark ? 'text-slate-200 hover:bg-slate-900' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">Home 2</a>
                    @endif
                    @if(isset($navShowHome3) && $navShowHome3)
                        <a href="{{ route('home.variant', ['variant' => 3]) }}" class="block px-4 py-3 text-base font-medium {{ $navIsDark ? 'text-slate-200 hover:bg-slate-900' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">Home 3</a>
                    @endif
                    @if(isset($navShowHome4) && $navShowHome4)
                        <a href="{{ route('home.variant', ['variant' => 4]) }}" class="block px-4 py-3 text-base font-medium {{ $navIsDark ? 'text-slate-200 hover:bg-slate-900' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">Home 4</a>
                    @endif
                    {{-- @if(isset($navShowHome5) && $navShowHome5)
                        <a href="{{ route('home.variant', ['variant' => 5]) }}" class="block px-4 py-3 text-base font-medium {{ $navIsDark ? 'text-slate-200 hover:bg-slate-900' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">Home 5</a>
                    @endif --}}
                @else
                    <a href="{{ route('home') }}" class="block px-4 py-3 text-base font-medium {{ $navIsDark ? 'text-slate-200 hover:bg-slate-900' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} {{ request()->routeIs('home') ? 'text-primary-600 dark:text-primary-400' : '' }}">Home</a>
                @endif
                
                <div class="border-t {{ $navIsDark ? 'border-slate-800' : 'border-gray-100 dark:border-gray-700' }} my-2"></div>
                
                @if(isset($navShowFeatures) && $navShowFeatures)
                    <a href="{{ route('features') }}" class="block px-4 py-3 text-base font-medium {{ $navIsDark ? 'text-slate-200 hover:bg-slate-900' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} {{ request()->routeIs('features') ? 'text-primary-600 dark:text-primary-400' : '' }}">Features</a>
                @endif
                @if(isset($navShowPricing) && $navShowPricing)
                    <a href="{{ route('pricing') }}" class="block px-4 py-3 text-base font-medium {{ $navIsDark ? 'text-slate-200 hover:bg-slate-900' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} {{ request()->routeIs('pricing') ? 'text-primary-600 dark:text-primary-400' : '' }}">Pricing</a>
                @endif
                @if(isset($navShowBlog) && $navShowBlog)
                    <a href="{{ route('blog.index') }}" class="block px-4 py-3 text-base font-medium {{ $navIsDark ? 'text-slate-200 hover:bg-slate-900' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} {{ request()->routeIs('blog.*') ? 'text-primary-600 dark:text-primary-400' : '' }}">Blog</a>
                @endif
                @if (isset($navShowRoadmap) && $navShowRoadmap && \Illuminate\Support\Facades\Route::has('roadmap'))
                    <a href="{{ route('roadmap') }}" class="block px-4 py-3 text-base font-medium {{ $navIsDark ? 'text-slate-200 hover:bg-slate-900' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} {{ request()->routeIs('roadmap') ? 'text-primary-600 dark:text-primary-400' : '' }}">Roadmap</a>
                @endif
                @if (isset($navShowDocs) && $navShowDocs && \Illuminate\Support\Facades\Route::has('docs'))
                    <a href="{{ route('docs') }}" class="block px-4 py-3 text-base font-medium {{ $navIsDark ? 'text-slate-200 hover:bg-slate-900' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} {{ request()->routeIs('docs') ? 'text-primary-600 dark:text-primary-400' : '' }}">Docs</a>
                @endif
                @if (isset($navShowApiDocs) && $navShowApiDocs && \Illuminate\Support\Facades\Route::has('api.docs.public'))
                    <a href="{{ route('api.docs.public') }}" class="block px-4 py-3 text-base font-medium {{ $navIsDark ? 'text-slate-200 hover:bg-slate-900' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} {{ request()->routeIs('api.docs.public') ? 'text-primary-600 dark:text-primary-400' : '' }}">API Docs</a>
                @endif
                
                <div class="border-t {{ $navIsDark ? 'border-slate-800' : 'border-gray-100 dark:border-gray-700' }} my-2"></div>
                
                <!-- Auth Links -->
                <div class="px-4 pt-2 space-y-3">
                    @auth('customer')
                        <a href="{{ route('customer.dashboard') }}" class="block w-full text-center py-3 text-base font-medium text-primary-600 dark:text-primary-400 border border-primary-600 dark:border-primary-400 rounded-lg {{ $navIsDark ? 'hover:bg-slate-900/60' : 'hover:bg-primary-50 dark:hover:bg-primary-900/20' }}">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="block w-full text-center py-3 text-base font-medium {{ $navIsDark ? 'text-slate-200 border-slate-700 hover:bg-slate-900' : 'text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700' }} border rounded-lg">
                            Login
                        </a>
                        @if($registrationEnabled)
                            <a href="{{ route('register') }}" class="block w-full text-center py-3 text-base font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700">
                                Sign Up
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Page Content -->
    <main>
        @yield('content')
    </main>

    <x-app-credit variant="public" />

    @php
        try {
            $gdprTitle = (string) \App\Models\Setting::get('gdpr_notice_title', 'We value your privacy');
            $gdprDescription = (string) \App\Models\Setting::get('gdpr_notice_description', 'We use cookies and similar technologies to improve your experience. You can accept or decline.');
            $gdprAcceptText = (string) \App\Models\Setting::get('gdpr_notice_accept_text', 'Accept');
            $gdprDeclineText = (string) \App\Models\Setting::get('gdpr_notice_decline_text', 'Decline');
            $gdprPosition = (string) \App\Models\Setting::get('gdpr_notice_position', 'bottom_full_width');
            $gdprDelaySeconds = \App\Models\Setting::get('gdpr_notice_delay_seconds', 0);
        } catch (\Throwable $e) {
            $gdprTitle = 'We value your privacy';
            $gdprDescription = 'We use cookies and similar technologies to improve your experience. You can accept or decline.';
            $gdprAcceptText = 'Accept';
            $gdprDeclineText = 'Decline';
            $gdprPosition = 'bottom_full_width';
            $gdprDelaySeconds = 0;
        }

        $gdprTitle = is_string($gdprTitle) ? trim($gdprTitle) : 'We value your privacy';
        $gdprDescription = is_string($gdprDescription) ? trim($gdprDescription) : '';
        $gdprAcceptText = is_string($gdprAcceptText) ? trim($gdprAcceptText) : 'Accept';
        $gdprDeclineText = is_string($gdprDeclineText) ? trim($gdprDeclineText) : 'Decline';
        $gdprPosition = is_string($gdprPosition) ? trim($gdprPosition) : 'bottom_full_width';
        $gdprDelaySeconds = is_numeric($gdprDelaySeconds) ? (int) $gdprDelaySeconds : 0;
        if ($gdprDelaySeconds < 0) {
            $gdprDelaySeconds = 0;
        }
        if (!in_array($gdprPosition, ['bottom_left', 'bottom_right', 'bottom_full_width'], true)) {
            $gdprPosition = 'bottom_full_width';
        }

        $gdprDelayMs = $gdprDelaySeconds * 1000;
        $gdprOuterClass = match ($gdprPosition) {
            'bottom_left' => 'left-4 bottom-4 max-w-md',
            'bottom_right' => 'right-4 bottom-4 max-w-md',
            default => 'left-0 right-0 bottom-0 w-full',
        };
        $gdprInnerWrapClass = $gdprPosition === 'bottom_full_width' ? 'mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 pb-4' : '';
        $gdprCardClass = $gdprPosition === 'bottom_full_width'
            ? 'rounded-t-xl sm:rounded-xl'
            : 'rounded-xl';
    @endphp

    <div
        data-gdpr-notice
        data-delay-ms="{{ (int) $gdprDelayMs }}"
        class="fixed z-50 hidden {{ $gdprOuterClass }}"
        role="dialog"
        aria-live="polite"
        aria-label="GDPR Notice"
    >
        <div class="{{ $gdprInnerWrapClass }}">
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-lg {{ $gdprCardClass }} p-4">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $gdprTitle !== '' ? e($gdprTitle) : 'We value your privacy' }}</div>
                        @if($gdprDescription !== '')
                            <div class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ e($gdprDescription) }}</div>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button" data-gdpr-decline class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800">
                            {{ $gdprDeclineText !== '' ? e($gdprDeclineText) : 'Decline' }}
                        </button>
                        <button type="button" data-gdpr-accept class="px-3 py-2 rounded-lg bg-primary-600 text-white text-sm hover:bg-primary-700">
                            {{ $gdprAcceptText !== '' ? e($gdprAcceptText) : 'Accept' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function initGdprNotice() {
            var el = document.querySelector('[data-gdpr-notice]');
            if (!el) return;
            if (el.dataset && el.dataset.bound === '1') return;
            if (el.dataset) el.dataset.bound = '1';

            var storageKey = 'mailpurse_gdpr_consent';
            try {
                var existing = localStorage.getItem(storageKey);
                if (existing === 'accepted' || existing === 'declined') {
                    el.remove();
                    return;
                }
            } catch (e) {
            }

            var delayMs = Number(el.getAttribute('data-delay-ms')) || 0;
            if (delayMs < 0) delayMs = 0;

            var show = function () {
                el.classList.remove('hidden');
            };

            if (delayMs > 0) {
                setTimeout(show, delayMs);
            } else {
                show();
            }

            var acceptBtn = el.querySelector('[data-gdpr-accept]');
            var declineBtn = el.querySelector('[data-gdpr-decline]');

            function setConsent(value) {
                try {
                    localStorage.setItem(storageKey, value);
                } catch (e) {
                }
                el.remove();
            }

            if (acceptBtn) {
                acceptBtn.addEventListener('click', function () {
                    setConsent('accepted');
                });
            }
            if (declineBtn) {
                declineBtn.addEventListener('click', function () {
                    setConsent('declined');
                });
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initGdprNotice);
        } else {
            initGdprNotice();
        }
        document.addEventListener('turbo:load', initGdprNotice);
    </script>
    
    @stack('scripts')
</body>
</html>
