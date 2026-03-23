@extends('layouts.admin')

@section('title', __('Campaigns'))
@section('page-title', __('Campaigns'))

@section('content')
<div class="space-y-6">
    <!-- Filters -->
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <form method="GET" action="{{ route('admin.campaigns.index') }}" class="w-full lg:flex-1 lg:max-w-4xl flex flex-col gap-2 lg:flex-row">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="{{ __('Search campaigns...') }}"
                class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
            >
            <select
                name="status"
                class="w-full lg:w-auto rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
            >
                <option value="">{{ __('All Statuses') }}</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                <option value="sending" {{ request('status') === 'sending' ? 'selected' : '' }}>{{ __('Sending') }}</option>
                <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>{{ __('Sent') }}</option>
            </select>
            <select
                name="customer_id"
                class="w-full lg:w-auto rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
            >
                <option value="">{{ __('All Customers') }}</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                        {{ $customer->full_name }} ({{ $customer->email }})
                    </option>
                @endforeach
            </select>
            <x-button type="submit" variant="primary" class="w-full lg:w-auto">{{ __('Filter') }}</x-button>
        </form>
    </div>

    <!-- Campaigns Table -->
    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Campaign') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Customer') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Recipients') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Performance') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Created') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($campaigns as $campaign)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $campaign->name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $campaign->subject }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <div>{{ $campaign->customer->full_name }}</div>
                                <div class="text-xs">{{ $campaign->customer->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColors = [
                                        'draft' => 'bg-white/10 text-admin-text-secondary',
                                        'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                        'sending' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                        'sent' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                    ];
                                @endphp
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$campaign->status] ?? 'bg-white/10 text-admin-text-secondary' }}">
                                    {{ __(ucfirst($campaign->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ number_format($campaign->sent_count ?? 0) }} / {{ number_format($campaign->total_recipients ?? 0) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                @if($campaign->sent_count > 0)
                                    <div>{{ __('Opens:') }} {{ number_format($campaign->open_rate ?? 0, 1) }}%</div>
                                    <div>{{ __('Clicks:') }} {{ number_format($campaign->click_rate ?? 0, 1) }}%</div>
                                    <div>{{ __('Replies:') }} {{ number_format($campaign->replied_count ?? 0) }}</div>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $campaign->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <x-button href="{{ route('admin.campaigns.show', $campaign) }}" variant="table" size="action" :pill="true" class="p-2" title="{{ __('View') }}" aria-label="{{ __('View') }}"><x-lucide name="eye" class="h-4 w-4" /><span class="sr-only">{{ __('View') }}</span></x-button>
                                    <form method="POST" action="{{ route('admin.campaigns.destroy', $campaign) }}" class="inline" onsubmit="return confirm(@json(__('Are you sure?')));">
                                        @csrf
                                        @method('DELETE')
                                        <x-button type="submit" variant="table-danger" size="action" :pill="true" class="p-2" title="{{ __('Delete') }}" aria-label="{{ __('Delete') }}"><x-lucide name="trash-2" class="h-4 w-4" /><span class="sr-only">{{ __('Delete') }}</span></x-button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                {{ __('No campaigns found.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($campaigns->hasPages())
            <div class="px-6 py-4 border-t border-admin-border">
                {{ $campaigns->links() }}
            </div>
        @endif
    </x-card>
</div>
@endsection

