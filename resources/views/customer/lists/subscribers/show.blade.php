@extends('layouts.customer')

@section('title', 'Subscriber Details')
@section('page-title', 'Subscriber Details')

@section('content')
<div class="max-w-4xl space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $subscriber->email }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $list->name }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('customer.lists.subscribers.index', ['list' => $list] + request()->query()) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
                Back to Subscribers
            </a>
            <a href="{{ route('customer.lists.subscribers.edit', [$list, $subscriber]) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
                Edit
            </a>
            @if($subscriber->status === 'unconfirmed')
                <form method="POST" action="{{ route('customer.lists.subscribers.resend-confirmation', [$list, $subscriber]) }}" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700">
                        Send Confirmation Email
                    </button>
                </form>
                <form method="POST" action="{{ route('customer.lists.subscribers.confirm', [$list, $subscriber]) }}" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700">
                        Confirm
                    </button>
                </form>
            @endif
            @if($subscriber->status !== 'unsubscribed')
                <form method="POST" action="{{ route('customer.lists.subscribers.unsubscribe', [$list, $subscriber]) }}" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700">
                        Unsubscribe
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Subscriber Details -->
    <x-card title="Subscriber Information">
        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $subscriber->email }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                <dd class="mt-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $subscriber->status === 'confirmed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                        {{ $subscriber->status === 'unconfirmed' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                        {{ $subscriber->status === 'unsubscribed' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' : '' }}
                        {{ $subscriber->status === 'bounced' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}
                    ">
                        {{ ucfirst($subscriber->status) }}
                    </span>
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">First Name</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $subscriber->first_name ?? 'N/A' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Name</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $subscriber->last_name ?? 'N/A' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Source</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $subscriber->source ?? 'N/A' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">IP Address</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $subscriber->ip_address ?? 'N/A' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Subscribed At</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ $subscriber->subscribed_at ? $subscriber->subscribed_at->format('M d, Y H:i') : 'N/A' }}
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Confirmed At</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ $subscriber->confirmed_at ? $subscriber->confirmed_at->format('M d, Y H:i') : 'N/A' }}
                </dd>
            </div>
            @if($subscriber->unsubscribed_at)
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Unsubscribed At</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                        {{ $subscriber->unsubscribed_at->format('M d, Y H:i') }}
                    </dd>
                </div>
            @endif
        </dl>
    </x-card>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-5">
        <x-card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Campaigns</div>
            <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ number_format((int) ($contactPerformance['total_campaigns'] ?? 0)) }}</div>
        </x-card>
        <x-card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Sent</div>
            <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ number_format((int) ($contactPerformance['sent_count'] ?? 0)) }}</div>
        </x-card>
        <x-card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Open Rate</div>
            <div class="mt-2 text-3xl font-bold text-blue-600 dark:text-blue-400">{{ number_format((float) ($contactPerformance['open_rate'] ?? 0), 2) }}%</div>
        </x-card>
        <x-card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Click Rate</div>
            <div class="mt-2 text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ number_format((float) ($contactPerformance['click_rate'] ?? 0), 2) }}%</div>
        </x-card>
        <x-card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Bounced</div>
            <div class="mt-2 text-3xl font-bold text-red-600 dark:text-red-400">{{ number_format((int) ($contactPerformance['bounced_count'] ?? 0)) }}</div>
        </x-card>
    </div>

    <x-card title="Activity History" :padding="false" class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Time</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Event</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Campaign</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Details</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">IP</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($activityHistory as $event)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ optional($event['occurred_at'] ?? null)->format('M d, Y H:i') ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ ucfirst((string) ($event['event'] ?? 'unknown')) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $event['campaign_name'] ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                @if(!empty($event['url']))
                                    <div class="truncate max-w-xs">{{ $event['url'] }}</div>
                                @endif
                                @if(!empty($event['details']))
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $event['details'] }}</div>
                                @endif
                                @if(empty($event['url']) && empty($event['details']))
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $event['ip_address'] ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No tracked activity yet for this contact.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    @if($subscriber->notes)
        <x-card title="Notes">
            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $subscriber->notes }}</p>
        </x-card>
    @endif
</div>
@endsection

