@extends('layouts.customer')

@section('title', __('Support Ticket'))
@section('page-title', __('Support Ticket'))

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/trix@2.1.1/dist/trix.css">
    <style>
        trix-editor {
            min-height: 140px;
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
            <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">#{{ $ticket->id }} · {{ __('Status:') }} {{ ucfirst($ticket->status) }}</div>
        </div>
        <div class="flex items-center gap-2">
            @customercan('support.permissions.can_close_tickets')
                @if($ticket->status !== 'closed')
                    <form method="POST" action="{{ route('customer.support-tickets.close', $ticket) }}">
                        @csrf
                        <x-button type="submit" variant="secondary">{{ __('Close Ticket') }}</x-button>
                    </form>
                @endif
            @endcustomercan
            <x-button href="{{ route('customer.support-tickets.index') }}" variant="secondary">{{ __('Back') }}</x-button>
        </div>
    </div>

    <x-card>
        <div class="space-y-4">
            @foreach($messages as $message)
                @php
                    $sender = $message->sender;
                    $isCustomer = $sender instanceof \App\Models\Customer;
                @endphp
                <div class="flex {{ $isCustomer ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-2xl w-full">
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 {{ $isCustomer ? 'bg-primary-50 dark:bg-primary-900/20' : 'bg-white dark:bg-gray-800' }}">
                            <div class="flex items-center justify-between gap-3">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $isCustomer ? __('You') : __('Support') }}
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

    @customercan('support.permissions.can_reply_tickets')
        @if($ticket->status !== 'closed')
            <x-card>
                <form method="POST" action="{{ route('customer.support-tickets.reply', $ticket) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="body" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Reply') }}</label>
                        <input id="body" type="hidden" name="body" value="{{ old('body') }}">
                        <trix-editor input="body" class="mt-1 bg-white dark:bg-gray-700 rounded-md border border-gray-300 dark:border-gray-600"></trix-editor>
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
    @endcustomercan
</div>
@endsection
