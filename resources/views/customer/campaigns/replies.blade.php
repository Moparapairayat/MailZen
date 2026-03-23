@extends('layouts.customer')

@section('title', 'Replies - ' . $campaign->name)
@section('page-title', 'Replies')

@section('content')
<div class="space-y-6" x-data="{ open: false, selected: null }">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Replies</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $campaign->name }}</p>
        </div>
        <div class="flex items-center gap-2">
            <x-button href="{{ route('customer.campaigns.show', $campaign) }}" variant="secondary">← Back to Campaign</x-button>
        </div>
    </div>

    <x-card>
        <form method="GET" action="{{ route('customer.campaigns.replies', $campaign) }}" class="flex items-end gap-4">
            <div class="flex-1">
                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                    placeholder="Search by from, subject, recipient email..."
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100">
            </div>
            <div>
                <x-button type="submit" variant="primary">Filter</x-button>
            </div>
            @if(request('search'))
                <div>
                    <a href="{{ route('customer.campaigns.replies', $campaign) }}" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">Clear</a>
                </div>
            @endif
        </form>
    </x-card>

    <x-card>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Received</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">From</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Recipient</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Subject</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($replies as $reply)
                        @php
                            $fromLabel = trim((string) ($reply->from_name ?? ''));
                            $fromEmail = trim((string) ($reply->from_email ?? ''));
                            $from = $fromLabel !== '' && $fromEmail !== '' ? ($fromLabel . ' <' . $fromEmail . '>') : ($fromEmail !== '' ? $fromEmail : ($fromLabel !== '' ? $fromLabel : '-'));
                            $recipientEmail = $reply->recipient?->email ?? '-';
                            $received = $reply->received_at ? $reply->received_at->format('M d, Y H:i') : ($reply->created_at?->format('M d, Y H:i') ?? '-');
                            $bodyText = \App\Services\ReplyProcessorService::decodeBodyText((string) ($reply->body_text ?? ''));
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $received }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $from }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $recipientEmail }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ \Illuminate\Support\Str::limit((string) ($reply->subject ?? ''), 80) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <button type="button"
                                    class="px-3 py-1.5 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700"
                                    @click="open = true; selected = {
                                        received: @js($received),
                                        from: @js($from),
                                        recipient: @js($recipientEmail),
                                        subject: @js((string) ($reply->subject ?? '')),
                                        body: @js($bodyText)
                                    }">
                                    View
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">No replies found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($replies->hasPages())
            <div class="mt-4">
                {{ $replies->links() }}
            </div>
        @endif
    </x-card>

    <div x-cloak x-show="open" class="fixed inset-0 z-50 flex items-center justify-center" aria-modal="true" role="dialog">
        <div class="fixed inset-0 bg-black/50" @click="open = false; selected = null"></div>
        <div class="relative w-full max-w-4xl mx-4 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Reply Details</h3>
                <button type="button" @click="open = false; selected = null" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <span class="sr-only">Close</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="px-6 py-4 space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Received</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100" x-text="selected?.received || '-' "></div>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Recipient</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100" x-text="selected?.recipient || '-' "></div>
                    </div>
                    <div class="sm:col-span-2">
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400">From</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100" x-text="selected?.from || '-' "></div>
                    </div>
                    <div class="sm:col-span-2">
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Subject</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100" x-text="selected?.subject || '-' "></div>
                    </div>
                </div>

                <div>
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Body</div>
                    <pre class="mt-2 whitespace-pre-wrap text-sm text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-900/30 border border-gray-200 dark:border-gray-700 rounded-md p-4 max-h-[420px] overflow-auto" x-text="selected?.body || ''"></pre>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="open = false; selected = null" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
