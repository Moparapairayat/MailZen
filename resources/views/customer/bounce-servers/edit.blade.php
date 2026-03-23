@extends('layouts.customer')

@section('title', 'Edit Bounce Server')
@section('page-title', 'Edit Bounce Server')

@section('content')
<x-card>
    <form method="POST" action="{{ route('customer.bounce-servers.update', $bounceServer) }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
            <input name="name" value="{{ old('name', $bounceServer->name) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Protocol</label>
                <select name="protocol" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                    <option value="imap" {{ old('protocol', $bounceServer->protocol) === 'imap' ? 'selected' : '' }}>IMAP</option>
                    <option value="pop3" {{ old('protocol', $bounceServer->protocol) === 'pop3' ? 'selected' : '' }}>POP3</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Encryption</label>
                <select name="encryption" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                    <option value="ssl" {{ old('encryption', $bounceServer->encryption) === 'ssl' ? 'selected' : '' }}>SSL</option>
                    <option value="tls" {{ old('encryption', $bounceServer->encryption) === 'tls' ? 'selected' : '' }}>TLS</option>
                    <option value="none" {{ old('encryption', $bounceServer->encryption) === 'none' ? 'selected' : '' }}>None</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Hostname</label>
                <input name="hostname" value="{{ old('hostname', $bounceServer->hostname) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Port</label>
                <input name="port" type="number" value="{{ old('port', $bounceServer->port) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Username</label>
                <input name="username" value="{{ old('username', $bounceServer->username) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password (leave blank to keep)</label>
                <input name="password" type="password" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mailbox</label>
                <input name="mailbox" value="{{ old('mailbox', $bounceServer->mailbox) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max emails per batch</label>
                <input name="max_emails_per_batch" type="number" value="{{ old('max_emails_per_batch', $bounceServer->max_emails_per_batch) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
            </div>
        </div>

        <div class="flex items-center gap-6">
            <label class="inline-flex items-center">
                <input type="checkbox" name="active" value="1" {{ old('active', $bounceServer->active) ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</span>
            </label>
            <label class="inline-flex items-center">
                <input type="checkbox" name="delete_after_processing" value="1" {{ old('delete_after_processing', $bounceServer->delete_after_processing) ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Delete after processing</span>
            </label>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
            <textarea name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">{{ old('notes', $bounceServer->notes) }}</textarea>
        </div>

        <div class="flex items-center justify-end gap-3">
            <x-button href="{{ route('customer.bounce-servers.index') }}" variant="secondary">Cancel</x-button>
            @customercan('servers.permissions.can_edit_bounce_servers')
                <x-button type="submit" variant="primary">Save</x-button>
            @endcustomercan
        </div>
    </form>
</x-card>
@endsection
