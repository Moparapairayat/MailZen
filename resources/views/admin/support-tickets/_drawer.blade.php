<div class="space-y-5">
    <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <div class="text-base font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $ticket->subject }}</div>
                <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">#{{ $ticket->id }} · {{ $ticket->customer?->full_name ?? '—' }} @if($ticket->customer?->email) ({{ $ticket->customer?->email }}) @endif</div>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div class="rounded-lg border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 p-3">
                <div class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Status') }}</div>
                <div class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ ucfirst($ticket->status) }}</div>
            </div>
            <div class="rounded-lg border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 p-3">
                <div class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Priority') }}</div>
                <div class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ ucfirst($ticket->priority) }}</div>
            </div>
            <div class="rounded-lg border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 p-3">
                <div class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Created') }}</div>
                <div class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $ticket->created_at?->format('M d, Y') }}</div>
            </div>
            <div class="rounded-lg border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 p-3">
                <div class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Last update') }}</div>
                <div class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ ($ticket->last_message_at ?? $ticket->updated_at)->diffForHumans() }}</div>
            </div>
        </div>

        @admincan('admin.support_tickets.edit')
            <div class="mt-4 grid grid-cols-1 gap-3 lg:grid-cols-2">
                <form method="POST" action="{{ route('admin.support-tickets.status', $ticket) }}" data-drawer-ajax class="flex items-end gap-2">
                    @csrf
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Ticket Status') }}</label>
                        <select name="status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:text-gray-100 sm:text-sm">
                            <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>{{ __('Open') }}</option>
                            <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>{{ __('Closed') }}</option>
                        </select>
                    </div>
                    <x-button type="submit" variant="secondary">{{ __('Update') }}</x-button>
                </form>

                <form method="POST" action="{{ route('admin.support-tickets.priority', $ticket) }}" data-drawer-ajax class="flex items-end gap-2">
                    @csrf
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Ticket Priority') }}</label>
                        <select name="priority" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:text-gray-100 sm:text-sm">
                            <option value="low" {{ $ticket->priority === 'low' ? 'selected' : '' }}>{{ __('Low') }}</option>
                            <option value="normal" {{ $ticket->priority === 'normal' ? 'selected' : '' }}>{{ __('Normal') }}</option>
                            <option value="high" {{ $ticket->priority === 'high' ? 'selected' : '' }}>{{ __('High') }}</option>
                        </select>
                    </div>
                    <x-button type="submit" variant="secondary">{{ __('Update') }}</x-button>
                </form>
            </div>
        @endadmincan
    </div>

    <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-800">
            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Conversation') }}</div>
        </div>

        <div class="p-4 space-y-4 max-h-[45vh] overflow-y-auto">
            @foreach($messages as $message)
                @php
                    $sender = $message->sender;
                    $isCustomer = $sender instanceof \App\Models\Customer;
                @endphp
                <div class="flex {{ $isCustomer ? 'justify-start' : 'justify-end' }}">
                    <div class="max-w-[85%]">
                        <div class="rounded-2xl px-4 py-3 border {{ $isCustomer ? 'bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-800' : 'bg-primary-50 dark:bg-primary-900/20 border-primary-100 dark:border-primary-900/40' }}">
                            <div class="flex items-center justify-between gap-3">
                                <div class="text-xs font-medium text-gray-700 dark:text-gray-200">
                                    {{ $isCustomer ? ($ticket->customer?->full_name ?? __('Customer')) : __('Admin') }}
                                </div>
                                <div class="text-[11px] text-gray-500 dark:text-gray-400">
                                    {{ $message->created_at->format('h:i A · d M Y') }}
                                </div>
                            </div>
                            <div class="mt-2 text-sm text-gray-800 dark:text-gray-200 prose prose-sm max-w-none dark:prose-invert">{!! $message->body_for_display !!}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @admincan('admin.support_tickets.edit')
            @if($ticket->status !== 'closed')
                <div class="p-4 border-t border-gray-100 dark:border-gray-800">
                    <form method="POST" action="{{ route('admin.support-tickets.reply', $ticket) }}" data-drawer-ajax class="space-y-3">
                        @csrf
                        @php($inputId = 'reply_body_' . $ticket->id)
                        <input id="{{ $inputId }}" type="hidden" name="body" value="">
                        <trix-editor input="{{ $inputId }}" class="bg-white dark:bg-gray-800 rounded-md border border-gray-300 dark:border-gray-700"></trix-editor>

                        <div class="flex items-center justify-end">
                            <x-button type="submit" variant="primary">{{ __('Send Reply') }}</x-button>
                        </div>
                    </form>
                </div>
            @endif
        @endadmincan
    </div>
</div>
