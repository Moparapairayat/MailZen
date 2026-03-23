@extends('layouts.customer')

@section('title', $transactionalEmail->name)
@section('page-title', $transactionalEmail->name)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $transactionalEmail->name }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $transactionalEmail->subject }}</p>
        </div>
        <div class="flex items-center gap-3">
            <x-button href="{{ route('customer.transactional-emails.edit', $transactionalEmail) }}" variant="secondary">Edit</x-button>
            <form method="POST" action="{{ route('customer.transactional-emails.destroy', $transactionalEmail) }}" class="inline" onsubmit="return confirm('Are you sure?');">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="danger">Delete</x-button>
            </form>
        </div>
    </div>

    <x-card title="Template Details">
        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Key</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">{{ $transactionalEmail->key }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    <span class="px-2 py-1 text-xs rounded-full {{ $transactionalEmail->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">{{ ucfirst($transactionalEmail->status) }}</span>
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Sent</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ number_format($transactionalEmail->sent_count ?? 0) }}</dd>
            </div>
            @if($transactionalEmail->description)
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $transactionalEmail->description }}</dd>
                </div>
            @endif
        </dl>
    </x-card>

    @if($transactionalEmail->html_content)
        <x-card title="Content Preview">
            <div class="prose max-w-none dark:prose-invert">
                {!! $transactionalEmail->html_content !!}
            </div>
        </x-card>
    @endif
</div>
@endsection

