@extends('layouts.admin')

@section('title', __('Delivery Servers'))
@section('page-title', __('Delivery Servers'))

@section('content')
<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex flex-col gap-4 mb-6 lg:flex-row lg:items-center lg:justify-between">
        <div>
            @admincan('admin.delivery_servers.test')
                <a href="{{ route('admin.delivery-servers.test') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Test SMTP/API') }}
                </a>
            @endadmincan
        </div>
        <div class="w-full lg:flex-1 lg:max-w-lg">
            <form method="GET" action="{{ route('admin.delivery-servers.index') }}" class="flex flex-col gap-2 lg:flex-row">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="{{ __('Search servers...') }}"
                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                >
                <select
                    name="type"
                    class="w-full lg:w-auto rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                >
                    <option value="">{{ __('All Types') }}</option>
                    <option value="smtp" {{ request('type') === 'smtp' ? 'selected' : '' }}>{{ __('SMTP') }}</option>
                    <option value="sendmail" {{ request('type') === 'sendmail' ? 'selected' : '' }}>{{ __('Sendmail') }}</option>
                    <option value="zeptomail" {{ request('type') === 'zeptomail' ? 'selected' : '' }}>{{ __('ZeptoMail') }}</option>
                    <option value="amazon-ses" {{ request('type') === 'amazon-ses' ? 'selected' : '' }}>{{ __('Amazon SES') }}</option>
                    <option value="mailgun" {{ request('type') === 'mailgun' ? 'selected' : '' }}>{{ __('Mailgun') }}</option>
                    <option value="sendgrid" {{ request('type') === 'sendgrid' ? 'selected' : '' }}>{{ __('SendGrid') }}</option>
                    <option value="postmark" {{ request('type') === 'postmark' ? 'selected' : '' }}>{{ __('Postmark') }}</option>
                    <option value="sparkpost" {{ request('type') === 'sparkpost' ? 'selected' : '' }}>{{ __('SparkPost') }}</option>
                </select>
                <select
                    name="status"
                    class="w-full lg:w-auto rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                >
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                </select>
                <x-button type="submit" variant="primary" class="w-full lg:w-auto">{{ __('Filter') }}</x-button>
            </form>
        </div>
        <x-button type="button" variant="primary" class="w-full lg:w-auto" @click="$dispatch('open-modal', 'choose-delivery-server-type')">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            {{ __('Add Server') }}
        </x-button>
    </div>

    <x-modal name="choose-delivery-server-type" maxWidth="md">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Add Delivery Server</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Choose the type of delivery server you want to add.</p>

            <div class="mt-6 grid grid-cols-1 gap-3">
                <x-button href="{{ route('admin.delivery-servers.create', ['flow' => 'smtp']) }}" variant="primary">Add SMTP Server</x-button>
                <x-button href="{{ route('admin.delivery-servers.create', ['flow' => 'api']) }}" variant="secondary">Add API Server</x-button>
            </div>

            <div class="mt-6 flex items-center justify-end">
                <x-button type="button" variant="secondary" @click="$dispatch('close-modal', 'choose-delivery-server-type')">Cancel</x-button>
            </div>
        </div>
    </x-modal>

    <!-- Servers Table -->
    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Name') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Type') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Hostname') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Quota') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @if(isset($systemSmtpServer) && $systemSmtpServer)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $systemSmtpServer->name }}</div>
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">{{ __('System') }}</span>
                                </div>
                                @if(!empty($systemSmtpServer->from_email))
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $systemSmtpServer->from_email }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    {{ strtoupper(str_replace('-', ' ', $systemSmtpServer->type)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ !empty($systemSmtpServer->hostname) ? $systemSmtpServer->hostname . ':' . ($systemSmtpServer->port ?? 587) : __('N/A') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    {{ __('Active') }}
                                </span>
                                <span class="ml-2 px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">{{ __('Locked') }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ __('Unlimited') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    @if(!empty($systemSmtpServer->id))
                                        <x-button href="{{ route('admin.delivery-servers.edit', $systemSmtpServer->id) }}" variant="table" size="action" :pill="true" class="p-2" title="{{ __('Edit') }}" aria-label="{{ __('Edit') }}"><x-lucide name="pencil" class="h-4 w-4" /><span class="sr-only">{{ __('Edit') }}</span></x-button>
                                    @endif
                                    <x-button href="{{ route('admin.delivery-servers.test') }}" variant="table" size="action" :pill="true">{{ __('Test') }}</x-button>
                                </div>
                            </td>
                        </tr>
                    @endif

                    @forelse($deliveryServers as $server)
                        @if(isset($systemSmtpServer) && $systemSmtpServer && !empty($systemSmtpServer->id) && (int) $server->id === (int) $systemSmtpServer->id)
                            @continue
                        @endif
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $server->name }}</div>
                                    @if($server->is_primary)
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">{{ __('Primary') }}</span>
                                    @endif
                                </div>
                                @if($server->from_email)
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $server->from_email }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    {{ strtoupper(str_replace('-', ' ', $server->type)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $server->hostname ? $server->hostname . ':' . $server->port : __('N/A') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $server->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : ($server->status === 'inactive' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200') }}">
                                    {{ __(ucfirst($server->status)) }}
                                </span>
                                @if($server->locked)
                                    <span class="ml-2 px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">{{ __('Locked') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                @if($server->monthly_quota > 0)
                                    {{ __(':count/month', ['count' => number_format($server->monthly_quota)]) }}
                                @else
                                    {{ __('Unlimited') }}
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <x-button href="{{ route('admin.delivery-servers.show', $server) }}" variant="table" size="action" :pill="true" class="p-2" title="{{ __('View') }}" aria-label="{{ __('View') }}"><x-lucide name="eye" class="h-4 w-4" /><span class="sr-only">{{ __('View') }}</span></x-button>
                                    @admincan('admin.delivery_servers.make_primary')
                                        @if(!$server->is_primary)
                                            <form method="POST" action="{{ route('admin.delivery-servers.make-primary', $server) }}" class="inline">
                                                @csrf
                                                <x-button type="submit" variant="table-info" size="action" :pill="true">{{ __('Make Primary') }}</x-button>
                                            </form>
                                        @endif
                                    @endadmincan
                                    <x-button href="{{ route('admin.delivery-servers.edit', $server) }}" variant="table" size="action" :pill="true" class="p-2" title="{{ __('Edit') }}" aria-label="{{ __('Edit') }}"><x-lucide name="pencil" class="h-4 w-4" /><span class="sr-only">{{ __('Edit') }}</span></x-button>
                                    <form method="POST" action="{{ route('admin.delivery-servers.destroy', $server) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure?') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <x-button type="submit" variant="table-danger" size="action" :pill="true" class="p-2" title="{{ __('Delete') }}" aria-label="{{ __('Delete') }}"><x-lucide name="trash-2" class="h-4 w-4" /><span class="sr-only">{{ __('Delete') }}</span></x-button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                {{ __('No delivery servers found.') }}
                                <a href="{{ route('admin.delivery-servers.create') }}" class="text-primary-600">{{ __('Add your first server') }}</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($deliveryServers->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $deliveryServers->links() }}
            </div>
        @endif
    </x-card>
</div>
@endsection

