@extends('layouts.admin')

@section('title', $bounceServer->name)
@section('page-title', $bounceServer->name)

@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $bounceServer->name }}</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Bounce server details') }}</p>
    </div>
    <div class="space-x-2">
        <x-button href="{{ route('admin.bounce-servers.edit', $bounceServer) }}" variant="secondary">{{ __('Edit') }}</x-button>
        <form method="POST" action="{{ route('admin.bounce-servers.destroy', $bounceServer) }}" class="inline" onsubmit="return confirm('{{ __('Delete this bounce server?') }}');">
            @csrf
            @method('DELETE')
            <x-button type="submit" variant="danger">{{ __('Delete') }}</x-button>
        </form>
    </div>
</div>

<x-card>
    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Protocol') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 uppercase">{{ $bounceServer->protocol }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Host') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $bounceServer->hostname }}:{{ $bounceServer->port }} ({{ strtoupper($bounceServer->encryption) }})</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Mailbox') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $bounceServer->mailbox ?? __('INBOX') }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Username') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $bounceServer->username }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Active') }}</dt>
            <dd class="mt-1">
                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $bounceServer->active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300' }}">
                    {{ $bounceServer->active ? __('Active') : __('Inactive') }}
                </span>
            </dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Delete After Processing') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $bounceServer->delete_after_processing ? __('Yes') : __('No') }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Max Emails Per Batch') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $bounceServer->max_emails_per_batch ?? 100 }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Notes') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $bounceServer->notes ?? __('—') }}</dd>
        </div>
    </dl>
</x-card>
@endsection

