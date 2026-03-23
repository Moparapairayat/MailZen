@extends('layouts.customer')

@section('title', __('Affiliate'))
@section('page-title', __('Affiliate'))

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-2">
            <a href="{{ route('customer.affiliate.index') }}" class="px-3 py-2 rounded-lg text-sm border border-admin-border hover:bg-white/5 {{ request()->routeIs('customer.affiliate.index') ? 'bg-white/10 text-admin-text-primary' : 'text-admin-text-secondary' }}">{{ __('Home') }}</a>
            <a href="{{ route('customer.affiliate.payments') }}" class="px-3 py-2 rounded-lg text-sm border border-admin-border hover:bg-white/5 {{ request()->routeIs('customer.affiliate.payments') ? 'bg-white/10 text-admin-text-primary' : 'text-admin-text-secondary' }}">{{ __('Payouts') }}</a>
        </div>

        @if($affiliate)
            @php
                $referralUrl = url('/?ref=' . $affiliate->code);
            @endphp
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end">
                <div class="text-xs text-admin-text-secondary">
                    {{ __('Referral link') }}:
                    <span class="text-admin-text-primary font-medium" id="affiliateReferralLinkText">{{ $referralUrl }}</span>
                </div>
                <button
                    type="button"
                    class="px-3 py-2 rounded-lg text-sm border border-admin-border hover:bg-white/5 text-admin-text-secondary"
                    data-copy-text="{{ $referralUrl }}"
                    data-copy-btn
                >
                    {{ __('Copy link') }}
                </button>
                <span class="text-xs text-green-600 dark:text-green-400 hidden" id="affiliateCopyToast">{{ __('Copied') }}</span>
            </div>
        @endif
    </div>

    @if(!$affiliate)
        <x-card>
            <div class="text-sm text-gray-700 dark:text-gray-200">
                {{ __('You do not have an affiliate profile yet.') }}
            </div>
            <form method="POST" action="{{ route('customer.affiliate.apply') }}" class="mt-4">
                @csrf
                <x-button type="submit" variant="primary">{{ __('Become an Affiliate') }}</x-button>
            </form>
        </x-card>
    @else
        @php
            $selectedRange = $range ?? 'today';
        @endphp

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('customer.affiliate.index', ['range' => '1y']) }}" class="px-3 py-2 rounded-lg text-sm border border-admin-border hover:bg-white/5 {{ $selectedRange === '1y' ? 'bg-white/10 text-admin-text-primary' : 'text-admin-text-secondary' }}">{{ __('Last Year') }}</a>
            <a href="{{ route('customer.affiliate.index', ['range' => '30d']) }}" class="px-3 py-2 rounded-lg text-sm border border-admin-border hover:bg-white/5 {{ $selectedRange === '30d' ? 'bg-white/10 text-admin-text-primary' : 'text-admin-text-secondary' }}">{{ __('Last Month') }}</a>
            <a href="{{ route('customer.affiliate.index', ['range' => '7d']) }}" class="px-3 py-2 rounded-lg text-sm border border-admin-border hover:bg-white/5 {{ $selectedRange === '7d' ? 'bg-white/10 text-admin-text-primary' : 'text-admin-text-secondary' }}">{{ __('Last Week') }}</a>
            <a href="{{ route('customer.affiliate.index', ['range' => 'today']) }}" class="px-3 py-2 rounded-lg text-sm border border-admin-border hover:bg-white/5 {{ $selectedRange === 'today' ? 'bg-white/10 text-admin-text-primary' : 'text-admin-text-secondary' }}">{{ __('Today') }}</a>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <x-card>
                <div class="text-xs font-medium text-admin-text-secondary">{{ __('Active Referrals') }}</div>
                <div class="mt-2 text-3xl font-semibold text-admin-text-primary">{{ number_format((int) $activeReferrals) }}</div>
                <div class="mt-1 text-xs text-admin-text-secondary">{{ __('All time') }}</div>
            </x-card>

            <x-card>
                <div class="text-xs font-medium text-admin-text-secondary">{{ __('Commission Revenue') }}</div>
                <div class="mt-2 text-3xl font-semibold text-admin-text-primary">{{ number_format((float) $commissionRevenue, 2) }}</div>
                <div class="mt-1 text-xs text-admin-text-secondary">{{ __('All time') }}</div>
            </x-card>

            <x-card>
                <div class="text-xs font-medium text-admin-text-secondary">{{ __('Upcoming Payout') }}</div>
                <div class="mt-2 text-3xl font-semibold text-admin-text-primary">{{ number_format((float) $upcomingPayoutAmount, 2) }}</div>
                <div class="mt-1 text-xs text-admin-text-secondary">{{ __('Unpaid commissions') }}</div>
            </x-card>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <x-card title="{{ __('Commission Trend') }}">
                    <div class="mt-2" style="height: 280px;">
                        <canvas id="affiliateCommissionChart"></canvas>
                    </div>
                </x-card>
            </div>
            <div>
                <x-card title="{{ __('Recent Referrals') }}" :padding="false">
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($recentReferrals as $ref)
                            <div class="px-6 py-4 flex items-center justify-between">
                                <div class="min-w-0">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                        {{ $ref->referredCustomer?->full_name ?? __('Visitor') }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $ref->referred_at ? $ref->referred_at->format('Y-m-d') : optional($ref->created_at)->format('Y-m-d') }}
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $ref->referred_customer_id ? __('Signed up') : __('Visited') }}
                                </div>
                            </div>
                        @empty
                            <div class="px-6 py-6 text-sm text-gray-500 dark:text-gray-400">{{ __('No referrals yet.') }}</div>
                        @endforelse
                    </div>
                </x-card>
            </div>
        </div>
    @endif
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

            const el = document.getElementById('affiliateCommissionChart');
            if (!el || typeof Chart === 'undefined') {
                return;
            }

            const ctx = el.getContext('2d');
            if (!ctx) {
                return;
            }

            const t0 = theme();
            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: @json(__('Commission')),
                        data: values,
                        borderColor: '#1E5FEA',
                        backgroundColor: 'rgba(30, 95, 234, 0.20)',
                        tension: 0.35,
                        fill: true,
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

    <script>
        (function () {
            const btn = document.querySelector('[data-copy-btn]');
            const toast = document.getElementById('affiliateCopyToast');
            if (!btn) {
                return;
            }

            const copyText = async (text) => {
                try {
                    if (navigator.clipboard && window.isSecureContext) {
                        await navigator.clipboard.writeText(text);
                        return true;
                    }
                } catch (e) {
                }

                try {
                    const ta = document.createElement('textarea');
                    ta.value = text;
                    ta.setAttribute('readonly', '');
                    ta.style.position = 'fixed';
                    ta.style.top = '-1000px';
                    ta.style.left = '-1000px';
                    document.body.appendChild(ta);
                    ta.select();
                    const ok = document.execCommand('copy');
                    document.body.removeChild(ta);
                    return ok;
                } catch (e) {
                    return false;
                }
            };

            btn.addEventListener('click', async function () {
                const text = btn.getAttribute('data-copy-text') || '';
                const ok = await copyText(text);
                if (!toast) {
                    return;
                }
                if (ok) {
                    toast.classList.remove('hidden');
                    window.setTimeout(() => toast.classList.add('hidden'), 1200);
                }
            });
        })();
    </script>
@endpush
