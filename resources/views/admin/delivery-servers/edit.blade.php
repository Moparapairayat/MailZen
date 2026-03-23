@extends('layouts.admin')

@section('title', __('Edit Delivery Server'))
@section('page-title', __('Edit Delivery Server'))

@php
    $isSystemServer = ($deliveryServer->customer_id === null && $deliveryServer->name === 'System (SMTP)');
@endphp

@section('content')
<div class="max-w-4xl">
    @if($isSystemServer)
        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">{{ __('System SMTP Server') }}</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        {{ __('This server is configured via environment variables (.env file). Some fields are read-only.') }}
                    </div>
                </div>
            </div>
        </div>
    @endif

    <x-card title="{{ __('Edit Delivery Server') }}">
        <form method="POST" action="{{ route('admin.delivery-servers.update', $deliveryServer) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Server Name') }} <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $deliveryServer->name) }}" required {{ $isSystemServer ? 'readonly' : '' }} class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm {{ $isSystemServer ? 'bg-gray-100 dark:bg-gray-600' : '' }}">
                    @if($isSystemServer)
                        <p class="mt-1 text-sm text-gray-500">{{ __('System servers cannot be renamed.') }}</p>
                    @endif
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Server Type') }} <span class="text-red-500">*</span></label>
                    <select name="type" id="type" required {{ $isSystemServer ? 'readonly' : '' }} class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm {{ $isSystemServer ? 'bg-gray-100 dark:bg-gray-600' : '' }}">
                        <option value="smtp" {{ old('type', $deliveryServer->type) == 'smtp' ? 'selected' : '' }}>{{ __('SMTP') }}</option>
                        @if(!$isSystemServer)
                            <option value="sendmail" {{ old('type', $deliveryServer->type) == 'sendmail' ? 'selected' : '' }}>{{ __('Sendmail') }}</option>
                            <option value="zeptomail" {{ old('type', $deliveryServer->type) == 'zeptomail' ? 'selected' : '' }}>{{ __('ZeptoMail') }}</option>
                            <option value="amazon-ses" {{ old('type', $deliveryServer->type) == 'amazon-ses' ? 'selected' : '' }}>{{ __('Amazon SES') }}</option>
                            <option value="mailgun" {{ old('type', $deliveryServer->type) == 'mailgun' ? 'selected' : '' }}>{{ __('Mailgun') }}</option>
                            <option value="sendgrid" {{ old('type', $deliveryServer->type) == 'sendgrid' ? 'selected' : '' }}>{{ __('SendGrid') }}</option>
                            <option value="postmark" {{ old('type', $deliveryServer->type) == 'postmark' ? 'selected' : '' }}>{{ __('Postmark') }}</option>
                            <option value="sparkpost" {{ old('type', $deliveryServer->type) == 'sparkpost' ? 'selected' : '' }}>{{ __('SparkPost') }}</option>
                            <option value="zeptomail-api" {{ old('type', $deliveryServer->type) == 'zeptomail-api' ? 'selected' : '' }}>{{ __('ZeptoMail API') }}</option>
                        @endif
                    </select>
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Status') }}</label>
                    <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        <option value="pending" {{ old('status', $deliveryServer->status) == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                        <option value="active" {{ old('status', $deliveryServer->status) == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                        <option value="inactive" {{ old('status', $deliveryServer->status) == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                    </select>
                </div>

                <div>
                    <label for="hostname" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Hostname') }}</label>
                    <input type="text" name="hostname" id="hostname" value="{{ old('hostname', $deliveryServer->hostname) }}" {{ $isSystemServer ? 'readonly' : '' }} class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm {{ $isSystemServer ? 'bg-gray-100 dark:bg-gray-600' : '' }}">
                    @if($isSystemServer)
                        <p class="mt-1 text-sm text-gray-500">{{ __('Configured via MAIL_HOST environment variable.') }}</p>
                    @endif
                </div>

                <div>
                    <label for="port" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Port') }}</label>
                    <input type="number" name="port" id="port" value="{{ old('port', $deliveryServer->port) }}" min="1" max="65535" {{ $isSystemServer ? 'readonly' : '' }} class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm {{ $isSystemServer ? 'bg-gray-100 dark:bg-gray-600' : '' }}">
                    @if($isSystemServer)
                        <p class="mt-1 text-sm text-gray-500">{{ __('Configured via MAIL_PORT environment variable.') }}</p>
                    @endif
                </div>

                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Username') }}</label>
                    <input type="text" name="username" id="username" value="{{ old('username', $deliveryServer->username) }}" {{ $isSystemServer ? 'readonly' : '' }} class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm {{ $isSystemServer ? 'bg-gray-100 dark:bg-gray-600' : '' }}">
                    @if($isSystemServer)
                        <p class="mt-1 text-sm text-gray-500">{{ __('Configured via MAIL_USERNAME environment variable.') }}</p>
                    @endif
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Password') }}</label>
                    <input type="password" name="password" id="password" placeholder="{{ __('Leave blank to keep current') }}" {{ $isSystemServer ? 'readonly' : '' }} class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm {{ $isSystemServer ? 'bg-gray-100 dark:bg-gray-600' : '' }}">
                    @if($isSystemServer)
                        <p class="mt-1 text-sm text-gray-500">{{ __('Configured via MAIL_PASSWORD environment variable.') }}</p>
                    @endif
                </div>

                <div>
                    <label for="encryption" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Encryption') }}</label>
                    <select name="encryption" id="encryption" {{ $isSystemServer ? 'readonly' : '' }} class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm {{ $isSystemServer ? 'bg-gray-100 dark:bg-gray-600' : '' }}">
                        <option value="tls" {{ old('encryption', $deliveryServer->encryption) == 'tls' ? 'selected' : '' }}>{{ __('TLS') }}</option>
                        <option value="ssl" {{ old('encryption', $deliveryServer->encryption) == 'ssl' ? 'selected' : '' }}>{{ __('SSL') }}</option>
                        <option value="none" {{ old('encryption', $deliveryServer->encryption) == 'none' ? 'selected' : '' }}>{{ __('None') }}</option>
                    </select>
                    @if($isSystemServer)
                        <p class="mt-1 text-sm text-gray-500">{{ __('Configured via MAIL_ENCRYPTION environment variable.') }}</p>
                    @endif
                </div>

                <div class="sm:col-span-2" id="provider-settings" style="display: none;">
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                        <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('Provider API Settings') }}</div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div id="mailgun-settings" style="display: none;">
                                <label for="settings_domain" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Mailgun Domain') }}</label>
                                <input type="text" name="settings[domain]" id="settings_domain" value="{{ old('settings.domain', data_get($deliveryServer->settings, 'domain')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                            </div>
                            <div id="mailgun-secret-settings" style="display: none;">
                                <label for="settings_secret_mailgun" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Mailgun API Key') }}</label>
                                <input type="password" name="settings[secret]" id="settings_secret_mailgun" value="" placeholder="{{ __('Leave blank to keep current') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                            </div>

                            <div id="sendgrid-settings" style="display: none;">
                                <label for="settings_api_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('SendGrid API Key') }}</label>
                                <input type="password" name="settings[api_key]" id="settings_api_key" value="" placeholder="{{ __('Leave blank to keep current') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                            </div>

                            <div id="postmark-settings" style="display: none;">
                                <label for="settings_token" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Postmark Token') }}</label>
                                <input type="password" name="settings[token]" id="settings_token" value="" placeholder="{{ __('Leave blank to keep current') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                            </div>

                            <div id="sparkpost-settings" style="display: none;">
                                <label for="settings_secret_sparkpost" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('SparkPost API Key') }}</label>
                                <input type="password" name="settings[secret]" id="settings_secret_sparkpost" value="" placeholder="{{ __('Leave blank to keep current') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                            </div>

                            <div id="ses-key-settings" style="display: none;">
                                <label for="settings_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('SES Key') }}</label>
                                <input type="password" name="settings[key]" id="settings_key" value="" placeholder="{{ __('Leave blank to keep current') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                            </div>
                            <div id="ses-secret-settings" style="display: none;">
                                <label for="settings_secret_ses" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('SES Secret') }}</label>
                                <input type="password" name="settings[secret]" id="settings_secret_ses" value="" placeholder="{{ __('Leave blank to keep current') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                            </div>
                            <div id="ses-region-settings" style="display: none;">
                                <label for="settings_region" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('SES Region') }}</label>
                                <input type="text" name="settings[region]" id="settings_region" value="{{ old('settings.region', data_get($deliveryServer->settings, 'region', 'us-east-1')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                            </div>

                            <div id="zeptomail-api-settings" style="display: none;">
                                <label for="settings_send_mail_token" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('ZeptoMail Send Mail Token') }}</label>
                                <input type="password" name="settings[send_mail_token]" id="settings_send_mail_token" value="" placeholder="{{ __('Leave blank to keep current') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                            </div>
                            <div id="zeptomail-api-mode-settings" style="display: none;">
                                <label for="settings_mode" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('ZeptoMail Mode') }}</label>
                                <select name="settings[mode]" id="settings_mode" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                    @php $mode = old('settings.mode', data_get($deliveryServer->settings, 'mode', 'raw')); @endphp
                                    <option value="raw" {{ $mode === 'raw' ? 'selected' : '' }}>{{ __('Raw HTML/Text') }}</option>
                                    <option value="template" {{ $mode === 'template' ? 'selected' : '' }}>{{ __('Template') }}</option>
                                </select>
                            </div>
                            <div id="zeptomail-api-template-key-settings" style="display: none;">
                                <label for="settings_template_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Template Key') }}</label>
                                <input type="text" name="settings[template_key]" id="settings_template_key" value="{{ old('settings.template_key', data_get($deliveryServer->settings, 'template_key')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                            </div>
                            <div id="zeptomail-api-template-alias-settings" style="display: none;">
                                <label for="settings_template_alias" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Template Alias') }}</label>
                                <input type="text" name="settings[template_alias]" id="settings_template_alias" value="{{ old('settings.template_alias', data_get($deliveryServer->settings, 'template_alias')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                            </div>
                            <div id="zeptomail-api-bounce-address-settings" style="display: none;">
                                <label for="settings_bounce_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Bounce Address') }}</label>
                                <input type="email" name="settings[bounce_address]" id="settings_bounce_address" value="{{ old('settings.bounce_address', data_get($deliveryServer->settings, 'bounce_address')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="from_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('From Email') }}</label>
                    <input type="email" name="from_email" id="from_email" value="{{ old('from_email', $deliveryServer->from_email) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                </div>

                <div>
                    <label for="from_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('From Name') }}</label>
                    <input type="text" name="from_name" id="from_name" value="{{ old('from_name', $deliveryServer->from_name) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                </div>


                <div>
                    <label for="second_quota" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Second Quota (0 = unlimited)') }}</label>
                    <input type="number" name="second_quota" id="second_quota" value="{{ old('second_quota', $deliveryServer->second_quota ?? 0) }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                </div>

                <div>
                    <label for="minute_quota" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Minute Quota (0 = unlimited)') }}</label>
                    <input type="number" name="minute_quota" id="minute_quota" value="{{ old('minute_quota', $deliveryServer->minute_quota ?? 0) }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                </div>

                <div>
                    <label for="hourly_quota" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Hourly Quota (0 = unlimited)') }}</label>
                    <input type="number" name="hourly_quota" id="hourly_quota" value="{{ old('hourly_quota', $deliveryServer->hourly_quota) }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                </div>

                <div>
                    <label for="daily_quota" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Daily Quota (0 = unlimited)') }}</label>
                    <input type="number" name="daily_quota" id="daily_quota" value="{{ old('daily_quota', $deliveryServer->daily_quota) }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                </div>

                <div>
                    <label for="monthly_quota" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Monthly Quota (0 = unlimited)') }}</label>
                    <input type="number" name="monthly_quota" id="monthly_quota" value="{{ old('monthly_quota', $deliveryServer->monthly_quota) }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                </div>

                <div class="sm:col-span-2">
                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Notes') }}</label>
                    <textarea name="notes" id="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">{{ old('notes', $deliveryServer->notes) }}</textarea>
                </div>

                <div class="sm:col-span-2">
                    <div class="flex items-center space-x-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="use_for" value="1" {{ old('use_for', $deliveryServer->use_for) ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('Use for campaigns') }}</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="use_for_transactional" value="1" {{ old('use_for_transactional', $deliveryServer->use_for_transactional) ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('Use for transactional') }}</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="locked" value="1" {{ old('locked', $deliveryServer->locked) ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('Locked') }}</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <x-button href="{{ route('admin.delivery-servers.show', $deliveryServer) }}" variant="secondary">{{ __('Cancel') }}</x-button>
                <x-button type="submit" variant="primary">{{ __('Update Server') }}</x-button>
            </div>
        </form>
    </x-card>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeEl = document.getElementById('type');
    const hostEl = document.getElementById('hostname');
    const portEl = document.getElementById('port');
    const encEl = document.getElementById('encryption');
    const providerSettings = document.getElementById('provider-settings');
    const mailgunSettings = document.getElementById('mailgun-settings');
    const mailgunSecretSettings = document.getElementById('mailgun-secret-settings');
    const sendgridSettings = document.getElementById('sendgrid-settings');
    const postmarkSettings = document.getElementById('postmark-settings');
    const sparkpostSettings = document.getElementById('sparkpost-settings');
    const sesKeySettings = document.getElementById('ses-key-settings');
    const sesSecretSettings = document.getElementById('ses-secret-settings');
    const sesRegionSettings = document.getElementById('ses-region-settings');
    const zeptoMailApiSettings = document.getElementById('zeptomail-api-settings');
    const zeptoMailApiModeSettings = document.getElementById('zeptomail-api-mode-settings');
    const zeptoMailApiTemplateKeySettings = document.getElementById('zeptomail-api-template-key-settings');
    const zeptoMailApiTemplateAliasSettings = document.getElementById('zeptomail-api-template-alias-settings');
    const zeptoMailApiBounceAddressSettings = document.getElementById('zeptomail-api-bounce-address-settings');

    if (!typeEl || !hostEl || !portEl || !encEl) {
        return;
    }

    const defaults = {
        'smtp': { host: '', port: 587, enc: 'tls' },
        'sendmail': { host: '', port: '', enc: 'none' },
        'zeptomail': { host: 'smtp.zeptomail.com', port: 587, enc: 'tls' },
        'amazon-ses': { host: 'email-smtp.us-east-1.amazonaws.com', port: 587, enc: 'tls' },
        'mailgun': { host: 'smtp.mailgun.org', port: 587, enc: 'tls' },
        'sendgrid': { host: 'smtp.sendgrid.net', port: 587, enc: 'tls' },
        'postmark': { host: 'smtp.postmarkapp.com', port: 587, enc: 'tls' },
        'sparkpost': { host: 'smtp.sparkpostmail.com', port: 587, enc: 'tls' },
        'zeptomail-api': { host: '', port: 587, enc: 'tls' },
    };

    function getDef(type) {
        return defaults[type] || { host: '', port: 587, enc: 'tls' };
    }

    function applyDefaults(previousType) {
        const type = typeEl.value;
        const def = getDef(type);
        const prev = previousType ? getDef(previousType) : null;

        const hostVal = (hostEl.value || '').trim();
        if (hostVal === '' || (prev && hostVal === (prev.host || ''))) {
            hostEl.value = def.host;
        }

        const portVal = (portEl.value || '').toString().trim();
        const prevPort = prev ? (prev.port || '').toString() : null;
        if (portVal === '' || (prevPort !== null && portVal === prevPort)) {
            portEl.value = def.port;
        }

        const encVal = (encEl.value || '').trim();
        if (encVal === '' || (prev && encVal === (prev.enc || ''))) {
            encEl.value = def.enc;
        }

        if (providerSettings) {
            const typeNeedsSettings = ['mailgun', 'sendgrid', 'postmark', 'sparkpost', 'amazon-ses', 'zeptomail-api'].includes(type);
            providerSettings.style.display = typeNeedsSettings ? 'block' : 'none';
            if (mailgunSettings) mailgunSettings.style.display = type === 'mailgun' ? 'block' : 'none';
            if (mailgunSecretSettings) mailgunSecretSettings.style.display = type === 'mailgun' ? 'block' : 'none';
            if (sendgridSettings) sendgridSettings.style.display = type === 'sendgrid' ? 'block' : 'none';
            if (postmarkSettings) postmarkSettings.style.display = type === 'postmark' ? 'block' : 'none';
            if (sparkpostSettings) sparkpostSettings.style.display = type === 'sparkpost' ? 'block' : 'none';
            if (sesKeySettings) sesKeySettings.style.display = type === 'amazon-ses' ? 'block' : 'none';
            if (sesSecretSettings) sesSecretSettings.style.display = type === 'amazon-ses' ? 'block' : 'none';
            if (sesRegionSettings) sesRegionSettings.style.display = type === 'amazon-ses' ? 'block' : 'none';
            if (zeptoMailApiSettings) zeptoMailApiSettings.style.display = type === 'zeptomail-api' ? 'block' : 'none';
            if (zeptoMailApiModeSettings) zeptoMailApiModeSettings.style.display = type === 'zeptomail-api' ? 'block' : 'none';
            if (zeptoMailApiTemplateKeySettings) zeptoMailApiTemplateKeySettings.style.display = type === 'zeptomail-api' ? 'block' : 'none';
            if (zeptoMailApiTemplateAliasSettings) zeptoMailApiTemplateAliasSettings.style.display = type === 'zeptomail-api' ? 'block' : 'none';
            if (zeptoMailApiBounceAddressSettings) zeptoMailApiBounceAddressSettings.style.display = type === 'zeptomail-api' ? 'block' : 'none';
        }

    }

    let lastType = typeEl.value;

    typeEl.addEventListener('change', function () {
        applyDefaults(lastType);
        lastType = typeEl.value;
    });

    applyDefaults(lastType);
});
</script>
@endpush
@endsection

