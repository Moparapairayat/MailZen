@extends('layouts.customer')

@section('title', 'Bounced Emails')
@section('page-title', 'Bounced Emails')

@section('content')
<div class="space-y-6">
    <x-card>
        <form method="GET" action="{{ route('customer.bounced-emails.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <input type="text" name="email" value="{{ $filters['email'] ?? '' }}" placeholder="Recipient email" class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>
            <div>
                <select name="campaign_id" class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    <option value="">All campaigns</option>
                    @foreach($campaigns as $campaign)
                        <option value="{{ $campaign->id }}" {{ (string)($filters['campaign_id'] ?? '') === (string)$campaign->id ? 'selected' : '' }}>{{ $campaign->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="list_id" class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    <option value="">All lists</option>
                    @foreach($lists as $list)
                        <option value="{{ $list->id }}" {{ (string)($filters['list_id'] ?? '') === (string)$list->id ? 'selected' : '' }}>{{ $list->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="bounce_type" class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    <option value="">All types</option>
                    <option value="hard" {{ ($filters['bounce_type'] ?? '') === 'hard' ? 'selected' : '' }}>Hard</option>
                    <option value="soft" {{ ($filters['bounce_type'] ?? '') === 'soft' ? 'selected' : '' }}>Soft</option>
                </select>
            </div>
            <div class="md:col-span-4 flex justify-end">
                <x-button type="submit" variant="primary">Filter</x-button>
            </div>
        </form>
    </x-card>

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Campaign</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">List</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Last Bounced</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($bounces as $bounce)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $bounce->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $bounce->campaign?->name ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $bounce->emailList?->name ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $bounce->bounce_type ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $bounce->last_bounced_at?->format('M d, Y H:i') ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <x-button href="{{ route('customer.bounced-emails.show', $bounce) }}" variant="table" size="action" :pill="true" class="p-2" title="View" aria-label="View"><x-lucide name="eye" class="h-4 w-4" /><span class="sr-only">View</span></x-button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No bounced emails found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($bounces->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">{{ $bounces->links() }}</div>
        @endif
    </x-card>
</div>
@endsection
