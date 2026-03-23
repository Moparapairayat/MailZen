@extends('layouts.admin')

@section('title', __('Bounced Emails'))
@section('page-title', __('Bounced Emails'))

@section('content')
<div class="space-y-4">
    <x-card title="{{ __('Filters') }}">
        <form method="GET" action="{{ route('admin.bounced-emails.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div>
                <label class="block text-sm font-medium text-admin-text-secondary">{{ __('Email') }}</label>
                <input type="text" name="email" value="{{ $filters['email'] ?? '' }}" class="mt-1 block w-full rounded-lg border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70" placeholder="{{ __('user@example.com') }}">
            </div>

            <div>
                <label class="block text-sm font-medium text-admin-text-secondary">{{ __('Campaign') }}</label>
                <select name="campaign_id" class="mt-1 block w-full rounded-lg border-admin-border bg-white/5 text-admin-text-primary">
                    <option value="">{{ __('All') }}</option>
                    @foreach($campaigns as $campaign)
                        <option value="{{ $campaign->id }}" @selected(($filters['campaign_id'] ?? '') == $campaign->id)>
                            #{{ $campaign->id }} - {{ $campaign->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-admin-text-secondary">{{ __('List') }}</label>
                <select name="list_id" class="mt-1 block w-full rounded-lg border-admin-border bg-white/5 text-admin-text-primary">
                    <option value="">{{ __('All') }}</option>
                    @foreach($lists as $list)
                        <option value="{{ $list->id }}" @selected(($filters['list_id'] ?? '') == $list->id)>
                            #{{ $list->id }} - {{ $list->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-admin-text-secondary">{{ __('Bounce Type') }}</label>
                <select name="bounce_type" class="mt-1 block w-full rounded-lg border-admin-border bg-white/5 text-admin-text-primary">
                    <option value="">{{ __('All') }}</option>
                    <option value="hard" @selected(($filters['bounce_type'] ?? '') === 'hard')>{{ __('Hard') }}</option>
                    <option value="soft" @selected(($filters['bounce_type'] ?? '') === 'soft')>{{ __('Soft') }}</option>
                    <option value="unknown" @selected(($filters['bounce_type'] ?? '') === 'unknown')>{{ __('Unknown') }}</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <x-button type="submit" variant="primary">{{ __('Apply') }}</x-button>
                <x-button href="{{ route('admin.bounced-emails.index') }}" variant="secondary">{{ __('Reset') }}</x-button>
            </div>
        </form>
    </x-card>

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Email') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Type') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Code') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Reason') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Campaign') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('List') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Bounce Mailbox') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Last Bounced') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($bounces as $bounce)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                <a class="text-primary-600 hover:text-primary-700" href="{{ route('admin.bounced-emails.show', $bounce) }}">
                                    {{ $bounce->email }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $bounce->bounce_type === 'hard' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : ($bounce->bounce_type === 'soft' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300') }}">
                                    {{ __(strtoupper($bounce->bounce_type)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $bounce->bounce_code ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 max-w-md">
                                <div class="truncate" title="{{ $bounce->reason ?? '' }}">{{ $bounce->reason ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                @if($bounce->campaign)
                                    <a class="text-primary-600 hover:text-primary-700" href="{{ route('admin.campaigns.show', $bounce->campaign) }}">
                                        #{{ $bounce->campaign->id }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $bounce->emailList?->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                <div class="truncate" title="{{ ($bounce->bounce_server_username ?? '') . ' ' . ($bounce->bounce_server_mailbox ?? '') }}">
                                    {{ $bounce->bounce_server_username ?? '-' }} {{ $bounce->bounce_server_mailbox ? '(' . $bounce->bounce_server_mailbox . ')' : '' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $bounce->last_bounced_at?->format('M d, Y H:i') ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('No bounced emails found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $bounces->links() }}
        </div>
    </x-card>
</div>
@endsection
