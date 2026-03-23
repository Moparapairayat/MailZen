@props(['variant' => 'public'])

@php
    $appName = config('app.name', 'MailZen');
    $creditNames = 'MOPARA PAIR AYAT & Fatema Binte Mariam';
@endphp

@if($variant === 'panel')
    <div class="mt-auto border-t border-gray-100 dark:border-admin-border pt-4">
        <div class="flex flex-col gap-1 text-xs sm:flex-row sm:items-center sm:justify-between">
            <p class="text-admin-text-secondary">{{ $appName }} panel</p>
            <p class="text-admin-text-secondary">
                Built and maintained by
                <span class="font-semibold text-primary-500">{{ $creditNames }}</span>
            </p>
        </div>
    </div>
@else
    <footer class="border-t border-gray-200 bg-white/90 backdrop-blur-sm dark:border-gray-800 dark:bg-gray-950/85">
        <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-5 text-sm sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-3">
                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $appName }}</span>
                <span class="hidden text-gray-300 dark:text-gray-700 sm:inline">/</span>
                <span class="text-gray-600 dark:text-gray-400">
                    Built and maintained by
                    <span class="font-semibold text-primary-600 dark:text-primary-400">{{ $creditNames }}</span>
                </span>
            </div>
            <p class="text-xs uppercase tracking-[0.22em] text-gray-400 dark:text-gray-500">Personal Build</p>
        </div>
    </footer>
@endif
