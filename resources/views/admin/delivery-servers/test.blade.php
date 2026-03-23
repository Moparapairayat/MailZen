@extends('layouts.admin')

@section('title', __('Test SMTP/API Connection'))
@section('page-title', __('Test SMTP/API Connection'))

@section('content')
<div class="max-w-4xl">
    <x-card title="{{ __('Test Email Delivery') }}">
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
            {{ __('Test your SMTP or API email delivery configuration by sending a test email. You can either use an existing delivery server or enter manual credentials.') }}
        </p>

        <form id="test-form" method="POST" action="{{ route('admin.delivery-servers.test.send') }}" class="space-y-6">
            @csrf

            <!-- Test Type Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('Test Type') }}</label>
                <div class="flex gap-4">
                    <label class="flex items-center">
                        <input type="radio" name="test_type" value="server" checked class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700" onchange="toggleTestType()">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('Use Existing Server') }}</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="test_type" value="manual" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700" onchange="toggleTestType()">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('Manual Configuration') }}</span>
                    </label>
                </div>
            </div>

            <!-- Server Selection -->
            <div id="server-selection">
                <label for="server_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('Select Delivery Server') }}
                </label>
                <select name="server_id" id="server_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    <option value="">{{ __('Select a server...') }}</option>
                    @foreach($deliveryServers as $server)
                        <option value="{{ $server->id }}">{{ $server->name }} ({{ ucfirst($server->type) }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Manual Configuration -->
            <div id="manual-config" style="display: none;">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Server Type') }} <span class="text-red-500">*</span>
                        </label>
                        <select name="type" id="type" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm" onchange="toggleManualFields()">
                            <option value="smtp">{{ __('SMTP (Generic)') }}</option>
                            <option value="zeptomail">{{ __('ZeptoMail (SMTP)') }}</option>
                            <option value="mailgun">{{ __('Mailgun (SMTP or API)') }}</option>
                            <option value="mailjet">{{ __('Mailjet') }}</option>
                            <option value="sendgrid">{{ __('SendGrid') }}</option>
                            <option value="postmark">{{ __('Postmark') }}</option>
                            <option value="amazon-ses">{{ __('Amazon SES') }}</option>
                            <option value="sparkpost">{{ __('SparkPost') }}</option>
                            <option value="sendmail">{{ __('Sendmail') }}</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">{{ __('For Mailgun: Use SMTP fields below for SMTP, or API fields for API method') }}</p>
                    </div>

                    <!-- SMTP Fields -->
                    <div id="smtp-fields">
                        <div>
                            <label for="hostname" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Hostname') }}</label>
                            <input type="text" name="hostname" id="hostname" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        </div>
                        <div>
                            <label for="port" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Port') }}</label>
                            <input type="number" name="port" id="port" value="587" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        </div>
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Username') }}</label>
                            <input type="text" name="username" id="username" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Password') }}</label>
                            <input type="password" name="password" id="password" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        </div>
                        <div>
                            <label for="encryption" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Encryption') }}</label>
                            <select name="encryption" id="encryption" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                <option value="tls">{{ __('TLS') }}</option>
                                <option value="ssl">{{ __('SSL') }}</option>
                                <option value="none">{{ __('None') }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- API Fields -->
                    <div id="api-fields" style="display: none;">
                        <div>
                            <label for="api_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('API Key') }}</label>
                            <input type="text" name="api_key" id="api_key" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        </div>
                        <div id="api-secret-field" style="display: none;">
                            <label for="api_secret" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('API Secret') }}</label>
                            <input type="password" name="api_secret" id="api_secret" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        </div>
                        <div id="api-hostname-field" style="display: none;">
                            <label for="api_hostname" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Domain/Region') }}</label>
                            <input type="text" name="api_hostname" id="api_hostname" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                            <p class="mt-1 text-xs text-gray-500">{{ __('For Mailgun: domain, For SES: region') }}</p>
                        </div>
                    </div>

                    <!-- Mailjet Fields (uses SMTP) -->
                    <div id="mailjet-fields" style="display: none;">
                        <div>
                            <label for="mailjet_hostname" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Hostname') }}</label>
                            <input type="text" name="hostname" id="mailjet_hostname" value="in-v3.mailjet.com" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        </div>
                        <div>
                            <label for="mailjet_port" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Port') }}</label>
                            <input type="number" name="port" id="mailjet_port" value="587" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        </div>
                        <div>
                            <label for="mailjet_api_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('API Key (Username)') }}</label>
                            <input type="text" name="api_key" id="mailjet_api_key" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        </div>
                        <div>
                            <label for="mailjet_api_secret" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('API Secret (Password)') }}</label>
                            <input type="password" name="api_secret" id="mailjet_api_secret" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        </div>
                        <div>
                            <label for="mailjet_encryption" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Encryption') }}</label>
                            <select name="encryption" id="mailjet_encryption" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                <option value="tls" selected>{{ __('TLS') }}</option>
                                <option value="ssl">{{ __('SSL') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email Details -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('Email Details') }}</h3>
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="to_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('To Email') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="to_email" id="to_email" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Subject') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="subject" id="subject" value="{{ __('Test Email from MailZen') }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Message') }} <span class="text-red-500">*</span>
                        </label>
                        <textarea name="message" id="message" rows="6" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">{{ __('This is a test email from MailZen. If you received this email, your SMTP/API configuration is working correctly!') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <x-button type="button" onclick="window.location.href='{{ route('admin.delivery-servers.index') }}'" variant="secondary">{{ __('Cancel') }}</x-button>
                <x-button type="submit" variant="primary" id="submit-btn">
                    <span id="submit-text">{{ __('Send Test Email') }}</span>
                    <span id="submit-loading" style="display: none;">{{ __('Sending...') }}</span>
                </x-button>
            </div>
            
            <!-- Result Message -->
            <div id="result-message" style="display: none;" class="mt-4"></div>
        </form>
    </x-card>
</div>

@push('scripts')
<script>
const i18n = {
    response_received: @json(__('Response received')),
    server_type: @json(__('Server Type:')),
    from: @json(__('From:')),
    to: @json(__('To:')),
    subject: @json(__('Subject:')),
    email_sent_successfully: @json(__('✓ Email sent successfully! Please check your inbox.')),
    error_label: @json(__('Error:')),
    error_result_message_not_found: @json(__('Error: Result message element not found. Please refresh the page.')),
    invalid_response_from_server: @json(__('Invalid response from server. Status: :status')),
    unexpected_error_occurred: @json(__('An unexpected error occurred')),
    check_console_and_logs: @json(__('Please check the browser console and server logs for more details.')),
};

function toggleTestType() {
    const testType = document.querySelector('input[name="test_type"]:checked').value;
    const serverSelection = document.getElementById('server-selection');
    const manualConfig = document.getElementById('manual-config');
    
    if (testType === 'server') {
        serverSelection.style.display = 'block';
        manualConfig.style.display = 'none';
    } else {
        serverSelection.style.display = 'none';
        manualConfig.style.display = 'block';
        toggleManualFields();
    }
}

function toggleManualFields() {
    const type = document.getElementById('type').value;
    const smtpFields = document.getElementById('smtp-fields');
    const apiFields = document.getElementById('api-fields');
    const mailjetFields = document.getElementById('mailjet-fields');
    const apiSecretField = document.getElementById('api-secret-field');
    const apiHostnameField = document.getElementById('api-hostname-field');
    
    // Hide all fields first
    smtpFields.style.display = 'none';
    apiFields.style.display = 'none';
    if (mailjetFields) mailjetFields.style.display = 'none';
    
    if (type === 'smtp') {
        smtpFields.style.display = 'block';
    } else if (type === 'mailjet') {
        if (mailjetFields) mailjetFields.style.display = 'block';
    } else {
        apiFields.style.display = 'block';
        
        // Show/hide API secret for SES
        if (type === 'amazon-ses') {
            apiSecretField.style.display = 'block';
            apiHostnameField.style.display = 'block';
        } else {
            apiSecretField.style.display = 'none';
            if (type === 'mailgun' || type === 'amazon-ses') {
                apiHostnameField.style.display = 'block';
            } else {
                apiHostnameField.style.display = 'none';
            }
        }
    }
}

// Wait for DOM to be fully loaded before attaching event listener
function initTestForm() {
    const form = document.getElementById('test-form');
    if (!form) {
        console.error('Form with id "test-form" not found!');
        return;
    }

    if (form.dataset.testEmailBound === '1') {
        return;
    }
    form.dataset.testEmailBound = '1';

    console.log('Attaching submit event listener to form');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('Form submitted - starting email test');
    
    const formData = new FormData(this);
    const submitBtn = document.getElementById('submit-btn');
    const submitText = document.getElementById('submit-text');
    const submitLoading = document.getElementById('submit-loading');
    const resultMessage = document.getElementById('result-message');
    
    if (!resultMessage) {
        console.error('Result message element not found!');
        alert(i18n.error_result_message_not_found);
        return;
    }
    
    // Disable submit button
    submitBtn.disabled = true;
    submitText.style.display = 'none';
    submitLoading.style.display = 'inline';
    resultMessage.style.display = 'none';
    resultMessage.innerHTML = '';
    
    try {
        const response = await fetch('{{ route('admin.delivery-servers.test.send') }}', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: formData
        });
        
        let data;
        try {
            data = await response.json();
        } catch (jsonError) {
            const text = await response.text();
            console.error('Failed to parse JSON response:', text);
            throw new Error(i18n.invalid_response_from_server.replace(':status', response.status));
        }
        
        console.log('Response received:', data);
        console.log('Response status:', response.status);
        
        // Always show the message
        resultMessage.style.display = 'block';
        resultMessage.className = 'p-4 rounded-lg ' + (data.success ? 'bg-green-50 border border-green-200 text-green-800 dark:bg-green-900/50 dark:border-green-800 dark:text-green-200' : 'bg-red-50 border border-red-200 text-red-800 dark:bg-red-900/50 dark:border-red-800 dark:text-red-200');
        
        let messageHtml = '<div class="space-y-2">';
        messageHtml += '<p class="font-medium text-lg">' + (data.message || i18n.response_received) + '</p>';
        
        if (data.success) {
            messageHtml += '<div class="text-sm space-y-1 mt-3">';
            if (data.details) {
                if (data.details.server_type) {
                    messageHtml += '<p><strong>' + i18n.server_type + '</strong> ' + data.details.server_type + '</p>';
                }
                if (data.details.from) {
                    messageHtml += '<p><strong>' + i18n.from + '</strong> ' + data.details.from + '</p>';
                }
                if (data.details.to) {
                    messageHtml += '<p><strong>' + i18n.to + '</strong> ' + data.details.to + '</p>';
                }
                if (data.details.subject) {
                    messageHtml += '<p><strong>' + i18n.subject + '</strong> ' + data.details.subject + '</p>';
                }
            }
            messageHtml += '<p class="mt-3 text-green-700 dark:text-green-300">' + i18n.email_sent_successfully + '</p>';
            messageHtml += '</div>';
        } else {
            if (data.error) {
                messageHtml += '<p class="text-sm mt-2 text-red-600 dark:text-red-400"><strong>' + i18n.error_label + '</strong> ' + data.error + '</p>';
            }
            if (data.errors) {
                messageHtml += '<ul class="text-sm mt-2 list-disc list-inside text-red-600 dark:text-red-400">';
                for (const [key, value] of Object.entries(data.errors)) {
                    messageHtml += '<li>' + key + ': ' + (Array.isArray(value) ? value.join(', ') : value) + '</li>';
                }
                messageHtml += '</ul>';
            }
        }
        messageHtml += '</div>';
        
        resultMessage.innerHTML = messageHtml;
        
        // Scroll to result message
        resultMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        
        if (data.success) {
            // Don't auto-reset on success, let user see the message
            console.log('Email sent successfully!');
        } else {
            console.error('Email sending failed:', data);
        }
    } catch (error) {
        console.error('Error sending test email:', error);
        resultMessage.style.display = 'block';
        resultMessage.className = 'p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 dark:bg-red-900/50 dark:border-red-800 dark:text-red-200';
        resultMessage.innerHTML = '<p class="font-medium">' + i18n.error_label + ' ' + (error.message || i18n.unexpected_error_occurred) + '</p><p class="text-sm mt-2">' + i18n.check_console_and_logs + '</p>';
    } finally {
        submitBtn.disabled = false;
        submitText.style.display = 'inline';
        submitLoading.style.display = 'none';
    }
    });
    
    console.log('Event listener attached successfully');
}

document.addEventListener('DOMContentLoaded', initTestForm);
document.addEventListener('turbo:load', initTestForm);
</script>
@endpush
@endsection

