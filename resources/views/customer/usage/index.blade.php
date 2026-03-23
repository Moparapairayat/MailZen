@extends('layouts.customer')

@section('title', 'Usage')
@section('page-title', 'Usage')

@section('content')
<div class="space-y-6">
    @php
        $metricLabels = [
            'emails_sent_this_month' => 'Emails sent (this month)',
            'email_validation_emails_this_month' => 'Email validation',
            'lists_count' => 'Email Lists',
            'subscribers_count' => 'Subscribers',
            'campaigns_count' => 'Campaigns',
            'bounce_servers_count' => 'Bounce Servers',
            'tracking_domains_count' => 'Tracking Domains',
            'sending_domains_count' => 'Sending Domains',
            'ai_tokens_used' => 'AI tokens',
        ];

        $metricDescriptions = [
            'emails_sent_this_month' => 'Emails sent in the current month.',
            'email_validation_emails_this_month' => 'Emails validated in the current month.',
            'lists_count' => 'Total email lists you have created.',
            'subscribers_count' => 'Confirmed subscribers across all lists.',
            'campaigns_count' => 'Total campaigns you have created.',
            'bounce_servers_count' => 'Bounce servers you have added.',
            'tracking_domains_count' => 'Tracking domains you have added.',
            'sending_domains_count' => 'Sending domains you have added.',
            'ai_tokens_used' => 'AI tokens used under your plan limits (admin keys only).',
        ];

        $metricSubtitles = [
            'emails_sent_this_month' => 'Monthly usage',
            'email_validation_emails_this_month' => 'Monthly usage',
            'lists_count' => 'Count',
            'subscribers_count' => 'Count',
            'campaigns_count' => 'Count',
            'bounce_servers_count' => 'Count',
            'tracking_domains_count' => 'Count',
            'sending_domains_count' => 'Count',
            'ai_tokens_used' => 'Plan usage',
        ];
    @endphp
    <x-card>
        <h3 class="text-lg font-semibold mb-2">Current Plan</h3>
        @if($subscription)
            <div class="text-sm text-gray-700 dark:text-gray-300">
                {{ $subscription->plan_name }}
                <span class="text-gray-500">({{ ucfirst($subscription->status) }})</span>
            </div>
        @else
            <div class="text-sm text-gray-600 dark:text-gray-400">No subscription found.</div>
        @endif
    </x-card>

    @if(isset($accessBoxes) && is_array($accessBoxes) && count($accessBoxes) > 0)
        <div>
            <div class="mb-3">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Access</h3>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($accessBoxes as $box)
                    @php
                        $label = (string) ($box['label'] ?? '');
                        $hasAccess = (bool) ($box['has_access'] ?? false);
                    @endphp

                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5">
                        <div class="flex items-center justify-between gap-3">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $label }}</div>

                            @if($hasAccess)
                                <span class="inline-flex items-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 px-2.5 py-0.5 text-xs font-medium">Access</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300 px-2.5 py-0.5 text-xs font-medium">No access</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div>
        <div class="mb-3">
            <h3 class="text-lg font-semibold">Usage</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">Your usage is renewed every month</p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($usageWithLimits as $row)
                @php
                    $metric = $row['metric'];
                    $label = $metricLabels[$metric] ?? ucwords(str_replace('_', ' ', $metric));
                    $desc = $metricDescriptions[$metric] ?? '';
                    $subtitle = $metricSubtitles[$metric] ?? '';
                    $hasAccess = array_key_exists('has_access', $row) ? (bool) $row['has_access'] : true;
                    $current = (int) ($row['current'] ?? 0);
                    $limit = $row['limit'] ?? null;

                    $percent = null;
                    if ($hasAccess && $limit !== null && (int) $limit > 0) {
                        $percent = min(100, max(0, ($current / (int) $limit) * 100));
                    }
                @endphp

                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5" @if(is_string($desc) && $desc !== '') title="{{ $desc }}" @endif>
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <div class="h-10 w-10 rounded-full bg-primary-50 text-primary-600 dark:bg-primary-900/30 dark:text-primary-300 flex items-center justify-center">
                                @switch($metric)
                                    @case('emails_sent_this_month')
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="M22 6l-10 7L2 6"/><path d="M2 6v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6"/><path d="M2 6l10 7L22 6"/></svg>
                                        @break
                                    @case('email_validation_emails_this_month')
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/></svg>
                                        @break
                                    @case('lists_count')
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="M8 6h13"/><path d="M8 12h13"/><path d="M8 18h13"/><path d="M3 6h.01"/><path d="M3 12h.01"/><path d="M3 18h.01"/></svg>
                                        @break
                                    @case('subscribers_count')
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                        @break
                                    @case('campaigns_count')
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="m3 11 18-5-5 18-4-7-9-6z"/><path d="m10 14 2.5 2.5"/></svg>
                                        @break
                                    @case('bounce_servers_count')
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><rect x="3" y="4" width="18" height="6" rx="2"/><rect x="3" y="14" width="18" height="6" rx="2"/><path d="M7 7h.01"/><path d="M7 17h.01"/></svg>
                                        @break
                                    @case('tracking_domains_count')
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10Z"/></svg>
                                        @break
                                    @case('sending_domains_count')
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4Z"/></svg>
                                        @break
                                    @default
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M8 12h8"/></svg>
                                @endswitch
                            </div>

                            <div>
                                <div class="flex items-center gap-2">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $label }}</div>
                                </div>

                                @if($subtitle !== '')
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $subtitle }}</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        @if(!$hasAccess)
                            <div class="text-sm text-gray-600 dark:text-gray-400">You have no access here</div>
                            <div class="mt-3 h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700"></div>
                        @else
                            <div class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                {{ $current }}
                                <span class="text-base font-medium text-gray-600 dark:text-gray-400">
                                    of {{ $limit !== null ? $limit : 'Unlimited' }}
                                </span>
                            </div>

                            <div class="mt-3 h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                <div class="h-full bg-emerald-500" style="width: {{ $percent !== null ? $percent : 0 }}%"></div>
                            </div>

                            @if($percent !== null)
                                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400" title="You have used {{ number_format($percent, 1) }}% of your limit.">
                                    {{ number_format($percent, 1) }}% used
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @empty
                <x-card>
                    <div class="text-center text-sm text-gray-500 dark:text-gray-400">No usage logged for this period.</div>
                </x-card>
            @endforelse
        </div>
    </div>
</div>
@endsection
