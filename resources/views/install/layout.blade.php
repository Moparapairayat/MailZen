<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', (string) config('app.fallback_locale', 'en')) }}" dir="{{ app('locale.direction')->dir((string) config('app.fallback_locale', 'en')) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Install' }} - {{ config('app.name', 'MailZen') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50">
<div class="min-h-screen flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-2xl">
        <div class="mb-6">
            <div class="flex items-center justify-between text-sm text-gray-500">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center {{ ($step ?? 1) >= 1 ? 'bg-[#1E5FEA] text-white' : 'bg-gray-200 text-gray-700' }}">1</div>
                    <span class="font-medium">Welcome</span>
                </div>
                <div class="flex-1 mx-3 h-px bg-gray-200"></div>
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center {{ ($step ?? 1) >= 2 ? 'bg-[#1E5FEA] text-white' : 'bg-gray-200 text-gray-700' }}">2</div>
                    <span class="font-medium">Server</span>
                </div>
                <div class="flex-1 mx-3 h-px bg-gray-200"></div>
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center {{ ($step ?? 1) >= 3 ? 'bg-[#1E5FEA] text-white' : 'bg-gray-200 text-gray-700' }}">3</div>
                    <span class="font-medium">Setup</span>
                </div>
                <div class="flex-1 mx-3 h-px bg-gray-200"></div>
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center {{ ($step ?? 1) >= 4 ? 'bg-[#1E5FEA] text-white' : 'bg-gray-200 text-gray-700' }}">4</div>
                    <span class="font-medium">Done</span>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-sm border border-gray-200 rounded-2xl p-8">
            @if($errors->any())
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    <div class="font-semibold mb-2">Please fix the errors below</div>
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</div>
</body>
</html>
