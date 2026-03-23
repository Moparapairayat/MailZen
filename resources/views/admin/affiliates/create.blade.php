@extends('layouts.admin')

@section('title', __('Add New Affiliate'))
@section('page-title', __('Add New Affiliate'))

@section('content')
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

<div class="mt-4 grid grid-cols-1 gap-6 lg:grid-cols-2">
    <x-card title="{{ __('Add New Affiliate') }}">
        <form method="POST" action="{{ route('admin.affiliates.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Customer Email') }}</label>
                <input name="customer_email" value="{{ old('customer_email') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" placeholder="customer@mailzen.com">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Affiliate Code') }}</label>
                    <input name="code" value="{{ old('code') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" placeholder="ALEX">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Status') }}</label>
                    <select name="status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                        <option value="pending" @selected(old('status', 'pending') === 'pending')>{{ __('Pending') }}</option>
                        <option value="approved" @selected(old('status') === 'approved')>{{ __('Approved') }}</option>
                        <option value="blocked" @selected(old('status') === 'blocked')>{{ __('Blocked') }}</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Destination URL') }}</label>
                <input name="destination_url" value="{{ old('destination_url') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" placeholder="https://example.com">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Commission (%)') }}</label>
                <input type="number" step="0.01" min="0" max="100" name="commission_rate_percent" value="{{ old('commission_rate_percent') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" placeholder="20">
                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('If set, overrides the global percent commission setting.') }}</div>
            </div>

            <div class="border-t border-admin-border pt-4">
                <div class="text-sm font-semibold text-admin-text-primary">{{ __('Payout Details') }}</div>
                <div class="mt-3 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Payout Method') }}</label>
                        <select name="payout_method" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                            <option value="bank_transfer" {{ old('payout_method', 'bank_transfer') === 'bank_transfer' ? 'selected' : '' }}>{{ __('Bank Transfer') }}</option>
                            <option value="paypal" {{ old('payout_method') === 'paypal' ? 'selected' : '' }}>{{ __('PayPal') }}</option>
                            <option value="payoneer" {{ old('payout_method') === 'payoneer' ? 'selected' : '' }}>{{ __('Payoneer') }}</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="md:col-span-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Bank Transfer') }}</div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Account Holder Name') }}</label>
                            <input name="bank_account_holder_name" value="{{ old('bank_account_holder_name') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Bank Name') }}</label>
                            <input name="bank_name" value="{{ old('bank_name') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Account Number') }}</label>
                            <input name="bank_account_number" value="{{ old('bank_account_number') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Country') }}</label>
                            <input name="bank_country" value="{{ old('bank_country') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('IBAN') }}</label>
                            <input name="bank_iban" value="{{ old('bank_iban') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('SWIFT') }}</label>
                            <input name="bank_swift" value="{{ old('bank_swift') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                        </div>

                        <div class="md:col-span-2 border-t border-admin-border pt-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('PayPal') }}</div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('PayPal Email') }}</label>
                            <input name="paypal_email" value="{{ old('paypal_email') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" placeholder="name@example.com">
                        </div>

                        <div class="md:col-span-2 border-t border-admin-border pt-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Payoneer') }}</div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Payoneer Email') }}</label>
                            <input name="payoneer_email" value="{{ old('payoneer_email') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" placeholder="name@example.com">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Payoneer Account ID') }}</label>
                            <input name="payoneer_account_id" value="{{ old('payoneer_account_id') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2">
                <x-button href="{{ route('admin.affiliates.index') }}" variant="secondary">{{ __('Back') }}</x-button>
                <x-button type="submit" variant="primary">{{ __('Save') }}</x-button>
            </div>
        </form>
    </x-card>

    <x-card title="{{ __('Preview') }}">
        <div class="text-sm text-admin-text-secondary">
            {{ __('Affiliate link will be:') }}
        </div>
        <div class="mt-2 text-sm text-admin-text-primary break-all">
            {{ url('/?ref=CODE') }}
        </div>
    </x-card>
</div>
@endsection
