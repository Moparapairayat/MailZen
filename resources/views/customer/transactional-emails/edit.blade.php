@extends('layouts.customer')

@section('title', 'Edit Transactional Email')
@section('page-title', 'Edit Transactional Email')

@section('content')
<div class="max-w-4xl">
    <x-card title="Edit Transactional Email">
        <form method="POST" action="{{ route('customer.transactional-emails.update', $transactionalEmail) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $transactionalEmail->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                </div>

                <div>
                    <label for="key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Key</label>
                    <input type="text" name="key" id="key" value="{{ old('key', $transactionalEmail->key) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                </div>

                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subject <span class="text-red-500">*</span></label>
                    <input type="text" name="subject" id="subject" value="{{ old('subject', $transactionalEmail->subject) }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                </div>

                <div class="sm:col-span-2">
                    <label for="html_content" class="block text-sm font-medium text-gray-700 dark:text-gray-300">HTML Content</label>
                    <textarea name="html_content" id="html_content" rows="10" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm font-mono">{{ old('html_content', $transactionalEmail->html_content) }}</textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <x-button href="{{ route('customer.transactional-emails.show', $transactionalEmail) }}" variant="secondary">Cancel</x-button>
                <x-button type="submit" variant="primary">Update Template</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection

