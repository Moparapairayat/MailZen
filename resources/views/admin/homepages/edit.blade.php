@extends('layouts.admin')

@section('title', $variantLabel)
@section('page-title', $variantLabel)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <x-button href="{{ route('admin.homepages.index') }}" variant="secondary">{{ __('Back') }}</x-button>
        <x-button href="{{ route('home.variant', ['variant' => (int) $variant]) }}" target="_blank" variant="secondary">{{ __('Preview') }}</x-button>
    </div>

    <x-card>
        <form method="POST" action="{{ route('admin.homepages.update', ['variant' => $variant]) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="space-y-4">
                <details class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden" open>
                    <summary class="px-4 py-3 bg-gray-50 dark:bg-gray-800 cursor-pointer select-none">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ __('Hero Section') }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Texts & image') }}</div>
                            </div>
                        </div>
                    </summary>

                    <div class="p-4 space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Hero Description') }}</label>
                            <input
                                type="text"
                                name="hero_description"
                                value="{{ old('hero_description', $form['hero_description'] ?? '') }}"
                                class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Hero Scroll Text') }}</label>
                            <input
                                type="text"
                                name="hero_scroll_text"
                                value="{{ old('hero_scroll_text', $form['hero_scroll_text'] ?? '') }}"
                                class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Hero Button') }}</label>
                            <input
                                type="text"
                                name="hero_button_text"
                                value="{{ old('hero_button_text', $form['hero_button_text'] ?? '') }}"
                                class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Hero Button Type') }}</label>
                            <select
                                name="hero_button_type"
                                class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                            >
                                @php
                                    $buttonType = old('hero_button_type', $form['hero_button_type'] ?? 'link');
                                @endphp
                                <option value="link" {{ $buttonType === 'link' ? 'selected' : '' }}>{{ __('Link') }}</option>
                                <option value="video" {{ $buttonType === 'video' ? 'selected' : '' }}>{{ __('Video') }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Hero Button URL') }}</label>
                            <input
                                type="text"
                                name="hero_button_url"
                                value="{{ old('hero_button_url', $form['hero_button_url'] ?? '') }}"
                                class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Hero Image') }}</label>

                            @if(!empty($heroImageUrl))
                                <div class="mb-3">
                                    <img src="{{ $heroImageUrl }}" alt="" class="max-w-full rounded-lg border border-gray-200 dark:border-gray-700">
                                </div>
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                    <input type="checkbox" name="remove_hero_image" value="1" class="rounded border-gray-300 dark:border-gray-600">
                                    <span>{{ __('Remove current image') }}</span>
                                </label>
                            @endif

                            <div class="mt-3">
                                <input
                                    type="file"
                                    name="hero_image"
                                    accept="image/*"
                                    class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                >
                            </div>
                        </div>

                        @if($variant === '4')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Hero Badge') }}</label>
                                    <input type="text" name="hero_badge" value="{{ old('hero_badge', $form['hero_badge'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Hero Title Highlight') }}</label>
                                    <input type="text" name="hero_title_highlight" value="{{ old('hero_title_highlight', $form['hero_title_highlight'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Hero Title Prefix') }}</label>
                                <input type="text" name="hero_title_prefix" value="{{ old('hero_title_prefix', $form['hero_title_prefix'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Secondary Button Text') }}</label>
                                    <input type="text" name="hero_secondary_button_text" value="{{ old('hero_secondary_button_text', $form['hero_secondary_button_text'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Secondary Button URL') }}</label>
                                    <input type="text" name="hero_secondary_button_url" value="{{ old('hero_secondary_button_url', $form['hero_secondary_button_url'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                                </div>
                            </div>

                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-4">
                                <div class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Hero Stats') }}</div>
                                @for($i = 1; $i <= 4; $i++)
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Value') }} {{ $i }}</label>
                                            <input type="text" name="stat_{{ $i }}_value" value="{{ old('stat_' . $i . '_value', $form['stat_' . $i . '_value'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Label') }} {{ $i }}</label>
                                            <input type="text" name="stat_{{ $i }}_label" value="{{ old('stat_' . $i . '_label', $form['stat_' . $i . '_label'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        @endif
                    </div>
                </details>

                <details class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <summary class="px-4 py-3 bg-gray-50 dark:bg-gray-800 cursor-pointer select-none">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ __('Logos Section') }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Upload partner/client logos') }}</div>
                            </div>
                        </div>
                    </summary>

                    <div class="p-4 space-y-4">
                        @if($variant === '4')
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Logos Title') }}</label>
                                <input type="text" name="logos_title" value="{{ old('logos_title', $form['logos_title'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach(($logos ?? []) as $logo)
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-3">
                                    <div class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Logo') }} {{ $logo['index'] }}</div>

                                    @if(!empty($logo['url']))
                                        <div>
                                            <img src="{{ $logo['url'] }}" alt="" class="max-h-16 w-auto rounded bg-white p-2 border border-gray-100">
                                        </div>
                                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                            <input type="checkbox" name="remove_logo_{{ $logo['index'] }}" value="1" class="rounded border-gray-300 dark:border-gray-600">
                                            <span>{{ __('Remove') }}</span>
                                        </label>
                                    @endif

                                    <input
                                        type="file"
                                        name="logo_{{ $logo['index'] }}"
                                        accept="image/*"
                                        class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                    >
                                </div>
                            @endforeach
                        </div>
                    </div>
                </details>

                @if($variant === '4')
                    <details class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        <summary class="px-4 py-3 bg-gray-50 dark:bg-gray-800 cursor-pointer select-none">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ __('Benefits Section') }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Title, subtitle, and 3 cards') }}</div>
                                </div>
                            </div>
                        </summary>

                        <div class="p-4 space-y-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Benefits Title') }}</label>
                                <input type="text" name="benefits_title" value="{{ old('benefits_title', $form['benefits_title'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Benefits Subtitle') }}</label>
                                <input type="text" name="benefits_subtitle" value="{{ old('benefits_subtitle', $form['benefits_subtitle'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                            </div>

                            @for($i = 1; $i <= 3; $i++)
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-3">
                                    <div class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Benefit Card') }} {{ $i }}</div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Title') }}</label>
                                        <input type="text" name="benefits_{{ $i }}_title" value="{{ old('benefits_' . $i . '_title', $form['benefits_' . $i . '_title'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Description') }}</label>
                                        <input type="text" name="benefits_{{ $i }}_description" value="{{ old('benefits_' . $i . '_description', $form['benefits_' . $i . '_description'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </details>
                @endif

                <details class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <summary class="px-4 py-3 bg-gray-50 dark:bg-gray-800 cursor-pointer select-none">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ __('Features Section') }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Title, subtitle, and 6 cards') }}</div>
                            </div>
                        </div>
                    </summary>

                    <div class="p-4 space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Features Title') }}</label>
                            <input type="text" name="features_title" value="{{ old('features_title', $form['features_title'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Features Subtitle') }}</label>
                            <input type="text" name="features_subtitle" value="{{ old('features_subtitle', $form['features_subtitle'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                        </div>

                        @for($i = 1; $i <= 6; $i++)
                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-3">
                                <div class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Feature') }} {{ $i }}</div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Title') }}</label>
                                    <input type="text" name="features_{{ $i }}_title" value="{{ old('features_' . $i . '_title', $form['features_' . $i . '_title'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Description') }}</label>
                                    <input type="text" name="features_{{ $i }}_description" value="{{ old('features_' . $i . '_description', $form['features_' . $i . '_description'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                                </div>
                            </div>
                        @endfor

                        @if($variant === '4')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Features CTA Text') }}</label>
                                    <input type="text" name="features_cta_text" value="{{ old('features_cta_text', $form['features_cta_text'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Features CTA URL') }}</label>
                                    <input type="text" name="features_cta_url" value="{{ old('features_cta_url', $form['features_cta_url'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                                </div>
                            </div>
                        @endif
                    </div>
                </details>

                <details class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <summary class="px-4 py-3 bg-gray-50 dark:bg-gray-800 cursor-pointer select-none">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ __('AI Section') }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $variant === '4' ? __('Badge, title, subtitle, and 4 cards') : __('Badge, title, subtitle, and 2 cards') }}
                                </div>
                            </div>
                        </div>
                    </summary>

                    <div class="p-4 space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('AI Badge') }}</label>
                            <input type="text" name="ai_badge" value="{{ old('ai_badge', $form['ai_badge'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('AI Title') }}</label>
                            <input type="text" name="ai_title" value="{{ old('ai_title', $form['ai_title'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                        </div>

                        @if($variant === '4')
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('AI Title Highlight') }}</label>
                                <input type="text" name="ai_title_highlight" value="{{ old('ai_title_highlight', $form['ai_title_highlight'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('AI Subtitle') }}</label>
                            <input type="text" name="ai_subtitle" value="{{ old('ai_subtitle', $form['ai_subtitle'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                        </div>

                        @for($i = 1; $i <= 2; $i++)
                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-3">
                                <div class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('AI Card') }} {{ $i }}</div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Title') }}</label>
                                    <input type="text" name="ai_{{ $i }}_title" value="{{ old('ai_' . $i . '_title', $form['ai_' . $i . '_title'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Description') }}</label>
                                    <input type="text" name="ai_{{ $i }}_description" value="{{ old('ai_' . $i . '_description', $form['ai_' . $i . '_description'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                                </div>
                            </div>
                        @endfor

                        @if($variant === '4')
                            @for($i = 3; $i <= 4; $i++)
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-3">
                                    <div class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('AI Card') }} {{ $i }}</div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Title') }}</label>
                                        <input type="text" name="ai_{{ $i }}_title" value="{{ old('ai_' . $i . '_title', $form['ai_' . $i . '_title'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Description') }}</label>
                                        <input type="text" name="ai_{{ $i }}_description" value="{{ old('ai_' . $i . '_description', $form['ai_' . $i . '_description'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                                    </div>
                                </div>
                            @endfor

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('AI CTA Text') }}</label>
                                    <input type="text" name="ai_cta_text" value="{{ old('ai_cta_text', $form['ai_cta_text'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('AI CTA URL') }}</label>
                                    <input type="text" name="ai_cta_url" value="{{ old('ai_cta_url', $form['ai_cta_url'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                                </div>
                            </div>
                        @endif
                    </div>
                </details>

                @if($variant === '4')
                    <details class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        <summary class="px-4 py-3 bg-gray-50 dark:bg-gray-800 cursor-pointer select-none">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ __('Testimonials Section') }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Title, subtitle, and 3 testimonials') }}</div>
                                </div>
                            </div>
                        </summary>

                        <div class="p-4 space-y-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Testimonials Title') }}</label>
                                <input type="text" name="testimonials_title" value="{{ old('testimonials_title', $form['testimonials_title'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Testimonials Subtitle') }}</label>
                                <input type="text" name="testimonials_subtitle" value="{{ old('testimonials_subtitle', $form['testimonials_subtitle'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                            </div>

                            @for($i = 1; $i <= 3; $i++)
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-3">
                                    <div class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Testimonial') }} {{ $i }}</div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Quote') }}</label>
                                        <input type="text" name="testimonial_{{ $i }}_quote" value="{{ old('testimonial_' . $i . '_quote', $form['testimonial_' . $i . '_quote'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Name') }}</label>
                                            <input type="text" name="testimonial_{{ $i }}_name" value="{{ old('testimonial_' . $i . '_name', $form['testimonial_' . $i . '_name'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Role') }}</label>
                                            <input type="text" name="testimonial_{{ $i }}_role" value="{{ old('testimonial_' . $i . '_role', $form['testimonial_' . $i . '_role'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Initial') }}</label>
                                            <input type="text" name="testimonial_{{ $i }}_initial" value="{{ old('testimonial_' . $i . '_initial', $form['testimonial_' . $i . '_initial'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </details>
                @endif

                <details class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <summary class="px-4 py-3 bg-gray-50 dark:bg-gray-800 cursor-pointer select-none">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ __('Get Started Section') }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Title, subtitle, and 3 steps') }}</div>
                            </div>
                        </div>
                    </summary>

                    <div class="p-4 space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Title') }}</label>
                            <input type="text" name="how_title" value="{{ old('how_title', $form['how_title'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Subtitle') }}</label>
                            <input type="text" name="how_subtitle" value="{{ old('how_subtitle', $form['how_subtitle'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                        </div>

                        @for($i = 1; $i <= 3; $i++)
                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-3">
                                <div class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Step') }} {{ $i }}</div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Title') }}</label>
                                    <input type="text" name="how_{{ $i }}_title" value="{{ old('how_' . $i . '_title', $form['how_' . $i . '_title'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Description') }}</label>
                                    <input type="text" name="how_{{ $i }}_description" value="{{ old('how_' . $i . '_description', $form['how_' . $i . '_description'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                                </div>
                            </div>
                        @endfor
                    </div>
                </details>

                <details class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <summary class="px-4 py-3 bg-gray-50 dark:bg-gray-800 cursor-pointer select-none">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ __('FAQs (Common)') }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Shared across all homepage variants') }}</div>
                            </div>
                        </div>
                    </summary>

                    <div class="p-4 space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('FAQ Title') }}</label>
                            <input type="text" name="faq_title" value="{{ old('faq_title', $form['faq_title'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('FAQ Subtitle') }}</label>
                            <input type="text" name="faq_subtitle" value="{{ old('faq_subtitle', $form['faq_subtitle'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                        </div>

                        @for($i = 1; $i <= 6; $i++)
                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-3">
                                <div class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('FAQ') }} {{ $i }}</div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Question') }}</label>
                                    <input type="text" name="faq_{{ $i }}_question" value="{{ old('faq_' . $i . '_question', $form['faq_' . $i . '_question'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Answer') }}</label>
                                    <input type="text" name="faq_{{ $i }}_answer" value="{{ old('faq_' . $i . '_answer', $form['faq_' . $i . '_answer'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                                </div>
                            </div>
                        @endfor
                    </div>
                </details>

                <details class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <summary class="px-4 py-3 bg-gray-50 dark:bg-gray-800 cursor-pointer select-none">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ __('Pricing (Common)') }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Shared across all homepage variants') }}</div>
                            </div>
                        </div>
                    </summary>

                    <div class="p-4 space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Pricing Badge') }}</label>
                            <input type="text" name="pricing_badge" value="{{ old('pricing_badge', $form['pricing_badge'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Pricing Title') }}</label>
                            <input type="text" name="pricing_title" value="{{ old('pricing_title', $form['pricing_title'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Pricing Subtitle') }}</label>
                            <input type="text" name="pricing_subtitle" value="{{ old('pricing_subtitle', $form['pricing_subtitle'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Toggle Monthly Text') }}</label>
                                <input type="text" name="pricing_toggle_monthly" value="{{ old('pricing_toggle_monthly', $form['pricing_toggle_monthly'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Toggle Annual Text') }}</label>
                                <input type="text" name="pricing_toggle_annual" value="{{ old('pricing_toggle_annual', $form['pricing_toggle_annual'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Toggle Save Text') }}</label>
                                <input type="text" name="pricing_toggle_save" value="{{ old('pricing_toggle_save', $form['pricing_toggle_save'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Popular Badge Text') }}</label>
                                <input type="text" name="pricing_popular_badge" value="{{ old('pricing_popular_badge', $form['pricing_popular_badge'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Compare Link Text') }}</label>
                                <input type="text" name="pricing_compare_text" value="{{ old('pricing_compare_text', $form['pricing_compare_text'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Default Pricing CTA Text') }}</label>
                            <input type="text" name="pricing_card_cta_text" value="{{ old('pricing_card_cta_text', $form['pricing_card_cta_text'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                        </div>

                        @for($i = 1; $i <= 3; $i++)
                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-3">
                                <div class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Pricing Card') }} {{ $i }}</div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Title') }}</label>
                                    <input type="text" name="pricing_card_{{ $i }}_title" value="{{ old('pricing_card_' . $i . '_title', $form['pricing_card_' . $i . '_title'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Description') }}</label>
                                    <input type="text" name="pricing_card_{{ $i }}_description" value="{{ old('pricing_card_' . $i . '_description', $form['pricing_card_' . $i . '_description'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('CTA Text') }}</label>
                                    <input type="text" name="pricing_card_{{ $i }}_cta_text" value="{{ old('pricing_card_' . $i . '_cta_text', $form['pricing_card_' . $i . '_cta_text'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                                </div>
                            </div>
                        @endfor
                    </div>
                </details>

                <details class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <summary class="px-4 py-3 bg-gray-50 dark:bg-gray-800 cursor-pointer select-none">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ __('Final CTA (Common)') }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Shared across all homepage variants') }}</div>
                            </div>
                        </div>
                    </summary>

                    <div class="p-4 space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('CTA Badge') }}</label>
                            <input type="text" name="cta_badge" value="{{ old('cta_badge', $form['cta_badge'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('CTA Title') }}</label>
                            <input type="text" name="cta_title" value="{{ old('cta_title', $form['cta_title'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('CTA Subtitle') }}</label>
                            <input type="text" name="cta_subtitle" value="{{ old('cta_subtitle', $form['cta_subtitle'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Primary Button Text') }}</label>
                                <input type="text" name="cta_primary_text" value="{{ old('cta_primary_text', $form['cta_primary_text'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Primary Button URL') }}</label>
                                <input type="text" name="cta_primary_url" value="{{ old('cta_primary_url', $form['cta_primary_url'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Secondary Button Text') }}</label>
                                <input type="text" name="cta_secondary_text" value="{{ old('cta_secondary_text', $form['cta_secondary_text'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Secondary Button URL') }}</label>
                                <input type="text" name="cta_secondary_url" value="{{ old('cta_secondary_url', $form['cta_secondary_url'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('CTA Note (optional)') }}</label>
                            <input type="text" name="cta_note" value="{{ old('cta_note', $form['cta_note'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                        </div>
                    </div>
                </details>
            </div>

            <div class="flex items-center justify-end gap-3">
                <x-button type="submit" variant="primary">{{ __('Save') }}</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
