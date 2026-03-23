@extends('layouts.admin')

@section('title', __('Add Reply Server'))
@section('page-title', __('Add Reply Server'))

@section('content')
<x-card title="{{ __('Reply Server Details') }}">
    <form method="POST" action="{{ route('admin.reply-servers.store') }}" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Customer ID (optional for system server)') }}</label>
                <input type="number" name="customer_id" value="{{ old('customer_id') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:text-gray-100 sm:text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Name') }}</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:text-gray-100 sm:text-sm">
                @error('name') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Reply Domain') }}</label>
                <input type="text" name="reply_domain" value="{{ old('reply_domain') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:text-gray-100 sm:text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Protocol') }}</label>
                <select name="protocol" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:text-gray-100 sm:text-sm">
                    <option value="imap" {{ old('protocol') === 'imap' ? 'selected' : '' }}>{{ __('IMAP') }}</option>
                    <option value="pop3" {{ old('protocol') === 'pop3' ? 'selected' : '' }}>{{ __('POP3') }}</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Hostname') }}</label>
                <input type="text" name="hostname" value="{{ old('hostname') }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:text-gray-100 sm:text-sm">
                @error('hostname') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Port') }}</label>
                <input type="number" name="port" value="{{ old('port', 993) }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:text-gray-100 sm:text-sm">
                @error('port') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Encryption') }}</label>
                <select name="encryption" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:text-gray-100 sm:text-sm">
                    <option value="ssl" {{ old('encryption') === 'ssl' ? 'selected' : '' }}>{{ __('SSL') }}</option>
                    <option value="tls" {{ old('encryption') === 'tls' ? 'selected' : '' }}>{{ __('TLS') }}</option>
                    <option value="none" {{ old('encryption') === 'none' ? 'selected' : '' }}>{{ __('None') }}</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Username') }}</label>
                <input type="text" name="username" value="{{ old('username') }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:text-gray-100 sm:text-sm">
                @error('username') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Password') }}</label>
                <input type="password" name="password" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:text-gray-100 sm:text-sm">
                @error('password') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Mailbox') }}</label>
                <input type="text" name="mailbox" value="{{ old('mailbox', 'INBOX') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:text-gray-100 sm:text-sm">
                @error('mailbox') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Max Emails Per Batch') }}</label>
                <input type="number" name="max_emails_per_batch" value="{{ old('max_emails_per_batch', 100) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:text-gray-100 sm:text-sm">
                @error('max_emails_per_batch') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Notes') }}</label>
                <textarea name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:text-gray-100 sm:text-sm">{{ old('notes') }}</textarea>
            </div>

            <div class="flex items-center space-x-6 sm:col-span-2">
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="active" value="1" {{ old('active', true) ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Active') }}</span>
                </label>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="delete_after_processing" value="1" {{ old('delete_after_processing') ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Delete emails after processing') }}</span>
                </label>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <x-button href="{{ route('admin.reply-servers.index') }}" variant="secondary">{{ __('Cancel') }}</x-button>
            <x-button type="submit" variant="primary">{{ __('Save') }}</x-button>
        </div>
    </form>
</x-card>
@endsection
