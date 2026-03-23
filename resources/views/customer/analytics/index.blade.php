@extends('layouts.customer')

@section('title', 'Analytics')
@section('page-title', 'Email Analytics')

@section('content')
<div class="space-y-6">
    @php
        $rangeLabel = ((int) $range === 30) ? 'Last 30 Days' : 'Last 7 Days';
        $deltas = $summary['deltas'] ?? [];

        $linePoints = collect($rateSeries ?? [])->map(function ($row, $i) {
            $x = (int) $i;
            $open = (float) ($row['open_rate'] ?? 0);
            $ct = (float) ($row['click_through'] ?? 0);
            $click = (float) ($row['click_rate'] ?? 0);
            return [
                'x' => $x,
                'day' => $row['day'] ?? null,
                'open_rate' => $open,
                'click_through' => $ct,
                'click_rate' => $click,
            ];
        })->values();

        $lineMax = max(1, (float) $linePoints->max(fn ($p) => max($p['open_rate'], $p['click_through'], $p['click_rate'])));

        $toSvgPath = function ($key) use ($linePoints, $lineMax) {
            if ($linePoints->count() <= 1) {
                return '';
            }
            $w = 100;
            $h = 100;
            $n = max(1, $linePoints->count() - 1);

            return $linePoints->map(function ($p, $idx) use ($key, $lineMax, $w, $h, $n) {
                $x = ($idx / $n) * $w;
                $y = $h - (($p[$key] / $lineMax) * $h);
                $cmd = $idx === 0 ? 'M' : 'L';
                return $cmd . number_format($x, 2, '.', '') . ' ' . number_format($y, 2, '.', '');
            })->implode(' ');
        };

        $pathOpen = $toSvgPath('open_rate');
        $pathClickThrough = $toSvgPath('click_through');
        $pathClick = $toSvgPath('click_rate');

        $deviceStats = collect($deviceStats ?? []);
        $deviceMax = max(1, (int) $deviceStats->max(fn ($r) => max((int) ($r['opened'] ?? 0), (int) ($r['clicked'] ?? 0))));
    @endphp

    <div class="flex items-center justify-between gap-3">
        <div></div>
        <form method="GET" action="{{ route('customer.analytics.index') }}" class="flex items-center gap-2">
            <select name="domain" class="h-9 rounded-md border border-admin-border bg-admin-sidebar px-3 pr-8 text-sm text-admin-text-primary focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">All Domains</option>
                @foreach($domains as $d)
                    <option value="{{ $d->id }}" @selected((int) $selectedDomainId === (int) $d->id)>{{ $d->domain }}</option>
                @endforeach
            </select>

            <select name="range" class="h-9 rounded-md border border-admin-border bg-admin-sidebar px-3 pr-8 text-sm text-admin-text-primary focus:outline-none focus:ring-2 focus:ring-primary-500" onchange="this.form.submit()">
                <option value="7" @selected((int) $range === 7)>Last 7 Days</option>
                <option value="30" @selected((int) $range === 30)>Last 30 Days</option>
            </select>
        </form>
    </div>

    <x-card :padding="false">
        <div class="grid grid-cols-1 divide-y divide-gray-200 dark:divide-admin-border sm:grid-cols-2 sm:divide-y-0 sm:divide-x lg:grid-cols-4">
            <div class="px-6 py-5">
                <div class="flex items-center justify-between">
                    <div class="text-sm font-medium text-admin-text-secondary">Sent</div>
                    <a href="{{ route('customer.campaigns.index') }}" class="text-admin-text-secondary hover:text-admin-text-primary" title="Open campaigns">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M21 14v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7"/></svg>
                    </a>
                </div>
                <div class="mt-2 text-3xl font-semibold text-admin-text-primary">{{ number_format((int) ($summary['total'] ?? 0)) }}</div>
                <div class="mt-1 text-xs text-admin-text-secondary">
                    {{ $rangeLabel }}
                    @php
                        $sentDelta = $deltas['sent'] ?? null;
                    @endphp
                    @if($sentDelta !== null)
                        <span class="ml-2 inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium {{ $sentDelta >= 0 ? 'bg-emerald-500/10 text-emerald-500' : 'bg-red-500/10 text-red-500' }}">
                            {{ $sentDelta >= 0 ? '+' : '' }}{{ number_format((float) $sentDelta, 2) }}%
                        </span>
                    @endif
                </div>
            </div>

            <div class="px-6 py-5">
                <div class="flex items-center justify-between">
                    <div class="text-sm font-medium text-admin-text-secondary">Open Rate</div>
                </div>
                <div class="mt-2 text-3xl font-semibold text-admin-text-primary">{{ number_format((float) ($summary['open_rate'] ?? 0), 2) }}%</div>
                <div class="mt-1 text-xs text-admin-text-secondary">
                    {{ number_format((int) ($summary['opened'] ?? 0)) }} Opened
                    @php
                        $openDelta = (float) ($deltas['open_rate'] ?? 0);
                    @endphp
                    <span class="ml-2 inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium {{ $openDelta >= 0 ? 'bg-emerald-500/10 text-emerald-500' : 'bg-red-500/10 text-red-500' }}">
                        {{ $openDelta >= 0 ? '+' : '' }}{{ number_format($openDelta, 2) }}%
                    </span>
                </div>
            </div>

            <div class="px-6 py-5">
                <div class="flex items-center justify-between">
                    <div class="text-sm font-medium text-admin-text-secondary">Click Rate</div>
                </div>
                <div class="mt-2 text-3xl font-semibold text-admin-text-primary">{{ number_format((float) ($summary['click_rate'] ?? 0), 2) }}%</div>
                <div class="mt-1 text-xs text-admin-text-secondary">
                    {{ number_format((int) ($summary['clicked'] ?? 0)) }} Clicked
                    @php
                        $clickDelta = (float) ($deltas['click_rate'] ?? 0);
                    @endphp
                    <span class="ml-2 inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium {{ $clickDelta >= 0 ? 'bg-emerald-500/10 text-emerald-500' : 'bg-red-500/10 text-red-500' }}">
                        {{ $clickDelta >= 0 ? '+' : '' }}{{ number_format($clickDelta, 2) }}%
                    </span>
                </div>
            </div>

            <div class="px-6 py-5">
                <div class="flex items-center justify-between">
                    <div class="text-sm font-medium text-admin-text-secondary">Click Through</div>
                </div>
                <div class="mt-2 text-3xl font-semibold text-admin-text-primary">{{ number_format((float) ($summary['click_through'] ?? 0), 2) }}%</div>
                <div class="mt-1 text-xs text-admin-text-secondary">
                    {{ number_format((int) ($summary['clicked'] ?? 0)) }} Click Through
                    @php
                        $ctDelta = (float) ($deltas['click_through'] ?? 0);
                    @endphp
                    <span class="ml-2 inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium {{ $ctDelta >= 0 ? 'bg-emerald-500/10 text-emerald-500' : 'bg-red-500/10 text-red-500' }}">
                        {{ $ctDelta >= 0 ? '+' : '' }}{{ number_format($ctDelta, 2) }}%
                    </span>
                </div>
            </div>
        </div>
    </x-card>

    <div class="flex items-center justify-between">
        <div class="text-sm font-semibold text-admin-text-primary">Delivery</div>
        <button type="button" class="text-xs font-medium text-primary-500 hover:text-primary-400">SAVE REPORT</button>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
        <x-card>
            <div class="text-xs font-medium text-admin-text-secondary">Delivered Rate</div>
            <div class="mt-2 text-2xl font-semibold text-admin-text-primary">{{ number_format((float) ($summary['delivery_rate'] ?? 0), 2) }}%</div>
            <div class="mt-1 text-xs text-admin-text-secondary">{{ number_format((int) ($summary['delivered'] ?? 0)) }} Delivered</div>
            <div class="mt-4 h-2 w-full rounded bg-white/5 overflow-hidden">
                <div class="h-full bg-emerald-500" style="width: {{ min(100, max(0, (float) ($summary['delivery_rate'] ?? 0))) }}%"></div>
            </div>
        </x-card>

        <x-card>
            <div class="text-xs font-medium text-admin-text-secondary">Hard Bounce Rate</div>
            <div class="mt-2 text-2xl font-semibold text-admin-text-primary">{{ number_format((float) ($summary['hard_bounce_rate'] ?? 0), 2) }}%</div>
            <div class="mt-1 text-xs text-admin-text-secondary">{{ number_format((int) ($summary['hard_bounced'] ?? 0)) }} Hard bounced</div>
            <div class="mt-4 h-2 w-full rounded bg-white/5 overflow-hidden">
                <div class="h-full bg-violet-500" style="width: {{ min(100, max(0, (float) ($summary['hard_bounce_rate'] ?? 0))) }}%"></div>
            </div>
        </x-card>

        <x-card>
            <div class="text-xs font-medium text-admin-text-secondary">Unsubscribed Rate</div>
            <div class="mt-2 text-2xl font-semibold text-admin-text-primary">{{ number_format((float) ($summary['unsubscribe_rate'] ?? 0), 2) }}%</div>
            <div class="mt-1 text-xs text-admin-text-secondary">{{ number_format((int) ($summary['unsubscribed'] ?? 0)) }} Unsubscribed</div>
            <div class="mt-4 h-2 w-full rounded bg-white/5 overflow-hidden">
                <div class="h-full bg-gray-400" style="width: {{ min(100, max(0, (float) ($summary['unsubscribe_rate'] ?? 0))) }}%"></div>
            </div>
        </x-card>

        <x-card>
            <div class="text-xs font-medium text-admin-text-secondary">Spam Report Rate</div>
            <div class="mt-2 text-2xl font-semibold text-admin-text-primary">{{ number_format((float) ($summary['spam_rate'] ?? 0), 2) }}%</div>
            <div class="mt-1 text-xs text-admin-text-secondary">{{ number_format((int) ($summary['complained'] ?? 0)) }} Reported</div>
            <div class="mt-4 h-2 w-full rounded bg-white/5 overflow-hidden">
                <div class="h-full bg-gray-300" style="width: {{ min(100, max(0, (float) ($summary['spam_rate'] ?? 0))) }}%"></div>
            </div>
        </x-card>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card>
            <div class="text-sm font-semibold text-admin-text-primary">Email Data Chart</div>
            <div class="mt-2 flex items-center gap-4 text-xs text-admin-text-secondary">
                <div class="flex items-center gap-2">
                    <span class="inline-block h-2 w-2 rounded-full bg-violet-500"></span>
                    <span>Click through rate</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-block h-2 w-2 rounded-full bg-indigo-500"></span>
                    <span>Open rate</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-block h-2 w-2 rounded-full bg-sky-500"></span>
                    <span>Click rate</span>
                </div>
            </div>

            <div class="mt-4" data-analytics-line-root data-points='@json($linePoints)'>
                <div class="relative w-full" style="height: 260px;">
                    <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="absolute inset-0 h-full w-full">
                        <path d="{{ $pathClickThrough }}" fill="none" stroke="rgb(139 92 246)" stroke-width="1.2" vector-effect="non-scaling-stroke" />
                        <path d="{{ $pathOpen }}" fill="none" stroke="rgb(99 102 241)" stroke-width="1.2" vector-effect="non-scaling-stroke" />
                        <path d="{{ $pathClick }}" fill="none" stroke="rgb(14 165 233)" stroke-width="1.2" vector-effect="non-scaling-stroke" />
                    </svg>

                    <div data-analytics-tooltip class="hidden absolute z-10 rounded-md border border-admin-border bg-admin-sidebar px-3 py-2 text-xs text-admin-text-primary shadow">
                        <div class="font-medium" data-analytics-tooltip-day></div>
                        <div class="mt-1 text-admin-text-secondary">
                            <span class="inline-block w-28">Open rate</span>
                            <span data-analytics-tooltip-open></span>
                        </div>
                        <div class="text-admin-text-secondary">
                            <span class="inline-block w-28">Click rate</span>
                            <span data-analytics-tooltip-click></span>
                        </div>
                        <div class="text-admin-text-secondary">
                            <span class="inline-block w-28">Click through</span>
                            <span data-analytics-tooltip-ct></span>
                        </div>
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-4 gap-2 text-[11px] text-admin-text-secondary">
                    @foreach(collect($rateSeries ?? [])->take(4) as $i => $row)
                        <div class="truncate">
                            {{ \Carbon\Carbon::parse($row['day'])->format('M d') }}
                        </div>
                    @endforeach
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="text-sm font-semibold text-admin-text-primary">Performance By Device Type</div>
            <div class="mt-2 flex items-center gap-4 text-xs text-admin-text-secondary">
                <div class="flex items-center gap-2">
                    <span class="inline-block h-2 w-2 rounded-full bg-indigo-500"></span>
                    <span>Opened</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-block h-2 w-2 rounded-full bg-indigo-200"></span>
                    <span>Clicks</span>
                </div>
            </div>

            <div class="mt-4 space-y-3">
                @foreach($deviceStats as $row)
                    @php
                        $openedCount = (int) ($row['opened'] ?? 0);
                        $clickedCount = (int) ($row['clicked'] ?? 0);
                        $openedW = round(($openedCount / $deviceMax) * 100, 2);
                        $clickedW = round(($clickedCount / $deviceMax) * 100, 2);
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-xs">
                            <div class="text-admin-text-secondary">{{ $row['label'] }}</div>
                            <div class="text-admin-text-secondary">{{ $openedCount }} Opened · {{ $clickedCount }} Clicks</div>
                        </div>
                        <div class="mt-2 flex items-center gap-2">
                            <div class="h-3 flex-1 rounded bg-white/5 overflow-hidden">
                                <div class="h-full bg-indigo-500" style="width: {{ $openedW }}%"></div>
                            </div>
                            <div class="h-3 flex-1 rounded bg-white/5 overflow-hidden">
                                <div class="h-full bg-indigo-200" style="width: {{ $clickedW }}%"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>
    </div>

    <x-card :padding="false">
        <div class="px-6 py-4 flex items-center justify-between gap-3">
            <div>
                <div class="text-sm font-semibold text-admin-text-primary">All Email Performance</div>
                <div class="mt-1 text-xs text-admin-text-secondary">Sent Emails</div>
            </div>
            <div class="flex items-center gap-2">
                <div class="relative">
                    <input type="text" placeholder="Search..." class="h-9 w-48 rounded-md border border-admin-border bg-admin-sidebar px-3 text-sm text-admin-text-primary focus:outline-none focus:ring-2 focus:ring-primary-500" />
                </div>
                <x-button type="button" variant="table" size="action" :pill="true">Manage Column</x-button>
                <x-button type="button" variant="primary" size="action" :pill="true">Export</x-button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-admin-border">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-admin-text-secondary uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-admin-text-secondary uppercase tracking-wider">Publish Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-admin-text-secondary uppercase tracking-wider">Sent</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-admin-text-secondary uppercase tracking-wider">Click-Through Rate</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-admin-text-secondary uppercase tracking-wider">Delivered Rate</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-admin-text-secondary uppercase tracking-wider">Unsubscribed Rate</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-admin-text-secondary uppercase tracking-wider">Spam Report Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-admin-border">
                    @forelse($campaigns as $c)
                        <tr class="hover:bg-white/5">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-admin-text-primary">{{ $c['name'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-text-secondary">
                                @php
                                    $publishedAt = $c['published_at'] ?? null;
                                @endphp
                                {{ $publishedAt ? \Carbon\Carbon::parse($publishedAt)->format('n/j/Y') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-text-secondary">{{ number_format((int) ($c['sent'] ?? 0)) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-text-secondary">{{ number_format((float) ($c['click_through'] ?? 0), 2) }}%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-text-secondary">{{ number_format((float) ($c['delivered_rate'] ?? 0), 2) }}%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-text-secondary">{{ number_format((float) ($c['unsubscribe_rate'] ?? 0), 2) }}%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-admin-text-secondary">{{ number_format((float) ($c['spam_rate'] ?? 0), 2) }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-sm text-admin-text-secondary">No campaigns found in this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($campaigns->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-admin-border">
                {{ $campaigns->links() }}
            </div>
        @endif
    </x-card>
</div>
@endsection

@push('scripts')
<script>
(() => {
  const root = document.querySelector('[data-analytics-line-root]');
  if (!root) return;

  const pointsRaw = root.getAttribute('data-points');
  if (!pointsRaw) return;

  let points = [];
  try { points = JSON.parse(pointsRaw) || []; } catch (e) { points = []; }
  if (!Array.isArray(points) || points.length === 0) return;

  const tooltip = root.querySelector('[data-analytics-tooltip]');
  const dayEl = root.querySelector('[data-analytics-tooltip-day]');
  const openEl = root.querySelector('[data-analytics-tooltip-open]');
  const clickEl = root.querySelector('[data-analytics-tooltip-click]');
  const ctEl = root.querySelector('[data-analytics-tooltip-ct]');

  const container = root.querySelector('.relative');
  if (!container || !tooltip) return;

  function showTooltip(p, clientX) {
    if (!p) return;
    dayEl.textContent = p.day || '';
    openEl.textContent = `${Number(p.open_rate || 0).toFixed(2)}%`;
    clickEl.textContent = `${Number(p.click_rate || 0).toFixed(2)}%`;
    ctEl.textContent = `${Number(p.click_through || 0).toFixed(2)}%`;

    const rect = container.getBoundingClientRect();
    const x = clientX - rect.left;
    tooltip.style.left = `${Math.min(Math.max(8, x + 12), rect.width - 170)}px`;
    tooltip.style.top = '8px';
    tooltip.classList.remove('hidden');
  }

  function hideTooltip() {
    tooltip.classList.add('hidden');
  }

  container.addEventListener('mousemove', (e) => {
    const rect = container.getBoundingClientRect();
    const t = (e.clientX - rect.left) / rect.width;
    const idx = Math.min(points.length - 1, Math.max(0, Math.round(t * (points.length - 1))));
    showTooltip(points[idx], e.clientX);
  });

  container.addEventListener('mouseleave', hideTooltip);
})();
</script>
@endpush
