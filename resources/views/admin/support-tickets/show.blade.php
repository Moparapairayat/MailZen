@extends('layouts.admin')

@section('title', __('Support Ticket'))
@section('page-title', __('Support Ticket'))

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/trix@2.1.1/dist/trix.css">
    <style>
        trix-editor {
            min-height: 160px;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/trix@2.1.1/dist/trix.umd.min.js" defer></script>
@endpush

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $ticket->subject }}</h2>
            <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                #{{ $ticket->id }} · {{ $ticket->customer?->full_name ?? '—' }} ({{ $ticket->customer?->email ?? '—' }})
            </div>
        </div>
        <div class="flex items-center gap-2">
            <x-button href="{{ route('admin.support-tickets.index') }}" variant="secondary">{{ __('Back') }}</x-button>
        </div>
    </div>

    <x-card>
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="text-sm text-gray-700 dark:text-gray-200">
                <div><strong>{{ __('Status:') }}</strong> {{ ucfirst($ticket->status) }}</div>
                <div><strong>{{ __('Priority:') }}</strong> {{ ucfirst($ticket->priority) }}</div>
            </div>

            @admincan('admin.support_tickets.edit')
                <div class="flex flex-col gap-3 lg:flex-row lg:items-end">
                    <form method="POST" action="{{ route('admin.support-tickets.status', $ticket) }}" class="flex items-end gap-2">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Status') }}</label>
                            <select name="status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>{{ __('Open') }}</option>
                                <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>{{ __('Closed') }}</option>
                            </select>
                        </div>
                        <x-button type="submit" variant="secondary">{{ __('Update') }}</x-button>
                    </form>

                    <form method="POST" action="{{ route('admin.support-tickets.priority', $ticket) }}" class="flex items-end gap-2">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Priority') }}</label>
                            <select name="priority" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
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
    </x-card>

    <x-card>
        <div class="space-y-4">
            @foreach($messages as $message)
                @php
                    $sender = $message->sender;
                    $isCustomer = $sender instanceof \App\Models\Customer;
                @endphp
                <div class="flex {{ $isCustomer ? 'justify-start' : 'justify-end' }}">
                    <div class="max-w-2xl w-full">
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 {{ $isCustomer ? 'bg-white dark:bg-gray-800' : 'bg-primary-50 dark:bg-primary-900/20' }}">
                            <div class="flex items-center justify-between gap-3">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $isCustomer ? ($ticket->customer?->full_name ?? __('Customer')) : __('Admin') }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $message->created_at->diffForHumans() }}
                                </div>
                            </div>
                            <div class="mt-2 text-sm text-gray-800 dark:text-gray-200 prose prose-sm max-w-none dark:prose-invert">{!! $message->body_for_display !!}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-card>

    @admincan('admin.support_tickets.edit')
        @if($ticket->status !== 'closed')
            <x-card>
                <form method="POST" action="{{ route('admin.support-tickets.reply', $ticket) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="body" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Reply') }}</label>
                        <input id="reply_body" type="hidden" name="body" value="{{ old('body') }}">
                        <trix-editor input="reply_body" class="mt-1 bg-white dark:bg-gray-700 rounded-md border border-gray-300 dark:border-gray-600"></trix-editor>
                        @error('body')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex items-center justify-end">
                        <x-button type="submit" variant="primary">{{ __('Send Reply') }}</x-button>
                    </div>
                </form>
            </x-card>
        @endif
    @endadmincan
</div>
@endsection
