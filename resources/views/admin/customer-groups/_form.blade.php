<div class="border-b border-gray-200 dark:border-gray-700">
    <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
        <button type="button" @click="activeTab = 'messages'" :class="activeTab === 'messages' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-primary-500 dark:!border-primary-400 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-transparent text-gray-500 hover:text-gray-700 hover:!border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
            {{ __('Messages') }}
        </button>
        <button type="button" @click="activeTab = 'email_lists'" :class="activeTab === 'email_lists' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-primary-500 dark:!border-primary-400 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-transparent text-gray-500 hover:text-gray-700 hover:!border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
            {{ __('Email Lists') }}
        </button>
        <button type="button" @click="activeTab = 'campaigns'" :class="activeTab === 'campaigns' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-primary-500 dark:!border-primary-400 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-transparent text-gray-500 hover:text-gray-700 hover:!border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
            {{ __('Campaigns') }}
        </button>
        <button type="button" @click="activeTab = 'autoresponders'" :class="activeTab === 'autoresponders' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-primary-500 dark:!border-primary-400 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-transparent text-gray-500 hover:text-gray-700 hover:!border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
            {{ __('Auto Responders') }}
        </button>
        <button type="button" @click="activeTab = 'automations'" :class="activeTab === 'automations' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-primary-500 dark:!border-primary-400 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-transparent text-gray-500 hover:text-gray-700 hover:!border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
            {{ __('Automations') }}
        </button>
        <button type="button" @click="activeTab = 'servers'" :class="activeTab === 'servers' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-primary-500 dark:!border-primary-400 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-transparent text-gray-500 hover:text-gray-700 hover:!border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
            {{ __('Servers') }}
        </button>
        <button type="button" @click="activeTab = 'tracking_domains'" :class="activeTab === 'tracking_domains' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-primary-500 dark:!border-primary-400 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-transparent text-gray-500 hover:text-gray-700 hover:!border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
            {{ __('Tracking Domain') }}
        </button>
        <button type="button" @click="activeTab = 'sending_domains'" :class="activeTab === 'sending_domains' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-primary-500 dark:!border-primary-400 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-transparent text-gray-500 hover:text-gray-700 hover:!border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
            {{ __('Sending Domain') }}
        </button>
        <button type="button" @click="activeTab = 'sending_quota'" :class="activeTab === 'sending_quota' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-primary-500 dark:!border-primary-400 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-transparent text-gray-500 hover:text-gray-700 hover:!border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
            {{ __('Email Sending Quota') }}
        </button>
        <button type="button" @click="activeTab = 'email_validation'" :class="activeTab === 'email_validation' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-primary-500 dark:!border-primary-400 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-transparent text-gray-500 hover:text-gray-700 hover:!border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
            {{ __('Email Validation') }}
        </button>
        <button type="button" @click="activeTab = 'integrations'" :class="activeTab === 'integrations' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-primary-500 dark:!border-primary-400 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-transparent text-gray-500 hover:text-gray-700 hover:!border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
            {{ __('Integrations') }}
        </button>
        <button type="button" @click="activeTab = 'ai'" :class="activeTab === 'ai' ? 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-primary-500 dark:!border-primary-400 text-primary-600 dark:text-primary-400' : 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm !border-transparent text-gray-500 hover:text-gray-700 hover:!border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
            {{ __('AI') }}
        </button>
    </nav>
</div>

<div x-show="activeTab === 'messages'">
    <x-card title="{{ __('Messages') }}">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Default access denied message') }}</label>
                <input type="text" name="messages[access][default]" value="{{ old('messages.access.default', data_get($settings ?? $defaultSettings ?? [], 'messages.access.default', '')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm" placeholder="{{ __('You have no access here.') }}">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Access denied message (Tracking Domains)') }}</label>
                <input type="text" name="messages[access][domains][tracking_domains][can_manage]" value="{{ old('messages.access.domains.tracking_domains.can_manage', data_get($settings ?? $defaultSettings ?? [], 'messages.access.domains.tracking_domains.can_manage', '')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm" placeholder="{{ __('You have no access to tracking domains.') }}">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Access denied message (Sending Domains)') }}</label>
                <input type="text" name="messages[access][domains][sending_domains][can_manage]" value="{{ old('messages.access.domains.sending_domains.can_manage', data_get($settings ?? $defaultSettings ?? [], 'messages.access.domains.sending_domains.can_manage', '')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm" placeholder="{{ __('You have no access to sending domains.') }}">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Access denied message (Delivery Servers)') }}</label>
                <input type="text" name="messages[access][servers][permissions][can_add_delivery_servers]" value="{{ old('messages.access.servers.permissions.can_add_delivery_servers', data_get($settings ?? $defaultSettings ?? [], 'messages.access.servers.permissions.can_add_delivery_servers', '')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm" placeholder="{{ __('You have no access to delivery servers.') }}">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Limit reached message (Tracking Domains)') }}</label>
                <input type="text" name="messages[limits][domains][tracking_domains][max_tracking_domains]" value="{{ old('messages.limits.domains.tracking_domains.max_tracking_domains', data_get($settings ?? $defaultSettings ?? [], 'messages.limits.domains.tracking_domains.max_tracking_domains', '')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm" placeholder="{{ __('Your tracking domain limits expired.') }}">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Limit reached message (Sending Domains)') }}</label>
                <input type="text" name="messages[limits][domains][sending_domains][max_sending_domains]" value="{{ old('messages.limits.domains.sending_domains.max_sending_domains', data_get($settings ?? $defaultSettings ?? [], 'messages.limits.domains.sending_domains.max_sending_domains', '')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm" placeholder="{{ __('Your sending domain limits expired.') }}">
            </div>
        </div>
    </x-card>
</div>

<div x-show="activeTab === 'automations'">
    <x-card title="{{ __('Automations') }}">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Access to automations') }}</span>
                    <div class="flex items-center">
                        <input type="hidden" name="automations[enabled]" value="0">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="automations[enabled]" value="1" {{ old('automations.enabled', data_get($settings ?? $defaultSettings ?? [], 'automations.enabled', false)) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </x-card>
</div>

<div x-show="activeTab === 'integrations'">
    <x-card title="{{ __('Integrations') }}">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Access to Google Integrations') }}</span>
                    <div class="flex items-center">
                        <input type="hidden" name="integrations[permissions][can_access_google]" value="0">
                        <label class="inline-flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                name="integrations[permissions][can_access_google]"
                                value="1"
                                {{ old('integrations.permissions.can_access_google', data_get($settings ?? $defaultSettings ?? [], 'integrations.permissions.can_access_google', false)) ? 'checked' : '' }}
                                class="sr-only peer"
                            >
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('Enables the Integrations → Google page for customers in this group (Sheets/Drive connect & sync).') }}</p>
            </div>
        </div>
    </x-card>
</div>

<div x-show="activeTab === 'email_validation'">
    <x-card title="{{ __('Email Validation') }}">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Access') }}</span>
                    <div class="flex items-center">
                        <input type="hidden" name="email_validation[access]" value="0">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="email_validation[access]" value="1" {{ old('email_validation.access', data_get($settings ?? $defaultSettings ?? [], 'email_validation.access', false)) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="sm:col-span-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Must add own tool') }}</span>
                    <div class="flex items-center">
                        <input type="hidden" name="email_validation[must_add]" value="0">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="email_validation[must_add]" value="1" {{ old('email_validation.must_add', data_get($settings ?? $defaultSettings ?? [], 'email_validation.must_add', false)) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Max Number of tools to add') }}</label>
                <input type="number" min="0" name="email_validation[max_tools]" value="{{ old('email_validation.max_tools', data_get($settings ?? $defaultSettings ?? [], 'email_validation.max_tools', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Monthly validation limit') }}</label>
                <input type="number" min="0" name="email_validation[monthly_limit]" value="{{ old('email_validation.monthly_limit', data_get($settings ?? $defaultSettings ?? [], 'email_validation.monthly_limit', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>
        </div>
    </x-card>
</div>

<div x-show="activeTab === 'ai'">
    <x-card title="{{ __('AI') }}">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Customer must use their API keys') }}</span>
                    <div class="flex items-center">
                        <input type="hidden" name="ai[must_use_own_keys]" value="0">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="ai[must_use_own_keys]" value="1" {{ old('ai.must_use_own_keys', data_get($settings ?? $defaultSettings ?? [], 'ai.must_use_own_keys', false)) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Token limit (when using admin keys)') }}</label>
                <input type="number" min="0" name="ai[token_limit]" value="{{ old('ai.token_limit', data_get($settings ?? $defaultSettings ?? [], 'ai.token_limit', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Image generation credits (when using admin keys)') }}</label>
                <input type="number" min="0" name="ai[image_credits]" value="{{ old('ai.image_credits', data_get($settings ?? $defaultSettings ?? [], 'ai.image_credits', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>
        </div>
    </x-card>
</div>

<div x-show="activeTab === 'email_lists'">
    <x-card title="{{ __('Email Lists') }}">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Max Number of Lists') }}</label>
                <input type="number" min="0" name="lists[limits][max_lists]" value="{{ old('lists.limits.max_lists', data_get($settings ?? $defaultSettings ?? [], 'lists.limits.max_lists', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Max Subscribers Per List') }}</label>
                <input type="number" min="0" name="lists[limits][max_subscribers_per_list]" value="{{ old('lists.limits.max_subscribers_per_list', data_get($settings ?? $defaultSettings ?? [], 'lists.limits.max_subscribers_per_list', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Max Forms Per List') }}</label>
                <input type="number" min="0" name="lists[limits][max_forms_per_list]" value="{{ old('lists.limits.max_forms_per_list', data_get($settings ?? $defaultSettings ?? [], 'lists.limits.max_forms_per_list', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Max Subscribers (All Lists)') }}</label>
                <input type="number" min="0" name="lists[limits][max_subscribers]" value="{{ old('lists.limits.max_subscribers', data_get($settings ?? $defaultSettings ?? [], 'lists.limits.max_subscribers', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>
        </div>
    </x-card>
</div>

<div x-show="activeTab === 'campaigns'">
    <x-card title="{{ __('Campaigns') }}">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Max Campaigns to Create') }}</label>
                <input type="number" min="0" name="campaigns[limits][max_campaigns]" value="{{ old('campaigns.limits.max_campaigns', data_get($settings ?? $defaultSettings ?? [], 'campaigns.limits.max_campaigns', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Max Campaigns to Run') }}</label>
                <input type="number" min="0" name="campaigns[limits][max_active_campaigns]" value="{{ old('campaigns.limits.max_active_campaigns', data_get($settings ?? $defaultSettings ?? [], 'campaigns.limits.max_active_campaigns', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>
            <div class="sm:col-span-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Access to A/B testing') }}</span>
                    <div class="flex items-center">
                        <input type="hidden" name="campaigns[features][ab_testing]" value="0">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="campaigns[features][ab_testing]" value="1" {{ old('campaigns.features.ab_testing', data_get($settings ?? $defaultSettings ?? [], 'campaigns.features.ab_testing', false)) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="sm:col-span-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Can create campaigns') }}</span>
                    <div class="flex items-center">
                        <input type="hidden" name="campaigns[permissions][can_create_campaigns]" value="0">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="campaigns[permissions][can_create_campaigns]" value="1" {{ old('campaigns.permissions.can_create_campaigns', data_get($settings ?? $defaultSettings ?? [], 'campaigns.permissions.can_create_campaigns', false)) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="sm:col-span-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Can edit campaigns') }}</span>
                    <div class="flex items-center">
                        <input type="hidden" name="campaigns[permissions][can_edit_campaigns]" value="0">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="campaigns[permissions][can_edit_campaigns]" value="1" {{ old('campaigns.permissions.can_edit_campaigns', data_get($settings ?? $defaultSettings ?? [], 'campaigns.permissions.can_edit_campaigns', false)) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="sm:col-span-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Can start/pause/resume campaigns') }}</span>
                    <div class="flex items-center">
                        <input type="hidden" name="campaigns[permissions][can_start_campaigns]" value="0">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="campaigns[permissions][can_start_campaigns]" value="1" {{ old('campaigns.permissions.can_start_campaigns', data_get($settings ?? $defaultSettings ?? [], 'campaigns.permissions.can_start_campaigns', false)) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="sm:col-span-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Can delete campaigns') }}</span>
                    <div class="flex items-center">
                        <input type="hidden" name="campaigns[permissions][can_delete_campaigns]" value="0">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="campaigns[permissions][can_delete_campaigns]" value="1" {{ old('campaigns.permissions.can_delete_campaigns', data_get($settings ?? $defaultSettings ?? [], 'campaigns.permissions.can_delete_campaigns', false)) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </x-card>
</div>

<div x-show="activeTab === 'autoresponders'">
    <x-card title="{{ __('Auto Responders') }}">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Access to auto responders') }}</span>
                    <div class="flex items-center">
                        <input type="hidden" name="autoresponders[enabled]" value="0">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="autoresponders[enabled]" value="1" {{ old('autoresponders.enabled', data_get($settings ?? $defaultSettings ?? [], 'autoresponders.enabled', false)) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Number of Auto Responders to Create') }}</label>
                <input type="number" min="0" name="autoresponders[max_autoresponders]" value="{{ old('autoresponders.max_autoresponders', data_get($settings ?? $defaultSettings ?? [], 'autoresponders.max_autoresponders', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>
        </div>
    </x-card>
</div>

<div x-show="activeTab === 'servers'">
    <x-card title="{{ __('Servers') }}">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
                <div class="sm:col-span-2 mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Allocated Delivery Servers') }}</label>
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
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('If set, this group can only use these delivery servers (unless a customer has their own allocation).') }}</p>
                </div>
                <div class="sm:col-span-2 mb-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Must add delivery server') }}</span>
                        <div class="flex items-center">
                            <input type="hidden" name="servers[permissions][must_add_delivery_server]" value="0">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="servers[permissions][must_add_delivery_server]" value="1" {{ old('servers.permissions.must_add_delivery_server', data_get($settings ?? $defaultSettings ?? [], 'servers.permissions.must_add_delivery_server', false)) ? 'checked' : '' }} class="sr-only peer">
                                <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="sm:col-span-2 mb-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Can add delivery servers') }}</span>
                        <div class="flex items-center">
                            <input type="hidden" name="servers[permissions][can_add_delivery_servers]" value="0">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="servers[permissions][can_add_delivery_servers]" value="1" {{ old('servers.permissions.can_add_delivery_servers', data_get($settings ?? $defaultSettings ?? [], 'servers.permissions.can_add_delivery_servers', false)) ? 'checked' : '' }} class="sr-only peer">
                                <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                            </label>
                        </div>
                    </div>
                </div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Max Delivery Servers') }}</label>
                <input type="number" min="0" name="servers[limits][max_delivery_servers]" value="{{ old('servers.limits.max_delivery_servers', data_get($settings ?? $defaultSettings ?? [], 'servers.limits.max_delivery_servers', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>
            <div>
                <div class="sm:col-span-2 mb-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Must add bounce server') }}</span>
                        <div class="flex items-center">
                            <input type="hidden" name="servers[permissions][must_add_bounce_server]" value="0">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="servers[permissions][must_add_bounce_server]" value="1" {{ old('servers.permissions.must_add_bounce_server', data_get($settings ?? $defaultSettings ?? [], 'servers.permissions.must_add_bounce_server', false)) ? 'checked' : '' }} class="sr-only peer">
                                <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="sm:col-span-2 mb-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Access bounce servers') }}</span>
                        <div class="flex items-center">
                            <input type="hidden" name="servers[permissions][can_access_bounce_servers]" value="0">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="servers[permissions][can_access_bounce_servers]" value="1" {{ old('servers.permissions.can_access_bounce_servers', data_get($settings ?? $defaultSettings ?? [], 'servers.permissions.can_access_bounce_servers', false)) ? 'checked' : '' }} class="sr-only peer">
                                <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="sm:col-span-2 mb-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Can add bounce servers') }}</span>
                        <div class="flex items-center">
                            <input type="hidden" name="servers[permissions][can_add_bounce_servers]" value="0">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="servers[permissions][can_add_bounce_servers]" value="1" {{ old('servers.permissions.can_add_bounce_servers', data_get($settings ?? $defaultSettings ?? [], 'servers.permissions.can_add_bounce_servers', false)) ? 'checked' : '' }} class="sr-only peer">
                                <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="sm:col-span-2 mb-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Can edit bounce servers') }}</span>
                        <div class="flex items-center">
                            <input type="hidden" name="servers[permissions][can_edit_bounce_servers]" value="0">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="servers[permissions][can_edit_bounce_servers]" value="1" {{ old('servers.permissions.can_edit_bounce_servers', data_get($settings ?? $defaultSettings ?? [], 'servers.permissions.can_edit_bounce_servers', false)) ? 'checked' : '' }} class="sr-only peer">
                                <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="sm:col-span-2 mb-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Can delete bounce servers') }}</span>
                        <div class="flex items-center">
                            <input type="hidden" name="servers[permissions][can_delete_bounce_servers]" value="0">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="servers[permissions][can_delete_bounce_servers]" value="1" {{ old('servers.permissions.can_delete_bounce_servers', data_get($settings ?? $defaultSettings ?? [], 'servers.permissions.can_delete_bounce_servers', false)) ? 'checked' : '' }} class="sr-only peer">
                                <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                            </label>
                        </div>
                    </div>
                </div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Max Bounce Servers') }}</label>
                <input type="number" min="0" name="servers[limits][max_bounce_servers]" value="{{ old('servers.limits.max_bounce_servers', data_get($settings ?? $defaultSettings ?? [], 'servers.limits.max_bounce_servers', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>

            <div>
                <div class="sm:col-span-2 mb-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Must add reply server') }}</span>
                        <div class="flex items-center">
                            <input type="hidden" name="servers[permissions][must_add_reply_server]" value="0">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="servers[permissions][must_add_reply_server]" value="1" {{ old('servers.permissions.must_add_reply_server', data_get($settings ?? $defaultSettings ?? [], 'servers.permissions.must_add_reply_server', false)) ? 'checked' : '' }} class="sr-only peer">
                                <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="sm:col-span-2 mb-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Access reply servers') }}</span>
                        <div class="flex items-center">
                            <input type="hidden" name="servers[permissions][can_access_reply_servers]" value="0">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="servers[permissions][can_access_reply_servers]" value="1" {{ old('servers.permissions.can_access_reply_servers', data_get($settings ?? $defaultSettings ?? [], 'servers.permissions.can_access_reply_servers', false)) ? 'checked' : '' }} class="sr-only peer">
                                <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="sm:col-span-2 mb-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Can add reply servers') }}</span>
                        <div class="flex items-center">
                            <input type="hidden" name="servers[permissions][can_add_reply_servers]" value="0">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="servers[permissions][can_add_reply_servers]" value="1" {{ old('servers.permissions.can_add_reply_servers', data_get($settings ?? $defaultSettings ?? [], 'servers.permissions.can_add_reply_servers', false)) ? 'checked' : '' }} class="sr-only peer">
                                <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="sm:col-span-2 mb-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Can edit reply servers') }}</span>
                        <div class="flex items-center">
                            <input type="hidden" name="servers[permissions][can_edit_reply_servers]" value="0">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="servers[permissions][can_edit_reply_servers]" value="1" {{ old('servers.permissions.can_edit_reply_servers', data_get($settings ?? $defaultSettings ?? [], 'servers.permissions.can_edit_reply_servers', false)) ? 'checked' : '' }} class="sr-only peer">
                                <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="sm:col-span-2 mb-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Can delete reply servers') }}</span>
                        <div class="flex items-center">
                            <input type="hidden" name="servers[permissions][can_delete_reply_servers]" value="0">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="servers[permissions][can_delete_reply_servers]" value="1" {{ old('servers.permissions.can_delete_reply_servers', data_get($settings ?? $defaultSettings ?? [], 'servers.permissions.can_delete_reply_servers', false)) ? 'checked' : '' }} class="sr-only peer">
                                <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Max Reply Servers') }}</label>
                <input type="number" min="0" name="servers[limits][max_reply_servers]" value="{{ old('servers.limits.max_reply_servers', data_get($settings ?? $defaultSettings ?? [], 'servers.limits.max_reply_servers', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>
        </div>
    </x-card>
</div>

<div x-show="activeTab === 'tracking_domains'">
    <x-card title="{{ __('Tracking Domain') }}">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Must add tracking domain') }}</span>
                    <div class="flex items-center">
                        <input type="hidden" name="domains[tracking_domains][must_add]" value="0">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="domains[tracking_domains][must_add]" value="1" {{ old('domains.tracking_domains.must_add', data_get($settings ?? $defaultSettings ?? [], 'domains.tracking_domains.must_add', false)) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                </div>
            </div>
            <div class="sm:col-span-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Access') }}</span>
                    <div class="flex items-center">
                        <input type="hidden" name="domains[tracking_domains][can_manage]" value="0">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="domains[tracking_domains][can_manage]" value="1" {{ old('domains.tracking_domains.can_manage', data_get($settings ?? $defaultSettings ?? [], 'domains.tracking_domains.can_manage', false)) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Limit (Max tracking domains)') }}</label>
                <input type="number" min="0" name="domains[tracking_domains][max_tracking_domains]" value="{{ old('domains.tracking_domains.max_tracking_domains', data_get($settings ?? $defaultSettings ?? [], 'domains.tracking_domains.max_tracking_domains', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>
        </div>
    </x-card>
</div>

<div x-show="activeTab === 'sending_domains'">
    <x-card title="{{ __('Sending Domain') }}">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Must add sending domain') }}</span>
                    <div class="flex items-center">
                        <input type="hidden" name="domains[sending_domains][must_add]" value="0">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="domains[sending_domains][must_add]" value="1" {{ old('domains.sending_domains.must_add', data_get($settings ?? $defaultSettings ?? [], 'domains.sending_domains.must_add', false)) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                </div>
            </div>
            <div class="sm:col-span-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Access') }}</span>
                    <div class="flex items-center">
                        <input type="hidden" name="domains[sending_domains][can_manage]" value="0">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="domains[sending_domains][can_manage]" value="1" {{ old('domains.sending_domains.can_manage', data_get($settings ?? $defaultSettings ?? [], 'domains.sending_domains.can_manage', false)) ? 'checked' : '' }} class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Limit (Max sending domains)') }}</label>
                <input type="number" min="0" name="domains[sending_domains][max_sending_domains]" value="{{ old('domains.sending_domains.max_sending_domains', data_get($settings ?? $defaultSettings ?? [], 'domains.sending_domains.max_sending_domains', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>
        </div>
    </x-card>
</div>

<div x-show="activeTab === 'sending_quota'">
    <x-card title="{{ __('Email Sending Quota') }}">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Daily') }}</label>
                <input type="number" min="0" name="sending_quota[daily_quota]" value="{{ old('sending_quota.daily_quota', data_get($settings ?? $defaultSettings ?? [], 'sending_quota.daily_quota', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Weekly') }}</label>
                <input type="number" min="0" name="sending_quota[weekly_quota]" value="{{ old('sending_quota.weekly_quota', data_get($settings ?? $defaultSettings ?? [], 'sending_quota.weekly_quota', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Monthly') }}</label>
                <input type="number" min="0" name="sending_quota[monthly_quota]" value="{{ old('sending_quota.monthly_quota', data_get($settings ?? $defaultSettings ?? [], 'sending_quota.monthly_quota', 0)) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>
        </div>
    </x-card>
</div>
