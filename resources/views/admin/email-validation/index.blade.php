@extends('layouts.admin')

@section('title', __('Email Validation'))
@section('page-title', __('Email Validation'))

@section('content')
<div class="space-y-6">
    <x-card title="{{ __('Email Validation') }}" subtitle="{{ __('Manage email validation settings via customer groups and guide customers to configure their tools.') }}">
        <div class="space-y-4">
            <div>
                <p class="text-sm text-admin-text-secondary">
                    {{ __('Email validation is configured per customer group. Customers run validations from their dashboard.') }}
                </p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row">
                @admincan('admin.customer_groups.access')
                    <x-button href="{{ route('admin.customer-groups.index') }}" variant="primary">{{ __('Manage Customer Groups') }}</x-button>
                @endadmincan
            </div>
        </div>
    </x-card>

    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <form method="GET" action="{{ route('admin.email-validation.index') }}" class="w-full lg:flex-1 lg:max-w-4xl flex flex-col gap-2 lg:flex-row">
            <input
                type="text"
                name="tool_search"
                value="{{ $toolSearch ?? '' }}"
                placeholder="{{ __('Search tools by name, provider, or customer...') }}"
                class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
            >
            <x-button type="submit" variant="primary" class="w-full lg:w-auto">{{ __('Filter Tools') }}</x-button>
        </form>

        @admincan('admin.email_validation.create')
            <div class="w-full lg:w-auto">
                <x-button href="{{ route('admin.email-validation.tools.create') }}" variant="primary" class="w-full lg:w-auto">{{ __('Add Tool') }}</x-button>
            </div>
        @endadmincan
    </div>

    <x-card :padding="false">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Tools') }}</div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Name') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Provider') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Customer') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Active') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Created') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($tools as $tool)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $tool->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $tool->provider }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <div>{{ $tool->customer?->full_name ?? __('Global') }}</div>
                                <div class="text-xs">
                                    @if($tool->customer_id !== null)
                                        {{ $tool->customer?->email ?? '—' }}
                                    @else
                                        @php
                                            $allowedGroupIds = (array) data_get($tool->meta ?? [], 'allowed_customer_group_ids', []);
                                            $allowedGroupIds = array_values(array_unique(array_filter(array_map('intval', $allowedGroupIds), fn ($id) => $id > 0)));
                                            $allowedGroupNames = array_values(array_filter(array_map(fn ($id) => $customerGroupNamesById[(int) $id] ?? null, $allowedGroupIds)));
                                        @endphp

                                        @if(empty($allowedGroupIds))
                                            {{ __('All customer groups') }}
                                        @elseif(!empty($allowedGroupNames))
                                            {{ implode(', ', $allowedGroupNames) }}
                                        @else
                                            —
                                        @endif
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $tool->active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                    {{ $tool->active ? __('Yes') : __('No') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ optional($tool->created_at)->format('M d, Y') }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                @if($tool->customer_id === null)
                                    <div class="flex items-center justify-end gap-2">
                                        @admincan('admin.email_validation.edit')
                                            <x-button href="{{ route('admin.email-validation.tools.edit', $tool) }}" variant="table" size="action" :pill="true">{{ __('Edit') }}</x-button>
                                        @endadmincan

                                        @admincan('admin.email_validation.delete')
                                            <form method="POST" action="{{ route('admin.email-validation.tools.destroy', $tool) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure?') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <x-button type="submit" variant="table-danger" size="action" :pill="true">{{ __('Delete') }}</x-button>
                                            </form>
                                        @endadmincan
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                {{ __('No tools found.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tools->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $tools->links() }}
            </div>
        @endif
    </x-card>

    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <form method="GET" action="{{ route('admin.email-validation.index') }}" class="w-full lg:flex-1 lg:max-w-5xl flex flex-col gap-2 lg:flex-row">
            <input type="hidden" name="tool_search" value="{{ $toolSearch ?? '' }}">
            <input
                type="text"
                name="run_search"
                value="{{ $runSearch ?? '' }}"
                placeholder="{{ __('Search runs by ID, customer, list, or tool...') }}"
                class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
            >
            <select
                name="run_status"
                class="w-full lg:w-auto rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
            >
                <option value="">{{ __('All Statuses') }}</option>
                <option value="pending" {{ ($runStatus ?? '') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                <option value="running" {{ ($runStatus ?? '') === 'running' ? 'selected' : '' }}>{{ __('Running') }}</option>
                <option value="completed" {{ ($runStatus ?? '') === 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                <option value="failed" {{ ($runStatus ?? '') === 'failed' ? 'selected' : '' }}>{{ __('Failed') }}</option>
            </select>
            <x-button type="submit" variant="primary" class="w-full lg:w-auto">{{ __('Filter Runs') }}</x-button>
        </form>
    </div>

    <x-card :padding="false">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Runs') }}</div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Run') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Customer') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('List') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Tool') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Progress') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Created') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($runs as $run)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                #{{ $run->id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <div>{{ $run->customer?->full_name ?? '—' }}</div>
                                <div class="text-xs">{{ $run->customer?->email ?? '—' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $run->list?->name ?? '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <div>{{ $run->tool?->name ?? '—' }}</div>
                                <div class="text-xs">{{ $run->tool?->provider ?? '—' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                        'running' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                        'completed' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                        'failed' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                    ];
                                @endphp
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$run->status] ?? 'bg-white/10 text-admin-text-secondary' }}">
                                    {{ __(ucfirst($run->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ number_format((int) $run->processed_count) }} / {{ number_format((int) $run->total_emails) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ optional($run->created_at)->format('M d, Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                {{ __('No runs found.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($runs->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $runs->links() }}
            </div>
        @endif
    </x-card>
</div>
@endsection
