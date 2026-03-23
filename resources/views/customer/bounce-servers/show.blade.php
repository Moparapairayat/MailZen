@extends('layouts.customer')

@section('title', 'Bounce Server')
@section('page-title', 'Bounce Server')

@section('content')
<div class="space-y-6">
    <x-card>
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-lg font-semibold">{{ $bounceServer->name }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $bounceServer->protocol }}://{{ $bounceServer->hostname }}:{{ $bounceServer->port }}</p>
            </div>
            <div class="flex items-center gap-2">
                @if($bounceServer->customer_id)
                    @customercan('servers.permissions.can_edit_bounce_servers')
                        <x-button href="{{ route('customer.bounce-servers.edit', $bounceServer) }}" variant="primary">Edit</x-button>
                    @endcustomercan
                    @customercan('servers.permissions.can_delete_bounce_servers')
                        <form method="POST" action="{{ route('customer.bounce-servers.destroy', $bounceServer) }}" onsubmit="return confirm('Are you sure?');">
                            @csrf
                            @method('DELETE')
                            <x-button type="submit" variant="danger">Delete</x-button>
                        </form>
                    @endcustomercan
                @endif
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div><strong>Username:</strong> {{ $bounceServer->username }}</div>
            <div><strong>Mailbox:</strong> {{ $bounceServer->mailbox }}</div>
            <div><strong>Encryption:</strong> {{ $bounceServer->encryption }}</div>
            <div><strong>Active:</strong> {{ $bounceServer->active ? 'Yes' : 'No' }}</div>
            <div><strong>Delete after processing:</strong> {{ $bounceServer->delete_after_processing ? 'Yes' : 'No' }}</div>
            <div><strong>Max emails/batch:</strong> {{ $bounceServer->max_emails_per_batch }}</div>
        </div>

        @if($bounceServer->notes)
            <div class="mt-4 text-sm">
                <strong>Notes:</strong>
                <div class="mt-1 text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $bounceServer->notes }}</div>
            </div>
        @endif
    </x-card>

    <div>
        <x-button href="{{ route('customer.bounce-servers.index') }}" variant="secondary">Back</x-button>
    </div>
</div>
@endsection
