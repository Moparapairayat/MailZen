@extends('layouts.admin')

@section('title', __('Manual Payment'))
@section('page-title', __('Manual Payment'))

@section('content')
@php
    $proofUrl = $manualPayment->proof_path ? \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($manualPayment->proof_path, '/')) : null;

    $badge = match ($manualPayment->status) {
        'submitted' => 'bg-yellow-100 text-yellow-800',
        'approved' => 'bg-green-100 text-green-800',
        'rejected' => 'bg-red-100 text-red-800',
        default => 'bg-gray-100 text-gray-800',
    };
@endphp

<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-admin-text-primary">#{{ $manualPayment->id }}</h2>
            <p class="mt-1 text-sm text-admin-text-secondary">{{ __('Manual payment details') }}</p>
        </div>
        <x-button href="{{ route('admin.manual-payments.index') }}" variant="secondary">{{ __('Back') }}</x-button>
    </div>

    <x-card :title="__('Summary')">
        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-admin-text-secondary">{{ __('Customer') }}</dt>
                <dd class="mt-1 text-sm text-admin-text-primary">
                    {{ $manualPayment->customer?->full_name ?? '—' }} ({{ $manualPayment->customer?->email ?? '—' }})
                </dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-admin-text-secondary">{{ __('Plan') }}</dt>
                <dd class="mt-1 text-sm text-admin-text-primary">
                    {{ $manualPayment->subscription?->plan_name ?? '—' }}
                </dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-admin-text-secondary">{{ __('Amount') }}</dt>
                <dd class="mt-1 text-sm text-admin-text-primary">
                    {{ strtoupper((string) ($manualPayment->currency ?? '')) }} {{ number_format((float) $manualPayment->amount, 2) }}
                </dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-admin-text-secondary">{{ __('Status') }}</dt>
                <dd class="mt-1">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $badge }}">{{ $manualPayment->status ?: '—' }}</span>
                </dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-admin-text-secondary">{{ __('Transfer reference') }}</dt>
                <dd class="mt-1 text-sm text-admin-text-primary break-all">{{ $manualPayment->transfer_reference ?: '—' }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-admin-text-secondary">{{ __('Submitted') }}</dt>
                <dd class="mt-1 text-sm text-admin-text-primary">{{ $manualPayment->submitted_at?->format('M d, Y H:i') ?? '—' }}</dd>
            </div>

            <div class="sm:col-span-2">
                <dt class="text-sm font-medium text-admin-text-secondary">{{ __('Payer notes') }}</dt>
                <dd class="mt-1 text-sm text-admin-text-primary whitespace-pre-wrap">{{ $manualPayment->payer_notes ?: '—' }}</dd>
            </div>

            <div class="sm:col-span-2">
                <dt class="text-sm font-medium text-admin-text-secondary">{{ __('Proof') }}</dt>
                <dd class="mt-2">
                    @if($proofUrl)
                        <a class="text-primary-600 hover:text-primary-700" href="{{ $proofUrl }}" target="_blank" rel="noopener noreferrer">{{ __('Open proof image') }}</a>
                    @else
                        —
                    @endif
                </dd>
            </div>

            <div class="sm:col-span-2">
                <dt class="text-sm font-medium text-admin-text-secondary">{{ __('Admin notes') }}</dt>
                <dd class="mt-1 text-sm text-admin-text-primary whitespace-pre-wrap">{{ $manualPayment->admin_notes ?: '—' }}</dd>
            </div>
        </dl>
    </x-card>

    @if(in_array($manualPayment->status, ['submitted', 'initiated'], true))
        <x-card :title="__('Review')">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <form method="POST" action="{{ route('admin.manual-payments.approve', $manualPayment) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Admin notes (optional)') }}</label>
                        <textarea name="admin_notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">{{ old('admin_notes') }}</textarea>
                    </div>
                    <x-button type="submit" variant="primary">{{ __('Approve & Activate') }}</x-button>
                </form>

                <form method="POST" action="{{ route('admin.manual-payments.reject', $manualPayment) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Admin notes (optional)') }}</label>
                        <textarea name="admin_notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">{{ old('admin_notes') }}</textarea>
                    </div>
                    <x-button type="submit" variant="danger">{{ __('Reject') }}</x-button>
                </form>
            </div>
        </x-card>
    @endif
</div>
@endsection
