@extends('layouts.admin')

@section('title', __('Register'))
@section('page-title', __('Register'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <x-button href="{{ route('admin.site-pages.index') }}" variant="secondary">{{ __('Back') }}</x-button>
        <x-button href="{{ route('register') }}" target="_blank" variant="secondary">{{ __('Preview') }}</x-button>
    </div>

    <x-card>
        <form method="POST" action="{{ route('admin.site-pages.register.update') }}" class="space-y-6">
            @csrf

            <div class="space-y-4">
                <details class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden" open>
                    <summary class="px-4 py-3 bg-gray-50 dark:bg-gray-800 cursor-pointer select-none">
                        <div class="flex items-center justify-between">
                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ __('Form') }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Title, subtitle, labels') }}</div>
                        </div>
                    </summary>
                    <div class="p-4 space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Welcome Title') }}</label>
                            <input type="text" name="welcome_title" value="{{ old('welcome_title', $form['welcome_title'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Welcome Subtitle') }}</label>
                            <input type="text" name="welcome_subtitle" value="{{ old('welcome_subtitle', $form['welcome_subtitle'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Register Button') }}</label>
                            <input type="text" name="button_register" value="{{ old('button_register', $form['button_register'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('OR Label') }}</label>
                            <input type="text" name="or_label" value="{{ old('or_label', $form['or_label'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Google Button') }}</label>
                            <input type="text" name="google_button" value="{{ old('google_button', $form['google_button'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Have Account Text') }}</label>
                                <input type="text" name="have_account" value="{{ old('have_account', $form['have_account'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Sign In Link Text') }}</label>
                                <input type="text" name="sign_in" value="{{ old('sign_in', $form['sign_in'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                            </div>
                        </div>
                    </div>
                </details>

                <details class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <summary class="px-4 py-3 bg-gray-50 dark:bg-gray-800 cursor-pointer select-none">
                        <div class="flex items-center justify-between">
                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ __('Marketing Panel') }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Headline, testimonial, partners') }}</div>
                        </div>
                    </summary>
                    <div class="p-4 space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Promo Title') }}</label>
                            <input type="text" name="promo_title" value="{{ old('promo_title', $form['promo_title'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Testimonial Quote') }}</label>
                            <input type="text" name="testimonial_quote" value="{{ old('testimonial_quote', $form['testimonial_quote'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Testimonial Name') }}</label>
                                <input type="text" name="testimonial_name" value="{{ old('testimonial_name', $form['testimonial_name'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Testimonial Role') }}</label>
                                <input type="text" name="testimonial_role" value="{{ old('testimonial_role', $form['testimonial_role'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Partners Title') }}</label>
                            <input type="text" name="partners_title" value="{{ old('partners_title', $form['partners_title'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @for($i = 1; $i <= 8; $i++)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Partner') }} {{ $i }}</label>
                                    <input type="text" name="partner_{{ $i }}" value="{{ old('partner_'.$i, $form['partner_'.$i] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
                                </div>
                            @endfor
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
