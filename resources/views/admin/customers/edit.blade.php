@extends('layouts.admin')

@section('title', 'Edit Customer')
@section('page-title', 'Edit Customer')

@section('content')
<div class="max-w-4xl">
    <x-card>
        <form method="POST" action="{{ route('admin.customers.update', $customer) }}">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Personal Information -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Personal Information</h3>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">First Name *</label>
                            <input
                                type="text"
                                name="first_name"
                                id="first_name"
                                value="{{ old('first_name', $customer->first_name) }}"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                            @error('first_name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Last Name *</label>
                            <input
                                type="text"
                                name="last_name"
                                id="last_name"
                                value="{{ old('last_name', $customer->last_name) }}"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                            @error('last_name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email *</label>
                            <input
                                type="email"
                                name="email"
                                id="email"
                                value="{{ old('email', $customer->email) }}"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                            @error('email')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password (leave blank to keep current)</label>
                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                            @error('password')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
                            <input
                                type="password"
                                name="password_confirmation"
                                id="password_confirmation"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                        </div>
                    </div>
                </div>

                <!-- Account Settings -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Account Settings</h3>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status *</label>
                            <select
                                name="status"
                                id="status"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                                <option value="pending" {{ old('status', $customer->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="active" {{ old('status', $customer->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $customer->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="suspended" {{ old('status', $customer->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="timezone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Timezone</label>
                            <input
                                type="text"
                                name="timezone"
                                id="timezone"
                                value="{{ old('timezone', $customer->timezone) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                            @error('timezone')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="language" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Language</label>
                            <input
                                type="text"
                                name="language"
                                id="language"
                                value="{{ old('language', $customer->language) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                            @error('language')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="currency" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Currency</label>
                            <input
                                type="text"
                                name="currency"
                                id="currency"
                                value="{{ old('currency', $customer->currency) }}"
                                maxlength="3"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                            @error('currency')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Quota & Limits -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Quota & Limits</h3>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="quota" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quota</label>
                            <input
                                type="number"
                                name="quota"
                                id="quota"
                                value="{{ old('quota', $customer->quota) }}"
                                step="0.01"
                                min="0"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                            @error('quota')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="max_lists" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Lists</label>
                            <input
                                type="number"
                                name="max_lists"
                                id="max_lists"
                                value="{{ old('max_lists', $customer->max_lists) }}"
                                min="0"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                            @error('max_lists')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="max_subscribers" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Subscribers</label>
                            <input
                                type="number"
                                name="max_subscribers"
                                id="max_subscribers"
                                value="{{ old('max_subscribers', $customer->max_subscribers) }}"
                                min="0"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                            @error('max_subscribers')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="max_campaigns" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Campaigns</label>
                            <input
                                type="number"
                                name="max_campaigns"
                                id="max_campaigns"
                                value="{{ old('max_campaigns', $customer->max_campaigns) }}"
                                min="0"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                            @error('max_campaigns')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="expires_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Expires At</label>
                            <input
                                type="date"
                                name="expires_at"
                                id="expires_at"
                                value="{{ old('expires_at', $customer->expires_at?->format('Y-m-d')) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                            @error('expires_at')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Company Information -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Company Information</h3>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="company_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company Name</label>
                            <input
                                type="text"
                                name="company_name"
                                id="company_name"
                                value="{{ old('company_name', $customer->company_name) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                            @error('company_name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
                            <input
                                type="text"
                                name="phone"
                                id="phone"
                                value="{{ old('phone', $customer->phone) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="country" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Country</label>
                            <input
                                type="text"
                                name="country"
                                id="country"
                                value="{{ old('country', $customer->country) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                            @error('country')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
                            <textarea
                                name="address"
                                id="address"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >{{ old('address', $customer->address) }}</textarea>
                            @error('address')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300">City</label>
                            <input
                                type="text"
                                name="city"
                                id="city"
                                value="{{ old('city', $customer->city) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                            @error('city')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="state" class="block text-sm font-medium text-gray-700 dark:text-gray-300">State</label>
                            <input
                                type="text"
                                name="state"
                                id="state"
                                value="{{ old('state', $customer->state) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                            @error('state')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="zip_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Zip Code</label>
                            <input
                                type="text"
                                name="zip_code"
                                id="zip_code"
                                value="{{ old('zip_code', $customer->zip_code) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                            @error('zip_code')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Billing Address (for tax)</h3>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="tax_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tax ID</label>
                            <input
                                type="text"
                                name="tax_id"
                                id="tax_id"
                                value="{{ old('tax_id', $customer->tax_id) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                            @error('tax_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="billing_address_line_1" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address line 1</label>
                            <input
                                type="text"
                                name="billing_address[address_line_1]"
                                id="billing_address_line_1"
                                value="{{ old('billing_address.address_line_1', data_get($customer->billing_address, 'address_line_1')) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                            @error('billing_address.address_line_1')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="billing_address_line_2" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address line 2</label>
                            <input
                                type="text"
                                name="billing_address[address_line_2]"
                                id="billing_address_line_2"
                                value="{{ old('billing_address.address_line_2', data_get($customer->billing_address, 'address_line_2')) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                            @error('billing_address.address_line_2')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="billing_city" class="block text-sm font-medium text-gray-700 dark:text-gray-300">City</label>
                            <input
                                type="text"
                                name="billing_address[city]"
                                id="billing_city"
                                value="{{ old('billing_address.city', data_get($customer->billing_address, 'city')) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                            @error('billing_address.city')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="billing_state" class="block text-sm font-medium text-gray-700 dark:text-gray-300">State</label>
                            <input
                                type="text"
                                name="billing_address[state]"
                                id="billing_state"
                                value="{{ old('billing_address.state', data_get($customer->billing_address, 'state')) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                            @error('billing_address.state')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="billing_postal_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Postal Code</label>
                            <input
                                type="text"
                                name="billing_address[postal_code]"
                                id="billing_postal_code"
                                value="{{ old('billing_address.postal_code', data_get($customer->billing_address, 'postal_code')) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                            @error('billing_address.postal_code')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="billing_country" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Country</label>
                            <input
                                type="text"
                                name="billing_address[country]"
                                id="billing_country"
                                value="{{ old('billing_address.country', data_get($customer->billing_address, 'country')) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                            @error('billing_address.country')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Customer Groups -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Customer Groups</h3>
                    <div class="space-y-2">
                        @forelse($customerGroups as $groupId => $groupName)
                            <label class="flex items-center">
                                <input
                                    type="checkbox"
                                    name="customer_group_ids[]"
                                    value="{{ $groupId }}"
                                    {{ in_array($groupId, old('customer_group_ids', $customer->customerGroups->pluck('id')->toArray())) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700"
                                >
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $groupName }}</span>
                            </label>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">No customer groups available.</p>
                        @endforelse
                    </div>
                    @error('customer_group_ids')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Allocated Delivery Servers -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Allocated Delivery Servers</h3>
                    <select
                        name="allocated_delivery_server_ids[]"
                        multiple
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >
                        @foreach(($deliveryServers ?? collect()) as $server)
                            @php
                                $serverId = (int) $server->id;
                                $selectedIds = old('allocated_delivery_server_ids', $allocatedDeliveryServerIds ?? []);
                                $isSelected = in_array($serverId, $selectedIds);
                                $ownerLabel = $server->customer ? ('Customer: ' . $server->customer->email) : 'System';
                            @endphp
                            <option value="{{ $serverId }}" {{ $isSelected ? 'selected' : '' }}>
                                {{ $server->name }} ({{ $ownerLabel }})
                            </option>
                        @endforeach
                    </select>
                    @error('allocated_delivery_server_ids')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    @error('allocated_delivery_server_ids.*')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">If set, this customer can only use these delivery servers (overrides group allocation).</p>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('admin.customers.index') }}" class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100">
                        Cancel
                    </a>
                    <x-button type="submit" variant="primary">Update Customer</x-button>
                </div>
            </div>
        </form>
    </x-card>
</div>
@endsection

