@extends('layouts.customer')

@section('title', 'Billing')
@section('page-title', 'Billing & Subscription')

@section('content')
@php
    $isCurrentPlanActive = $subscription
        && !$subscription->isExpired()
        && in_array($subscription->status, ['active', 'trialing'], true);

    $isSubscriptionActiveOrTrialing = $subscription
        && !$subscription->isExpired()
        && in_array($subscription->status, ['active', 'trialing'], true);

    $renewalDate = null;
    if ($subscription?->period_end) {
        $renewalDate = $subscription->period_end;
    } elseif ($subscription?->trial_ends_at && $subscription->trial_ends_at->isFuture()) {
        $renewalDate = $subscription->trial_ends_at;
    } elseif ($subscription?->starts_at) {
        $renewalDate = $subscription->starts_at->copy()->addMonths($subscription?->billing_cycle === 'yearly' ? 12 : 1);
    }

    $emailsUsed = (int) ($usage['emails_sent_this_month'] ?? 0);
    $subsUsed = (int) ($usage['subscribers_count'] ?? 0);
    $campaignsUsed = (int) ($usage['campaigns_count'] ?? 0);

    $emailsLimit = (int) ($currentPlan?->customerGroup?->limit('sending_quota.monthly_quota', 0) ?? 0);
    $subsLimit = (int) ($currentPlan?->customerGroup?->limit('lists.limits.max_subscribers', 0) ?? 0);
    $campaignsLimit = (int) ($currentPlan?->customerGroup?->limit('campaigns.limits.max_campaigns', 0) ?? 0);

    $emailsPercent = $emailsLimit > 0 ? min(100, (int) round(($emailsUsed / $emailsLimit) * 100)) : null;
    $subsPercent = $subsLimit > 0 ? min(100, (int) round(($subsUsed / $subsLimit) * 100)) : null;
    $campaignsPercent = $campaignsLimit > 0 ? min(100, (int) round(($campaignsUsed / $campaignsLimit) * 100)) : null;
@endphp

<div class="space-y-6">
    <div class="rounded-xl border border-admin-border overflow-hidden bg-gradient-to-r from-admin-button-from/15 via-transparent to-admin-button-to/10">
        <div class="px-6 py-6">
            <h1 class="text-3xl font-bold tracking-tight">Billing</h1>
            <p class="mt-2 text-sm text-admin-text-secondary max-w-2xl">
                Manage your subscription, view payment history, and update your billing details — all in one place.
            </p>
        </div>
    </div>

    <div class="space-y-3">
        <h2 class="text-lg font-semibold">Subscription Overview</h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <x-card class="bg-admin-sidebar">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-sm text-admin-text-secondary">Current Plan</div>
                        <div class="mt-2 flex items-center gap-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $isSubscriptionActiveOrTrialing ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-white/10 border-admin-border' }}">
                                {{ $currentPlan?->name ?? ($subscription?->plan_name ?? 'No Plan') }}
                            </span>
                        </div>
                        <div class="mt-2 flex items-end gap-2">
                            <div class="text-2xl font-bold">
                                {{ $subscription?->currency ?? ($currentPlan?->currency ?? 'USD') }}
                                {{ number_format((float) ($subscription?->price ?? ($currentPlan?->price ?? 0)), 2) }}
                            </div>
                            <div class="text-sm text-admin-text-secondary">
                                / {{ $subscription?->billing_cycle ?? ($currentPlan?->billing_cycle ?? 'monthly') }}
                            </div>
                        </div>
                        <div class="mt-2 text-xs text-admin-text-secondary">
                            Status: {{ $subscription ? ucfirst($subscription->status) : '—' }}
                            @if($subscription?->trial_ends_at)
                                <span class="mx-2">·</span>
                                Trial ends {{ $subscription->trial_ends_at->format('M d, Y') }}
                            @endif
                        </div>

                        @if($subscription && $subscription->status === 'pending' && $subscription->provider === 'manual')
                            <div class="mt-4">
                                <x-button href="{{ route('customer.billing.manual.show', $subscription) }}" variant="primary">
                                    {{ __('Complete Manual Payment') }}
                                </x-button>
                            </div>
                        @endif
                    </div>

                    <div class="shrink-0">
                        @if($portalUrl)
                            <x-button href="{{ $portalUrl }}" variant="secondary" class="px-5">Manage</x-button>
                        @else
                            <x-button type="button" variant="secondary" class="px-5" disabled>Manage</x-button>
                        @endif
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-between text-xs text-admin-text-secondary">
                    <div>
                        Renews: {{ $renewalDate ? $renewalDate->format('M d, Y') : '—' }}
                    </div>
                    <div>
                        @if($isCurrentPlanActive)
                            Active
                        @else
                            Not active
                        @endif
                    </div>
                </div>
            </x-card>

            <x-card class="bg-admin-sidebar">
                <div class="text-sm text-admin-text-secondary">Usage Summary (this month)</div>

                <div class="mt-4 space-y-4">
                    <div>
                        <div class="flex items-end justify-between">
                            <div class="text-lg font-semibold">
                                {{ number_format($emailsUsed) }}
                                @if($emailsLimit > 0)
                                    <span class="text-admin-text-secondary font-normal">/ {{ number_format($emailsLimit) }}</span>
                                @endif
                            </div>
                            <div class="text-xs text-admin-text-secondary">
                                {{ $emailsPercent !== null ? $emailsPercent . '%' : '—' }}
                            </div>
                        </div>
                        <div class="mt-2 h-2 rounded-full bg-white/10 border border-admin-border overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-admin-button-from to-admin-button-to" style="width: {{ $emailsPercent ?? 0 }}%"></div>
                        </div>
                        <div class="mt-2 text-xs text-admin-text-secondary">Emails Sent</div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <div class="flex items-end justify-between">
                                <div class="text-base font-semibold">
                                    {{ number_format($subsUsed) }}
                                    @if($subsLimit > 0)
                                        <span class="text-admin-text-secondary font-normal">/ {{ number_format($subsLimit) }}</span>
                                    @endif
                                </div>
                                <div class="text-xs text-admin-text-secondary">
                                    {{ $subsPercent !== null ? $subsPercent . '%' : '—' }}
                                </div>
                            </div>
                            <div class="mt-2 h-2 rounded-full bg-white/10 border border-admin-border overflow-hidden">
                                <div class="h-full bg-white/40" style="width: {{ $subsPercent ?? 0 }}%"></div>
                            </div>
                            <div class="mt-2 text-xs text-admin-text-secondary">Subscribers</div>
                        </div>

                        <div>
                            <div class="flex items-end justify-between">
                                <div class="text-base font-semibold">
                                    {{ number_format($campaignsUsed) }}
                                    @if($campaignsLimit > 0)
                                        <span class="text-admin-text-secondary font-normal">/ {{ number_format($campaignsLimit) }}</span>
                                    @endif
                                </div>
                                <div class="text-xs text-admin-text-secondary">
                                    {{ $campaignsPercent !== null ? $campaignsPercent . '%' : '—' }}
                                </div>
                            </div>
                            <div class="mt-2 h-2 rounded-full bg-white/10 border border-admin-border overflow-hidden">
                                <div class="h-full bg-white/25" style="width: {{ $campaignsPercent ?? 0 }}%"></div>
                            </div>
                            <div class="mt-2 text-xs text-admin-text-secondary">Campaigns</div>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>

    <div class="space-y-3">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-lg font-semibold">Billing History</h2>
            <x-button type="button" variant="secondary" class="px-4" disabled>
                Filter
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L15 12.414V19a1 1 0 01-.553.894l-4 2A1 1 0 019 21v-8.586L3.293 6.707A1 1 0 013 6V4z" />
                </svg>
            </x-button>
        </div>

        <x-card :padding="false">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-admin-border">
                    <thead class="bg-white/5 border-b border-admin-border">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-admin-text-secondary uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-admin-text-secondary uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-admin-text-secondary uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-admin-text-secondary uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-admin-text-secondary uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-admin-border">
                        @php
                            $hasCurrentInvoiceRow = false;
                            foreach ($invoiceEvents as $event) {
                                if (data_get($event->payload, 'billing_reason') === 'subscription_create') {
                                    $hasCurrentInvoiceRow = true;
                                    break;
                                }
                            }
                        @endphp

                        @if($isCurrentPlanActive && !$hasCurrentInvoiceRow)
                            <tr class="hover:bg-white/5">
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    {{ $subscription?->starts_at ? $subscription->starts_at->format('M d, Y') : now()->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-admin-text-secondary">
                                    {{ $subscription?->plan_name ?? 'Current Subscription' }}
                                    @if($subscription?->billing_cycle)
                                        - {{ ucfirst($subscription->billing_cycle) }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    {{ $subscription?->currency ?? ($currentPlan?->currency ?? 'USD') }}
                                    {{ number_format((float) ($subscription?->price ?? ($currentPlan?->price ?? 0)), 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-white/10 text-admin-text-secondary border border-admin-border">
                                        Current
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex items-center gap-2">
                                        @if(!empty($latestInvoiceUrl))
                                            <x-button href="{{ $latestInvoiceUrl }}" variant="table" size="sm" :pill="true" target="_blank">Invoice</x-button>
                                        @else
                                            <x-button type="button" variant="table" size="sm" :pill="true" disabled>Invoice</x-button>
                                        @endif
                                        <x-button type="button" variant="table-danger" size="sm" :pill="true" disabled>Delete</x-button>
                                    </div>
                                </td>
                            </tr>
                        @endif

                        @if($invoiceEvents->count() > 0)
                            @foreach($invoiceEvents as $event)
                            @php
                                $invoice = $event->payload;
                                $created = data_get($invoice, 'created');
                                $date = $created ? \Carbon\Carbon::createFromTimestamp($created)->format('M d, Y') : ($event->processed_at?->format('M d, Y') ?? $event->created_at->format('M d, Y'));

                                $amountCents = (int) (data_get($invoice, 'amount_paid') ?? data_get($invoice, 'amount_due') ?? data_get($invoice, 'total') ?? 0);
                                $currency = strtoupper((string) (data_get($invoice, 'currency') ?? ($subscription?->currency ?? 'USD')));
                                $amount = number_format($amountCents / 100, 2);

                                $status = (string) (data_get($invoice, 'status') ?? data_get($invoice, 'paid') ? 'paid' : '');
                                $hostedInvoiceUrl = data_get($invoice, 'hosted_invoice_url');
                                $invoicePdf = data_get($invoice, 'invoice_pdf');
                                $downloadUrl = $hostedInvoiceUrl ?: $invoicePdf;

                                $description = data_get($invoice, 'lines.data.0.description')
                                    ?? data_get($invoice, 'description')
                                    ?? ($subscription?->plan_name ? ($subscription->plan_name . ' - ' . ucfirst($subscription->billing_cycle)) : 'Invoice');

                                $statusLabel = strtolower($status);
                                $statusIsPaid = in_array($statusLabel, ['paid', 'succeeded'], true);
                                $statusIsFailed = in_array($statusLabel, ['failed', 'uncollectible'], true);
                            @endphp
                            <tr class="hover:bg-white/5">
                                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $date }}</td>
                                <td class="px-6 py-4 text-sm text-admin-text-secondary">{{ $description }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $currency }} {{ $amount }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($statusIsPaid)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-500/15 text-green-300 border border-green-500/20">
                                            Paid
                                        </span>
                                    @elseif($statusIsFailed)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-500/15 text-red-300 border border-red-500/20">
                                            Failed
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-white/10 text-admin-text-secondary border border-admin-border">
                                            {{ $statusLabel !== '' ? ucfirst($statusLabel) : '—' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex items-center gap-2">
                                        @if($downloadUrl)
                                            <x-button href="{{ $downloadUrl }}" variant="table" size="sm" :pill="true" target="_blank">Invoice</x-button>
                                        @else
                                            <x-button type="button" variant="table" size="sm" :pill="true" disabled>Invoice</x-button>
                                        @endif

                                        <form method="POST" action="{{ route('customer.billing.history.destroy', $event) }}" onsubmit="return confirm('Delete this invoice entry from your billing history?');">
                                            @csrf
                                            @method('DELETE')
                                            <x-button type="submit" variant="table-danger" size="sm" :pill="true">Delete</x-button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        @elseif(isset($stripeInvoices) && $stripeInvoices->count() > 0)
                            @foreach($stripeInvoices as $invoice)
                            @php
                                $created = data_get($invoice, 'created');
                                $date = $created ? \Carbon\Carbon::createFromTimestamp($created)->format('M d, Y') : '—';

                                $amountCents = (int) (data_get($invoice, 'amount_paid') ?? data_get($invoice, 'amount_due') ?? data_get($invoice, 'total') ?? 0);
                                $currency = strtoupper((string) (data_get($invoice, 'currency') ?? ($subscription?->currency ?? 'USD')));
                                $amount = number_format($amountCents / 100, 2);

                                $status = (string) (data_get($invoice, 'status') ?? (data_get($invoice, 'paid') ? 'paid' : ''));
                                $hostedInvoiceUrl = data_get($invoice, 'hosted_invoice_url');
                                $invoicePdf = data_get($invoice, 'invoice_pdf');
                                $downloadUrl = $hostedInvoiceUrl ?: $invoicePdf;

                                $description = data_get($invoice, 'lines.data.0.description')
                                    ?? data_get($invoice, 'description')
                                    ?? 'Invoice';

                                $statusLabel = strtolower($status);
                                $statusIsPaid = in_array($statusLabel, ['paid', 'succeeded'], true);
                                $statusIsFailed = in_array($statusLabel, ['failed', 'uncollectible'], true);
                            @endphp
                            <tr class="hover:bg-white/5">
                                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $date }}</td>
                                <td class="px-6 py-4 text-sm text-admin-text-secondary">{{ $description }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $currency }} {{ $amount }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($statusIsPaid)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-500/15 text-green-300 border border-green-500/20">
                                            Paid
                                        </span>
                                    @elseif($statusIsFailed)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-500/15 text-red-300 border border-red-500/20">
                                            Failed
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-white/10 text-admin-text-secondary border border-admin-border">
                                            {{ $statusLabel !== '' ? ucfirst($statusLabel) : '—' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex items-center gap-2">
                                        @if($downloadUrl)
                                            <x-button href="{{ $downloadUrl }}" variant="table" size="sm" :pill="true" target="_blank">{{ __('Invoice') }}</x-button>
                                        @else
                                            <x-button type="button" variant="table" size="sm" :pill="true" disabled>{{ __('Invoice') }}</x-button>
                                        @endif

                                        <x-button type="button" variant="table-danger" size="sm" :pill="true" disabled>{{ __('Delete') }}</x-button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        @elseif(!$isCurrentPlanActive)
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-admin-text-secondary">
                                    {{ __('No billing history yet.') }}
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>

    <div class="space-y-3">
        <h2 class="text-lg font-semibold">{{ __('Payment Method') }}</h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="rounded-xl border border-admin-border bg-gradient-to-br from-white/10 via-transparent to-white/5 p-6">
                <div class="flex items-start justify-between">
                    <div class="text-xl font-bold tracking-widest">
                        {{ strtoupper($paymentMethodCard['brand'] ?? __('CARD')) }}
                    </div>
                    <div class="w-8 h-8 rounded-full bg-white/10 border border-admin-border"></div>
                </div>
                <div class="mt-10 text-lg tracking-widest text-admin-text-secondary">
                    •••• •••• •••• {{ $paymentMethodCard['last4'] ?? '••••' }}
                </div>
                <div class="mt-6 grid grid-cols-2 gap-4 text-xs text-admin-text-secondary">
                    <div>
                        <div class="uppercase">{{ __('Name') }}</div>
                        <div class="mt-1 text-sm text-admin-text-primary">{{ $customerName ?: '—' }}</div>
                    </div>
                    <div>
                        <div class="uppercase">{{ __('Valid Thru') }}</div>
                        <div class="mt-1 text-sm text-admin-text-primary">
                            @if(!empty($paymentMethodCard['exp_month']) && !empty($paymentMethodCard['exp_year']))
                                {{ str_pad((string) $paymentMethodCard['exp_month'], 2, '0', STR_PAD_LEFT) }}/{{ substr((string) $paymentMethodCard['exp_year'], -2) }}
                            @else
                                —
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-dashed border-admin-border bg-white/5 p-6 flex items-center justify-center">
                <div class="text-center">
                    <div class="mx-auto w-10 h-10 rounded-full border border-admin-border bg-white/10 flex items-center justify-center">
                        <span class="text-xl leading-none">+</span>
                    </div>
                    <div class="mt-3 font-medium">{{ __('Add New Card') }}</div>
                    <div class="mt-1 text-sm text-admin-text-secondary">{{ __('Manage your payment method in the billing portal.') }}</div>
                    <div class="mt-4">
                        @if($portalUrl)
                            <x-button href="{{ $portalUrl }}" variant="primary">{{ __('Open Billing Portal') }}</x-button>
                        @else
                            <x-button type="button" variant="primary" disabled>{{ __('Open Billing Portal') }}</x-button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="plans" class="space-y-3">
        <h2 class="text-lg font-semibold">{{ __('Upgrade Plans') }}</h2>
        <x-card :padding="false">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-6">
                @foreach($plans as $plan)
                    @php
                        $isCurrentPlan = $subscription
                            && $subscription->plan_db_id === $plan->id
                            && !$subscription->isExpired()
                            && in_array($subscription->status, ['active', 'trialing'], true);
                    @endphp
                    <div class="border border-admin-border rounded-lg p-4 flex flex-col gap-3 bg-white/5">
                        <div class="text-lg font-semibold">{{ $plan->name }}</div>
                        <div class="text-2xl font-bold">{{ $plan->currency }} {{ number_format($plan->price, 2) }} <span class="text-sm text-admin-text-secondary">/ {{ $plan->billing_cycle }}</span></div>
                        <div class="text-sm text-admin-text-secondary">{{ __('Customer Group:') }} {{ $plan->customerGroup?->name ?? __('N/A') }}</div>
                        @if($plan->customerGroup)
                            @php
                                $planFeatures = $plan->customerGroup->displayAccessAndLimits();
                            @endphp
                            <div class="text-xs text-admin-text-secondary space-y-2">
                                @foreach($planFeatures as $row)
                                    <div class="flex items-start gap-2">
                                        @if(($row['status'] ?? true) === false)
                                            <svg class="w-4 h-4 mt-0.5 text-admin-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 mt-0.5 text-admin-button-from" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        @endif
                                        <div>{{ $row['label'] ?? '' }}: {{ $row['value'] ?? '' }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        @if($isCurrentPlan)
                            <div class="mt-auto">
                                <x-button type="button" variant="secondary" class="w-full" disabled>
                                    {{ __('Current Plan') }}
                                </x-button>
                            </div>
                        @else
                            <form method="GET" action="{{ route('customer.billing.checkout.show', $plan) }}" class="mt-auto">
                                <div class="mb-3">
                                    <label class="block text-xs font-medium text-admin-text-secondary">{{ __('Coupon code (optional)') }}</label>
                                    <input name="coupon_code" value="{{ old('coupon_code') }}" class="mt-1 block w-full rounded-md border-admin-border bg-transparent" placeholder="{{ __('WELCOME10') }}">
                                </div>
                                <x-button type="submit" variant="primary" class="w-full">{{ __('Choose Plan') }}</x-button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        </x-card>
    </div>
</div>
@endsection

