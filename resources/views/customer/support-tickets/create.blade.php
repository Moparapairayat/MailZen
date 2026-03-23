@extends('layouts.customer')

@section('title', __('New Support Ticket'))
@section('page-title', __('New Support Ticket'))

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/trix@2.1.1/dist/trix.css">
    <style>
        trix-editor {
            min-height: 160px;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/trix@2.1.1/dist/trix.umd.min.js" defer></script>
@endpush

@section('content')
<div class="space-y-6">
    <x-card>
        <form method="POST" action="{{ route('customer.support-tickets.store') }}" class="space-y-4">
            @csrf

            <div>
                <label for="subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Subject') }}</label>
                <input
                    id="subject"
                    name="subject"
                    value="{{ old('subject') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    required
                />
                @error('subject')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Priority') }}</label>
                <select
                    id="priority"
                    name="priority"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                >
                    @php
                        $value = old('priority', 'normal');
                    @endphp
                    <option value="low" {{ $value === 'low' ? 'selected' : '' }}>{{ __('Low') }}</option>
                    <option value="normal" {{ $value === 'normal' ? 'selected' : '' }}>{{ __('Normal') }}</option>
                    <option value="high" {{ $value === 'high' ? 'selected' : '' }}>{{ __('High') }}</option>
                </select>
                @error('priority')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="body" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Message') }}</label>
                <input id="body" type="hidden" name="body" value="{{ old('body') }}">
                <trix-editor input="body" class="mt-1 bg-white dark:bg-gray-700 rounded-md border border-gray-300 dark:border-gray-600"></trix-editor>
                @error('body')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-2">
                <x-button href="{{ route('customer.support-tickets.index') }}" variant="secondary">{{ __('Cancel') }}</x-button>
                <x-button type="submit" variant="primary">{{ __('Create Ticket') }}</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
