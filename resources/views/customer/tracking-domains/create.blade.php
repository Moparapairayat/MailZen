@extends('layouts.customer')

@section('title', 'Add Tracking Domain')
@section('page-title', 'Add Tracking Domain')

@section('content')
<div class="max-w-2xl">
    <x-card title="Add Tracking Domain">
        <form method="POST" action="{{ route('customer.tracking-domains.store') }}" class="space-y-6">
            @csrf

            <div>
                <label for="domain" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Domain <span class="text-red-500">*</span></label>
                <input type="text" name="domain" id="domain" value="{{ old('domain') }}" required placeholder="track.example.com" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                @error('domain')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Enter the domain you want to use for tracking links (e.g., track.example.com)</p>
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                <textarea name="notes" id="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">{{ old('notes') }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('customer.tracking-domains.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Cancel</a>
                    @customercan('domains.tracking_domains.permissions.can_create_tracking_domains')
                        <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded hover:bg-primary-700">Create Tracking Domain</button>
                    @endcustomercan
                </div>
            </div>
        </form>
    </x-card>
</div>
@endsection
