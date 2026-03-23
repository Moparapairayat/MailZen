<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app('locale.direction')->dir() }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-bind:class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        try {
            $appName = \App\Models\Setting::get('app_name', config('app.name', 'MailZen'));
        } catch (\Throwable $e) {
            $appName = config('app.name', 'MailZen');
        }

        if (!is_string($appName)) {
            $appName = config('app.name', 'MailZen');
        }
    @endphp
    @php
        $pageTitle = trim($__env->yieldContent('title', 'Admin'));
    @endphp
    <title>{{ __($pageTitle) }} - {{ $appName }}</title>

    <!-- Fonts -->
    @php
        $fontFamily = \App\Models\Setting::get('admin_font_family', 'Inter');
        $fontWeights = \App\Models\Setting::get('admin_font_weights', '400,500,600,700');
        $fontWeightsUrl = preg_replace('/\s*,\s*/', ';', $fontWeights);
        // Convert font name to Google Fonts format (replace spaces with +)
        $fontFamilyUrl = str_replace(' ', '+', $fontFamily);
        // Build Google Fonts URL
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
</head>
 @php
     $disableMainScroll = request()->routeIs('admin.ai-tools.email-text-generator');
 @endphp
 <body class="admin-theme font-sans antialiased bg-admin-main text-admin-text-primary {{ $disableMainScroll ? 'lg:h-screen lg:overflow-hidden' : '' }}" style="--app-font-family: '{{ $fontFamily }}', sans-serif; font-family: var(--app-font-family);">
     <div class="flex lg:pl-64 {{ $disableMainScroll ? 'lg:h-screen lg:overflow-hidden' : 'min-h-screen' }}" x-data="{ sidebarOpen: false }">
         <div x-cloak x-show="sidebarOpen" class="fixed inset-0 bg-black/50 z-30 lg:hidden" @click="sidebarOpen = false"></div>
         <!-- Sidebar -->
         <x-sidebar />
         @if(false)
         <aside class="w-64 bg-admin-sidebar border-r border-admin-border flex flex-col">
             <!-- Logo -->
             <div class="h-16 flex items-center justify-between px-6 border-b border-admin-border">
                <a href="{{ route('admin.dashboard') }}" class="text-xl font-bold text-admin-text-primary">
                    {{ config('app.name', 'MailZen') }}
                </a>
                <button
                    @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)"
                    class="p-2 rounded-lg text-admin-text-secondary hover:text-admin-text-primary hover:bg-white/5"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </button>
            </div>

            {{-- Navigation --}}
            <nav class="flex flex-col gap-6 items-start relative shrink-0 w-full overflow-y-auto flex-1 px-4 py-6">
                {{-- General --}}
                <div class="flex flex-col gap-3 items-start relative shrink-0 w-full">
                    <p class="font-normal leading-[18px] relative shrink-0 text-[#a8a8a8] dark:text-admin-text-secondary text-xs tracking-[-0.36px]">General</p>

                    <div class="flex flex-col gap-3 items-start relative shrink-0 w-full">
                        <a href="{{ route('admin.dashboard') }}" class="flex gap-2.5 items-center relative shrink-0 {{ request()->routeIs('admin.dashboard') ? 'text-[#1E5FEA]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                            <div class="relative shrink-0 size-[18px]">
                                <svg class="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                    <path d="M7.3125 2.25H4.3125C3.78916 2.25 3.5275 2.25 3.31457 2.31459C2.83517 2.46001 2.46001 2.83517 2.31459 3.31457C2.25 3.5275 2.25 3.78917 2.25 4.3125C2.25 4.83584 2.25 5.0975 2.31459 5.31043C2.46001 5.78983 2.83517 6.16499 3.31457 6.31041C3.5275 6.375 3.78916 6.375 4.3125 6.375H7.3125C7.83585 6.375 8.09752 6.375 8.31045 6.31041C8.78985 6.16499 9.165 5.78983 9.31042 5.31043C9.375 5.0975 9.375 4.83584 9.375 4.3125C9.375 3.78917 9.375 3.5275 9.31042 3.31457C9.165 2.83517 8.78985 2.46001 8.31045 2.31459C8.09752 2.25 7.83585 2.25 7.3125 2.25Z" stroke="currentColor" stroke-linejoin="round" stroke-width="1.25" />
                                    <path d="M15.75 7.3125V4.3125C15.75 3.78916 15.75 3.5275 15.6854 3.31457C15.54 2.83517 15.1649 2.46001 14.6855 2.31459C14.4725 2.25 14.2109 2.25 13.6875 2.25C13.1642 2.25 12.9025 2.25 12.6896 2.31459C12.2102 2.46001 11.835 2.83517 11.6896 3.31457C11.625 3.5275 11.625 3.78916 11.625 4.3125V7.3125C11.625 7.83585 11.625 8.09752 11.6896 8.31045C11.835 8.78985 12.2102 9.165 12.6896 9.31042C12.9025 9.375 13.1642 9.375 13.6875 9.375C14.2109 9.375 14.4725 9.375 14.6855 9.31042C15.1649 9.165 15.54 8.78985 15.6854 8.31045C15.75 8.09752 15.75 7.83585 15.75 7.3125Z" stroke="currentColor" stroke-linejoin="round" stroke-width="1.25" />
                                    <path d="M12.6896 15.6854C12.9025 15.75 13.1642 15.75 13.6875 15.75C14.2109 15.75 14.4725 15.75 14.6855 15.6854C15.1649 15.54 15.54 15.1649 15.6854 14.6855C15.75 14.4725 15.75 14.2109 15.75 13.6875C15.75 13.1642 15.75 12.9025 15.6854 12.6896C15.54 12.2102 15.1649 11.835 14.6855 11.6896C14.4725 11.625 14.2109 11.625 13.6875 11.625C13.1642 11.625 12.9025 11.625 12.6896 11.6896C12.2102 11.835 11.835 12.2102 11.6896 12.6896C11.625 12.9025 11.625 13.1642 11.625 13.6875C11.625 14.2109 11.625 14.4725 11.6896 14.6855C11.835 15.1649 12.2102 15.54 12.6896 15.6854Z" stroke="currentColor" stroke-linejoin="round" stroke-width="1.25" />
                                    <path d="M6.375 8.625H5.25C3.83578 8.625 3.12868 8.625 2.68934 9.06435C2.25 9.5037 2.25 10.2108 2.25 11.625V12.75C2.25 14.1642 2.25 14.8713 2.68934 15.3106C3.12868 15.75 3.83578 15.75 5.25 15.75H6.375C7.7892 15.75 8.4963 15.75 8.93565 15.3106C9.375 14.8713 9.375 14.1642 9.375 12.75V11.625C9.375 10.2108 9.375 9.5037 8.93565 9.06435C8.4963 8.625 7.7892 8.625 6.375 8.625Z" stroke="currentColor" stroke-linejoin="round" stroke-width="1.25" />
                                </svg>
                            </div>
                            <p class="font-medium leading-[22px] relative shrink-0 text-base tracking-[-0.48px]">Dashboard</p>
                        </a>

                        <a href="{{ route('admin.users.index') }}" class="flex gap-2.5 items-center relative shrink-0 {{ request()->routeIs('admin.users.*') ? 'text-[#1E5FEA]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                            <div class="relative shrink-0 size-[18px]">
                                <svg class="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                    <path d="M12.375 15V13.4778C12.375 12.5461 11.9555 11.6324 11.1077 11.246C10.0736 10.7746 8.83342 10.5 7.5 10.5C6.16659 10.5 4.92638 10.7746 3.89226 11.246C3.04445 11.6324 2.625 12.5461 2.625 13.4778V15" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                    <path d="M15.75 15.0007V13.4785C15.75 12.5467 14.9555 11.6332 14.1077 11.2467C13.9123 11.1576 13.7094 11.0755 13.5 11.001" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                    <path d="M7.5 8.25C8.94975 8.25 10.125 7.07475 10.125 5.625C10.125 4.17525 8.94975 3 7.5 3C6.05025 3 4.875 4.17525 4.875 5.625C4.875 7.07475 6.05025 8.25 7.5 8.25Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                    <path d="M11.25 3.1084C12.3343 3.43111 13.125 4.43556 13.125 5.62469C13.125 6.81383 12.3343 7.8183 11.25 8.14103" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                </svg>
                            </div>
                            <p class="font-normal leading-[22px] relative shrink-0 text-base tracking-[-0.48px]">Users</p>
                        </a>
                    </div>
                </div>

                {{-- Marketing --}}
                <div class="flex flex-col gap-3 items-start relative shrink-0 w-full">
                    <p class="font-normal leading-[18px] relative shrink-0 text-[#a8a8a8] dark:text-admin-text-secondary text-xs tracking-[-0.36px]">Marketing</p>

                    <div class="flex flex-col gap-3 items-start relative shrink-0 w-full">
                        <a href="{{ route('admin.campaigns.index') }}" class="flex gap-2.5 items-center relative shrink-0 {{ request()->routeIs('admin.campaigns.*') ? 'text-[#1E5FEA]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                            <div class="relative shrink-0 size-[18px]">
                                <svg class="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                    <path d="M11.1947 2.18327L6.20514 4.57839C5.82113 4.76272 5.41082 4.8089 4.99256 4.7152C4.71883 4.65388 4.58194 4.62322 4.47172 4.61063C3.10307 4.45434 2.25 5.53756 2.25 6.7832V7.4668C2.25 8.71245 3.10307 9.79567 4.47172 9.63937C4.58194 9.62677 4.71884 9.5961 4.99256 9.53482C5.41082 9.44107 5.82113 9.48727 6.20514 9.67162L11.1947 12.0667C12.3401 12.6166 12.9127 12.8914 13.5513 12.6772C14.1898 12.4629 14.4089 12.0031 14.8473 11.0835C16.0509 8.5584 16.0509 5.69164 14.8473 3.16647C14.4089 2.24689 14.1898 1.78711 13.5513 1.57282C12.9127 1.35855 12.3401 1.63345 11.1947 2.18327Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                    <path d="M8.59358 15.5782L7.47506 16.5C4.95387 14.5004 5.26188 13.5469 5.26188 9.75H6.11225C6.45734 11.8957 7.27134 12.912 8.39453 13.6478C9.0864 14.1009 9.22905 15.0544 8.59358 15.5782Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                    <path d="M5.625 9.375V4.875" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                </svg>
                            </div>
                            <p class="font-normal leading-[22px] relative shrink-0 text-base tracking-[-0.48px]">Campaigns</p>
                        </a>

                        <a href="{{ route('admin.lists.index') }}" class="flex gap-2.5 items-center relative shrink-0 {{ request()->routeIs('admin.lists.*') ? 'text-[#1E5FEA]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                            <div class="relative shrink-0 size-[18px]">
                                <svg class="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                    <path d="M1.5 14.25L6.68477 10.7179C8.57903 9.42737 9.42097 9.42737 11.3152 10.7179L16.5 14.25" stroke="currentColor" stroke-linejoin="round" stroke-width="1.125" />
                                    <path d="M15.75 15.0007V13.4785C15.75 12.5467 14.9555 11.6332 14.1077 11.2467C13.9123 11.1576 13.7094 11.0755 13.5 11.001" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                    <path d="M7.5 8.25C8.94975 8.25 10.125 7.07475 10.125 5.625C10.125 4.17525 8.94975 3 7.5 3C6.05025 3 4.875 4.17525 4.875 5.625C4.875 7.07475 6.05025 8.25 7.5 8.25Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                    <path d="M11.25 3.1084C12.3343 3.43111 13.125 4.43556 13.125 5.62469C13.125 6.81383 12.3343 7.8183 11.25 8.14103" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                </svg>
                            </div>
                            <p class="font-normal leading-[22px] relative shrink-0 text-base tracking-[-0.48px]">Lists</p>
                        </a>

                        <a href="{{ route('admin.email-validation.index') }}" class="flex gap-2.5 items-center relative shrink-0 {{ request()->routeIs('admin.email-validation.*') ? 'text-[#1E5FEA]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                            <div class="relative shrink-0 size-[18px]">
                                <svg class="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                    <path d="M2.25 4.5C2.25 3.67157 2.92157 3 3.75 3H14.25C15.0784 3 15.75 3.67157 15.75 4.5V13.5C15.75 14.3284 15.0784 15 14.25 15H3.75C2.92157 15 2.25 14.3284 2.25 13.5V4.5Z" stroke="currentColor" stroke-width="1.125" />
                                    <path d="M4.5 6H13.5" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                    <path d="M4.5 9H10.5" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                    <path d="M4.5 12H9" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                    <path d="M12.75 9.75L13.5 10.5L15 9" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                </svg>
                            </div>
                            <p class="font-normal leading-[22px] relative shrink-0 text-base tracking-[-0.48px]">Email Validation</p>
                        </a>
                    </div>
                </div>

                {{-- User Menu --}}
                <div class="p-4 border-t border-admin-border">
                    <div class="flex items-center">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-admin-text-primary truncate">
                                {{ auth('admin')->user()?->full_name }}
                            </p>
                            <p class="text-xs text-admin-text-secondary truncate">
                                {{ auth('admin')->user()?->email }}
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
            </aside>
            @endif

            {{-- Main Content --}}
            <div class="flex-1 flex flex-col overflow-hidden min-h-0" x-data="{ 
                    profileMenuOpen: false,
                    notificationsOpen: false,
                    languageMenuOpen: false
                }" @click.outside="profileMenuOpen = false; notificationsOpen = false; languageMenuOpen = false">
                {{-- Top Bar --}}
                <header class="h-16 bg-admin-main border-b border-admin-border flex items-center justify-between px-4 sm:px-6 gap-4">
                    {{-- Left: title + search --}}
                    <div class="flex items-center gap-4 flex-1 min-w-0">
                         <button
                             type="button"
                             class="lg:hidden p-2 rounded-md text-admin-text-secondary hover:text-admin-text-primary hover:bg-white/5 focus:outline-none focus:ring-2 focus:ring-primary-500"
                             @click.stop="sidebarOpen = true"
                             aria-label="{{ __('Open sidebar') }}"
                         >
                             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                             </svg>
                         </button>
                        <div class="min-w-0">
                            @php
                                $pageHeading = trim($__env->yieldContent('page-title', 'Dashboard'));
                            @endphp
                    <h1 class="text-xl font-semibold text-admin-text-primary truncate">
                        {{ __($pageHeading) }}
                    </h1>
                        </div>
                        {{-- Search on the right of title --}}
                         <div class="hidden lg:block flex-1 max-w-xl" x-data="headerSearch({ suggestUrl: @js(route('admin.search.suggest')), searchUrl: @js(route('admin.search.index')), initialQuery: @js(request('q')), minChars: 1, variant: 'admin' })" @click.outside="close()">
                             <form action="{{ route('admin.search.index') }}" method="GET" class="relative">
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
                                    placeholder="{{ __('Search users, customers, campaigns, lists...') }}"
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
                                        <span x-show="loading">{{ __('Searching...') }}</span>
                                        <span x-show="!loading" x-text="hasItems ? (items.length + ' results') : @js(__('No results'))"></span>
                                        <a :href="searchUrlWithQuery" class="text-xs text-primary-400 hover:text-primary-300" x-show="(query || '').trim().length">{{ __('View all') }}</a>
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

                    {{-- Right actions --}}
                    <div class="flex items-center gap-4">
                        {{-- Notifications --}}
                        @admincan('admin.notifications.access')
                            <div class="relative">
                                <button
                                    type="button"
                                    @click.stop="notificationsOpen = !notificationsOpen; profileMenuOpen = false; languageMenuOpen = false"
                                    class="relative p-2 rounded-full text-admin-text-secondary hover:text-admin-text-primary hover:bg-white/5 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    data-notifications-root
                                    data-notifications-feed-url="{{ route('admin.notifications.feed') }}"
                                    data-notifications-mark-all-read-url="{{ route('admin.notifications.mark-all-read') }}"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                    @php
                                        $unreadCount = auth('admin')->check()
                                            ? auth('admin')->user()->unreadNotifications()->count()
                                            : 0;
                                    @endphp
                                    @if($unreadCount > 0)
                                        <span class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-red-500 text-white" data-notifications-badge>
                                            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                                        </span>
                                    @endif
                                </button>

                                {{-- Notifications dropdown --}}
                                <div
                                    x-cloak
                                    x-show="notificationsOpen"
                                    x-transition
                                    class="origin-top-right absolute right-0 mt-2 w-80 rounded-lg shadow-lg bg-admin-sidebar ring-1 ring-admin-border z-20"
                                >
                                    <div class="px-4 py-3 border-b border-admin-border flex items-center justify-between">
                                        <h3 class="text-sm font-semibold text-admin-text-primary">
                                            {{ __('Notifications') }}
                                        </h3>
                                        <div class="flex items-center gap-2">
                                            @admincan('admin.notifications.edit')
                                                <button type="button" class="text-xs text-admin-text-secondary hover:text-admin-text-primary" data-notifications-mark-all-read>
                                                    {{ __('Mark all read') }}
                                                </button>
                                            @endadmincan
                                            <p class="text-xs text-admin-text-secondary" data-notifications-unread-label>
                                            @if($unreadCount > 0)
                                                {{ $unreadCount }} {{ __('unread') }}
                                            @else
                                                {{ __('Up to date') }}
                                            @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="max-h-80 overflow-y-auto divide-y divide-admin-border" data-notifications-list>
                                        @php
                                            $notifications = auth('admin')->check()
                                                ? auth('admin')->user()->notifications()->latest()->limit(10)->get()
                                                : collect();
                                        @endphp

                                        @forelse($notifications as $notification)
                                            @php
                                                $notificationUrl = is_array($notification->data) && is_string($notification->data['url'] ?? null)
                                                    ? $notification->data['url']
                                                    : null;
                                            @endphp

                                            @if(is_string($notificationUrl) && trim($notificationUrl) !== '')
                                                <a href="{{ $notificationUrl }}" class="block px-4 py-3 text-sm {{ $notification->read_at ? 'bg-admin-sidebar' : 'bg-white/5' }}">
                                                    <p class="text-admin-text-primary font-medium">
                                                        {{ $notification->data['title'] ?? __('Notification') }}
                                                    </p>
                                                    @if(!empty($notification->data['message']))
                                                        <p class="mt-1 text-xs text-admin-text-secondary">
                                                            {{ $notification->data['message'] }}
                                                        </p>
                                                    @endif
                                                    <p class="mt-1 text-[11px] text-admin-text-secondary/80">
                                                        {{ $notification->created_at->diffForHumans() }}
                                                    </p>
                                                </a>
                                            @else
                                                <div class="px-4 py-3 text-sm {{ $notification->read_at ? 'bg-admin-sidebar' : 'bg-white/5' }}">
                                                    <p class="text-admin-text-primary font-medium">
                                                        {{ $notification->data['title'] ?? __('Notification') }}
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
                                            @endif
                                        @empty
                                            <div class="px-4 py-6 text-center text-sm text-admin-text-secondary">
                                                {{ __('No notifications yet.') }}
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @endadmincan

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
                                    aria-label="{{ __('Change language') }}"
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
                                            <form method="POST" action="{{ route('admin.language.update') }}" data-turbo="false">
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
                            aria-label="{{ __('Toggle dark mode') }}"
                        >
                            <svg x-cloak x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <svg x-cloak x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                        </button>

                        {{-- Profile dropdown --}}
                        <div class="relative">
                            <button
                                type="button"
                                @click.stop="profileMenuOpen = !profileMenuOpen; notificationsOpen = false; languageMenuOpen = false"
                                class="flex items-center gap-2 text-admin-text-secondary hover:text-admin-text-primary focus:outline-none"
                            >
                                @php
                                    $user = auth('admin')->user();
                                    $avatarUrl = $user && $user->avatar_path
                                        ? \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($user->avatar_path, '/'))
                                        : null;
                                @endphp
                                <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center overflow-hidden">
                                    @if($avatarUrl)
                                        <img src="{{ $avatarUrl }}" alt="{{ $user->full_name }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-xs font-semibold text-admin-text-primary">
                                            {{ strtoupper(Str::substr($user->first_name, 0, 1) . Str::substr($user->last_name, 0, 1)) }}
                                        </span>
                                    @endif
                                </div>
                                <div class="hidden sm:flex flex-col items-start">
                                    <span class="text-sm font-medium text-admin-text-primary max-w-[140px] truncate">
                                        {{ $user->full_name }}
                                    </span>
                                    <span class="text-xs text-admin-text-secondary max-w-[140px] truncate">
                                        {{ $user->email }}
                                    </span>
                                </div>
                                <svg class="w-4 h-4 text-admin-text-secondary ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            {{-- Dropdown menu --}}
                            <div
                                x-cloak
                                x-show="profileMenuOpen"
                                x-transition
                                class="origin-top-right absolute right-0 mt-2 w-48 rounded-lg shadow-lg bg-admin-sidebar ring-1 z-20"
                            >
                                <div class="py-1">
                                    <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-admin-text-primary hover:bg-white/5">
                                        <svg class="w-4 h-4 text-admin-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <span>{{ __('Profile') }}</span>
                                    </a>
                                    <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-admin-text-primary hover:bg-white/5">
                                        <svg class="w-4 h-4 text-admin-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c-.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c.94-1.543-.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span>{{ __('Settings') }}</span>
                                        @php
                                            $headerUpdateAvailable = false;
                                            try {
                                                $st = \Illuminate\Support\Facades\Cache::get('update_server:update_status');
                                                $headerUpdateAvailable = is_array($st) ? (bool) ($st['update_available'] ?? false) : false;
                                            } catch (\Throwable $e) {
                                                $headerUpdateAvailable = false;
                                            }
                                        @endphp
                                        @if($headerUpdateAvailable)
                                            <span class="ml-auto inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-red-500 text-white">{{ __('Update') }}</span>
                                        @endif
                                    </a>
                                    @admincan('admin.translations.access')
                                        <a href="{{ route('admin.translations.locales.index') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-admin-text-primary hover:bg-white/5">
                                            <svg class="w-4 h-4 text-admin-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m4 16l-4-9-4 9m4-9v9m8-6h-6" />
                                            </svg>
                                            <span>{{ __('Translations') }}</span>
                                        </a>
                                    @endadmincan
                                    <div class="border-t border-admin-border my-1"></div>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30">
                                            {{ __('Log out') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                {{-- Page Content --}}
                <main class="flex-1 flex flex-col min-h-0 overflow-y-auto {{ $disableMainScroll ? 'lg:overflow-y-hidden' : '' }} overflow-x-auto sm:overflow-x-visible p-4 sm:p-6">
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
