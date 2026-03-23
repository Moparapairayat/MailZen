<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app('locale.direction')->dir() }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-bind:class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $pageTitle = trim($__env->yieldContent('title', 'Dashboard'));
    @endphp
    @php
        try {
            $siteTitle = \App\Models\Setting::get('site_title', \App\Models\Setting::get('app_name', config('app.name', 'MailZen')));
            $faviconPath = \App\Models\Setting::get('site_favicon');
            $metaDescription = \App\Models\Setting::get('meta_description');
            $metaKeywords = \App\Models\Setting::get('meta_keywords');
            $siteMeta = \App\Models\Setting::get('site_meta');
        } catch (\Throwable $e) {
            $siteTitle = config('app.name', 'MailZen');
            $faviconPath = null;
            $metaDescription = null;
            $metaKeywords = null;
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

    @if(is_string($metaDescription) && trim($metaDescription) !== '')
        <meta name="description" content="{{ $metaDescription }}">
    @endif
    @if(is_string($metaKeywords) && trim($metaKeywords) !== '')
        <meta name="keywords" content="{{ $metaKeywords }}">
    @endif
    
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

    <!-- Fonts -->
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
    <style>
        body {
            font-family: '{{ $fontFamily }}', sans-serif;
        }
    </style>

    <script>
        window.__mailpurseSupportedGoogleFontFamilies = @json(config('mailzen.fonts.supported_google_families', []));
    </script>

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

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
    <style>
        aside[data-sidebar="app"][data-collapsed="true"] nav p {
            display: none;
        }

        aside[data-sidebar="app"][data-collapsed="true"] nav a,
        aside[data-sidebar="app"][data-collapsed="true"] nav button {
            justify-content: center;
            gap: 0;
        }

        aside[data-sidebar="app"][data-collapsed="true"] nav button > svg {
            display: none;
        }

        aside[data-sidebar="app"][data-collapsed="true"] nav .border-l {
            display: none;
        }

        aside[data-sidebar="app"][data-collapsed="true"] .sidebar-logo-img {
            width: 44px;
            margin-left: 0.75rem;
            margin-right: 0.75rem;
        }

        aside[data-sidebar="app"][data-collapsed="true"] .sidebar-user-info {
            display: none;
        }
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

            window.mailpurseMetaTrackCustom = function (eventName, payload) {
                if (typeof window.fbq !== 'function') {
                    return;
                }
                window.fbq('track', eventName, payload || {});
            };

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
 @php
     $disableMainScroll = request()->routeIs('customer.ai-tools.email-text-generator');
     $appLogo = null;
 @endphp
 <body class="customer-theme font-sans antialiased bg-admin-main text-admin-text-primary {{ $disableMainScroll ? 'lg:h-screen lg:overflow-hidden' : '' }}" style="--app-font-family: '{{ $fontFamily }}', sans-serif; font-family: var(--app-font-family);">
     <div
         class="flex {{ $disableMainScroll ? 'lg:h-screen lg:overflow-hidden' : 'min-h-screen' }}"
         x-data="{
             sidebarOpen: false,
             sidebarCollapsed: (localStorage.getItem('customerSidebarCollapsed') === 'true'),
             toggleSidebarCollapsed() {
                 this.sidebarCollapsed = !this.sidebarCollapsed;
                 localStorage.setItem('customerSidebarCollapsed', this.sidebarCollapsed ? 'true' : 'false');
             }
         }"
         :class="sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-64'"
     >
        <div x-cloak x-show="sidebarOpen" class="fixed inset-0 bg-black/50 z-30 lg:hidden" @click="sidebarOpen = false"></div>
        <!-- Sidebar -->
        <aside
            data-sidebar="app"
            class="bg-white dark:bg-admin-sidebar fixed inset-y-0 left-0 z-40 h-screen w-64 border-r border-gray-100 dark:border-admin-border flex flex-col transform -translate-x-full lg:translate-x-0 transition-all duration-200"
            :data-collapsed="sidebarCollapsed ? 'true' : 'false'"
            :class="[(sidebarOpen ? 'translate-x-0' : ''), (sidebarCollapsed ? 'lg:w-20' : 'lg:w-64')]"
        >
             <div class="h-full">
                 <div class="flex flex-col items-start justify-between p-4 relative h-full">
                     <div class="flex flex-col gap-8 items-start relative w-full flex-1 min-h-0">
                         <!-- Logo -->
                         <div class="flex items-center justify-between w-full">
                           <a href="{{ route('customer.dashboard') }}" class="relative shrink-0">
                               @php
                                    $appLogo = null;

                                    $appLogoDark = null;
                                    $brandingDisk = (string) config('filesystems.branding_disk', 'public');

                                    try {
                                        $appLogo = \App\Models\Setting::get('app_logo');
                                        $appLogoDark = \App\Models\Setting::get('app_logo_dark');
                                    } catch (\Throwable $e) {
                                        $appLogo = null;
                                        $appLogoDark = null;
                                    }
                                @endphp
    
                                @if(isset($appLogo) && is_string($appLogo) && trim($appLogo) !== '')
                                    <img
                                        src="{{ $brandingDisk === 'public' ? \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($appLogo, '/')) : \Illuminate\Support\Facades\Storage::disk($brandingDisk)->url($appLogo) }}"
                                        alt="{{ __('App Logo') }}"
                                        class="sidebar-logo-img block dark:hidden h-auto object-contain w-[150px] mx-3 mt-3"
                                    />

                                    @if(isset($appLogoDark) && is_string($appLogoDark) && trim($appLogoDark) !== '')
                                        <img
                                            src="{{ $brandingDisk === 'public' ? \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($appLogoDark, '/')) : \Illuminate\Support\Facades\Storage::disk($brandingDisk)->url($appLogoDark) }}"
                                            alt="{{ __('App Logo') }}"
                                            class="sidebar-logo-img hidden dark:block h-auto object-contain w-[150px] mx-3 mt-3"
                                        />
                                    @endif
                                @else
                                    <span class="block text-xl font-bold text-admin-text-primary px-3 py-2">
                                        {{ config('app.name', 'MailZen') }}
                                    </span>
                                @endif
                            </a>

                             <button
                                 type="button"
                                 class="lg:hidden p-2 rounded-md text-admin-text-secondary hover:text-admin-text-primary hover:bg-white/5"
                                 @click="sidebarOpen = false"
                                 aria-label="{{ __('Close sidebar') }}"
                             >
                                 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                 </svg>
                             </button>
                         </div>
    
                        <!-- Navigation -->
                        <nav class="flex flex-col gap-6 items-start relative w-full flex-1 min-h-0 overflow-y-auto">
                            <div class="flex flex-col gap-3 items-start relative shrink-0 w-full">
                                <p class="font-normal leading-[18px] relative shrink-0 text-[#a8a8a8] dark:text-admin-text-secondary text-xs tracking-[-0.36px] ml-3">{{ __('General') }}</p>
                                <div class="flex flex-col items-start relative shrink-0 w-full">
                                    {{-- ADD DASHBOARD PAGE HERE --}}
                                    <a href="{{ route('customer.dashboard') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('customer.dashboard') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                        <div class="relative shrink-0 w-[18px] h-[18px]">
                                            <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                                <path d="M7.3125 2.25H4.3125C3.78916 2.25 3.5275 2.25 3.31457 2.31459C2.83517 2.46001 2.46001 2.83517 2.31459 3.31457C2.25 3.5275 2.25 3.78917 2.25 4.3125C2.25 4.83584 2.25 5.0975 2.31459 5.31043C2.46001 5.78983 2.83517 6.16499 3.31457 6.31041C3.5275 6.375 3.78916 6.375 4.3125 6.375H7.3125C7.83585 6.375 8.09752 6.375 8.31045 6.31041C8.78985 6.16499 9.165 5.78983 9.31042 5.31043C9.375 5.0975 9.375 4.83584 9.375 4.3125C9.375 3.78917 9.375 3.5275 9.31042 3.31457C9.165 2.83517 8.78985 2.46001 8.31045 2.31459C8.09752 2.25 7.83585 2.25 7.3125 2.25Z" stroke="currentColor" stroke-linejoin="round" stroke-width="1.25" />
                                                <path d="M15.75 7.3125V4.3125C15.75 3.78916 15.75 3.5275 15.6854 3.31457C15.54 2.83517 15.1649 2.46001 14.6855 2.31459C14.4725 2.25 14.2109 2.25 13.6875 2.25C13.1642 2.25 12.9025 2.25 12.6896 2.31459C12.2102 2.46001 11.835 2.83517 11.6896 3.31457C11.625 3.5275 11.625 3.78916 11.625 4.3125V7.3125C11.625 7.83585 11.625 8.09752 11.6896 8.31045C11.835 8.78985 12.2102 9.165 12.6896 9.31042C12.9025 9.375 13.1642 9.375 13.6875 9.375C14.2109 9.375 14.4725 9.375 14.6855 9.31042C15.1649 9.165 15.54 8.78985 15.6854 8.31045C15.75 8.09752 15.75 7.83585 15.75 7.3125Z" stroke="currentColor" stroke-linejoin="round" stroke-width="1.25" />
                                                <path d="M12.6896 15.6854C12.9025 15.75 13.1642 15.75 13.6875 15.75C14.2109 15.75 14.4725 15.75 14.6855 15.6854C15.1649 15.54 15.54 15.1649 15.6854 14.6855C15.75 14.4725 15.75 14.2109 15.75 13.6875C15.75 13.1642 15.75 12.9025 15.6854 12.6896C15.54 12.2102 15.1649 11.835 14.6855 11.6896C14.4725 11.625 14.2109 11.625 13.6875 11.625C13.1642 11.625 12.9025 11.625 12.6896 11.6896C12.2102 11.835 11.835 12.2102 11.6896 12.6896C11.625 12.9025 11.625 13.1642 11.625 13.6875C11.625 14.2109 11.625 14.4725 11.6896 14.6855C11.835 15.1649 12.2102 15.54 12.6896 15.6854Z" stroke="currentColor" stroke-linejoin="round" stroke-width="1.25" />
                                                <path d="M6.375 8.625H5.25C3.83578 8.625 3.12868 8.625 2.68934 9.06435C2.25 9.5037 2.25 10.2108 2.25 11.625V12.75C2.25 14.1642 2.25 14.8713 2.68934 15.3106C3.12868 15.75 3.83578 15.75 5.25 15.75H6.375C7.7892 15.75 8.4963 15.75 8.93565 15.3106C9.375 14.8713 9.375 14.1642 9.375 12.75V11.625C9.375 10.2108 9.375 9.5037 8.93565 9.06435C8.4963 8.625 7.7892 8.625 6.375 8.625Z" stroke="currentColor" stroke-linejoin="round" stroke-width="1.25" />
                                            </svg>
                                        </div> 
                                        <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Dashboard') }}</p>
                                    </a>                                    
                                    

                                    <a href="{{ route('customer.analytics.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('customer.analytics.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                        <div class="relative shrink-0 w-[18px] h-[18px]">
                                            <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                                <path d="M2.25 15.75V6.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.25" />
                                                <path d="M6.75 15.75V2.25" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.25" />
                                                <path d="M11.25 15.75V9" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.25" />
                                                <path d="M15.75 15.75V4.5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.25" />
                                            </svg>
                                        </div>
                                        <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Analytics') }}</p>
                                    </a>

                                    <div x-data="{ open: {{ request()->routeIs('customer.templates.*') ? 'true' : 'false' }} }" class="w-full">
                                        <button type="button" @click="open = !open" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('customer.templates.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                            <div class="relative shrink-0 w-[18px] h-[18px]">
                                                <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                                    <path d="M1.5 14.25L6.68477 10.7179C8.57903 9.42737 9.42097 9.42737 11.3152 10.7179L16.5 14.25" stroke="currentColor" stroke-linejoin="round" stroke-width="1.125" />
                                                    <path d="M1.51194 10.9132C1.56139 13.1882 1.58613 14.3257 2.43458 15.1681C3.28303 16.0105 4.46387 16.0396 6.82554 16.0981C8.27947 16.134 9.72052 16.134 11.1745 16.0981C13.5361 16.0396 14.7169 16.0105 15.5654 15.1681C16.4139 14.3257 16.4386 13.1882 16.4881 10.9132C16.5123 9.79867 16.4996 8.69505 16.45 7.56908C16.4193 6.86973 16.4039 6.52006 16.2265 6.20988C16.0492 5.89971 15.7435 5.69951 15.132 5.29912L12.3114 3.45214C10.7056 2.40072 9.90285 1.875 9 1.875C8.09715 1.875 7.29433 2.40071 5.68862 3.45214L2.86798 5.29912C2.25652 5.69951 1.95079 5.89971 1.77344 6.20988C1.59609 6.52006 1.5807 6.86974 1.54992 7.56908C1.50037 8.69505 1.48771 9.79867 1.51194 10.9132Z" stroke="currentColor" stroke-linejoin="round" stroke-width="1.125" />
                                                    <path d="M16.5 7.125L13.301 9.4554C12.5253 10.0204 11.8878 10.5 10.875 10.5M1.5 7.125L4.69903 9.4554C5.47466 10.0204 6.11221 10.5 7.125 10.5" stroke="currentColor" stroke-linejoin="round" stroke-width="1.125" />
                                                </svg>
                                            </div>
                                            <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px] flex-1 text-left">{{ __('Templates') }}</p>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" :class="open ? 'rotate-180' : ''">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        <div x-cloak x-show="open" class="mt-1 ml-6 flex flex-col gap-1 border-l border-gray-200 dark:border-white/10 pl-4">
                                            <a href="{{ route('customer.templates.index') }}" class="flex items-center w-full py-2 text-sm {{ (request()->routeIs('customer.templates.*') && (request()->query('type') === null || request()->query('type') === 'email')) ? 'text-[#1E5FEA]' : 'text-gray-500 dark:text-admin-text-secondary' }} hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                                {{ __('Email templates') }}
                                            </a>
                                            <a href="{{ route('customer.templates.index', ['type' => 'footer']) }}" class="flex items-center w-full py-2 text-sm {{ (request()->routeIs('customer.templates.*') && request()->query('type') === 'footer') ? 'text-[#1E5FEA]' : 'text-gray-500 dark:text-admin-text-secondary' }} hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                                {{ __('Footer templates') }}
                                            </a>
                                            <a href="{{ route('customer.templates.index', ['type' => 'signature']) }}" class="flex items-center w-full py-2 text-sm {{ (request()->routeIs('customer.templates.*') && request()->query('type') === 'signature') ? 'text-[#1E5FEA]' : 'text-gray-500 dark:text-admin-text-secondary' }} hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                                {{ __('Signature') }}
                                            </a>
                                        </div>
                                    </div>

                                    @customercan('api.permissions.can_access_api')
                                        <a href="{{ route('customer.api.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('customer.api.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                            <div class="relative shrink-0 w-[18px] h-[18px]">
                                                <svg class="block w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                </svg>
                                            </div>
                                            <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('API') }}</p>
                                        </a>
                                    @endcustomercan

                                    @customercan('ai_tools.permissions.can_access_ai_tools')
                                        <a href="{{ route('customer.ai-tools.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('customer.ai-tools.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                            <div class="relative shrink-0 w-[18px] h-[18px]">
                                                <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                                    <path d="M9 1.5C5.27208 1.5 2.25 4.52208 2.25 8.25C2.25 11.9779 5.27208 15 9 15C12.7279 15 15.75 11.9779 15.75 8.25C15.75 4.52208 12.7279 1.5 9 1.5Z" stroke="currentColor" stroke-width="1.125" />
                                                    <path d="M6.75 8.25H11.25" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                                    <path d="M9 6V10.5" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                                </svg>
                                            </div>
                                            <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('AI Tools') }}</p>
                                        </a>
                                    @endcustomercan

                                    @customercan('servers.permissions.can_access_delivery_servers')
                                        <a href="{{ route('customer.integrations.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('customer.integrations.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                            <div class="relative shrink-0 w-[18px] h-[18px]">
                                                <svg class="block w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0l-4 4H8l-4-4m16 0H4" />
                                                </svg>
                                            </div>
                                            <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Integrations') }}</p>
                                        </a>
                                    @endcustomercan
                                </div>
                            </div>
    
                            <div class="flex flex-col gap-3 items-start relative shrink-0 w-full">
                                <p class="font-normal leading-[18px] relative shrink-0 text-[#a8a8a8] dark:text-admin-text-secondary text-xs tracking-[-0.36px] ml-3">{{ __('Marketing') }}</p>
                                <div class="flex flex-col items-start relative shrink-0 w-full">
                                    <a href="{{ route('customer.campaigns.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('customer.campaigns.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                        <div class="relative shrink-0 w-[18px] h-[18px]">
                                            <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                                <path d="M11.1947 2.18327L6.20514 4.57839C5.82113 4.76272 5.41082 4.8089 4.99256 4.7152C4.71883 4.65388 4.58194 4.62322 4.47172 4.61063C3.10307 4.45434 2.25 5.53756 2.25 6.7832V7.4668C2.25 8.71245 3.10307 9.79567 4.47172 9.63937C4.58194 9.62677 4.71884 9.5961 4.99256 9.53482C5.41082 9.44107 5.82113 9.48727 6.20514 9.67162L11.1947 12.0667C12.3401 12.6166 12.9127 12.8914 13.5513 12.6772C14.1898 12.4629 14.4089 12.0031 14.8473 11.0835C16.0509 8.5584 16.0509 5.69164 14.8473 3.16647C14.4089 2.24689 14.1898 1.78711 13.5513 1.57282C12.9127 1.35855 12.3401 1.63345 11.1947 2.18327Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                                <path d="M8.59358 15.5782L7.47506 16.5C4.95387 14.5004 5.26188 13.5469 5.26188 9.75H6.11225C6.45734 11.8957 7.27134 12.912 8.39453 13.6478C9.0864 14.1009 9.22905 15.0544 8.59358 15.5782Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                                <path d="M5.625 9.375V4.875" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                            </svg>
                                        </div>
                                        <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Campaigns') }}</p>
                                    </a>

                                    <a href="{{ route('customer.auto-responders.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('customer.auto-responders.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                        <div class="relative shrink-0 w-[18px] h-[18px]">
                                            <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                                <path d="M2.25 4.5C2.25 3.67157 2.92157 3 3.75 3H14.25C15.0784 3 15.75 3.67157 15.75 4.5V13.5C15.75 14.3284 15.0784 15 14.25 15H3.75C2.92157 15 2.25 14.3284 2.25 13.5V4.5Z" stroke="currentColor" stroke-width="1.125" />
                                                <path d="M4.5 6H13.5" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                                <path d="M4.5 9H12" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                                <path d="M4.5 12H10.5" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                            </svg>
                                        </div>
                                        <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Auto Responders') }}</p>
                                    </a>

                                    @customercan('automations.enabled')
                                        <a href="{{ route('customer.automations.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('customer.automations.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                            <div class="relative shrink-0 w-[18px] h-[18px]">
                                                <svg class="block w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                </svg>
                                            </div>
                                            <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Automation') }}</p>
                                        </a>
                                    @endcustomercan
    
                                    @php
                                        $listsTab = (string) request()->input('tab', 'lists');
                                        $listsMenuOpen = request()->routeIs('customer.lists.*') || request()->routeIs('customer.forms.*');
                                        $listsTabIsOverview = request()->routeIs('customer.lists.index') && $listsTab === 'overview';
                                        $listsTabIsLists = request()->routeIs('customer.lists.*') && in_array($listsTab, ['', 'lists'], true);
                                        $listsTabIsSegments = request()->routeIs('customer.lists.index') && $listsTab === 'segments';
                                        $listsTabIsTags = request()->routeIs('customer.lists.index') && $listsTab === 'tags';
                                        $listsTabIsForms = request()->routeIs('customer.forms.*') || (request()->routeIs('customer.lists.index') && $listsTab === 'forms');
                                    @endphp

                                    <div x-data="{ open: {{ $listsMenuOpen ? 'true' : 'false' }} }" class="w-full">
                                        <button type="button" @click="open = !open" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ $listsMenuOpen ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                            <div class="relative shrink-0 w-[18px] h-[18px]">
                                                <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                                    <path d="M1.5 14.25L6.68477 10.7179C8.57903 9.42737 9.42097 9.42737 11.3152 10.7179L16.5 14.25" stroke="currentColor" stroke-linejoin="round" stroke-width="1.125" />
                                                    <path d="M1.51194 10.9132C1.56139 13.1882 1.58613 14.3257 2.43458 15.1681C3.28303 16.0105 4.46387 16.0396 6.82554 16.0981C8.27947 16.134 9.72052 16.134 11.1745 16.0981C13.5361 16.0396 14.7169 16.0105 15.5654 15.1681C16.4139 14.3257 16.4386 13.1882 16.4881 10.9132C16.5123 9.79867 16.4996 8.69505 16.45 7.56908C16.4193 6.86973 16.4039 6.52006 16.2265 6.20988C16.0492 5.89971 15.7435 5.69951 15.132 5.29912L12.3114 3.45214C10.7056 2.40072 9.90285 1.875 9 1.875C8.09715 1.875 7.29433 2.40071 5.68862 3.45214L2.86798 5.29912C2.25652 5.69951 1.95079 5.89971 1.77344 6.20988C1.59609 6.52006 1.5807 6.86974 1.54992 7.56908C1.50037 8.69505 1.48771 9.79867 1.51194 10.9132Z" stroke="currentColor" stroke-linejoin="round" stroke-width="1.125" />
                                                    <path d="M16.5 7.125L13.301 9.4554C12.5253 10.0204 11.8878 10.5 10.875 10.5M1.5 7.125L4.69903 9.4554C5.47466 10.0204 6.11221 10.5 7.125 10.5" stroke="currentColor" stroke-linejoin="round" stroke-width="1.125" />
                                                </svg>
                                            </div>
                                            <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px] flex-1 text-left">{{ __('Lists') }}</p>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" :class="open ? 'rotate-180' : ''">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        <div x-cloak x-show="open" class="mt-1 ml-6 flex flex-col gap-1 border-l border-gray-200 dark:border-white/10 pl-4">
                                            <a href="{{ route('customer.lists.index', ['tab' => 'overview']) }}" class="flex items-center w-full py-2 text-sm {{ $listsTabIsOverview ? 'text-[#1E5FEA]' : 'text-gray-500 dark:text-admin-text-secondary' }} hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                                {{ __('Overview') }}
                                            </a>
                                            <a href="{{ route('customer.lists.index') }}" class="flex items-center w-full py-2 text-sm {{ $listsTabIsLists ? 'text-[#1E5FEA]' : 'text-gray-500 dark:text-admin-text-secondary' }} hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                                {{ __('Lists') }}
                                            </a>
                                            <a href="{{ route('customer.lists.index', ['tab' => 'segments']) }}" class="flex items-center w-full py-2 text-sm {{ $listsTabIsSegments ? 'text-[#1E5FEA]' : 'text-gray-500 dark:text-admin-text-secondary' }} hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                                {{ __('Segments') }}
                                            </a>
                                            <a href="{{ route('customer.lists.index', ['tab' => 'tags']) }}" class="flex items-center w-full py-2 text-sm {{ $listsTabIsTags ? 'text-[#1E5FEA]' : 'text-gray-500 dark:text-admin-text-secondary' }} hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                                {{ __('Tags') }}
                                            </a>
                                            <a href="{{ route('customer.forms.index') }}" class="flex items-center w-full py-2 text-sm {{ $listsTabIsForms ? 'text-[#1E5FEA]' : 'text-gray-500 dark:text-admin-text-secondary' }} hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                                {{ __('Forms & Popups') }}
                                            </a>
                                        </div>
                                    </div>

                                    <a href="{{ route('customer.email-validation.runs.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('customer.email-validation.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                        <div class="relative shrink-0 w-[18px] h-[18px]">
                                            <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                                <path d="M2.25 4.5C2.25 3.67157 2.92157 3 3.75 3H14.25C15.0784 3 15.75 3.67157 15.75 4.5V13.5C15.75 14.3284 15.0784 15 14.25 15H3.75C2.92157 15 2.25 14.3284 2.25 13.5V4.5Z" stroke="currentColor" stroke-width="1.125" />
                                                <path d="M4.5 6H13.5" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                                <path d="M4.5 9H10.5" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                                <path d="M4.5 12H9" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                                <path d="M12.75 9.75L13.5 10.5L15 9" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                            </svg>
                                        </div>
                                        <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Email Validation') }}</p>
                                    </a>
                                </div>
                            </div>
    
                            <div class="flex flex-col gap-3 items-start relative shrink-0 w-full">
                                <p class="font-normal leading-[18px] relative shrink-0 text-[#a8a8a8] dark:text-admin-text-secondary text-xs tracking-[-0.36px] ml-3">{{ __('Delivery') }}</p>
                                <div class="flex flex-col items-start relative shrink-0 w-full">
                                    <a href="{{ route('customer.delivery-servers.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('customer.delivery-servers.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                        <div class="relative shrink-0 w-[18px] h-[18px]">
                                            <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                                <path d="M3 10.5H4.79612C5.01673 10.5 5.23431 10.5497 5.43163 10.6452L6.96311 11.3862C7.16043 11.4817 7.37801 11.5313 7.59863 11.5313H8.38057C9.13687 11.5313 9.75 12.1247 9.75 12.8565C9.75 12.8861 9.72975 12.9121 9.70035 12.9202L7.79468 13.4471C7.4528 13.5416 7.08675 13.5087 6.76875 13.3548L5.13158 12.5627" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                                <path d="M9.75 12.375L13.1946 11.3167C13.8053 11.1264 14.4653 11.352 14.8478 11.8817C15.1244 12.2647 15.0118 12.8132 14.6089 13.0457L8.97218 16.2979C8.61368 16.5047 8.19067 16.5552 7.7964 16.4382L3 15.0149" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                                <path d="M11.25 9H9.75C8.3358 9 7.6287 9 7.18934 8.56065C6.75 8.1213 6.75 7.41421 6.75 6V4.5C6.75 3.08579 6.75 2.37868 7.18934 1.93934C7.6287 1.5 8.3358 1.5 9.75 1.5H11.25C12.6642 1.5 13.3713 1.5 13.8106 1.93934C14.25 2.37868 14.25 3.08579 14.25 4.5V6C14.25 7.41421 14.25 8.1213 13.8106 8.56065C13.3713 9 12.6642 9 11.25 9Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                                <path d="M9.75 3.75H11.25" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                            </svg>
                                        </div>
                                        <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Delivery Servers') }}</p>
                                    </a>

                                    <a href="{{ route('customer.warmups.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('customer.warmups.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                        <div class="relative shrink-0 w-[18px] h-[18px]">
                                            <svg class="block w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z" />
                                            </svg>
                                        </div>
                                        <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Email Warmups') }}</p>
                                    </a>
    
                                    @customercan('servers.permissions.can_access_bounce_servers')
                                    <a href="{{ route('customer.bounce-servers.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('customer.bounce-servers.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                        <div class="relative shrink-0 w-[18px] h-[18px]">
                                            <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                                <path d="M1.5 7.5C1.5 5.37868 1.5 4.31802 2.15901 3.65901C2.81802 3 3.87868 3 6 3H12C14.1213 3 15.1819 3 15.841 3.65901C16.5 4.31802 16.5 5.37868 16.5 7.5V10.5C16.5 12.6213 16.5 13.6819 15.841 14.341C15.1819 15 14.1213 15 12 15H6C3.87868 15 2.81802 15 2.15901 14.341C1.5 13.6819 1.5 12.6213 1.5 10.5V7.5Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                                <path d="M4.5 7.42822C4.5 4.01991 9 7.49261 9 9.75H6.375C5.0724 9.75 4.5 8.60242 4.5 7.42822Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                                <path d="M13.5 7.42822C13.5 4.01991 9 7.49261 9 9.75H11.625C12.9276 9.75 13.5 8.60242 13.5 7.42822Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                                <path d="M9 3V15" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                                <path d="M1.5 9.75H16.5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                                <path d="M11.25 12L9 9.75L6.75 12" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                            </svg>
                                        </div>
                                        <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Bounce Servers') }}</p>
                                    </a>
                                    @endcustomercan

                                    @customercan('servers.permissions.can_access_reply_servers')
                                    <a href="{{ route('customer.reply-servers.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('customer.reply-servers.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                        <div class="relative shrink-0 w-[18px] h-[18px]">
                                            <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                                <path d="M1.5 7.5C1.5 5.37868 1.5 4.31802 2.15901 3.65901C2.81802 3 3.87868 3 6 3H12C14.1213 3 15.1819 3 15.841 3.65901C16.5 4.31802 16.5 5.37868 16.5 7.5V10.5C16.5 12.6213 16.5 13.6819 15.841 14.341C15.1819 15 14.1213 15 12 15H6C3.87868 15 2.81802 15 2.15901 14.341C1.5 13.6819 1.5 12.6213 1.5 10.5V7.5Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                                <path d="M4.5 7.42822C4.5 4.01991 9 7.49261 9 9.75H6.375C5.0724 9.75 4.5 8.60242 4.5 7.42822Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                                <path d="M13.5 7.42822C13.5 4.01991 9 7.49261 9 9.75H11.625C12.9276 9.75 13.5 8.60242 13.5 7.42822Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                                <path d="M9 3V15" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                                <path d="M1.5 9.75H16.5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                                <path d="M11.25 12L9 9.75L6.75 12" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                            </svg>
                                        </div>
                                        <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Reply Servers') }}</p>
                                    </a>
                                    @endcustomercan
    
                                    <a href="{{ route('customer.bounced-emails.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('customer.bounced-emails.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                        <div class="relative shrink-0 w-[18px] h-[18px]">
                                            <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                                <path d="M1.5 4.125L6.68477 7.06847C8.57903 8.14385 9.42097 8.14385 11.3152 7.06847L16.5 4.125" stroke="currentColor" stroke-linejoin="round" stroke-width="1.125" />
                                                <path d="M8.625 14.9969C8.27527 14.9923 7.17512 15.0058 6.82412 14.9969C4.46275 14.9374 3.28206 14.9077 2.43372 14.0545C1.58537 13.2013 1.56086 12.0496 1.51183 9.74602C1.49606 9.00532 1.49605 8.26905 1.51182 7.52835C1.56085 5.22481 1.58537 4.07304 2.43371 3.21984C3.28206 2.36663 4.46275 2.33691 6.82411 2.27747C8.27947 2.24084 9.72053 2.24084 11.1759 2.27748C13.5373 2.33692 14.7179 2.36665 15.5662 3.21985C16.4146 4.07305 16.4392 5.22482 16.4881 7.52835C16.4987 8.02297 16.5022 7.76565 16.4986 8.259" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                                <path d="M11.2613 10.79C12.0657 10.3102 12.7679 10.5035 13.1897 10.8115C13.3627 10.9378 13.4492 11.0009 13.5 11.0009C13.5509 11.0009 13.6373 10.9378 13.8103 10.8115C14.2322 10.5035 14.9343 10.3102 15.7388 10.79C16.7946 11.4196 17.0335 13.4969 14.5982 15.2493C14.1343 15.5831 13.9024 15.75 13.5 15.75C13.0976 15.75 12.8657 15.5831 12.4019 15.2493C9.96653 13.4969 10.2054 11.4196 11.2613 10.79Z" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                            </svg>
                                        </div>
                                        <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Bounced Emails') }}</p>
                                    </a>
                                </div>
                            </div>
    
                            <div class="flex flex-col gap-3 items-start relative shrink-0 w-full">
                                <p class="font-normal leading-[18px] relative shrink-0 text-[#a8a8a8] dark:text-admin-text-secondary text-xs tracking-[-0.36px] ml-3">{{ __('Domains') }}</p>
                                <div class="flex flex-col items-start relative shrink-0 w-full">
                                    <a href="{{ route('customer.tracking-domains.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('customer.tracking-domains.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                        <div class="relative shrink-0 w-[18px] h-[18px]">
                                            <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                                <path d="M8.99997 1.5C5.27205 1.5 2.24997 4.52208 2.24997 8.25C2.24997 11.9779 5.27205 15 8.99997 15C12.7279 15 15.75 11.9779 15.75 8.25C15.75 4.52208 12.7279 1.5 8.99997 1.5Z" stroke="currentColor" stroke-width="1.125" />
                                                <path d="M9 5.25V8.25L11.25 9.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                            </svg>
                                        </div>
                                        <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Tracking Domains') }}</p>
                                    </a>
    
                                    <a href="{{ route('customer.sending-domains.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('customer.sending-domains.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                        <div class="relative shrink-0 w-[18px] h-[18px]">
                                            <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                                <path d="M9 16.125V5.25M11.25 14.25C10.8076 14.7051 9.63023 16.5 9 16.5C8.36977 16.5 7.19238 14.7051 6.75 14.25" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                                <path d="M15.1745 8.625C16.0582 9.0465 16.5 9.33038 16.5 9.75008C16.5 10.2701 15.8219 10.5815 14.4655 11.2046L11.9176 12.375M2.82545 8.625C1.94182 9.0465 1.5 9.33038 1.5 9.75008C1.5 10.2701 2.17817 10.5815 3.53452 11.2046L6.08243 12.375" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                                <path d="M6.08259 7.875L3.53452 6.70452C2.17817 6.08147 1.5 5.76995 1.5 5.25C1.5 4.73005 2.17817 4.41853 3.53452 3.79548L7.2043 2.10973C8.0892 1.70324 8.53163 1.5 9 1.5C9.46837 1.5 9.9108 1.70324 10.7957 2.10973L14.4655 3.79548C15.8219 4.41853 16.5 4.73005 16.5 5.25C16.5 5.76995 15.8219 6.08147 14.4655 6.70453L11.9174 7.875" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                            </svg>
                                        </div>
                                        <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Sending Domains') }}</p>
                                    </a>
                                </div>
                            </div>
    
                            <div class="flex flex-col gap-3 items-start relative shrink-0 w-full">
                                <p class="font-normal leading-[18px] relative shrink-0 text-[#a8a8a8] dark:text-admin-text-secondary text-xs tracking-[-0.36px] ml-3">{{ __('Settings') }}</p>
                                <div class="flex flex-col items-start relative shrink-0 w-full">
                                    <a href="{{ route('customer.settings.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('customer.settings.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                        <div class="relative shrink-0 w-[18px] h-[18px]">
                                            <svg class="block w-full h-full" fill="none" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z" />
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.4 15a1.9 1.9 0 0 0 .38 2.09l.07.07a2.3 2.3 0 0 1 0 3.25 2.3 2.3 0 0 1-3.25 0l-.07-.07a1.9 1.9 0 0 0-2.09-.38 1.9 1.9 0 0 0-1.15 1.73V22a2.3 2.3 0 0 1-4.6 0v-.1a1.9 1.9 0 0 0-1.15-1.73 1.9 1.9 0 0 0-2.09.38l-.07.07a2.3 2.3 0 0 1-3.25 0 2.3 2.3 0 0 1 0-3.25l.07-.07A1.9 1.9 0 0 0 4.6 15a1.9 1.9 0 0 0-1.73-1.15H2.8a2.3 2.3 0 0 1 0-4.6h.1A1.9 1.9 0 0 0 4.6 7.4a1.9 1.9 0 0 0-.38-2.09l-.07-.07a2.3 2.3 0 0 1 0-3.25 2.3 2.3 0 0 1 3.25 0l.07.07a1.9 1.9 0 0 0 2.09.38A1.9 1.9 0 0 0 10.77.7V.6a2.3 2.3 0 0 1 4.6 0v.1a1.9 1.9 0 0 0 1.15 1.73 1.9 1.9 0 0 0 2.09-.38l.07-.07a2.3 2.3 0 0 1 3.25 0 2.3 2.3 0 0 1 0 3.25l-.07.07A1.9 1.9 0 0 0 19.4 7.4c.2.5.28 1.04.24 1.58a1.9 1.9 0 0 0 1.15 1.73H22a2.3 2.3 0 0 1 0 4.6h-.1a1.9 1.9 0 0 0-1.73 1.15Z" />
                                            </svg>
                                        </div>
                                        <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Settings') }}</p>
                                    </a>

                                    @customercan('support.permissions.can_access_support')
                                        <a href="{{ route('customer.support-tickets.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('customer.support-tickets.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                            <div class="relative shrink-0 w-[18px] h-[18px]">
                                                <svg class="block w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a10.94 10.94 0 01-4-.73L3 20l1.46-3.65A7.92 7.92 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                                </svg>
                                            </div>
                                            <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Support') }}</p>
                                        </a>
                                    @endcustomercan
    
                                    <a href="{{ route('customer.billing.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('customer.billing.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                        <div class="relative shrink-0 w-[18px] h-[18px]">
                                            <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                                <path d="M9.66068 5.26242L13.2403 6.21619M8.89335 8.11005L10.6831 8.5869M8.98237 13.4748L9.69832 13.6656C11.7232 14.2051 12.7358 14.4749 13.5334 14.017C14.331 13.559 14.6023 12.5522 15.1449 10.5387L15.9122 7.6911C16.4548 5.67754 16.7261 4.67076 16.2656 3.87762C15.8051 3.08448 14.7926 2.81471 12.7676 2.27518L12.0517 2.08443C10.0267 1.54489 9.01425 1.27513 8.21662 1.73305C7.41897 2.19097 7.14767 3.19775 6.60508 5.21131L5.83774 8.0589C5.29515 10.0724 5.02386 11.0792 5.48437 11.8723C5.94489 12.6655 6.95738 12.9353 8.98237 13.4748Z" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                                <path d="M9 15.7096L8.28578 15.9041C6.26552 16.4542 5.25542 16.7293 4.45964 16.2624C3.66388 15.7955 3.39322 14.769 2.8519 12.7159L2.08637 9.81246C1.54505 7.75941 1.27439 6.73287 1.73383 5.92418C2.13125 5.22463 3 5.2501 4.125 5.25001" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                            </svg>
                                        </div>
                                        <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Billing') }}</p>
                                    </a>

                                    <a href="{{ route('customer.affiliate.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('customer.affiliate.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                        <div class="relative shrink-0 w-[18px] h-[18px]">
                                            <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                                <path d="M3.75 15.75V3.75C3.75 2.92157 4.42157 2.25 5.25 2.25H12.75C13.5784 2.25 14.25 2.92157 14.25 3.75V15.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                                <path d="M6 6.75H12" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                                <path d="M6 9.75H12" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                                <path d="M6 12.75H10.5" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                            </svg>
                                        </div>
                                        <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Affiliate') }}</p>
                                    </a>
    
                                    <a href="{{ route('customer.usage.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('customer.usage.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                        <div class="relative shrink-0 w-[18px] h-[18px]">
                                            <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                                <path d="M7.3125 2.25H4.3125C3.78916 2.25 3.5275 2.25 3.31457 2.31459C2.83517 2.46001 2.46001 2.83517 2.31459 3.31457C2.25 3.5275 2.25 3.78917 2.25 4.3125C2.25 4.83584 2.25 5.0975 2.31459 5.31043C2.46001 5.78983 2.83517 6.16499 3.31457 6.31041C3.5275 6.375 3.78916 6.375 4.3125 6.375H7.3125C7.83585 6.375 8.09752 6.375 8.31045 6.31041C8.78985 6.16499 9.165 5.78983 9.31042 5.31043C9.375 5.0975 9.375 4.83584 9.375 4.3125C9.375 3.78917 9.375 3.5275 9.31042 3.31457C9.165 2.83517 8.78985 2.46001 8.31045 2.31459C8.09752 2.25 7.83585 2.25 7.3125 2.25Z" stroke="currentColor" stroke-linejoin="round" stroke-width="1.25" />
                                                <path d="M15.75 7.3125V4.3125C15.75 3.78916 15.75 3.5275 15.6854 3.31457C15.54 2.83517 15.1649 2.46001 14.6855 2.31459C14.4725 2.25 14.2109 2.25 13.6875 2.25C13.1642 2.25 12.9025 2.25 12.6896 2.31459C12.2102 2.46001 11.835 2.83517 11.6896 3.31457C11.625 3.5275 11.625 3.78916 11.625 4.3125V7.3125C11.625 7.83585 11.625 8.09752 11.6896 8.31045C11.835 8.78985 12.2102 9.165 12.6896 9.31042C12.9025 9.375 13.1642 9.375 13.6875 9.375C14.2109 9.375 14.4725 9.375 14.6855 9.31042C15.1649 9.165 15.54 8.78985 15.6854 8.31045C15.75 8.09752 15.75 7.83585 15.75 7.3125Z" stroke="currentColor" stroke-linejoin="round" stroke-width="1.25" />
                                                <path d="M12.6896 15.6854C12.9025 15.75 13.1642 15.75 13.6875 15.75C14.2109 15.75 14.4725 15.75 14.6855 15.6854C15.1649 15.54 15.54 15.1649 15.6854 14.6855C15.75 14.4725 15.75 14.2109 15.75 13.6875C15.75 13.1642 15.75 12.9025 15.6854 12.6896C15.54 12.2102 15.1649 11.835 14.6855 11.6896C14.4725 11.625 14.2109 11.625 13.6875 11.625C13.1642 11.625 12.9025 11.625 12.6896 11.6896C12.2102 11.835 11.835 12.2102 11.6896 12.6896C11.625 12.9025 11.625 13.1642 11.625 13.6875C11.625 14.2109 11.625 14.4725 11.6896 14.6855C11.835 15.1649 12.2102 15.54 12.6896 15.6854Z" stroke="currentColor" stroke-linejoin="round" stroke-width="1.25" />
                                                <path d="M6.375 8.625H5.25C3.83578 8.625 3.12868 8.625 2.68934 9.06435C2.25 9.5037 2.25 10.2108 2.25 11.625V12.75C2.25 14.1642 2.25 14.8713 2.68934 15.3106C3.12868 15.75 3.83578 15.75 5.25 15.75H6.375C7.7892 15.75 8.4963 15.75 8.93565 15.3106C9.375 14.8713 9.375 14.1642 9.375 12.75V11.625C9.375 10.2108 9.375 9.5037 8.93565 9.06435C8.4963 8.625 7.7892 8.625 6.375 8.625Z" stroke="currentColor" stroke-linejoin="round" stroke-width="1.25" />
                                            </svg>
                                        </div>
                                        <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Usage') }}</p>
                                    </a>
                                </div>
                            </div>
                        </nav>
                    </div>
    
                    <!-- User Menu -->
                    <div class="w-full pt-4 border-t border-gray-100 dark:border-admin-border mt-auto shrink-0">
                        <div class="flex items-center">
                            <div class="sidebar-user-info flex-1 min-w-0">
                                <p class="text-sm font-medium text-admin-text-primary truncate">
                                    {{ auth()->guard('customer')->user()->full_name }}
                                </p>
                                <p class="text-xs text-admin-text-secondary truncate">
                                    {{ auth()->guard('customer')->user()->email }}
                                </p>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="ml-2 p-2 text-admin-text-secondary hover:text-admin-text-primary">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main Content -->
            <div class="flex-1 flex flex-col overflow-hidden min-h-0" x-data="{ 
                    profileMenuOpen: false,
                    notificationsOpen: false,
                    languageMenuOpen: false
                }" @click.outside="profileMenuOpen = false; notificationsOpen = false; languageMenuOpen = false">
                <!-- Top Bar -->
                <header class="h-16 bg-admin-main border-b border-admin-border flex items-center justify-between px-4 sm:px-6 gap-4">
                    <!-- Left: title + search -->
                    <div class="flex items-center gap-4 flex-1 min-w-0">
                        <button
                            type="button"
                            class="lg:hidden p-2 rounded-md text-admin-text-secondary hover:text-admin-text-primary hover:bg-white/5 focus:outline-none focus:ring-2 focus:ring-primary-500"
                            @click.stop="sidebarOpen = true"
                            aria-label="Open sidebar"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <button
                            type="button"
                            class="hidden lg:inline-flex p-2 rounded-md text-admin-text-secondary hover:text-admin-text-primary hover:bg-white/5 focus:outline-none focus:ring-2 focus:ring-primary-500"
                            @click="toggleSidebarCollapsed()"
                            :aria-label="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!sidebarCollapsed">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="sidebarCollapsed">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                        <div class="min-w-0">
                            <h1 class="text-xl font-semibold text-admin-text-primary truncate">
                                @yield('page-title', 'Dashboard')
                            </h1>
                        </div>
                        <!-- Search on the right of title -->
                        <div class="hidden lg:block flex-1 max-w-xl" x-data="headerSearch({ suggestUrl: @js(route('customer.search.suggest')), searchUrl: @js(route('customer.search.index')), initialQuery: @js(request('q')), minChars: 1, variant: 'customer' })" @click.outside="close()">
                            <form action="{{ route('customer.search.index') }}" method="GET" class="relative">
                                <span class="absolute inset-y-0 left-3 flex items-center text-admin-text-secondary/80">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                                    </svg>
                                </span>
                                <input
                                    type="search"
                                    name="q"
                                    class="block w-full pl-10 pr-4 py-2 text-sm border border-admin-border rounded-lg bg-white/5 placeholder:text-admin-text-secondary/70 focus:bg-white/10 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-admin-text-primary"
                                    placeholder="Search campaigns, lists, subscribers, templates..."
                                    value="{{ request('q') }}"
                                    x-model="query"
                                    @input="onInput()"
                                    @focus="onFocus()"
                                    @keydown="onKeydown($event)"
                                    autocomplete="off"
                                >

                                <div
                                    x-cloak
                                    x-show="open"
                                    x-transition
                                    class="absolute left-0 right-0 mt-2 rounded-lg shadow-lg ring-1 z-30"
                                    :class="dropdownBgClass"
                                >
                                    <div class="px-3 py-2 text-xs text-admin-text-secondary flex items-center justify-between border-b border-gray-500">
                                        <span x-show="loading">Searching...</span>
                                        <span x-show="!loading" x-text="hasItems ? (items.length + ' results') : 'No results'"></span>
                                        <a :href="searchUrlWithQuery" class="text-xs text-primary-400 hover:text-primary-300" x-show="(query || '').trim().length">View all</a>
                                    </div>
                                    <div class="max-h-80 overflow-auto rounded-lg">
                                        <template x-for="(item, idx) in items" :key="item.type + '-' + item.url">
                                            <button
                                                type="button"
                                                class="w-full text-left px-3 py-2"
                                                :class="[(idx === activeIndex ? 'bg-white/5' : ''), itemHoverClass]"
                                                @mouseenter="activeIndex = idx"
                                                @click="select(item)"
                                            >
                                                <div class="flex items-center justify-between gap-3">
                                                    <p class="text-sm text-admin-text-primary font-medium truncate" x-text="item.label"></p>
                                                    <span class="text-[11px] text-admin-text-secondary whitespace-nowrap" x-text="item.type"></span>
                                                </div>
                                                <p class="mt-0.5 text-xs text-admin-text-secondary truncate" x-text="item.description"></p>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Right actions -->
                    <div class="flex items-center gap-4">
                        <!-- Quota progress -->
                        @php
                            $customer = auth('customer')->user();
                            $quotaUsed = 0;
                            $quotaTotal = null;

                            if ($customer) {
                                $quotaTotal = $customer->groupLimit('sending_quota.monthly_quota');
                                if ($quotaTotal === null) {
                                    $fallbackQuota = (int) ($customer->quota ?? 0);
                                    $quotaTotal = $fallbackQuota > 0 ? $fallbackQuota : null;
                                }

                                $periodStart = now()->copy()->startOfMonth()->toDateString();
                                $periodEnd = now()->copy()->endOfMonth()->toDateString();
                                $quotaUsed = (int) (\App\Models\UsageLog::query()
                                    ->where('customer_id', $customer->id)
                                    ->where('metric', 'emails_sent_this_month')
                                    ->where('period_start', $periodStart)
                                    ->where('period_end', $periodEnd)
                                    ->value('amount') ?? 0);
                            }

                            $quotaUsedPercent = $quotaTotal ? min(100, round(($quotaUsed / $quotaTotal) * 100)) : 0;
                            $quotaRemainingPercent = $quotaTotal ? max(0, 100 - $quotaUsedPercent) : 100;
                        @endphp
                        <a href="{{ route('customer.usage.index') }}" class="hidden md:flex flex-col w-44 group">
                        <div class="flex items-center justify-between text-xs text-admin-text-secondary mb-1">
                            <span class="inline-flex items-center gap-1 group-hover:text-admin-text-primary">
                                Quota
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </span>
                            @if($quotaTotal)
                                <span>{{ number_format($quotaUsed) }} / {{ number_format($quotaTotal) }}</span>
                            @else
                                <span>Unlimited</span>
                            @endif
                        </div>
                        <div class="w-full h-2 rounded-full bg-white/10 overflow-hidden">
                            <div
                                class="h-2 rounded-full @if($quotaUsedPercent >= 90) bg-red-500 @elseif($quotaUsedPercent >= 70) bg-yellow-400 @else bg-primary-500 @endif"
                                style="width: {{ $quotaRemainingPercent }}%;"
                            ></div>
                        </div>
                        </a>
                    <!-- Notifications -->
                    <div class="relative">
                        <button
                            type="button"
                            @click.stop="notificationsOpen = !notificationsOpen; profileMenuOpen = false; languageMenuOpen = false"
                            class="relative p-2 rounded-full text-admin-text-secondary hover:text-admin-text-primary hover:bg-white/5 focus:outline-none focus:ring-2 focus:ring-primary-500"
                            data-notifications-root
                            data-notifications-feed-url="{{ route('customer.notifications.feed') }}"
                            data-notifications-mark-all-read-url="{{ route('customer.notifications.mark-all-read') }}"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            @php
                                $unreadCount = auth('customer')->check()
                                    ? auth('customer')->user()->unreadNotifications()->count()
                                    : 0;
                            @endphp
                            @if($unreadCount > 0)
                                <span class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-red-500 text-white" data-notifications-badge>
                                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                                </span>
                            @endif
                        </button>

                        <!-- Notifications dropdown -->
                        <div
                            x-cloak
                            x-show="notificationsOpen"
                            x-transition
                            class="origin-top-right absolute right-0 mt-2 w-80 rounded-lg shadow-lg bg-admin-sidebar ring-1 ring-admin-border z-20"
                        >
                            <div class="px-4 py-3 border-b border-admin-border flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-admin-text-primary">
                                    Notifications
                                </h3>
                                <div class="flex items-center gap-2">
                                    <button type="button" class="text-xs text-admin-text-secondary hover:text-admin-text-primary" data-notifications-mark-all-read>
                                        Mark all read
                                    </button>
                                    <p class="text-xs text-admin-text-secondary" data-notifications-unread-label>
                                    @if($unreadCount > 0)
                                        {{ $unreadCount }} unread
                                    @else
                                        Up to date
                                    @endif
                                    </p>
                                </div>
                            </div>
                            <div class="max-h-80 overflow-y-auto divide-y divide-admin-border" data-notifications-list>
                                @php
                                    $notifications = auth('customer')->check()
                                        ? auth('customer')->user()->notifications()->latest()->limit(10)->get()
                                        : collect();
                                @endphp

                                @forelse($notifications as $notification)
                                    <div class="px-4 py-3 text-sm {{ $notification->read_at ? 'bg-admin-sidebar' : 'bg-white/5' }}">
                                        <p class="text-admin-text-primary font-medium">
                                            {{ $notification->data['title'] ?? 'Notification' }}
                                        </p>
                                        @if(!empty($notification->data['message']))
                                            <p class="mt-1 text-xs text-admin-text-secondary">
                                                {{ $notification->data['message'] }}
                                            </p>
                                        @endif
                                        <p class="mt-1 text-[11px] text-admin-text-secondary/80">
                                            {{ $notification->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                @empty
                                    <div class="px-4 py-6 text-center text-sm text-admin-text-secondary">
                                        No notifications yet.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    @php
                        $activeLocales = collect(app(\App\Translation\LocaleJsonService::class)->listLocales());
                        $currentLocale = (string) app()->getLocale();
                        $currentLocale = trim($currentLocale) !== '' ? $currentLocale : 'en';
                    @endphp
                    @if($activeLocales->count() > 1)
                        <div class="relative">
                            <button
                                type="button"
                                @click.stop="languageMenuOpen = !languageMenuOpen; profileMenuOpen = false; notificationsOpen = false"
                                class="relative inline-flex items-center justify-center h-9 w-9 rounded-full border focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors bg-white/5 border-admin-border text-admin-text-secondary hover:text-admin-text-primary"
                                aria-label="Change language"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21a9 9 0 100-18 9 9 0 000 18z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.6 9h16.8" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.6 15h16.8" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3a12 12 0 000 18" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3a12 12 0 010 18" />
                                </svg>
                            </button>

                            <div
                                x-cloak
                                x-show="languageMenuOpen"
                                x-transition
                                class="origin-top-right absolute right-0 mt-2 w-44 rounded-lg shadow-lg bg-admin-sidebar ring-1 ring-admin-border z-20 overflow-hidden"
                            >
                                <div class="py-1">
                                    @foreach($activeLocales as $loc)
                                        <form method="POST" action="{{ route('customer.language.update') }}" data-turbo="false">
                                            @csrf
                                            <button
                                                type="submit"
                                                name="locale"
                                                value="{{ $loc->code }}"
                                                class="w-full text-left px-3 py-2 text-sm text-admin-text-primary hover:bg-white/5"
                                            >
                                                <span class="font-medium">{{ strtoupper($loc->code) }}</span>
                                                <span class="text-xs text-admin-text-secondary">{{ $loc->name }}</span>
                                            </button>
                                        </form>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <button
                        type="button"
                        @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)"
                        class="relative inline-flex items-center justify-center h-9 w-9 rounded-full border focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors"
                        :class="darkMode ? 'bg-[#557CFF] border-[#5F86FF] text-white' : 'bg-white/5 border-admin-border text-admin-text-secondary'"
                        aria-label="Toggle dark mode"
                    >
                        <svg x-cloak x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg x-cloak x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>

                    <!-- Profile dropdown -->
                    <div class="relative">
                        <button
                            type="button"
                            @click.stop="profileMenuOpen = !profileMenuOpen; notificationsOpen = false; languageMenuOpen = false"
                            class="flex items-center gap-2 text-admin-text-secondary hover:text-admin-text-primary focus:outline-none"
                        >
                            @php
                                $customer = auth('customer')->user();
                                $avatarUrl = $customer && $customer->avatar_path
                                    ? \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($customer->avatar_path, '/'))
                                    : null;
                            @endphp
                            <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center overflow-hidden">
                                @if($avatarUrl)
                                    <img src="{{ $avatarUrl }}" alt="{{ $customer->full_name }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-xs font-semibold text-admin-text-primary">
                                        {{ strtoupper(Str::substr($customer->first_name, 0, 1) . Str::substr($customer->last_name, 0, 1)) }}
                                    </span>
                                @endif
                            </div>
                            <div class="hidden sm:flex flex-col items-start">
                                <span class="text-sm font-medium text-admin-text-primary max-w-[140px] truncate">
                                    {{ $customer->full_name }}
                                </span>
                                <span class="text-xs text-admin-text-secondary max-w-[140px] truncate">
                                    {{ $customer->email }}
                                </span>
                            </div>
                            <svg class="w-4 h-4 text-admin-text-secondary ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Dropdown menu -->
                        <div
                            x-cloak
                            x-show="profileMenuOpen"
                            x-transition
                            class="origin-top-right absolute right-0 mt-2 w-48 rounded-lg shadow-lg bg-admin-sidebar ring-1 z-20"
                        >
                            <div class="py-1">
                                <a href="{{ route('customer.profile.edit') }}" class="block px-4 py-2 text-sm text-admin-text-primary hover:bg-white/5">
                                    Profile
                                </a>
                                <a href="{{ route('customer.billing.index') }}" class="block px-4 py-2 text-sm text-admin-text-primary hover:bg-white/5">
                                    Account
                                </a>
                                <div class="border-t border-admin-border my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30">
                                        Log out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 flex flex-col min-h-0 overflow-y-auto {{ $disableMainScroll ? 'lg:overflow-y-hidden' : '' }} overflow-x-auto lg:overflow-x-visible p-4 sm:p-6">
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

                <x-app-credit variant="panel" />
            </main>
        </div>
    </div>

    <script>
        (function () {
            const MASK = '********';

            async function fetchSecretValue(url) {
                if (!url) {
                    return '';
                }

                const res = await fetch(url, {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!res.ok) {
                    return '';
                }

                const json = await res.json();
                if (!json || json.success !== true) {
                    return '';
                }

                return typeof json.value === 'string' ? json.value : '';
            }

            function updateEyeIcon(svg, isVisible) {
                if (!svg) {
                    return;
                }

                if (isVisible) {
                    svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />';
                } else {
                    svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
                }
            }

            document.addEventListener('click', function (e) {
                const btn = e.target && e.target.closest ? e.target.closest('[data-toggle-secret]') : null;
                if (!btn) {
                    return;
                }

                const wrapper = btn.closest('[data-secret-wrapper]');
                if (!wrapper) {
                    return;
                }

                const input = wrapper.querySelector('input');
                if (!input) {
                    return;
                }

                const togglingToText = input.type === 'password';
                const nextType = togglingToText ? 'text' : 'password';
                input.type = nextType;

                const changed = input.dataset.changed === '1';
                const secretUrl = input.getAttribute('data-secret-url') || '';

                if (togglingToText && !changed && input.value === MASK && secretUrl) {
                    fetchSecretValue(secretUrl)
                        .then(function (value) {
                            if (value) {
                                input.value = value;
                                input.dataset.revealed = '1';
                            }
                        })
                        .catch(function () {
                            // ignore
                        });
                }

                if (!togglingToText && input.dataset.revealed === '1' && !changed) {
                    input.value = MASK;
                    delete input.dataset.revealed;
                }

                const svg = btn.querySelector('svg');
                updateEyeIcon(svg, nextType === 'text');
            });

            document.addEventListener('DOMContentLoaded', function () {
                const inputs = document.querySelectorAll('input[data-secret-input]');
                inputs.forEach(function (input) {
                    input.dataset.initialValue = input.value;
                    input.addEventListener('input', function () {
                        input.dataset.changed = '1';
                        delete input.dataset.revealed;
                    });
                });

                const forms = document.querySelectorAll('form');
                forms.forEach(function (form) {
                    form.addEventListener('submit', function () {
                        const formInputs = form.querySelectorAll('input[data-secret-input]');
                        formInputs.forEach(function (input) {
                            const initial = input.dataset.initialValue || '';
                            const changed = input.dataset.changed === '1';
                            const revealed = input.dataset.revealed === '1';
                            if (!changed && ((input.value === MASK && initial === MASK) || revealed)) {
                                input.value = '';
                            }
                        });
                    });
                });
            });
        })();
    </script>

    <div
        data-confirm-modal-root
        x-data="{
            open: false,
            title: 'Are you sure?',
            message: '',
            confirmText: 'Confirm',
            cancelText: 'Cancel',
            variant: 'default',
            form: null,
            openFromEvent(e) {
                const d = (e && e.detail) ? e.detail : {};
                this.form = d.form || null;
                this.title = (typeof d.title === 'string' && d.title) ? d.title : 'Are you sure?';
                this.message = (typeof d.message === 'string') ? d.message : '';
                this.confirmText = (typeof d.confirmText === 'string' && d.confirmText) ? d.confirmText : 'Confirm';
                this.cancelText = (typeof d.cancelText === 'string' && d.cancelText) ? d.cancelText : 'Cancel';
                this.variant = (typeof d.variant === 'string' && d.variant) ? d.variant : 'default';
                this.open = true;
                this.$nextTick(() => {
                    try {
                        const btn = this.$refs.confirmBtn;
                        if (btn) btn.focus();
                    } catch (err) {
                        // ignore
                    }
                });
            },
            close() {
                this.open = false;
                this.form = null;
            },
            confirm() {
                const form = this.form;
                if (!form) {
                    this.close();
                    return;
                }
                form.dataset.mpSkipConfirm = '1';
                this.open = false;
                this.form = null;
                this.$nextTick(() => {
                    try {
                        form.submit();
                    } finally {
                        window.setTimeout(() => {
                            try {
                                delete form.dataset.mpSkipConfirm;
                            } catch (e) {
                                // ignore
                            }
                        }, 0);
                    }
                });
            }
        }"
        x-on:open-confirm-modal.window="openFromEvent($event)"
    >
        <div x-cloak x-show="open" class="fixed inset-0 z-[9999]">
            <div class="absolute inset-0 bg-black/40" @click="close()"></div>
            <div class="absolute inset-0 flex items-center justify-center p-4">
                <div
                    class="w-full max-w-lg rounded-2xl bg-white shadow-xl ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10"
                    @click.stop
                    @keydown.escape.window="close()"
                >
                    <div class="flex items-start justify-between px-6 pt-6">
                        <div class="pr-8">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100" x-text="title"></h3>
                        </div>
                        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-300 dark:hover:bg-white/10 dark:hover:text-gray-100" @click="close()" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="px-6 pb-6 pt-2">
                        <p class="text-sm text-gray-600 dark:text-gray-300" x-text="message"></p>

                        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                            <button type="button" class="inline-flex justify-center rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600" @click="close()" x-text="cancelText"></button>
                            <button
                                x-ref="confirmBtn"
                                type="button"
                                class="inline-flex justify-center rounded-xl px-5 py-2.5 text-sm font-medium text-white shadow-sm"
                                :class="variant === 'danger' ? 'bg-red-600 hover:bg-red-700' : 'bg-primary-600 hover:bg-primary-700'"
                                @click="confirm()"
                                x-text="confirmText"
                            ></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
