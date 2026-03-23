@extends('layouts.customer')

@section('title', 'Campaign Recipients - ' . $campaign->name)
@section('page-title', 'Campaign Recipients')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $campaign->name }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Recipient Details</p>
        </div>
        <x-button href="{{ route('customer.campaigns.show', $campaign) }}" variant="secondary">
            ← Back to Campaign
        </x-button>
    </div>

    <!-- Filters -->
    <x-card>
        <form method="GET" action="{{ route('customer.campaigns.recipients', $campaign) }}" class="flex items-end gap-4">
            <div class="flex-1">
                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                    placeholder="Search by email, name..." 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100">
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                <select name="status" id="status" 
                    class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                    <option value="opened" {{ request('status') === 'opened' ? 'selected' : '' }}>Opened</option>
                    <option value="clicked" {{ request('status') === 'clicked' ? 'selected' : '' }}>Clicked</option>
                    <option value="bounced" {{ request('status') === 'bounced' ? 'selected' : '' }}>Bounced</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            <div>
                <x-button type="submit" variant="primary">Filter</x-button>
            </div>
            @if(request('search') || request('status'))
            <div>
                <a href="{{ route('customer.campaigns.recipients', $campaign) }}" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">
                    Clear
                </a>
            </div>
            @endif
        </form>
    </x-card>

    <!-- Recipients Table -->
    <x-card>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Sent At</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Opened At</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Clicked At</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Error</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($recipients as $recipient)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                            {{ $recipient->email }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                            {{ $recipient->first_name }} {{ $recipient->last_name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                {{ $recipient->status === 'sent' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : '' }}
                                {{ $recipient->status === 'opened' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                {{ $recipient->status === 'clicked' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : '' }}
                                {{ $recipient->status === 'bounced' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}
                                {{ $recipient->status === 'failed' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}
                                {{ $recipient->status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                            ">
                                {{ ucfirst($recipient->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $recipient->sent_at ? $recipient->sent_at->format('M d, Y H:i') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $recipient->opened_at ? $recipient->opened_at->format('M d, Y H:i') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <div>
                                {{ $recipient->clicked_at ? $recipient->clicked_at->format('M d, Y H:i') : '-' }}
                            </div>
                            @if($recipient->logs->count() > 0)
                                <div class="mt-1 space-y-1">
                                    @foreach($recipient->logs as $log)
                                        <div class="text-xs">
                                            <a href="{{ $log->url }}" target="_blank" class="text-blue-600 hover:underline dark:text-blue-400">
                                                {{ \Illuminate\Support\Str::limit($log->url, 60) }}
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-red-600 dark:text-red-400">
                            @if($recipient->failure_reason)
                                <span class="truncate max-w-xs block" title="{{ $recipient->failure_reason }}">
                                    {{ \Illuminate\Support\Str::limit($recipient->failure_reason, 50) }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            No recipients found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($recipients->hasPages())
        <div class="mt-4">
            {{ $recipients->links() }}
        </div>
        @endif
    </x-card>
</div>
@endsection

