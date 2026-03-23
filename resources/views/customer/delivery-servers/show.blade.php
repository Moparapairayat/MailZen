@extends('layouts.customer')

@section('title', $deliveryServer->name)
@section('page-title', $deliveryServer->name)

@section('content')
<div class="space-y-6">
    @if($deliveryServer->customer_id && $deliveryServer->type === 'smtp' && $deliveryServer->username && filter_var($deliveryServer->username, FILTER_VALIDATE_EMAIL))
        <x-card>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    @if($deliveryServer->isVerified())
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <h3 class="text-sm font-semibold text-green-800 dark:text-green-200">Delivery Server Verified</h3>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Verified on {{ $deliveryServer->verified_at->format('M d, Y H:i') }}</p>
                        </div>
                    @else
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <h3 class="text-sm font-semibold text-yellow-800 dark:text-yellow-200">Verification Required</h3>
                            <p class="text-xs text-gray-600 dark:text-gray-400">A verification email has been sent to {{ $deliveryServer->username }} (SMTP account)</p>
                        </div>
                    @endif
                </div>
                @customercan('servers.permissions.can_edit_delivery_servers')
                    @if(!$deliveryServer->isVerified())
                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('customer.delivery-servers.resend-verification', $deliveryServer) }}" class="inline">
                                @csrf
                                <x-button type="submit" variant="secondary" size="sm">Resend Verification Email</x-button>
                            </form>
                        </div>
                    @endif
                @endcustomercan
            </div>
        </x-card>
    @endif

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $deliveryServer->name }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                <span class="px-2 py-1 text-xs rounded-full {{ $deliveryServer->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                    {{ ucfirst($deliveryServer->status) }}
                </span>
                @if($deliveryServer->locked)
                    <span class="ml-2 px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Locked</span>
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            @if($deliveryServer->customer_id)
                @customercan('servers.permissions.can_edit_delivery_servers')
                    <x-button href="{{ route('customer.delivery-servers.edit', $deliveryServer) }}" variant="secondary">Edit</x-button>
                @endcustomercan

                @customercan('servers.permissions.can_delete_delivery_servers')
                    <form method="POST" action="{{ route('customer.delivery-servers.destroy', $deliveryServer) }}" class="inline" onsubmit="return confirm('Are you sure?');">
                        @csrf
                        @method('DELETE')
                        <x-button type="submit" variant="danger">Delete</x-button>
                    </form>
                @endcustomercan
            @endif
        </div>
    </div>

    <x-card title="Server Configuration">
        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Type</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ strtoupper(str_replace('-', ' ', $deliveryServer->type)) }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    <span class="px-2 py-1 text-xs rounded-full {{ $deliveryServer->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                        {{ ucfirst($deliveryServer->status) }}
                    </span>
                </dd>
            </div>
            @if($deliveryServer->hostname)
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Hostname</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $deliveryServer->hostname }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Port</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $deliveryServer->port }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Encryption</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ strtoupper($deliveryServer->encryption) }}</dd>
                </div>
            @endif
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">From Email</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $deliveryServer->from_email ?? 'N/A' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">From Name</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $deliveryServer->from_name ?? 'N/A' }}</dd>
            </div>
            @if($deliveryServer->trackingDomain)
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tracking Domain</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $deliveryServer->trackingDomain->domain }}</dd>
                </div>
            @endif
            @if($deliveryServer->bounceServer)
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Bounce Server</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                        {{ $deliveryServer->bounceServer->name }}
                        <span class="text-gray-500 dark:text-gray-400">({{ $deliveryServer->bounceServer->hostname }})</span>
                    </dd>
                </div>
            @endif
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Hourly Quota</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $deliveryServer->hourly_quota > 0 ? number_format($deliveryServer->hourly_quota) : 'Unlimited' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Daily Quota</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $deliveryServer->daily_quota > 0 ? number_format($deliveryServer->daily_quota) : 'Unlimited' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Monthly Quota</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $deliveryServer->monthly_quota > 0 ? number_format($deliveryServer->monthly_quota) : 'Unlimited' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Usage</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    <span class="{{ $deliveryServer->use_for ? 'text-green-600' : 'text-gray-400' }}">Campaigns</span> |
                    <span class="{{ $deliveryServer->use_for_transactional ? 'text-green-600' : 'text-gray-400' }}">Transactional</span> |
                    <span class="{{ $deliveryServer->use_for_email_to_list ? 'text-green-600' : 'text-gray-400' }}">Email to List</span>
                </dd>
            </div>
            @if($deliveryServer->notes)
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Notes</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $deliveryServer->notes }}</dd>
                </div>
            @endif
        </dl>
    </x-card>

    @if($deliveryServer->customer_id)
        @customercan('servers.permissions.can_edit_delivery_servers')
            <x-card title="Test Email">
                <form method="POST" action="{{ route('customer.delivery-servers.test-email', $deliveryServer) }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="to_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email to</label>
                        <input
                            type="email"
                            name="to_email"
                            id="to_email"
                            value="{{ old('to_email') }}"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                        >
                        @error('to_email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end">
                        <x-button type="submit" variant="primary">Send</x-button>
                    </div>
                </form>
            </x-card>
        @endcustomercan
    @endif

    @php
        $webhookUrls = is_array($deliveryServer->settings ?? null) ? ($deliveryServer->settings['webhooks'] ?? null) : null;
        $isAmazonSes = $deliveryServer->type === 'amazon-ses';
        $snsWebhookUrl = rtrim((string) config('app.url'), '/') . '/ses/sns';
        if (is_array($webhookUrls) && !empty($webhookUrls['sns'])) {
            $snsWebhookUrl = $webhookUrls['sns'];
        }
    @endphp

    @if(is_array($webhookUrls) && count($webhookUrls) > 0)
        <x-card title="Provider Webhooks">
            <div class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                @if($isAmazonSes)
                    Configure AWS SES event publishing via SNS. Create an SNS topic, subscribe this HTTPS endpoint, then in SES publish Delivery/Bounce/Complaint events to that topic.
                @else
                    Configure these URLs in your provider dashboard.
                @endif
            </div>

            <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                @if($isAmazonSes)
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">SNS (SES)</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 break-all">
                            <code>{{ $snsWebhookUrl }}</code>
                        </dd>
                    </div>
                @elseif(!empty($webhookUrls['sns']))
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">SNS (SES)</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 break-all">
                            <code>{{ $webhookUrls['sns'] }}</code>
                        </dd>
                    </div>
                @endif
                @if(!$isAmazonSes && !empty($webhookUrls['bounce']))
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Bounce / Failed</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 break-all">
                            <code>{{ $webhookUrls['bounce'] }}</code>
                        </dd>
                    </div>
                @endif
                @if(!$isAmazonSes && !empty($webhookUrls['open']))
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Open</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 break-all">
                            <code>{{ $webhookUrls['open'] }}</code>
                        </dd>
                    </div>
                @endif
                @if(!$isAmazonSes && !empty($webhookUrls['click']))
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Click</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 break-all">
                            <code>{{ $webhookUrls['click'] }}</code>
                        </dd>
                    </div>
                @endif
            </dl>
        </x-card>
    @endif

    <div>
        <x-button href="{{ route('customer.delivery-servers.index') }}" variant="secondary">Back</x-button>
    </div>
</div>
@endsection
