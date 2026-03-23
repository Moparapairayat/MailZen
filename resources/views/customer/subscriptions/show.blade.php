@extends('layouts.customer')

@section('title', $subscription->plan_name)
@section('page-title', $subscription->plan_name)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $subscription->plan_name }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Status: <span class="px-2 py-1 text-xs rounded-full {{ $subscription->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">{{ ucfirst($subscription->status) }}</span>
            </p>
        </div>
        <div class="flex items-center gap-3">
            @if($subscription->status === 'active' && $subscription->auto_renew)
                <form method="POST" action="{{ route('customer.subscriptions.cancel', $subscription) }}" onsubmit="return confirm('Are you sure you want to cancel this subscription?');">
                    @csrf
                    <x-button type="submit" variant="danger">Cancel Subscription</x-button>
                </form>
            @endif
        </div>
    </div>

    <x-card title="Subscription Details">
        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Plan</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $subscription->plan_name }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    <span class="px-2 py-1 text-xs rounded-full {{ $subscription->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">{{ ucfirst($subscription->status) }}</span>
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Billing Cycle</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($subscription->billing_cycle) }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Price</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $subscription->currency }} {{ number_format($subscription->price, 2) }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Starts At</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $subscription->starts_at->format('M d, Y') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Ends At</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $subscription->ends_at ? $subscription->ends_at->format('M d, Y') : 'N/A' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Auto Renew</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $subscription->auto_renew ? 'Yes' : 'No' }}</dd>
            </div>
        </dl>
    </x-card>
</div>
@endsection

