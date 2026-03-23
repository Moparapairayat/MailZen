@extends('layouts.admin')

@section('title', 'Edit Locale')
@section('page-title', 'Edit Locale')

@section('content')
<div class="space-y-6">
    <x-card title="Edit Locale">
        <form method="POST" action="{{ route('admin.translations.locales.update', ['locale' => $locale->code]) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <div>
                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Locale Code</label>
                    <input
                        type="text"
                        name="code"
                        value="{{ old('code', $locale->code) }}"
                        class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                        readonly
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
                        value="{{ old('name', $locale->name) }}"
                        class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Flag (PNG)</label>
                    <input
                        type="file"
                        name="flag"
                        accept="image/png"
                        class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                    >
                    @error('flag')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror

                    @if(!empty($locale->flag))
                        <div class="mt-2">
                            <img
                                src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($locale->flag, '/')) }}"
                                alt="{{ $locale->code }}"
                                class="h-8 w-8 rounded border border-gray-300 dark:border-gray-700"
                            >
                        </div>
                    @endif
                </div>

                <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                    <x-button href="{{ route('admin.translations.bulk.edit', ['locale' => $locale->code]) }}" variant="secondary">Manage Strings</x-button>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-between">
                <x-button href="{{ route('admin.translations.locales.index') }}" variant="secondary">Back</x-button>
                <x-button type="submit" variant="primary">Save</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
