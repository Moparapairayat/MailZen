@extends('layouts.customer')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <x-card>
        <div class="flex items-center justify-between">
            <div>
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Your local time</div>
                <div class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ ($customerLocalTime ?? now())->format('D, M j, Y g:i A') }}
                </div>
                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Timezone: {{ $customerTimezone ?? config('app.timezone') }}
                </div>
            </div>

            <div class="text-xs">
                <a href="{{ route('customer.settings.index') }}" class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                    Change timezone
                </a>
            </div>
        </div>
    </x-card>

    @if(auth()->guard('customer')->user()->status === 'pending')
        <div class="p-4 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg dark:bg-yellow-900/50 dark:border-yellow-800 dark:text-yellow-200">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium">Account Pending Approval</h3>
                    <div class="mt-2 text-sm">
                        <p>Your account is pending approval. Some features may be limited until an administrator approves your account.</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-card>
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-primary-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Email Lists</dt>
                        <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ number_format($emailListsCount ?? 0) }}
                        </dd>
                    </dl>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Subscribers</dt>
                        <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ number_format($subscribersCount ?? 0) }}
                        </dd>
                    </dl>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Campaigns</dt>
                        <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ number_format($campaignsCount ?? 0) }}
                        </dd>
                    </dl>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Quota Used</dt>
                        <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ number_format(auth()->guard('customer')->user()->quota_usage, 2) }} / {{ number_format(auth()->guard('customer')->user()->quota, 2) }}
                        </dd>
                    </dl>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Recent Activity -->
    <x-card title="Recent Activity">
        @if(($recentCampaigns ?? collect())->isEmpty() && ($recentSubscribers ?? collect())->isEmpty())
            <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                <p>No recent activity</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Recent Campaigns</h3>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($recentCampaigns as $campaign)
                            <li class="py-3 flex items-center justify-between">
                                <div class="min-w-0">
                                    <a href="{{ route('customer.campaigns.show', $campaign) }}" class="text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 truncate">
                                        {{ $campaign->name }}
                                    </a>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Status: <span class="uppercase">{{ $campaign->status }}</span>
                                        @if($campaign->created_at)
                                            • Created {{ $campaign->created_at->diffForHumans() }}
                                        @endif
                                    </p>
                                </div>
                            </li>
                        @empty
                            <li class="py-3 text-sm text-gray-500 dark:text-gray-400">
                                No recent campaigns.
                            </li>
                        @endforelse
                    </ul>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Recent Subscribers</h3>
                        @if(($recentSubscribers ?? collect())->isNotEmpty())
                            <a href="{{ route('customer.lists.subscribers.index', ['list' => $recentSubscribers->first()->list_id]) }}"
                               class="text-xs font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                                Show all
                            </a>
                        @endif
                    </div>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($recentSubscribers as $subscriber)
                            <li class="py-3 flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $subscriber->full_name }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $subscriber->email }}
                                        @if($subscriber->created_at)
                                            • Joined {{ $subscriber->created_at->diffForHumans() }}
                                        @endif
                                    </p>
                                </div>
                            </li>
                        @empty
                            <li class="py-3 text-sm text-gray-500 dark:text-gray-400">
                                No recent subscribers.
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        @endif
    </x-card>
</div>
@endsection

