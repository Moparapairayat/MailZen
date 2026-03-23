@extends('layouts.customer')

@section('title', __('Affiliate'))
@section('page-title', __('Affiliate'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <a href="{{ route('customer.affiliate.index') }}" class="px-3 py-2 rounded-lg text-sm border border-admin-border hover:bg-white/5 {{ request()->routeIs('customer.affiliate.index') ? 'bg-white/10 text-admin-text-primary' : 'text-admin-text-secondary' }}">{{ __('Home') }}</a>
            <a href="{{ route('customer.affiliate.payments') }}" class="px-3 py-2 rounded-lg text-sm border border-admin-border hover:bg-white/5 {{ request()->routeIs('customer.affiliate.payments') ? 'bg-white/10 text-admin-text-primary' : 'text-admin-text-secondary' }}">{{ __('Payouts') }}</a>
        </div>

        @if($affiliate)
            <div class="text-xs text-admin-text-secondary">{{ __('Code') }}: <span class="text-admin-text-primary font-medium">{{ $affiliate->code }}</span></div>
        @endif
    </div>

    @php
        $payoutDetails = is_array($affiliate?->payout_details) ? $affiliate->payout_details : [];
        $payoutMethod = old('payout_method', data_get($payoutDetails, 'method', 'bank_transfer'));
    @endphp

    <x-card title="{{ __('Payout Settings') }}">
        <form method="POST" action="{{ route('customer.affiliate.payout-settings.update') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Payout Method') }}</label>
                <select name="payout_method" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    <option value="bank_transfer" {{ $payoutMethod === 'bank_transfer' ? 'selected' : '' }}>{{ __('Bank Transfer') }}</option>
                    <option value="paypal" {{ $payoutMethod === 'paypal' ? 'selected' : '' }}>{{ __('PayPal') }}</option>
                    <option value="payoneer" {{ $payoutMethod === 'payoneer' ? 'selected' : '' }}>{{ __('Payoneer') }}</option>
                </select>
                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('This is where we will send your affiliate earnings. It is not related to how you pay for your subscription.') }}</div>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <div class="lg:col-span-2 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Bank Transfer Details') }}</div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Account Holder Name') }}</label>
                    <input name="bank_account_holder_name" value="{{ old('bank_account_holder_name', data_get($payoutDetails, 'bank.account_holder_name')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Bank Name') }}</label>
                    <input name="bank_name" value="{{ old('bank_name', data_get($payoutDetails, 'bank.bank_name')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Account Number') }}</label>
                    <input name="bank_account_number" value="{{ old('bank_account_number', data_get($payoutDetails, 'bank.account_number')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Country') }}</label>
                    <input name="bank_country" value="{{ old('bank_country', data_get($payoutDetails, 'bank.country')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('IBAN') }}</label>
                    <input name="bank_iban" value="{{ old('bank_iban', data_get($payoutDetails, 'bank.iban')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('SWIFT') }}</label>
                    <input name="bank_swift" value="{{ old('bank_swift', data_get($payoutDetails, 'bank.swift')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                </div>

                <div class="lg:col-span-2 border-t border-gray-200 dark:border-gray-700 pt-4 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('PayPal Details') }}</div>
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('PayPal Email') }}</label>
                    <input name="paypal_email" value="{{ old('paypal_email', data_get($payoutDetails, 'paypal.email')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" placeholder="name@example.com">
                </div>

                <div class="lg:col-span-2 border-t border-gray-200 dark:border-gray-700 pt-4 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Payoneer Details') }}</div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Payoneer Email') }}</label>
                    <input name="payoneer_email" value="{{ old('payoneer_email', data_get($payoutDetails, 'payoneer.email')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" placeholder="name@example.com">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Payoneer Account ID') }}</label>
                    <input name="payoneer_account_id" value="{{ old('payoneer_account_id', data_get($payoutDetails, 'payoneer.account_id')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                </div>
            </div>

            <div class="flex justify-end">
                <x-button type="submit" variant="primary">{{ __('Save Payout Settings') }}</x-button>
            </div>
        </form>
    </x-card>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card title="{{ __('Payouts') }}" :padding="false">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Amount') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Created') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($payouts as $payout)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                    {{ strtoupper((string) $payout->currency) }} {{ number_format((float) $payout->amount, 2) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200">{{ $payout->status }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200 text-right">{{ optional($payout->created_at)->format('Y-m-d') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-6 text-sm text-gray-500 dark:text-gray-400">{{ __('No payouts yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($payouts->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">{{ $payouts->links() }}</div>
            @endif
        </x-card>

        <x-card title="{{ __('Commissions') }}" :padding="false">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Amount') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Created') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($commissions as $commission)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                    {{ strtoupper((string) $commission->commission_currency) }} {{ number_format((float) $commission->commission_amount, 2) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200">{{ $commission->status }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200 text-right">{{ optional($commission->created_at)->format('Y-m-d') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-6 text-sm text-gray-500 dark:text-gray-400">{{ __('No commissions yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($commissions->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">{{ $commissions->appends(['commissions_page' => $commissions->currentPage()])->links() }}</div>
            @endif
        </x-card>
    </div>
</div>
@endsection
