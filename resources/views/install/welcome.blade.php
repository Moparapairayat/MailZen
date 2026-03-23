@extends('install.layout', ['title' => 'Welcome', 'step' => 1])

@section('content')
    <div class="text-center">
        <div class="mx-auto w-14 h-14 rounded-2xl bg-[#1E5FEA]/10 flex items-center justify-center mb-4">
            <svg class="w-7 h-7 text-[#1E5FEA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z" />
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Let’s start.</h1>
        <p class="text-gray-600 mb-8">We’ll guide you through a quick installation to get your app running.</p>

        <a href="{{ route('install.requirements') }}" class="inline-flex items-center justify-center w-full rounded-xl bg-[#1E5FEA] px-5 py-3 text-white font-semibold hover:bg-[#184FC6]">
            Next
            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </a>
    </div>
@endsection
