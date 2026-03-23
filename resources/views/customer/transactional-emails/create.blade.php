@extends('layouts.customer')

@section('title', 'Create Transactional Email')
@section('page-title', 'Create Transactional Email')

@section('content')
<div class="max-w-4xl">
    <x-card title="Transactional Email Template">
        <form method="POST" action="{{ route('customer.transactional-emails.store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    @error('name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Key</label>
                    <input type="text" name="key" id="key" value="{{ old('key') }}" placeholder="welcome-email" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave empty to auto-generate from name</p>
                    @error('key')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subject <span class="text-red-500">*</span></label>
                    <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    @error('subject')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                    <textarea name="description" id="description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">{{ old('description') }}</textarea>
                </div>

                <div class="sm:col-span-2">
                    <label for="html_content" class="block text-sm font-medium text-gray-700 dark:text-gray-300">HTML Content</label>
                    <textarea name="html_content" id="html_content" rows="10" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm font-mono">{{ old('html_content') }}</textarea>
                </div>

                <div class="sm:col-span-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="status" value="active" {{ old('status', 'active') == 'active' ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <x-button href="{{ route('customer.transactional-emails.index') }}" variant="secondary">Cancel</x-button>
                <x-button type="submit" variant="primary">Create Template</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection

