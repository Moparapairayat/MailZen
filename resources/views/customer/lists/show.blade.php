@extends('layouts.customer')

@section('title', $list->display_name ?? $list->name)
@section('page-title', $list->display_name ?? $list->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $list->display_name ?? $list->name }}</h2>
            @if($list->description)
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $list->description }}</p>
            @endif
        </div>
        <div class="flex gap-3">
            @customercan('lists.permissions.can_edit_lists')
                <a href="{{ route('customer.lists.edit', $list) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
                    Edit
                </a>
                <a href="{{ route('customer.lists.settings', $list) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
                    Settings
                </a>
            @endcustomercan
            <a href="{{ route('customer.lists.subscribers.index', $list) }}" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700">
                Manage Subscribers
            </a>
            <a href="{{ route('customer.lists.forms.index', $list) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
                Forms
            </a>
        </div>
    </div>

    @include('customer.lists.partials.subnav', ['list' => $list])

    <!-- Stats -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-4">
        <x-card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Subscribers</div>
            <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($list->subscribers_count) }}</div>
        </x-card>
        <x-card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Confirmed</div>
            <div class="mt-2 text-3xl font-bold text-green-600 dark:text-green-400">{{ number_format($list->confirmed_subscribers_count) }}</div>
        </x-card>
        <x-card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Unsubscribed</div>
            <div class="mt-2 text-3xl font-bold text-gray-600 dark:text-gray-400">{{ number_format($list->unsubscribed_count) }}</div>
        </x-card>
        <x-card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Bounced</div>
            <div class="mt-2 text-3xl font-bold text-red-600 dark:text-red-400">{{ number_format($list->bounced_count) }}</div>
        </x-card>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Campaigns</div>
            <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ number_format((int) ($listPerformance['campaigns_count'] ?? 0)) }}</div>
        </x-card>
        <x-card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Delivered</div>
            <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ number_format((int) ($listPerformance['delivered_count'] ?? 0)) }}</div>
        </x-card>
        <x-card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Open Rate</div>
            <div class="mt-2 text-3xl font-bold text-blue-600 dark:text-blue-400">{{ number_format((float) ($listPerformance['open_rate'] ?? 0), 2) }}%</div>
        </x-card>
        <x-card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Click Rate</div>
            <div class="mt-2 text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ number_format((float) ($listPerformance['click_rate'] ?? 0), 2) }}%</div>
        </x-card>
    </div>

    <!-- List Details -->
    <x-card title="List Details">
        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $list->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                        {{ $list->status === 'inactive' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' : '' }}
                        {{ $list->status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                    ">
                        {{ ucfirst($list->status) }}
                    </span>
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Opt-in Type</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($list->opt_in) }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">From Name</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $list->from_name ?? 'N/A' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">From Email</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $list->from_email ?? 'N/A' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $list->created_at->format('M d, Y') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Subscriber</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ $list->last_subscriber_at ? $list->last_subscriber_at->format('M d, Y') : 'Never' }}
                </dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tags</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    @if(is_array($list->tags ?? null) && count($list->tags) > 0)
                        <div class="flex flex-wrap gap-2">
                            @foreach($list->tags as $tag)
                                <span class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs text-gray-700 dark:text-gray-200">{{ $tag }}</span>
                            @endforeach
                        </div>
                    @else
                        <span class="text-gray-500 dark:text-gray-400">No tags assigned.</span>
                    @endif
                </dd>
            </div>
        </dl>
    </x-card>

    <x-card title="Recent Campaign Activity" :padding="false" class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Campaign</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Sent</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Opened</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Clicked</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Created</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($recentCampaigns as $campaign)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $campaign->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ ucfirst((string) $campaign->status) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ number_format((int) $campaign->sent_count) }}</td>
                            <td class="px-4 py-3 text-sm text-blue-600 dark:text-blue-400">{{ number_format((int) $campaign->opened_count) }}</td>
                            <td class="px-4 py-3 text-sm text-indigo-600 dark:text-indigo-400">{{ number_format((int) $campaign->clicked_count) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ optional($campaign->created_at)->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No campaign activity yet for this list.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</div>
@endsection

