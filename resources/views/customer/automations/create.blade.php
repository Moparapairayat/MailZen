@extends('layouts.customer')

@section('title', 'Create Automation')
@section('page-title', 'Create Automation')

@section('content')
<div class="max-w-2xl">
    <x-card>
        <form method="POST" action="{{ route('customer.automations.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Name</label>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    required
                >
            </div>

            <div class="flex items-center justify-end gap-2">
                <x-button href="{{ route('customer.automations.index') }}" variant="secondary">Cancel</x-button>
                <x-button type="submit" variant="primary">Create</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
