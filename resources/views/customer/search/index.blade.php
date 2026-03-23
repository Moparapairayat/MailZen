@extends('layouts.customer')

@section('title', 'Search')
@section('page-title', 'Search')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Search</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Search across your campaigns, lists, subscribers, templates and transactional emails.
                </p>
            </div>
            <form action="{{ route('customer.search.index') }}" method="GET" class="w-full max-w-md">
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
                {{-- Campaigns --}}
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl">
                    <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Campaigns</h3>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $results['campaigns']->count() }} results</span>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($results['campaigns'] as $campaign)
                            <a href="{{ route('customer.campaigns.show', $campaign) }}" class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50">
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

                {{-- Lists --}}
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl">
                    <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Email lists</h3>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $results['lists']->count() }} results</span>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($results['lists'] as $list)
                            <a href="{{ route('customer.lists.show', $list) }}" class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50">
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

                {{-- Subscribers --}}
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl">
                    <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Subscribers</h3>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $results['subscribers']->count() }} results</span>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($results['subscribers'] as $subscriber)
                            <a href="{{ route('customer.lists.subscribers.show', [$subscriber->emailList, $subscriber]) }}" class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $subscriber->email }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                        {{ trim($subscriber->first_name . ' ' . $subscriber->last_name) ?: 'No name' }} · List: {{ $subscriber->emailList->name }}
                                    </p>
                                </div>
                                <p class="text-xs text-gray-400">
                                    {{ $subscriber->created_at->diffForHumans() }}
                                </p>
                            </a>
                        @empty
                            <p class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400">
                                No subscribers found.
                            </p>
                        @endforelse
                    </div>
                </div>

                {{-- Templates --}}
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl">
                    <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Templates</h3>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $results['templates']->count() }} results</span>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($results['templates'] as $template)
                            <a href="{{ route('customer.templates.show', $template) }}" class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $template->name }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                        Type: {{ $template->type }}
                                    </p>
                                </div>
                                <p class="text-xs text-gray-400">
                                    {{ $template->created_at->diffForHumans() }}
                                </p>
                            </a>
                        @empty
                            <p class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400">
                                No templates found.
                            </p>
                        @endforelse
                    </div>
                </div>

                {{-- Transactional emails --}}
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl">
                    <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Transactional emails</h3>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $results['transactionalEmails']->count() }} results</span>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($results['transactionalEmails'] as $email)
                            <a href="{{ route('customer.transactional-emails.show', $email) }}" class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $email->subject }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                        Key: {{ $email->key }}
                                    </p>
                                </div>
                                <p class="text-xs text-gray-400">
                                    {{ $email->created_at->diffForHumans() }}
                                </p>
                            </a>
                        @empty
                            <p class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400">
                                No transactional emails found.
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection


