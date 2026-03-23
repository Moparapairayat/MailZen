@extends('layouts.admin')

@section('title', __('View Customer Group'))
@section('page-title', __('View Customer Group'))

@section('content')
<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $customerGroup->name }}</h2>
            @if($customerGroup->description)
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $customerGroup->description }}</p>
            @endif
        </div>
        <div class="flex items-center gap-3">
            @if($customerGroup->is_system)
                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                    {{ __('System Group') }}
                </span>
            @else
                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                    {{ __('Custom Group') }}
                </span>
            @endif
            <a href="{{ route('admin.customer-groups.edit', $customerGroup) }}" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600">
                {{ __('Edit') }}
            </a>
            <a href="{{ route('admin.customer-groups.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                {{ __('Back') }}
            </a>
        </div>
    </div>

    <!-- Basic Information -->
    <x-card title="{{ __('Basic Information') }}">
        <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Name') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customerGroup->name }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Type') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    @if($customerGroup->is_system)
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">{{ __('System') }}</span>
                    @else
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">{{ __('Custom') }}</span>
                    @endif
                </dd>
            </div>
            @if($customerGroup->description)
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Description') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customerGroup->description }}</dd>
                </div>
            @endif
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Total Customers') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customerGroup->customers->count() }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Created At') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customerGroup->created_at->format('M d, Y H:i') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Updated At') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $customerGroup->updated_at->format('M d, Y H:i') }}</dd>
            </div>
        </dl>
    </x-card>

    <!-- Settings Overview -->
    @if($settings)
        <x-card title="{{ __('Settings Overview') }}">
            <div class="space-y-6" x-data="{ activeSection: 'general' }">
                <!-- Section Tabs -->
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
                        @if(isset($settings['general']))
                            <button type="button" @click="activeSection = 'general'" :class="activeSection === 'general' ? 'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm !border-primary-500 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm !border-transparent text-gray-500 hover:text-gray-700 hover:border-white-100 dark:text-gray-400 dark:hover:text-gray-300'">
                                {{ __('General') }}
                            </button>
                        @endif
                        @if(isset($settings['servers']))
                            <button type="button" @click="activeSection = 'servers'" :class="activeSection === 'servers' ? 'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm !border-primary-500 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm !border-transparent text-gray-500 hover:text-gray-700 hover:border-white-100 dark:text-gray-400 dark:hover:text-gray-300'">
                                {{ __('Servers') }}
                            </button>
                        @endif
                        @if(isset($settings['domains']))
                            <button type="button" @click="activeSection = 'domains'" :class="activeSection === 'domains' ? 'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm !border-primary-500 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm !border-transparent text-gray-500 hover:text-gray-700 hover:border-white-100 dark:text-gray-400 dark:hover:text-gray-300'">
                                {{ __('Domains') }}
                            </button>
                        @endif
                        @if(isset($settings['lists']))
                            <button type="button" @click="activeSection = 'lists'" :class="activeSection === 'lists' ? 'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm !border-primary-500 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm !border-transparent text-gray-500 hover:text-gray-700 hover:border-white-100 dark:text-gray-400 dark:hover:text-gray-300'">
                                {{ __('Lists') }}
                            </button>
                        @endif
                        @if(isset($settings['campaigns']))
                            <button type="button" @click="activeSection = 'campaigns'" :class="activeSection === 'campaigns' ? 'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm !border-primary-500 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm !border-transparent text-gray-500 hover:text-gray-700 hover:border-white-100 dark:text-gray-400 dark:hover:text-gray-300'">
                                {{ __('Campaigns') }}
                            </button>
                        @endif
                        @if(isset($settings['autoresponders']))
                            <button type="button" @click="activeSection = 'autoresponders'" :class="activeSection === 'autoresponders' ? 'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm !border-primary-500 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm !border-transparent text-gray-500 hover:text-gray-700 hover:border-white-100 dark:text-gray-400 dark:hover:text-gray-300'">
                                {{ __('Auto Responders') }}
                            </button>
                        @endif
                        {{-- @if(isset($settings['surveys']))
                            <button type="button" @click="activeSection = 'surveys'" :class="activeSection === 'surveys' ? 'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm !border-primary-500 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm !border-transparent text-gray-500 hover:text-gray-700 hover:border-white-100 dark:text-gray-400 dark:hover:text-gray-300'">
                                Surveys
                            </button>
                        @endif --}}
                        @if(isset($settings['sending_quota']))
                            <button type="button" @click="activeSection = 'quota'" :class="activeSection === 'quota' ? 'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm !border-primary-500 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm !border-transparent text-gray-500 hover:text-gray-700 hover:border-white-100 dark:text-gray-400 dark:hover:text-gray-300'">
                                {{ __('Quota') }}
                            </button>
                        @endif
                    </nav>
                </div>

                <!-- General Section -->
                @if(isset($settings['general']))
                    <div x-show="activeSection === 'general'">
                        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            @if(isset($settings['general']['group_name']))
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Group Name') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $settings['general']['group_name'] ?: '-' }}</dd>
                                </div>
                            @endif
                            @if(isset($settings['general']['show_articles_menu']))
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Show Articles Menu') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                        <span class="px-2 py-1 text-xs rounded-full {{ $settings['general']['show_articles_menu'] ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                            {{ $settings['general']['show_articles_menu'] ? __('Yes') : __('No') }}
                                        </span>
                                    </dd>
                                </div>
                            @endif
                            @if(isset($settings['general']['mask_email_addresses']))
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Mask Email Addresses') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                        <span class="px-2 py-1 text-xs rounded-full {{ $settings['general']['mask_email_addresses'] ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                            {{ $settings['general']['mask_email_addresses'] ? __('Yes') : __('No') }}
                                        </span>
                                    </dd>
                                </div>
                            @endif
                            @if(isset($settings['general']['notification_frequency']))
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Notification Frequency') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 capitalize">{{ $settings['general']['notification_frequency'] ? __($settings['general']['notification_frequency']) : __('Disabled') }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                @endif

                <!-- Domains Section -->
                @if(isset($settings['domains']))
                    <div x-show="activeSection === 'domains'">
                        <div class="space-y-6">
                            @if(isset($settings['domains']['tracking_domains']))
                                <div>
                                    <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('Tracking Domains') }}</h4>
                                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Can Manage') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                <span class="px-2 py-1 text-xs rounded-full {{ $settings['domains']['tracking_domains']['can_manage'] ?? false ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                                    {{ ($settings['domains']['tracking_domains']['can_manage'] ?? false) ? __('Yes') : __('No') }}
                                                </span>
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Select for Servers') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                <span class="px-2 py-1 text-xs rounded-full {{ $settings['domains']['tracking_domains']['select_for_servers'] ?? false ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                                    {{ ($settings['domains']['tracking_domains']['select_for_servers'] ?? false) ? __('Yes') : __('No') }}
                                                </span>
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Select for Campaigns') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                <span class="px-2 py-1 text-xs rounded-full {{ $settings['domains']['tracking_domains']['select_for_campaigns'] ?? false ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                                    {{ ($settings['domains']['tracking_domains']['select_for_campaigns'] ?? false) ? __('Yes') : __('No') }}
                                                </span>
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Max Tracking Domains') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $settings['domains']['tracking_domains']['max_tracking_domains'] ?? 0 }}</dd>
                                        </div>
                                    </dl>
                                </div>
                            @endif

                            @if(isset($settings['domains']['sending_domains']))
                                <div>
                                    <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('Sending Domains') }}</h4>
                                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Can Manage') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                <span class="px-2 py-1 text-xs rounded-full {{ $settings['domains']['sending_domains']['can_manage'] ?? false ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                                    {{ ($settings['domains']['sending_domains']['can_manage'] ?? false) ? __('Yes') : __('No') }}
                                                </span>
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Max Sending Domains') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $settings['domains']['sending_domains']['max_sending_domains'] ?? 0 }}</dd>
                                        </div>
                                    </dl>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Servers Section -->
                @if(isset($settings['servers']))
                    <div x-show="activeSection === 'servers'">
                        <div class="space-y-6">
                            @if(isset($settings['servers']['limits']))
                                <div>
                                    <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('Limits') }}</h4>
                                    <div class="space-y-6">
                                        <div>
                                            <h5 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('Delivery Servers') }}</h5>
                                            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                                <div>
                                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Max Delivery Servers') }}</dt>
                                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $settings['servers']['limits']['max_delivery_servers'] ?? 0 }}</dd>
                                                </div>
                                                <div>
                                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Max FBL Servers') }}</dt>
                                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $settings['servers']['limits']['max_feedback_loop_servers'] ?? 0 }}</dd>
                                                </div>
                                            </dl>
                                        </div>
                                        <div>
                                            <h5 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('Bounce Servers') }}</h5>
                                            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                                <div>
                                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Max Bounce Servers') }}</dt>
                                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $settings['servers']['limits']['max_bounce_servers'] ?? 0 }}</dd>
                                                </div>
                                                <div>
                                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Max Email Box Monitors') }}</dt>
                                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $settings['servers']['limits']['max_email_box_monitors'] ?? 0 }}</dd>
                                                </div>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if(isset($settings['servers']['permissions']))
                                <div>
                                    <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('Permissions') }}</h4>
                                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Must Add Bounce Server') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                <span class="px-2 py-1 text-xs rounded-full {{ ($settings['servers']['permissions']['must_add_bounce_server'] ?? false) ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                                    {{ ($settings['servers']['permissions']['must_add_bounce_server'] ?? false) ? __('Yes') : __('No') }}
                                                </span>
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Can Add Delivery Servers') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                <span class="px-2 py-1 text-xs rounded-full {{ ($settings['servers']['permissions']['can_add_delivery_servers'] ?? false) ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                                    {{ ($settings['servers']['permissions']['can_add_delivery_servers'] ?? false) ? __('Yes') : __('No') }}
                                                </span>
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Can Add Bounce Servers') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                <span class="px-2 py-1 text-xs rounded-full {{ ($settings['servers']['permissions']['can_add_bounce_servers'] ?? false) ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                                    {{ ($settings['servers']['permissions']['can_add_bounce_servers'] ?? false) ? __('Yes') : __('No') }}
                                                </span>
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Can Select Delivery Servers For Campaigns') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                <span class="px-2 py-1 text-xs rounded-full {{ ($settings['servers']['permissions']['can_select_delivery_servers_for_campaigns'] ?? false) ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                                    {{ ($settings['servers']['permissions']['can_select_delivery_servers_for_campaigns'] ?? false) ? __('Yes') : __('No') }}
                                                </span>
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Can Use System Servers') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                <span class="px-2 py-1 text-xs rounded-full {{ ($settings['servers']['permissions']['can_use_system_servers'] ?? false) ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                                    {{ ($settings['servers']['permissions']['can_use_system_servers'] ?? false) ? __('Yes') : __('No') }}
                                                </span>
                                            </dd>
                                        </div>
                                    </dl>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                @if(isset($settings['lists']))
                    <div x-show="activeSection === 'lists'">
                        <div class="space-y-6">
                            @if(isset($settings['lists']['limits']))
                                <div>
                                    <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('Email Lists') }}</h4>
                                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Max Number of Lists') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $settings['lists']['limits']['max_lists'] ?? 0 }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Max Subscribers Per List') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $settings['lists']['limits']['max_subscribers_per_list'] ?? 0 }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Max Forms Per List') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $settings['lists']['limits']['max_forms_per_list'] ?? 0 }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Max Subscribers (All Lists)') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $settings['lists']['limits']['max_subscribers'] ?? 0 }}</dd>
                                        </div>
                                    </dl>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                @if(isset($settings['campaigns']))
                    <div x-show="activeSection === 'campaigns'">
                        <div class="space-y-6">
                            @if(isset($settings['campaigns']['limits']))
                                <div>
                                    <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('Campaigns') }}</h4>
                                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Max Campaigns to Create') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $settings['campaigns']['limits']['max_campaigns'] ?? 0 }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Max Campaigns to Run') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $settings['campaigns']['limits']['max_active_campaigns'] ?? 0 }}</dd>
                                        </div>
                                    </dl>
                                </div>
                            @endif

                            @if(isset($settings['campaigns']['features']['ab_testing']))
                                <div>
                                    <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('Features') }}</h4>
                                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('A/B Testing') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                <span class="px-2 py-1 text-xs rounded-full {{ ($settings['campaigns']['features']['ab_testing'] ?? false) ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                                    {{ ($settings['campaigns']['features']['ab_testing'] ?? false) ? __('Yes') : __('No') }}
                                                </span>
                                            </dd>
                                        </div>
                                    </dl>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                @if(isset($settings['autoresponders']))
                    <div x-show="activeSection === 'autoresponders'">
                        <div class="space-y-6">
                            <div>
                                <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('Auto Responders') }}</h4>
                                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Enabled') }}</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            <span class="px-2 py-1 text-xs rounded-full {{ ($settings['autoresponders']['enabled'] ?? false) ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                                {{ ($settings['autoresponders']['enabled'] ?? false) ? __('Yes') : __('No') }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Number of Auto Responders to Create') }}</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $settings['autoresponders']['max_autoresponders'] ?? 0 }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>
                @endif

                @if(isset($settings['sending_quota']))
                    <div x-show="activeSection === 'quota'">
                        <div class="space-y-6">
                            <div>
                                <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('Email Sending Quota') }}</h4>
                                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Daily') }}</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $settings['sending_quota']['daily_quota'] ?? 0 }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Weekly') }}</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $settings['sending_quota']['weekly_quota'] ?? 0 }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Monthly') }}</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $settings['sending_quota']['monthly_quota'] ?? 0 }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Other sections can be added similarly -->
            </div>
        </x-card>
    @endif

    <!-- Customers List -->
    @if($customerGroup->customers->count() > 0)
        <x-card title="{{ __('Customers in this Group (:count)', ['count' => $customerGroup->customers->count()]) }}" :padding="false">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ __('Name') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ __('Email') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ __('Status') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ __('Actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($customerGroup->customers as $customer)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $customer->full_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $customer->email }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $customer->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                           ($customer->status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 
                                           ($customer->status === 'suspended' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 
                                           'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300')) }}">
                                        {{ __(ucfirst($customer->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <x-button href="{{ route('admin.customers.show', $customer) }}" variant="table" size="action" :pill="true">{{ __('View') }}</x-button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>
    @else
        <x-card title="{{ __('Customers') }}">
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No customers in this group yet.') }}</p>
        </x-card>
    @endif
</div>
@endsection

