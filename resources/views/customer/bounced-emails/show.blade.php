@extends('layouts.customer')

@section('title', 'Bounced Email')
@section('page-title', 'Bounced Email')

@section('content')
<div class="space-y-6">
    <x-card>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div><strong>Email:</strong> {{ $bounce->email }}</div>
            <div><strong>Bounce Type:</strong> {{ $bounce->bounce_type ?? '—' }}</div>
            <div><strong>Campaign:</strong> {{ $bounce->campaign?->name ?? '—' }}</div>
            <div><strong>List:</strong> {{ $bounce->emailList?->name ?? '—' }}</div>
            <div><strong>Last Bounced:</strong> {{ $bounce->last_bounced_at?->format('M d, Y H:i') ?? '—' }}</div>
            <div><strong>Reason:</strong> {{ $bounce->reason ?? '—' }}</div>
        </div>

        @if($bounce->diagnostic_code)
            <div class="mt-4 text-sm">
                <strong>Diagnostic code:</strong>
                <div class="mt-1 text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $bounce->diagnostic_code }}</div>
            </div>
        @endif

        @if($bounce->raw_message)
            <div class="mt-4 text-sm">
                <strong>Raw message:</strong>
                <pre class="mt-2 p-3 rounded bg-gray-100 dark:bg-gray-900 overflow-x-auto text-xs">{{ $bounce->raw_message }}</pre>
            </div>
        @endif
    </x-card>

    <div>
        <x-button href="{{ route('customer.bounced-emails.index') }}" variant="secondary">Back</x-button>
    </div>
</div>
@endsection
