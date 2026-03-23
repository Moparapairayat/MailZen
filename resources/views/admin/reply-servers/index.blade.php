@extends('layouts.admin')

@section('title', __('Reply Servers'))
@section('page-title', __('Reply Servers'))

@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ __('Reply Servers') }}</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Manage IMAP/POP3 reply tracking mailboxes.') }}</p>
    </div>
    <x-button href="{{ route('admin.reply-servers.create') }}" variant="primary">{{ __('Add Reply Server') }}</x-button>
</div>

<x-card :padding="false">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Name') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Protocol') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Host') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Reply Domain') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Active') }}</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($servers as $server)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $server->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 uppercase">{{ $server->protocol }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $server->hostname }}:{{ $server->port }} ({{ strtoupper($server->encryption) }})</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $server->reply_domain ?? __('—') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $server->active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300' }}">
                                {{ $server->active ? __('Active') : __('Inactive') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                                <x-button href="{{ route('admin.reply-servers.show', $server) }}" variant="table" size="action" :pill="true">{{ __('View') }}</x-button>
                                <x-button href="{{ route('admin.reply-servers.edit', $server) }}" variant="table" size="action" :pill="true">{{ __('Edit') }}</x-button>
                                <form method="POST" action="{{ route('admin.reply-servers.destroy', $server) }}" class="inline" onsubmit="return confirm('{{ __('Delete this reply server?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="submit" variant="table-danger" size="action" :pill="true">{{ __('Delete') }}</x-button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('No reply servers configured.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $servers->links() }}
    </div>
</x-card>
@endsection
