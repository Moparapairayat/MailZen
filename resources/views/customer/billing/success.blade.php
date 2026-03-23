@extends('layouts.customer')

@section('title', 'Payment Successful')
@section('page-title', 'Payment Successful')

@php
    $purchaseSessionId = session('meta_pixel_purchase_subscription_id');
    $subscriptionId = isset($subscription?->id) ? (string) $subscription->id : null;
    $shouldTrackPurchase = is_string($purchaseSessionId)
        && $purchaseSessionId !== ''
        && is_string($subscriptionId)
        && $subscriptionId !== ''
        && hash_equals($purchaseSessionId, $subscriptionId);

    $purchaseValue = is_numeric($subscription?->price ?? null) ? (float) $subscription->price : 0.0;
    $purchaseCurrency = strtoupper((string) ($subscription?->currency ?? 'USD'));
    if (!preg_match('/^[A-Z]{3}$/', $purchaseCurrency)) {
        $purchaseCurrency = 'USD';
    }

    $purchasePlanName = (string) ($subscription?->plan_name ?: ($subscription?->plan?->name ?? 'Subscription'));
@endphp

@section('content')
<div class="space-y-6">
    <div class="rounded-xl border border-admin-border overflow-hidden bg-gradient-to-r from-admin-button-from/15 via-transparent to-admin-button-to/10">
        <div class="px-6 py-6">
            <h1 class="text-3xl font-bold tracking-tight">Payment successful</h1>
            <p class="mt-2 text-sm text-admin-text-secondary max-w-2xl">
                Your subscription is being activated. If you don't see it active immediately, refresh in a few seconds.
            </p>
        </div>
    </div>

    <x-card>
        <div class="flex items-start gap-4">
            <div class="mt-1">
                <svg class="w-10 h-10 text-admin-button-from" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <div class="flex-1">
                <div class="text-lg font-semibold">You're all set</div>

                @if($subscription)
                    <div class="mt-2 text-sm text-admin-text-secondary">
                        <div>Plan: <span class="font-medium text-admin-text">{{ $subscription->plan_name ?: ($subscription->plan?->name ?? 'Subscription') }}</span></div>
                        <div>Status: <span class="font-medium text-admin-text">{{ ucfirst($subscription->status) }}</span></div>
                    </div>
                @else
                    <div class="mt-2 text-sm text-admin-text-secondary">
                        Subscription details are not available yet.
                    </div>
                @endif

                <div class="mt-5 flex items-center gap-3">
                    <x-button href="{{ route('customer.billing.index') }}" variant="primary">Go to Billing</x-button>
                    <x-button href="{{ route('customer.dashboard') }}" variant="secondary">Go to Dashboard</x-button>
                </div>
            </div>
        </div>
    </x-card>
</div>
@endsection

@if($shouldTrackPurchase)
    @push('scripts')
        <script>
            if (typeof window.mailpurseMetaTrackCustom === 'function') {
                window.mailpurseMetaTrackCustom('Purchase', {
                    value: {{ json_encode($purchaseValue) }},
                    currency: {{ json_encode($purchaseCurrency) }},
                    content_name: {{ json_encode($purchasePlanName) }},
                    content_category: 'subscription',
                    content_type: 'product',
                });
            }
        </script>
    @endpush
@endif
