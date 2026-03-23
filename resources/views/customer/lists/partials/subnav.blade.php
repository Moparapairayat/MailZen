@php
    $isOverview = request()->routeIs('customer.lists.show');
    $isSubscribers = request()->routeIs('customer.lists.subscribers.*');
    $isForms = request()->routeIs('customer.lists.forms.*');
    $isSettings = request()->routeIs('customer.lists.settings*');
@endphp

<div class="border-b border-gray-200 dark:border-gray-700">
    <nav class="-mb-px flex flex-wrap gap-x-6" aria-label="Tabs">
        <a
            href="{{ route('customer.lists.show', $list) }}"
            class="whitespace-nowrap py-3 px-1 text-sm font-medium"
            @class([
                'border-b-2 border-primary-500 text-primary-600' => $isOverview,
                'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' => !$isOverview,
            ])
        >
            Overview
        </a>
        <a
            href="{{ route('customer.lists.subscribers.index', $list) }}"
            class="whitespace-nowrap py-3 px-1 text-sm font-medium"
            @class([
                'border-b-2 border-primary-500 text-primary-600' => $isSubscribers,
                'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' => !$isSubscribers,
            ])
        >
            Subscribers
        </a>
        <a
            href="{{ route('customer.lists.forms.index', $list) }}"
            class="whitespace-nowrap py-3 px-1 text-sm font-medium"
            @class([
                'border-b-2 border-primary-500 text-primary-600' => $isForms,
                'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' => !$isForms,
            ])
        >
            Forms
        </a>
        <a
            href="{{ route('customer.lists.settings', $list) }}"
            class="whitespace-nowrap py-3 px-1 text-sm font-medium"
            @class([
                'border-b-2 border-primary-500 text-primary-600' => $isSettings,
                'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' => !$isSettings,
            ])
        >
            Settings
        </a>
    </nav>
</div>
