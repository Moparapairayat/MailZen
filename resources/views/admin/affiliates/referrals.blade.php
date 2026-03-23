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

    <form method="GET" action="{{ route('admin.affiliates.referrals') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <input type="text" name="q" value="{{ $search }}" placeholder="{{ __('Search affiliate or customer') }}" class="block w-full sm:w-96 rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
        <div class="flex items-center gap-2">
            <x-button type="submit" variant="secondary">{{ __('Search') }}</x-button>
            <x-button href="{{ route('admin.affiliates.referrals') }}" variant="secondary">{{ __('Reset') }}</x-button>
        </div>
    </form>

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Affiliate') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Visitor') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Referred Customer') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Date') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($referrals as $ref)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                <div class="font-medium">{{ $ref->affiliate?->code ?? '—' }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $ref->affiliate?->customer?->email ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ $ref->visitor_id ?: '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                @if($ref->referred_customer_id)
                                    <div>{{ $ref->referredCustomer?->full_name ?? '—' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $ref->referredCustomer?->email ?? '' }}</div>
                                @else
                                    <span class="text-gray-500 dark:text-gray-400">{{ __('Not registered') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200 text-right">{{ ($ref->referred_at ?? $ref->created_at)?->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('No referrals found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($referrals->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">{{ $referrals->links() }}</div>
        @endif
    </x-card>
</div>
@endsection
