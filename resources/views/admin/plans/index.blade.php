@extends('layouts.admin')

@section('title', __('Plans'))
@section('page-title', __('Plans'))

@section('content')
@if(isset($pricingSettings))
<x-card class="mb-6">
    <form method="POST" action="{{ route('admin.plans.pricing-settings.update') }}" class="space-y-4">
        @csrf
        <div class="text-base font-semibold">{{ __('Pricing Section') }}</div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Badge') }}</label>
                <input type="text" name="badge" value="{{ old('badge', $pricingSettings['badge'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Title') }}</label>
                <input type="text" name="title" value="{{ old('title', $pricingSettings['title'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Subtitle') }}</label>
            <input type="text" name="subtitle" value="{{ old('subtitle', $pricingSettings['subtitle'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Toggle Monthly') }}</label>
                <input type="text" name="toggle_monthly" value="{{ old('toggle_monthly', $pricingSettings['toggle_monthly'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Toggle Annual') }}</label>
                <input type="text" name="toggle_annual" value="{{ old('toggle_annual', $pricingSettings['toggle_annual'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Toggle Save Label') }}</label>
                <input type="text" name="toggle_save" value="{{ old('toggle_save', $pricingSettings['toggle_save'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Pricing Columns') }}</label>
                <select name="columns" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                    @foreach([1,2,3,4,5] as $n)
                        <option value="{{ $n }}" @selected((int) old('columns', (int) ($pricingSettings['columns'] ?? 3)) === $n)>{{ $n }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Popular Badge') }}</label>
                <input type="text" name="popular_badge" value="{{ old('popular_badge', $pricingSettings['popular_badge'] ?? '') }}" class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
            </div>
        </div>

        <div class="flex items-center gap-2">
            <input type="hidden" name="show_all" value="0">
            <input type="checkbox" name="show_all" value="1" @checked((bool) old('show_all', (bool) ($pricingSettings['show_all'] ?? false)))>
            <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Show all plans together') }}</span>
        </div>

        <div class="flex justify-end">
            <x-button type="submit" variant="primary">{{ __('Save Pricing Section') }}</x-button>
        </div>
    </form>
</x-card>
@endif

<div class="flex items-center justify-between mb-4">
    <h2 class="text-lg font-semibold">{{ __('Subscription Plans') }}</h2>
    @admincan('admin.plans.create')
        <x-button href="{{ route('admin.plans.create') }}" variant="primary">{{ __('Create Plan') }}</x-button>
    @endadmincan
    </div>

<x-card :padding="false">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Name') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Price') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Billing') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Customer Group') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Stripe Price') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Active') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Popular') }}</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($plans as $plan)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $plan->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $plan->currency }} {{ number_format($plan->price, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ ucfirst($plan->billing_cycle) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $plan->customerGroup?->name ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $plan->stripe_price_id ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $plan->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $plan->is_active ? __('Active') : __('Disabled') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $plan->is_popular ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $plan->is_popular ? __('Yes') : __('No') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                            @admincan('admin.plans.edit')
                                <x-button href="{{ route('admin.plans.edit', $plan) }}" variant="table" size="action" :pill="true">{{ __('Edit') }}</x-button>
                            @endadmincan
                            @admincan('admin.plans.delete')
                                <form method="POST" action="{{ route('admin.plans.destroy', $plan) }}" class="inline" onsubmit="return confirm(@json(__('Delete plan?')));">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="submit" variant="table-danger" size="action" :pill="true">{{ __('Delete') }}</x-button>
                                </form>
                            @endadmincan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('No plans found.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($plans->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">{{ $plans->links() }}</div>
    @endif
</x-card>
@endsection

