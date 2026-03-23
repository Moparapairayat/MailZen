@extends('layouts.admin')

@section('title', __('Manual Payments'))
@section('page-title', __('Manual Payments'))

@section('content')
<div class="space-y-4">
    <x-card>
        <form method="GET" action="{{ route('admin.manual-payments.index') }}" class="flex flex-col gap-3 lg:flex-row lg:items-end">
            <div class="flex-1">
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Search') }}</label>
                <input
                    name="q"
                    value="{{ $search }}"
                    placeholder="{{ __('Transfer reference, customer name/email') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                >
            </div>

            <div class="w-full lg:w-56">
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Status') }}</label>
                <select name="status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    <option value="" {{ $status === '' ? 'selected' : '' }}>{{ __('All') }}</option>
                    <option value="initiated" {{ $status === 'initiated' ? 'selected' : '' }}>{{ __('Initiated') }}</option>
                    <option value="submitted" {{ $status === 'submitted' ? 'selected' : '' }}>{{ __('Submitted') }}</option>
                    <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>{{ __('Approved') }}</option>
                    <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <x-button type="submit" variant="primary">{{ __('Filter') }}</x-button>
                <x-button href="{{ route('admin.manual-payments.index') }}" variant="secondary">{{ __('Reset') }}</x-button>
            </div>
        </form>
    </x-card>

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Customer') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Plan') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Amount') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Submitted') }}</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($manualPayments as $payment)
                        @php
                            $badge = match ($payment->status) {
                                'submitted' => 'bg-yellow-100 text-yellow-800',
                                'approved' => 'bg-green-100 text-green-800',
                                'rejected' => 'bg-red-100 text-red-800',
                                default => 'bg-gray-100 text-gray-800',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                <div class="font-medium">{{ $payment->customer?->full_name ?? '—' }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $payment->customer?->email ?? '—' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                {{ $payment->subscription?->plan_name ?? '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                {{ strtoupper((string) ($payment->currency ?? '')) }} {{ number_format((float) $payment->amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $badge }}">
                                    {{ $payment->status ?: '—' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                {{ $payment->submitted_at?->diffForHumans() ?? '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                <x-button href="{{ route('admin.manual-payments.show', $payment) }}" variant="table" size="sm" :pill="true">{{ __('View') }}</x-button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                {{ __('No manual payments found.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4">
            {{ $manualPayments->links() }}
        </div>
    </x-card>
</div>
@endsection
