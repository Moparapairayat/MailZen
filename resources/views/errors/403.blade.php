@php($layout = auth('admin')->check() ? 'layouts.admin' : (auth('customer')->check() ? 'layouts.customer' : 'layouts.public'))
@extends($layout)

@section('title', 'Access Denied')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white dark:bg-admin-sidebar border border-gray-200 dark:border-admin-border rounded-lg p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-admin-text-primary">You have no access here</h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-admin-text-secondary">
                    {{ trim($exception->getMessage()) !== '' ? $exception->getMessage() : 'You do not have access to this feature.' }}
                </p>
            </div>
            <div class="text-sm text-gray-500 dark:text-admin-text-secondary">403</div>
        </div>

        <div class="mt-6 flex items-center gap-3">
            @if(auth('customer')->check())
                <a href="{{ route('customer.dashboard') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-primary-600 text-white text-sm font-medium hover:bg-primary-700">
                    Go to dashboard
                </a>
            @endif
            <a href="{{ url()->previous() }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/5">
                Go back
            </a>
        </div>
    </div>
</div>
@endsection
