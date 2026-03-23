@extends('install.layout', ['title' => 'Complete', 'step' => 4])

@section('content')
    <div class="text-center">
        <div class="mx-auto w-14 h-14 rounded-2xl bg-green-100 flex items-center justify-center mb-4">
            <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Complete & Congratulations</h1>
        <p class="text-gray-600 mb-8">Your application has been installed successfully.</p>

        <a href="{{ route('login') }}" class="inline-flex items-center justify-center w-full rounded-xl bg-[#1E5FEA] px-5 py-3 text-white font-semibold hover:bg-[#184FC6]">
            Go to Login
        </a>
    </div>
@endsection
