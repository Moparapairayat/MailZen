@php($layout = auth('customer')->check() ? 'layouts.customer' : 'layouts.public')
@extends($layout)

@section('title', 'Limit Reached')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white dark:bg-admin-sidebar border border-gray-200 dark:border-admin-border rounded-lg p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-admin-text-primary">Limit reached</h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-admin-text-secondary">
                    {{ trim($exception->getMessage()) !== '' ? $exception->getMessage() : 'Your current plan limits have been reached.' }}
                </p>
            </div>
            <div class="text-sm text-gray-500 dark:text-admin-text-secondary">429</div>
        </div>

        <div class="mt-6 flex items-center gap-3">
            @if(auth('customer')->check())
                <a href="{{ route('customer.billing.index') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-primary-600 text-white text-sm font-medium hover:bg-primary-700">
                    View billing
                </a>
            @endif
            <a href="{{ url()->previous() }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/5">
                Go back
            </a>
        </div>
    </div>
</div>
@endsection
