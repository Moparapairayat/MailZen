@extends('layouts.admin')

@section('title', 'Activities')
@section('page-title', 'Activities')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-2">
            <a
                href="{{ route('admin.activities.index', ['range' => '7d']) }}"
                class="px-3 py-2 rounded-lg text-sm border border-admin-border hover:bg-white/5 {{ ($range ?? '7d') === '7d' ? 'bg-white/10 text-admin-text-primary' : 'text-admin-text-secondary' }}"
            >
                Last 7 Days
            </a>
            <a
                href="{{ route('admin.activities.index', ['range' => '30d']) }}"
                class="px-3 py-2 rounded-lg text-sm border border-admin-border hover:bg-white/5 {{ ($range ?? '7d') === '30d' ? 'bg-white/10 text-admin-text-primary' : 'text-admin-text-secondary' }}"
            >
                Last 30 Days
            </a>
        </div>

        <form method="GET" action="{{ route('admin.activities.index') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <input type="hidden" name="range" value="custom" />
            <div class="flex items-center gap-2">
                <div>
                    <label class="sr-only" for="from">From</label>
                    <input
                        id="from"
                        name="from"
                        type="date"
                        value="{{ optional($startDate ?? null)->toDateString() }}"
                        class="px-3 py-2 rounded-lg text-sm border border-admin-border bg-white/5 text-admin-text-primary"
                    />
                </div>
                <div>
                    <label class="sr-only" for="to">To</label>
                    <input
                        id="to"
                        name="to"
                        type="date"
                        value="{{ optional($endDate ?? null)->toDateString() }}"
                        class="px-3 py-2 rounded-lg text-sm border border-admin-border bg-white/5 text-admin-text-primary"
                    />
                </div>
                <button type="submit" class="px-3 py-2 rounded-lg text-sm bg-primary-500 text-white hover:bg-primary-600">
                    Apply
                </button>
            </div>
        </form>
    </div>

    <x-card :padding="false">
        <x-slot name="header">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-admin-text-primary">All Activities</h3>
                    <p class="mt-1 text-sm text-admin-text-secondary">
                        {{ optional($startDate ?? null)->format('M j, Y') }} - {{ optional($endDate ?? null)->format('M j, Y') }}
                    </p>
                </div>
                <a href="{{ route('admin.dashboard', request()->only(['range', 'from', 'to'])) }}" class="text-sm text-primary-500 hover:text-primary-600">
                    Back to dashboard
                </a>
            </div>
        </x-slot>

        <div class="divide-y divide-gray-200 dark:divide-admin-border/60">
            @forelse(($activities ?? []) as $item)
                <div class="px-6 py-3 flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-admin-text-primary">{{ $item['label'] ?? '' }}</p>
                        <p class="text-xs text-admin-text-secondary truncate">{{ $item['detail'] ?? '' }}</p>
                    </div>
                    <p class="text-xs text-admin-text-secondary whitespace-nowrap">
                        {{ \Carbon\Carbon::parse($item['at'])->diffForHumans() }}
                    </p>
                </div>
            @empty
                <div class="px-6 py-10 text-center text-admin-text-secondary">
                    <p>No activities</p>
                </div>
            @endforelse
        </div>

        <div class="activities-pagination px-6 py-4 border-t border-admin-border bg-white/5 rounded-b-lg">
            {{ $activities->links() }}
        </div>
    </x-card>
</div>
@endsection
