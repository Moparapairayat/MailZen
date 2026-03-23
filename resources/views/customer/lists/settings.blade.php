@extends('layouts.customer')

@section('title', 'List Settings')
@section('page-title', 'Settings: ' . $list->name)

@section('content')
<div class="max-w-4xl">
    @include('customer.lists.partials.subnav', ['list' => $list])

    <x-card title="Email List Settings">
        <form method="POST" action="{{ route('customer.lists.settings.update', $list) }}" class="space-y-6" x-data="{ tab: 'from' }">
            @csrf
            @method('PUT')

            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="-mb-px flex flex-wrap gap-x-6" aria-label="Tabs">
                    <button type="button" @click="tab = 'from'" class="whitespace-nowrap py-3 px-1 text-sm font-medium" :class="tab === 'from' ? 'border-b-2 border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'">From</button>
                    <button type="button" @click="tab = 'optin'" class="whitespace-nowrap py-3 px-1 text-sm font-medium" :class="tab === 'optin' ? 'border-b-2 border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'">Opt-in</button>
                    <button type="button" @click="tab = 'company'" class="whitespace-nowrap py-3 px-1 text-sm font-medium" :class="tab === 'company' ? 'border-b-2 border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'">Company</button>
                    <button type="button" @click="tab = 'welcome'" class="whitespace-nowrap py-3 px-1 text-sm font-medium" :class="tab === 'welcome' ? 'border-b-2 border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'">Welcome Email</button>
                    <button type="button" @click="tab = 'unsubscribe'" class="whitespace-nowrap py-3 px-1 text-sm font-medium" :class="tab === 'unsubscribe' ? 'border-b-2 border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'">Unsubscribe</button>
                    <button type="button" @click="tab = 'gdpr'" class="whitespace-nowrap py-3 px-1 text-sm font-medium" :class="tab === 'gdpr' ? 'border-b-2 border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'">GDPR</button>
                    <button type="button" @click="tab = 'custom_fields'" class="whitespace-nowrap py-3 px-1 text-sm font-medium" :class="tab === 'custom_fields' ? 'border-b-2 border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'">Custom Fields</button>
                </nav>
            </div>

            <!-- From Settings -->
            <div class="space-y-4" x-show="tab === 'from'" x-cloak>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">From Settings</h3>
                
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="from_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            From Name
                        </label>
                        <input
                            type="text"
                            name="from_name"
                            id="from_name"
                            value="{{ old('from_name', $list->from_name) }}"
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
                        <input
                            type="email"
                            name="from_email"
                            id="from_email"
                            value="{{ old('from_email', $list->from_email) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                        >
                        @error('from_email')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="reply_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Reply To Email
                        </label>
                        <input
                            type="email"
                            name="reply_to"
                            id="reply_to"
                            value="{{ old('reply_to', $list->reply_to) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                        >
                        @error('reply_to')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="default_subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Default Subject
                        </label>
                        <input
                            type="text"
                            name="default_subject"
                            id="default_subject"
                            value="{{ old('default_subject', $list->default_subject) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                        >
                        @error('default_subject')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="space-y-4" x-show="tab === 'optin'" x-cloak>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Opt-in Settings</h3>
                
                <div class="flex items-center">
                    <input
                        id="double_opt_in"
                        name="double_opt_in"
                        type="checkbox"
                        value="1"
                        {{ old('double_opt_in', $list->double_opt_in) ? 'checked' : '' }}
                        class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                    >
                    <label for="double_opt_in" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                        Enable Double Opt-in (Requires email confirmation)
                    </label>
                </div>
            </div>

            <div class="space-y-4" x-show="tab === 'company'" x-cloak>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Company Information</h3>
                
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="company_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Company Name
                        </label>
                        <input
                            type="text"
                            name="company_name"
                            id="company_name"
                            value="{{ old('company_name', $list->company_name) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                        >
                    </div>

                    <div>
                        <label for="company_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Company Address (for compliance)
                        </label>
                        <textarea
                            name="company_address"
                            id="company_address"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                        >{{ old('company_address', $list->company_address) }}</textarea>
                    </div>

                    <div>
                        <label for="footer_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Footer Text (Auto-added to emails)
                        </label>
                        <textarea
                            name="footer_text"
                            id="footer_text"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                        >{{ old('footer_text', $list->footer_text) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="space-y-4" x-show="tab === 'welcome'" x-cloak>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Welcome Email</h3>
                
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input
                            id="welcome_email_enabled"
                            name="welcome_email_enabled"
                            type="checkbox"
                            value="1"
                            {{ old('welcome_email_enabled', $list->welcome_email_enabled) ? 'checked' : '' }}
                            class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                        >
                        <label for="welcome_email_enabled" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                            Send welcome email to new subscribers
                        </label>
                    </div>

                    <div>
                        <label for="welcome_email_subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Welcome Email Subject
                        </label>
                        <input
                            type="text"
                            name="welcome_email_subject"
                            id="welcome_email_subject"
                            value="{{ old('welcome_email_subject', $list->welcome_email_subject) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                        >
                    </div>

                    <div>
                        <label for="welcome_email_content" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Welcome Email Content
                        </label>
                        <textarea
                            name="welcome_email_content"
                            id="welcome_email_content"
                            rows="5"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                        >{{ old('welcome_email_content', $list->welcome_email_content) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="space-y-4" x-show="tab === 'unsubscribe'" x-cloak>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Unsubscribe Settings</h3>
                
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input
                            id="unsubscribe_email_enabled"
                            name="unsubscribe_email_enabled"
                            type="checkbox"
                            value="1"
                            {{ old('unsubscribe_email_enabled', $list->unsubscribe_email_enabled) ? 'checked' : '' }}
                            class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                        >
                        <label for="unsubscribe_email_enabled" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                            Send unsubscribe confirmation email
                        </label>
                    </div>

                    <div>
                        <label for="unsubscribe_redirect_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Unsubscribe Redirect URL
                        </label>
                        <input
                            type="url"
                            name="unsubscribe_redirect_url"
                            id="unsubscribe_redirect_url"
                            value="{{ old('unsubscribe_redirect_url', $list->unsubscribe_redirect_url) }}"
                            placeholder="https://example.com/unsubscribed"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                        >
                        <p class="mt-1 text-sm text-gray-500">Where to redirect users after they unsubscribe</p>
                    </div>
                </div>
            </div>

            <div class="space-y-4" x-show="tab === 'gdpr'" x-cloak>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">GDPR Compliance</h3>
                
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input
                            id="gdpr_enabled"
                            name="gdpr_enabled"
                            type="checkbox"
                            value="1"
                            {{ old('gdpr_enabled', $list->gdpr_enabled) ? 'checked' : '' }}
                            class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                        >
                        <label for="gdpr_enabled" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                            Enable GDPR compliance checkbox
                        </label>
                    </div>

                    <div>
                        <label for="gdpr_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            GDPR Text
                        </label>
                        <textarea
                            name="gdpr_text"
                            id="gdpr_text"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            placeholder="I agree to the processing of my personal data..."
                        >{{ old('gdpr_text', $list->gdpr_text) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="space-y-4" x-show="tab === 'custom_fields'" x-cloak>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Custom Fields</h3>

                <div class="text-sm text-gray-600 dark:text-gray-400">
                    Define extra fields (e.g. address) that can be used in subscription forms for this list.
                </div>

                @php
                    $existingCustomFields = old('custom_fields', $list->custom_fields ?? []);
                    if (!is_array($existingCustomFields)) {
                        $existingCustomFields = [];
                    }
                    if (count($existingCustomFields) === 0) {
                        $existingCustomFields = [['key' => '', 'label' => '', 'type' => 'text', 'required' => false]];
                    }
                @endphp

                <div class="space-y-3" x-data="{ rows: {{ Illuminate\Support\Js::from($existingCustomFields) }} }">
                    <template x-for="(row, idx) in rows" :key="idx">
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-6 items-end">
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Key</label>
                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm" :name="`custom_fields[${idx}][key]`" x-model="row.key" placeholder="address">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Label</label>
                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm" :name="`custom_fields[${idx}][label]`" x-model="row.label" placeholder="Address">
                            </div>
                            <div class="sm:col-span-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                                <select class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm" :name="`custom_fields[${idx}][type]`" x-model="row.type">
                                    <option value="text">Text</option>
                                    <option value="textarea">Textarea</option>
                                </select>
                            </div>
                            <div class="sm:col-span-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Required</label>
                                <div class="mt-2 flex items-center gap-2">
                                    <input type="hidden" :name="`custom_fields[${idx}][required]`" value="0">
                                    <input type="checkbox" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded" :name="`custom_fields[${idx}][required]`" value="1" x-model="row.required">
                                    <button type="button" class="ml-auto text-sm text-red-600 hover:text-red-700" @click="rows = rows.filter((_, i) => i !== idx)" x-show="rows.length > 1">Remove</button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div>
                        <button type="button" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600" @click="rows.push({ key: '', label: '', type: 'text', required: false })">
                            Add Field
                        </button>
                    </div>
                </div>

                @error('custom_fields')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('customer.lists.show', $list) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    Save Settings
                </button>
            </div>
        </form>
    </x-card>
</div>
@endsection

