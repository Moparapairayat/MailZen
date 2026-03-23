@props([
    'title' => null,
    'subtitle' => null,
    'header' => null,
    'footer' => null,
    'padding' => true,
])

@php
    $paddingEnabled = !in_array($padding, [false, 0, '0', 'false'], true);
@endphp

<div {{ $attributes->merge(['class' => 'bg-admin-sidebar rounded-lg shadow-sm border border-admin-border']) }}>
    @if($title || $subtitle || $header)
        <div class="px-6 py-4 border-b border-admin-border">
            @if($header)
                {{ $header }}
            @else
                @if($title)
                    <h3 class="text-lg font-semibold text-admin-text-primary">{{ $title }}</h3>
                @endif
                @if($subtitle)
                    <p class="mt-1 text-sm text-admin-text-secondary">{{ $subtitle }}</p>
                @endif
            @endif
        </div>
    @endif
    
    <div class="{{ $paddingEnabled ? 'px-6 py-4' : '' }}">
        {{ $slot }}
    </div>
    
    @if($footer)
        <div class="px-6 py-4 border-t border-admin-border bg-white/5 rounded-b-lg">
            {{ $footer }}
        </div>
    @endif
</div>

