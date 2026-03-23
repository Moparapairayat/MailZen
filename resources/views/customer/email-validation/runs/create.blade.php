@extends('layouts.customer')

@section('title', 'Run Email Validation')
@section('page-title', 'Run Email Validation')

@section('content')
<x-card>
    <form method="POST" action="{{ route('customer.email-validation.runs.store') }}" class="space-y-4">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Validation Tool</label>
                <select name="tool_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                    @foreach($tools as $tool)
                        <option value="{{ $tool->id }}" {{ (string) old('tool_id') === (string) $tool->id ? 'selected' : '' }}>{{ $tool->name }} ({{ strtoupper($tool->provider) }})</option>
                    @endforeach
                </select>
                @error('tool_id')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email List</label>
                <select name="list_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                    @foreach($lists as $list)
                        <option value="{{ $list->id }}" {{ (string) old('list_id') === (string) $list->id ? 'selected' : '' }}>{{ $list->name }}</option>
                    @endforeach
                </select>
                @error('list_id')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Invalid Email Action</label>
            <select name="invalid_action" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                <option value="unsubscribe" {{ old('invalid_action', 'unsubscribe') === 'unsubscribe' ? 'selected' : '' }}>Unsubscribe</option>
                <option value="mark_spam" {{ old('invalid_action') === 'mark_spam' ? 'selected' : '' }}>Mark Spam</option>
                <option value="delete" {{ old('invalid_action') === 'delete' ? 'selected' : '' }}>Delete</option>
            </select>
            @error('invalid_action')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-end gap-3">
            <x-button href="{{ route('customer.email-validation.runs.index') }}" variant="secondary">Cancel</x-button>
            @customercan('email_validation.access')
                <x-button type="submit" variant="primary">Start</x-button>
            @endcustomercan
        </div>
    </form>
</x-card>
@endsection
