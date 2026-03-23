@extends('layouts.admin')

@section('title', __('Features'))
@section('page-title', __('Features'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <x-button href="{{ route('admin.site-pages.index') }}" variant="secondary">{{ __('Back') }}</x-button>
        <x-button href="{{ route('features') }}" target="_blank" variant="secondary">{{ __('Preview') }}</x-button>
    </div>

    <x-card>
        <form method="POST" action="{{ route('admin.site-pages.features.update') }}" class="space-y-6">
            @csrf

            <div class="space-y-4">
                <details class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden" open>
                    <summary class="px-4 py-3 bg-gray-50 dark:bg-gray-800 cursor-pointer select-none">
                        <div class="flex items-center justify-between">
                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ __('Hero Section') }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Title & subtitle') }}</div>
                        </div>
                    </summary>
                    <div class="p-4 space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Hero Title') }}</label>
                            <input type="text" name="hero_title" value="{{ old('hero_title', $form['hero_title'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Hero Subtitle') }}</label>
                            <input type="text" name="hero_subtitle" value="{{ old('hero_subtitle', $form['hero_subtitle'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                        </div>
                    </div>
                </details>

                @for($i = 1; $i <= 4; $i++)
                    <details class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        <summary class="px-4 py-3 bg-gray-50 dark:bg-gray-800 cursor-pointer select-none">
                            <div class="flex items-center justify-between">
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ __('Section') }} {{ $i }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Title, description, bullets') }}</div>
                            </div>
                        </summary>
                        <div class="p-4 space-y-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Section Title') }}</label>
                                <input type="text" name="section_{{ $i }}_title" value="{{ old('section_'.$i.'_title', $form['section_'.$i.'_title'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Section Description') }}</label>
                                <input type="text" name="section_{{ $i }}_description" value="{{ old('section_'.$i.'_description', $form['section_'.$i.'_description'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-4">
                                    <div class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Detail items') }}</div>
                                    @for($j = 1; $j <= 3; $j++)
                                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-3">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Title') }} {{ $j }}</label>
                                                <input type="text" name="section_{{ $i }}_dt_{{ $j }}" value="{{ old('section_'.$i.'_dt_'.$j, $form['section_'.$i.'_dt_'.$j] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Description') }} {{ $j }}</label>
                                                <input type="text" name="section_{{ $i }}_dd_{{ $j }}" value="{{ old('section_'.$i.'_dd_'.$j, $form['section_'.$i.'_dd_'.$j] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                                            </div>
                                        </div>
                                    @endfor
                                </div>

                                <div class="space-y-4">
                                    <div class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Bullets') }}</div>
                                    @for($j = 1; $j <= 3; $j++)
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Bullet') }} {{ $j }}</label>
                                            <input type="text" name="section_{{ $i }}_bullet_{{ $j }}" value="{{ old('section_'.$i.'_bullet_'.$j, $form['section_'.$i.'_bullet_'.$j] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </details>
                @endfor
            </div>

            <div class="flex items-center justify-end gap-3">
                <x-button type="submit" variant="primary">{{ __('Save') }}</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
