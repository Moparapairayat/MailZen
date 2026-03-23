@extends('install.layout', ['title' => 'Server Requirements', 'step' => 2])

@section('content')
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Server Requirements</h1>

    <div class="space-y-6">
        <div class="rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 bg-gray-50 flex items-center justify-between">
                <div class="font-semibold text-gray-800">PHP version 8.1.0 required</div>
                <div class="font-semibold {{ $phpOk ? 'text-green-600' : 'text-red-600' }}">{{ $phpVersion }}</div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 bg-gray-50 font-semibold text-gray-800">Extensions</div>
            <div class="divide-y divide-gray-100">
                @foreach($extResults as $ext => $ok)
                    <div class="px-5 py-3 flex items-center justify-between">
                        <div class="text-gray-700">{{ $ext }}</div>
                        <div class="{{ $ok ? 'text-green-600' : 'text-red-600' }} font-semibold">{{ $ok ? 'OK' : 'Missing' }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 bg-gray-50 font-semibold text-gray-800">Permissions</div>
            <div class="divide-y divide-gray-100">
                @foreach($writable as $path => $ok)
                    <div class="px-5 py-3 flex items-center justify-between">
                        <div class="text-gray-700 break-all">{{ $path }}</div>
                        <div class="{{ $ok ? 'text-green-600' : 'text-red-600' }} font-semibold">{{ $ok ? 'Writable' : 'Not writable' }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="pt-2">
            @if(!$allOk)
                <div class="mb-4 rounded-lg border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800">
                    Please fix the failing items above before continuing.
                </div>
            @endif

            <a href="{{ route('install.setup') }}" class="inline-flex items-center justify-center w-full rounded-xl {{ $allOk ? 'bg-[#1E5FEA] hover:bg-[#184FC6]' : 'bg-gray-300 cursor-not-allowed' }} px-5 py-3 text-white font-semibold" {{ $allOk ? '' : 'aria-disabled=true tabindex=-1' }}>
                Next
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>
    </div>
@endsection
