@extends('layouts.admin')

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
                    <p><strong>{{ __('Verification Results:') }}</strong></p>
                    <ul class="list-disc list-inside ml-2">
                        <li>{{ __('DKIM:') }} {{ $results['dkim'] ? __('✓ Verified') : __('✗ Not Found') }}</li>
                        <li>{{ __('SPF:') }} {{ $results['spf'] ? __('✓ Verified') : __('✗ Not Found') }}</li>
                        <li>{{ __('DMARC:') }} {{ $results['dmarc'] ? __('✓ Verified') : __('✗ Not Found (Optional)') }}</li>
                    </ul>
                </div>
            @endif
        </div>
    @endif

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $sendingDomain->domain }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ __('Status:') }}
                <span class="px-2 py-1 text-xs rounded-full {{ $sendingDomain->status === 'verified' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                    {{ __(ucfirst($sendingDomain->status)) }}
                </span>
            </p>
        </div>
        <div class="flex items-center gap-3">
            @if($sendingDomain->status !== 'verified')
                <form method="POST" action="{{ route('admin.sending-domains.verify', $sendingDomain) }}">
                    @csrf
                    <x-button type="submit" variant="primary">{{ __('Verify Domain') }}</x-button>
                </form>
                <form method="POST" action="{{ route('admin.sending-domains.mark-verified', $sendingDomain) }}">
                    @csrf
                    <x-button type="submit" variant="secondary">{{ __('Mark Verified') }}</x-button>
                </form>
            @endif
            <x-button href="{{ route('admin.sending-domains.index') }}" variant="secondary">{{ __('Back') }}</x-button>
        </div>
    </div>

    <x-card title="{{ __('Domain Information') }}">
        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Domain') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $sendingDomain->domain }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Customer') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    @if($sendingDomain->customer)
                        <a href="{{ route('admin.customers.show', $sendingDomain->customer) }}" class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300">
                            {{ $sendingDomain->customer->full_name ?? $sendingDomain->customer->email }}
                        </a>
                    @else
                        -
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Status') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    <span class="px-2 py-1 text-xs rounded-full {{ $sendingDomain->status === 'verified' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                        {{ __(ucfirst($sendingDomain->status)) }}
                    </span>
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Verified At') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $sendingDomain->verified_at ? $sendingDomain->verified_at->format('M d, Y H:i') : __('Not verified') }}</dd>
            </div>
            @if($sendingDomain->spf_record)
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('SPF DNS Record') }}</dt>
                    <dd class="mt-1 space-y-2">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('Add this TXT record to your DNS:') }}</div>
                        <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('Host/Name:') }}
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
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('DKIM DNS Record') }}</dt>
                    <dd class="mt-1 space-y-2">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('Add this TXT record to your DNS:') }}</div>
                        <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('Host/Name:') }}
                            <span class="font-mono">{{ $sendingDomain->dns_records['dkim']['host'] ?? 'mail._domainkey' }}.{{ $sendingDomain->domain }}</span>
                        </div>
                        <div class="text-sm text-gray-900 dark:text-gray-100 font-mono bg-gray-100 dark:bg-gray-700 p-3 rounded break-all">
                            {{ $sendingDomain->dns_records['dkim']['record'] ?? 'v=DKIM1; k=rsa; p=' . str_replace(['-----BEGIN PUBLIC KEY-----', '-----END PUBLIC KEY-----', "\n", "\r"], '', $sendingDomain->dkim_public_key) }}
                        </div>
                    </dd>
                </div>
            @endif

            @if($sendingDomain->dmarc_record)
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('DMARC DNS Record') }}</dt>
                    <dd class="mt-1 space-y-2">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('Add this TXT record to your DNS:') }}</div>
                        <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('Host/Name:') }}
                            <span class="font-mono">{{ $sendingDomain->dns_records['dmarc']['host'] ?? '_dmarc' }}.{{ $sendingDomain->domain }}</span>
                        </div>
                        <div class="text-sm text-gray-900 dark:text-gray-100 font-mono bg-gray-100 dark:bg-gray-700 p-3 rounded break-all">
                            {{ $sendingDomain->dns_records['dmarc']['record'] ?? $sendingDomain->dmarc_record }}
                        </div>
                    </dd>
                </div>
            @endif
            @if($sendingDomain->notes)
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Notes') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $sendingDomain->notes }}</dd>
                </div>
            @endif
        </dl>
    </x-card>

    @if($sendingDomain->verification_data && isset($sendingDomain->verification_data['last_checked']))
        <x-card title="{{ __('Last Verification Results') }}">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-3">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Last Checked') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $sendingDomain->verification_data['last_checked'] }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('DKIM') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ !empty($sendingDomain->verification_data['dkim_verified']) ? __('Verified') : __('Not Verified') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('SPF') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ !empty($sendingDomain->verification_data['spf_verified']) ? __('Verified') : __('Not Verified') }}</dd>
                </div>
            </dl>
        </x-card>
    @endif
</div>
@endsection
