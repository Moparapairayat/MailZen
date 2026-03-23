@extends('layouts.admin')

@section('title', __('Edit Sending Domain'))
@section('page-title', __('Edit Sending Domain'))

@section('content')
<div class="max-w-4xl">
    <x-card title="{{ __('Edit Sending Domain') }}">
        <form method="POST" action="{{ route('admin.sending-domains.update', $sendingDomain) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="domain" class="block text-sm font-medium text-admin-text-secondary">{{ __('Domain') }}</label>
                    <input
                        type="text"
                        id="domain"
                        value="{{ $sendingDomain->domain }}"
                        disabled
                        class="mt-1 block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm sm:text-sm opacity-75"
                    >
                </div>

                <div class="sm:col-span-2">
                    <label for="spf_record" class="block text-sm font-medium text-admin-text-secondary">{{ __('SPF Record (Optional)') }}</label>
                    <textarea
                        name="spf_record"
                        id="spf_record"
                        rows="2"
                        class="mt-1 block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                    >{{ old('spf_record', $sendingDomain->spf_record) }}</textarea>
                    @error('spf_record')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="dmarc_record" class="block text-sm font-medium text-admin-text-secondary">{{ __('DMARC Record (Optional)') }}</label>
                    <textarea
                        name="dmarc_record"
                        id="dmarc_record"
                        rows="2"
                        class="mt-1 block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                    >{{ old('dmarc_record', $sendingDomain->dmarc_record) }}</textarea>
                    @error('dmarc_record')
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
                    >{{ old('notes', $sendingDomain->notes) }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-admin-border">
                <x-button href="{{ route('admin.sending-domains.show', $sendingDomain) }}" variant="secondary">{{ __('Cancel') }}</x-button>
                <x-button type="submit" variant="primary">{{ __('Update Domain') }}</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
