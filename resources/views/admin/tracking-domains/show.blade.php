@extends('layouts.admin')

@section('title', $trackingDomain->domain)
@section('page-title', $trackingDomain->domain)

@section('content')
<div class="space-y-6">
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg dark:bg-red-900/50 dark:border-red-800 dark:text-red-200">
            <p class="font-medium">{{ session('error') }}</p>
            @if(session('verification_results'))
                @php $results = session('verification_results'); @endphp
                <div class="mt-2 text-sm space-y-1">
                    <p><strong>{{ __('Verification Results:') }}</strong></p>
                    <ul class="list-disc list-inside ml-2">
                        <li>{{ __('CNAME:') }} {{ !empty($results['cname']) ? __('✓ Verified') : __('✗ Not Found / Mismatch') }}</li>
                    </ul>
                </div>
            @endif
        </div>
    @endif

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $trackingDomain->domain }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ __('Status:') }}
                <span class="px-2 py-1 text-xs rounded-full {{ $trackingDomain->status === 'verified' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                    {{ __(ucfirst($trackingDomain->status)) }}
                </span>
            </p>
        </div>
        <div class="flex items-center gap-3">
            @if($trackingDomain->status !== 'verified')
                <form method="POST" action="{{ route('admin.tracking-domains.verify', $trackingDomain) }}">
                    @csrf
                    <x-button type="submit" variant="primary">{{ __('Verify Domain') }}</x-button>
                </form>
                <form method="POST" action="{{ route('admin.tracking-domains.mark-verified', $trackingDomain) }}">
                    @csrf
                    <x-button type="submit" variant="secondary">{{ __('Mark Verified') }}</x-button>
                </form>
            @endif
            <x-button href="{{ route('admin.tracking-domains.edit', $trackingDomain) }}" variant="secondary">{{ __('Edit') }}</x-button>
            <x-button href="{{ route('admin.tracking-domains.index') }}" variant="secondary">{{ __('Back') }}</x-button>
        </div>
    </div>

    <x-card title="{{ __('Domain Information') }}">
        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Domain') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $trackingDomain->domain }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Customer') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    @if($trackingDomain->customer)
                        <a href="{{ route('admin.customers.show', $trackingDomain->customer) }}" class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300">
                            {{ $trackingDomain->customer->full_name ?? $trackingDomain->customer->email }}
                        </a>
                    @else
                        -
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Status') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ __(ucfirst($trackingDomain->status)) }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Verified At') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $trackingDomain->verified_at ? $trackingDomain->verified_at->format('M d, Y H:i') : __('Not verified') }}</dd>
            </div>

            @if(is_array($trackingDomain->dns_records) && isset($trackingDomain->dns_records['cname']))
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('CNAME DNS Record') }}</dt>
                    <dd class="mt-1 space-y-2">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('Add this CNAME record to your DNS:') }}</div>
                        <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('Host/Name:') }}
                            <span class="font-mono">{{ $trackingDomain->dns_records['cname']['host'] ?? $trackingDomain->domain }}</span>
                        </div>
                        <div class="text-sm text-gray-900 dark:text-gray-100 font-mono bg-gray-100 dark:bg-gray-700 p-3 rounded break-all">
                            {{ $trackingDomain->dns_records['cname']['target'] ?? '' }}
                        </div>
                    </dd>
                </div>
            @endif
            <div class="sm:col-span-2">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Verification Token') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono break-all">{{ $trackingDomain->verification_token }}</dd>
            </div>
            @if($trackingDomain->notes)
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Notes') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $trackingDomain->notes }}</dd>
                </div>
            @endif
        </dl>
    </x-card>

    @if($trackingDomain->verification_data && isset($trackingDomain->verification_data['last_checked']))
        <x-card title="{{ __('Last Verification Results') }}">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-3">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('CNAME') }}</dt>
                    <dd class="mt-1">
                        <span class="px-2 py-1 text-xs rounded-full {{ $trackingDomain->verification_data['cname_verified'] ?? false ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                            {{ ($trackingDomain->verification_data['cname_verified'] ?? false) ? __('✓ Verified') : __('✗ Not Verified') }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Expected Target') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono break-all">
                        {{ $trackingDomain->verification_data['expected_target'] ?? '' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Found Target(s)') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono break-all">
                        @php
                            $foundTargets = $trackingDomain->verification_data['found_targets'] ?? [];
                            $foundTargets = is_array($foundTargets) ? $foundTargets : [];
                        @endphp
                        {{ empty($foundTargets) ? '-' : implode(', ', $foundTargets) }}
                    </dd>
                </div>
            </dl>
            @if(!empty($trackingDomain->verification_data['errors']))
                <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg dark:bg-red-900/50 dark:border-red-800">
                    <p class="text-sm font-medium text-red-800 dark:text-red-200 mb-2">{{ __('Verification Errors:') }}</p>
                    <ul class="text-sm text-red-700 dark:text-red-300 list-disc list-inside space-y-1">
                        @foreach($trackingDomain->verification_data['errors'] as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </x-card>
    @endif
</div>
@endsection
