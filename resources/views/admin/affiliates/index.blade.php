@extends('layouts.admin')

@section('title', __('Affiliation'))
@section('page-title', __('Affiliation'))

@section('content')
<div class="space-y-4">
    <div class="border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
        <nav class="-mb-px flex min-w-max space-x-6 sm:space-x-8 px-2 sm:px-0" aria-label="Tabs">
            @foreach(($navItems ?? []) as $item)
                <a
                    href="{{ route($item['route']) }}"
                    class="{{ ($item['active'] ?? false) ? '!border-primary-500 text-primary-600 dark:text-primary-400 border-b-2' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }} whitespace-nowrap shrink-0 py-3 sm:py-4 px-2 sm:px-1 font-medium text-sm"
                >
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>
    </div>

    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between lg:gap-0">
        <form method="GET" action="{{ route('admin.affiliates.index') }}" class="w-full lg:w-auto flex flex-col gap-2 lg:flex-row lg:items-center">
            <input
                type="text"
                name="q"
                value="{{ $search }}"
                placeholder="{{ __('Search by code/customer') }}"
                class="block w-full lg:w-72 rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
            >
            <x-button type="submit" variant="secondary" class="w-full lg:w-auto">{{ __('Search') }}</x-button>
            <x-button href="{{ route('admin.affiliates.index') }}" variant="secondary" class="w-full lg:w-auto">{{ __('Reset') }}</x-button>
        </form>

        <x-button href="{{ route('admin.affiliates.create') }}" variant="primary" class="w-full lg:w-auto">{{ __('Add New') }}</x-button>

        <div class="flex flex-wrap gap-2 text-sm text-gray-600 dark:text-gray-300">
            <div>{{ __('Affiliates') }}: {{ number_format((int) ($stats['affiliates'] ?? 0)) }}</div>
            <div>{{ __('Referrals') }}: {{ number_format((int) ($stats['referrals'] ?? 0)) }}</div>
            <div>{{ __('Commissions') }}: {{ number_format((int) ($stats['commissions'] ?? 0)) }}</div>
            <div>{{ __('Payouts') }}: {{ number_format((int) ($stats['payouts'] ?? 0)) }}</div>
        </div>

    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-card title="{{ __('Affiliates Created (Last 30 Days)') }}">
                <div class="mt-2" style="height: 280px;">
                    <canvas id="adminAffiliatesChart"></canvas>
                </div>
            </x-card>
        </div>
        <x-card title="{{ __('Overview') }}">
            <div class="grid grid-cols-2 gap-3 text-sm text-gray-700 dark:text-gray-200">
                <div class="rounded-md border border-gray-200 dark:border-gray-700 p-3">
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Affiliates') }}</div>
                    <div class="text-lg font-semibold">{{ number_format((int) ($stats['affiliates'] ?? 0)) }}</div>
                </div>
                <div class="rounded-md border border-gray-200 dark:border-gray-700 p-3">
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Referrals') }}</div>
                    <div class="text-lg font-semibold">{{ number_format((int) ($stats['referrals'] ?? 0)) }}</div>
                </div>
                <div class="rounded-md border border-gray-200 dark:border-gray-700 p-3">
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Commissions') }}</div>
                    <div class="text-lg font-semibold">{{ number_format((int) ($stats['commissions'] ?? 0)) }}</div>
                </div>
                <div class="rounded-md border border-gray-200 dark:border-gray-700 p-3">
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Payouts') }}</div>
                    <div class="text-lg font-semibold">{{ number_format((int) ($stats['payouts'] ?? 0)) }}</div>
                </div>
            </div>
        </x-card>
    </div>

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Code') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Customer') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($affiliates as $affiliate)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $affiliate->code }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                <div>{{ $affiliate->customer?->full_name ?? '—' }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $affiliate->customer?->email ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ $affiliate->status }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-700 dark:text-gray-200">
                                <div class="flex items-center justify-end gap-2">
                                    @if($affiliate->status !== 'approved')
                                        <form method="POST" action="{{ route('admin.affiliates.approve', $affiliate) }}" class="inline" onsubmit="return confirm(@json(__('Approve this affiliate?')));">
                                            @csrf
                                            <x-button type="submit" variant="table" size="action" :pill="true">{{ __('Approve') }}</x-button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('No affiliates found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($affiliates->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">{{ $affiliates->links() }}</div>
        @endif
    </x-card>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            const labels = @json($chartLabels ?? []);
            const values = @json($chartValues ?? []);

            const isDark = () => document.documentElement.classList.contains('dark');
            const theme = () => {
                if (isDark()) {
                    return {
                        gridColor: 'rgba(255, 255, 255, 0.10)',
                        tickColor: 'rgba(255, 255, 255, 0.70)',
                    };
                }
                return {
                    gridColor: 'rgba(17, 24, 39, 0.10)',
                    tickColor: 'rgba(17, 24, 39, 0.70)',
                };
            };

            const el = document.getElementById('adminAffiliatesChart');
            if (!el || typeof Chart === 'undefined') {
                return;
            }

            const ctx = el.getContext('2d');
            if (!ctx) {
                return;
            }

            const t0 = theme();
            const chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: @json(__('Affiliates')),
                        data: values,
                        backgroundColor: 'rgba(30, 95, 234, 0.25)',
                        borderColor: '#1E5FEA',
                        borderWidth: 1,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                    },
                    scales: {
                        x: {
                            grid: { color: t0.gridColor },
                            ticks: { color: t0.tickColor },
                        },
                        y: {
                            grid: { color: t0.gridColor },
                            ticks: { color: t0.tickColor },
                            beginAtZero: true,
                        },
                    }
                }
            });

            const applyTheme = () => {
                const t = theme();
                chart.options.scales.x.grid.color = t.gridColor;
                chart.options.scales.y.grid.color = t.gridColor;
                chart.options.scales.x.ticks.color = t.tickColor;
                chart.options.scales.y.ticks.color = t.tickColor;
                chart.update('none');
            };

            const observer = new MutationObserver(applyTheme);
            observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
        })();
    </script>
@endpush
