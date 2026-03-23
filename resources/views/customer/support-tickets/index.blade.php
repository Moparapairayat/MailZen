@extends('layouts.customer')

@section('title', __('Support'))
@section('page-title', __('Support'))

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between lg:gap-0">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('Support Tickets') }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Create tickets and view replies from the support team.') }}</p>
        </div>
        @customercan('support.permissions.can_create_tickets')
            <x-button href="{{ route('customer.support-tickets.create') }}" variant="primary" class="w-full lg:w-auto">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('New Ticket') }}
            </x-button>
        @endcustomercan
    </div>

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Subject') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Priority') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Last update') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($tickets as $ticket)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $ticket->subject }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">#{{ $ticket->id }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @php
                                    $badge = $ticket->status === 'closed'
                                        ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                                        : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
                                @endphp
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $badge }}">
                                    {{ ucfirst($ticket->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                {{ ucfirst($ticket->priority) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ ($ticket->last_message_at ?? $ticket->updated_at)->diffForHumans() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <x-button href="{{ route('customer.support-tickets.show', $ticket) }}" variant="table" size="action" :pill="true">{{ __('View') }}</x-button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                {{ __('No tickets yet.') }}
                                @customercan('support.permissions.can_create_tickets')
                                    <a href="{{ route('customer.support-tickets.create') }}" class="text-primary-600 hover:text-primary-700 dark:text-primary-400">{{ __('Create one') }}</a>
                                @endcustomercan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tickets->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $tickets->links() }}
            </div>
        @endif
    </x-card>
</div>
@endsection
