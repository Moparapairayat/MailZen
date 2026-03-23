@extends('layouts.customer')

@section('title', 'Email Validation Tool')
@section('page-title', 'Email Validation Tool')

@section('content')
<div class="space-y-6">
    <x-card>
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-lg font-semibold">{{ $tool->name }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Provider: {{ strtoupper($tool->provider) }}</p>
            </div>
            <div class="flex items-center gap-2">
                @if($tool->customer_id)
                    @customercan('email_validation.permissions.can_edit_tools')
                        <x-button href="{{ route('customer.email-validation.tools.edit', $tool) }}" variant="primary">Edit</x-button>
                    @endcustomercan

                    @customercan('email_validation.permissions.can_delete_tools')
                        <form method="POST" action="{{ route('customer.email-validation.tools.destroy', $tool) }}" onsubmit="return confirm('Are you sure?');">
                            @csrf
                            @method('DELETE')
                            <x-button type="submit" variant="danger">Delete</x-button>
                        </form>
                    @endcustomercan
                @endif
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div><strong>Owner:</strong> {{ $tool->customer_id ? 'Mine' : 'System' }}</div>
            <div><strong>Active:</strong> {{ $tool->active ? 'Yes' : 'No' }}</div>
        </div>

        <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end">
            @customercan('email_validation.access')
                <x-button href="{{ route('customer.email-validation.runs.create') }}" variant="primary">Run Validation</x-button>
            @endcustomercan
            <x-button href="{{ route('customer.email-validation.tools.index') }}" variant="secondary">Back</x-button>
        </div>
    </x-card>
</div>
@endsection
