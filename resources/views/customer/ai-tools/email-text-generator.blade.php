@extends('layouts.customer')

@section('title', 'Email Text Generator')
@section('page-title', 'Email Text Generator')

@section('content')
@php($customer = auth('customer')->user())
<div class="flex flex-col min-h-0 h-full gap-6">
    <div class="grid grid-cols-1 gap-6 lg:[grid-template-columns:380px_1fr] flex-1 min-h-0 lg:overflow-hidden">
        <x-card class="lg:h-full lg:overflow-scroll" :padding="false">
            <div class="px-6 py-4 lg:h-full lg:flex lg:flex-col lg:min-h-0">
                <form id="aiTextForm" method="POST" action="{{ route('customer.ai-tools.email-text-generator.generate') }}" class="flex flex-col lg:flex-1 lg:min-h-0">
                    @csrf

                    <div class="flex-1 overflow-y-auto lg:pr-2 lg:min-h-0">
                        <div class="space-y-5">
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Email Type / Purpose</label>
                                    <select name="email_type" class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                                        @php($types = ['Marketing Campaign','Newsletter','Cold Outreach','Follow-Up','Transactional','Announcement','Support / Apology','Sales Pitch','Internal / Corporate'])
                                        @foreach($types as $t)
                                            <option value="{{ $t }}" {{ old('email_type', 'Marketing Campaign') === $t ? 'selected' : '' }}>{{ $t }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Tone</label>
                                    <select name="tone" class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                                        @php($tones = ['Professional','Friendly','Casual','Formal','Persuasive','Urgent','Warm','Playful','Confident','Empathetic'])
                                        @foreach($tones as $t)
                                            <option value="{{ $t }}" {{ old('tone', 'Professional') === $t ? 'selected' : '' }}>{{ $t }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Target Audience</label>
                                    <select name="audience" class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                                        @php($audiences = ['Customer / Lead','Existing Customer','New Subscriber','VIP / High Value','Cold List','Warm List','Internal Team','custom'])
                                        @foreach($audiences as $a)
                                            <option value="{{ $a }}" {{ old('audience', 'Customer / Lead') === $a ? 'selected' : '' }}>{{ $a === 'custom' ? 'Custom' : $a }}</option>
                                        @endforeach
                                    </select>
                                    <input name="audience_custom" value="{{ old('audience_custom') }}" placeholder="Custom audience (optional)" class="mt-2 w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
                                </div>

                                <div>
                                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Email Length</label>
                                    <select name="length" class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                                        @php($lengths = ['Short','Medium','Long','custom'])
                                        @foreach($lengths as $l)
                                            <option value="{{ $l }}" {{ old('length', 'Short') === $l ? 'selected' : '' }}>{{ $l === 'custom' ? 'Custom' : $l }}</option>
                                        @endforeach
                                    </select>
                                    <input name="word_count" value="{{ old('word_count') }}" type="number" min="10" max="5000" placeholder="Word count (only for custom)" class="mt-2 w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
                                </div>

                                <div>
                                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Objective / Goal</label>
                                    <select name="objective" class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                                        @php($objectives = ['Get Reply','Drive Clicks','Sell Product','Book Call','Confirm Action','Announce Update','Deliver Information','Collect Feedback'])
                                        @foreach($objectives as $o)
                                            <option value="{{ $o }}" {{ old('objective', 'Get Reply') === $o ? 'selected' : '' }}>{{ $o }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Subject Idea / Title</label>
                                    <input name="subject_idea" value="{{ old('subject_idea') }}" class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900" placeholder="Optional" />
                                </div>

                                <div>
                                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Brief Description / Context</label>
                                    <textarea name="context" rows="4" class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900" placeholder="Optional">{{ old('context') }}</textarea>
                                </div>

                                <div>
                                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Key Points To Include</label>
                                    <textarea name="key_points" rows="4" class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900" placeholder="Optional">{{ old('key_points') }}</textarea>
                                </div>

                                <div>
                                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Call To Action</label>
                                    <input name="cta" value="{{ old('cta') }}" class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900" placeholder="Optional" />
                                </div>

                                <div>
                                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Offer / Product Details</label>
                                    <textarea name="offer_details" rows="3" class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900" placeholder="Optional">{{ old('offer_details') }}</textarea>
                                </div>

                                <div>
                                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Provider</label>
                                    <select id="providerSelect" name="provider" class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                                        <option value="chatgpt" {{ old('provider', 'chatgpt') === 'chatgpt' ? 'selected' : '' }}>ChatGPT</option>
                                        <option value="gemini" {{ old('provider') === 'gemini' ? 'selected' : '' }}>Gemini</option>
                                        <option value="claude" {{ old('provider') === 'claude' ? 'selected' : '' }}>Claude</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Model</label>
                                    <select id="modelSelect" name="model" class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                                        <option value="" {{ old('model') ? '' : 'selected' }}>Default</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="sticky bottom-0 mt-5 pt-4 pb-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 z-10">
                        <x-button id="generateBtn" type="submit" variant="primary" class="w-full justify-center">Generate</x-button>
                    </div>
                </form>
            </div>
        </x-card>

        <x-card class="lg:h-full lg:overflow-scroll" :padding="false">
            <div class="px-6 py-4 flex flex-col lg:h-full lg:min-h-0">
                <div class="flex items-center justify-between gap-3">
                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Generated</div>
                    <div class="flex items-center gap-3">
                        <button id="exportToTemplatesBtn" type="button" class="hidden text-xs font-medium px-3 py-1.5 rounded-md border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-950 hover:bg-gray-50 dark:hover:bg-gray-900">Export to templates</button>

                        <div class="text-xs text-gray-600 dark:text-gray-400">
                        <span id="tokensUsedLabel" class="hidden">Tokens used: <span id="tokensUsedValue">0</span></span>
                        <span id="tokensUsageLabel" class="{{ $customer ? '' : 'hidden' }} {{ $customer ? '' : '' }}">
                            @php($aiTokenLimit = (int) ($customer ? $customer->groupSetting('ai.token_limit', 0) : 0))
                            Usage: <span id="tokensUsageUsed">{{ $customer ? (int) ($customer->ai_token_usage ?? 0) : 0 }}</span>
                            / <span id="tokensUsageLimit">{{ $aiTokenLimit > 0 ? $aiTokenLimit : 'Unlimited' }}</span>
                        </span>
                        </div>
                    </div>
                </div>

                <div class="mt-3 flex-1 overflow-y-auto min-h-0">
                    <div id="generatedPlaceholder" class="text-sm text-gray-600 dark:text-gray-400 {{ session('generated_text') ? 'hidden' : '' }}">Generated output will appear here.</div>
                    <div id="generatedLoading" class="hidden">
                        <div class="space-y-2 animate-pulse">
                            <div class="h-3 rounded bg-gray-200 dark:bg-gray-800 w-11/12"></div>
                            <div class="h-3 rounded bg-gray-200 dark:bg-gray-800 w-10/12"></div>
                            <div class="h-3 rounded bg-gray-200 dark:bg-gray-800 w-9/12"></div>
                            <div class="h-3 rounded bg-gray-200 dark:bg-gray-800 w-10/12"></div>
                            <div class="h-3 rounded bg-gray-200 dark:bg-gray-800 w-8/12"></div>
                        </div>
                    </div>
                    <textarea id="generatedOutput" rows="22" class="w-full h-full min-h-[24rem] rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 {{ session('generated_text') ? '' : 'hidden' }}">{{ session('generated_text') }}</textarea>
                    <div id="generatedError" class="mt-3 text-sm text-red-600 dark:text-red-400 hidden"></div>
                    <div id="exportSuccess" class="mt-3 text-sm text-green-700 dark:text-green-300 hidden"></div>
                </div>
            </div>
        </x-card>
    </div>
</div>

<script>
    function initAiEmailTextGenerator() {
        var providerSelect = document.getElementById('providerSelect');
        var modelSelect = document.getElementById('modelSelect');

        if (!providerSelect || !modelSelect) {
            return;
        }

        if (providerSelect.getAttribute('data-model-initialized') === '1') {
            return;
        }

        function setModelOptions(provider) {
            if (!modelSelect) return;

            var current = (modelSelect.getAttribute('data-current') || '').trim();

            while (modelSelect.firstChild) {
                modelSelect.removeChild(modelSelect.firstChild);
            }

            var add = function (value, label) {
                var opt = document.createElement('option');
                opt.value = value;
                opt.textContent = label;
                modelSelect.appendChild(opt);
            };

            add('', 'Default');

            if (provider === 'gemini') {
                add('gemini-2.0-flash', 'gemini-2.0-flash');
                add('gemini-2.0-pro', 'gemini-2.0-pro');
                add('gemini-1.5-flash-latest', 'gemini-1.5-flash-latest');
                add('gemini-1.5-pro-latest', 'gemini-1.5-pro-latest');
                add('gemini-1.0-pro-latest', 'gemini-1.0-pro-latest');
            } else if (provider === 'claude') {
                add('claude-3-5-sonnet-20241022', 'claude-3.5-sonnet');
                add('claude-3-5-haiku-20241022', 'claude-3.5-haiku');
                add('claude-3-opus-20240229', 'claude-3-opus');
            } else {
                add('gpt-5', 'gpt-5');
                add('gpt-5-mini', 'gpt-5-mini');
                add('gpt-5.2', 'gpt-5.2');
                add('gpt-5-nano', 'gpt-5-nano');
                add('gpt-4.1', 'gpt-4.1');
            }

            if (current !== '') {
                modelSelect.value = current;
            }
        }

        if (modelSelect) {
            modelSelect.setAttribute('data-current', @js(old('model', '')));
        }
        if (providerSelect) {
            setModelOptions(providerSelect.value);
            providerSelect.addEventListener('change', function () {
                setModelOptions(providerSelect.value);
            });
        }

        providerSelect.setAttribute('data-model-initialized', '1');

        var form = document.getElementById('aiTextForm');
        var btn = document.getElementById('generateBtn');
        var output = document.getElementById('generatedOutput');
        var placeholder = document.getElementById('generatedPlaceholder');
        var loading = document.getElementById('generatedLoading');
        var errorBox = document.getElementById('generatedError');

        var tokensUsedLabel = document.getElementById('tokensUsedLabel');
        var tokensUsedValue = document.getElementById('tokensUsedValue');
        var tokensUsageUsed = document.getElementById('tokensUsageUsed');
        var tokensUsageLimit = document.getElementById('tokensUsageLimit');
        var exportBtn = document.getElementById('exportToTemplatesBtn');
        var exportSuccess = document.getElementById('exportSuccess');

        if (!form || !btn || !output || !placeholder || !loading || !errorBox) return;

        var typingTimer = null;

        function stopTyping() {
            if (typingTimer) {
                clearTimeout(typingTimer);
                typingTimer = null;
            }
        }

        function showError(message) {
            errorBox.textContent = message || 'Something went wrong.';
            errorBox.classList.remove('hidden');
        }

        function clearError() {
            errorBox.textContent = '';
            errorBox.classList.add('hidden');
        }

        function showExportSuccess(url) {
            if (!exportSuccess) return;
            if (typeof url !== 'string' || !url) {
                exportSuccess.textContent = 'Exported successfully.';
                exportSuccess.classList.remove('hidden');
                return;
            }

            exportSuccess.innerHTML = 'Exported to templates. <a href="' + url + '" class="underline font-medium">View template</a>';
            exportSuccess.classList.remove('hidden');
        }

        function clearExportSuccess() {
            if (!exportSuccess) return;
            exportSuccess.textContent = '';
            exportSuccess.classList.add('hidden');
        }

        function startTyping(text) {
            stopTyping();

            loading.classList.add('hidden');

            placeholder.classList.add('hidden');
            output.classList.remove('hidden');
            output.value = '';

            var chunks = String(text || '').split(/(\s+)/);
            var i = 0;

            var tick = function () {
                if (i >= chunks.length) {
                    typingTimer = null;
                    btn.disabled = false;
                    btn.textContent = 'Generate';

                    if (exportBtn) {
                        exportBtn.classList.remove('hidden');
                        exportBtn.disabled = false;
                        exportBtn.textContent = 'Export to templates';
                    }
                    return;
                }

                output.value += chunks[i];
                output.scrollTop = output.scrollHeight;
                i += 1;
                typingTimer = setTimeout(tick, 18);
            };

            tick();
        }

        function getCsrfToken() {
            var meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.getAttribute('content') : '';
        }

        function setExportVisibleFromExistingText() {
            if (!exportBtn) return;
            if (output && !output.classList.contains('hidden') && String(output.value || '').trim() !== '') {
                exportBtn.classList.remove('hidden');
            }
        }

        setExportVisibleFromExistingText();

        if (exportBtn) {
            exportBtn.addEventListener('click', function () {
                clearError();
                clearExportSuccess();

                var text = String(output && !output.classList.contains('hidden') ? (output.value || '') : '').trim();
                if (!text) {
                    return;
                }

                var ok = window.confirm('Export this generated text to Templates?');
                if (!ok) {
                    return;
                }

                exportBtn.disabled = true;
                exportBtn.textContent = 'Exporting...';

                fetch(@js(route('customer.ai-tools.email-text-generator.export-to-template')), {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': getCsrfToken(),
                    },
                    body: JSON.stringify({ text: text })
                }).then(async function (res) {
                    var data = null;
                    try {
                        data = await res.json();
                    } catch (e) {
                        data = null;
                    }

                    if (!res.ok || !data || data.success !== true || typeof data.view_url !== 'string') {
                        exportBtn.disabled = false;
                        exportBtn.textContent = 'Export to templates';
                        showError((data && typeof data.message === 'string') ? data.message : 'Failed to export.');
                        return;
                    }

                    exportBtn.disabled = false;
                    exportBtn.textContent = 'Export to templates';
                    showExportSuccess(data.view_url);
                }).catch(function () {
                    exportBtn.disabled = false;
                    exportBtn.textContent = 'Export to templates';
                    showError('Failed to export.');
                });
            });
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            stopTyping();
            clearError();
            clearExportSuccess();

            btn.disabled = true;
            btn.textContent = 'Generating...';

            if (exportBtn) {
                exportBtn.classList.add('hidden');
                exportBtn.disabled = true;
            }

            placeholder.classList.add('hidden');
            output.classList.add('hidden');
            output.value = '';

            loading.classList.remove('hidden');

            var fd = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: fd
            }).then(async function (res) {
                var data = null;
                try {
                    data = await res.json();
                } catch (err) {
                    data = null;
                }

                if (!res.ok) {
                    var msg = 'Failed to generate.';
                    if (data && typeof data.message === 'string') {
                        msg = data.message;
                    }
                    if (data && data.errors && typeof data.errors === 'object') {
                        var firstKey = Object.keys(data.errors)[0];
                        if (firstKey && Array.isArray(data.errors[firstKey]) && data.errors[firstKey][0]) {
                            msg = data.errors[firstKey][0];
                        }
                    }

                    btn.disabled = false;
                    btn.textContent = 'Generate';
                    showError(msg);

                    loading.classList.add('hidden');
                    placeholder.classList.remove('hidden');
                    output.classList.add('hidden');
                    output.value = '';
                    return;
                }

                if (data && data.success === true) {
                    if (tokensUsedLabel && tokensUsedValue && typeof data.tokens === 'number') {
                        tokensUsedValue.textContent = String(data.tokens);
                        tokensUsedLabel.classList.remove('hidden');
                    }
                    if (tokensUsageUsed && typeof data.ai_token_usage === 'number') {
                        tokensUsageUsed.textContent = String(data.ai_token_usage);
                    }
                    if (tokensUsageLimit && typeof data.ai_token_limit === 'number') {
                        tokensUsageLimit.textContent = data.ai_token_limit > 0
                            ? String(data.ai_token_limit)
                            : 'Unlimited';
                    }
                    startTyping(data.text || '');
                    return;
                }

                btn.disabled = false;
                btn.textContent = 'Generate';
                showError((data && data.message) ? data.message : 'Failed to generate.');

                loading.classList.add('hidden');
                placeholder.classList.remove('hidden');
                output.classList.add('hidden');
                output.value = '';
            }).catch(function () {
                btn.disabled = false;
                btn.textContent = 'Generate';
                showError('Failed to generate.');

                loading.classList.add('hidden');
                placeholder.classList.remove('hidden');
                output.classList.add('hidden');
                output.value = '';
            });
        });

    }

    document.addEventListener('DOMContentLoaded', initAiEmailTextGenerator);
    document.addEventListener('turbo:load', initAiEmailTextGenerator);
    if (document.readyState !== 'loading') {
        initAiEmailTextGenerator();
    }
</script>
@endsection
