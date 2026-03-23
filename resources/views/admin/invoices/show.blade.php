@extends('layouts.admin')

@section('title', __('Invoice'))
@section('page-title', __('Invoice'))

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-admin-text-primary">{{ $invoiceId }}</h2>
            <p class="mt-1 text-sm text-admin-text-secondary">{{ __('Invoice details') }}</p>
        </div>
        <x-button href="{{ route('admin.invoices.index') }}" variant="secondary">{{ __('Back') }}</x-button>
    </div>

    @if(($mode ?? 'events') !== 'stripe' && !empty($stripeError))
        <x-card>
            <div class="text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded-md p-3">
                {{ __('Stripe invoice details could not be loaded. Showing stored webhook entry instead.') }}
                <div class="mt-1 text-xs text-amber-700 font-mono break-all">{{ $stripeError }}</div>
            </div>
        </x-card>
    @endif

    @php
        $stripeCustomerId = data_get($invoice, 'customer');
        $stripeSubscriptionId = data_get($invoice, 'subscription');
        $statusText = (string) data_get($invoice, 'status', '');
        $currency = strtoupper((string) data_get($invoice, 'currency', ''));
        $amountPaid = data_get($invoice, 'amount_paid');
        $amountDue = data_get($invoice, 'amount_due');
        $amountTotal = data_get($invoice, 'total');
        $amountCents = $amountPaid ?? $amountDue ?? $amountTotal;
        $amount = is_numeric($amountCents) ? ((float) $amountCents / 100) : null;

        $createdTs = data_get($invoice, 'created');
        $createdAt = is_numeric($createdTs) ? \Carbon\Carbon::createFromTimestamp((int) $createdTs) : null;

        $hostedInvoiceUrl = data_get($invoice, 'hosted_invoice_url');
        $invoicePdfUrl = data_get($invoice, 'invoice_pdf');

        $badge = match ($statusText) {
            'paid' => 'bg-green-100 text-green-800',
            'open' => 'bg-yellow-100 text-yellow-800',
            'void' => 'bg-gray-100 text-gray-800',
            'uncollectible' => 'bg-red-100 text-red-800',
            'draft' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };

        $raw = json_encode($invoice, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    @endphp

    <x-card :title="__('Summary')">
        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-admin-text-secondary">{{ __('Invoice ID') }}</dt>
                <dd class="mt-1 text-sm text-admin-text-primary">{{ $invoiceId }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-admin-text-secondary">{{ __('Status') }}</dt>
                <dd class="mt-1">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $badge }}">
                        {{ $statusText !== '' ? $statusText : '—' }}
                    </span>
                </dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-admin-text-secondary">{{ __('Customer') }}</dt>
                <dd class="mt-1 text-sm text-admin-text-primary">
                    @if($customer)
                        <a class="text-primary-600 hover:text-primary-700" href="{{ route('admin.customers.show', $customer) }}">
                            {{ $customer->full_name }} ({{ $customer->email }})
                        </a>
                    @else
                        {{ $stripeCustomerId ?: '—' }}
                    @endif
                </dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-admin-text-secondary">{{ __('Subscription') }}</dt>
                <dd class="mt-1 text-sm text-admin-text-primary">
                    @if($subscription)
                        <div>{{ $subscription->plan_name }}</div>
                        <div class="text-xs text-admin-text-secondary">{{ $subscription->stripe_subscription_id }}</div>
                    @else
                        {{ $stripeSubscriptionId ?: '—' }}
                    @endif
                </dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-admin-text-secondary">{{ __('Amount') }}</dt>
                <dd class="mt-1 text-sm text-admin-text-primary">
                    @if($amount !== null)
                        {{ $currency ? $currency . ' ' : '' }}{{ number_format($amount, 2) }}
                    @else
                        —
                    @endif
                </dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-admin-text-secondary">{{ __('Created') }}</dt>
                <dd class="mt-1 text-sm text-admin-text-primary">
                    {{ $createdAt?->format('M d, Y H:i') ?? '—' }}
                </dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-admin-text-secondary">{{ __('Hosted Invoice URL') }}</dt>
                <dd class="mt-1 text-sm">
                    @if($hostedInvoiceUrl)
                        <a class="text-primary-600 hover:text-primary-700 break-all" href="{{ $hostedInvoiceUrl }}" target="_blank" rel="noopener noreferrer">{{ $hostedInvoiceUrl }}</a>
                    @else
                        —
                    @endif
                </dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-admin-text-secondary">{{ __('Invoice PDF') }}</dt>
                <dd class="mt-1 text-sm">
                    @if($invoicePdfUrl)
                        <a class="text-primary-600 hover:text-primary-700 break-all" href="{{ $invoicePdfUrl }}" target="_blank" rel="noopener noreferrer">{{ $invoicePdfUrl }}</a>
                    @else
                        —
                    @endif
                </dd>
            </div>

            @if($event)
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-admin-text-secondary">{{ __('Webhook') }}</dt>
                    <dd class="mt-1 text-sm text-admin-text-primary">
                        <div class="font-medium">{{ $event->type }}</div>
                        <div class="text-xs text-admin-text-secondary">{{ $event->event_id }}</div>
                    </dd>
                </div>
            @endif
        </dl>
    </x-card>

    <x-card :title="__('Raw Invoice Payload')">
        <pre class="text-xs whitespace-pre-wrap bg-white/5 border border-admin-border rounded-lg p-4 overflow-auto text-admin-text-primary">{{ $raw ?: '—' }}</pre>
    </x-card>
</div>
@endsection
