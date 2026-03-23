@extends('layouts.admin')

@section('title', __('Invoices'))
@section('page-title', __('Invoices'))

@section('content')
<div class="space-y-4">
    @if(($mode ?? 'events') !== 'stripe' && !empty($stripeError))
        <x-card>
            <div class="text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded-md p-3">
                {{ __('Stripe invoice purchase history could not be loaded. Showing stored webhook entries instead.') }}
                <div class="mt-1 text-xs text-amber-700 font-mono break-all">{{ $stripeError }}</div>
            </div>
        </x-card>
    @endif

    <x-card>
        <form method="GET" action="{{ route('admin.invoices.index') }}" class="flex flex-col gap-3 lg:flex-row lg:items-end">
            <div class="flex-1">
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Search') }}</label>
                <input
                    name="q"
                    value="{{ $search }}"
                    placeholder="{{ __('Invoice ID, event ID, customer name/email') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                >
            </div>

            <div class="w-full lg:w-56">
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Status') }}</label>
                <select name="status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    <option value="" {{ $status === '' ? 'selected' : '' }}>{{ __('All') }}</option>
                    <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>{{ __('Paid') }}</option>
                    <option value="open" {{ $status === 'open' ? 'selected' : '' }}>{{ __('Open') }}</option>
                    <option value="void" {{ $status === 'void' ? 'selected' : '' }}>{{ __('Void') }}</option>
                    <option value="uncollectible" {{ $status === 'uncollectible' ? 'selected' : '' }}>{{ __('Uncollectible') }}</option>
                    <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <x-button type="submit" variant="primary">{{ __('Filter') }}</x-button>
                <x-button href="{{ route('admin.invoices.index') }}" variant="secondary">{{ __('Reset') }}</x-button>
            </div>
        </form>
    </x-card>

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Invoice') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Customer') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Subscription') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Amount') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Event') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Processed') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @if(($mode ?? 'events') === 'stripe')
                        @forelse($invoices as $invoice)
                            @php
                                $invoiceId = data_get($invoice, 'id');
                                $stripeCustomerId = data_get($invoice, 'customer');
                                $stripeSubscriptionId = data_get($invoice, 'subscription');
                                $customer = $stripeCustomerId ? ($customersByStripeId[$stripeCustomerId] ?? null) : null;
                                $subscription = $stripeSubscriptionId ? ($subscriptionsByStripeId[$stripeSubscriptionId] ?? null) : null;
                                $amountPaid = data_get($invoice, 'amount_paid');
                                $amountDue = data_get($invoice, 'amount_due');
                                $amountTotal = data_get($invoice, 'total');
                                $currency = strtoupper((string) data_get($invoice, 'currency', ''));
                                $statusText = (string) data_get($invoice, 'status', '');
                                $createdTs = data_get($invoice, 'created');

                                $amountCents = $amountPaid ?? $amountDue ?? $amountTotal;
                                $amount = is_numeric($amountCents) ? ((float) $amountCents / 100) : null;
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    @if($invoiceId)
                                        <a class="text-primary-600 hover:text-primary-700" href="{{ route('admin.invoices.show', $invoiceId) }}">
                                            {{ $invoiceId }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                    @if($customer)
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $customer->full_name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $customer->email }}</div>
                                    @else
                                        <div class="text-gray-500 dark:text-gray-400">—</div>
                                        @if($stripeCustomerId)
                                            <div class="text-xs text-gray-400">{{ $stripeCustomerId }}</div>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                    @if($subscription)
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $subscription->plan_name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $subscription->stripe_subscription_id }}</div>
                                    @else
                                        <div class="text-gray-500 dark:text-gray-400">—</div>
                                        @if($stripeSubscriptionId)
                                            <div class="text-xs text-gray-400">{{ $stripeSubscriptionId }}</div>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                    @if($amount !== null)
                                        {{ $currency ? $currency . ' ' : '' }}{{ number_format($amount, 2) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @php
                                        $badge = match ($statusText) {
                                            'paid' => 'bg-green-100 text-green-800',
                                            'open' => 'bg-yellow-100 text-yellow-800',
                                            'void' => 'bg-gray-100 text-gray-800',
                                            'uncollectible' => 'bg-red-100 text-red-800',
                                            'draft' => 'bg-gray-100 text-gray-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                    @endphp
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $badge }}">
                                        {{ $statusText !== '' ? $statusText : '—' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ __('Stripe') }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('API') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                    @if(is_numeric($createdTs))
                                        {{ \Carbon\Carbon::createFromTimestamp((int) $createdTs)->diffForHumans() }}
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('No invoices found.') }}
                                </td>
                            </tr>
                        @endforelse
                    @else
                        @forelse($events as $event)
                            @php
                                $invoiceId = data_get($event->payload, 'id');
                                $stripeCustomerId = data_get($event->payload, 'customer');
                                $stripeSubscriptionId = data_get($event->payload, 'subscription');
                                $customer = $stripeCustomerId ? ($customersByStripeId[$stripeCustomerId] ?? null) : null;
                                $subscription = $stripeSubscriptionId ? ($subscriptionsByStripeId[$stripeSubscriptionId] ?? null) : null;
                                $amountPaid = data_get($event->payload, 'amount_paid');
                                $amountDue = data_get($event->payload, 'amount_due');
                                $currency = strtoupper((string) data_get($event->payload, 'currency', ''));
                                $statusText = (string) data_get($event->payload, 'status', '');

                                $amountCents = $amountPaid ?? $amountDue;
                                $amount = is_numeric($amountCents) ? ((float) $amountCents / 100) : null;
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    @if($invoiceId)
                                        <a class="text-primary-600 hover:text-primary-700" href="{{ route('admin.invoices.show', $invoiceId) }}">
                                            {{ $invoiceId }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                    @if($customer)
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $customer->full_name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $customer->email }}</div>
                                    @else
                                        <div class="text-gray-500 dark:text-gray-400">—</div>
                                        @if($stripeCustomerId)
                                            <div class="text-xs text-gray-400">{{ $stripeCustomerId }}</div>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                    @if($subscription)
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $subscription->plan_name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $subscription->stripe_subscription_id }}</div>
                                    @else
                                        <div class="text-gray-500 dark:text-gray-400">—</div>
                                        @if($stripeSubscriptionId)
                                            <div class="text-xs text-gray-400">{{ $stripeSubscriptionId }}</div>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                    @if($amount !== null)
                                        {{ $currency ? $currency . ' ' : '' }}{{ number_format($amount, 2) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @php
                                        $badge = match ($statusText) {
                                            'paid' => 'bg-green-100 text-green-800',
                                            'open' => 'bg-yellow-100 text-yellow-800',
                                            'void' => 'bg-gray-100 text-gray-800',
                                            'uncollectible' => 'bg-red-100 text-red-800',
                                            'draft' => 'bg-gray-100 text-gray-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                    @endphp
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $badge }}">
                                        {{ $statusText !== '' ? $statusText : '—' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $event->type }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $event->event_id }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                    {{ $event->processed_at?->diffForHumans() ?? $event->created_at?->diffForHumans() ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('No invoices found.') }}
                                </td>
                            </tr>
                        @endforelse
                    @endif
                </tbody>
            </table>
        </div>

        @if(($mode ?? 'events') === 'stripe')
            @if($stripePagination)
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        @if(!empty($stripePagination['prev']))
                            <x-button
                                href="{{ route('admin.invoices.index', array_filter(array_merge(request()->query(), ['ending_before' => $stripePagination['prev'], 'starting_after' => null]))) }}"
                                variant="secondary"
                            >{{ __('Prev') }}</x-button>
                        @endif
                    </div>
                    <div>
                        @if(!empty($stripePagination['next']))
                            <x-button
                                href="{{ route('admin.invoices.index', array_filter(array_merge(request()->query(), ['starting_after' => $stripePagination['next'], 'ending_before' => null]))) }}"
                                variant="secondary"
                            >{{ __('Next') }}</x-button>
                        @endif
                    </div>
                </div>
            @endif
        @else
            @if($events && $events->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">{{ $events->links() }}</div>
            @endif
        @endif
    </x-card>
</div>
@endsection
