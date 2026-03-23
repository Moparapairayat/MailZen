@extends('layouts.admin')

@section('title', $replyServer->name)
@section('page-title', $replyServer->name)

@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $replyServer->name }}</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Reply server details') }}</p>
    </div>
    <div class="space-x-2">
        <x-button href="{{ route('admin.reply-servers.edit', $replyServer) }}" variant="secondary">{{ __('Edit') }}</x-button>
        <form method="POST" action="{{ route('admin.reply-servers.destroy', $replyServer) }}" class="inline" onsubmit="return confirm('{{ __('Delete this reply server?') }}');">
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
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 uppercase">{{ $replyServer->protocol }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Host') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $replyServer->hostname }}:{{ $replyServer->port }} ({{ strtoupper($replyServer->encryption) }})</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Mailbox') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $replyServer->mailbox ?? __('INBOX') }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Username') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $replyServer->username }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Reply Domain') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $replyServer->reply_domain ?? __('—') }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Active') }}</dt>
            <dd class="mt-1">
                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $replyServer->active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300' }}">
                    {{ $replyServer->active ? __('Active') : __('Inactive') }}
                </span>
            </dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Delete After Processing') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $replyServer->delete_after_processing ? __('Yes') : __('No') }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Max Emails Per Batch') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $replyServer->max_emails_per_batch ?? 100 }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Notes') }}</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $replyServer->notes ?? __('—') }}</dd>
        </div>
    </dl>
</x-card>

<!-- Process Logs -->
<x-card class="mt-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Process Logs') }}</h2>
        <div class="text-sm text-gray-500 dark:text-gray-400">
            @if($replyServer->last_processed_at)
                {{ __('Last processed') }}: {{ $replyServer->last_processed_at->format('Y-m-d H:i:s') }}
            @else
                {{ __('Never processed') }}
            @endif
        </div>
    </div>
    
    @if(is_array($replyServer->process_logs) && !empty($replyServer->process_logs))
        <div class="space-y-3 max-h-64 overflow-y-auto">
            @foreach(array_reverse($replyServer->process_logs) as $log)
                <div class="border-l-4 {{ $log['processed'] > 0 ? 'border-green-500' : 'border-gray-300' }} pl-4 py-2">
                    <div class="flex items-center justify-between">
                        <div class="text-sm">
                            <span class="font-medium text-gray-900 dark:text-gray-100">
                                {{ __('Processed') }}: {{ $log['processed'] }} {{ __('replies') }}
                            </span>
                            @if($log['errors'] > 0)
                                <span class="ml-2 text-red-600 dark:text-red-400">
                                    {{ __('Errors') }}: {{ $log['errors'] }}
                                </span>
                            @endif
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ \Carbon\Carbon::parse($log['processed_at'])->format('H:i:s') }}
                            @if(isset($log['total_time_ms']))
                                <span class="ml-2">{{ $log['total_time_ms'] }}ms</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
            {{ __('No process logs available') }}
        </div>
    @endif
</x-card>

<!-- Error Logs -->
@if(is_array($replyServer->error_logs) && !empty($replyServer->error_logs))
<x-card class="mt-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-red-600 dark:text-red-400">{{ __('Error Logs') }}</h2>
        <div class="text-sm text-gray-500 dark:text-gray-400">
            @if($replyServer->last_error_at)
                {{ __('Last error') }}: {{ $replyServer->last_error_at->format('Y-m-d H:i:s') }}
            @endif
        </div>
    </div>
    
    <div class="space-y-3 max-h-64 overflow-y-auto">
        @foreach(array_reverse($replyServer->error_logs) as $log)
            <div class="border-l-4 border-red-500 pl-4 py-2">
                <div class="text-sm">
                    <div class="font-medium text-red-600 dark:text-red-400">
                        {{ $log['error'] }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {{ \Carbon\Carbon::parse($log['error_at'])->format('Y-m-d H:i:s') }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-card>
@endif

<!-- Connection Status -->
<x-card class="mt-6">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Connection Status') }}</h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="text-center p-4 border rounded-lg {{ $replyServer->active ? 'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20' : 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/50' }}">
            <div class="text-2xl font-bold {{ $replyServer->active ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-400' }}">
                {{ $replyServer->active ? '●' : '○' }}
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                {{ $replyServer->active ? __('Active') : __('Inactive') }}
            </div>
        </div>
        
        <div class="text-center p-4 border rounded-lg border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/20">
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                {{ is_array($replyServer->process_logs) ? count($replyServer->process_logs) : 0 }}
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                {{ __('Process Runs') }}
            </div>
        </div>
        
        <div class="text-center p-4 border rounded-lg {{ is_array($replyServer->error_logs) && !empty($replyServer->error_logs) ? 'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20' : 'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20' }}">
            <div class="text-2xl font-bold {{ is_array($replyServer->error_logs) && !empty($replyServer->error_logs) ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                {{ is_array($replyServer->error_logs) ? count($replyServer->error_logs) : 0 }}
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                {{ __('Errors') }}
            </div>
        </div>
    </div>
</x-card>

@endsection
