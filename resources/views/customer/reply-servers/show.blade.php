@extends('layouts.customer')

@section('title', 'Reply Server')
@section('page-title', 'Reply Server')

@section('content')
<div class="space-y-6">
    <x-card>
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-lg font-semibold">{{ $replyServer->name }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $replyServer->protocol }}://{{ $replyServer->hostname }}:{{ $replyServer->port }}</p>
            </div>
            <div class="flex items-center gap-2">
                @if($replyServer->customer_id)
                    @customercan('servers.permissions.can_edit_reply_servers')
                        <x-button href="{{ route('customer.reply-servers.edit', $replyServer) }}" variant="primary">Edit</x-button>
                    @endcustomercan
                    @customercan('servers.permissions.can_delete_reply_servers')
                        <form method="POST" action="{{ route('customer.reply-servers.destroy', $replyServer) }}" onsubmit="return confirm('Are you sure?');">
                            @csrf
                            @method('DELETE')
                            <x-button type="submit" variant="danger">Delete</x-button>
                        </form>
                    @endcustomercan
                @endif
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div><strong>Username:</strong> {{ $replyServer->username }}</div>
            <div><strong>Mailbox:</strong> {{ $replyServer->mailbox }}</div>
            <div><strong>Reply Domain:</strong> {{ $replyServer->reply_domain ?? '—' }}</div>
            <div><strong>Encryption:</strong> {{ $replyServer->encryption }}</div>
            <div><strong>Active:</strong> {{ $replyServer->active ? 'Yes' : 'No' }}</div>
            <div><strong>Delete after processing:</strong> {{ $replyServer->delete_after_processing ? 'Yes' : 'No' }}</div>
            <div><strong>Max emails/batch:</strong> {{ $replyServer->max_emails_per_batch }}</div>
        </div>

        @if($replyServer->notes)
            <div class="mt-4 text-sm">
                <strong>Notes:</strong>
                <div class="mt-1 text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $replyServer->notes }}</div>
            </div>
        @endif
    </x-card>

    <div>
        <x-button href="{{ route('customer.reply-servers.index') }}" variant="secondary">Back</x-button>
    </div>
</div>
@endsection
