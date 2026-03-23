@extends('layouts.admin')

@section('title', 'Add Locale')
@section('page-title', 'Add Locale')

@section('content')
<div class="space-y-6">
    <x-card title="Add Locale">
        <form method="POST" action="{{ route('admin.translations.locales.store') }}">
            @csrf

            <div class="space-y-4">
                <div>
                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Locale Code</label>
                    <input
                        type="text"
                        name="code"
                        value="{{ old('code') }}"
                        placeholder="e.g. en, en_US, ar"
                        class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                    >
                    @error('code')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Name</label>
                    <input
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="e.g. English, US English"
                        class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-2">
                    <label class="inline-flex items-center gap-2">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Active</span>
                    </label>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-between">
                <x-button href="{{ route('admin.translations.locales.index') }}" variant="secondary">Back</x-button>
                <x-button type="submit" variant="primary">Create</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
