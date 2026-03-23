@props([
    'name' => 'modal',
    'show' => false,
    'maxWidth' => '2xl',
    'closeable' => true,
])

@php
    $maxWidthClasses = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
        '3xl' => 'sm:max-w-3xl',
        '4xl' => 'sm:max-w-4xl',
        '5xl' => 'sm:max-w-5xl',
        '6xl' => 'sm:max-w-6xl',
        '7xl' => 'sm:max-w-7xl',
    ];
@endphp

<div
    x-data="{ show: @js($show) }"
    x-on:open-modal.window="if ($event.detail === @js($name) || $event.detail?.name === @js($name)) show = true"
    x-on:close-modal.window="if ($event.detail === @js($name) || $event.detail?.name === @js($name)) show = false"
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false"
    x-show="show"
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-6 overflow-y-auto"
    style="display: none;"
>
    <div
        x-show="show"
        class="fixed inset-0 transform transition-all"
        x-on:click="show = false"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="absolute inset-0 bg-admin-main opacity-75"></div>
    </div>

    <div
        x-show="show"
        class="w-full transform transition-all"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    >
        <div class="relative mx-auto bg-admin-sidebar rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:w-full {{ $maxWidthClasses[$maxWidth] }}">
            @if($closeable)
                <div class="absolute top-0 right-0 pt-4 pr-4">
                    <button
                        type="button"
                        class="text-admin-text-secondary hover:text-admin-text-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                        x-on:click="show = false"
                    >
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            @endif

            {{ $slot }}
        </div>
    </div>
</div>

