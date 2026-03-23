@extends('layouts.admin')

@section('title', __('Affiliation'))
@section('page-title', __('Affiliation'))

@section('content')
<div class="space-y-4">
    <div class="border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
        <nav class="-mb-px flex min-w-max space-x-6 sm:space-x-8 px-2 sm:px-0" aria-label="Tabs">
            @foreach(($navItems ?? []) as $item)
                <a
                    href="{{ route($item['route']) }}"
                    class="{{ ($item['active'] ?? false) ? '!border-primary-500 text-primary-600 dark:text-primary-400 border-b-2' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }} whitespace-nowrap shrink-0 py-3 sm:py-4 px-2 sm:px-1 font-medium text-sm"
                >
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>
    </div>

    <form method="POST" action="{{ route('admin.affiliates.settings.update') }}">
        @csrf

        <x-card title="{{ __('Affiliate Settings') }}">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Enable Affiliate Programme') }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Enable referral tracking and commission creation.') }}</div>
                    </div>
                    <div>
                        <input type="hidden" name="affiliate_enabled" value="0">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="affiliate_enabled" value="1" {{ old('affiliate_enabled', $enabled ?? false) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Cookie Duration (days)') }}</label>
                        <input type="number" min="1" max="3650" name="affiliate_cookie_days" value="{{ old('affiliate_cookie_days', $cookieDays ?? 30) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Minimum Payout Amount') }}</label>
                        <input type="number" min="0" name="affiliate_min_payout_amount" value="{{ old('affiliate_min_payout_amount', $minPayoutAmount ?? 50) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Commission Scope') }}</label>
                        <select name="affiliate_commission_scope" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                            <option value="first_payment" {{ old('affiliate_commission_scope', $commissionScope ?? 'first_payment') === 'first_payment' ? 'selected' : '' }}>{{ __('First Payment') }}</option>
                            <option value="recurring" {{ old('affiliate_commission_scope', $commissionScope ?? 'first_payment') === 'recurring' ? 'selected' : '' }}>{{ __('Recurring') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Commission Type') }}</label>
                        <select name="affiliate_commission_type" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                            <option value="percent" {{ old('affiliate_commission_type', $commissionType ?? 'percent') === 'percent' ? 'selected' : '' }}>{{ __('Percent') }}</option>
                            <option value="fixed" {{ old('affiliate_commission_type', $commissionType ?? 'percent') === 'fixed' ? 'selected' : '' }}>{{ __('Fixed') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Percent Rate') }}</label>
                        <input type="number" step="0.01" min="0" max="100" name="affiliate_commission_rate_percent" value="{{ old('affiliate_commission_rate_percent', $commissionRatePercent ?? 20) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Used when commission type is percent (can be overridden per affiliate).') }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Fixed Amount') }}</label>
                        <input type="number" step="0.01" min="0" name="affiliate_commission_fixed_amount" value="{{ old('affiliate_commission_fixed_amount', $commissionFixedAmount ?? '10.00') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Used when commission type is fixed.') }}</div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <x-button type="submit" variant="primary">{{ __('Save Settings') }}</x-button>
                </div>
            </div>
        </x-card>
    </form>
</div>
@endsection
