@extends('layouts.customer')

@section('title', 'Create Auto Responder')
@section('page-title', 'Create Auto Responder')

@section('content')
<div class="max-w-2xl">
    <x-card title="Create Auto Responder">
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
            Create an auto responder to automatically send emails when subscribers perform certain actions. 
            After creating, you'll be taken to the visual workflow builder to design your email sequence.
        </p>

        <form method="POST" action="{{ route('customer.auto-responders.store') }}" class="space-y-6">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="e.g., Welcome Series" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                @error('name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="list_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email List <span class="text-red-500">*</span></label>
                <select name="list_id" id="list_id" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    <option value="">Select a list...</option>
                    @foreach($emailLists as $list)
                        <option value="{{ $list->id }}" {{ old('list_id') == $list->id ? 'selected' : '' }}>{{ $list->name }}</option>
                    @endforeach
                </select>
                @error('list_id')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="trigger" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Trigger <span class="text-red-500">*</span></label>
                <select name="trigger" id="trigger" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    <option value="subscriber_added" {{ old('trigger', 'subscriber_confirmed') == 'subscriber_added' ? 'selected' : '' }}>Subscriber Added</option>
                    <option value="subscriber_confirmed" {{ old('trigger', 'subscriber_confirmed') == 'subscriber_confirmed' ? 'selected' : '' }}>Subscriber Confirmed</option>
                    <option value="subscriber_unsubscribed" {{ old('trigger') == 'subscriber_unsubscribed' ? 'selected' : '' }}>Subscriber Unsubscribed</option>
                </select>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">When should this auto responder start?</p>
                @error('trigger')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <x-button href="{{ route('customer.auto-responders.index') }}" variant="secondary">Cancel</x-button>
                <x-button type="submit" variant="primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                    Continue to Workflow Builder
                </x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection

