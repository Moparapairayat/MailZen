@extends('layouts.admin')

@section('title', __('Email Lists'))
@section('page-title', __('Email Lists'))

@section('content')
<div class="space-y-6">
    <!-- Filters -->
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <form method="GET" action="{{ route('admin.lists.index') }}" class="w-full lg:flex-1 lg:max-w-4xl flex flex-col gap-2 lg:flex-row">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="{{ __('Search lists...') }}"
                class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
            >
            <select
                name="status"
                class="w-full lg:w-auto rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
            >
                <option value="">{{ __('All Statuses') }}</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
            </select>
            <select
                name="customer_id"
                class="w-full lg:w-auto rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
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

    <!-- Lists Table -->
    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Name') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Customer') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Subscribers') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Created') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($emailLists as $list)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    <a href="{{ route('admin.lists.show', $list) }}" class="hover:text-primary-600 dark:hover:text-primary-400">
                                        {{ $list->display_name ?? $list->name }}
                                    </a>
                                </div>
                                @if($list->description)
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ Str::limit($list->description, 50) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <div>{{ $list->customer?->full_name ?? '—' }}</div>
                                <div class="text-xs">{{ $list->customer?->email ?? '—' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ number_format($list->subscribers_count) }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ number_format($list->confirmed_subscribers_count) }} {{ __('confirmed') }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $list->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : ($list->status === 'inactive' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200') }}">
                                    {{ __(ucfirst($list->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $list->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <x-button href="{{ route('admin.lists.show', $list) }}" variant="table" size="action" :pill="true" class="p-2" title="{{ __('View') }}" aria-label="{{ __('View') }}"><x-lucide name="eye" class="h-4 w-4" /><span class="sr-only">{{ __('View') }}</span></x-button>
                                    @admincan('admin.lists.delete')
                                        <form method="POST" action="{{ route('admin.lists.destroy', $list) }}" class="inline" onsubmit="return confirm(@json(__('Are you sure?')));">
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
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                {{ __('No email lists found.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($emailLists->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $emailLists->links() }}
            </div>
        @endif
    </x-card>
</div>
@endsection

