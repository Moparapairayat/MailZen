@extends('layouts.customer')

@section('title', 'Automation')
@section('page-title', 'Automation')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between lg:gap-0">
        <div class="w-full lg:flex-1 lg:max-w-lg">
            <form method="GET" action="{{ route('customer.automations.index') }}" class="flex flex-col gap-2 lg:flex-row lg:items-center">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search automations..."
                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                >
                <select
                    name="status"
                    class="w-full lg:w-auto rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                >
                    <option value="">All Statuses</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <x-button type="submit" variant="primary" class="w-full lg:w-auto">Search</x-button>
            </form>
        </div>
        <x-button href="{{ route('customer.automations.create') }}" variant="primary" class="w-full lg:w-auto">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Create Automation
        </x-button>
    </div>

    @php
        $statusColors = [
            'draft' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
            'active' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            'inactive' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        ];
    @endphp

    @if($automations->isEmpty())
        <x-card>
            <div class="text-center text-sm text-gray-500 dark:text-gray-400">
                No automations found. <a href="{{ route('customer.automations.create') }}" class="text-primary-600">Create your first automation</a>
            </div>
        </x-card>
    @else
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 xl:grid-cols-3">
            @foreach($automations as $automation)
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $automation->name }}</div>
                                <div class="mt-1 flex items-center gap-2">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$automation->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($automation->status) }}
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Updated {{ $automation->updated_at?->diffForHumans() }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <x-button href="{{ route('customer.automations.edit', $automation) }}" variant="table" size="action" :pill="true" class="p-2" title="Edit" aria-label="Edit"><x-lucide name="pencil" class="h-4 w-4" /><span class="sr-only">Edit</span></x-button>
                                <form method="POST" action="{{ route('customer.automations.destroy', $automation) }}" class="inline" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="submit" variant="table-danger" size="action" :pill="true" class="p-2" title="Delete" aria-label="Delete"><x-lucide name="trash-2" class="h-4 w-4" /><span class="sr-only">Delete</span></x-button>
                                </form>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div class="rounded-md border border-gray-200 dark:border-gray-700 px-3 py-2">
                                <div class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Runs</div>
                                <div class="mt-0.5 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ (int) ($automation->runs_total ?? 0) }}</div>
                            </div>
                            <div class="rounded-md border border-gray-200 dark:border-gray-700 px-3 py-2">
                                <div class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Active</div>
                                <div class="mt-0.5 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ (int) ($automation->runs_active ?? 0) }}</div>
                            </div>
                            <div class="rounded-md border border-gray-200 dark:border-gray-700 px-3 py-2">
                                <div class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Completed</div>
                                <div class="mt-0.5 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ (int) ($automation->runs_completed ?? 0) }}</div>
                            </div>
                            <div class="rounded-md border border-gray-200 dark:border-gray-700 px-3 py-2">
                                <div class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Stopped</div>
                                <div class="mt-0.5 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ (int) ($automation->runs_stopped ?? 0) }}</div>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-2 text-xs text-gray-600 dark:text-gray-300">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-gray-500 dark:text-gray-400">Last triggered</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">
                                    @if($automation->last_triggered_at)
                                        {{ \Illuminate\Support\Carbon::parse($automation->last_triggered_at)->diffForHumans() }}
                                    @else
                                        —
                                    @endif
                                </span>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-gray-500 dark:text-gray-400">Next scheduled</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">
                                    @if($automation->next_scheduled_for)
                                        {{ \Illuminate\Support\Carbon::parse($automation->next_scheduled_for)->diffForHumans() }}
                                    @else
                                        —
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($automations->hasPages())
            <div class="pt-2">{{ $automations->links() }}</div>
        @endif
    @endif
</div>
@endsection
