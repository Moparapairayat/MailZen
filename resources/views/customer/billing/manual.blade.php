@extends('layouts.customer')

@section('title', __('Manual Payment'))
@section('page-title', __('Manual Payment'))

@section('content')
@php
    $qrPath = is_array($manualConfig ?? null) ? (data_get($manualConfig, 'qr_image_path') ?: data_get($manualConfig, 'qr_image')) : null;
@endphp

<div class="space-y-6">
    <div class="rounded-xl border border-admin-border bg-white/5 p-6">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold">{{ __('Bank Transfer Instructions') }}</h2>
                <div class="mt-1 text-sm text-admin-text-secondary">
                    {{ __('Complete the transfer, then submit your confirmation below so we can activate your plan.') }}
                </div>
            </div>
            <x-button href="{{ route('customer.billing.index') }}" variant="secondary">{{ __('Back') }}</x-button>
        </div>

        <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="rounded-lg border border-admin-border bg-admin-sidebar p-5">
                <div class="text-sm text-admin-text-secondary">{{ __('Plan') }}</div>
                <div class="mt-1 font-semibold">{{ $subscription->plan_name }}</div>
                <div class="mt-2 text-sm text-admin-text-secondary">
                    {{ $subscription->currency }} {{ number_format((float) $subscription->price, 2) }} / {{ $subscription->billing_cycle }}
                </div>

                <div class="mt-4 space-y-2 text-sm">
                    <div><span class="text-admin-text-secondary">{{ __('Bank:') }}</span> <span class="text-admin-text-primary">{{ data_get($manualConfig, 'bank_name') ?: '—' }}</span></div>
                    <div><span class="text-admin-text-secondary">{{ __('Account name:') }}</span> <span class="text-admin-text-primary">{{ data_get($manualConfig, 'account_name') ?: '—' }}</span></div>
                    <div><span class="text-admin-text-secondary">{{ __('Account number:') }}</span> <span class="text-admin-text-primary">{{ data_get($manualConfig, 'account_number') ?: '—' }}</span></div>
                </div>

                @if(is_string(data_get($manualConfig, 'instructions')) && trim((string) data_get($manualConfig, 'instructions')) !== '')
                    <div class="mt-4 text-sm text-admin-text-secondary whitespace-pre-line">{{ data_get($manualConfig, 'instructions') }}</div>
                @endif
            </div>

            <div class="rounded-lg border border-admin-border bg-white/5 p-5">
                <div class="text-sm text-admin-text-secondary">{{ __('QR Code') }}</div>
                @if(is_string($qrPath) && trim($qrPath) !== '')
                    <div class="mt-3">
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($qrPath, '/')) }}" class="max-h-72 w-auto rounded border border-admin-border" alt="{{ __('QR Code') }}">
                    </div>
                @else
                    <div class="mt-3 text-sm text-admin-text-secondary">{{ __('No QR code configured.') }}</div>
                @endif
            </div>
        </div>
    </div>

    <x-card>
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold">{{ __('Submit Confirmation') }}</h3>
                <div class="mt-1 text-sm text-admin-text-secondary">
                    {{ __('Optional: upload proof of transfer to speed up verification.') }}
                </div>
            </div>
            <div class="text-xs text-admin-text-secondary">
                {{ __('Status:') }} {{ $manualPayment ? ucfirst($manualPayment->status) : __('Pending') }}
            </div>
        </div>

        <form method="POST" action="{{ route('customer.billing.manual.confirm', $subscription) }}" enctype="multipart/form-data" class="mt-6 space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-medium text-admin-text-secondary">{{ __('Transfer reference (optional)') }}</label>
                <input name="transfer_reference" value="{{ old('transfer_reference', $manualPayment?->transfer_reference) }}" class="mt-1 block w-full rounded-md border-admin-border bg-transparent" placeholder="{{ __('e.g. Bank transaction ID') }}">
                @error('transfer_reference')
                    <div class="mt-1 text-sm text-red-400">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-admin-text-secondary">{{ __('Notes (optional)') }}</label>
                <textarea name="payer_notes" rows="3" class="mt-1 block w-full rounded-md border-admin-border bg-transparent" placeholder="{{ __('Any extra details for the admin') }}">{{ old('payer_notes', $manualPayment?->payer_notes) }}</textarea>
                @error('payer_notes')
                    <div class="mt-1 text-sm text-red-400">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-admin-text-secondary">{{ __('Proof image (optional)') }}</label>
                <input type="file" name="proof" accept="image/*" class="mt-1 block w-full text-sm">
                @error('proof')
                    <div class="mt-1 text-sm text-red-400">{{ $message }}</div>
                @enderror

                @if($manualPayment && is_string($manualPayment->proof_path) && trim($manualPayment->proof_path) !== '')
                    <div class="mt-3">
                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($manualPayment->proof_path, '/')) }}" target="_blank" class="text-primary-400 hover:underline">{{ __('View uploaded proof') }}</a>
                    </div>
                @endif
            </div>

            <div class="flex items-center justify-end">
                <x-button type="submit" variant="primary">{{ __('Confirm Transfer') }}</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
