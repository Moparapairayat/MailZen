@extends('layouts.customer')

@section('title', $warmup->name)
@section('page-title', $warmup->name)

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between lg:gap-0">
        <div>
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 text-sm font-semibold rounded-full 
                    {{ $warmup->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $warmup->status === 'paused' ? 'bg-yellow-100 text-yellow-800' : '' }}
                    {{ $warmup->status === 'completed' ? 'bg-blue-100 text-blue-800' : '' }}
                    {{ $warmup->status === 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                    {{ $warmup->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}">
                    {{ ucfirst($warmup->status) }}
                </span>
                @php $health = $warmup->getHealthScore(); @endphp
                <span class="px-3 py-1 text-sm font-semibold rounded-full 
                    {{ $health === 'excellent' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $health === 'good' ? 'bg-blue-100 text-blue-800' : '' }}
                    {{ $health === 'fair' ? 'bg-yellow-100 text-yellow-800' : '' }}
                    {{ $health === 'poor' ? 'bg-red-100 text-red-800' : '' }}">
                    Health: {{ ucfirst($health) }}
                </span>
            </div>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $warmup->from_email }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if($warmup->canStart())
                <form method="POST" action="{{ route('customer.warmups.start', $warmup) }}" class="inline">
                    @csrf
                    <x-button type="submit" variant="primary">Start Warmup</x-button>
                </form>
            @endif
            @if($warmup->canPause())
                <form method="POST" action="{{ route('customer.warmups.pause', $warmup) }}" class="inline">
                    @csrf
                    <x-button type="submit" variant="secondary">Pause</x-button>
                </form>
            @endif
            @if(!$warmup->isActive())
                <x-button href="{{ route('customer.warmups.edit', $warmup) }}" variant="secondary">Edit</x-button>
            @endif
            <x-button href="{{ route('customer.warmups.index') }}" variant="secondary">Back</x-button>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
        <x-card>
            <div class="text-center">
                <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($stats['total_sent']) }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Sent</div>
            </div>
        </x-card>
        <x-card>
            <div class="text-center">
                <div class="text-3xl font-bold text-green-600">{{ $stats['open_rate'] }}%</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Open Rate</div>
            </div>
        </x-card>
        <x-card>
            <div class="text-center">
                <div class="text-3xl font-bold text-blue-600">{{ $stats['click_rate'] }}%</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Click Rate</div>
            </div>
        </x-card>
        <x-card>
            <div class="text-center">
                <div class="text-3xl font-bold {{ $stats['bounce_rate'] > 2 ? 'text-red-600' : 'text-gray-900 dark:text-gray-100' }}">{{ $stats['bounce_rate'] }}%</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Bounce Rate</div>
            </div>
        </x-card>
    </div>

    <x-card>
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Warmup Progress</h3>
        <div class="space-y-4">
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500 dark:text-gray-400">Day {{ $stats['current_day'] }} of {{ $stats['total_days'] }}</span>
                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $stats['progress'] }}% Complete</span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4">
                <div class="bg-primary-600 h-4 rounded-full transition-all duration-500" style="width: {{ $stats['progress'] }}%"></div>
            </div>
            @if($warmup->isActive())
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Next send: <strong>{{ $warmup->getTodayTargetVolume() }} emails</strong> at {{ $warmup->send_time }} ({{ $warmup->timezone }})
                </p>
            @endif
        </div>
    </x-card>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card>
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Configuration</h3>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Delivery Server</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $warmup->deliveryServer?->name ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Email List</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $warmup->emailList?->name ?? 'Seed Emails' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Starting Volume</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $warmup->starting_volume }} emails/day</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Max Volume</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $warmup->max_volume }} emails/day</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Daily Increase</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ ($warmup->daily_increase_rate - 1) * 100 }}%</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Send Time</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $warmup->send_time }} ({{ $warmup->timezone }})</dd>
                </div>
                @if($warmup->started_at)
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Started</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $warmup->started_at->format('M d, Y') }}</dd>
                    </div>
                @endif
            </dl>
        </x-card>

        <x-card>
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Volume Schedule</h3>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                @for($day = 1; $day <= min($warmup->total_days, 14); $day++)
                    @php $volume = $warmup->calculateVolumeForDay($day); @endphp
                    <div class="flex items-center justify-between py-1 {{ $day <= $warmup->current_day ? 'opacity-50' : '' }}">
                        <span class="text-sm {{ $day === $warmup->current_day + 1 ? 'font-bold text-primary-600' : 'text-gray-500 dark:text-gray-400' }}">
                            Day {{ $day }}
                            @if($day === $warmup->current_day + 1 && $warmup->isActive())
                                <span class="text-xs text-primary-600">(Next)</span>
                            @endif
                        </span>
                        <div class="flex items-center gap-2">
                            <div class="w-24 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-primary-600 h-2 rounded-full" style="width: {{ min(100, ($volume / $warmup->max_volume) * 100) }}%"></div>
                            </div>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100 w-16 text-right">{{ number_format($volume) }}</span>
                        </div>
                    </div>
                @endfor
                @if($warmup->total_days > 14)
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center pt-2">... and {{ $warmup->total_days - 14 }} more days</p>
                @endif
            </div>
        </x-card>
    </div>

    <x-card :padding="false">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Daily Activity Log</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Day</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Target</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Sent</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Opened</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Bounced</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($warmup->logs as $log)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                Day {{ $log->day_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $log->send_date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ number_format($log->target_volume) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ number_format($log->sent_count) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ number_format($log->opened_count) }} ({{ $log->open_rate ?? 0 }}%)
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm {{ $log->bounced_count > 0 ? 'text-red-600' : 'text-gray-500 dark:text-gray-400' }}">
                                {{ number_format($log->bounced_count) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $log->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $log->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $log->status === 'pending' ? 'bg-gray-100 text-gray-800' : '' }}
                                    {{ $log->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ ucfirst(str_replace('_', ' ', $log->status)) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                No activity yet. Start the warmup to begin sending.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    @if(!$warmup->isActive() && !$warmup->isCompleted())
        <div class="flex justify-end">
            <form method="POST" action="{{ route('customer.warmups.destroy', $warmup) }}" onsubmit="return confirm('Are you sure you want to delete this warmup?');">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="danger">Delete Warmup</x-button>
            </form>
        </div>
    @endif
</div>
@endsection
