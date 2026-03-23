@extends('layouts.customer')

@section('title', 'Delivery Servers')
@section('page-title', 'Delivery Servers')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between lg:gap-0">
        <div class="w-full lg:flex-1 lg:max-w-lg">
            <form method="GET" action="{{ route('customer.delivery-servers.index') }}" class="flex flex-col gap-2 lg:flex-row lg:items-center">
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search servers..." class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                <select name="type" class="block w-full lg:w-auto rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    <option value="">All Types</option>
                    <option value="smtp" {{ ($filters['type'] ?? '') === 'smtp' ? 'selected' : '' }}>SMTP</option>
                    <option value="sendmail" {{ ($filters['type'] ?? '') === 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                    <option value="zeptomail" {{ ($filters['type'] ?? '') === 'zeptomail' ? 'selected' : '' }}>ZeptoMail</option>
                    <option value="amazon-ses" {{ ($filters['type'] ?? '') === 'amazon-ses' ? 'selected' : '' }}>Amazon SES</option>
                    <option value="mailgun" {{ ($filters['type'] ?? '') === 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                    <option value="sendgrid" {{ ($filters['type'] ?? '') === 'sendgrid' ? 'selected' : '' }}>SendGrid</option>
                    <option value="postmark" {{ ($filters['type'] ?? '') === 'postmark' ? 'selected' : '' }}>Postmark</option>
                    <option value="sparkpost" {{ ($filters['type'] ?? '') === 'sparkpost' ? 'selected' : '' }}>SparkPost</option>
                </select>
                <select name="status" class="block w-full lg:w-auto rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    <option value="">All Statuses</option>
                    <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
                <x-button type="submit" variant="primary" class="w-full lg:w-auto">Search</x-button>
            </form>
        </div>
        @customercan('servers.permissions.can_create_delivery_servers')
            <x-button type="button" variant="primary" class="w-full lg:w-auto" @click="$dispatch('open-modal', 'choose-delivery-server-type')">Add Server</x-button>
        @endcustomercan
    </div>

    <x-modal name="choose-delivery-server-type" maxWidth="md">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Add Delivery Server</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Choose the type of delivery server you want to add.</p>

            <div class="mt-6 grid grid-cols-1 gap-3">
                <x-button href="{{ route('customer.delivery-servers.create', ['flow' => 'smtp']) }}" variant="primary">Add SMTP Server</x-button>
                <x-button href="{{ route('customer.delivery-servers.create', ['flow' => 'api']) }}" variant="secondary">Add API Server</x-button>
            </div>

            <div class="mt-6 flex items-center justify-end">
                <x-button type="button" variant="secondary" @click="$dispatch('close-modal', 'choose-delivery-server-type')">Cancel</x-button>
            </div>
        </div>
    </x-modal>

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Owner</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Hostname</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($deliveryServers as $server)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ $server->name }}{{ $server->customer_id ? '' : ' (System)' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $server->customer_id ? 'Mine' : 'System' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $server->type }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $server->hostname ?? '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $server->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ ucfirst($server->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <x-button href="{{ route('customer.delivery-servers.show', $server) }}" variant="table" size="action" :pill="true" class="p-2" title="View" aria-label="View"><x-lucide name="eye" class="h-4 w-4" /><span class="sr-only">View</span></x-button>
                                    @if($server->customer_id)
                                        @customercan('servers.permissions.can_edit_delivery_servers')
                                            <x-button href="{{ route('customer.delivery-servers.edit', $server) }}" variant="table" size="action" :pill="true" class="p-2" title="Edit" aria-label="Edit"><x-lucide name="pencil" class="h-4 w-4" /><span class="sr-only">Edit</span></x-button>
                                        @endcustomercan

                                        @customercan('servers.permissions.can_delete_delivery_servers')
                                            <form method="POST" action="{{ route('customer.delivery-servers.destroy', $server) }}" class="inline" onsubmit="return confirm('Are you sure?');">
                                                @csrf
                                                @method('DELETE')
                                                <x-button type="submit" variant="table-danger" size="action" :pill="true" class="p-2" title="Delete" aria-label="Delete"><x-lucide name="trash-2" class="h-4 w-4" /><span class="sr-only">Delete</span></x-button>
                                            </form>
                                        @endcustomercan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                No delivery servers found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($deliveryServers->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">{{ $deliveryServers->links() }}</div>
        @endif
    </x-card>
</div>
@endsection
