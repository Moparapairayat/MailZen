@extends('layouts.admin')

@section('title', 'Settings')
@section('page-title', 'Settings')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-md bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-md bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
        <nav class="-mb-px flex min-w-max space-x-6 sm:space-x-8 px-2 sm:px-0" aria-label="Tabs">
            <a
                href="{{ route('admin.settings.index') }}"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap shrink-0 py-3 sm:py-4 px-2 sm:px-1 font-medium text-sm"
            >
                Settings
            </a>

            @admincan('admin.translations.access')
                <a
                    href="{{ route('admin.translations.locales.index') }}"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap shrink-0 py-3 sm:py-4 px-2 sm:px-1 font-medium text-sm"
                >
                    Translations
                </a>
            @endadmincan

            <a
                href="{{ route('admin.settings.logs') }}"
                class="!border-primary-500 text-primary-600 dark:text-primary-400 border-b-2 whitespace-nowrap shrink-0 py-3 sm:py-4 px-2 sm:px-1 font-medium text-sm"
            >
                Logs
            </a>
        </nav>
    </div>

    <x-card>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Application Logs</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Showing latest log entries from the end of the log file.</p>
            </div>

            <form method="GET" action="{{ route('admin.settings.logs') }}" class="flex items-center gap-2">
                <label for="per_page" class="text-sm text-gray-600 dark:text-gray-300">Per page</label>
                <select
                    id="per_page"
                    name="per_page"
                    class="rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    onchange="this.form.submit()"
                >
                    @foreach([10,20,30,50] as $size)
                        <option value="{{ $size }}" {{ (int) $perPage === (int) $size ? 'selected' : '' }}>{{ $size }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="mt-6 space-y-4">
            @if($logs->count() === 0)
                <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                    <p>No log entries found.</p>
                </div>
            @else
                @foreach($logs as $entry)
                    <pre class="text-xs whitespace-pre-wrap break-words rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 text-gray-900 dark:text-gray-100 overflow-auto">{{ $entry }}</pre>
                @endforeach

                <div class="pt-2">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </x-card>
</div>
@endsection
