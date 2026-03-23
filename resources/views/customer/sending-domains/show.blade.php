@extends('layouts.customer')

@section('title', $sendingDomain->domain)
@section('page-title', $sendingDomain->domain)

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
                        <li>DKIM: {{ $results['dkim'] ? '✓ Verified' : '✗ Not Found' }}</li>
                        <li>SPF: {{ $results['spf'] ? '✓ Verified' : '✗ Not Found' }}</li>
                        <li>DMARC: {{ $results['dmarc'] ? '✓ Verified' : '✗ Not Found (Optional)' }}</li>
                    </ul>
                </div>
            @endif
        </div>
    @endif

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $sendingDomain->domain }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Status: <span class="px-2 py-1 text-xs rounded-full {{ $sendingDomain->status === 'verified' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">{{ ucfirst($sendingDomain->status) }}</span>
            </p>
        </div>
        <div class="flex items-center gap-3">
            @if($sendingDomain->customer_id)
                @customercan('domains.sending_domains.permissions.can_edit_sending_domains')
                    @if($sendingDomain->status !== 'verified')
                        <form method="POST" action="{{ route('customer.sending-domains.verify', $sendingDomain) }}">
                            @csrf
                            <x-button type="submit" variant="primary">Verify Domain</x-button>
                        </form>
                        <form method="POST" action="{{ route('customer.sending-domains.mark-verified', $sendingDomain) }}">
                            @csrf
                            <x-button type="submit" variant="secondary">Mark Verified</x-button>
                        </form>
                    @endif
                    <x-button href="{{ route('customer.sending-domains.edit', $sendingDomain) }}" variant="secondary">Edit</x-button>
                @endcustomercan
            @endif
        </div>
    </div>

    <x-card title="Domain Information">
        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Domain</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $sendingDomain->domain }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    <span class="px-2 py-1 text-xs rounded-full {{ $sendingDomain->status === 'verified' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">{{ ucfirst($sendingDomain->status) }}</span>
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Verified At</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $sendingDomain->verified_at ? $sendingDomain->verified_at->format('M d, Y H:i') : 'Not verified' }}</dd>
            </div>
            @if($sendingDomain->spf_record)
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">SPF DNS Record</dt>
                    <dd class="mt-1 space-y-2">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">
                            Add this TXT record to your DNS:
                        </div>
                        <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Host/Name:
                            <span class="font-mono">{{ $sendingDomain->dns_records['spf']['host'] ?? '@' }}</span>
                        </div>
                        <div class="text-sm text-gray-900 dark:text-gray-100 font-mono bg-gray-100 dark:bg-gray-700 p-3 rounded break-all">
                            {{ $sendingDomain->dns_records['spf']['record'] ?? $sendingDomain->spf_record }}
                        </div>
                    </dd>
                </div>
            @endif
            @if($sendingDomain->dkim_public_key)
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">DKIM DNS Record</dt>
                    <dd class="mt-1 space-y-2">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">
                            Add this TXT record to your DNS:
                        </div>
                        <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Host/Name: <span class="font-mono">{{ $sendingDomain->dns_records['dkim']['host'] ?? 'mail._domainkey' }}.{{ $sendingDomain->domain }}</span>
                        </div>
                        <div class="text-sm text-gray-900 dark:text-gray-100 font-mono bg-gray-100 dark:bg-gray-700 p-3 rounded break-all">
                            {{ $sendingDomain->dns_records['dkim']['record'] ?? 'v=DKIM1; k=rsa; p=' . str_replace(['-----BEGIN PUBLIC KEY-----', '-----END PUBLIC KEY-----', "\n", "\r"], '', $sendingDomain->dkim_public_key) }}
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                            <strong>Note:</strong> Once this domain is verified, MailZen will automatically append DKIM signatures to emails sent from addresses using this domain (e.g., contact@{{ $sendingDomain->domain }}), but only if your delivery server doesn't already sign emails with DKIM.
                        </p>
                    </dd>
                </div>
            @endif

            @if($sendingDomain->dmarc_record)
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">DMARC DNS Record</dt>
                    <dd class="mt-1 space-y-2">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">
                            Add this TXT record to your DNS:
                        </div>
                        <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Host/Name:
                            <span class="font-mono">{{ $sendingDomain->dns_records['dmarc']['host'] ?? '_dmarc' }}.{{ $sendingDomain->domain }}</span>
                        </div>
                        <div class="text-sm text-gray-900 dark:text-gray-100 font-mono bg-gray-100 dark:bg-gray-700 p-3 rounded break-all">
                            {{ $sendingDomain->dns_records['dmarc']['record'] ?? $sendingDomain->dmarc_record }}
                        </div>
                    </dd>
                </div>
            @endif
        </dl>
    </x-card>

    @if($sendingDomain->verification_data && isset($sendingDomain->verification_data['last_checked']))
        <x-card title="Last Verification Results">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-3">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">DKIM</dt>
                    <dd class="mt-1">
                        <span class="px-2 py-1 text-xs rounded-full {{ $sendingDomain->verification_data['dkim_verified'] ?? false ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                            {{ ($sendingDomain->verification_data['dkim_verified'] ?? false) ? '✓ Verified' : '✗ Not Found' }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">SPF</dt>
                    <dd class="mt-1">
                        <span class="px-2 py-1 text-xs rounded-full {{ $sendingDomain->verification_data['spf_verified'] ?? false ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                            {{ ($sendingDomain->verification_data['spf_verified'] ?? false) ? '✓ Verified' : '✗ Not Found' }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">DMARC</dt>
                    <dd class="mt-1">
                        <span class="px-2 py-1 text-xs rounded-full {{ $sendingDomain->verification_data['dmarc_verified'] ?? false ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                            {{ ($sendingDomain->verification_data['dmarc_verified'] ?? false) ? '✓ Verified' : '✗ Not Found (Optional)' }}
                        </span>
                    </dd>
                </div>
            </dl>
            @if(!empty($sendingDomain->verification_data['errors']))
                <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg dark:bg-red-900/50 dark:border-red-800">
                    <p class="text-sm font-medium text-red-800 dark:text-red-200 mb-2">Verification Errors:</p>
                    <ul class="text-sm text-red-700 dark:text-red-300 list-disc list-inside space-y-1">
                        @foreach($sendingDomain->verification_data['errors'] as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                Last checked: {{ \Carbon\Carbon::parse($sendingDomain->verification_data['last_checked'])->format('M d, Y H:i:s') }}
            </p>
        </x-card>
    @endif
</div>
@endsection

