@extends('layouts.admin')

@section('title', $campaign->name)
@section('page-title', $campaign->name)

@section('content')
<div class="space-y-6">
    <!-- Campaign Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $campaign->name }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $campaign->subject }}</p>
        </div>
        <form method="POST" action="{{ route('admin.campaigns.destroy', $campaign) }}" class="inline" onsubmit="return confirm(@json(__('Are you sure?')));">
            @csrf
            @method('DELETE')
            <x-button type="submit" variant="danger">{{ __('Delete') }}</x-button>
        </form>
    </div>

    <!-- Campaign Stats -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Status') }}</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                <span class="px-2 py-1 text-xs rounded-full {{ $campaign->status === 'sent' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ __(ucfirst($campaign->status)) }}
                </span>
            </div>
        </x-card>
        <x-card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Recipients') }}</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($campaign->total_recipients ?? 0) }}</div>
        </x-card>
        <x-card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Open Rate') }}</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($campaign->open_rate ?? 0, 1) }}%</div>
        </x-card>
        <x-card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Click Rate') }}</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($campaign->click_rate ?? 0, 1) }}%</div>
        </x-card>
    </div>

    <!-- Campaign Details -->
    <x-card title="{{ __('Campaign Details') }}">
        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Customer') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    <a href="{{ route('admin.customers.show', $campaign->customer) }}" class="text-primary-600 hover:text-primary-700">
                        {{ $campaign->customer->full_name }} ({{ $campaign->customer->email }})
                    </a>
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Email List') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $campaign->emailList->name ?? __('No List') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Type') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ __(ucfirst($campaign->type)) }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('From') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $campaign->from_name }} &lt;{{ $campaign->from_email }}&gt;</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Created') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $campaign->created_at->format('M d, Y H:i') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Sent At') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $campaign->started_at ? $campaign->started_at->format('M d, Y H:i') : __('Not sent') }}</dd>
            </div>
        </dl>
    </x-card>
</div>
@endsection

