@extends('layouts.admin')

@section('title', 'Add Translation')
@section('page-title', 'Add Translation')

@section('content')
<div class="space-y-6">
    <x-card title="Add Translation">
        <form method="POST" action="{{ route('admin.translations.locales.lines.store', $translation_locale) }}">
            @csrf

            <div class="space-y-4">
                <div class="text-sm text-admin-text-secondary">
                    Locale: <span class="text-admin-text-primary font-medium">{{ $translation_locale->code }}</span> — {{ $translation_locale->name }}
                </div>

                <div>
                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Group</label>
                    <input
                        type="text"
                        name="group"
                        value="{{ old('group', $line->group ?? '*') }}"
                        placeholder="Use * for JSON-style translations"
                        class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                    >
                    @error('group')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Key</label>
                    <input
                        type="text"
                        name="key"
                        value="{{ old('key') }}"
                        placeholder="e.g. auth.failed"
                        class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                    >
                    @error('key')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Text</label>
                    <textarea
                        name="text"
                        rows="4"
                        class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                    >{{ old('text') }}</textarea>
                    @error('text')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 flex items-center justify-between">
                <x-button href="{{ route('admin.translations.bulk.edit', $translation_locale) }}" variant="secondary">Back</x-button>
                <x-button type="submit" variant="primary">Create</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
