@extends('layouts.public')

@section('title', 'Checkout')

@section('content')
<div class="bg-white dark:bg-gray-900">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center">
            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white">
                {{ __('Redirecting to checkout...') }}
            </h1>
            <p class="mt-4 text-gray-500 dark:text-gray-400">
                {{ __('Preparing your subscription for:') }}
                <span class="font-semibold text-gray-900 dark:text-white">{{ $plan->name }}</span>
            </p>
        </div>

        <form id="pricing-checkout-redirect-form" method="GET" action="{{ route('customer.billing.checkout.show', $plan) }}" class="mt-10">
            <input type="hidden" name="coupon_code" value="">
            <noscript>
                <div class="text-center">
                    <x-button type="submit" variant="primary">{{ __('Continue') }}</x-button>
                </div>
            </noscript>
        </form>

        <script>
            (function () {
                function submitCheckoutRedirectForm() {
                    var form = document.getElementById('pricing-checkout-redirect-form');
                    if (!form || form.dataset.redirectSubmitted === '1') {
                        return;
                    }

                    form.dataset.redirectSubmitted = '1';

                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                        return;
                    }

                    form.submit();
                }

                submitCheckoutRedirectForm();
                document.addEventListener('DOMContentLoaded', submitCheckoutRedirectForm, { once: true });
                window.addEventListener('pageshow', submitCheckoutRedirectForm, { once: true });
            })();
        </script>
    </div>
</div>
@endsection
