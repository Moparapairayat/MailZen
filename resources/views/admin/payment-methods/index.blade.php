@extends('layouts.admin')

@section('title', __('Payment Methods'))
@section('page-title', __('Payment Methods'))

@section('content')
<div class="space-y-6">
    <form method="POST" action="{{ route('admin.payment-methods.update') }}" enctype="multipart/form-data">
        @csrf

        <x-card>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Default Provider') }}</label>
                    <select name="default_provider" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        <option value="stripe" {{ $defaultProvider === 'stripe' ? 'selected' : '' }}>{{ __('Stripe') }}</option>
                        <option value="paypal" {{ $defaultProvider === 'paypal' ? 'selected' : '' }}>{{ __('PayPal') }}</option>
                        <option value="razorpay" {{ $defaultProvider === 'razorpay' ? 'selected' : '' }}>{{ __('Razorpay') }}</option>
                        <option value="paystack" {{ $defaultProvider === 'paystack' ? 'selected' : '' }}>{{ __('Paystack') }}</option>
                        <option value="flutterwave" {{ $defaultProvider === 'flutterwave' ? 'selected' : '' }}>{{ __('Flutterwave') }}</option>
                        <option value="manual" {{ $defaultProvider === 'manual' ? 'selected' : '' }}>{{ __('Manual') }}</option>
                    </select>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Provider') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Enabled') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Configuration') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Stripe') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="hidden" name="providers[stripe][enabled]" value="0">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="providers[stripe][enabled]" value="1" {{ old('providers.stripe.enabled', data_get($providers, 'stripe.enabled', false)) ? 'checked' : '' }} class="sr-only peer">
                                        <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                                    </label>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Mode') }}</label>
                                            <select name="providers[stripe][mode]" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                                <option value="live" {{ old('providers.stripe.mode', data_get($providers, 'stripe.mode', 'live')) === 'live' ? 'selected' : '' }}>{{ __('Live') }}</option>
                                                <option value="sandbox" {{ old('providers.stripe.mode', data_get($providers, 'stripe.mode', 'live')) === 'sandbox' ? 'selected' : '' }}>{{ __('Sandbox') }}</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Live Public Key') }}</label>
                                            <input name="providers[stripe][live][public_key]" value="{{ old('providers.stripe.live.public_key', data_get($providers, 'stripe.live.public_key')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Live Secret') }}</label>
                                            <input type="password" name="providers[stripe][live][secret]" value="{{ old('providers.stripe.live.secret', data_get($providers, 'stripe.live.secret')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Live Webhook Secret') }}</label>
                                            <input type="password" name="providers[stripe][live][webhook_secret]" value="{{ old('providers.stripe.live.webhook_secret', data_get($providers, 'stripe.live.webhook_secret')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Sandbox Public Key') }}</label>
                                            <input name="providers[stripe][sandbox][public_key]" value="{{ old('providers.stripe.sandbox.public_key', data_get($providers, 'stripe.sandbox.public_key')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Sandbox Secret') }}</label>
                                            <input type="password" name="providers[stripe][sandbox][secret]" value="{{ old('providers.stripe.sandbox.secret', data_get($providers, 'stripe.sandbox.secret')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Sandbox Webhook Secret') }}</label>
                                            <input type="password" name="providers[stripe][sandbox][webhook_secret]" value="{{ old('providers.stripe.sandbox.webhook_secret', data_get($providers, 'stripe.sandbox.webhook_secret')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('PayPal') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="hidden" name="providers[paypal][enabled]" value="0">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="providers[paypal][enabled]" value="1" {{ old('providers.paypal.enabled', data_get($providers, 'paypal.enabled', false)) ? 'checked' : '' }} class="sr-only peer">
                                        <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                                    </label>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Mode') }}</label>
                                            <select name="providers[paypal][mode]" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                                <option value="live" {{ old('providers.paypal.mode', data_get($providers, 'paypal.mode', 'live')) === 'live' ? 'selected' : '' }}>{{ __('Live') }}</option>
                                                <option value="sandbox" {{ old('providers.paypal.mode', data_get($providers, 'paypal.mode', 'live')) === 'sandbox' ? 'selected' : '' }}>{{ __('Sandbox') }}</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Live Client ID') }}</label>
                                            <input name="providers[paypal][live][client_id]" value="{{ old('providers.paypal.live.client_id', data_get($providers, 'paypal.live.client_id')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Live Client Secret') }}</label>
                                            <input type="password" name="providers[paypal][live][client_secret]" value="{{ old('providers.paypal.live.client_secret', data_get($providers, 'paypal.live.client_secret')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Sandbox Client ID') }}</label>
                                            <input name="providers[paypal][sandbox][client_id]" value="{{ old('providers.paypal.sandbox.client_id', data_get($providers, 'paypal.sandbox.client_id')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Sandbox Client Secret') }}</label>
                                            <input type="password" name="providers[paypal][sandbox][client_secret]" value="{{ old('providers.paypal.sandbox.client_secret', data_get($providers, 'paypal.sandbox.client_secret')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Razorpay') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="hidden" name="providers[razorpay][enabled]" value="0">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="providers[razorpay][enabled]" value="1" {{ old('providers.razorpay.enabled', data_get($providers, 'razorpay.enabled', false)) ? 'checked' : '' }} class="sr-only peer">
                                        <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                                    </label>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Mode') }}</label>
                                            <select name="providers[razorpay][mode]" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                                <option value="live" {{ old('providers.razorpay.mode', data_get($providers, 'razorpay.mode', 'live')) === 'live' ? 'selected' : '' }}>{{ __('Live') }}</option>
                                                <option value="sandbox" {{ old('providers.razorpay.mode', data_get($providers, 'razorpay.mode', 'live')) === 'sandbox' ? 'selected' : '' }}>{{ __('Sandbox') }}</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Live Key ID') }}</label>
                                            <input name="providers[razorpay][live][key_id]" value="{{ old('providers.razorpay.live.key_id', data_get($providers, 'razorpay.live.key_id')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Live Key Secret') }}</label>
                                            <input type="password" name="providers[razorpay][live][key_secret]" value="{{ old('providers.razorpay.live.key_secret', data_get($providers, 'razorpay.live.key_secret')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Live Webhook Secret') }}</label>
                                            <input type="password" name="providers[razorpay][live][webhook_secret]" value="{{ old('providers.razorpay.live.webhook_secret', data_get($providers, 'razorpay.live.webhook_secret')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Sandbox Key ID') }}</label>
                                            <input name="providers[razorpay][sandbox][key_id]" value="{{ old('providers.razorpay.sandbox.key_id', data_get($providers, 'razorpay.sandbox.key_id')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Sandbox Key Secret') }}</label>
                                            <input type="password" name="providers[razorpay][sandbox][key_secret]" value="{{ old('providers.razorpay.sandbox.key_secret', data_get($providers, 'razorpay.sandbox.key_secret')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Sandbox Webhook Secret') }}</label>
                                            <input type="password" name="providers[razorpay][sandbox][webhook_secret]" value="{{ old('providers.razorpay.sandbox.webhook_secret', data_get($providers, 'razorpay.sandbox.webhook_secret')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Paystack') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="hidden" name="providers[paystack][enabled]" value="0">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="providers[paystack][enabled]" value="1" {{ old('providers.paystack.enabled', data_get($providers, 'paystack.enabled', false)) ? 'checked' : '' }} class="sr-only peer">
                                        <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                                    </label>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Mode') }}</label>
                                            <select name="providers[paystack][mode]" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                                <option value="live" {{ old('providers.paystack.mode', data_get($providers, 'paystack.mode', 'live')) === 'live' ? 'selected' : '' }}>{{ __('Live') }}</option>
                                                <option value="sandbox" {{ old('providers.paystack.mode', data_get($providers, 'paystack.mode', 'live')) === 'sandbox' ? 'selected' : '' }}>{{ __('Sandbox') }}</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Live Public Key') }}</label>
                                            <input name="providers[paystack][live][public_key]" value="{{ old('providers.paystack.live.public_key', data_get($providers, 'paystack.live.public_key')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Live Secret') }}</label>
                                            <input type="password" name="providers[paystack][live][secret]" value="{{ old('providers.paystack.live.secret', data_get($providers, 'paystack.live.secret')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Sandbox Public Key') }}</label>
                                            <input name="providers[paystack][sandbox][public_key]" value="{{ old('providers.paystack.sandbox.public_key', data_get($providers, 'paystack.sandbox.public_key')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Sandbox Secret') }}</label>
                                            <input type="password" name="providers[paystack][sandbox][secret]" value="{{ old('providers.paystack.sandbox.secret', data_get($providers, 'paystack.sandbox.secret')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Flutterwave') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="hidden" name="providers[flutterwave][enabled]" value="0">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="providers[flutterwave][enabled]" value="1" {{ old('providers.flutterwave.enabled', data_get($providers, 'flutterwave.enabled', false)) ? 'checked' : '' }} class="sr-only peer">
                                        <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                                    </label>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Mode') }}</label>
                                            <select name="providers[flutterwave][mode]" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                                <option value="live" {{ old('providers.flutterwave.mode', data_get($providers, 'flutterwave.mode', 'live')) === 'live' ? 'selected' : '' }}>{{ __('Live') }}</option>
                                                <option value="sandbox" {{ old('providers.flutterwave.mode', data_get($providers, 'flutterwave.mode', 'live')) === 'sandbox' ? 'selected' : '' }}>{{ __('Sandbox') }}</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Live Public Key') }}</label>
                                            <input name="providers[flutterwave][live][public_key]" value="{{ old('providers.flutterwave.live.public_key', data_get($providers, 'flutterwave.live.public_key')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Live Secret') }}</label>
                                            <input type="password" name="providers[flutterwave][live][secret]" value="{{ old('providers.flutterwave.live.secret', data_get($providers, 'flutterwave.live.secret')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Live Encryption Key') }}</label>
                                            <input type="password" name="providers[flutterwave][live][encryption_key]" value="{{ old('providers.flutterwave.live.encryption_key', data_get($providers, 'flutterwave.live.encryption_key')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Live Webhook Secret') }}</label>
                                            <input type="password" name="providers[flutterwave][live][webhook_secret]" value="{{ old('providers.flutterwave.live.webhook_secret', data_get($providers, 'flutterwave.live.webhook_secret')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Sandbox Public Key') }}</label>
                                            <input name="providers[flutterwave][sandbox][public_key]" value="{{ old('providers.flutterwave.sandbox.public_key', data_get($providers, 'flutterwave.sandbox.public_key')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Sandbox Secret') }}</label>
                                            <input type="password" name="providers[flutterwave][sandbox][secret]" value="{{ old('providers.flutterwave.sandbox.secret', data_get($providers, 'flutterwave.sandbox.secret')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Sandbox Encryption Key') }}</label>
                                            <input type="password" name="providers[flutterwave][sandbox][encryption_key]" value="{{ old('providers.flutterwave.sandbox.encryption_key', data_get($providers, 'flutterwave.sandbox.encryption_key')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Sandbox Webhook Secret') }}</label>
                                            <input type="password" name="providers[flutterwave][sandbox][webhook_secret]" value="{{ old('providers.flutterwave.sandbox.webhook_secret', data_get($providers, 'flutterwave.sandbox.webhook_secret')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Manual') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="hidden" name="providers[manual][enabled]" value="0">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="providers[manual][enabled]" value="1" {{ old('providers.manual.enabled', data_get($providers, 'manual.enabled', false)) ? 'checked' : '' }} class="sr-only peer">
                                        <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                                    </label>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Bank Name') }}</label>
                                            <input name="providers[manual][bank_name]" value="{{ old('providers.manual.bank_name', data_get($providers, 'manual.bank_name')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Account Name') }}</label>
                                            <input name="providers[manual][account_name]" value="{{ old('providers.manual.account_name', data_get($providers, 'manual.account_name')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Account Number') }}</label>
                                            <input name="providers[manual][account_number]" value="{{ old('providers.manual.account_number', data_get($providers, 'manual.account_number')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Instructions') }}</label>
                                            <textarea name="providers[manual][instructions]" rows="4" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">{{ old('providers.manual.instructions', data_get($providers, 'manual.instructions')) }}</textarea>
                                        </div>
                                        <div class="md:col-span-2">
                                            @php
                                                $manualQrPath = old('providers.manual.qr_image_path', data_get($providers, 'manual.qr_image_path'));
                                            @endphp
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('QR Code Image') }}</label>
                                            <input type="file" name="providers[manual][qr_image]" accept="image/*" class="mt-1 block w-full text-sm text-gray-700 dark:text-gray-200">

                                            @if(is_string($manualQrPath) && trim($manualQrPath) !== '')
                                                <div class="mt-2">
                                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($manualQrPath, '/')) }}" alt="{{ __('QR Code') }}" class="h-24 w-auto rounded border border-gray-200 dark:border-gray-700">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-end gap-4 pt-6 mt-6 border-t border-gray-200 dark:border-gray-700">
                    @admincan('admin.payment_methods.edit')
                        <x-button type="submit" variant="primary">{{ __('Save') }}</x-button>
                    @endadmincan
                </div>
            </div>
        </x-card>
    </form>
</div>
@endsection
