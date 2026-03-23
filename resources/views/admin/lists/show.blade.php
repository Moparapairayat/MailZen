@extends('layouts.admin')

@section('title', $list->name)
@section('page-title', $list->display_name ?? $list->name)

@section('content')
<div class="space-y-6">
    <!-- List Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $list->display_name ?? $list->name }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $list->description }}</p>
        </div>
        @admincan('admin.lists.delete')
            <form method="POST" action="{{ route('admin.lists.destroy', $list) }}" class="inline" onsubmit="return confirm(@json(__('Are you sure?')));">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="danger">{{ __('Delete') }}</x-button>
            </form>
        @endadmincan
    </div>

    <!-- List Stats -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Total Subscribers') }}</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($list->subscribers_count) }}</div>
        </x-card>
        <x-card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Confirmed') }}</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($list->confirmed_subscribers_count) }}</div>
        </x-card>
        <x-card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Unsubscribed') }}</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($list->unsubscribed_count) }}</div>
        </x-card>
        <x-card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Bounced') }}</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($list->bounced_count) }}</div>
        </x-card>
    </div>

    <!-- List Details -->
    <x-card title="{{ __('List Details') }}">
        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Customer') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    @if($list->customer)
                        <a href="{{ route('admin.customers.show', $list->customer) }}" class="text-primary-600 hover:text-primary-700">
                            {{ $list->customer->full_name }} ({{ $list->customer->email }})
                        </a>
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Status') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    <span class="px-2 py-1 text-xs rounded-full {{ $list->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ __(ucfirst($list->status)) }}
                    </span>
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('From Email') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $list->from_email ?? __('N/A') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('From Name') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $list->from_name ?? __('N/A') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Created') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $list->created_at->format('M d, Y H:i') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Last Subscriber') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $list->last_subscriber_at ? $list->last_subscriber_at->format('M d, Y H:i') : __('Never') }}</dd>
            </div>
        </dl>
    </x-card>

    <!-- Recent Subscribers -->
    @if($list->subscribers->count() > 0)
        <x-card title="{{ __('Recent Subscribers') }}">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Email') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Name') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Status') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Subscribed') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($list->subscribers as $subscriber)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $subscriber->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $subscriber->full_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $subscriber->status === 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ __(ucfirst($subscriber->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $subscriber->created_at->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>
    @endif
</div>
@endsection

