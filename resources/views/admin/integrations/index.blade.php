@extends('layouts.admin')

@section('title', __('Integrations'))
@section('page-title', __('Integrations'))

@section('content')
<div class="space-y-6" x-data="{ tab: @js($tab), setTab(next) { this.tab = next; const url = new URL(window.location.href); url.searchParams.set('tab', next); window.history.replaceState({}, '', url); } }">
    <div class="border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
        <nav class="-mb-px flex min-w-max space-x-6 sm:space-x-8 px-2 sm:px-0" aria-label="Tabs">
            <a
                href="{{ route('admin.integrations.index', ['tab' => 'delivery-servers']) }}"
                @click.prevent="setTab('delivery-servers')"
                class="whitespace-nowrap shrink-0 py-3 sm:py-4 px-2 sm:px-1 font-medium text-sm"
                :class="tab === 'delivery-servers' ? '!border-primary-500 text-primary-600 dark:text-primary-400 border-b-2' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
            >
                {{ __('Delivery Servers') }}
            </a>
            <a
                href="{{ route('admin.integrations.index', ['tab' => 'google']) }}"
                @click.prevent="setTab('google')"
                class="whitespace-nowrap shrink-0 py-3 sm:py-4 px-2 sm:px-1 font-medium text-sm"
                :class="tab === 'google' ? '!border-primary-500 text-primary-600 dark:text-primary-400 border-b-2' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
            >
                {{ __('Google') }}
            </a>
            <a
                href="{{ route('admin.integrations.index', ['tab' => 'wordpress']) }}"
                @click.prevent="setTab('wordpress')"
                class="whitespace-nowrap shrink-0 py-3 sm:py-4 px-2 sm:px-1 font-medium text-sm"
                :class="tab === 'wordpress' ? '!border-primary-500 text-primary-600 dark:text-primary-400 border-b-2' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
            >
                {{ __('Wordpress') }}
            </a>
        </nav>
    </div>

    <div x-show="tab === 'delivery-servers'" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($providers as $provider)
                @php
                    $type = (string) ($provider['type'] ?? '');
                    $supported = (bool) ($provider['supported'] ?? false);
                    $isConfigured = (bool) ($configured[$type] ?? false);
                    $server = $supported ? ($serversByType[$type] ?? null) : null;
                    $serverId = data_get($server, 'id');
                    $serverId = is_numeric($serverId) ? (int) $serverId : null;

                    $docsUrl = match ($type) {
                        'mailgun' => 'https://documentation.mailgun.com/docs/mailgun/api-reference/api-overview',
                        'sendgrid' => 'https://docs.sendgrid.com/ui/account-and-settings/api-keys',
                        'postmark' => 'https://postmarkapp.com/developer/user-guide/send-email-with-api',
                        'sparkpost' => 'https://developers.sparkpost.com/api/',
                        'amazon-ses' => 'https://docs.aws.amazon.com/IAM/latest/UserGuide/id_credentials_access-keys.html',
                        'zeptomail-api' => 'https://www.zoho.com/zeptomail/help/api/email-sending.html',
                        default => null,
                    };
                @endphp

                <div class="relative rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
                    @if($isConfigured)
                        <div class="absolute top-3 ltr:right-3 rtl:left-3 rtl:right-auto z-10 h-6 w-6 rounded-full bg-green-500 text-white flex items-center justify-center">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    @endif

                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $provider['label'] }}</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $provider['description'] }}</p>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between gap-3">
                        @if($supported)
                            <x-button
                                type="button"
                                variant="secondary"
                                size="sm"
                                @click="$dispatch('open-modal', 'configure-delivery-server-{{ $type }}')"
                            >
                                <span class="inline-flex items-center gap-2">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ __('Configure') }}
                                </span>
                            </x-button>

                            @if($docsUrl)
                                <a
                                    href="{{ $docsUrl }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
                                >
                                    {{ __('Docs') }}
                                </a>
                            @endif
                        @else
                            <x-button type="button" variant="secondary" size="sm" disabled>
                                {{ __('Coming Soon') }}
                            </x-button>
                        @endif
                    </div>
                </div>

                @if($supported)
                    <x-modal name="configure-delivery-server-{{ $type }}" maxWidth="2xl">
                        <div class="p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Configure') }} {{ $provider['label'] }}</h2>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Enter the required API credentials and settings.') }}</p>
                                </div>
                            </div>

                            <form method="POST" action="{{ $serverId ? route('admin.delivery-servers.update', $serverId) : route('admin.delivery-servers.store') }}" class="mt-6 space-y-4">
                                @csrf
                                @if($serverId)
                                    @method('PUT')
                                @endif

                                <input type="hidden" name="type" value="{{ $type }}">
                                <input type="hidden" name="name" value="{{ $provider['label'] }}">
                                <input type="hidden" name="status" value="active">

                                @if($type === 'mailgun')
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Domain') }}</label>
                                        <input
                                            type="text"
                                            name="settings[domain]"
                                            value="{{ old('settings.domain', data_get($server, 'settings.domain', '')) }}"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('API Secret') }}</label>
                                        <div class="relative" data-secret-wrapper>
                                            <input
                                                type="password"
                                                name="settings[secret]"
                                                value="{{ !empty(data_get($server, 'settings.secret')) ? '********' : '' }}"
                                                data-secret-url="{{ $serverId ? route('admin.delivery-servers.secret', ['delivery_server' => $serverId, 'field' => 'secret']) : '' }}"
                                                data-secret-input
                                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm pr-10"
                                            />
                                            <button
                                                type="button"
                                                data-toggle-secret
                                                class="absolute inset-y-0 right-0 flex items-center px-3 mt-1 text-gray-500 hover:text-gray-700 dark:text-gray-300"
                                                aria-label="{{ __('Toggle secret visibility') }}"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                @if($type === 'sendgrid')
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('API Key') }}</label>
                                        <div class="relative" data-secret-wrapper>
                                            <input
                                                type="password"
                                                name="settings[api_key]"
                                                value="{{ !empty(data_get($server, 'settings.api_key')) ? '********' : '' }}"
                                                data-secret-url="{{ $serverId ? route('admin.delivery-servers.secret', ['delivery_server' => $serverId, 'field' => 'api_key']) : '' }}"
                                                data-secret-input
                                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm pr-10"
                                            />
                                            <button
                                                type="button"
                                                data-toggle-secret
                                                class="absolute inset-y-0 right-0 flex items-center px-3 mt-1 text-gray-500 hover:text-gray-700 dark:text-gray-300"
                                                aria-label="{{ __('Toggle secret visibility') }}"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                @if($type === 'postmark')
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Server Token') }}</label>
                                        <div class="relative" data-secret-wrapper>
                                            <input
                                                type="password"
                                                name="settings[token]"
                                                value="{{ !empty(data_get($server, 'settings.token')) ? '********' : '' }}"
                                                data-secret-url="{{ $serverId ? route('admin.delivery-servers.secret', ['delivery_server' => $serverId, 'field' => 'token']) : '' }}"
                                                data-secret-input
                                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm pr-10"
                                            />
                                            <button
                                                type="button"
                                                data-toggle-secret
                                                class="absolute inset-y-0 right-0 flex items-center px-3 mt-1 text-gray-500 hover:text-gray-700 dark:text-gray-300"
                                                aria-label="{{ __('Toggle secret visibility') }}"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                @if($type === 'sparkpost')
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('API Secret') }}</label>
                                        <div class="relative" data-secret-wrapper>
                                            <input
                                                type="password"
                                                name="settings[secret]"
                                                value="{{ !empty(data_get($server, 'settings.secret')) ? '********' : '' }}"
                                                data-secret-url="{{ $serverId ? route('admin.delivery-servers.secret', ['delivery_server' => $serverId, 'field' => 'secret']) : '' }}"
                                                data-secret-input
                                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm pr-10"
                                            />
                                            <button
                                                type="button"
                                                data-toggle-secret
                                                class="absolute inset-y-0 right-0 flex items-center px-3 mt-1 text-gray-500 hover:text-gray-700 dark:text-gray-300"
                                                aria-label="{{ __('Toggle secret visibility') }}"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                @if($type === 'amazon-ses')
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Access Key') }}</label>
                                        <div class="relative" data-secret-wrapper>
                                            <input
                                                type="password"
                                                name="settings[key]"
                                                value="{{ !empty(data_get($server, 'settings.key')) ? '********' : '' }}"
                                                data-secret-url="{{ $serverId ? route('admin.delivery-servers.secret', ['delivery_server' => $serverId, 'field' => 'key']) : '' }}"
                                                data-secret-input
                                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm pr-10"
                                            />
                                            <button
                                                type="button"
                                                data-toggle-secret
                                                class="absolute inset-y-0 right-0 flex items-center px-3 mt-1 text-gray-500 hover:text-gray-700 dark:text-gray-300"
                                                aria-label="{{ __('Toggle secret visibility') }}"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Secret Key') }}</label>
                                        <div class="relative" data-secret-wrapper>
                                            <input
                                                type="password"
                                                name="settings[secret]"
                                                value="{{ !empty(data_get($server, 'settings.secret')) ? '********' : '' }}"
                                                data-secret-url="{{ $serverId ? route('admin.delivery-servers.secret', ['delivery_server' => $serverId, 'field' => 'secret']) : '' }}"
                                                data-secret-input
                                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm pr-10"
                                            />
                                            <button
                                                type="button"
                                                data-toggle-secret
                                                class="absolute inset-y-0 right-0 flex items-center px-3 mt-1 text-gray-500 hover:text-gray-700 dark:text-gray-300"
                                                aria-label="{{ __('Toggle secret visibility') }}"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Region') }}</label>
                                        <input
                                            type="text"
                                            name="settings[region]"
                                            value="{{ old('settings.region', data_get($server, 'settings.region', '')) }}"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('From Email') }}</label>
                                        <input
                                            type="email"
                                            name="from_email"
                                            value="{{ old('from_email', data_get($server, 'from_email', '')) }}"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                        />
                                    </div>
                                @endif

                                @if($type === 'zeptomail-api')
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Send Mail Token') }}</label>
                                        <div class="relative" data-secret-wrapper>
                                            <input
                                                type="password"
                                                name="settings[send_mail_token]"
                                                value="{{ !empty(data_get($server, 'settings.send_mail_token')) ? '********' : '' }}"
                                                data-secret-url="{{ $serverId ? route('admin.delivery-servers.secret', ['delivery_server' => $serverId, 'field' => 'send_mail_token']) : '' }}"
                                                data-secret-input
                                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm pr-10"
                                            />
                                            <button
                                                type="button"
                                                data-toggle-secret
                                                class="absolute inset-y-0 right-0 flex items-center px-3 mt-1 text-gray-500 hover:text-gray-700 dark:text-gray-300"
                                                aria-label="{{ __('Toggle secret visibility') }}"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Mode') }}</label>
                                        <select
                                            name="settings[mode]"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                        >
                                            @php $mode = old('settings.mode', data_get($server, 'settings.mode', 'raw')); @endphp
                                            <option value="raw" {{ $mode === 'raw' ? 'selected' : '' }}>{{ __('Raw HTML/Text') }}</option>
                                            <option value="template" {{ $mode === 'template' ? 'selected' : '' }}>{{ __('Template') }}</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Template Key') }}</label>
                                        <input
                                            type="text"
                                            name="settings[template_key]"
                                            value="{{ old('settings.template_key', data_get($server, 'settings.template_key', '')) }}"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Template Alias') }}</label>
                                        <input
                                            type="text"
                                            name="settings[template_alias]"
                                            value="{{ old('settings.template_alias', data_get($server, 'settings.template_alias', '')) }}"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                        />
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Used when mode is Template.') }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Bounce Address') }}</label>
                                        <input
                                            type="email"
                                            name="settings[bounce_address]"
                                            value="{{ old('settings.bounce_address', data_get($server, 'settings.bounce_address', '')) }}"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                        />
                                    </div>
                                @endif

                                <div class="pt-4 flex items-center justify-between gap-2">
                                    <div>
                                        @if($serverId)
                                            <form method="POST" action="{{ route('admin.delivery-servers.destroy', $serverId) }}" onsubmit="return confirm('{{ __('Are you sure you want to reset/remove this configuration?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <x-button type="submit" variant="danger" size="sm">
                                                    {{ __('Reset') }}
                                                </x-button>
                                            </form>
                                        @endif
                                    </div>
                                    <x-button
                                        type="button"
                                        variant="secondary"
                                        size="sm"
                                        @click="$dispatch('close-modal', 'configure-delivery-server-{{ $type }}')"
                                    >
                                        {{ __('Cancel') }}
                                    </x-button>
                                    <x-button type="submit" variant="primary" size="sm">
                                        {{ __('Save') }}
                                    </x-button>
                                </div>
                            </form>
                        </div>
                    </x-modal>
                @endif
            @endforeach
        </div>
    </div>

    <div x-show="tab === 'google'" class="space-y-4">
        <x-card>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Google') }}</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Google integrations are connected per customer account.') }}</p>
                </div>
            </div>

            <div class="mt-4 space-y-3">
                @if(!($googleSocialiteAvailable ?? false))
                    <div class="p-3 rounded-md border border-red-200 bg-red-50 text-red-800 dark:border-red-800 dark:bg-red-900/30 dark:text-red-200">
                        <div class="text-sm font-semibold">{{ __('Google integration is not available') }}</div>
                        <div class="mt-1 text-sm">{{ __('Laravel Socialite is not installed/enabled.') }}</div>
                    </div>
                @elseif(!($googleOAuthConfigured ?? false))
                    <div class="p-3 rounded-md border border-yellow-200 bg-yellow-50 text-yellow-800 dark:border-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200">
                        <div class="text-sm font-semibold">{{ __('Google OAuth is not configured') }}</div>
                        <div class="mt-1 text-sm">{{ __('Set Google Client ID and Client Secret in Admin → Settings → Auth.') }}</div>
                    </div>
                @else
                    <div class="p-3 rounded-md border border-green-200 bg-green-50 text-green-800 dark:border-green-800 dark:bg-green-900/30 dark:text-green-200">
                        <div class="text-sm font-semibold">{{ __('Google OAuth is configured') }}</div>
                        <div class="mt-1 text-sm">{{ __('Customers can connect Sheets/Drive from Customer → Integrations → Google.') }}</div>
                    </div>
                @endif
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="relative rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Google Sheets') }}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Import and auto-sync subscribers, and export campaign reports.') }}</p>
                        </div>
                    </div>

                    <div class="mt-3">
                        <ul class="text-sm text-gray-500 dark:text-gray-400 space-y-1">
                            <li>{{ __('Auto-sync subscribers from Sheets to a list') }}</li>
                            <li>{{ __('Import contacts with field mapping and tags') }}</li>
                            <li>{{ __('Export campaigns and metrics to Sheets') }}</li>
                        </ul>
                    </div>

                    <div class="mt-4">
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('OAuth Redirect URL') }}</div>
                        <div class="mt-1 rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3 py-2 font-mono text-xs break-all">{{ $googleRedirectSheets ?? '' }}</div>
                    </div>
                </div>

                <div class="relative rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Google Drive') }}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Use Drive assets in templates and store exports and backups.') }}</p>
                        </div>
                    </div>

                    <div class="mt-3">
                        <ul class="text-sm text-gray-500 dark:text-gray-400 space-y-1">
                            <li>{{ __('Pick images from Drive for your email templates') }}</li>
                            <li>{{ __('Save templates to Drive') }}</li>
                            <li>{{ __('Export backups to a Drive folder') }}</li>
                        </ul>
                    </div>

                    <div class="mt-4">
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('OAuth Redirect URL') }}</div>
                        <div class="mt-1 rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3 py-2 font-mono text-xs break-all">{{ $googleRedirectDrive ?? '' }}</div>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <a
                    href="{{ route('admin.settings.index', ['category' => 'auth']) }}"
                    class="text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
                >
                    {{ __('Go to Auth settings') }}
                </a>
            </div>
        </x-card>
    </div>

    <div x-show="tab === 'wordpress'" class="space-y-4">
        <x-card>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Wordpress') }}</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Connect WordPress and WooCommerce events to MailZen automations.') }}</p>
                </div>
                <div class="shrink-0">
                    <x-button href="{{ route('admin.integrations.wordpress.plugin') }}" variant="secondary" size="sm">
                        {{ __('Download Plugin') }}
                    </x-button>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('How to install') }}</h4>
                    <ol class="mt-2 text-sm text-gray-500 dark:text-gray-400 space-y-1 list-decimal list-inside">
                        <li>{{ __('Download the plugin zip from above.') }}</li>
                        <li>{{ __('In WordPress: Plugins → Add New → Upload Plugin → choose the zip → Install → Activate.') }}</li>
                        <li>{{ __('In WordPress: Settings → MailZen.') }}</li>
                    </ol>
                </div>

                <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('API Key (Customer token)') }}</h4>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('The plugin authenticates using a Customer API Key (Bearer token). Create it from the customer dashboard: Customer → API → Create API Key.') }}</p>
                </div>
            </div>

            <div class="mt-4 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('How it works') }}</h4>
                <ul class="mt-2 text-sm text-gray-500 dark:text-gray-400 space-y-1">
                    <li>{{ __('WordPress uses your Base URL + Customer API Key to fetch lists and a signing secret.') }}</li>
                    <li>{{ __('When you click “Test Connection” in the plugin, it syncs a signing secret used to sign event requests.') }}</li>
                    <li>{{ __('Events are sent to MailZen and can trigger automations (events like wp_* and woo_*).') }}</li>
                </ul>
            </div>
        </x-card>
    </div>
</div>
@endsection
