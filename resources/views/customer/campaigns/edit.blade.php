@extends('layouts.customer')

@section('title', 'Edit Campaign')
@section('page-title', 'Edit Campaign')

@push('styles')
<style>
    #editor-container {
        height: calc(100vh - 310px);
        min-height: 700px;
    }
</style>
@endpush

@section('content')
<div class="max-w-4xl">
    @if(!empty($runPreflightIssues))
        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 text-yellow-900 rounded-lg dark:bg-yellow-900/20 dark:border-yellow-800 dark:text-yellow-100">
            <div class="flex items-start">
                <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold mb-1">Action required before you can run this campaign</h3>
                    <ul class="text-sm list-disc list-inside space-y-1">
                        @foreach($runPreflightIssues as $issue)
                            <li>{{ $issue }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif
    <x-card title="Edit Campaign">
        <form id="unlayer-form" method="POST" action="{{ route('customer.campaigns.update', $campaign) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <input type="hidden" name="wizard_step" id="wizard_step" value="{{ old('wizard_step', request('step', 1)) }}">

            <div class="w-full">
                <div class="flex items-center" data-campaign-stepper>
                    <button type="button" class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-semibold" data-stepper-step="1">1</button>
                    <div class="h-1 flex-1 mx-2 rounded" data-stepper-connector="1"></div>
                    <button type="button" class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-semibold" data-stepper-step="2">2</button>
                    <div class="h-1 flex-1 mx-2 rounded" data-stepper-connector="2"></div>
                    <button type="button" class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-semibold" data-stepper-step="3">3</button>
                    <div class="h-1 flex-1 mx-2 rounded" data-stepper-connector="3"></div>
                    <button type="button" class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-semibold" data-stepper-step="4">4</button>
                </div>
                <div class="mt-2 flex items-start text-xs text-gray-500 dark:text-gray-400">
                    <div class="w-9 text-center">Basics</div>
                    <div class="flex-1 mx-2"></div>
                    <div class="w-9 text-center">Servers</div>
                    <div class="flex-1 mx-2"></div>
                    <div class="w-9 text-center">Content</div>
                    <div class="flex-1 mx-2"></div>
                    <div class="w-9 text-center">Schedule</div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div data-campaign-step="1" class="contents">
                <div class="sm:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Campaign Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name', $campaign->name) }}"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Subject <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="subject"
                        id="subject"
                        value="{{ old('subject', $campaign->subject) }}"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >
                    @error('subject')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="list_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Email List
                    </label>
                    <select
                        name="list_id"
                        id="list_id"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >
                        <option value="">Select a list...</option>
                        @foreach($emailLists as $list)
                            <option value="{{ $list->id }}" {{ old('list_id', $campaign->list_id) == $list->id ? 'selected' : '' }}>
                                {{ $list->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Type
                    </label>
                    <select
                        name="type"
                        id="type"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >
                        <option value="regular" {{ old('type', $campaign->type) == 'regular' ? 'selected' : '' }}>Regular</option>
                        <option value="autoresponder" {{ old('type', $campaign->type) == 'autoresponder' ? 'selected' : '' }}>Auto Responder</option>
                        <option value="recurring" {{ old('type', $campaign->type) == 'recurring' ? 'selected' : '' }}>Recurring</option>
                    </select>
                </div>
                </div>

                <div data-campaign-step="2" class="hidden">
                <div>
                    <label for="delivery_server_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Delivery Server
                    </label>
                    <select
                        name="delivery_server_id"
                        id="delivery_server_id"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >
                        <option value="">Use Default (Auto-select)</option>
                        @foreach($deliveryServers as $server)
                            @php
                                $serverFromEmailValue = $server->from_email;
                                if (is_array($serverFromEmailValue)) {
                                    $serverFromEmailValue = $serverFromEmailValue['address'] ?? $serverFromEmailValue[0] ?? (string) reset($serverFromEmailValue);
                                }
                                $serverFromEmailValue = trim((string) ($serverFromEmailValue ?? ''));
                            @endphp
                            <option value="{{ $server->id }}" data-type="{{ $server->type }}" data-from-email="{{ $serverFromEmailValue }}" {{ old('delivery_server_id', $campaign->delivery_server_id) == $server->id ? 'selected' : '' }}>
                                {{ $server->name }}{{ $server->customer_id ? '' : ' (System)' }} ({{ ucfirst($server->type) }})
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Select a delivery server for this campaign. If not selected, the system will auto-select an active server.
                    </p>
                    @error('delivery_server_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="reply_server_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Reply Server
                    </label>
                    <select
                        name="reply_server_id"
                        id="reply_server_id"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >
                        <option value="">Use Default (Env / Disabled)</option>
                        @foreach($replyServers as $server)
                            <option value="{{ $server->id }}" {{ old('reply_server_id', $campaign->reply_server_id) == $server->id ? 'selected' : '' }}>
                                {{ $server->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Select a reply tracking server for this campaign. If not selected, reply tracking uses the global configuration.
                    </p>
                    @error('reply_server_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="sending_domain_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Sending Domain
                    </label>
                    <select
                        name="sending_domain_id"
                        id="sending_domain_id"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >
                        <option value="">Use Email List's Sending Domain (or Default)</option>
                        @foreach($sendingDomains as $domain)
                            <option value="{{ $domain->id }}" {{ old('sending_domain_id', $campaign->sending_domain_id) == $domain->id ? 'selected' : '' }}>
                                {{ $domain->domain }} {{ $domain->status === 'verified' ? '✓' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Select a verified sending domain for this campaign. If not selected, the email list's sending domain will be used.
                    </p>
                    @error('sending_domain_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tracking_domain_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tracking Domain
                    </label>
                    <select
                        name="tracking_domain_id"
                        id="tracking_domain_id"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >
                        <option value="">Use Default (App Domain)</option>
                        @foreach($trackingDomains as $domain)
                            <option value="{{ $domain->id }}" {{ old('tracking_domain_id', $campaign->tracking_domain_id) == $domain->id ? 'selected' : '' }}>
                                {{ $domain->domain }} {{ $domain->status === 'verified' ? '✓' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Select a verified tracking domain for opens/clicks tracking.
                    </p>
                    @error('tracking_domain_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="bounce_server_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Bounce Server
                    </label>
                    <select
                        name="bounce_server_id"
                        id="bounce_server_id"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >
                        <option value="">Use Default (None)</option>
                        @foreach($bounceServers as $server)
                            <option value="{{ $server->id }}" {{ old('bounce_server_id', $campaign->bounce_server_id) == $server->id ? 'selected' : '' }}>
                                {{ $server->name }}{{ $server->customer_id ? '' : ' (System)' }} ({{ $server->hostname }})
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Select a bounce server to track bounces for this campaign.
                    </p>
                    @error('bounce_server_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                
                <div>
                    <label for="from_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        From Name
                    </label>
                    @php
                        $fromNameValue = old('from_name', $campaign->from_name);
                        if (is_array($fromNameValue)) {
                            $fromNameValue = $fromNameValue['name'] ?? $fromNameValue[0] ?? (string) reset($fromNameValue);
                        }
                        $fromNameValue = (string) ($fromNameValue ?? '');
                    @endphp
                    <input
                        type="text"
                        name="from_name"
                        id="from_name"
                        value="{{ $fromNameValue }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >
                    @error('from_name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="from_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        From Email
                    </label>
                    @php
                        $fromEmailValue = old('from_email', $campaign->from_email);
                        if (is_array($fromEmailValue)) {
                            $fromEmailValue = $fromEmailValue['address'] ?? $fromEmailValue[0] ?? (string) reset($fromEmailValue);
                        }
                        $fromEmailValue = (string) ($fromEmailValue ?? '');
                    @endphp
                    <input
                        type="email"
                        name="from_email"
                        id="from_email"
                        value="{{ $fromEmailValue }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >
                    <div id="ses-from-email-warning" class="mt-2 hidden">
                        <div class="p-3 bg-yellow-50 border border-yellow-200 text-yellow-900 rounded-lg dark:bg-yellow-900/20 dark:border-yellow-800 dark:text-yellow-100 text-sm">
                            It may fail if From email is not verified, Use Amazon SES verified email here.
                        </div>
                    </div>
                    @error('from_email')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                </div>

                <div data-campaign-step="3" class="hidden">
                <div class="sm:col-span-2">
                    <label for="template_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Email Template
                    </label>
                    <select
                        name="template_id"
                        id="template_id"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >
                        <option value="">Select a template...</option>
                        @foreach($templates as $template)
                            <option value="{{ $template->id }}" {{ old('template_id', $campaign->template_id) == $template->id ? 'selected' : '' }}>
                                {{ $template->name }} ({{ ucfirst($template->type) }})
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Select a template to use for this campaign.
                        @customercan('templates.permissions.can_create_templates')
                            You can also <a href="{{ route('customer.templates.unlayer.create') }}" target="_blank" class="text-primary-600 hover:text-primary-700">create a new template</a>.
                        @endcustomercan
                    </p>
                    @error('template_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="signature_template_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Signature
                    </label>
                    <select
                        name="signature_template_id"
                        id="signature_template_id"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >
                        <option value="">None</option>
                        @php
                            $selectedSignatureTemplateId = old('signature_template_id', $campaign->settings['signature_template_id'] ?? '');
                        @endphp
                        @foreach($signatureTemplates as $template)
                            <option value="{{ $template->id }}" {{ (string) $selectedSignatureTemplateId === (string) $template->id ? 'selected' : '' }}>
                                {{ $template->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('signature_template_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="footer_template_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Footer Template
                    </label>
                    <select
                        name="footer_template_id"
                        id="footer_template_id"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >
                        <option value="">None</option>
                        @php
                            $selectedFooterTemplateId = old('footer_template_id', $campaign->settings['footer_template_id'] ?? '');
                        @endphp
                        @foreach($footerTemplates as $template)
                            <option value="{{ $template->id }}" {{ (string) $selectedFooterTemplateId === (string) $template->id ? 'selected' : '' }}>
                                {{ $template->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('footer_template_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <input type="hidden" name="html_content" id="html_content" value="{{ old('html_content', $campaign->html_content) }}">
                <input type="hidden" name="plain_text_content" id="plain_text_content" value="{{ old('plain_text_content', $campaign->plain_text_content) }}">
                @php
                    $templateDataValue = old('template_data', $campaign->template_data);
                    if ($templateDataValue === null) {
                        $templateDataValue = '';
                    }
                    if (is_array($templateDataValue) || is_object($templateDataValue)) {
                        $templateDataValue = json_encode($templateDataValue) ?: '';
                    }
                @endphp
                <input type="hidden" name="template_data" id="grapesjs_data" value="{{ $templateDataValue }}">

                <div class="sm:col-span-2">
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Personalization Tags</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Click a tag to copy</div>
                        </div>
                        <div class="mt-3" data-campaign-tags></div>
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        <script type="application/json" id="unlayer-design-json">@json($unlayerDesign)</script>
                        <div
                            id="editor-container"
                            data-unlayer-editor
                            data-unlayer-display-mode="email"
                            data-unlayer-project-id="{{ $unlayerProjectId }}"
                            data-unlayer-design-script-id="unlayer-design-json"
                        ></div>
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Personalization Tags</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Click a tag to copy</div>
                        </div>
                        <div class="mt-3" data-campaign-tags></div>
                    </div>
                </div>
                </div>

                <div data-campaign-step="4" class="hidden">
                <div>
                    <label for="send_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Send At (Optional)
                    </label>
                    <input
                        type="datetime-local"
                        name="send_at"
                        id="send_at"
                        value="{{ old('send_at', optional($campaign->scheduled_at ?? $campaign->send_at)->format('Y-m-d\TH:i')) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >
                    @error('send_at')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <input type="hidden" name="spam_scoring_enabled" value="1">
                    <div class="flex items-center space-x-6">
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                name="track_opens"
                                value="1"
                                {{ old('track_opens', $campaign->track_opens) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700"
                            >
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Track Opens</span>
                        </label>
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                name="track_clicks"
                                value="1"
                                {{ old('track_clicks', $campaign->track_clicks) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700"
                            >
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Track Clicks</span>
                        </label>
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                name="enable_spintax"
                                value="1"
                                {{ old('enable_spintax', $campaign->settings['enable_spintax'] ?? false) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700"
                            >
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Enable Spintax</span>
                        </label>
                    </div>
                    
                    {{-- Spintax Help --}}
                    <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-md dark:bg-blue-900/20 dark:border-blue-800 hidden" id="spintax-help">
                        <p class="text-sm text-blue-800 dark:text-blue-200">
                            <strong>Spintax Format:</strong> Use {option1|option2|option3} to randomly select different text for each email.<br>
                            Example: {Hello|Hi|Hey} {there|world}, {check out|see|discover} our {amazing|great|fantastic} {offer|deal|promotion}!
                        </p>
                    </div>
                    
                    {{-- Spam Score Help --}}
                    <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-md dark:bg-yellow-900/20 dark:border-yellow-800" id="spam-help">
                        <div class="flex items-start gap-3">
                            <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                <strong>Spam Scoring:</strong> Analyze your current subject + content before sending.<br>
                                Blocking threshold: {{ config('mailzen.spam_scoring.blocking_threshold', 15) }} points
                            </p>
                        </div>

                        <div id="spam-check-loading" class="hidden mt-3 text-sm text-yellow-700 dark:text-yellow-300">
                            Checking spam score...
                        </div>

                        <div id="spam-check-error" class="hidden mt-3 p-2 text-sm rounded border border-red-200 bg-red-50 text-red-700 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-200"></div>

                        <div id="spam-check-results" class="hidden mt-3 space-y-3">
                            <div class="p-3 rounded border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800/60">
                                <div class="grid grid-cols-1 sm:grid-cols-[180px_minmax(0,1fr)] gap-4 items-center">
                                    <div class="mx-auto w-full max-w-[180px]">
                                        <svg viewBox="0 0 180 110" class="w-full h-auto" aria-label="Spam score speedometer">
                                            <path d="M 20 90 A 70 70 0 0 1 68.4 23.4" stroke="#34d399" stroke-width="14" fill="none" stroke-linecap="butt" />
                                            <path d="M 68.4 23.4 A 70 70 0 0 1 131.1 33.4" stroke="#f59e0b" stroke-width="14" fill="none" stroke-linecap="butt" />
                                            <path d="M 131.1 33.4 A 70 70 0 0 1 160 90" stroke="#f87171" stroke-width="14" fill="none" stroke-linecap="butt" />

                                            <path d="M 66.7 25.7 L 70.1 21.1" stroke="#f3f4f6" stroke-width="2" stroke-linecap="round" />
                                            <path d="M 129.2 35.9 L 133.0 30.9" stroke="#f3f4f6" stroke-width="2" stroke-linecap="round" />

                                            <text x="16" y="92" font-size="9" font-weight="600" fill="#64748b">0</text>
                                            <text x="66" y="18" font-size="9" font-weight="600" fill="#64748b">2</text>
                                            <text x="133" y="30" font-size="9" font-weight="600" fill="#64748b">3.5</text>
                                            <text x="162" y="92" font-size="9" font-weight="600" fill="#64748b">5</text>

                                            <line id="spam-gauge-needle" x1="90" y1="90" x2="28" y2="72" stroke="#64748b" stroke-width="3.5" stroke-linecap="round" />
                                            <polygon id="spam-gauge-needle-tail" points="84,90 92,90 95,96 86,98" fill="#64748b" />
                                            <circle cx="90" cy="90" r="4.5" fill="#64748b" />

                                            <g id="spam-gauge-badge" transform="translate(20 72)">
                                                <circle r="12" fill="#34d399" />
                                                <text id="spam-gauge-badge-text" x="0" y="3" text-anchor="middle" font-size="8" font-weight="700" fill="#ffffff">0.0</text>
                                            </g>
                                        </svg>
                                        <div class="-mt-2 text-center">
                                            <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Spam Score</div>
                                            <div class="text-xl font-bold text-gray-900 dark:text-gray-100"><span id="spam-score-percent">0.0/5</span></div>
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <div>
                                            <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Risk Level</div>
                                            <div id="spam-risk-badge" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold">Unknown</div>
                                        </div>
                                        <div>
                                            <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Internal Score</div>
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300"><span id="spam-score-points">0</span> points</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="spam-overall-recommendation" class="hidden p-3 rounded border text-sm"></div>

                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Checks & Remarks</h4>
                                <div id="spam-check-list" class="mt-2 space-y-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <x-button type="button" variant="secondary" data-wizard-prev>Back</x-button>
                </div>
                <div class="flex items-center gap-3">
                    <x-button href="{{ route('customer.campaigns.show', $campaign) }}" variant="secondary">Cancel</x-button>
                    <x-button type="button" variant="primary" data-wizard-next>Next</x-button>
                    @customercan('campaigns.permissions.can_edit_campaigns')
                        <x-button type="button" id="btn-save" variant="primary">Update Campaign</x-button>
                    @endcustomercan
                </div>
            </div>
        </form>
    </x-card>
</div>

@push('scripts')
<script>
// Initialize help toggles for spintax and spam scoring
const initFeatureHelp = () => {
    const spintaxCheckbox = document.querySelector('input[name="enable_spintax"]');
    const spintaxHelp = document.getElementById('spintax-help');
    const spamHelp = document.getElementById('spam-help');

    if (spintaxCheckbox && spintaxHelp) {
        const toggleSpintaxHelp = () => {
            spintaxHelp.classList.toggle('hidden', !spintaxCheckbox.checked);
        };
        spintaxCheckbox.addEventListener('change', toggleSpintaxHelp);
        toggleSpintaxHelp(); // Set initial state
    }

    if (spamHelp) {
        spamHelp.classList.remove('hidden');
    }
};

const initSpamScoreChecker = () => {
    const form = document.getElementById('unlayer-form');
    const loadingEl = document.getElementById('spam-check-loading');
    const errorEl = document.getElementById('spam-check-error');
    const resultsEl = document.getElementById('spam-check-results');
    const percentEl = document.getElementById('spam-score-percent');
    const pointsEl = document.getElementById('spam-score-points');
    const gaugeNeedleEl = document.getElementById('spam-gauge-needle');
    const gaugeNeedleTailEl = document.getElementById('spam-gauge-needle-tail');
    const gaugeBadgeEl = document.getElementById('spam-gauge-badge');
    const gaugeBadgeTextEl = document.getElementById('spam-gauge-badge-text');
    const badgeEl = document.getElementById('spam-risk-badge');
    const checkListEl = document.getElementById('spam-check-list');
    const recommendationEl = document.getElementById('spam-overall-recommendation');

    if (!form || !resultsEl || !checkListEl || !recommendationEl) {
        return;
    }

    if (form.dataset.spamCheckerBound === '1') {
        return;
    }
    form.dataset.spamCheckerBound = '1';

    const routeUrl = '{{ route('customer.campaigns.spam-preview') }}';
    const csrf = (form.querySelector('input[name="_token"]') || {}).value || '';
    let autoCheckTimer = null;
    let activeRequestId = 0;

    const toneToBadgeClasses = {
        positive: 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200',
        warning: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-200',
        danger: 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200',
    };

    const toneToRowClasses = {
        positive: 'border-green-200 bg-green-50 dark:border-green-900/50 dark:bg-green-900/20',
        warning: 'border-yellow-200 bg-yellow-50 dark:border-yellow-900/50 dark:bg-yellow-900/20',
        danger: 'border-red-200 bg-red-50 dark:border-red-900/50 dark:bg-red-900/20',
    };

    const toneToRecommendationClasses = {
        positive: 'border-green-200 bg-green-50 text-green-800 dark:border-green-900/50 dark:bg-green-900/20 dark:text-green-200',
        warning: 'border-yellow-200 bg-yellow-50 text-yellow-800 dark:border-yellow-900/50 dark:bg-yellow-900/20 dark:text-yellow-200',
        danger: 'border-red-200 bg-red-50 text-red-800 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-200',
    };

    const escapeHtml = (value) => String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');

    const collectCampaignContent = () => {
        const subject = (document.getElementById('subject') || {}).value || '';
        const htmlInput = document.getElementById('html_content');
        const plainInput = document.getElementById('plain_text_content');
        const deliverySelect = document.getElementById('delivery_server_id');
        const deliveryOption = deliverySelect ? deliverySelect.options[deliverySelect.selectedIndex] : null;
        const replyServerSelect = document.getElementById('reply_server_id');

        const payload = {
            subject,
            html_content: htmlInput ? String(htmlInput.value || '') : '',
            plain_text_content: plainInput ? String(plainInput.value || '') : '',
            from_name: ((document.getElementById('from_name') || {}).value || '').trim(),
            from_email: ((document.getElementById('from_email') || {}).value || '').trim(),
            reply_to: ((document.getElementById('reply_to') || {}).value || '').trim(),
            delivery_server_id: deliverySelect ? String(deliverySelect.value || '').trim() : '',
            delivery_server_type: deliveryOption ? String(deliveryOption.dataset.type || '').trim() : '',
            delivery_server_from_email: deliveryOption ? String(deliveryOption.dataset.fromEmail || '').trim() : '',
            reply_server_id: replyServerSelect ? String(replyServerSelect.value || '').trim() : '',
        };

        if (!window.unlayer || typeof window.unlayer.exportHtml !== 'function') {
            return Promise.resolve(payload);
        }

        return new Promise((resolve) => {
            let settled = false;
            const finish = () => {
                if (settled) {
                    return;
                }
                settled = true;
                resolve(payload);
            };

            const timeoutId = window.setTimeout(() => {
                finish();
            }, 1800);

            try {
                window.unlayer.exportHtml((data) => {
                    payload.html_content = String((data && data.html) ? data.html : payload.html_content);
                    const derivedPlain = payload.html_content
                        .replace(/<style[\s\S]*?<\/style>/gi, ' ')
                        .replace(/<script[\s\S]*?<\/script>/gi, ' ')
                        .replace(/<[^>]+>/g, ' ')
                        .replace(/\s+/g, ' ')
                        .trim();

                    payload.plain_text_content = derivedPlain || payload.plain_text_content;
                    if (htmlInput) htmlInput.value = payload.html_content;
                    if (plainInput) plainInput.value = payload.plain_text_content;

                    if (settled) {
                        return;
                    }
                    window.clearTimeout(timeoutId);
                    finish();
                });
            } catch (e) {
                window.clearTimeout(timeoutId);
                finish();
            }
        });
    };

    const setLoading = (loading) => {
        loadingEl.classList.toggle('hidden', !loading);
    };

    const renderGauge = (scorePercent) => {
        const percent = Math.max(0, Math.min(100, Number(scorePercent || 0)));
        const scaleValue = Math.max(0, Math.min(5, percent / 20));
        const angleDeg = 180 - ((scaleValue / 5) * 180);
        const angleRad = (angleDeg * Math.PI) / 180;

        const tipX = 90 + (62 * Math.cos(angleRad));
        const tipY = 90 - (62 * Math.sin(angleRad));

        if (gaugeNeedleEl) {
            gaugeNeedleEl.setAttribute('x1', '90');
            gaugeNeedleEl.setAttribute('y1', '90');
            gaugeNeedleEl.setAttribute('x2', `${tipX.toFixed(1)}`);
            gaugeNeedleEl.setAttribute('y2', `${tipY.toFixed(1)}`);
        }

        if (gaugeNeedleTailEl) {
            const tailAngle = (angleDeg + 180) * Math.PI / 180;
            const baseX = 90 + 6 * Math.cos(tailAngle);
            const baseY = 90 - 6 * Math.sin(tailAngle);
            gaugeNeedleTailEl.setAttribute('points', `${baseX.toFixed(1)},${baseY.toFixed(1)} 92,90 95,96 86,98`);
        }

        if (gaugeBadgeEl) {
            const bubbleX = 90 + (78 * Math.cos(angleRad));
            const bubbleY = 90 - (78 * Math.sin(angleRad));
            gaugeBadgeEl.setAttribute('transform', `translate(${bubbleX.toFixed(1)} ${bubbleY.toFixed(1)})`);
        }

        if (gaugeBadgeTextEl) {
            gaugeBadgeTextEl.textContent = scaleValue.toFixed(1);
        }

        percentEl.textContent = `${scaleValue.toFixed(1)}/5`;
    };

    const showError = (message) => {
        errorEl.textContent = message;
        errorEl.classList.remove('hidden');
    };

    const hideError = () => {
        errorEl.classList.add('hidden');
        errorEl.textContent = '';
    };

    const hideResults = () => {
        resultsEl.classList.add('hidden');
        recommendationEl.classList.add('hidden');
    };

    const renderRecommendation = (result, tone) => {
        const threshold = Number(result.blocking_threshold || 0);
        const score = Number(result.score || 0);
        const shouldBlock = Boolean(result.should_block);
        const recommendationClass = toneToRecommendationClasses[tone] || toneToRecommendationClasses.warning;

        recommendationEl.className = `p-3 rounded border text-sm ${recommendationClass}`;

        if (shouldBlock) {
            recommendationEl.textContent = `Recommendation: High spam risk. Reduce trigger words/formatting and bring score below ${threshold} before sending.`;
            recommendationEl.classList.remove('hidden');
            return;
        }

        if (tone === 'warning') {
            recommendationEl.textContent = `Recommendation: Medium spam risk. Improve a few highlighted items to stay comfortably below the ${threshold}-point threshold.`;
            recommendationEl.classList.remove('hidden');
            return;
        }

        recommendationEl.textContent = score <= 0
            ? 'Recommendation: Looks clean. You can proceed and still run a final check after any content edits.'
            : 'Recommendation: Safe to send. Keep current subject/content style for better deliverability.';
        recommendationEl.classList.remove('hidden');
    };

    const renderResults = (result) => {
        const tone = String(result.risk_tone || '').toLowerCase() || 'warning';
        const badgeClass = toneToBadgeClasses[tone] || toneToBadgeClasses.warning;

        pointsEl.textContent = String(result.score || 0);
        renderGauge(result.score_percent || 0);

        badgeEl.className = `inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold ${badgeClass}`;
        badgeEl.textContent = result.assessment || 'Unknown';

        const checks = Array.isArray(result.checks) ? result.checks : [];
        checkListEl.innerHTML = checks.map((check) => {
            const checkTone = String(check.tone || 'warning').toLowerCase();
            const rowClass = toneToRowClasses[checkTone] || toneToRowClasses.warning;
            const remarks = Array.isArray(check.remarks) ? check.remarks : [];
            const safeLabel = escapeHtml(check.label || 'Check');

            return `
                <div class="p-3 rounded border ${rowClass}">
                    <div class="flex items-center justify-between gap-2">
                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">${safeLabel}</div>
                        <div class="text-xs font-medium text-gray-700 dark:text-gray-300">+${Number(check.score || 0)} pts</div>
                    </div>
                    <ul class="mt-2 space-y-1">
                        ${remarks.map((remark) => {
                            const remarkTone = String((remark && remark.tone) || 'warning').toLowerCase();
                            const icon = remarkTone === 'danger' ? '[!]' : (remarkTone === 'positive' ? '[OK]' : '[-]');
                            const text = escapeHtml((remark && remark.text) || '');
                            return `<li class="text-xs text-gray-700 dark:text-gray-300">${icon} ${text}</li>`;
                        }).join('')}
                    </ul>
                </div>
            `;
        }).join('');

        renderRecommendation(result, tone);
        resultsEl.classList.remove('hidden');
    };

    const runSpamCheck = async () => {
        hideError();
        setLoading(true);
        const requestId = ++activeRequestId;

        try {
            const payload = await collectCampaignContent();

            const response = await fetch(routeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json();
            if (!response.ok || !data || !data.result) {
                throw new Error((data && data.message) ? data.message : 'Failed to calculate spam score.');
            }

            if (requestId !== activeRequestId) {
                return;
            }

            renderResults(data.result);
        } catch (error) {
            showError(error && error.message ? error.message : 'Spam checker failed. Please try again.');
        } finally {
            if (requestId === activeRequestId) {
                setLoading(false);
            }
        }
    };

    const scheduleAutoCheck = () => {
        if (autoCheckTimer) {
            clearTimeout(autoCheckTimer);
        }
        autoCheckTimer = setTimeout(() => {
            runSpamCheck();
        }, 700);
    };

    [
        'subject',
        'from_name',
        'from_email',
        'reply_to',
        'delivery_server_id',
        'reply_server_id',
        'template_id',
        'list_id',
    ].forEach((id) => {
        const field = document.getElementById(id);
        if (!field) {
            return;
        }

        const eventName = field.tagName === 'SELECT' ? 'change' : 'input';
        field.addEventListener(eventName, scheduleAutoCheck);
        if (eventName !== 'change') {
            field.addEventListener('change', scheduleAutoCheck);
        }
    });

    document.addEventListener('campaign:spam-input-changed', scheduleAutoCheck);

    const bindUnlayerAutoCheck = () => {
        if (!window.unlayer || typeof window.unlayer.addEventListener !== 'function') {
            return false;
        }

        if (window.__mailpurseSpamUnlayerBound === true) {
            return true;
        }

        window.unlayer.addEventListener('design:updated', () => {
            scheduleAutoCheck();
        });
        window.__mailpurseSpamUnlayerBound = true;
        return true;
    };

    if (!bindUnlayerAutoCheck()) {
        let attempts = 0;
        const poll = setInterval(() => {
            attempts += 1;
            if (bindUnlayerAutoCheck() || attempts > 80) {
                clearInterval(poll);
            }
        }, 250);
    }

    hideResults();
    runSpamCheck();
};

const initCampaignTemplateLoader = () => {
    const templateSelect = document.getElementById('template_id');
    const htmlContent = document.getElementById('html_content');
    const plainTextContent = document.getElementById('plain_text_content');
    const designInput = document.getElementById('grapesjs_data');
    const baseUrl = '{{ url("/customer/templates") }}';

    if (!templateSelect) {
        return;
    }

    if (templateSelect.dataset.templateLoaderBound === '1') {
        return;
    }
    templateSelect.dataset.templateLoaderBound = '1';

    const waitForUnlayer = (timeoutMs = 20000) => new Promise((resolve, reject) => {
        const start = Date.now();
        const tick = () => {
            if (window.__mailpurseUnlayerReady === true && window.unlayer && typeof window.unlayer.loadDesign === 'function') {
                resolve();
                return;
            }
            if (Date.now() - start >= timeoutMs) {
                reject(new Error('Unlayer editor is not ready yet.'));
                return;
            }
            setTimeout(tick, 75);
        };
        tick();
    });

    const ensureUnlayerInitVisible = () => {
        try {
            if (typeof window.__mailpurseSetupUnlayerEditors === 'function') {
                window.__mailpurseSetupUnlayerEditors();
            }
        } catch (e) {
        }
    };

    const loadTemplate = (templateId) => {
        if (!templateId) {
            if (htmlContent) {
                htmlContent.value = '';
            }
            if (plainTextContent) {
                plainTextContent.value = '';
            }
            if (designInput) {
                designInput.value = '';
            }
            window.__mailpurseUnlayerPendingDesign = null;
            document.dispatchEvent(new CustomEvent('campaign:spam-input-changed'));
            return;
        }

        fetch(`${baseUrl}/${templateId}/content`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (htmlContent && data.html_content) {
                htmlContent.value = data.html_content;
            }
            if (plainTextContent && data.plain_text_content) {
                plainTextContent.value = data.plain_text_content;
            }

            document.dispatchEvent(new CustomEvent('campaign:spam-input-changed'));

            if (data && data.builder === 'unlayer' && data.builder_data) {
                const design = data.builder_data;
                if (designInput) {
                    try {
                        designInput.value = JSON.stringify(design);
                    } catch (e) {
                        designInput.value = '';
                    }
                }

                window.__mailpurseUnlayerPendingDesign = design;
                ensureUnlayerInitVisible();

                if (window.__mailpurseUnlayerReady === true && window.unlayer && typeof window.unlayer.loadDesign === 'function') {
                    try {
                        window.unlayer.loadDesign(design);
                        window.__mailpurseUnlayerPendingDesign = null;
                    } catch (e) {
                    }
                    return;
                }

                waitForUnlayer()
                    .then(() => {
                        try {
                            window.unlayer.loadDesign(design);
                            window.__mailpurseUnlayerPendingDesign = null;
                        } catch (e) {
                        }
                    })
                    .catch(() => {
                    });
            }
        })
        .catch(error => {
            console.error('Error loading template:', error);
        });
    };

    templateSelect.addEventListener('change', function() {
        loadTemplate(this.value);
    });

    if (templateSelect.value) {
        loadTemplate(templateSelect.value);
    }
};

document.addEventListener('DOMContentLoaded', initCampaignTemplateLoader);
document.addEventListener('turbo:load', initCampaignTemplateLoader);

const initCampaignSesFromEmail = () => {
    const deliverySelect = document.getElementById('delivery_server_id');
    const fromEmailInput = document.getElementById('from_email');
    const warning = document.getElementById('ses-from-email-warning');

    if (!deliverySelect || !fromEmailInput || !warning) {
        return;
    }

    if (deliverySelect.dataset.sesFromEmailBound === '1') {
        return;
    }
    deliverySelect.dataset.sesFromEmailBound = '1';

    const isSesServer = (optionEl) => {
        const type = optionEl ? String(optionEl.dataset.type || '') : '';
        return type === 'amazon-ses';
    };

    const getServerFromEmail = (optionEl) => {
        const val = optionEl ? String(optionEl.dataset.fromEmail || '') : '';
        return val.trim();
    };

    const updateState = (opts = {}) => {
        const selectedOption = deliverySelect.options[deliverySelect.selectedIndex] || null;
        const isSes = isSesServer(selectedOption);
        const serverFrom = getServerFromEmail(selectedOption);

        if (isSes && serverFrom && opts.applyServerFromEmail) {
            fromEmailInput.value = serverFrom;
        }

        if (!isSes) {
            warning.classList.add('hidden');
            return;
        }

        const currentFrom = String(fromEmailInput.value || '').trim().toLowerCase();
        const expectedFrom = String(serverFrom || '').trim().toLowerCase();

        if (!expectedFrom) {
            warning.classList.remove('hidden');
            return;
        }

        if (currentFrom && currentFrom !== expectedFrom) {
            warning.classList.remove('hidden');
            return;
        }

        warning.classList.add('hidden');
    };

    deliverySelect.addEventListener('change', function () {
        updateState({ applyServerFromEmail: true });
    });

    fromEmailInput.addEventListener('input', function () {
        updateState({ applyServerFromEmail: false });
    });

    updateState({ applyServerFromEmail: false });
};

document.addEventListener('DOMContentLoaded', initCampaignSesFromEmail);
document.addEventListener('turbo:load', initCampaignSesFromEmail);

// Initialize feature help
document.addEventListener('DOMContentLoaded', initFeatureHelp);
document.addEventListener('turbo:load', initFeatureHelp);
document.addEventListener('DOMContentLoaded', initSpamScoreChecker);
document.addEventListener('turbo:load', initSpamScoreChecker);
</script>

<script type="application/json" id="campaign-tags-json">@json($campaignTagsByList)</script>
<script>
const initCampaignPersonalizationTags = () => {
    const listSelect = document.getElementById('list_id');
    const containers = document.querySelectorAll('[data-campaign-tags]');
    if (!containers.length) {
        return;
    }

    if (containers[0].dataset.tagsBound === '1') {
        return;
    }
    containers.forEach((c) => { c.dataset.tagsBound = '1'; });

    const jsonEl = document.getElementById('campaign-tags-json');
    let byList = {};
    try {
        byList = jsonEl ? JSON.parse((jsonEl.textContent || '').trim() || '{}') : {};
    } catch (e) {
        byList = {};
    }

    function fallbackCopyText(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.setAttribute('readonly', '');
        textarea.style.position = 'fixed';
        textarea.style.top = '-9999px';
        textarea.style.left = '-9999px';
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
        } finally {
            textarea.remove();
        }
    }

    async function copyText(text) {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(text);
            return;
        }
        fallbackCopyText(text);
    }

    function renderTags() {
        const listId = listSelect ? String(listSelect.value || '') : '';
        const config = (byList && listId && byList[listId] && typeof byList[listId] === 'object') ? byList[listId] : null;
        const standard = config && Array.isArray(config.standard) ? config.standard : [];
        const custom = config && Array.isArray(config.custom) ? config.custom : [];
        const tags = [...standard, ...custom];

        const html = `
            <div class='flex flex-wrap gap-2'>
                ${tags.map((t, i) => {
                    const safeLabel = String(t.label).replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    const safeTag = String(t.tag).replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    return `<button type='button' class='px-2.5 py-1.5 rounded-md border border-gray-200 dark:border-gray-700 text-xs font-mono text-gray-800 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700' data-copy-text='${safeTag}' title='Copy'>${safeLabel}: ${safeTag}</button>`;
                }).join('')}
            </div>
            ${listId ? '' : `<div class='mt-2 text-xs text-gray-500 dark:text-gray-400'>Select an Email List to see available tags for that list.</div>`}
        `;

        containers.forEach((c) => {
            c.innerHTML = html;
        });
    }

    document.addEventListener('click', async function (e) {
        const btn = e.target && e.target.closest ? e.target.closest('[data-copy-text]') : null;
        if (!btn) return;
        const text = btn.getAttribute('data-copy-text') || '';
        if (!text) return;
        const originalTitle = btn.getAttribute('title') || '';
        try {
            btn.disabled = true;
            await copyText(text);
            btn.setAttribute('title', 'Copied!');
            setTimeout(function () {
                btn.disabled = false;
                btn.setAttribute('title', originalTitle || 'Copy');
            }, 1200);
        } catch (err) {
            btn.disabled = false;
        }
    });

    if (listSelect) {
        listSelect.addEventListener('change', renderTags);
    }
    renderTags();
};

document.addEventListener('DOMContentLoaded', initCampaignPersonalizationTags);
document.addEventListener('turbo:load', initCampaignPersonalizationTags);

</script>

<script type="application/json" id="campaign-wizard-errors">@json(array_keys($errors->toArray()))</script>
<script>
const initCampaignWizard = () => {
    const form = document.getElementById('unlayer-form');
    if (!form) {
        return;
    }

    if (form.dataset.campaignWizardBound === '1') {
        return;
    }
    form.dataset.campaignWizardBound = '1';

    const stepEls = Array.from(form.querySelectorAll('[data-campaign-step]'));
    const stepper = form.querySelector('[data-campaign-stepper]');
    const stepButtons = stepper ? Array.from(stepper.querySelectorAll('[data-stepper-step]')) : [];
    const connectorEls = stepper ? Array.from(stepper.querySelectorAll('[data-stepper-connector]')) : [];
    const prevBtn = form.querySelector('[data-wizard-prev]');
    const nextBtn = form.querySelector('[data-wizard-next]');
    const submitBtn = document.getElementById('btn-save');
    const stepInput = document.getElementById('wizard_step');

    const fieldStep = {
        name: 1,
        subject: 1,
        list_id: 1,
        type: 1,
        delivery_server_id: 2,
        reply_server_id: 2,
        sending_domain_id: 2,
        tracking_domain_id: 2,
        bounce_server_id: 2,
        from_name: 2,
        from_email: 2,
        template_id: 3,
        signature_template_id: 3,
        footer_template_id: 3,
        html_content: 3,
        plain_text_content: 3,
        template_data: 3,
        send_at: 4,
        track_opens: 4,
        track_clicks: 4,
        enable_spintax: 3,
        spam_scoring_enabled: 4,
        recurring_interval_days: 4,
    };

    const readErrors = () => {
        const el = document.getElementById('campaign-wizard-errors');
        try {
            return el ? (JSON.parse((el.textContent || '').trim() || '[]') || []) : [];
        } catch (e) {
            return [];
        }
    };

    const clampStep = (n) => {
        const step = Number(n);
        if (!Number.isFinite(step)) return 1;
        return Math.min(4, Math.max(1, step));
    };

    const setStepUi = (n) => {
        const step = clampStep(n);

        stepEls.forEach((el) => {
            const elStep = clampStep(el.getAttribute('data-campaign-step'));
            if (elStep === step) {
                el.classList.remove('hidden');
                el.classList.add('contents');
            } else {
                el.classList.add('hidden');
                el.classList.remove('contents');
            }
        });

        stepButtons.forEach((btn) => {
            const btnStep = clampStep(btn.getAttribute('data-stepper-step'));

            btn.classList.remove('bg-primary-600', 'text-white', 'bg-gray-200', 'text-gray-600', 'dark:bg-gray-700', 'dark:text-gray-300', 'bg-primary-600/15', 'text-primary-700', 'dark:text-primary-200');

            if (btnStep === step) {
                btn.classList.add('bg-primary-600', 'text-white');
                return;
            }
            if (btnStep < step) {
                btn.classList.add('bg-primary-600/15', 'text-primary-700', 'dark:text-primary-200');
                return;
            }
            btn.classList.add('bg-gray-200', 'text-gray-600', 'dark:bg-gray-700', 'dark:text-gray-300');
        });

        connectorEls.forEach((c) => {
            const connStep = clampStep(c.getAttribute('data-stepper-connector'));
            c.classList.remove('bg-primary-600', 'bg-gray-200', 'dark:bg-gray-700');
            if (connStep < step) {
                c.classList.add('bg-primary-600');
            } else {
                c.classList.add('bg-gray-200', 'dark:bg-gray-700');
            }
        });

        if (prevBtn) {
            prevBtn.classList.toggle('hidden', step === 1);
        }
        if (nextBtn) {
            nextBtn.classList.toggle('hidden', step === 4);
        }
        if (submitBtn) {
            submitBtn.classList.toggle('hidden', step !== 4);
        }

        if (stepInput) {
            stepInput.value = String(step);
        }

        if (step === 3) {
            try {
                if (typeof window.__mailpurseSetupUnlayerEditors === 'function') {
                    setTimeout(() => window.__mailpurseSetupUnlayerEditors(), 0);
                }
            } catch (e) {
            }
        }
    };

    const validateStep = (n) => {
        const step = clampStep(n);
        if (step === 1) {
            const nameInput = document.getElementById('name');
            const subjectInput = document.getElementById('subject');
            if (nameInput && !nameInput.reportValidity()) {
                return false;
            }
            if (subjectInput && !subjectInput.reportValidity()) {
                return false;
            }
        }
        return true;
    };

    const exportUnlayerIfAvailable = () => {
        const editorEl = form.querySelector('[data-unlayer-editor]');
        if (!editorEl || !window.unlayer || typeof window.unlayer.exportHtml !== 'function') {
            return Promise.resolve();
        }

        return new Promise((resolve) => {
            try {
                window.unlayer.exportHtml((data) => {
                    const htmlInput = document.getElementById('html_content');
                    const plainInput = document.getElementById('plain_text_content');
                    const dataInput = document.getElementById('grapesjs_data');

                    if (htmlInput) {
                        htmlInput.value = (data && data.html) ? data.html : '';
                    }

                    if (dataInput) {
                        dataInput.value = JSON.stringify((data && data.design) ? data.design : null);
                    }

                    const plainText = String((data && data.html) ? data.html : '')
                        .replace(/<style[\s\S]*?<\/style>/gi, ' ')
                        .replace(/<script[\s\S]*?<\/script>/gi, ' ')
                        .replace(/<[^>]+>/g, ' ')
                        .replace(/\s+/g, ' ')
                        .trim();

                    if (plainInput) {
                        plainInput.value = plainText;
                    }

                    resolve();
                });
            } catch (e) {
                resolve();
            }
        });
    };

    const getCurrentStep = () => clampStep(stepInput ? stepInput.value : 1);

    const goTo = async (nextStep) => {
        const current = getCurrentStep();
        const target = clampStep(nextStep);

        if (target === current) {
            return;
        }

        if (target > current) {
            if (!validateStep(current)) {
                return;
            }
            if (current === 3) {
                await exportUnlayerIfAvailable();
            }
        }

        setStepUi(target);
    };

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            goTo(getCurrentStep() - 1);
        });
    }
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            goTo(getCurrentStep() + 1);
        });
    }

    stepButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            const step = clampStep(btn.getAttribute('data-stepper-step'));
            goTo(step);
        });
    });

    const errors = readErrors();
    let initial = clampStep(stepInput ? stepInput.value : 1);

    if (errors && errors.length) {
        const steps = errors
            .map((k) => fieldStep[String(k)] || null)
            .filter((v) => Number.isFinite(v));
        if (steps.length) {
            initial = Math.min(...steps);
        }
    }

    setStepUi(initial);
};

document.addEventListener('DOMContentLoaded', initCampaignWizard);
document.addEventListener('turbo:load', initCampaignWizard);
</script>
@endpush
@endsection
