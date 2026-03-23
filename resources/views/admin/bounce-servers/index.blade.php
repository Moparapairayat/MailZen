@extends('layouts.admin')

@section('title', __('Bounce Servers'))
@section('page-title', __('Bounce Servers'))

@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ __('Bounce Servers') }}</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Manage IMAP/POP3 bounce mailboxes.') }}</p>
    </div>
    <x-button href="{{ route('admin.bounce-servers.create') }}" variant="primary">{{ __('Add Bounce Server') }}</x-button>
</div>

<x-card :padding="false">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Name') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Protocol') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Host') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Mailbox') }}</th>
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $server->mailbox ?? __('INBOX') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $server->active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300' }}">
                                {{ $server->active ? __('Active') : __('Inactive') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                            <x-button href="{{ route('admin.bounce-servers.show', $server) }}" variant="table" size="action" :pill="true" class="p-2" title="{{ __('View') }}" aria-label="{{ __('View') }}"><x-lucide name="eye" class="h-4 w-4" /><span class="sr-only">{{ __('View') }}</span></x-button>
                            <x-button href="{{ route('admin.bounce-servers.edit', $server) }}" variant="table" size="action" :pill="true" class="p-2" title="{{ __('Edit') }}" aria-label="{{ __('Edit') }}"><x-lucide name="pencil" class="h-4 w-4" /><span class="sr-only">{{ __('Edit') }}</span></x-button>
                            <form method="POST" action="{{ route('admin.bounce-servers.destroy', $server) }}" class="inline" onsubmit="return confirm('{{ __('Delete this bounce server?') }}');">
                                @csrf
                                @method('DELETE')
                                <x-button type="submit" variant="table-danger" size="action" :pill="true" class="p-2" title="{{ __('Delete') }}" aria-label="{{ __('Delete') }}"><x-lucide name="trash-2" class="h-4 w-4" /><span class="sr-only">{{ __('Delete') }}</span></x-button>
                            </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('No bounce servers configured.') }}</td>
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

