@extends('layouts.admin')

@section('title', __('View Customer'))
@section('page-title', __('View Customer'))

@section('content')
<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex items-center justify-end gap-4">
        <form method="POST" action="{{ route('admin.customers.email-verification.update', $customer) }}" class="inline" onsubmit="return confirm(@json($customer->hasVerifiedEmail() ? __('Mark this customer email as unverified?') : __('Mark this customer email as verified?')));">
            @csrf
            @method('PATCH')
            <input type="hidden" name="verified" value="{{ $customer->hasVerifiedEmail() ? 0 : 1 }}">
            <x-button type="submit" variant="{{ $customer->hasVerifiedEmail() ? 'warning' : 'success' }}">
                {{ $customer->hasVerifiedEmail() ? __('Mark Unverified') : __('Mark Verified') }}
            </x-button>
        </form>
        <x-button href="{{ route('admin.customers.edit', $customer) }}" variant="primary">
            {{ __('Edit Customer') }}
        </x-button>
    </div>

    <!-- Customer Details -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Main Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Personal Information -->
            <x-card title="{{ __('Personal Information') }}">
                <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('First Name') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->first_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Last Name') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->last_name }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Email') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            <div class="flex items-center gap-2">
                                <span>{{ $customer->email }}</span>
                                @if($customer->hasVerifiedEmail())
                                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">{{ __('Verified') }}</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">{{ __('Unverified') }}</span>
                                @endif
                            </div>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Timezone') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->timezone }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Language') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->language }}</dd>
                    </div>
                </dl>
            </x-card>

            <!-- Company Information -->
            <x-card title="{{ __('Company Information') }}">
                <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Company Name') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->company_name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Phone') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->phone ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Country') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->country ?? '-' }}</dd>
                    </div>
                    @if($customer->address)
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Address') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->address }}</dd>
                        </div>
                    @endif
                    @if($customer->city || $customer->state || $customer->zip_code)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('City') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->city ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('State') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->state ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Zip Code') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->zip_code ?? '-' }}</dd>
                        </div>
                    @endif
                </dl>
            </x-card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Status Card -->
            <x-card title="{{ __('Account Status') }}">
                <div class="space-y-4">
                    <div>
                        @php
                            $statusColors = [
                                'active' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                'inactive' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                'suspended' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                            ];
                        @endphp
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $statusColors[$customer->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ __(ucfirst($customer->status)) }}
                        </span>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Created') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->created_at->format('M d, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Email Verified') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $customer->hasVerifiedEmail() ? $customer->email_verified_at->format('M d, Y') : __('No') }}
                        </dd>
                    </div>
                    @if($customer->expires_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Expires At') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->expires_at->format('M d, Y') }}</dd>
                        </div>
                    @endif
                </div>
            </x-card>

            <!-- Quota & Limits -->
            <x-card title="{{ __('Quota & Limits') }}">
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Quota Usage') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ number_format($customer->quota_usage, 2) }} / {{ number_format($customer->quota, 2) }}
                        </dd>
                        <div class="mt-2 w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                            <div
                                class="bg-primary-600 h-2 rounded-full"
                                style="width: {{ $customer->quota > 0 ? min(100, ($customer->quota_usage / $customer->quota) * 100) : 0 }}%"
                            ></div>
                        </div>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Max Lists') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->max_lists ?: __('Unlimited') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Max Subscribers') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->max_subscribers ?: __('Unlimited') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Max Campaigns') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->max_campaigns ?: __('Unlimited') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Currency') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customer->currency }}</dd>
                    </div>
                </dl>
            </x-card>

            <!-- Customer Groups -->
            @if($customer->customerGroups->count() > 0)
                <x-card title="{{ __('Customer Groups') }}">
                    <ul class="space-y-2">
                        @foreach($customer->customerGroups as $group)
                            <li class="text-sm text-gray-900 dark:text-gray-100">{{ $group->name }}</li>
                        @endforeach
                    </ul>
                </x-card>
            @endif
        </div>
    </div>
</div>
@endsection

