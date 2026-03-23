@extends('layouts.admin')

@section('title', __('Edit Tracking Domain'))
@section('page-title', __('Edit Tracking Domain'))

@section('content')
<div class="max-w-4xl">
    <x-card title="{{ __('Edit Tracking Domain') }}">
        <form method="POST" action="{{ route('admin.tracking-domains.update', $trackingDomain) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="domain" class="block text-sm font-medium text-admin-text-secondary">{{ __('Domain') }}</label>
                    <input
                        type="text"
                        id="domain"
                        value="{{ $trackingDomain->domain }}"
                        disabled
                        class="mt-1 block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm sm:text-sm opacity-75"
                    >
                </div>

                <div class="sm:col-span-2">
                    <label for="customer_id" class="block text-sm font-medium text-admin-text-secondary">{{ __('Customer (Optional)') }}</label>
                    <select
                        name="customer_id"
                        id="customer_id"
                        class="mt-1 block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                    >
                        <option value="">{{ __('No customer') }}</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ (string) old('customer_id', $trackingDomain->customer_id) === (string) $customer->id ? 'selected' : '' }}>
                                {{ $customer->email }}
                            </option>
                        @endforeach
                    </select>
                    @error('customer_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="notes" class="block text-sm font-medium text-admin-text-secondary">{{ __('Notes') }}</label>
                    <textarea
                        name="notes"
                        id="notes"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                    >{{ old('notes', $trackingDomain->notes) }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-admin-border">
                <x-button href="{{ route('admin.tracking-domains.show', $trackingDomain) }}" variant="secondary">{{ __('Cancel') }}</x-button>
                <x-button type="submit" variant="primary">{{ __('Update Domain') }}</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
