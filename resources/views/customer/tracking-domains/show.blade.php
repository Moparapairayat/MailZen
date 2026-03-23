@extends('layouts.customer')

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
                    <p><strong>Verification Results:</strong></p>
                    <ul class="list-disc list-inside ml-2">
                        <li>CNAME: {{ !empty($results['cname']) ? '✓ Verified' : '✗ Not Found / Mismatch' }}</li>
                    </ul>
                </div>
            @endif
        </div>
    @endif

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $trackingDomain->domain }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Status: <span class="px-2 py-1 text-xs rounded-full {{ $trackingDomain->status === 'verified' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">{{ ucfirst($trackingDomain->status) }}</span>
            </p>
        </div>
        <div class="flex items-center gap-3">
            @if($trackingDomain->customer_id)
                @customercan('domains.tracking_domains.permissions.can_edit_tracking_domains')
                    @if($trackingDomain->status !== 'verified')
                        <form method="POST" action="{{ route('customer.tracking-domains.verify', $trackingDomain) }}">
                            @csrf
                            <x-button type="submit" variant="primary">Verify Domain</x-button>
                        </form>
                        <form method="POST" action="{{ route('customer.tracking-domains.mark-verified', $trackingDomain) }}">
                            @csrf
                            <x-button type="submit" variant="secondary">Mark Verified</x-button>
                        </form>
                    @endif
                    <x-button href="{{ route('customer.tracking-domains.edit', $trackingDomain) }}" variant="secondary">Edit</x-button>
                @endcustomercan
            @endif
        </div>
    </div>

    <x-card title="Domain Information">
        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Domain</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $trackingDomain->domain }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    <span class="px-2 py-1 text-xs rounded-full {{ $trackingDomain->status === 'verified' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">{{ ucfirst($trackingDomain->status) }}</span>
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Verified At</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $trackingDomain->verified_at ? $trackingDomain->verified_at->format('M d, Y H:i') : 'Not verified' }}</dd>
            </div>

            @if(is_array($trackingDomain->dns_records) && isset($trackingDomain->dns_records['cname']))
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">CNAME DNS Record</dt>
                    <dd class="mt-1 space-y-2">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">
                            Add this CNAME record to your DNS:
                        </div>
                        <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Host/Name:
                            <span class="font-mono">{{ $trackingDomain->dns_records['cname']['host'] ?? $trackingDomain->domain }}</span>
                        </div>
                        <div class="text-sm text-gray-900 dark:text-gray-100 font-mono bg-gray-100 dark:bg-gray-700 p-3 rounded break-all">
                            {{ $trackingDomain->dns_records['cname']['target'] ?? '' }}
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                            Once verified, MailZen will use this domain for open/click/unsubscribe tracking links.
                        </p>
                    </dd>
                </div>
            @endif
            @if($trackingDomain->verification_token)
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Verification Token</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono bg-gray-100 dark:bg-gray-700 p-2 rounded">{{ $trackingDomain->verification_token }}</dd>
                </div>
            @endif
            @if($trackingDomain->notes)
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Notes</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $trackingDomain->notes }}</dd>
                </div>
            @endif
        </dl>
    </x-card>

    @if($trackingDomain->verification_data && isset($trackingDomain->verification_data['last_checked']))
        <x-card title="Last Verification Results">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-3">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">CNAME</dt>
                    <dd class="mt-1">
                        <span class="px-2 py-1 text-xs rounded-full {{ $trackingDomain->verification_data['cname_verified'] ?? false ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                            {{ ($trackingDomain->verification_data['cname_verified'] ?? false) ? '✓ Verified' : '✗ Not Verified' }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Expected Target</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono break-all">
                        {{ $trackingDomain->verification_data['expected_target'] ?? '' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Found Target(s)</dt>
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
                    <p class="text-sm font-medium text-red-800 dark:text-red-200 mb-2">Verification Errors:</p>
                    <ul class="text-sm text-red-700 dark:text-red-300 list-disc list-inside space-y-1">
                        @foreach($trackingDomain->verification_data['errors'] as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                Last checked: {{ \Carbon\Carbon::parse($trackingDomain->verification_data['last_checked'])->format('M d, Y H:i:s') }}
            </p>
        </x-card>
    @endif
</div>
@endsection

