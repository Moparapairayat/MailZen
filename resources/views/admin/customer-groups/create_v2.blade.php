@extends('layouts.admin')

@section('title', 'Create Customer Group')
@section('page-title', 'Create Customer Group')

@section('content')
<div class="max-w-7xl" x-data="{ activeTab: 'email_lists' }">
    <form method="POST" action="{{ route('admin.customer-groups.store') }}">
        @csrf

        <div class="space-y-6">
            <x-card title="Basic Information">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name *</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                        <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </x-card>

            @include('admin.customer-groups._form', ['defaultSettings' => $defaultSettings])

            <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('admin.customer-groups.index') }}" class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100">Cancel</a>
                <x-button type="submit" variant="primary">Create Customer Group</x-button>
            </div>
        </div>
    </form>
</div>
@endsection
