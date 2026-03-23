@extends('layouts.customer')

@section('title', 'Checkout')
@section('page-title', 'Checkout')

@section('content')
@php
    $providerLabels = [
        'stripe' => 'Stripe',
        'paypal' => 'PayPal',
        'razorpay' => 'Razorpay',
        'flutterwave' => 'Flutterwave',
        'manual' => 'Bank Transfer',
    ];

    $enabledProviders = isset($enabledProviders) && is_array($enabledProviders) ? $enabledProviders : [];
    $selectedProvider = isset($selectedProvider) && is_string($selectedProvider) ? $selectedProvider : 'stripe';
    $couponCode = isset($couponCode) && is_string($couponCode) ? $couponCode : '';

    $planCurrency = strtoupper((string) ($plan->currency ?? 'USD'));
    if (!preg_match('/^[A-Z]{3}$/', $planCurrency)) {
        $planCurrency = 'USD';
    }

    $planValue = is_numeric($plan->price ?? null) ? (float) $plan->price : 0.0;
@endphp

<div class="max-w-6xl mx-auto space-y-6">
    <div class="rounded-xl border border-admin-border overflow-hidden bg-gradient-to-r from-admin-button-from/15 via-transparent to-admin-button-to/10">
        <div class="px-6 py-6">
            <h1 class="text-3xl font-bold tracking-tight">Checkout</h1>
            <p class="mt-2 text-sm text-admin-text-secondary max-w-2xl">
                Choose a payment method to subscribe to <span class="font-semibold">{{ $plan->name }}</span>.
            </p>
        </div>
    </div>

    <x-card>
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <div class="lg:col-span-7 space-y-6">
                <div>
                    <div class="text-sm font-semibold">Billing information</div>
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-admin-text-secondary">Name</label>
                            <input value="{{ $customer->full_name }}" class="mt-1 block w-full rounded-md border-admin-border bg-transparent" disabled>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-admin-text-secondary">Email</label>
                            <input value="{{ $customer->email }}" class="mt-1 block w-full rounded-md border-admin-border bg-transparent" disabled>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="text-sm font-semibold">Payment method</div>
                    <div class="mt-3 flex flex-wrap items-center gap-3">
                        @foreach($enabledProviders as $providerKey)
                            @php
                                $label = $providerLabels[$providerKey] ?? ucfirst($providerKey);
                                $isSelected = $providerKey === $selectedProvider;
                            @endphp

                            <label class="cursor-pointer" data-provider-option>
                                <input type="radio" name="provider_choice" value="{{ $providerKey }}" class="sr-only" {{ $isSelected ? 'checked' : '' }}>
                                <span
                                    data-provider-pill
                                    class="inline-flex items-center justify-center rounded-lg border px-5 py-3 text-sm font-medium transition-colors hover:border-primary-500 hover:bg-primary-500/10 {{ $isSelected ? 'border-primary-500 bg-primary-500/10' : 'border-admin-border bg-white/0' }}"
                                >
                                    {{ $label }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <div class="mt-3 text-xs text-admin-text-secondary">
                        Card details are collected securely on the payment provider page.
                    </div>
                </div>
            </div>

            <div class="lg:col-span-5">
                <div class="lg:sticky lg:top-6 space-y-3">
                    <div class="text-sm font-semibold">Order summary</div>
                    <div class="rounded-lg border border-admin-border bg-white/5 p-5 space-y-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="text-sm text-admin-text-secondary">Plan</div>
                                <div class="font-semibold">{{ $plan->name }}</div>
                                <div class="text-xs text-admin-text-secondary mt-1">Billed {{ $plan->billing_cycle === 'yearly' ? 'yearly' : 'monthly' }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-admin-text-secondary">Price</div>
                                <div class="text-2xl font-bold">{{ $plan->currency }} {{ number_format((float) $plan->price, 2) }}</div>
                            </div>
                        </div>

                        <div class="border-t border-admin-border pt-4">
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-medium">Total</div>
                                <div class="text-sm font-semibold">{{ $plan->currency }} {{ number_format((float) $plan->price, 2) }}</div>
                            </div>
                        </div>
                    </div>

                    <form id="checkout-form" method="POST" action="{{ route('customer.billing.checkout', $plan) }}" class="space-y-3" data-turbo="false">
                        @csrf
                        <input type="hidden" name="provider" id="provider" value="{{ $selectedProvider }}">

                        <div>
                            <label class="block text-xs font-medium text-admin-text-secondary">Coupon code (optional)</label>
                            <input name="coupon_code" value="{{ old('coupon_code', $couponCode) }}" class="mt-1 block w-full rounded-md border-admin-border bg-transparent" placeholder="WELCOME10">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <x-button href="{{ route('customer.billing.index') }}" variant="secondary">Back</x-button>
                            <x-button type="submit" variant="primary">Continue to Payment</x-button>
                        </div>
                    </form>

                    <div class="text-xs text-admin-text-secondary">
                        By continuing, you agree to be charged according to your selected plan.
                    </div>
                </div>
            </div>
        </div>

        <script>
            const initBillingCheckoutProviderChoice = () => {
                var form = document.getElementById('checkout-form');
                if (!form) {
                    return;
                }

                 if (form.dataset.metaInitiateTracked !== '1' && typeof window.mailpurseMetaTrackCustom === 'function') {
                    window.mailpurseMetaTrackCustom('InitiateCheckout', {
                        value: {{ json_encode($planValue) }},
                        currency: {{ json_encode($planCurrency) }},
                        content_name: {{ json_encode((string) $plan->name) }},
                        content_category: 'subscription',
                    });
                    form.dataset.metaInitiateTracked = '1';
                }

                if (form.dataset.providerChoiceBound === '1') {
                    return;
                }
                form.dataset.providerChoiceBound = '1';

                var radios = document.querySelectorAll('input[name="provider_choice"]');
                var hidden = document.getElementById('provider');

                function syncPills() {
                    document.querySelectorAll('[data-provider-option]').forEach(function (option) {
                        var radio = option.querySelector('input[type="radio"]');
                        var pill = option.querySelector('[data-provider-pill]');

                        if (!radio || !pill) {
                            return;
                        }

                        if (radio.checked) {
                            pill.classList.add('border-primary-500', 'bg-primary-500/10');
                            pill.classList.remove('border-admin-border', 'bg-white/0');
                        } else {
                            pill.classList.add('border-admin-border', 'bg-white/0');
                            pill.classList.remove('border-primary-500', 'bg-primary-500/10');
                        }
                    });
                }

                radios.forEach(function (radio) {
                    radio.addEventListener('change', function () {
                        if (hidden) {
                            hidden.value = radio.value;
                        }

                        syncPills();
                    });
                });

                document.querySelectorAll('[data-provider-option]').forEach(function (option) {
                    option.addEventListener('click', function () {
                        var radio = option.querySelector('input[type="radio"]');
                        if (radio) {
                            radio.checked = true;
                            radio.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    });
                });

                form.addEventListener('submit', function () {
                    if (typeof window.mailpurseMetaTrackCustom !== 'function') {
                        return;
                    }

                    var selectedProviderInput = document.getElementById('provider');
                    var selectedProvider = selectedProviderInput ? selectedProviderInput.value : null;

                    window.mailpurseMetaTrackCustom('AddPaymentInfo', {
                        value: {{ json_encode($planValue) }},
                        currency: {{ json_encode($planCurrency) }},
                        content_name: {{ json_encode((string) $plan->name) }},
                        payment_method: selectedProvider || 'unknown',
                        content_category: 'subscription',
                    });
                });

                syncPills();
            };

            document.addEventListener('DOMContentLoaded', initBillingCheckoutProviderChoice);
            document.addEventListener('turbo:load', initBillingCheckoutProviderChoice);
        </script>
    </x-card>
</div>
@endsection
