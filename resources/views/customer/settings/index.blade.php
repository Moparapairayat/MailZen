@extends('layouts.customer')

@section('title', __('Settings'))
@section('page-title', __('Settings'))

@section('content')
<div class="space-y-6">
    <x-card>
        <h3 class="text-lg font-semibold">{{ __('Account') }}</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Basic account settings are managed from your profile page.') }}</p>

        <div class="mt-4 flex items-center gap-3">
            <x-button href="{{ route('customer.profile.edit') }}" variant="primary">{{ __('Edit Profile') }}</x-button>
            <x-button href="{{ route('customer.billing.index') }}" variant="secondary">{{ __('Billing') }}</x-button>
        </div>
    </x-card>

    <x-card>
        <h3 class="text-lg font-semibold">{{ __('Email & Password') }}</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Manage your sign-in credentials.') }}</p>

        <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Email') }}</h4>
                <form method="POST" action="{{ route('customer.settings.email.update') }}" class="mt-4 space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Email address') }}</label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            value="{{ old('email', $customer->email) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            autocomplete="email"
                            required
                        />
                        @error('email', 'updateEmail')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end">
                        @customercan('settings.permissions.can_edit_settings')
                            <x-button type="submit" variant="primary">{{ __('Update Email') }}</x-button>
                        @endcustomercan
                    </div>
                </form>
            </div>

            <div>
                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Password') }}</h4>
                <form method="POST" action="{{ route('customer.settings.password.update') }}" class="mt-4 space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Current password') }}</label>
                        <input
                            type="password"
                            name="current_password"
                            id="current_password"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            autocomplete="current-password"
                            required
                        />
                        @error('current_password', 'updatePassword')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('New password') }}</label>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            autocomplete="new-password"
                            required
                        />
                        @error('password', 'updatePassword')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Confirm new password') }}</label>
                        <input
                            type="password"
                            name="password_confirmation"
                            id="password_confirmation"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            autocomplete="new-password"
                            required
                        />
                    </div>

                    <div class="flex items-center justify-end">
                        @customercan('settings.permissions.can_edit_settings')
                            <x-button type="submit" variant="primary">{{ __('Update Password') }}</x-button>
                        @endcustomercan
                    </div>
                </form>
            </div>
        </div>
    </x-card>

    <x-card>
        <h3 class="text-lg font-semibold">{{ __('Preferences') }}</h3>
        <form method="POST" action="{{ route('customer.settings.update') }}" class="mt-4 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="timezone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Timezone') }}</label>
                <select
                    name="timezone"
                    id="timezone"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                >
                    @foreach(($timezones ?? []) as $tz)
                        <option value="{{ $tz }}" {{ old('timezone', $customer->timezone ?? 'UTC') === $tz ? 'selected' : '' }}>
                            {{ $tz }}
                        </option>
                    @endforeach
                </select>
                @error('timezone')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="openai_api_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('OpenAI API Key') }}</label>
                    <div class="relative" data-secret-wrapper>
                        <input
                            type="password"
                            name="openai_api_key"
                            id="openai_api_key"
                            value="{{ !empty($customer->openai_api_key) ? '********' : '' }}"
                            autocomplete="new-password"
                            placeholder="{{ __('Leave blank to keep current key') }}"
                            data-secret-url="{{ route('customer.settings.secret', ['key' => 'openai_api_key']) }}"
                            data-secret-input
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm pr-10"
                        />

                        <button
                            type="button"
                            data-toggle-secret
                            class="absolute inset-y-0 right-0 flex items-center px-3 mt-1 text-gray-500 hover:text-gray-700 dark:text-gray-300"
                            aria-label="{{ __('Toggle secret visibility') }}"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                    @error('openai_api_key')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="gemini_api_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Gemini API Key') }}</label>
                    <div class="relative" data-secret-wrapper>
                        <input
                            type="password"
                            name="gemini_api_key"
                            id="gemini_api_key"
                            value="{{ !empty($customer->gemini_api_key) ? '********' : '' }}"
                            autocomplete="new-password"
                            placeholder="{{ __('Leave blank to keep current key') }}"
                            data-secret-url="{{ route('customer.settings.secret', ['key' => 'gemini_api_key']) }}"
                            data-secret-input
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm pr-10"
                        />

                        <button
                            type="button"
                            data-toggle-secret
                            class="absolute inset-y-0 right-0 flex items-center px-3 mt-1 text-gray-500 hover:text-gray-700 dark:text-gray-300"
                            aria-label="{{ __('Toggle secret visibility') }}"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                    @error('gemini_api_key')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="ai_own_daily_limit" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('AI Daily Limit (Own Keys)') }}</label>
                    <input
                        type="number"
                        name="ai_own_daily_limit"
                        id="ai_own_daily_limit"
                        value="{{ old('ai_own_daily_limit', (int) ($customer->ai_own_daily_limit ?? 0)) }}"
                        min="0"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    />
                    @error('ai_own_daily_limit')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ __('Usage today:') }} {{ (int) ($customer->ai_own_daily_usage ?? 0) }}
                        / {{ ((int) ($customer->ai_own_daily_limit ?? 0)) > 0 ? (int) ($customer->ai_own_daily_limit ?? 0) : __('Unlimited') }}
                    </p>
                </div>

                <div>
                    <label for="ai_own_monthly_limit" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('AI Monthly Limit (Own Keys)') }}</label>
                    <input
                        type="number"
                        name="ai_own_monthly_limit"
                        id="ai_own_monthly_limit"
                        value="{{ old('ai_own_monthly_limit', (int) ($customer->ai_own_monthly_limit ?? 0)) }}"
                        min="0"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    />
                    @error('ai_own_monthly_limit')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ __('Usage this month:') }} {{ (int) ($customer->ai_own_monthly_usage ?? 0) }}
                        / {{ ((int) ($customer->ai_own_monthly_limit ?? 0)) > 0 ? (int) ($customer->ai_own_monthly_limit ?? 0) : __('Unlimited') }}
                    </p>
                </div>
            </div>

            <div>
                <label for="language" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Language') }}</label>
                <select
                    name="language"
                    id="language"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                >
                    @foreach(($activeLocales ?? []) as $loc)
                        <option value="{{ $loc->code }}" {{ old('language', $customer->language ?? '') === $loc->code ? 'selected' : '' }}>
                            {{ $loc->code }} — {{ $loc->name }}
                        </option>
                    @endforeach
                </select>
                @error('language')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end">
                @customercan('settings.permissions.can_edit_settings')
                    <x-button type="submit" variant="primary">{{ __('Save Preferences') }}</x-button>
                @endcustomercan
            </div>
        </form>

        <div class="mt-4 text-sm text-gray-700 dark:text-gray-300">
            <div><strong>{{ __('Current Timezone:') }}</strong> {{ $customer->timezone ?? '—' }}</div>
            <div><strong>{{ __('Language:') }}</strong> {{ $customer->language ?? '—' }}</div>
            <div><strong>{{ __('Currency:') }}</strong> {{ $customer->currency ?? '—' }}</div>
        </div>
    </x-card>
</div>
@endsection
