@extends('layouts.admin')

@section('title', __('Bounced Email'))
@section('page-title', __('Bounced Email'))

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-admin-text-primary">{{ $bounce->email }}</h2>
            <p class="mt-1 text-sm text-admin-text-secondary">{{ __('Bounce details') }}</p>
        </div>
        <x-button href="{{ route('admin.bounced-emails.index') }}" variant="secondary">{{ __('Back') }}</x-button>
    </div>

    <x-card>
        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-admin-text-secondary">{{ __('Bounce Type') }}</dt>
                <dd class="mt-1">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $bounce->bounce_type === 'hard' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : ($bounce->bounce_type === 'soft' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300') }}">
                        {{ __(strtoupper($bounce->bounce_type)) }}
                    </span>
                </dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-admin-text-secondary">{{ __('Bounce Code') }}</dt>
                <dd class="mt-1 text-sm text-admin-text-primary">{{ $bounce->bounce_code ?? __('—') }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-admin-text-secondary">{{ __('Campaign') }}</dt>
                <dd class="mt-1 text-sm text-admin-text-primary">
                    @if($bounce->campaign)
                        <a class="text-primary-600 hover:text-primary-700" href="{{ route('admin.campaigns.show', $bounce->campaign) }}">
                            #{{ $bounce->campaign->id }} - {{ $bounce->campaign->name }}
                        </a>
                    @else
                        {{ __('—') }}
                    @endif
                </dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-admin-text-secondary">{{ __('List') }}</dt>
                <dd class="mt-1 text-sm text-admin-text-primary">
                    {{ $bounce->emailList?->name ?? __('—') }}
                </dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-admin-text-secondary">{{ __('Bounce Mailbox') }}</dt>
                <dd class="mt-1 text-sm text-admin-text-primary">
                    {{ $bounce->bounce_server_username ?? __('—') }} {{ $bounce->bounce_server_mailbox ? '(' . $bounce->bounce_server_mailbox . ')' : '' }}
                </dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-admin-text-secondary">{{ __('Last Bounced At') }}</dt>
                <dd class="mt-1 text-sm text-admin-text-primary">
                    {{ $bounce->last_bounced_at?->format('M d, Y H:i') ?? __('—') }}
                </dd>
            </div>

            <div class="sm:col-span-2">
                <dt class="text-sm font-medium text-admin-text-secondary">{{ __('Reason') }}</dt>
                <dd class="mt-1 text-sm text-admin-text-primary whitespace-pre-wrap">{{ $bounce->reason ?? __('—') }}</dd>
            </div>

            <div class="sm:col-span-2">
                <dt class="text-sm font-medium text-admin-text-secondary">{{ __('Diagnostic Code') }}</dt>
                <dd class="mt-1 text-sm text-admin-text-primary whitespace-pre-wrap">{{ $bounce->diagnostic_code ?? __('—') }}</dd>
            </div>

            <div class="sm:col-span-2">
                <dt class="text-sm font-medium text-admin-text-secondary">{{ __('Raw Message') }}</dt>
                <dd class="mt-1">
                    <pre class="text-xs whitespace-pre-wrap bg-white/5 border border-admin-border rounded-lg p-4 overflow-auto text-admin-text-primary">{{ $bounce->raw_message ?? __('—') }}</pre>
                </dd>
            </div>
        </dl>
    </x-card>
</div>
@endsection
