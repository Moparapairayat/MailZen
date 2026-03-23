@extends('layouts.admin')

@section('title', $deliveryServer->name)
@section('page-title', $deliveryServer->name)

@section('content')
<div class="space-y-6">
    <!-- Verification Status -->
    @if($deliveryServer->type === 'smtp' && $deliveryServer->username && filter_var($deliveryServer->username, FILTER_VALIDATE_EMAIL))
        <x-card>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    @if($deliveryServer->isVerified())
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <h3 class="text-sm font-semibold text-green-800 dark:text-green-200">{{ __('Delivery Server Verified') }}</h3>
                            <p class="text-xs text-gray-600 dark:text-gray-400">{{ __('Verified on :date', ['date' => $deliveryServer->verified_at->format('M d, Y H:i')]) }}</p>
                        </div>
                    @else
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <h3 class="text-sm font-semibold text-yellow-800 dark:text-yellow-200">{{ __('Verification Required') }}</h3>
                            <p class="text-xs text-gray-600 dark:text-gray-400">{{ __('A verification email has been sent to :email (SMTP account)', ['email' => $deliveryServer->username]) }}</p>
                        </div>
                    @endif
                </div>
                @if(!$deliveryServer->isVerified())
                    @admincan('admin.delivery_servers.resend_verification')
                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('admin.delivery-servers.resend-verification', $deliveryServer) }}" class="inline">
                                @csrf
                                <x-button type="submit" variant="secondary" size="sm">{{ __('Resend Verification Email') }}</x-button>
                            </form>
                        </div>
                    @endadmincan
                @endif
            </div>
            @if(!$deliveryServer->isVerified())
                <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                    <p class="text-xs text-yellow-800 dark:text-yellow-200">
                        <strong>{{ __('Not receiving the email?') }}</strong> {{ __('Check:') }}
                    </p>
                    <ul class="text-xs text-yellow-700 dark:text-yellow-300 mt-2 list-disc list-inside space-y-1">
                        <li>{{ __('Check your spam/junk folder') }}</li>
                        <li>{{ __('Check Laravel logs:') }} <code>storage/logs/laravel.log</code></li>
                        <li>{{ __('Ensure your mail server is properly configured and accessible') }}</li>
                    </ul>
                </div>
            @endif
        </x-card>
    @endif

    <!-- Server Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $deliveryServer->name }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                <span class="px-2 py-1 text-xs rounded-full {{ $deliveryServer->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                    {{ __(ucfirst($deliveryServer->status)) }}
                </span>
                @if($deliveryServer->locked)
                    <span class="ml-2 px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">{{ __('Locked') }}</span>
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            <x-button href="{{ route('admin.delivery-servers.edit', $deliveryServer) }}" variant="secondary">{{ __('Edit') }}</x-button>
            <form method="POST" action="{{ route('admin.delivery-servers.destroy', $deliveryServer) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure?') }}');">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="danger">{{ __('Delete') }}</x-button>
            </form>
        </div>
    </div>

    <x-card title="{{ __('Clock Skew Utilities') }}">
        <div class="flex flex-col gap-4">
            <div class="flex flex-wrap items-center gap-2">
                <form method="POST" action="{{ route('admin.delivery-servers.clock-skew-check', $deliveryServer) }}" class="inline">
                    @csrf
                    <x-button type="submit" variant="secondary" size="sm">{{ __('Run Clock Skew Check') }}</x-button>
                </form>

                <form method="POST" action="{{ route('admin.delivery-servers.restart-workers', $deliveryServer) }}" class="inline" onsubmit="return confirm('{{ __('Restart queue workers?') }}');">
                    @csrf
                    <x-button type="submit" variant="secondary" size="sm">{{ __('Restart Workers') }}</x-button>
                </form>
            </div>

            @if(is_array($clockSkewStatus ?? null))
                <dl class="grid grid-cols-1 gap-x-4 gap-y-3 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Last Check') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $clockSkewStatus['checked_at'] ?? __('N/A') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Status') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            @if(($clockSkewStatus['ok'] ?? false) === true)
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">{{ __('OK') }}</span>
                            @else
                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">{{ __('Not OK') }}</span>
                            @endif
                        </dd>
                    </div>

                    @if(array_key_exists('skew_seconds', $clockSkewStatus))
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Skew (seconds)') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $clockSkewStatus['skew_seconds'] ?? __('N/A') }}</dd>
                        </div>
                    @endif

                    @if(!empty($clockSkewStatus['error'] ?? null))
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Error') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 break-all">{{ $clockSkewStatus['error'] }}</dd>
                        </div>
                    @endif
                </dl>
            @endif

            @if(is_array($clockSkewLastRestart ?? null) && !empty($clockSkewLastRestart['restarted_at'] ?? null))
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Last worker restart: :date', ['date' => $clockSkewLastRestart['restarted_at']]) }}
                </div>
            @endif
        </div>
    </x-card>

    <!-- Server Details -->
    <x-card title="{{ __('Server Configuration') }}">
        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Type') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ strtoupper(str_replace('-', ' ', $deliveryServer->type)) }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Status') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    <span class="px-2 py-1 text-xs rounded-full {{ $deliveryServer->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                        {{ __(ucfirst($deliveryServer->status)) }}
                    </span>
                </dd>
            </div>
            @if($deliveryServer->hostname)
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Hostname') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $deliveryServer->hostname }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Port') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $deliveryServer->port }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Encryption') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ strtoupper($deliveryServer->encryption) }}</dd>
                </div>
            @endif
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('From Email') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $deliveryServer->from_email ?? __('N/A') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('From Name') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $deliveryServer->from_name ?? __('N/A') }}</dd>
            </div>
            @if($deliveryServer->trackingDomain)
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Tracking Domain') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $deliveryServer->trackingDomain->domain }}</dd>
                </div>
            @endif
            @if($deliveryServer->bounceServer)
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Bounce Server') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                        <a href="{{ route('admin.bounce-servers.show', $deliveryServer->bounceServer) }}" class="text-primary-600 hover:text-primary-900 dark:text-primary-400">
                            {{ $deliveryServer->bounceServer->name }}
                        </a>
                        <span class="text-gray-500 dark:text-gray-400">({{ $deliveryServer->bounceServer->hostname }})</span>
                    </dd>
                </div>
            @endif
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Hourly Quota') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $deliveryServer->hourly_quota > 0 ? number_format($deliveryServer->hourly_quota) : __('Unlimited') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Daily Quota') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $deliveryServer->daily_quota > 0 ? number_format($deliveryServer->daily_quota) : __('Unlimited') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Monthly Quota') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $deliveryServer->monthly_quota > 0 ? number_format($deliveryServer->monthly_quota) : __('Unlimited') }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Usage') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    <span class="{{ $deliveryServer->use_for ? 'text-green-600' : 'text-gray-400' }}">{{ __('Campaigns') }}</span> |
                    <span class="{{ $deliveryServer->use_for_transactional ? 'text-green-600' : 'text-gray-400' }}">{{ __('Transactional') }}</span> |
                    <span class="{{ $deliveryServer->use_for_email_to_list ? 'text-green-600' : 'text-gray-400' }}">{{ __('Email to List') }}</span>
                </dd>
            </div>
            @if($deliveryServer->notes)
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Notes') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $deliveryServer->notes }}</dd>
                </div>
            @endif
        </dl>
    </x-card>

    @php
        $webhookUrls = is_array($deliveryServer->settings ?? null) ? ($deliveryServer->settings['webhooks'] ?? null) : null;
        $isAmazonSes = $deliveryServer->type === 'amazon-ses';
        $snsWebhookUrl = rtrim((string) config('app.url'), '/') . '/ses/sns';
        if (is_array($webhookUrls) && !empty($webhookUrls['sns'])) {
            $snsWebhookUrl = $webhookUrls['sns'];
        }
    @endphp

    @if(is_array($webhookUrls) && count($webhookUrls) > 0)
        <x-card title="{{ __('Provider Webhooks') }}">
            <div class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                @if($isAmazonSes)
                    {{ __('Configure AWS SES event publishing via SNS. Create an SNS topic, subscribe this HTTPS endpoint, then in SES publish Delivery/Bounce/Complaint events to that topic.') }}
                @else
                    {{ __('Configure these URLs in your provider dashboard.') }}
                @endif
            </div>

            <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                @if($isAmazonSes)
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('SNS (SES)') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 break-all">
                            <code>{{ $snsWebhookUrl }}</code>
                        </dd>
                    </div>
                @elseif(!empty($webhookUrls['sns']))
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('SNS (SES)') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 break-all">
                            <code>{{ $webhookUrls['sns'] }}</code>
                        </dd>
                    </div>
                @endif
                @if(!$isAmazonSes && !empty($webhookUrls['bounce']))
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Bounce / Failed') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 break-all">
                            <code>{{ $webhookUrls['bounce'] }}</code>
                        </dd>
                    </div>
                @endif
                @if(!$isAmazonSes && !empty($webhookUrls['open']))
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Open') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 break-all">
                            <code>{{ $webhookUrls['open'] }}</code>
                        </dd>
                    </div>
                @endif
                @if(!$isAmazonSes && !empty($webhookUrls['click']))
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Click') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 break-all">
                            <code>{{ $webhookUrls['click'] }}</code>
                        </dd>
                    </div>
                @endif
            </dl>
        </x-card>
    @endif
</div>
@endsection

