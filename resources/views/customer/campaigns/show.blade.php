@extends('layouts.customer')

@section('title', $campaign->name)
@section('page-title', $campaign->name)

@section('content')
<div class="space-y-6">
    @if(!empty($runPreflightIssues))
        <div class="p-4 bg-yellow-50 border border-yellow-200 text-yellow-900 rounded-lg dark:bg-yellow-900/20 dark:border-yellow-800 dark:text-yellow-100">
            <div class="flex items-start">
                <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold mb-1">Action required before you can run this campaign</h3>
                    <ul class="text-sm list-disc list-inside space-y-1">
                        @foreach($runPreflightIssues as $issue)
                            <li>{{ $issue }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif
    @if($campaign->status === 'failed' && $campaign->failure_reason)
        <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg dark:bg-red-900/50 dark:border-red-800 dark:text-red-200">
            <div class="flex items-start">
                <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold mb-1">Campaign Failed</h3>
                    <p class="text-sm">{{ $campaign->failure_reason }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- 1️⃣ Campaign Summary (TOP SECTION) -->
    <x-card>
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $campaign->name }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $campaign->subject }}</p>
                <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div>
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Status</div>
                        <div class="mt-1">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $campaign->status === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                {{ $campaign->status === 'running' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : '' }}
                                {{ $campaign->status === 'queued' ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200' : '' }}
                                {{ $campaign->status === 'paused' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                {{ $campaign->status === 'failed' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}
                                {{ $campaign->status === 'draft' || $campaign->status === 'scheduled' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' : '' }}
                            ">
                                {{ ucfirst($campaign->status) }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Sender</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $campaign->from_name }}<br>
                            <span class="text-gray-500">{{ $campaign->from_email }}</span>
                        </div>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Started At</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $campaign->started_at ? $campaign->started_at->format('M d, Y H:i') : 'Not started' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Completed At</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $campaign->finished_at ? $campaign->finished_at->format('M d, Y H:i') : '-' }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 ml-4">
                @if($campaign->isFailed() || $campaign->isCompleted())
                    @customercan('campaigns.permissions.can_start_campaigns')
                        <form method="POST" action="{{ route('customer.campaigns.rerun', $campaign) }}" class="inline" onsubmit="return confirm('This will reset the campaign to draft status. Are you sure?');">
                            @csrf
                            <x-button type="submit" variant="primary" size="sm">🔁 Rerun</x-button>
                        </form>
                    @endcustomercan
                @endif
                @if($campaign->canStart())
                    @customercan('campaigns.features.ab_testing')
                        <x-button href="{{ route('customer.campaigns.ab-test', $campaign) }}" variant="secondary" size="sm">A/B Test</x-button>
                    @endcustomercan
                    @customercan('campaigns.permissions.can_start_campaigns')
                        <form method="POST" action="{{ route('customer.campaigns.start', $campaign) }}" class="inline" onsubmit="return confirm('Are you sure you want to start this campaign?');">
                            @csrf
                            <x-button type="submit" variant="primary" size="sm">▶ Start</x-button>
                        </form>
                    @endcustomercan
                @endif
                @if($campaign->canPause())
                    @customercan('campaigns.permissions.can_start_campaigns')
                        <form method="POST" action="{{ route('customer.campaigns.pause', $campaign) }}" class="inline">
                            @csrf
                            <x-button type="submit" variant="warning" size="sm">⏸ Pause</x-button>
                        </form>
                    @endcustomercan
                @endif
                @if($campaign->canResume())
                    @customercan('campaigns.permissions.can_start_campaigns')
                        <form method="POST" action="{{ route('customer.campaigns.resume', $campaign) }}" class="inline">
                            @csrf
                            <x-button type="submit" variant="primary" size="sm">▶ Resume</x-button>
                        </form>
                    @endcustomercan
                @endif
                @if($campaign->hasAbTest() && ($campaign->isRunning() || $campaign->isCompleted()))
                    @customercan('campaigns.features.ab_testing')
                        <x-button href="{{ route('customer.campaigns.ab-test', $campaign) }}" variant="secondary" size="sm">A/B Results</x-button>
                    @endcustomercan
                @endif
                @customercan('campaigns.permissions.can_edit_campaigns')
                    <x-button href="{{ route('customer.campaigns.edit', $campaign) }}" variant="secondary" size="sm">Edit</x-button>
                @endcustomercan
            </div>
        </div>
    </x-card>

    <!-- 2️⃣ Core Performance Metrics (KPIs) -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Sent</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100" id="sentCount">
                {{ number_format($stats['sent_count']) }}
            </div>
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                of {{ number_format($stats['total_recipients']) }} total
            </div>
        </x-card>
        <x-card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Delivered</div>
            <div class="mt-1 text-2xl font-semibold text-green-600 dark:text-green-400" id="deliveredCount">
                {{ number_format($stats['delivered']) }}
            </div>
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                {{ number_format($stats['delivery_rate'], 1) }}% delivery rate
            </div>
        </x-card>
        <x-card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Open Rate</div>
            <div class="mt-1 text-2xl font-semibold text-blue-600 dark:text-blue-400" id="openRate">
                {{ number_format($stats['open_rate'], 1) }}%
            </div>
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                {{ number_format($stats['opened_count']) }} opens
            </div>
        </x-card>
        <x-card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Click Rate</div>
            <div class="mt-1 text-2xl font-semibold text-purple-600 dark:text-purple-400" id="clickRate">
                {{ number_format($stats['click_rate'], 1) }}%
            </div>
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                {{ number_format($stats['clicked_count']) }} clicks
            </div>
        </x-card>
        <x-card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Replies</div>
            <div class="mt-1 text-2xl font-semibold text-indigo-600 dark:text-indigo-400" id="repliedCount">
                {{ number_format($campaign->replied_count ?? 0) }}
            </div>
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                <a href="{{ route('customer.campaigns.replies', $campaign) }}" class="text-blue-600 hover:underline dark:text-blue-400">View replies</a>
            </div>
        </x-card>
    </div>

    <!-- Additional KPI Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Bounce Rate</div>
            <div class="mt-1 text-2xl font-semibold text-red-600 dark:text-red-400" id="bounceRate">
                {{ number_format($stats['bounce_rate'], 1) }}%
            </div>
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                {{ number_format($stats['bounced_count']) }} bounces
            </div>
        </x-card>
        <x-card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Failure Rate</div>
            <div class="mt-1 text-2xl font-semibold text-red-600 dark:text-red-400" id="failureRate">
                {{ number_format($stats['failure_rate'], 1) }}%
            </div>
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                {{ number_format($stats['failed_count']) }} failed
            </div>
        </x-card>
        <x-card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Unsubscribed</div>
            <div class="mt-1 text-2xl font-semibold text-orange-600 dark:text-orange-400" id="unsubscribedCount">
                {{ number_format($stats['unsubscribed_count']) }}
            </div>
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Unsubscribes
            </div>
        </x-card>
        <x-card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Complaints</div>
            <div class="mt-1 text-2xl font-semibold text-red-600 dark:text-red-400" id="complainedCount">
                {{ number_format($stats['complained_count']) }}
            </div>
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Spam complaints
            </div>
        </x-card>
    </div>

    <!-- 3️⃣ Delivery Progress (Live while running) -->
    @if($campaign->status === 'running' && $stats['total_recipients'] > 0)
    <x-card title="Delivery Progress">
        <div class="space-y-4">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300" id="progressText">
                        {{ number_format($stats['sent_count']) }} / {{ number_format($stats['total_recipients']) }} emails sent
                    </span>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300" id="progressPercentage">
                        {{ number_format(($stats['sent_count'] / $stats['total_recipients']) * 100, 1) }}%
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-4 dark:bg-gray-700">
                    <div class="bg-blue-600 h-4 rounded-full transition-all duration-300" id="progressBar" style="width: {{ ($stats['sent_count'] / $stats['total_recipients']) * 100 }}%"></div>
                </div>
            </div>
            <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                <span>📊 Sending speed: <strong id="sendingSpeed">{{ number_format($stats['sending_speed'], 2) }}</strong> emails/sec</span>
                <span>⏱️ Started: {{ $campaign->started_at->diffForHumans() }}</span>
            </div>
        </div>
    </x-card>
    @endif

    <!-- 4️⃣ Recipient Status Breakdown -->
    <x-card title="Recipient Status Breakdown">
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
            @php
                $statusLabels = [
                    'pending' => ['label' => 'Pending', 'color' => 'yellow'],
                    'sent' => ['label' => 'Sent', 'color' => 'blue'],
                    'opened' => ['label' => 'Opened', 'color' => 'green'],
                    'clicked' => ['label' => 'Clicked', 'color' => 'purple'],
                    'bounced' => ['label' => 'Bounced', 'color' => 'red'],
                    'failed' => ['label' => 'Failed', 'color' => 'red'],
                ];
            @endphp
            @foreach($statusLabels as $status => $info)
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-2xl font-bold text-{{ $info['color'] }}-600 dark:text-{{ $info['color'] }}-400" id="statusCount_{{ $status }}">
                        {{ number_format($stats['recipient_statuses'][$status] ?? 0) }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $info['label'] }}</div>
                </div>
            @endforeach
        </div>
    </x-card>

    <!-- 5️⃣ Engagement Details - Top Links -->
    @if($stats['top_links']->count() > 0)
    <x-card title="Top Clicked Links">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">URL</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Clicks</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($stats['top_links'] as $link)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                            <a href="{{ $link->url }}" target="_blank" class="text-blue-600 hover:underline dark:text-blue-400">
                                {{ \Illuminate\Support\Str::limit($link->url, 60) }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ number_format($link->clicks) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-card>
    @endif

    <!-- 6️⃣ Errors & Warnings -->
    @if($stats['failed_count'] > 0 || $stats['error_breakdown']->count() > 0)
    <x-card title="Errors & Warnings">
        <div class="space-y-4">
            @if($stats['failed_count'] > 0)
            <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-red-800 dark:text-red-200">
                            ✖ {{ number_format($stats['failed_count']) }} emails failed
                        </h3>
                        @if($stats['error_breakdown']->count() > 0)
                        <div class="mt-2 space-y-1">
                            @foreach($stats['error_breakdown'] as $error)
                            <p class="text-sm text-red-700 dark:text-red-300 break-words whitespace-pre-wrap">
                                <strong>{{ number_format($error->count) }}</strong> - {{ $error->failure_reason }}
                            </p>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            @if($stats['bounced_count'] > 0)
            <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <div>
                        <h3 class="text-sm font-semibold text-yellow-800 dark:text-yellow-200">
                            ⚠ {{ number_format($stats['bounced_count']) }} emails bounced
                        </h3>
                        <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                            Bounce rate: {{ number_format($stats['bounce_rate'], 1) }}%
                        </p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </x-card>
    @endif

    <!-- 7️⃣ Unsubscribes & Complaints -->
    @if($stats['unsubscribed_count'] > 0 || $stats['complained_count'] > 0)
    <x-card title="Unsubscribes & Complaints">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @if($stats['unsubscribed_count'] > 0)
            <div class="p-4 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-orange-600 dark:text-orange-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                    <div>
                        <div class="text-sm font-semibold text-orange-800 dark:text-orange-200">
                            {{ number_format($stats['unsubscribed_count']) }} Unsubscribed
                        </div>
                        <div class="text-xs text-orange-600 dark:text-orange-400 mt-1">
                            Users opted out of future emails
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if($stats['complained_count'] > 0)
            <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <div>
                        <div class="text-sm font-semibold text-red-800 dark:text-red-200">
                            🚨 {{ number_format($stats['complained_count']) }} Spam Complaints
                        </div>
                        <div class="text-xs text-red-600 dark:text-red-400 mt-1">
                            Users marked email as spam
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </x-card>
    @endif

    <!-- 10️⃣ Deliverability Indicators -->
    <x-card title="Deliverability Indicators">
        <div class="flex items-center gap-6">
            <div class="flex items-center gap-2">
                @if($stats['deliverability']['dkim'])
                    <span class="text-green-600 dark:text-green-400">✓</span>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">DKIM</span>
                @else
                    <span class="text-red-600 dark:text-red-400">✗</span>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">DKIM</span>
                @endif
            </div>
            <div class="flex items-center gap-2">
                @if($stats['deliverability']['spf'])
                    <span class="text-green-600 dark:text-green-400">✓</span>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">SPF</span>
                @else
                    <span class="text-red-600 dark:text-red-400">✗</span>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">SPF</span>
                @endif
            </div>
            <div class="flex items-center gap-2">
                @if($stats['deliverability']['dmarc'])
                    <span class="text-green-600 dark:text-green-400">✓</span>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">DMARC</span>
                @else
                    <span class="text-red-600 dark:text-red-400">✗</span>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">DMARC</span>
                @endif
            </div>
            <div class="flex items-center gap-2 ml-auto">
                @php
                    $bounceHealth = 'Good';
                    $bounceHealthColor = 'green';
                    if ($stats['bounce_rate'] > 5) {
                        $bounceHealth = 'Critical';
                        $bounceHealthColor = 'red';
                    } elseif ($stats['bounce_rate'] > 2) {
                        $bounceHealth = 'Warning';
                        $bounceHealthColor = 'yellow';
                    }
                @endphp
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Bounce Rate Health:</span>
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-{{ $bounceHealthColor }}-100 text-{{ $bounceHealthColor }}-800 dark:bg-{{ $bounceHealthColor }}-900 dark:text-{{ $bounceHealthColor }}-200">
                    {{ $bounceHealth }}
                </span>
            </div>
        </div>
    </x-card>

    <!-- 9️⃣ Per-Recipient Table Link -->
    <x-card>
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recipients</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">View detailed recipient status and engagement</p>
            </div>
            <x-button href="{{ route('customer.campaigns.recipients', $campaign) }}" variant="primary">
                View All Recipients
            </x-button>
        </div>
    </x-card>

    <!-- Campaign Details -->
    <x-card title="Campaign Details">
        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email List</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $campaign->emailList->name ?? 'No List' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Type</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($campaign->type) }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $campaign->created_at->format('M d, Y H:i') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Recipients</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ number_format($stats['total_recipients']) }}</dd>
            </div>
        </dl>
    </x-card>
</div>

@push('scripts')
<script>
    let refreshInterval;
    
    function updateCampaignStats() {
        fetch('{{ route('customer.campaigns.stats', $campaign) }}', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const stats = data.stats;
                
                // Update sent count
                if (document.getElementById('sentCount')) {
                    document.getElementById('sentCount').textContent = new Intl.NumberFormat().format(stats.sent_count);
                }
                
                // Update delivered
                if (document.getElementById('deliveredCount')) {
                    document.getElementById('deliveredCount').textContent = new Intl.NumberFormat().format(stats.delivered);
                }
                
                // Update progress bar
                if (stats.total_recipients > 0) {
                    const progress = (stats.sent_count / stats.total_recipients) * 100;
                    const progressBar = document.getElementById('progressBar');
                    const progressText = document.getElementById('progressText');
                    const progressPercentage = document.getElementById('progressPercentage');
                    
                    if (progressBar) {
                        progressBar.style.width = progress + '%';
                    }
                    if (progressText) {
                        progressText.textContent = new Intl.NumberFormat().format(stats.sent_count) + ' / ' + 
                            new Intl.NumberFormat().format(stats.total_recipients) + ' emails sent';
                    }
                    if (progressPercentage) {
                        progressPercentage.textContent = progress.toFixed(1) + '%';
                    }
                }
                
                // Update sending speed
                if (document.getElementById('sendingSpeed')) {
                    document.getElementById('sendingSpeed').textContent = stats.sending_speed.toFixed(2);
                }
                
                // Update rates
                const rateElements = {
                    'openRate': stats.open_rate,
                    'clickRate': stats.click_rate,
                    'bounceRate': stats.bounce_rate,
                    'failureRate': stats.failure_rate,
                };
                
                Object.keys(rateElements).forEach(id => {
                    const el = document.getElementById(id);
                    if (el) {
                        el.textContent = rateElements[id].toFixed(1) + '%';
                    }
                });
                
                // Update counts
                const countElements = {
                    'unsubscribedCount': stats.unsubscribed_count,
                    'complainedCount': stats.complained_count,
                };
                
                Object.keys(countElements).forEach(id => {
                    const el = document.getElementById(id);
                    if (el) {
                        el.textContent = new Intl.NumberFormat().format(countElements[id]);
                    }
                });
                
                // Update status counts
                if (stats.recipient_statuses) {
                    Object.keys(stats.recipient_statuses).forEach(status => {
                        const el = document.getElementById('statusCount_' + status);
                        if (el) {
                            el.textContent = new Intl.NumberFormat().format(stats.recipient_statuses[status] || 0);
                        }
                    });
                }
                
                // Stop refreshing if campaign is completed or failed
                if (stats.status === 'completed' || stats.status === 'failed' || stats.status === 'paused') {
                    clearInterval(refreshInterval);
                }
            }
        })
        .catch(error => {
            console.error('Error updating stats:', error);
        });
    }
    
    // Start auto-refresh every 3 seconds for running campaigns
    document.addEventListener('DOMContentLoaded', function() {
        if ('{{ $campaign->status }}' === 'running') {
            refreshInterval = setInterval(updateCampaignStats, 3000);
        }
    });
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
    });
</script>
@endpush
@endsection
