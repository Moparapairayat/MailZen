@extends('layouts.customer')

@section('title', __('Profile'))
@section('page-title', __('Profile'))

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Profile') }}</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Update your personal information, bio and social links. This information may appear in your emails or account UI.') }}
            </p>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
            <form action="{{ route('customer.profile.update') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <!-- Avatar -->
                <div>
                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Avatar') }}</h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ __('This will be used in your account header and may be shown in emails where applicable.') }}
                    </p>
                    <div class="mt-4 flex items-center gap-4">
                        @php
                            $avatarUrl = $customer->avatar_path ? \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($customer->avatar_path, '/')) : null;
                        @endphp
                        <div class="w-16 h-16 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center overflow-hidden">
                            @if($avatarUrl)
                                <img src="{{ $avatarUrl }}" alt="{{ $customer->full_name }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-sm font-semibold text-primary-700 dark:text-primary-300">
                                    {{ strtoupper(Str::substr($customer->first_name, 0, 1) . Str::substr($customer->last_name, 0, 1)) }}
                                </span>
                            @endif
                        </div>
                        <div>
                            <label class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md shadow-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586A2 2 0 0118.828 12H20a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-2a2 2 0 012-2h1.172a2 2 0 001.414-.586L8 14m4-10h.01M12 4a2 2 0 11-.01 4.01A2 2 0 0112 4z" />
                                </svg>
                                <span>{{ __('Upload new') }}</span>
                                <input type="file" name="avatar" class="hidden" accept="image/*">
                            </label>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ __('PNG or JPG up to 2MB.') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Billing details') }}</h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ __('This information is used to calculate VAT or sales tax during checkout.') }}
                    </p>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label for="tax_id" class="block text-xs font-medium text-gray-700 dark:text-gray-200">
                                {{ __('Tax ID') }}
                            </label>
                            <input
                                type="text"
                                name="tax_id"
                                id="tax_id"
                                value="{{ old('tax_id', $customer->tax_id) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                            >
                            @error('tax_id')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="billing_address_line_1" class="block text-xs font-medium text-gray-700 dark:text-gray-200">
                                {{ __('Address line 1') }}
                            </label>
                            <input
                                type="text"
                                name="billing_address[address_line_1]"
                                id="billing_address_line_1"
                                value="{{ old('billing_address.address_line_1', data_get($customer->billing_address, 'address_line_1')) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                            >
                            @error('billing_address.address_line_1')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="billing_address_line_2" class="block text-xs font-medium text-gray-700 dark:text-gray-200">
                                {{ __('Address line 2') }}
                            </label>
                            <input
                                type="text"
                                name="billing_address[address_line_2]"
                                id="billing_address_line_2"
                                value="{{ old('billing_address.address_line_2', data_get($customer->billing_address, 'address_line_2')) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                            >
                            @error('billing_address.address_line_2')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="billing_city" class="block text-xs font-medium text-gray-700 dark:text-gray-200">
                                {{ __('City') }}
                            </label>
                            <input
                                type="text"
                                name="billing_address[city]"
                                id="billing_city"
                                value="{{ old('billing_address.city', data_get($customer->billing_address, 'city')) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                            >
                            @error('billing_address.city')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="billing_state" class="block text-xs font-medium text-gray-700 dark:text-gray-200">
                                {{ __('State / Region') }}
                            </label>
                            <input
                                type="text"
                                name="billing_address[state]"
                                id="billing_state"
                                value="{{ old('billing_address.state', data_get($customer->billing_address, 'state')) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                            >
                            @error('billing_address.state')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="billing_postal_code" class="block text-xs font-medium text-gray-700 dark:text-gray-200">
                                {{ __('Postal code') }}
                            </label>
                            <input
                                type="text"
                                name="billing_address[postal_code]"
                                id="billing_postal_code"
                                value="{{ old('billing_address.postal_code', data_get($customer->billing_address, 'postal_code')) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                            >
                            @error('billing_address.postal_code')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="billing_country" class="block text-xs font-medium text-gray-700 dark:text-gray-200">
                                {{ __('Country') }}
                            </label>
                            <input
                                type="text"
                                name="billing_address[country]"
                                id="billing_country"
                                value="{{ old('billing_address.country', data_get($customer->billing_address, 'country')) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                            >
                            @error('billing_address.country')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Basic info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                            {{ __('First name') }}
                        </label>
                        <input
                            type="text"
                            name="first_name"
                            id="first_name"
                            value="{{ old('first_name', $customer->first_name) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                            required
                        >
                        @error('first_name')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                            {{ __('Last name') }}
                        </label>
                        <input
                            type="text"
                            name="last_name"
                            id="last_name"
                            value="{{ old('last_name', $customer->last_name) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                            required
                        >
                        @error('last_name')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Bio -->
                <div>
                    <label for="bio" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        {{ __('Bio') }}
                    </label>
                    <textarea
                        name="bio"
                        id="bio"
                        rows="4"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                        placeholder="{{ __('Tell your subscribers a bit about you or your brand.') }}"
                    >{{ old('bio', $customer->bio) }}</textarea>
                    @error('bio')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Social links -->
                <div>
                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Social links') }}</h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ __('These links can be used in your email footers or profile areas.') }}
                    </p>
                    <div class="mt-4 grid grid-cols-1 gap-4">
                        <div>
                            <label for="website_url" class="block text-xs font-medium text-gray-700 dark:text-gray-200">
                                {{ __('Website') }}
                            </label>
                            <input
                                type="url"
                                name="website_url"
                                id="website_url"
                                value="{{ old('website_url', $customer->website_url) }}"
                                placeholder="https://example.com"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                            >
                            @error('website_url')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="twitter_url" class="block text-xs font-medium text-gray-700 dark:text-gray-200">
                                {{ __('X / Twitter') }}
                            </label>
                            <input
                                type="url"
                                name="twitter_url"
                                id="twitter_url"
                                value="{{ old('twitter_url', $customer->twitter_url) }}"
                                placeholder="https://x.com/username"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                            >
                            @error('twitter_url')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="facebook_url" class="block text-xs font-medium text-gray-700 dark:text-gray-200">
                                {{ __('Facebook') }}
                            </label>
                            <input
                                type="url"
                                name="facebook_url"
                                id="facebook_url"
                                value="{{ old('facebook_url', $customer->facebook_url) }}"
                                placeholder="https://facebook.com/username"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                            >
                            @error('facebook_url')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="linkedin_url" class="block text-xs font-medium text-gray-700 dark:text-gray-200">
                                {{ __('LinkedIn') }}
                            </label>
                            <input
                                type="url"
                                name="linkedin_url"
                                id="linkedin_url"
                                value="{{ old('linkedin_url', $customer->linkedin_url) }}"
                                placeholder="https://linkedin.com/in/username"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                            >
                            @error('linkedin_url')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                    <button
                        type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                    >
                        {{ __('Save changes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection


