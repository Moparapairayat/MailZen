@extends('layouts.customer')

@section('title', 'Email Validation Run')
@section('page-title', 'Email Validation Run')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between lg:gap-0">
        <div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Run #{{ $run->id }}</div>
            <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $run->list?->name ?? 'Email List' }}</div>
        </div>
        <div class="flex flex-col gap-2 w-full lg:w-auto lg:flex-row">
            <x-button href="{{ route('customer.email-validation.runs.index') }}" variant="secondary" class="w-full lg:w-auto">Back</x-button>

            @php($isPaused = (bool) data_get($run->settings ?? [], 'is_paused', false))

            @if($run->status === 'running' && !$isPaused)
                <form method="POST" action="{{ route('customer.email-validation.runs.pause', $run) }}" class="w-full lg:w-auto">
                    @csrf
                    <x-button type="submit" variant="secondary" class="w-full lg:w-auto">Pause</x-button>
                </form>
            @elseif($run->status === 'running' && $isPaused)
                <form method="POST" action="{{ route('customer.email-validation.runs.resume', $run) }}" class="w-full lg:w-auto">
                    @csrf
                    <x-button type="submit" class="w-full lg:w-auto">Resume</x-button>
                </form>
            @elseif($run->status === 'failed')
                <form method="POST" action="{{ route('customer.email-validation.runs.resume-failed', $run) }}" class="w-full lg:w-auto">
                    @csrf
                    <x-button type="submit" class="w-full lg:w-auto">Resume</x-button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100" id="runStatus">{{ ucfirst($run->status) }}</div>
        </x-card>
        <x-card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100" id="totalCount">{{ number_format($run->total_emails) }}</div>
        </x-card>
        <x-card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Processed</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100" id="processedCount">{{ number_format($run->processed_count) }}</div>
        </x-card>
        <x-card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Undeliverable</div>
            <div class="mt-1 text-2xl font-semibold text-red-600 dark:text-red-400" id="undeliverableCount">{{ number_format($run->undeliverable_count) }}</div>
        </x-card>
    </div>

    <div id="runFailureWrapper" class="{{ $run->status === 'failed' && $run->failure_reason ? '' : 'hidden' }}">
        <x-card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Failure Reason</div>
            <div class="mt-2 text-sm text-red-700 dark:text-red-300 break-words" id="runFailureReason">{{ $run->failure_reason }}</div>
        </x-card>
    </div>

    @php($pauseReason = is_string(data_get($run->settings ?? [], 'pause_reason')) ? (string) data_get($run->settings ?? [], 'pause_reason') : null)
    <div id="runPauseWrapper" class="{{ $isPaused && $pauseReason ? '' : 'hidden' }}">
        <x-card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Paused Reason</div>
            <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300 break-words" id="runPauseReason">{{ $pauseReason }}</div>
        </x-card>
    </div>

    @if(in_array($run->status, ['pending', 'running'], true) && $run->total_emails > 0)
        <x-card title="Validation Progress">
            <div class="space-y-4">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300" id="progressText">
                            {{ number_format($run->processed_count) }} / {{ number_format($run->total_emails) }} checked
                        </span>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300" id="progressPercentage">
                            {{ number_format(($run->processed_count / max(1, $run->total_emails)) * 100, 1) }}%
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-4 dark:bg-gray-700">
                        <div class="bg-blue-600 h-4 rounded-full transition-all duration-300" id="progressBar" style="width: {{ ($run->processed_count / max(1, $run->total_emails)) * 100 }}%"></div>
                    </div>
                </div>
            </div>
        </x-card>
    @endif

    <x-card title="Result Breakdown">
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-6">
            <div class="text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <div class="text-2xl font-bold text-green-600 dark:text-green-400" id="deliverableCount">{{ number_format($run->deliverable_count) }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Deliverable</div>
            </div>
            <div class="text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <div class="text-2xl font-bold text-red-600 dark:text-red-400" id="undeliverableCount2">{{ number_format($run->undeliverable_count) }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Undeliverable</div>
            </div>
            <div class="text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <div class="text-2xl font-bold text-red-600 dark:text-red-400" id="inboxFullCount">0</div>
                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Inbox Full</div>
            </div>
            <div class="text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400" id="acceptAllCount">{{ number_format($run->accept_all_count) }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Accept-all</div>
            </div>
            <div class="text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100" id="unknownCount">{{ number_format($run->unknown_count) }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Unknown</div>
            </div>
            <div class="text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100" id="errorCount">{{ number_format($run->error_count) }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Errors</div>
            </div>
        </div>
    </x-card>

    <div id="runErrorDetailsWrapper" class="{{ (int) $run->error_count > 0 ? '' : 'hidden' }}">
        <x-card title="Error Details">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Email</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Message</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Result</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Time</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700" id="runErrorDetailsBody">
                        <tr>
                            <td colspan="4" class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                Showing the most recent errors.
            </div>
        </x-card>
    </div>
</div>

@push('scripts')
<script>
    let refreshInterval;

    function updateRunStats() {
        fetch('{{ route('customer.email-validation.runs.stats', $run) }}', {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                return;
            }

            const stats = data.stats;

            document.getElementById('runStatus').textContent = stats.status.charAt(0).toUpperCase() + stats.status.slice(1);
            document.getElementById('totalCount').textContent = new Intl.NumberFormat().format(stats.total_emails);
            document.getElementById('processedCount').textContent = new Intl.NumberFormat().format(stats.processed_count);

            document.getElementById('deliverableCount').textContent = new Intl.NumberFormat().format(stats.deliverable_count);
            document.getElementById('undeliverableCount').textContent = new Intl.NumberFormat().format(stats.undeliverable_count);
            document.getElementById('undeliverableCount2').textContent = new Intl.NumberFormat().format(stats.undeliverable_count);
            const inboxFullEl = document.getElementById('inboxFullCount');
            if (inboxFullEl) {
                inboxFullEl.textContent = new Intl.NumberFormat().format(stats.inbox_full_count || 0);
            }
            document.getElementById('acceptAllCount').textContent = new Intl.NumberFormat().format(stats.accept_all_count);
            document.getElementById('unknownCount').textContent = new Intl.NumberFormat().format(stats.unknown_count);
            document.getElementById('errorCount').textContent = new Intl.NumberFormat().format(stats.error_count);

            const progressText = document.getElementById('progressText');
            const progressPercentage = document.getElementById('progressPercentage');
            const progressBar = document.getElementById('progressBar');

            if (progressText && progressPercentage && progressBar) {
                progressText.textContent = new Intl.NumberFormat().format(stats.processed_count) + ' / ' + new Intl.NumberFormat().format(stats.total_emails) + ' checked';
                progressPercentage.textContent = stats.percent + '%';
                progressBar.style.width = stats.percent + '%';
            }

            const failureWrapper = document.getElementById('runFailureWrapper');
            const failureReason = document.getElementById('runFailureReason');
            if (failureWrapper && failureReason) {
                if (stats.status === 'failed' && stats.failure_reason) {
                    failureReason.textContent = stats.failure_reason;
                    failureWrapper.classList.remove('hidden');
                }
            }

            const pauseWrapper = document.getElementById('runPauseWrapper');
            const pauseReason = document.getElementById('runPauseReason');
            if (pauseWrapper && pauseReason) {
                if (stats.is_paused && stats.pause_reason) {
                    pauseReason.textContent = stats.pause_reason;
                    pauseWrapper.classList.remove('hidden');
                } else {
                    pauseWrapper.classList.add('hidden');
                }
            }

            if (stats.status === 'completed' || stats.status === 'failed') {
                clearInterval(refreshInterval);
            }
        })
        .catch(error => {
            console.error('Error updating stats:', error);
        });
    }

    function updateErrorDetails() {
        fetch('{{ route('customer.email-validation.runs.errors', $run) }}?limit=50', {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                return;
            }

            const items = Array.isArray(data.items) ? data.items : [];
            const wrapper = document.getElementById('runErrorDetailsWrapper');
            const body = document.getElementById('runErrorDetailsBody');
            if (!wrapper || !body) {
                return;
            }

            if (items.length === 0) {
                wrapper.classList.add('hidden');
                return;
            }

            wrapper.classList.remove('hidden');
            body.innerHTML = '';

            items.forEach(item => {
                const tr = document.createElement('tr');
                const email = (item.email || '—');
                const message = (item.message || '—');
                const result = (item.result || '—');
                const time = (item.validated_at || '—');

                tr.innerHTML = `
                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 break-words">${email}</td>
                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 break-words">${message}</td>
                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">${result}</td>
                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">${time}</td>
                `;
                body.appendChild(tr);
            });
        })
        .catch(error => {
            console.error('Error updating error details:', error);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        if ('{{ $run->status }}' === 'pending' || '{{ $run->status }}' === 'running') {
            refreshInterval = setInterval(updateRunStats, 3000);
            updateErrorDetails();
            setInterval(updateErrorDetails, 5000);
        }
    });

    window.addEventListener('beforeunload', function() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
    });
</script>
@endpush
@endsection
