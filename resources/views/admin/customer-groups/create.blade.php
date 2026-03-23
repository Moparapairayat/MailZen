@extends('layouts.admin')

@section('title', __('Create Customer Group'))
@section('page-title', __('Create Customer Group'))

@section('content')
<div class="max-w-7xl" x-data="{ activeTab: 'general' }">
    <form method="POST" action="{{ route('admin.customer-groups.store') }}">
        @csrf

        <div class="space-y-6">
            <!-- Basic Information -->
            <x-card title="{{ __('Basic Information') }}">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Name') }} *</label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name') }}"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                        >
                        @error('name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Description') }}</label>
                        <textarea
                            name="description"
                            id="description"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </x-card>

            <!-- Tabs Navigation -->
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
                    <button type="button" @click="activeTab = 'general'" :class="activeTab === 'general' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-primary-500 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
                        {{ __('General') }}
                    </button>
                    <button type="button" @click="activeTab = 'servers'" :class="activeTab === 'servers' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-primary-500 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
                        {{ __('Servers') }}
                    </button>
                    <button type="button" @click="activeTab = 'domains'" :class="activeTab === 'domains' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-primary-500 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
                        {{ __('Domains') }}
                    </button>
                    <button type="button" @click="activeTab = 'lists'" :class="activeTab === 'lists' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-primary-500 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
                        {{ __('Lists') }}
                    </button>
                    <button type="button" @click="activeTab = 'campaigns'" :class="activeTab === 'campaigns' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-primary-500 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
                        {{ __('Campaigns') }}
                    </button>
                    <button type="button" @click="activeTab = 'surveys'" :class="activeTab === 'surveys' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-primary-500 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
                        {{ __('Surveys') }}
                    </button>
                    <button type="button" @click="activeTab = 'quota'" :class="activeTab === 'quota' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-primary-500 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
                        {{ __('Quota') }}
                    </button>
                    <button type="button" @click="activeTab = 'other'" :class="activeTab === 'other' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-primary-500 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
                        {{ __('Other') }}
                    </button>
                </nav>
            </div>

            <!-- General Tab -->
            <div x-show="activeTab === 'general'">
                <x-card>
                    <div class="space-y-6">
                    <div>
                        <label for="general_group_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Group Name</label>
                        <input type="text" name="general[group_name]" id="general_group_name" value="{{ old('general.group_name') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    </div>

                    <div class="space-y-3">
                        <label class="flex items-center">
                            <input type="checkbox" name="general[show_articles_menu]" value="1" {{ old('general.show_articles_menu') ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Show Articles Menu</span>
                        </label>

                        <label class="flex items-center">
                            <input type="checkbox" name="general[mask_email_addresses]" value="1" {{ old('general.mask_email_addresses') ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Mask Email Addresses</span>
                        </label>
                    </div>

                    <div>
                        <label for="general_notification_frequency" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notification Frequency</label>
                        <select name="general[notification_frequency]" id="general_notification_frequency" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                            <option value="disabled" {{ old('general.notification_frequency') === 'disabled' ? 'selected' : '' }}>Disabled</option>
                            <option value="daily" {{ old('general.notification_frequency') === 'daily' ? 'selected' : '' }}>Daily</option>
                            <option value="weekly" {{ old('general.notification_frequency') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="monthly" {{ old('general.notification_frequency') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                        </select>
                    </div>

                    <div>
                        <label for="general_notification_message" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notification Message</label>
                        <textarea name="general[notification_message]" id="general_notification_message" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">{{ old('general.notification_message') }}</textarea>
                    </div>
                    </div>
                </x-card>
            </div>

            <!-- Servers Tab -->
            <div x-show="activeTab === 'servers'">
                <x-card>
                    <div class="space-y-6">
                    <div>
                        <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">Limits</h4>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="servers_limits_max_delivery_servers" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Delivery Servers</label>
                                <input type="number" name="servers[limits][max_delivery_servers]" id="servers_limits_max_delivery_servers" value="{{ old('servers.limits.max_delivery_servers', 0) }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                            </div>
                            <div>
                                <label for="servers_limits_max_bounce_servers" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Bounce Servers</label>
                                <input type="number" name="servers[limits][max_bounce_servers]" id="servers_limits_max_bounce_servers" value="{{ old('servers.limits.max_bounce_servers', 0) }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                            </div>
                            <div>
                                <label for="servers_limits_max_feedback_loop_servers" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max FBL Servers</label>
                                <input type="number" name="servers[limits][max_feedback_loop_servers]" id="servers_limits_max_feedback_loop_servers" value="{{ old('servers.limits.max_feedback_loop_servers', 0) }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                            </div>
                            <div>
                                <label for="servers_limits_max_email_box_monitors" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Email Box Monitors</label>
                                <input type="number" name="servers[limits][max_email_box_monitors]" id="servers_limits_max_email_box_monitors" value="{{ old('servers.limits.max_email_box_monitors', 0) }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">Permissions</h4>
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" name="servers[permissions][must_add_bounce_server]" value="1" {{ old('servers.permissions.must_add_bounce_server') ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Must Add Bounce Server</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="servers[permissions][can_add_delivery_servers]" value="1" {{ old('servers.permissions.can_add_delivery_servers') ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Can Add Delivery Servers</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="servers[permissions][can_add_bounce_servers]" value="1" {{ old('servers.permissions.can_add_bounce_servers') ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Can Add Bounce Servers</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="servers[permissions][can_select_delivery_servers_for_campaigns]" value="1" {{ old('servers.permissions.can_select_delivery_servers_for_campaigns') ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Can Select Delivery Servers for Campaigns</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="servers[permissions][can_use_system_servers]" value="1" {{ old('servers.permissions.can_use_system_servers') ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Can Use System Servers</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label for="servers_custom_headers" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Custom Headers</label>
                        <textarea name="servers[custom_headers]" id="servers_custom_headers" rows="4" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm font-mono text-xs">{{ old('servers.custom_headers') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">One header per line in format: Header-Name: Header-Value</p>
                    </div>
                    </div>
                </x-card>
            </div>

            <!-- Continue with other tabs... Due to length, I'll create a separate partial or continue in next response -->
            {{-- DOMAINS TAB --}}
            {{-- Tracking Domains Section --}}
            <div class="mt-8">
                <x-card title="Tracking Domains">
                    <div class="space-y-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Can manage tracking domains <span class="text-red-500">*</span>
                            </label>
                            <div x-data="{checked: {{ old('domains.tracking.can_manage', '0') == '1' ? 'true' : 'false' }}}">
                                <input type="hidden" name="domains[tracking][can_manage]" :value="checked ? 1 : 0">
                                <button type="button"
                                        @click="checked = !checked"
                                        :class="checked ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <span :class="checked ? 'translate-x-6 bg-white' : 'translate-x-1 bg-white'"
                                        class="inline-block h-4 w-4 transform rounded-full shadow transition-transform"></span>
                                </button>
                                <span class="ml-3 text-sm text-gray-700 dark:text-gray-300" x-text="checked ? 'Yes' : 'No'"></span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Can select tracking domains for delivery servers <span class="text-red-500">*</span>
                            </label>
                            <div x-data="{checked: {{ old('domains.tracking.can_select_for_servers', '0') == '1' ? 'true' : 'false' }}}">
                                <input type="hidden" name="domains[tracking][can_select_for_servers]" :value="checked ? 1 : 0">
                                <button type="button"
                                        @click="checked = !checked"
                                        :class="checked ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <span :class="checked ? 'translate-x-6 bg-white' : 'translate-x-1 bg-white'"
                                        class="inline-block h-4 w-4 transform rounded-full shadow transition-transform"></span>
                                </button>
                                <span class="ml-3 text-sm text-gray-700 dark:text-gray-300" x-text="checked ? 'Yes' : 'No'"></span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Can select tracking domains for campaigns <span class="text-red-500">*</span>
                            </label>
                            <div x-data="{checked: {{ old('domains.tracking.can_select_for_campaigns', '0') == '1' ? 'true' : 'false' }}}">
                                <input type="hidden" name="domains[tracking][can_select_for_campaigns]" :value="checked ? 1 : 0">
                                <button type="button"
                                        @click="checked = !checked"
                                        :class="checked ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <span :class="checked ? 'translate-x-6 bg-white' : 'translate-x-1 bg-white'"
                                        class="inline-block h-4 w-4 transform rounded-full shadow transition-transform"></span>
                                </button>
                                <span class="ml-3 text-sm text-gray-700 dark:text-gray-300" x-text="checked ? 'Yes' : 'No'"></span>
                            </div>
                        </div>
                        <div>
                            <label for="domains_tracking_max" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Max. tracking domains <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="number"
                                name="domains[tracking][max]"
                                id="domains_tracking_max"
                                value="{{ old('domains.tracking.max', 0) }}"
                                min="0"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                        </div>
                    </div>
                </x-card>
            </div>

            {{-- Sending Domains Section --}}
            <div class="mt-8">
                <x-card title="Sending Domains">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Can manage sending domains <span class="text-red-500">*</span>
                            </label>
                            <div x-data="{checked: {{ old('domains.sending.can_manage', '0') == '1' ? 'true' : 'false' }}}">
                                <input type="hidden" name="domains[sending][can_manage]" :value="checked ? 1 : 0">
                                <button type="button"
                                        @click="checked = !checked"
                                        :class="checked ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <span :class="checked ? 'translate-x-6 bg-white' : 'translate-x-1 bg-white'"
                                        class="inline-block h-4 w-4 transform rounded-full shadow transition-transform"></span>
                                </button>
                                <span class="ml-3 text-sm text-gray-700 dark:text-gray-300" x-text="checked ? 'Yes' : 'No'"></span>
                            </div>
                        </div>
                        <div>
                            <label for="domains_sending_max" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Max. sending domains <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="number"
                                name="domains[sending][max]"
                                id="domains_sending_max"
                                value="{{ old('domains.sending.max', 0) }}"
                                min="0"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                        </div>
                    </div>
                </x-card>
            </div>

            {{-- LISTS TAB --}}
            {{-- LISTS TAB --}}
            <div class="mt-8">
                <x-card title="Lists">
                    <div class="space-y-6">

                        {{-- Permission Toggles --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                            {{-- Can import subscribers --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Can import subscribers <span class="text-red-500">*</span>
                                </label>
                                <div x-data="{checked: {{ old('lists.can_import', '1') == '1' ? 'true' : 'false' }}}">
                                    <input type="hidden" name="lists[can_import]" :value="checked ? 1 : 0">
                                    <button type="button"
                                            @click="checked = !checked"
                                            :class="checked ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <span :class="checked ? 'translate-x-6 bg-white' : 'translate-x-1 bg-white'"
                                            class="inline-block h-4 w-4 transform rounded-full shadow transition-transform"></span>
                                    </button>
                                    <span class="ml-3 text-sm text-gray-700 dark:text-gray-300" x-text="checked ? 'Yes' : 'No'"></span>
                                </div>
                            </div>

                            {{-- Can export subscribers --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Can export subscribers <span class="text-red-500">*</span>
                                </label>
                                <div x-data="{checked: {{ old('lists.can_export', '1') == '1' ? 'true' : 'false' }}}">
                                    <input type="hidden" name="lists[can_export]" :value="checked ? 1 : 0">
                                    <button type="button"
                                            @click="checked = !checked"
                                            :class="checked ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <span :class="checked ? 'translate-x-6 bg-white' : 'translate-x-1 bg-white'"
                                            class="inline-block h-4 w-4 transform rounded-full shadow transition-transform"></span>
                                    </button>
                                    <span class="ml-3 text-sm text-gray-700 dark:text-gray-300" x-text="checked ? 'Yes' : 'No'"></span>
                                </div>
                            </div>

                            {{-- Can copy subscribers --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Can copy subscribers <span class="text-red-500">*</span>
                                </label>
                                <div x-data="{checked: {{ old('lists.can_copy', '1') == '1' ? 'true' : 'false' }}}">
                                    <input type="hidden" name="lists[can_copy]" :value="checked ? 1 : 0">
                                    <button type="button"
                                            @click="checked = !checked"
                                            :class="checked ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <span :class="checked ? 'translate-x-6 bg-white' : 'translate-x-1 bg-white'"
                                            class="inline-block h-4 w-4 transform rounded-full shadow transition-transform"></span>
                                    </button>
                                    <span class="ml-3 text-sm text-gray-700 dark:text-gray-300" x-text="checked ? 'Yes' : 'No'"></span>
                                </div>
                            </div>

                            {{-- Can delete own lists --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Can delete own lists <span class="text-red-500">*</span>
                                </label>
                                <div x-data="{checked: {{ old('lists.can_delete_lists', '1') == '1' ? 'true' : 'false' }}}">
                                    <input type="hidden" name="lists[can_delete_lists]" :value="checked ? 1 : 0">
                                    <button type="button"
                                            @click="checked = !checked"
                                            :class="checked ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <span :class="checked ? 'translate-x-6 bg-white' : 'translate-x-1 bg-white'"
                                            class="inline-block h-4 w-4 transform rounded-full shadow transition-transform"></span>
                                    </button>
                                    <span class="ml-3 text-sm text-gray-700 dark:text-gray-300" x-text="checked ? 'Yes' : 'No'"></span>
                                </div>
                            </div>
                            
                            {{-- Can delete own subscribers --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Can delete own subscribers <span class="text-red-500">*</span>
                                </label>
                                <div x-data="{checked: {{ old('lists.can_delete_subscribers', '1') == '1' ? 'true' : 'false' }}}">
                                    <input type="hidden" name="lists[can_delete_subscribers]" :value="checked ? 1 : 0">
                                    <button type="button"
                                            @click="checked = !checked"
                                            :class="checked ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <span :class="checked ? 'translate-x-6 bg-white' : 'translate-x-1 bg-white'"
                                            class="inline-block h-4 w-4 transform rounded-full shadow transition-transform"></span>
                                    </button>
                                    <span class="ml-3 text-sm text-gray-700 dark:text-gray-300" x-text="checked ? 'Yes' : 'No'"></span>
                                </div>
                            </div>

                            {{-- Can segment lists --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Can segment lists <span class="text-red-500">*</span>
                                </label>
                                <div x-data="{checked: {{ old('lists.can_segment', '1') == '1' ? 'true' : 'false' }}}">
                                    <input type="hidden" name="lists[can_segment]" :value="checked ? 1 : 0">
                                    <button type="button"
                                            @click="checked = !checked"
                                            :class="checked ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <span :class="checked ? 'translate-x-6 bg-white' : 'translate-x-1 bg-white'"
                                            class="inline-block h-4 w-4 transform rounded-full shadow transition-transform"></span>
                                    </button>
                                    <span class="ml-3 text-sm text-gray-700 dark:text-gray-300" x-text="checked ? 'Yes' : 'No'"></span>
                                </div>
                            </div>

                            {{-- Mark blacklisted as confirmed --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Mark blacklisted as confirmed <span class="text-red-500">*</span>
                                </label>
                                <div x-data="{checked: {{ old('lists.confirm_blacklisted', '0') == '1' ? 'true' : 'false' }}}">
                                    <input type="hidden" name="lists[confirm_blacklisted]" :value="checked ? 1 : 0">
                                    <button type="button"
                                            @click="checked = !checked"
                                            :class="checked ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <span :class="checked ? 'translate-x-6 bg-white' : 'translate-x-1 bg-white'"
                                            class="inline-block h-4 w-4 transform rounded-full shadow transition-transform"></span>
                                    </button>
                                    <span class="ml-3 text-sm text-gray-700 dark:text-gray-300" x-text="checked ? 'Yes' : 'No'"></span>
                                </div>
                            </div>

                            {{-- Use own blacklist --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Use own blacklist <span class="text-red-500">*</span>
                                </label>
                                <div x-data="{checked: {{ old('lists.own_blacklist', '0') == '1' ? 'true' : 'false' }}}">
                                    <input type="hidden" name="lists[own_blacklist]" :value="checked ? 1 : 0">
                                    <button type="button"
                                            @click="checked = !checked"
                                            :class="checked ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <span :class="checked ? 'translate-x-6 bg-white' : 'translate-x-1 bg-white'"
                                            class="inline-block h-4 w-4 transform rounded-full shadow transition-transform"></span>
                                    </button>
                                    <span class="ml-3 text-sm text-gray-700 dark:text-gray-300" x-text="checked ? 'Yes' : 'No'"></span>
                                </div>
                            </div>

                            {{-- Can edit own subscribers --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Can edit own subscribers <span class="text-red-500">*</span>
                                </label>
                                <div x-data="{checked: {{ old('lists.can_edit_subscribers', '1') == '1' ? 'true' : 'false' }}}">
                                    <input type="hidden" name="lists[can_edit_subscribers]" :value="checked ? 1 : 0">
                                    <button type="button"
                                            @click="checked = !checked"
                                            :class="checked ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <span :class="checked ? 'translate-x-6 bg-white' : 'translate-x-1 bg-white'"
                                            class="inline-block h-4 w-4 transform rounded-full shadow transition-transform"></span>
                                    </button>
                                    <span class="ml-3 text-sm text-gray-700 dark:text-gray-300" x-text="checked ? 'Yes' : 'No'"></span>
                                </div>
                            </div>

                            {{-- Subscriber profile update optin history --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Subscriber profile update optin history <span class="text-red-500">*</span>
                                </label>
                                <div x-data="{checked: {{ old('lists.profile_optin_history', '1') == '1' ? 'true' : 'false' }}}">
                                    <input type="hidden" name="lists[profile_optin_history]" :value="checked ? 1 : 0">
                                    <button type="button"
                                            @click="checked = !checked"
                                            :class="checked ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <span :class="checked ? 'translate-x-6 bg-white' : 'translate-x-1 bg-white'"
                                            class="inline-block h-4 w-4 transform rounded-full shadow transition-transform"></span>
                                    </button>
                                    <span class="ml-3 text-sm text-gray-700 dark:text-gray-300" x-text="checked ? 'Yes' : 'No'"></span>
                                </div>
                            </div>
                            
                            {{-- Can create list from filtered search results --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Can create list from filtered search results <span class="text-red-500">*</span>
                                </label>
                                <div x-data="{checked: {{ old('lists.can_create_from_search', '1') == '1' ? 'true' : 'false' }}}">
                                    <input type="hidden" name="lists[can_create_from_search]" :value="checked ? 1 : 0">
                                    <button type="button"
                                            @click="checked = !checked"
                                            :class="checked ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <span :class="checked ? 'translate-x-6 bg-white' : 'translate-x-1 bg-white'"
                                            class="inline-block h-4 w-4 transform rounded-full shadow transition-transform"></span>
                                    </button>
                                    <span class="ml-3 text-sm text-gray-700 dark:text-gray-300" x-text="checked ? 'Yes' : 'No'"></span>
                                </div>
                            </div>

                            {{-- Show 7 days subscribers activity --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Show 7 days subscribers activity <span class="text-red-500">*</span>
                                </label>
                                <div x-data="{checked: {{ old('lists.show_7days_activity', '1') == '1' ? 'true' : 'false' }}}">
                                    <input type="hidden" name="lists[show_7days_activity]" :value="checked ? 1 : 0">
                                    <button type="button"
                                            @click="checked = !checked"
                                            :class="checked ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <span :class="checked ? 'translate-x-6 bg-white' : 'translate-x-1 bg-white'"
                                            class="inline-block h-4 w-4 transform rounded-full shadow transition-transform"></span>
                                    </button>
                                    <span class="ml-3 text-sm text-gray-700 dark:text-gray-300" x-text="checked ? 'Yes' : 'No'"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Numeric inputs --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            {{-- Max. lists --}}
                            <div>
                                <label for="lists_max" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Max. lists <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="number"
                                    name="lists[max]"
                                    id="lists_max"
                                    value="{{ old('lists.max', -1) }}"
                                    min="-1"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                >
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Set -1 for unlimited</p>
                            </div>
                            {{-- Max. subscribers --}}
                            <div>
                                <label for="lists_max_subscribers" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Max. subscribers <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="number"
                                    name="lists[max_subscribers]"
                                    id="lists_max_subscribers"
                                    value="{{ old('lists.max_subscribers', -1) }}"
                                    min="-1"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                >
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Set -1 for unlimited</p>
                            </div>
                            {{-- Max. subscribers per list --}}
                            <div>
                                <label for="lists_max_per_list" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Max. subscribers per list <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="number"
                                    name="lists[max_per_list]"
                                    id="lists_max_per_list"
                                    value="{{ old('lists.max_per_list', -1) }}"
                                    min="-1"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                >
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Set -1 for unlimited</p>
                            </div>
                            {{-- Max. custom fields per list --}}
                            <div>
                                <label for="lists_max_custom_fields" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Max. custom fields per list <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="number"
                                    name="lists[max_custom_fields]"
                                    id="lists_max_custom_fields"
                                    value="{{ old('lists.max_custom_fields', -1) }}"
                                    min="-1"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                >
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Set -1 for unlimited</p>
                            </div>
                            {{-- Copy subscribers at once --}}
                            <div>
                                <label for="lists_copy_at_once" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Copy subscribers at once <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="number"
                                    name="lists[copy_at_once]"
                                    id="lists_copy_at_once"
                                    value="{{ old('lists.copy_at_once', 100) }}"
                                    min="1"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                >
                            </div>
                            {{-- Max. segment conditions --}}
                            <div>
                                <label for="lists_max_segment_conditions" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Max. segment conditions <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="number"
                                    name="lists[max_segment_conditions]"
                                    id="lists_max_segment_conditions"
                                    value="{{ old('lists.max_segment_conditions', 3) }}"
                                    min="0"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                >
                            </div>
                            {{-- Max. segment wait timeout --}}
                            <div>
                                <label for="lists_max_segment_timeout" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Max. segment wait timeout <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="number"
                                    name="lists[max_segment_wait_timeout]"
                                    id="lists_max_segment_timeout"
                                    value="{{ old('lists.max_segment_wait_timeout', 5) }}"
                                    min="0"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                >
                            </div>
                        </div>

                        {{-- Force opt-in/out, double opt-in --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mt-6">
                            {{-- Force the OPT-IN process --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Force the OPT-IN process
                                </label>
                                <div x-data="{checked: {{ old('lists.force_optin', '0') == '1' ? 'true' : 'false' }}}">
                                    <input type="hidden" name="lists[force_optin]" :value="checked ? 1 : 0">
                                    <button type="button"
                                            @click="checked = !checked"
                                            :class="checked ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <span :class="checked ? 'translate-x-6 bg-white' : 'translate-x-1 bg-white'"
                                              class="inline-block h-4 w-4 transform rounded-full shadow transition-transform"></span>
                                    </button>
                                </div>
                            </div>
                            {{-- Force the OPT-OUT process --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Force the OPT-OUT process
                                </label>
                                <div x-data="{checked: {{ old('lists.force_optout', '0') == '1' ? 'true' : 'false' }}}">
                                    <input type="hidden" name="lists[force_optout]" :value="checked ? 1 : 0">
                                    <button type="button"
                                            @click="checked = !checked"
                                            :class="checked ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <span :class="checked ? 'translate-x-6 bg-white' : 'translate-x-1 bg-white'"
                                              class="inline-block h-4 w-4 transform rounded-full shadow transition-transform"></span>
                                    </button>
                                </div>
                            </div>
                            {{-- Force double opt-in confirmation --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Force double opt-in confirmation
                                </label>
                                <div x-data="{checked: {{ old('lists.force_double_optin', '0') == '1' ? 'true' : 'false' }}}">
                                    <input type="hidden" name="lists[force_double_optin]" :value="checked ? 1 : 0">
                                    <button type="button"
                                            @click="checked = !checked"
                                            :class="checked ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <span :class="checked ? 'translate-x-6 bg-white' : 'translate-x-1 bg-white'"
                                              class="inline-block h-4 w-4 transform rounded-full shadow transition-transform"></span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Custom fields default visibility --}}
                        <div class="mt-6">
                            <label for="lists_custom_fields_visibility" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Custom fields default visibility
                            </label>
                            <select
                                name="lists[custom_fields_visibility]"
                                id="lists_custom_fields_visibility"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                                <option value="visible" {{ old('lists.custom_fields_visibility') == 'visible' ? 'selected' : '' }}>{{ __('Visible') }}</option>
                                <option value="hidden" {{ old('lists.custom_fields_visibility') == 'hidden' ? 'selected' : '' }}>{{ __('Hidden') }}</option>
                                <option value="" {{ old('lists.custom_fields_visibility') == '' ? 'selected' : '' }}>{{ __('Default') }}</option>
                            </select>
                        </div>
                    </div>
                </x-card>
            </div>
            
            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('admin.customer-groups.index') }}" class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100">
                    {{ __('Cancel') }}
                </a>
                <x-button type="submit" variant="primary">{{ __('Create Customer Group') }}</x-button>
            </div>
        </div>
    </form>
</div>
@endsection

