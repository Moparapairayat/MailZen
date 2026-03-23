<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app('locale.direction')->dir() }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-bind:class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('Reset Password') }} - {{ config('app.name', 'MailZen') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-md bg-white rounded-xl border border-gray-200 shadow-sm p-8">
            <h1 class="text-2xl font-bold text-gray-900">{{ __('Reset password') }}</h1>
            <p class="mt-2 text-sm text-gray-600">{{ __('Choose a new password for your account.') }}</p>

            @if($errors->any())
                <div class="mt-6 rounded-md bg-red-50 p-4 border border-red-200">
                    <div class="text-sm font-medium text-red-800">{{ __('There were errors with your submission') }}</div>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-6">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}" />

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Email') }}</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        autocomplete="email"
                        required
                        value="{{ old('email', $email ?? '') }}"
                        class="block w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus-visible:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900 placeholder-gray-400 sm:text-sm"
                        placeholder="{{ __('Enter your email') }}"
                    />
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">{{ __('New Password') }}</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="new-password"
                        required
                        class="block w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus-visible:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900 placeholder-gray-400 sm:text-sm"
                        placeholder="{{ __('Enter new password') }}"
                    />
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Confirm Password') }}</label>
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        required
                        class="block w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus-visible:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900 placeholder-gray-400 sm:text-sm"
                        placeholder="{{ __('Confirm new password') }}"
                    />
                </div>

                <button type="submit" class="w-full inline-flex justify-center items-center py-3 px-4 rounded-lg text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    {{ __('Reset Password') }}
                </button>

                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-sm font-medium text-primary-600 hover:text-primary-700">{{ __('Back to login') }}</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
