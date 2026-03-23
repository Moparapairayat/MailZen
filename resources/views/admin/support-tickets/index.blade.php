@extends('layouts.admin')

@section('title', __('Support Tickets'))
@section('page-title', __('Support Tickets'))

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/trix@2.1.1/dist/trix.css">
    <style>
        trix-editor {
            min-height: 140px;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/trix@2.1.1/dist/trix.umd.min.js" defer></script>
@endpush

@section('content')
@php
    $status = $filters['status'] ?? 'open';
@endphp

<div
    x-data="{
        open: false,
        loading: false,
        ticketId: null,
        html: '',
        baseUrl: @js(url('/admin/support-tickets')),
        csrf: @js(csrf_token()),

        openDrawer(id) {
            this.ticketId = id;
            this.open = true;
            this.loadDrawer();
        },

        closeDrawer() {
            this.open = false;
            this.loading = false;
            this.ticketId = null;
            this.html = '';
        },

        async loadDrawer() {
            if (!this.ticketId) return;

            this.loading = true;
            try {
                const url = this.baseUrl + '/' + encodeURIComponent(this.ticketId) + '/drawer';
                const res = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!res.ok) {
                    this.html = '<div class=\'text-sm text-red-600\'>Failed to load ticket (' + res.status + ').</div>';
                    return;
                }

                this.html = await res.text();
            } catch (e) {
                this.html = '<div class=\'text-sm text-red-600\'>Failed to load ticket.</div>';
            } finally {
                this.loading = false;
            }
        },

        async onDrawerSubmit(e) {
            const form = e.target;
            if (!(form instanceof HTMLFormElement)) return;
            if (!form.hasAttribute('data-drawer-ajax')) return;

            e.preventDefault();

            const formData = new FormData(form);

            try {
                const res = await fetch(form.action, {
                    method: (form.method || 'POST').toUpperCase(),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.csrf,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                if (!res.ok) {
                    const data = await res.json().catch(() => null);
                    const msg = data?.message || 'Request failed.';
                    alert(msg);
                    return;
                }

                await this.loadDrawer();
                this.updateListAfterSubmit(form, formData);
            } catch (err) {
                alert('Request failed.');
            }
        },

        updateListAfterSubmit(form, formData) {
            const row = document.querySelector(`[data-ticket-row='${this.ticketId}']`);
            if (!row) return;

            const action = (form.action || '').toString();

            if (action.endsWith('/status')) {
                const next = (formData.get('status') || '').toString();
                const prev = (row.dataset.status || '').toString();
                if (!next) return;

                row.dataset.status = next;

                const badge = document.querySelector(`[data-ticket-status='${this.ticketId}']`);
                if (badge) {
                    badge.textContent = next.charAt(0).toUpperCase() + next.slice(1);
                    badge.className = 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ' + (next === 'closed'
                        ? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200'
                        : 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200'
                    );
                }

                const openCountEl = document.querySelector('[data-open-count]');
                const closedCountEl = document.querySelector('[data-closed-count]');
                if (openCountEl && closedCountEl && prev && prev !== next) {
                    const openCount = parseInt(openCountEl.textContent || '0', 10) || 0;
                    const closedCount = parseInt(closedCountEl.textContent || '0', 10) || 0;
                    if (prev === 'open' && next === 'closed') {
                        openCountEl.textContent = String(Math.max(0, openCount - 1));
                        closedCountEl.textContent = String(closedCount + 1);
                    }
                    if (prev === 'closed' && next === 'open') {
                        closedCountEl.textContent = String(Math.max(0, closedCount - 1));
                        openCountEl.textContent = String(openCount + 1);
                    }
                }

                const currentStatus = @js($status);
                if (currentStatus === 'open' && next === 'closed') {
                    row.remove();
                }
                if (currentStatus === 'closed' && next === 'open') {
                    row.remove();
                }
            }

            if (action.endsWith('/priority')) {
                const next = (formData.get('priority') || '').toString();
                if (!next) return;

                row.dataset.priority = next;

                const badge = document.querySelector(`[data-ticket-priority='${this.ticketId}']`);
                if (badge) {
                    badge.textContent = next.charAt(0).toUpperCase() + next.slice(1);
                    badge.className = 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ' + (next === 'high'
                        ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200'
                        : (next === 'low'
                            ? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200'
                            : 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200'
                        )
                    );
                }
            }

            if (action.endsWith('/reply')) {
                const lastUpdate = document.querySelector(`[data-ticket-last-update='${this.ticketId}']`);
                if (lastUpdate) {
                    lastUpdate.textContent = 'just now';
                }
            }
        }
    }"
    class="space-y-4"
>
    <x-card :padding="false">
        <div class="p-4 border-b border-gray-100 dark:border-gray-800">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="inline-flex items-center gap-1 rounded-xl bg-gray-50 dark:bg-gray-800 p-1 border border-gray-100 dark:border-gray-700">
                    <a
                        href="{{ request()->fullUrlWithQuery(['status' => 'open', 'page' => null]) }}"
                        class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ $status === 'open' ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100' }}"
                    >
                        {{ __('Opened') }} (<span data-open-count>{{ $openCount ?? 0 }}</span>)
                    </a>
                    <a
                        href="{{ request()->fullUrlWithQuery(['status' => 'closed', 'page' => null]) }}"
                        class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ $status === 'closed' ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100' }}"
                    >
                        {{ __('Closed') }} (<span data-closed-count>{{ $closedCount ?? 0 }}</span>)
                    </a>
                </div>

                <form method="GET" action="{{ route('admin.support-tickets.index') }}" class="w-full lg:w-auto">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                            </svg>
                        </span>
                        <input
                            name="search"
                            value="{{ $filters['search'] ?? '' }}"
                            placeholder="{{ __('Search') }}"
                            class="w-full lg:w-80 pl-10 pr-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm text-gray-900 dark:text-gray-100 placeholder:text-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        />
                    </div>
                </form>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50 dark:bg-gray-800/60">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Ticket ID') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Title') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Customer') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Priority') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Last update') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($tickets as $ticket)
                        @php
                            $statusBadge = $ticket->status === 'closed'
                                ? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200'
                                : 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200';

                            $priorityBadge = match ($ticket->priority) {
                                'high' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200',
                                'low' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200',
                                default => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200',
                            };
                        @endphp
                        <tr
                            class="hover:bg-gray-50 dark:hover:bg-gray-800/40 cursor-pointer"
                            data-ticket-row="{{ $ticket->id }}"
                            data-status="{{ $ticket->status }}"
                            data-priority="{{ $ticket->priority }}"
                            @click="openDrawer({{ $ticket->id }})"
                        >
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100 whitespace-nowrap">#{{ $ticket->id }}</td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $ticket->subject }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $ticket->created_at?->format('M d, Y') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $ticket->customer?->full_name ?? '—' }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $ticket->customer?->email ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span data-ticket-status="{{ $ticket->id }}" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusBadge }}">
                                    {{ ucfirst($ticket->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span data-ticket-priority="{{ $ticket->id }}" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $priorityBadge }}">
                                    {{ ucfirst($ticket->priority) }}
                                </span>
                            </td>
                            <td data-ticket-last-update="{{ $ticket->id }}" class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                {{ ($ticket->last_message_at ?? $ticket->updated_at)->diffForHumans() }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800"
                                    @click.stop="openDrawer({{ $ticket->id }})"
                                    aria-label="{{ __('View') }}"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                {{ __('No tickets found.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tickets->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800">
                {{ $tickets->links() }}
            </div>
        @endif
    </x-card>

    <div
        x-cloak
        x-show="open"
        class="fixed inset-0 z-50"
        @keydown.window.escape="closeDrawer()"
    >
        <div class="absolute inset-0 bg-black/40" @click="closeDrawer()"></div>

        <div class="absolute right-0 top-0 h-full w-full max-w-2xl bg-white dark:bg-gray-900 border-l border-gray-200 dark:border-gray-800 shadow-2xl">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                <div class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('Support Ticket') }}</div>
                <button type="button" class="p-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800" @click="closeDrawer()" aria-label="{{ __('Close') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="h-[calc(100%-64px)] overflow-y-auto p-5" @submit="onDrawerSubmit($event)">
                <template x-if="loading">
                    <div class="py-10 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('Loading...') }}</div>
                </template>
                <div x-html="html"></div>
            </div>
        </div>
    </div>
</div>
@endsection
