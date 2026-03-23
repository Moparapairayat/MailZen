@extends('layouts.customer')

@section('title', 'Create form')
@section('page-title', 'Create form')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Create form</h2>
        <a href="{{ route('customer.forms.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
            Back
        </a>
    </div>

    <form method="POST" action="{{ route('customer.forms.store') }}" class="space-y-6" x-data="{ template: '{{ old('template', 'default') }}' }">
        @csrf

        <x-card>
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Enter form's name here <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name', 'Untitled form') }}"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="list_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Select a mail list <span class="text-red-500">*</span>
                    </label>
                    <select
                        name="list_id"
                        id="list_id"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >
                        <option value="">--</option>
                        @foreach($lists as $list)
                            <option value="{{ $list->id }}" {{ (string) old('list_id') === (string) $list->id ? 'selected' : '' }}>
                                {{ $list->display_name ?? $list->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('list_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Form Type <span class="text-red-500">*</span>
                    </label>
                    <select
                        name="type"
                        id="type"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >
                        <option value="embedded" {{ old('type', 'embedded') === 'embedded' ? 'selected' : '' }}>Embedded HTML Form</option>
                        <option value="popup" {{ old('type') === 'popup' ? 'selected' : '' }}>Popup</option>
                        <option value="api" {{ old('type') === 'api' ? 'selected' : '' }}>API Endpoint</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Form Title (public)
                    </label>
                    <input
                        type="text"
                        name="title"
                        id="title"
                        value="{{ old('title') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >
                    @error('title')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </x-card>

        <div class="flex items-center justify-between">
            <div>
                <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">Select one from the base templates below</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Default</div>
            </div>

            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-gray-700 rounded-md shadow-sm hover:bg-gray-800 dark:bg-gray-600 dark:hover:bg-gray-500">
                Start Design
            </button>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($templates as $tpl)
                <label class="block cursor-pointer">
                    <input
                        type="radio"
                        name="template"
                        value="{{ $tpl['key'] }}"
                        class="sr-only"
                        @change="template = '{{ $tpl['key'] }}'"
                        {{ old('template', 'default') === $tpl['key'] ? 'checked' : '' }}
                    >

                    <div class="rounded-lg border p-3 transition-colors"
                        :class="template === '{{ $tpl['key'] }}' ? 'border-primary-500 ring-2 ring-primary-500/30 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800'">
                        <div class="aspect-[4/3] rounded-md overflow-hidden border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">
                            <div class="h-10 bg-gray-100 dark:bg-gray-800"></div>
                            <div class="p-3 space-y-2">
                                <div class="h-3 w-2/3 bg-gray-200 dark:bg-gray-700 rounded"></div>
                                <div class="h-8 w-full bg-gray-100 dark:bg-gray-800 rounded"></div>
                                <div class="h-8 w-full bg-gray-100 dark:bg-gray-800 rounded"></div>
                                <div class="h-8 w-1/2 bg-gray-800 dark:bg-gray-200 rounded"></div>
                            </div>
                        </div>

                        <div class="mt-3 flex items-center justify-between">
                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ $tpl['label'] }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Select</div>
                        </div>
                    </div>
                </label>
            @endforeach
        </div>

        @if($errors->has('template'))
            <p class="text-sm text-red-600 dark:text-red-400">{{ $errors->first('template') }}</p>
        @endif
    </form>
</div>
@endsection
