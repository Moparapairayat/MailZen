@extends('layouts.admin')

@section('title', __('Pricing'))
@section('page-title', __('Pricing'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <x-button href="{{ route('admin.site-pages.index') }}" variant="secondary">{{ __('Back') }}</x-button>
        <x-button href="{{ route('pricing') }}" target="_blank" variant="secondary">{{ __('Preview') }}</x-button>
    </div>

    <x-card>
        <form method="POST" action="{{ route('admin.site-pages.pricing.update') }}" class="space-y-6">
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

                <details class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <summary class="px-4 py-3 bg-gray-50 dark:bg-gray-800 cursor-pointer select-none">
                        <div class="flex items-center justify-between">
                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ __('Labels') }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Badges & buttons') }}</div>
                        </div>
                    </summary>
                    <div class="p-4 space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Featured Plan') }}</label>
                            <select name="featured_plan_id" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                <option value="0">{{ __('Auto (first active plan)') }}</option>
                                @foreach(($availablePlans ?? []) as $p)
                                    <option value="{{ $p->id }}" {{ (int) old('featured_plan_id', $featuredPlanId ?? 0) === (int) $p->id ? 'selected' : '' }}>
                                        {{ $p->name }} ({{ $p->currency }} {{ number_format((float) $p->price, 2) }} / {{ $p->billing_cycle }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Popular Badge') }}</label>
                            <input type="text" name="popular_badge" value="{{ old('popular_badge', $form['popular_badge'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('CTA (Logged in)') }}</label>
                                <input type="text" name="cta_auth" value="{{ old('cta_auth', $form['cta_auth'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('CTA (Guest)') }}</label>
                                <input type="text" name="cta_guest" value="{{ old('cta_guest', $form['cta_guest'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                            </div>
                        </div>
                    </div>
                </details>

                <details class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <summary class="px-4 py-3 bg-gray-50 dark:bg-gray-800 cursor-pointer select-none">
                        <div class="flex items-center justify-between">
                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ __('FAQ Section') }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Questions & answers') }}</div>
                        </div>
                    </summary>
                    <div class="p-4 space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('FAQ Title') }}</label>
                            <input type="text" name="faq_title" value="{{ old('faq_title', $form['faq_title'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                        </div>

                        @php
                            $faqOld = old('faq');
                            $faqForm = is_array($faqOld) ? $faqOld : (is_array($faq ?? null) ? $faq : []);
                        @endphp

                        <div class="space-y-4">
                            @foreach($faqForm as $idx => $row)
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-3">
                                    <div class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('FAQ') }} {{ $idx + 1 }}</div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Question') }}</label>
                                        <input type="text" name="faq[{{ $idx }}][q]" value="{{ is_array($row) ? ($row['q'] ?? '') : '' }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Answer') }}</label>
                                        <input type="text" name="faq[{{ $idx }}][a]" value="{{ is_array($row) ? ($row['a'] ?? '') : '' }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                                    </div>
                                </div>
                            @endforeach
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
