@extends('layouts.customer')

@section('title', 'Email Warmups')
@section('page-title', 'Email Warmups')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between lg:gap-0">
        <div class="w-full lg:flex-1 lg:max-w-lg">
            <form method="GET" action="{{ route('customer.warmups.index') }}" class="flex flex-col gap-2 lg:flex-row lg:items-center">
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search warmups..." class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                <select name="status" class="block w-full lg:w-auto rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    <option value="">All Statuses</option>
                    <option value="draft" {{ ($filters['status'] ?? '') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="paused" {{ ($filters['status'] ?? '') === 'paused' ? 'selected' : '' }}>Paused</option>
                    <option value="completed" {{ ($filters['status'] ?? '') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="failed" {{ ($filters['status'] ?? '') === 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
                <x-button type="submit" variant="primary" class="w-full lg:w-auto">Search</x-button>
            </form>
        </div>
        <x-button href="{{ route('customer.warmups.create') }}" variant="primary" class="w-full lg:w-auto">New Warmup</x-button>
    </div>

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Server</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Progress</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Sent</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Health</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($warmups as $warmup)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $warmup->name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $warmup->from_email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $warmup->deliveryServer?->name ?? '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-24 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="bg-primary-600 h-2 rounded-full" style="width: {{ $warmup->getProgressPercentage() }}%"></div>
                                    </div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $warmup->current_day }}/{{ $warmup->total_days }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ number_format($warmup->total_sent) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php $health = $warmup->getHealthScore(); @endphp
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $health === 'excellent' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $health === 'good' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $health === 'fair' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $health === 'poor' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ ucfirst($health) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $warmup->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $warmup->status === 'paused' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $warmup->status === 'completed' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $warmup->status === 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                                    {{ $warmup->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ ucfirst($warmup->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <x-button href="{{ route('customer.warmups.show', $warmup) }}" variant="table" size="action" :pill="true" class="p-2" title="View" aria-label="View"><x-lucide name="eye" class="h-4 w-4" /><span class="sr-only">View</span></x-button>
                                    @if($warmup->canStart())
                                        <form method="POST" action="{{ route('customer.warmups.start', $warmup) }}" class="inline">
                                            @csrf
                                            <x-button type="submit" variant="table" size="action" :pill="true">Start</x-button>
                                        </form>
                                    @endif
                                    @if($warmup->canPause())
                                        <form method="POST" action="{{ route('customer.warmups.pause', $warmup) }}" class="inline">
                                            @csrf
                                            <x-button type="submit" variant="table" size="action" :pill="true">Pause</x-button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="text-gray-500 dark:text-gray-400">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No warmups found</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new email warmup.</p>
                                    <div class="mt-6">
                                        <x-button href="{{ route('customer.warmups.create') }}" variant="primary">New Warmup</x-button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($warmups->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">{{ $warmups->links() }}</div>
        @endif
    </x-card>
</div>
@endsection
