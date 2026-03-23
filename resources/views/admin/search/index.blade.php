@extends('layouts.admin')

@section('title', 'Search')
@section('page-title', 'Search')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Search</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Search across users, customers, campaigns, lists, delivery servers, bounce servers and plans.
                </p>
            </div>
            <form action="{{ route('admin.search.index') }}" method="GET" class="w-full max-w-md">
                <div class="relative">
                    <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                        </svg>
                    </span>
                    <input
                        type="search"
                        name="q"
                        class="block w-full pl-10 pr-4 py-2 text-sm border border-gray-200 rounded-lg bg-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100 dark:placeholder-gray-500"
                        placeholder="Search..."
                        value="{{ $query }}"
                    >
                </div>
            </form>
        </div>

        @if($query === '')
            <div class="bg-white dark:bg-gray-800 border border-dashed border-gray-300 dark:border-gray-700 rounded-xl p-8 text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Start by typing something in the search box above.
                </p>
            </div>
        @else
            <div class="space-y-6">
                {{-- Users --}}
                @admincan('admin.users.access')
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl">
                        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Users</h3>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $results['users']->count() }} results</span>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($results['users'] as $user)
                                <a href="{{ route('admin.users.show', $user) }}" class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $user->full_name }}
                                        </p>
                                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                            {{ $user->email }}
                                        </p>
                                    </div>
                                    <p class="text-xs text-gray-400">
                                        {{ $user->created_at->diffForHumans() }}
                                    </p>
                                </a>
                            @empty
                                <p class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    No users found.
                                </p>
                            @endforelse
                        </div>
                    </div>
                @endadmincan

                {{-- Customers --}}
                @admincan('admin.customers.access')
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl">
                        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Customers</h3>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $results['customers']->count() }} results</span>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($results['customers'] as $customer)
                                <a href="{{ route('admin.customers.show', $customer) }}" class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $customer->full_name }}
                                        </p>
                                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                            {{ $customer->email }} @if($customer->company_name) · {{ $customer->company_name }} @endif
                                        </p>
                                    </div>
                                    <p class="text-xs text-gray-400">
                                        {{ $customer->created_at->diffForHumans() }}
                                    </p>
                                </a>
                            @empty
                                <p class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    No customers found.
                                </p>
                            @endforelse
                        </div>
                    </div>
                @endadmincan

                {{-- Campaigns --}}
                @admincan('admin.campaigns.access')
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl">
                        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Campaigns</h3>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $results['campaigns']->count() }} results</span>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($results['campaigns'] as $campaign)
                                <a href="{{ route('admin.campaigns.show', $campaign) }}" class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $campaign->name }}
                                        </p>
                                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                            Subject: {{ $campaign->subject }}
                                        </p>
                                    </div>
                                    <p class="text-xs text-gray-400">
                                        {{ $campaign->created_at->diffForHumans() }}
                                    </p>
                                </a>
                            @empty
                                <p class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    No campaigns found.
                                </p>
                            @endforelse
                        </div>
                    </div>
                @endadmincan

                {{-- Lists --}}
                @admincan('admin.lists.access')
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl">
                        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Email lists</h3>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $results['lists']->count() }} results</span>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($results['lists'] as $list)
                                <a href="{{ route('admin.lists.show', $list) }}" class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $list->name }}
                                        </p>
                                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                            {{ number_format($list->subscribers_count ?? 0) }} subscribers
                                        </p>
                                    </div>
                                    <p class="text-xs text-gray-400">
                                        {{ $list->created_at->diffForHumans() }}
                                    </p>
                                </a>
                            @empty
                                <p class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    No lists found.
                                </p>
                            @endforelse
                        </div>
                    </div>
                @endadmincan

                {{-- Delivery Servers --}}
                @admincan('admin.delivery_servers.access')
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl">
                        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Delivery Servers</h3>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $results['deliveryServers']->count() }} results</span>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($results['deliveryServers'] as $server)
                                <a href="{{ route('admin.delivery-servers.show', $server) }}" class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $server->name }}
                                        </p>
                                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                            {{ $server->hostname }}
                                        </p>
                                    </div>
                                    <p class="text-xs text-gray-400">
                                        {{ $server->created_at->diffForHumans() }}
                                    </p>
                                </a>
                            @empty
                                <p class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    No delivery servers found.
                                </p>
                            @endforelse
                        </div>
                    </div>
                @endadmincan

                {{-- Bounce Servers --}}
                @admincan('admin.bounce_servers.access')
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl">
                        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Bounce Servers</h3>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $results['bounceServers']->count() }} results</span>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($results['bounceServers'] as $server)
                                <a href="{{ route('admin.bounce-servers.show', $server) }}" class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $server->name }}
                                        </p>
                                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                            {{ $server->hostname }}
                                        </p>
                                    </div>
                                    <p class="text-xs text-gray-400">
                                        {{ $server->created_at->diffForHumans() }}
                                    </p>
                                </a>
                            @empty
                                <p class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    No bounce servers found.
                                </p>
                            @endforelse
                        </div>
                    </div>
                @endadmincan

                {{-- Plans --}}
                @admincan('admin.plans.access')
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl">
                        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Plans</h3>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $results['plans']->count() }} results</span>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($results['plans'] as $plan)
                                <a href="{{ route('admin.plans.show', $plan) }}" class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $plan->name }}
                                        </p>
                                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                            Price: {{ $plan->price ? '$' . number_format($plan->price, 2) : 'Free' }}
                                        </p>
                                    </div>
                                    <p class="text-xs text-gray-400">
                                        {{ $plan->created_at->diffForHumans() }}
                                    </p>
                                </a>
                            @empty
                                <p class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    No plans found.
                                </p>
                            @endforelse
                        </div>
                    </div>
                @endadmincan
            </div>
        @endif
    </div>
@endsection

