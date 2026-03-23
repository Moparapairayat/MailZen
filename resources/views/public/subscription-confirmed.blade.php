<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app('locale.direction')->dir() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $success ? 'Subscription Confirmed' : 'Confirmation Failed' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 text-center">
            @if($success)
                <div class="rounded-full bg-green-100 w-16 h-16 flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-gray-900">Subscription Confirmed!</h2>
                <p class="text-gray-600">{{ $message }}</p>
                @if(isset($list))
                    <p class="text-sm text-gray-500">You're now subscribed to: {{ $list->display_name ?? $list->name }}</p>
                @endif
            @else
                <div class="rounded-full bg-red-100 w-16 h-16 flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-gray-900">Confirmation Failed</h2>
                <p class="text-gray-600">{{ $message }}</p>
            @endif
        </div>
    </div>
</body>
</html>

