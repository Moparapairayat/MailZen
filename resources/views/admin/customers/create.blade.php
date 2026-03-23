@extends('layouts.admin')

@section('title', 'Create Customer')
@section('page-title', 'Create Customer')

@section('content')
<div class="max-w-7xl" x-data="{
    activeTab: 'profile',
    ipRestrictions: {{ json_encode(old('security.ip_restrictions', [])) }},
    autoTaggingRules: {{ json_encode(old('automation.auto_tagging_rules', [])) }},
    addIpRestriction() {
        this.ipRestrictions.push('');
    },
    removeIpRestriction(index) {
        this.ipRestrictions.splice(index, 1);
    },
    addAutoTaggingRule() {
        this.autoTaggingRules.push({ trigger: '', tag: '' });
    },
    removeAutoTaggingRule(index) {
        this.autoTaggingRules.splice(index, 1);
    }
}">
    <form method="POST" action="{{ route('admin.customers.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="space-y-6">
            <!-- Basic Information -->
            <x-card title="Basic Information">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">First Name *</label>
                        <input
                            type="text"
                            name="first_name"
                            id="first_name"
                            value="{{ old('first_name') }}"
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
                            value="{{ old('last_name') }}"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                        >
                        @error('last_name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email *</label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            value="{{ old('email') }}"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                        >
                        @error('email')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="confirm_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Email *</label>
                        <input
                            type="email"
                            name="confirm_email"
                            id="confirm_email"
                            value="{{ old('confirm_email') }}"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                        >
                        @error('confirm_email')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password *</label>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                        >
                        @error('password')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password *</label>
                        <input
                            type="password"
                            name="confirm_password"
                            id="confirm_password"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                        >
                        @error('confirm_password')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </x-card>

            <!-- Tabs Navigation -->
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
                    <button type="button" @click="activeTab = 'profile'" :class="activeTab === 'profile' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-primary-500 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
                        Profile
                    </button>
                    <button type="button" @click="activeTab = 'account'" :class="activeTab === 'account' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-primary-500 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
                        Account Settings
                    </button>
                    <button type="button" @click="activeTab = 'groups'" :class="activeTab === 'groups' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-primary-500 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
                        Groups & Roles
                    </button>
                    <button type="button" @click="activeTab = 'contact'" :class="activeTab === 'contact' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-primary-500 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
                        Contact Preferences
                    </button>
                    <button type="button" @click="activeTab = 'security'" :class="activeTab === 'security' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-primary-500 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
                        Security
                    </button>
                    <button type="button" @click="activeTab = 'limits'" :class="activeTab === 'limits' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-primary-500 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
                        Limits
                    </button>
                    <button type="button" @click="activeTab = 'automation'" :class="activeTab === 'automation' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-primary-500 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
                        Automation
                    </button>
                    <button type="button" @click="activeTab = 'billing'" :class="activeTab === 'billing' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-primary-500 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
                        Billing
                    </button>
                </nav>
            </div>

            <!-- Profile Tab -->
            <div x-show="activeTab === 'profile'">
                <x-card>
                    <div class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="timezone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Timezone</label>
                            <select name="timezone" id="timezone" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                <option value="">Select timezone</option>
                                @foreach($timezones as $tz)
                                    <option value="{{ $tz }}" {{ old('timezone', 'UTC') === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                                @endforeach
                            </select>
                            @error('timezone')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="language" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Language</label>
                            <select name="language" id="language" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                <option value="">Select language</option>
                                @foreach($languages as $code => $name)
                                    <option value="{{ $code }}" {{ old('language', 'en') === $code ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('language')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="birth_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Birth Date</label>
                            <input
                                type="date"
                                name="birth_date"
                                id="birth_date"
                                value="{{ old('birth_date') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                            @error('birth_date')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
                            <input
                                type="tel"
                                name="phone"
                                id="phone"
                                value="{{ old('phone') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="avatar" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Avatar</label>
                            <input
                                type="file"
                                name="avatar"
                                id="avatar"
                                accept="image/*"
                                class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-primary-900/50 dark:file:text-primary-300"
                            >
                            @error('avatar')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    </div>
                </x-card>
            </div>

            <!-- Account Settings Tab -->
            <div x-show="activeTab === 'account'">
                <x-card>
                    <div class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status *</label>
                            <select name="status" id="status" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                <option value="active" {{ old('status', 'pending') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="pending" {{ old('status', 'pending') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="parent_account" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Parent Account</label>
                            <input
                                type="text"
                                name="parent_account"
                                id="parent_account"
                                value="{{ old('parent_account') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                            @error('parent_account')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="auto_deactivate_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Auto Deactivate At</label>
                            <input
                                type="datetime-local"
                                name="auto_deactivate_at"
                                id="auto_deactivate_at"
                                value="{{ old('auto_deactivate_at') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                            @error('auto_deactivate_at')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center">
                            <input
                                type="checkbox"
                                name="send_details_via_email"
                                id="send_details_via_email"
                                value="1"
                                {{ old('send_details_via_email') ? 'checked' : '' }}
                                class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700"
                            >
                            <label for="send_details_via_email" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Send Details Via Email</label>
                            @error('send_details_via_email')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    </div>
                </x-card>
            </div>

            <!-- Groups & Roles Tab -->
            <div x-show="activeTab === 'groups'">
                <x-card>
                    <div class="space-y-6">
                    <div>
                        <label for="group_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer Group</label>
                        <select name="group_id" id="group_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                            <option value="">Select a group</option>
                            @foreach($customerGroups as $groupId => $groupName)
                                <option value="{{ $groupId }}" {{ old('group_id') == $groupId ? 'selected' : '' }}>{{ $groupName }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Assign customer to a permission or pricing group.</p>
                        @error('group_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Allocated Delivery Servers</label>
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

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Roles</label>
                        <div class="space-y-2">
                            @foreach(['viewer', 'sender', 'manager', 'admin'] as $role)
                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        name="roles[]"
                                        value="{{ $role }}"
                                        {{ in_array($role, old('roles', [])) ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700"
                                    >
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 capitalize">{{ $role }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Modern role-based access control (RBAC)</p>
                        @error('roles')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    </div>
                </x-card>
            </div>

            <!-- Contact Preferences Tab -->
            <div x-show="activeTab === 'contact'">
                <x-card>
                    <div class="space-y-6">
                    <div class="space-y-4">
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                name="marketing_emails"
                                value="1"
                                {{ old('marketing_emails', true) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700"
                            >
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Marketing Emails</span>
                        </label>

                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                name="transactional_emails"
                                value="1"
                                {{ old('transactional_emails', true) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700"
                            >
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Transactional Emails</span>
                        </label>

                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                name="sms_notifications"
                                value="1"
                                {{ old('sms_notifications') ? 'checked' : '' }}
                                class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700"
                            >
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">SMS Notifications</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Preferred Channels</label>
                        <div class="space-y-2">
                            @foreach(['email', 'sms', 'push', 'whatsapp'] as $channel)
                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        name="preferred_channels[]"
                                        value="{{ $channel }}"
                                        {{ in_array($channel, old('preferred_channels', [])) ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700"
                                    >
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 capitalize">{{ $channel }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('preferred_channels')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    </div>
                </x-card>
            </div>

            <!-- Security Tab -->
            <div x-show="activeTab === 'security'">
                <x-card>
                    <div class="space-y-6">
                    <div>
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                name="two_factor_auth_enabled"
                                value="1"
                                {{ old('two_factor_auth_enabled') ? 'checked' : '' }}
                                class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700"
                            >
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Enable Two-Factor Authentication</span>
                        </label>
                        @error('two_factor_auth_enabled')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="security_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Security Notes</label>
                        <textarea
                            name="security_notes"
                            id="security_notes"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                        >{{ old('security_notes') }}</textarea>
                        @error('security_notes')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">IP Restrictions</label>
                            <button type="button" @click="addIpRestriction()" class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400">+ Add IP</button>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Optional: restrict account access to specific IPs.</p>
                        <div class="space-y-2" x-show="ipRestrictions.length > 0">
                            <template x-for="(ip, index) in ipRestrictions" :key="index">
                                <div class="flex gap-2">
                                    <input
                                        type="text"
                                        :name="`security[ip_restrictions][${index}]`"
                                        x-model="ipRestrictions[index]"
                                        placeholder="e.g., 192.168.1.1"
                                        class="flex-1 rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                    >
                                    <button type="button" @click="removeIpRestriction(index)" class="px-3 py-2 text-sm text-red-600 hover:text-red-700 dark:text-red-400">Remove</button>
                                </div>
                            </template>
                        </div>
                        <p x-show="ipRestrictions.length === 0" class="text-sm text-gray-500 dark:text-gray-400">No IP restrictions added.</p>
                        @error('security.ip_restrictions')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    </div>
                </x-card>
            </div>

            <!-- Limits Tab -->
            <div x-show="activeTab === 'limits'">
                <x-card>
                    <div class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="monthly_sending_limit" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Monthly Sending Limit</label>
                            <input
                                type="number"
                                name="monthly_sending_limit"
                                id="monthly_sending_limit"
                                value="{{ old('monthly_sending_limit', 10000) }}"
                                min="0"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                            @error('monthly_sending_limit')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="daily_sending_limit" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Daily Sending Limit</label>
                            <input
                                type="number"
                                name="daily_sending_limit"
                                id="daily_sending_limit"
                                value="{{ old('daily_sending_limit', 2000) }}"
                                min="0"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                            @error('daily_sending_limit')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="max_lists" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Lists</label>
                            <input
                                type="number"
                                name="max_lists"
                                id="max_lists"
                                value="{{ old('max_lists', 10) }}"
                                min="0"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                            @error('max_lists')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="max_campaigns_per_day" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Campaigns Per Day</label>
                            <input
                                type="number"
                                name="max_campaigns_per_day"
                                id="max_campaigns_per_day"
                                value="{{ old('max_campaigns_per_day', 5) }}"
                                min="0"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                            @error('max_campaigns_per_day')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    </div>
                </x-card>
            </div>

            <!-- Automation Tab -->
            <div x-show="activeTab === 'automation'">
                <x-card>
                    <div class="space-y-6">
                    <div>
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                name="welcome_campaign"
                                value="1"
                                {{ old('welcome_campaign', true) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700"
                            >
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Automatically trigger welcome email/campaign</span>
                        </label>
                        @error('welcome_campaign')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Auto Tagging Rules</label>
                            <button type="button" @click="addAutoTaggingRule()" class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400">+ Add Rule</button>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Example: { 'trigger': 'created', 'tag': 'new-user' }</p>
                        <div class="space-y-3" x-show="autoTaggingRules.length > 0">
                            <template x-for="(rule, index) in autoTaggingRules" :key="index">
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 items-end">
                                    <div>
                                        <label :for="`auto_tagging_trigger_${index}`" class="block text-xs font-medium text-gray-700 dark:text-gray-300">Trigger</label>
                                        <input
                                            type="text"
                                            :name="`automation[auto_tagging_rules][${index}][trigger]`"
                                            :id="`auto_tagging_trigger_${index}`"
                                            x-model="autoTaggingRules[index].trigger"
                                            placeholder="e.g., created"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                        >
                                    </div>
                                    <div>
                                        <label :for="`auto_tagging_tag_${index}`" class="block text-xs font-medium text-gray-700 dark:text-gray-300">Tag</label>
                                        <input
                                            type="text"
                                            :name="`automation[auto_tagging_rules][${index}][tag]`"
                                            :id="`auto_tagging_tag_${index}`"
                                            x-model="autoTaggingRules[index].tag"
                                            placeholder="e.g., new-user"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                        >
                                    </div>
                                    <div>
                                        <button type="button" @click="removeAutoTaggingRule(index)" class="w-full px-3 py-2 text-sm text-red-600 hover:text-red-700 dark:text-red-400">Remove</button>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <p x-show="autoTaggingRules.length === 0" class="text-sm text-gray-500 dark:text-gray-400">No auto tagging rules added.</p>
                        @error('automation.auto_tagging_rules')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    </div>
                </x-card>
            </div>

            <!-- Billing Tab -->
            <div x-show="activeTab === 'billing'">
                <x-card>
                    <div class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="plan_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Plan</label>
                            <select name="plan_id" id="plan_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                <option value="">Select a plan</option>
                                @foreach($plans as $planId => $planName)
                                    <option value="{{ $planId }}" {{ old('plan_id') == $planId ? 'selected' : '' }}>{{ $planName }}</option>
                                @endforeach
                            </select>
                            @error('plan_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="renewal_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Renewal Type</label>
                            <select name="renewal_type" id="renewal_type" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                <option value="">Select renewal type</option>
                                <option value="monthly" {{ old('renewal_type') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="yearly" {{ old('renewal_type') === 'yearly' ? 'selected' : '' }}>Yearly</option>
                            </select>
                            @error('renewal_type')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="tax_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tax ID</label>
                            <input
                                type="text"
                                name="tax_id"
                                id="tax_id"
                                value="{{ old('tax_id') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                            @error('tax_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">Billing Address</h4>
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label for="billing_address_line_1" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address Line 1</label>
                                <input
                                    type="text"
                                    name="billing_address[address_line_1]"
                                    id="billing_address_line_1"
                                    value="{{ old('billing_address.address_line_1') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                >
                                @error('billing_address.address_line_1')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label for="billing_address_line_2" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address Line 2</label>
                                <input
                                    type="text"
                                    name="billing_address[address_line_2]"
                                    id="billing_address_line_2"
                                    value="{{ old('billing_address.address_line_2') }}"
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
                                    value="{{ old('billing_address.city') }}"
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
                                    value="{{ old('billing_address.state') }}"
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
                                    value="{{ old('billing_address.postal_code') }}"
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
                                    value="{{ old('billing_address.country') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                >
                                @error('billing_address.country')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    </div>
                </x-card>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('admin.customers.index') }}" class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100">
                    Cancel
                </a>
                <x-button type="submit" variant="primary">Create Customer</x-button>
            </div>
        </div>
    </form>
</div>
@endsection
