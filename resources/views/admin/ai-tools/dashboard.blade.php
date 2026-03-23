@extends('layouts.admin')

@section('title', __('AI Dashboard'))
@section('page-title', __('AI Dashboard'))

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-2">
            <a
                href="{{ route('admin.ai-tools.dashboard', ['range' => '7d']) }}"
                class="px-3 py-2 rounded-lg text-sm border border-admin-border hover:bg-white/5 {{ ($range ?? '7d') === '7d' ? 'bg-white/10 text-admin-text-primary' : 'text-admin-text-secondary' }}"
            >
                {{ __('Last 7 Days') }}
            </a>
            <a
                href="{{ route('admin.ai-tools.dashboard', ['range' => '30d']) }}"
                class="px-3 py-2 rounded-lg text-sm border border-admin-border hover:bg-white/5 {{ ($range ?? '7d') === '30d' ? 'bg-white/10 text-admin-text-primary' : 'text-admin-text-secondary' }}"
            >
                {{ __('Last 30 Days') }}
            </a>
        </div>

        <form method="GET" action="{{ route('admin.ai-tools.dashboard') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <input type="hidden" name="range" value="custom" />
            <div class="flex items-center gap-2">
                <div>
                    <label class="sr-only" for="from">{{ __('From') }}</label>
                    <input
                        id="from"
                        name="from"
                        type="date"
                        value="{{ optional($startDate ?? null)->toDateString() }}"
                        class="px-3 py-2 rounded-lg text-sm border border-admin-border bg-white/5 text-admin-text-primary"
                    />
                </div>
                <div>
                    <label class="sr-only" for="to">{{ __('To') }}</label>
                    <input
                        id="to"
                        name="to"
                        type="date"
                        value="{{ optional($endDate ?? null)->toDateString() }}"
                        class="px-3 py-2 rounded-lg text-sm border border-admin-border bg-white/5 text-admin-text-primary"
                    />
                </div>
                <button type="submit" class="px-3 py-2 rounded-lg text-sm bg-primary-500 text-white hover:bg-primary-600">
                    {{ __('Apply') }}
                </button>
            </div>
        </form>
    </div>

    <x-card>
        <div class="flex flex-col gap-1">
            <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('Admin AI usage aggregated from successful AI generations that used admin keys.') }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-500">{{ __('Range:') }} {{ $rangeLabel ?? __('This month') }}</div>
        </div>
    </x-card>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-card>
            <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Total tokens used') }}</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format((int) ($totalTokensUsed ?? 0)) }}</div>

            @php
                $tl = $totalLimit;
                $tp = $totalPercent;
            @endphp

            <div class="mt-4">
                <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-400">
                    <span>{{ __('Monthly limit') }}</span>
                    <span>
                        @if(is_numeric($tl))
                            {{ number_format((int) $tl) }}
                        @else
                            {{ __('Unlimited') }}
                        @endif
                    </span>
                </div>

                <div class="mt-2 h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                    @if(is_numeric($tp))
                        <div class="h-full bg-primary-500" style="width: {{ (float) $tp }}%"></div>
                    @else
                        <div class="h-full bg-primary-500" style="width: 0%"></div>
                    @endif
                </div>

                <div class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                    @if(is_numeric($tp))
                        {{ number_format((float) $tp, 1) }}%
                    @else
                        {{ __('No limit configured') }}
                    @endif
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Tokens used today') }}</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format((int) ($totalTokensUsedToday ?? 0)) }}</div>

            @php
                $dl = $totalDailyLimit;
                $dp = $totalDailyPercent;
            @endphp

            <div class="mt-4">
                <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-400">
                    <span>{{ __('Daily limit') }}</span>
                    <span>
                        @if(is_numeric($dl))
                            {{ number_format((int) $dl) }}
                        @else
                            {{ __('Unlimited') }}
                        @endif
                    </span>
                </div>

                <div class="mt-2 h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                    @if(is_numeric($dp))
                        <div class="h-full bg-primary-500" style="width: {{ (float) $dp }}%"></div>
                    @else
                        <div class="h-full bg-primary-500" style="width: 0%"></div>
                    @endif
                </div>

                <div class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                    @if(is_numeric($dp))
                        {{ number_format((float) $dp, 1) }}%
                    @else
                        {{ __('No limit configured') }}
                    @endif
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Estimated total cost') }}</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                ${{ number_format(((int) ($totalCostCents ?? 0)) / 100, 2) }}
            </div>
            <div class="mt-2 text-xs text-gray-500 dark:text-gray-500">
                {{ __('Based on the configured cost per 1M tokens in Settings > AI.') }}
            </div>
        </x-card>

        <x-card>
            <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Providers tracked') }}</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ is_array($providerStats ?? null) ? count($providerStats) : 0 }}</div>
            <div class="mt-2 text-xs text-gray-500 dark:text-gray-500">
                {{ __('ChatGPT, Gemini, Claude') }}
            </div>
        </x-card>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card>
            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Usage by provider') }}</div>

            <div class="mt-4 space-y-4">
                @foreach(($providerStats ?? []) as $key => $stat)
                    @php
                        $used = (int) ($stat['tokens_used'] ?? 0);
                        $limit = (int) ($stat['token_limit'] ?? 0);
                        $percent = $stat['percent'] ?? null;
                        $estimatedCostCents = (int) ($stat['estimated_cost_cents'] ?? 0);

                        $daily = is_array($providerDailyStats ?? null) ? ($providerDailyStats[$key] ?? null) : null;
                        $dailyUsed = (int) (($daily['tokens_used'] ?? null) ?? 0);
                        $dailyLimit = (int) (($daily['token_limit'] ?? null) ?? 0);
                    @endphp

                    <div>
                        <div class="flex items-center justify-between">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $stat['label'] ?? $key }}</div>
                            <div class="text-xs text-gray-600 dark:text-gray-400">
                                {{ number_format($used) }}
                                /
                                {{ $limit > 0 ? number_format($limit) : __('Unlimited') }}
                                ·
                                ${{ number_format($estimatedCostCents / 100, 2) }}
                            </div>
                        </div>

                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                            {{ __('Today:') }}
                            {{ number_format($dailyUsed) }}
                            /
                            {{ $dailyLimit > 0 ? number_format($dailyLimit) : __('Unlimited') }}
                        </div>

                        <div class="mt-2 h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                            @if(is_numeric($percent))
                                <div class="h-full bg-primary-500" style="width: {{ (float) $percent }}%"></div>
                            @else
                                <div class="h-full bg-primary-500" style="width: 0%"></div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>

        <x-card>
            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Breakdown by provider / model') }}</div>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Provider') }}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Model') }}</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Tokens') }}</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Est. Cost') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse(($breakdownRows ?? []) as $row)
                            @php
                                $tokens = (int) ($row['tokens'] ?? 0);
                                $costCents = (int) ($row['estimated_cost_cents'] ?? 0);
                            @endphp
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $row['provider_label'] ?? ($row['provider'] ?? '') }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">{{ $row['model'] ?? __('Default') }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 text-right">{{ number_format($tokens) }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 text-right">${{ number_format($costCents / 100, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-4 py-6 text-sm text-gray-500 dark:text-gray-400" colspan="4">{{ __('No AI usage yet for the selected range.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card>
            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Tokens by provider') }}</div>
            <div class="mt-4" style="height: 320px;">
                <canvas id="aiProviderChart"></canvas>
            </div>
        </x-card>

        <x-card>
            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Tokens by provider / model') }}</div>
            <div class="mt-4" style="height: 320px;">
                <canvas id="aiProviderModelChart"></canvas>
            </div>
        </x-card>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card>
            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Estimated cost by provider') }}</div>
            <div class="mt-4" style="height: 320px;">
                <canvas id="aiProviderCostChart"></canvas>
            </div>
        </x-card>

        <x-card>
            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Estimated cost by provider / model') }}</div>
            <div class="mt-4" style="height: 320px;">
                <canvas id="aiProviderModelCostChart"></canvas>
            </div>
        </x-card>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            const providerStats = @json($providerStats ?? []);
            const breakdownRows = @json($breakdownRows ?? []);

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

            const providerLabels = [];
            const providerTokens = [];
            const providerCost = [];

            Object.keys(providerStats || {}).forEach(function (key) {
                const stat = providerStats[key] || {};
                providerLabels.push(stat.label || key);
                providerTokens.push(Number(stat.tokens_used || 0));
                providerCost.push(Number(stat.estimated_cost_cents || 0) / 100);
            });

            const providerChartEl = document.getElementById('aiProviderChart');
            if (providerChartEl && typeof Chart !== 'undefined') {
                const ctx = providerChartEl.getContext('2d');
                if (ctx) {
                    const t0 = theme();
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: providerLabels,
                            datasets: [{
                                label: @json(__('Tokens')),
                                data: providerTokens,
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
                                    ticks: { color: t0.tickColor }
                                },
                                y: {
                                    grid: { color: t0.gridColor },
                                    ticks: { color: t0.tickColor },
                                    beginAtZero: true,
                                }
                            }
                        }
                    });

                    window.__aiProviderChart = Chart.getChart(providerChartEl);
                }
            }

            const pmLabels = [];
            const pmTokens = [];
            (Array.isArray(breakdownRows) ? breakdownRows : []).forEach(function (row) {
                const provider = row.provider_label || row.provider || '';
                const model = row.model || 'Default';
                pmLabels.push(String(provider) + ' / ' + String(model));
                pmTokens.push(Number(row.tokens || 0));
            });

            const providerModelChartEl = document.getElementById('aiProviderModelChart');
            if (providerModelChartEl && typeof Chart !== 'undefined') {
                const ctx = providerModelChartEl.getContext('2d');
                if (ctx) {
                    const t0 = theme();
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: pmLabels,
                            datasets: [{
                                label: @json(__('Tokens')),
                                data: pmTokens,
                                backgroundColor: 'rgba(16, 148, 137, 0.25)',
                                borderColor: '#109489',
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
                                    ticks: {
                                        color: t0.tickColor,
                                        maxRotation: 60,
                                        minRotation: 0,
                                    }
                                },
                                y: {
                                    grid: { color: t0.gridColor },
                                    ticks: { color: t0.tickColor },
                                    beginAtZero: true,
                                }
                            }
                        }
                    });

                    window.__aiProviderModelChart = Chart.getChart(providerModelChartEl);
                }
            }

            const providerCostChartEl = document.getElementById('aiProviderCostChart');
            if (providerCostChartEl && typeof Chart !== 'undefined') {
                const ctx = providerCostChartEl.getContext('2d');
                if (ctx) {
                    const t0 = theme();
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: providerLabels,
                            datasets: [{
                                label: @json(__('Cost ($)')),
                                data: providerCost,
                                backgroundColor: 'rgba(245, 158, 11, 0.25)',
                                borderColor: '#F59E0B',
                                borderWidth: 1,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: function (ctx) {
                                            const v = Number(ctx.raw || 0);
                                            return ' $' + v.toFixed(2);
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: { color: t0.gridColor },
                                    ticks: { color: t0.tickColor }
                                },
                                y: {
                                    grid: { color: t0.gridColor },
                                    ticks: {
                                        color: t0.tickColor,
                                        callback: function (v) {
                                            const n = Number(v || 0);
                                            return '$' + n;
                                        }
                                    },
                                    beginAtZero: true,
                                }
                            }
                        }
                    });

                    window.__aiProviderCostChart = Chart.getChart(providerCostChartEl);
                }
            }

            const pmCost = [];
            (Array.isArray(breakdownRows) ? breakdownRows : []).forEach(function (row) {
                pmCost.push(Number(row.estimated_cost_cents || 0) / 100);
            });

            const providerModelCostChartEl = document.getElementById('aiProviderModelCostChart');
            if (providerModelCostChartEl && typeof Chart !== 'undefined') {
                const ctx = providerModelCostChartEl.getContext('2d');
                if (ctx) {
                    const t0 = theme();
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: pmLabels,
                            datasets: [{
                                label: @json(__('Cost ($)')),
                                data: pmCost,
                                backgroundColor: 'rgba(168, 85, 247, 0.22)',
                                borderColor: '#A855F7',
                                borderWidth: 1,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: function (ctx) {
                                            const v = Number(ctx.raw || 0);
                                            return ' $' + v.toFixed(2);
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: { color: t0.gridColor },
                                    ticks: {
                                        color: t0.tickColor,
                                        maxRotation: 60,
                                        minRotation: 0,
                                    }
                                },
                                y: {
                                    grid: { color: t0.gridColor },
                                    ticks: {
                                        color: t0.tickColor,
                                        callback: function (v) {
                                            const n = Number(v || 0);
                                            return '$' + n;
                                        }
                                    },
                                    beginAtZero: true,
                                }
                            }
                        }
                    });

                    window.__aiProviderModelCostChart = Chart.getChart(providerModelCostChartEl);
                }
            }

            const applyTheme = () => {
                const t = theme();

                const c1 = window.__aiProviderChart;
                if (c1) {
                    c1.options.scales.x.grid.color = t.gridColor;
                    c1.options.scales.y.grid.color = t.gridColor;
                    c1.options.scales.x.ticks.color = t.tickColor;
                    c1.options.scales.y.ticks.color = t.tickColor;
                    c1.update('none');
                }

                const c2 = window.__aiProviderModelChart;
                if (c2) {
                    c2.options.scales.x.grid.color = t.gridColor;
                    c2.options.scales.y.grid.color = t.gridColor;
                    c2.options.scales.x.ticks.color = t.tickColor;
                    c2.options.scales.y.ticks.color = t.tickColor;
                    c2.update('none');
                }

                const c3 = window.__aiProviderCostChart;
                if (c3) {
                    c3.options.scales.x.grid.color = t.gridColor;
                    c3.options.scales.y.grid.color = t.gridColor;
                    c3.options.scales.x.ticks.color = t.tickColor;
                    c3.options.scales.y.ticks.color = t.tickColor;
                    c3.update('none');
                }

                const c4 = window.__aiProviderModelCostChart;
                if (c4) {
                    c4.options.scales.x.grid.color = t.gridColor;
                    c4.options.scales.y.grid.color = t.gridColor;
                    c4.options.scales.x.ticks.color = t.tickColor;
                    c4.options.scales.y.ticks.color = t.tickColor;
                    c4.update('none');
                }
            };

            const observer = new MutationObserver(applyTheme);
            observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
        })();
    </script>
@endpush
