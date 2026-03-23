@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'iconPosition' => 'left',
    'href' => null,
    'pill' => false,
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
    
    $userClass = $attributes->get('class');
    $hasPaddingOverride = is_string($userClass) && preg_match('/(?:^|\s)!?p[trblxy]?-(?:\d+(?:\.\d+)?|px|\[[^\]]+\]|[a-zA-Z0-9_-]+)/', $userClass);
    
    $variantClasses = [
        'primary' => 'btn-brand-gradient text-white focus:ring-primary-500',
        'secondary' => 'bg-white/5 text-admin-text-primary hover:bg-white/10 focus:ring-primary-500 border border-admin-border',
        'success' => 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
        'warning' => 'bg-yellow-600 text-white hover:bg-yellow-700 focus:ring-yellow-500',
        'info' => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
        'outline' => 'border-2 border-admin-border text-admin-text-primary hover:bg-white/5 focus:ring-primary-500',
        'ghost' => 'text-admin-text-secondary hover:bg-white/5 hover:text-admin-text-primary focus:ring-primary-500',
        'table' => 'bg-white border border-gray-200 text-gray-900 hover:bg-gray-50 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100 dark:hover:bg-gray-700',
        'table-info' => 'bg-white border border-blue-200 text-blue-700 hover:bg-blue-50 focus:ring-blue-500 dark:bg-gray-800 dark:border-blue-800 dark:text-blue-300 dark:hover:bg-blue-900/20',
        'table-danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
    ];
    
    $sizeClasses = [
        'xs' => 'px-2 py-1 text-xs',
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-6 py-3 text-base',
        'xl' => 'px-8 py-4 text-lg',
        'action' => 'px-6 py-2 text-sm',
    ];

    $sizeClass = $sizeClasses[$size];
    if ($hasPaddingOverride) {
        $sizeClassParts = preg_split('/\s+/', trim($sizeClass)) ?: [];
        $sizeClass = implode(' ', array_values(array_filter($sizeClassParts, fn ($c) => !preg_match('/^!?p[trblxy]?-/', $c))));
    }

    $classes = $baseClasses . ' ' . $variantClasses[$variant] . ' ' . $sizeClass . ($pill ? ' rounded-full' : '');
    $tag = $href ? 'a' : 'button';
    $mergedAttributes = $attributes->except('class')->merge([
        'class' => trim($classes . ' ' . (is_string($userClass) ? $userClass : '')),
    ]);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $mergedAttributes }}>
        @if($icon && $iconPosition === 'left')
            <span class="mr-2">{{ $icon }}</span>
        @endif

        {{ $slot }}

        @if($icon && $iconPosition === 'right')
            <span class="ml-2">{{ $icon }}</span>
        @endif
    </a>
@else
    <button type="{{ $type }}" {{ $mergedAttributes }}>
        @if($icon && $iconPosition === 'left')
            <span class="mr-2">{{ $icon }}</span>
        @endif

        {{ $slot }}

        @if($icon && $iconPosition === 'right')
            <span class="ml-2">{{ $icon }}</span>
        @endif
    </button>
@endif

