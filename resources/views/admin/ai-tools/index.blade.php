@extends('layouts.admin')

@section('title', __('AI Tools'))
@section('page-title', __('AI Tools'))

@section('content')
<div class="space-y-6">
    <x-card>
        <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Admin AI tools use the global AI API keys configured in settings.') }}</div>
    </x-card>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <a href="{{ route('admin.ai-tools.dashboard') }}" class="block rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 hover:border-primary-300 dark:hover:border-primary-700 transition-colors">
            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('AI Dashboard') }}</div>
            <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('View token usage, limits and estimated cost.') }}</div>
        </a>
        <a href="{{ route('admin.ai-tools.email-text-generator') }}" class="block rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 hover:border-primary-300 dark:hover:border-primary-700 transition-colors">
            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Email Text Generator') }}</div>
            <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Generate subject + body from structured inputs.') }}</div>
        </a>
    </div>
</div>
@endsection
