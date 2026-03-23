@extends('layouts.admin')

@section('title', __('Coupons'))
@section('page-title', __('Coupons'))

@section('content')
<div class="space-y-4">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between lg:gap-0">
        <form method="GET" action="{{ route('admin.coupons.index') }}" class="w-full lg:w-auto flex flex-col gap-2 lg:flex-row lg:items-center">
            <input
                type="text"
                name="q"
                value="{{ $search }}"
                placeholder="{{ __('Search by code/name') }}"
                class="block w-full lg:w-72 rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
            >
            <x-button type="submit" variant="secondary" class="w-full lg:w-auto">{{ __('Search') }}</x-button>
            <x-button href="{{ route('admin.coupons.index') }}" variant="secondary" class="w-full lg:w-auto">{{ __('Reset') }}</x-button>
        </form>

        @admincan('admin.coupons.create')
            <x-button href="{{ route('admin.coupons.create') }}" variant="primary" class="w-full lg:w-auto">{{ __('Create Coupon') }}</x-button>
        @endadmincan
    </div>

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Code') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Type') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Value') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Duration') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Active') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($coupons as $coupon)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ $coupon->code }}
                                @if($coupon->name)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $coupon->name }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                {{ $coupon->type }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                @if($coupon->type === 'percent')
                                    {{ number_format((float) $coupon->percent_off, 2) }}%
                                @else
                                    {{ strtoupper((string) $coupon->currency) }} {{ number_format((float) $coupon->amount_off, 2) }}
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                {{ $coupon->duration }}
                                @if($coupon->duration === 'repeating' && $coupon->duration_in_months)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $coupon->duration_in_months }} {{ __('months') }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $coupon->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $coupon->is_active ? __('Active') : __('Disabled') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                @admincan('admin.coupons.edit')
                                    <x-button href="{{ route('admin.coupons.edit', $coupon) }}" variant="table" size="action" :pill="true" class="p-2" title="{{ __('Edit') }}" aria-label="{{ __('Edit') }}"><x-lucide name="pencil" class="h-4 w-4" /><span class="sr-only">{{ __('Edit') }}</span></x-button>
                                @endadmincan
                                @admincan('admin.coupons.delete')
                                    <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" class="inline" onsubmit="return confirm(@json(__('Delete coupon?')));">
                                        @csrf
                                        @method('DELETE')
                                        <x-button type="submit" variant="table-danger" size="action" :pill="true" class="p-2" title="{{ __('Delete') }}" aria-label="{{ __('Delete') }}"><x-lucide name="trash-2" class="h-4 w-4" /><span class="sr-only">{{ __('Delete') }}</span></x-button>
                                    </form>
                                @endadmincan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('No coupons found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($coupons->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">{{ $coupons->links() }}</div>
        @endif
    </x-card>
</div>
@endsection
