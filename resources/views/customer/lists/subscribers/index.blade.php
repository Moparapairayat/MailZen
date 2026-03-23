@extends('layouts.customer')

@section('title', 'Subscribers: ' . $list->name)
@section('page-title', 'Subscribers: ' . $list->name)

@php
    $listCustomColumns = [];
    $columnKeys = [];
    $reservedColumnKeys = [
        'email',
        'first_name',
        'last_name',
        'status',
        'source',
        'subscribed_at',
        'unsubscribed_at',
        'tags',
        'custom_fields',
        'notes',
        'campaigns',
        'open_rate',
        'click_rate',
    ];

    $definedCustomFields = is_array($list->custom_fields ?? null)
        ? array_values(array_filter($list->custom_fields, fn ($field) => is_array($field) && !empty($field['key'])))
        : [];

    foreach ($definedCustomFields as $field) {
        $key = trim((string) ($field['key'] ?? ''));
        if ($key === '') {
            continue;
        }

        $keyLower = strtolower($key);
        if (isset($columnKeys[$keyLower]) || in_array($keyLower, $reservedColumnKeys, true)) {
            continue;
        }

        $listCustomColumns[] = [
            'key' => $key,
            'label' => trim((string) ($field['label'] ?? '')) !== '' ? (string) $field['label'] : $key,
        ];
        $columnKeys[$keyLower] = true;
    }

    $subscriberRows = method_exists($subscribers, 'getCollection') ? $subscribers->getCollection() : collect($subscribers ?? []);
    foreach ($subscriberRows as $subscriberRow) {
        $subscriberCustomFields = is_array($subscriberRow->custom_fields ?? null) ? $subscriberRow->custom_fields : [];
        foreach (array_keys($subscriberCustomFields) as $rawKey) {
            $key = trim((string) $rawKey);
            if ($key === '') {
                continue;
            }

            $keyLower = strtolower($key);
            if (isset($columnKeys[$keyLower]) || in_array($keyLower, $reservedColumnKeys, true)) {
                continue;
            }

            $listCustomColumns[] = [
                'key' => $key,
                'label' => ucwords(str_replace('_', ' ', $key)),
            ];
            $columnKeys[$keyLower] = true;
        }
    }
@endphp

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Subscribers</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $list->display_name ?? $list->name }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('customer.lists.subscribers.import', $list) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
                Import CSV
            </a>
            <a href="{{ route('customer.lists.subscribers.export', $list) }}?{{ http_build_query(['search' => $filters['search'] ?? null, 'status' => $filters['status'] ?? null]) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
                Export CSV
            </a>
            <a href="{{ route('customer.lists.subscribers.create', $list) }}" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700">
                Add Subscriber
            </a>
        </div>
    </div>

    @include('customer.lists.partials.subnav', ['list' => $list])

    <div id="importProgressCard" class="hidden">
        <x-card title="Subscriber Import Progress">
            <div class="space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <x-card>
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</div>
                        <div id="importStatus" class="text-sm text-gray-900 dark:text-gray-100">-</div>
                    </x-card>
                    <x-card>
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total</div>
                        <div id="importTotal" class="text-sm text-gray-900 dark:text-gray-100">0</div>
                    </x-card>
                    <x-card>
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Processed</div>
                        <div id="importProcessed" class="text-sm text-gray-900 dark:text-gray-100">0</div>
                    </x-card>
                    <x-card>
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Imported</div>
                        <div id="importImported" class="text-sm text-gray-900 dark:text-gray-100">0</div>
                    </x-card>
                </div>

                <div id="importProgressWrap" class="hidden">
                    <div class="flex items-center justify-between mb-2">
                        <span id="importProgressText" class="text-sm text-gray-700 dark:text-gray-300">0 / 0 imported</span>
                        <span id="importProgressPercentage" class="text-sm text-gray-700 dark:text-gray-300">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-4 dark:bg-gray-700">
                        <div id="importProgressBar" class="bg-blue-600 h-4 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>

                <div id="importFailureWrap" class="hidden" role="alert">
                    <div class="text-sm text-red-700 dark:text-red-300" id="importFailureReason"></div>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Filters -->
    <x-card>
        <form method="GET" action="{{ route('customer.lists.subscribers.index', $list) }}" class="flex flex-col gap-3 lg:flex-row lg:items-center">
            <div class="flex-1 w-full">
                <input
                    type="text"
                    name="search"
                    value="{{ $filters['search'] ?? '' }}"
                    placeholder="Search by email, name..."
                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                >
            </div>
            <div class="w-full lg:w-56">
                <select
                    name="status"
                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                >
                    <option value="">All Statuses</option>
                    <option value="confirmed" {{ ($filters['status'] ?? '') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="unconfirmed" {{ ($filters['status'] ?? '') === 'unconfirmed' ? 'selected' : '' }}>Unconfirmed</option>
                    <option value="unsubscribed" {{ ($filters['status'] ?? '') === 'unsubscribed' ? 'selected' : '' }}>Unsubscribed</option>
                    <option value="bounced" {{ ($filters['status'] ?? '') === 'bounced' ? 'selected' : '' }}>Bounced</option>
                </select>
            </div>
            <button type="submit" class="w-full lg:w-auto px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700">
                Filter
            </button>
        </form>
    </x-card>

    <!-- Bulk Actions Bar -->
    <div id="bulkActionsBar" class="hidden bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <span id="selectedCount" class="text-sm font-medium text-primary-900 dark:text-primary-100">0 selected</span>
                <button type="button" id="selectAllMatchingBtn" onclick="toggleSelectAllMatching()" class="hidden text-sm text-primary-700 hover:text-primary-900 dark:text-primary-300">
                    Select all {{ $subscribers->total() }}
                </button>
                <div class="flex gap-2">
                    <form id="bulkConfirmForm" method="POST" action="{{ route('customer.lists.subscribers.bulk-confirm', $list) }}" class="inline">
                        @csrf
                        <input type="hidden" name="subscriber_ids" id="bulkConfirmIds">
                        <input type="hidden" name="all_matching" class="bulk-all-matching" value="0">
                        <input type="hidden" name="search" value="{{ $filters['search'] ?? '' }}">
                        <input type="hidden" name="status" value="{{ $filters['status'] ?? '' }}">
                        <button type="submit" class="px-3 py-1.5 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700">
                            Confirm Selected
                        </button>
                    </form>
                    <form id="bulkUnsubscribeForm" method="POST" action="{{ route('customer.lists.subscribers.bulk-unsubscribe', $list) }}" class="inline">
                        @csrf
                        <input type="hidden" name="subscriber_ids" id="bulkUnsubscribeIds">
                        <input type="hidden" name="all_matching" class="bulk-all-matching" value="0">
                        <input type="hidden" name="search" value="{{ $filters['search'] ?? '' }}">
                        <input type="hidden" name="status" value="{{ $filters['status'] ?? '' }}">
                        <button type="submit" class="px-3 py-1.5 text-sm font-medium text-white bg-yellow-600 rounded-md hover:bg-yellow-700">
                            Unsubscribe Selected
                        </button>
                    </form>
                    <form id="bulkResendForm" method="POST" action="{{ route('customer.lists.subscribers.bulk-resend', $list) }}" class="inline">
                        @csrf
                        <input type="hidden" name="subscriber_ids" id="bulkResendIds">
                        <input type="hidden" name="all_matching" class="bulk-all-matching" value="0">
                        <input type="hidden" name="search" value="{{ $filters['search'] ?? '' }}">
                        <input type="hidden" name="status" value="{{ $filters['status'] ?? '' }}">
                        <button type="submit" class="px-3 py-1.5 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                            Resend Confirmation
                        </button>
                    </form>
                    <form id="bulkDeleteForm" method="POST" action="{{ route('customer.lists.subscribers.bulk-delete', $list) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete the selected subscribers? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="subscriber_ids" id="bulkDeleteIds">
                        <input type="hidden" name="all_matching" class="bulk-all-matching" value="0">
                        <input type="hidden" name="search" value="{{ $filters['search'] ?? '' }}">
                        <input type="hidden" name="status" value="{{ $filters['status'] ?? '' }}">
                        <button type="submit" class="px-3 py-1.5 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">
                            Delete Selected
                        </button>
                    </form>
                </div>
            </div>
            <button onclick="clearSelection()" class="text-sm text-primary-600 hover:text-primary-800 dark:text-primary-400">
                Clear Selection
            </button>
        </div>
    </div>

    <!-- Subscribers Table -->
    <x-card :padding="false" class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tags</th>
                        @foreach($listCustomColumns as $field)
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ $field['label'] ?? $field['key'] }}
                            </th>
                        @endforeach
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Campaigns</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Open Rate</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Click Rate</th>
                        <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Source</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Subscribed</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($subscribers as $subscriber)
                        <tr>
                            <td class="px-6 py-3">
                                <input type="checkbox" name="subscriber_ids[]" value="{{ $subscriber->id }}" class="subscriber-checkbox rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600" onchange="updateBulkActions(false)">
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $subscriber->email }}</td>
                            <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100">
                                {{ $subscriber->first_name }} {{ $subscriber->last_name }}
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100">
                                @if(is_array($subscriber->tags ?? null) && count($subscriber->tags) > 0)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach(array_slice($subscriber->tags, 0, 3) as $tag)
                                            <span class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs text-gray-700 dark:text-gray-200">{{ $tag }}</span>
                                        @endforeach
                                        @if(count($subscriber->tags) > 3)
                                            <span class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs text-gray-700 dark:text-gray-200">+{{ count($subscriber->tags) - 3 }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-500 dark:text-gray-400">—</span>
                                @endif
                            </td>
                            @foreach($listCustomColumns as $field)
                                @php
                                    $fieldKey = (string) ($field['key'] ?? '');
                                    $subscriberCustomFields = is_array($subscriber->custom_fields ?? null) ? $subscriber->custom_fields : [];
                                    $value = $fieldKey !== '' ? ($subscriberCustomFields[$fieldKey] ?? null) : null;
                                    if (is_array($value)) {
                                        $value = implode(', ', array_filter(array_map(fn ($v) => is_scalar($v) ? (string) $v : null, $value)));
                                    }
                                @endphp
                                <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100">
                                    {{ ($value !== null && $value !== '') ? $value : '—' }}
                                </td>
                            @endforeach
                            <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100">{{ number_format((int) ($subscriber->total_campaigns ?? 0)) }}</td>
                            <td class="px-6 py-3 text-sm text-blue-600 dark:text-blue-400">{{ number_format((float) ($subscriber->open_rate ?? 0), 2) }}%</td>
                            <td class="px-6 py-3 text-sm text-indigo-600 dark:text-indigo-400">{{ number_format((float) ($subscriber->click_rate ?? 0), 2) }}%</td>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $subscriber->status === 'confirmed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                    {{ $subscriber->status === 'unconfirmed' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                    {{ $subscriber->status === 'unsubscribed' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' : '' }}
                                    {{ $subscriber->status === 'bounced' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}
                                ">
                                    {{ ucfirst($subscriber->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $subscriber->source ?? 'N/A' }}</td>
                            <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400">
                                {{ $subscriber->subscribed_at ? $subscriber->subscribed_at->format('M d, Y') : 'N/A' }}
                            </td>
                            <td class="px-6 py-3 text-right text-sm font-medium">
                                <x-button href="{{ route('customer.lists.subscribers.show', ['list' => $list, 'subscriber' => $subscriber] + request()->query()) }}" variant="table" size="action" :pill="true" class="p-2" title="View" aria-label="View"><x-lucide name="eye" class="h-4 w-4" /><span class="sr-only">View</span></x-button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 11 + count($listCustomColumns) }}" class="text-center py-8 text-sm text-gray-500 dark:text-gray-400">
                                No subscribers found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
            {{ $subscribers->links() }}
        </div>
    </x-card>
</div>

@push('scripts')
<script>
    let importRefreshInterval = null;
    let allMatchingSelected = false;
    const totalMatchingSubscribers = {{ $subscribers->total() }};

    function formatNumber(value) {
        return new Intl.NumberFormat().format(parseInt(value || 0, 10));
    }

    function capitalize(value) {
        const text = String(value || '');
        if (!text) {
            return '';
        }
        return text.charAt(0).toUpperCase() + text.slice(1);
    }

    function setHidden(el, hidden) {
        if (!el) {
            return;
        }
        if (hidden) {
            el.classList.add('hidden');
        } else {
            el.classList.remove('hidden');
        }
    }

    function updateImportStats() {
        fetch('{{ route('customer.lists.subscribers.import.stats', $list) }}', {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (!data || !data.success) {
                return;
            }

            const importCard = document.getElementById('importProgressCard');
            const importStatus = document.getElementById('importStatus');
            const importTotal = document.getElementById('importTotal');
            const importProcessed = document.getElementById('importProcessed');
            const importImported = document.getElementById('importImported');
            const progressWrap = document.getElementById('importProgressWrap');
            const progressText = document.getElementById('importProgressText');
            const progressPercentage = document.getElementById('importProgressPercentage');
            const progressBar = document.getElementById('importProgressBar');
            const failureWrap = document.getElementById('importFailureWrap');
            const failureReason = document.getElementById('importFailureReason');

            const stats = data.import;

            if (!stats) {
                setHidden(importCard, true);
                if (importRefreshInterval) {
                    clearInterval(importRefreshInterval);
                    importRefreshInterval = null;
                }
                return;
            }

            setHidden(importCard, false);

            const status = String(stats.status || 'unknown');
            const total = parseInt(stats.total_rows || 0, 10);
            const processed = parseInt(stats.processed_count || 0, 10);
            const imported = parseInt(stats.imported_count || 0, 10);
            const percent = stats.percent || 0;

            if (importStatus) {
                importStatus.textContent = capitalize(status);
            }
            if (importTotal) {
                importTotal.textContent = formatNumber(total);
            }
            if (importProcessed) {
                importProcessed.textContent = formatNumber(processed);
            }
            if (importImported) {
                importImported.textContent = formatNumber(imported);
            }

            const showProgress = (status === 'queued' || status === 'running') && total > 0;
            setHidden(progressWrap, !showProgress);
            if (showProgress && progressText && progressPercentage && progressBar) {
                progressText.textContent = formatNumber(processed) + ' / ' + formatNumber(total) + ' processed';
                progressPercentage.textContent = String(percent) + '%';
                progressBar.style.width = String(percent) + '%';
            }

            const isFailed = status === 'failed';
            setHidden(failureWrap, !isFailed);
            if (isFailed && failureReason) {
                failureReason.textContent = String(stats.failure_reason || 'Import failed.');
            }

            if (status === 'completed' || status === 'failed') {
                if (importRefreshInterval) {
                    clearInterval(importRefreshInterval);
                    importRefreshInterval = null;
                }
            }
        })
        .catch(() => {
        });
    }

    function toggleSelectAll(checkbox) {
        const checkboxes = document.querySelectorAll('.subscriber-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = checkbox.checked;
        });
        updateBulkActions(false);
    }

    function toggleSelectAllMatching() {
        allMatchingSelected = !allMatchingSelected;
        if (allMatchingSelected) {
            const checkboxes = document.querySelectorAll('.subscriber-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = true;
            });
        }
        updateBulkActions(true);
    }

    function updateBulkActions(preserveAllMatching = true) {
        if (!preserveAllMatching) {
            allMatchingSelected = false;
        }

        const checkboxes = document.querySelectorAll('.subscriber-checkbox:checked');
        const selectedIds = Array.from(checkboxes).map(cb => cb.value);
        const count = allMatchingSelected ? totalMatchingSubscribers : selectedIds.length;
        
        const bulkBar = document.getElementById('bulkActionsBar');
        const selectedCount = document.getElementById('selectedCount');
        const selectAllMatchingBtn = document.getElementById('selectAllMatchingBtn');
        
        if (count > 0) {
            bulkBar.classList.remove('hidden');
            selectedCount.textContent = (allMatchingSelected ? 'All ' : '') + count + ' selected';
            
            // Update hidden inputs
            document.getElementById('bulkConfirmIds').value = allMatchingSelected ? '' : selectedIds.join(',');
            document.getElementById('bulkUnsubscribeIds').value = allMatchingSelected ? '' : selectedIds.join(',');
            document.getElementById('bulkResendIds').value = allMatchingSelected ? '' : selectedIds.join(',');
            document.getElementById('bulkDeleteIds').value = allMatchingSelected ? '' : selectedIds.join(',');

            document.querySelectorAll('.bulk-all-matching').forEach(el => {
                el.value = allMatchingSelected ? '1' : '0';
            });

            if (selectAllMatchingBtn) {
                if (allMatchingSelected) {
                    selectAllMatchingBtn.classList.remove('hidden');
                    selectAllMatchingBtn.textContent = 'Select this page only';
                } else if (selectedIds.length > 0 && totalMatchingSubscribers > selectedIds.length) {
                    selectAllMatchingBtn.classList.remove('hidden');
                    selectAllMatchingBtn.textContent = 'Select all ' + totalMatchingSubscribers;
                } else {
                    selectAllMatchingBtn.classList.add('hidden');
                }
            }
        } else {
            bulkBar.classList.add('hidden');
            if (selectAllMatchingBtn) {
                selectAllMatchingBtn.classList.add('hidden');
            }
        }
        
        // Update select all checkbox
        const allCheckboxes = document.querySelectorAll('.subscriber-checkbox');
        const selectAll = document.getElementById('selectAll');
        if (allCheckboxes.length > 0) {
            selectAll.checked = checkboxes.length === allCheckboxes.length;
            selectAll.indeterminate = checkboxes.length > 0 && checkboxes.length < allCheckboxes.length;
        }
    }

    function clearSelection() {
        allMatchingSelected = false;
        const checkboxes = document.querySelectorAll('.subscriber-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = false;
        });
        document.getElementById('selectAll').checked = false;
        document.getElementById('selectAll').indeterminate = false;
        updateBulkActions(true);
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateBulkActions(true);

        updateImportStats();
        if (!importRefreshInterval) {
            importRefreshInterval = setInterval(updateImportStats, 3000);
        }
    });
</script>
@endpush
@endsection

