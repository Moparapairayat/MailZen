@extends('layouts.admin')

@section('title', __('Sending Domains'))
@section('page-title', __('Sending Domains'))

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between lg:gap-0">
        <div class="w-full lg:flex-1 lg:max-w-lg">
            <form method="GET" action="{{ route('admin.sending-domains.index') }}" class="flex flex-col gap-2 lg:flex-row lg:items-center">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="{{ __('Search domains...') }}"
                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                >
                <select
                    name="status"
                    class="w-full lg:w-auto rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                >
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>{{ __('Verified') }}</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                </select>
                <x-button type="submit" variant="primary" class="w-full lg:w-auto">{{ __('Search') }}</x-button>
            </form>
        </div>

        <div class="flex items-center justify-end">
            <x-button href="{{ route('admin.sending-domains.create') }}" variant="primary" class="w-full lg:w-auto">{{ __('Add Domain') }}</x-button>
        </div>
    </div>

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Domain') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Customer') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Verified') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($sendingDomains as $domain)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                <div class="flex items-center gap-2">
                                    <span>{{ $domain->domain }}</span>
                                    @if($domain->is_primary)
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">{{ __('Primary') }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                @if($domain->customer)
                                    <a href="{{ route('admin.customers.show', $domain->customer) }}" class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300">
                                        {{ $domain->customer->full_name ?? $domain->customer->email }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $domain->status === 'verified' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                    {{ __(ucfirst($domain->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $domain->verified_at ? $domain->verified_at->format('M d, Y') : __('Not verified') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <x-button href="{{ route('admin.sending-domains.show', $domain) }}" variant="table" size="action" :pill="true">{{ __('View') }}</x-button>
                                    @if(!$domain->is_primary)
                                        <form method="POST" action="{{ route('admin.sending-domains.make-primary', $domain) }}" class="inline">
                                            @csrf
                                            <x-button type="submit" variant="table-info" size="action" :pill="true">{{ __('Make Primary') }}</x-button>
                                        </form>
                                    @endif
                                    <x-button href="{{ route('admin.sending-domains.edit', $domain) }}" variant="table" size="action" :pill="true">{{ __('Edit') }}</x-button>
                                    <form method="POST" action="{{ route('admin.sending-domains.destroy', $domain) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure?') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <x-button type="submit" variant="table-danger" size="action" :pill="true">{{ __('Delete') }}</x-button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                {{ __('No sending domains found.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sendingDomains->hasPages())
            <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                {{ $sendingDomains->links() }}
            </div>
        @endif
    </x-card>
</div>
@endsection
