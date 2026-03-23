@extends('layouts.customer')

@section('title', 'Create Subscription Form')
@section('page-title', 'Create Subscription Form: ' . $list->name)

@section('content')
<div class="max-w-6xl">
    <x-card title="Create Subscription Form">
        <form method="POST" action="{{ route('customer.lists.forms.store', $list) }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Form Name <span class="text-red-500">*</span>
                    </label>
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

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Form Title (public)
                    </label>
                    <input
                        type="text"
                        name="title"
                        id="title"
                        value="{{ old('title') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >
                    @error('title')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Form Type <span class="text-red-500">*</span>
                    </label>
                    <select
                        name="type"
                        id="type"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >
                        <option value="embedded" {{ old('type', 'embedded') === 'embedded' ? 'selected' : '' }}>Embedded HTML Form</option>
                        <option value="popup" {{ old('type') === 'popup' ? 'selected' : '' }}>Popup</option>
                        <option value="api" {{ old('type') === 'api' ? 'selected' : '' }}>API Endpoint</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div x-data="{ type: '{{ old('type', 'embedded') }}' }" x-init="type = document.getElementById('type') ? document.getElementById('type').value : type; document.getElementById('type') && document.getElementById('type').addEventListener('change', function(e){ type = e.target.value; });">
                    <div x-show="type === 'popup'" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Popup Options
                        </label>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="popup_delay_seconds" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Popup Delay (seconds)
                                </label>
                                <input
                                    type="number"
                                    name="settings[popup_delay_seconds]"
                                    id="popup_delay_seconds"
                                    min="0"
                                    max="3600"
                                    value="{{ old('settings.popup_delay_seconds', 5) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                >
                                @error('settings.popup_delay_seconds')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="popup_width" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Popup Width (px)
                                </label>
                                <input
                                    type="number"
                                    name="settings[popup_width]"
                                    id="popup_width"
                                    min="200"
                                    max="1400"
                                    value="{{ old('settings.popup_width', 600) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                >
                                @error('settings.popup_width')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="popup_height" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Popup Height (px)
                                </label>
                                <input
                                    type="number"
                                    name="settings[popup_height]"
                                    id="popup_height"
                                    min="200"
                                    max="1400"
                                    value="{{ old('settings.popup_height', 420) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                >
                                @error('settings.popup_height')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="popup_bg_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Popup Background
                                </label>
                                <input
                                    type="color"
                                    name="settings[popup_bg_color]"
                                    id="popup_bg_color"
                                    value="{{ old('settings.popup_bg_color', '#ffffff') }}"
                                    class="mt-1 block w-full h-10 rounded-md border-gray-300 dark:border-gray-600 shadow-sm"
                                >
                                @error('settings.popup_bg_color')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="popup_overlay_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Popup Overlay
                                </label>
                                <input
                                    type="color"
                                    name="settings[popup_overlay_color]"
                                    id="popup_overlay_color"
                                    value="{{ old('settings.popup_overlay_color', '#000000') }}"
                                    class="mt-1 block w-full h-10 rounded-md border-gray-300 dark:border-gray-600 shadow-sm"
                                >
                                @error('settings.popup_overlay_color')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-end">
                                <label class="flex items-center">
                                    <input type="hidden" name="settings[popup_show_once]" value="0">
                                    <input
                                        type="checkbox"
                                        name="settings[popup_show_once]"
                                        value="1"
                                        {{ old('settings.popup_show_once', false) ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                    >
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Show only once per visitor</span>
                                </label>
                                @error('settings.popup_show_once')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Description
                    </label>
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

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Display Options
                    </label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="hidden" name="settings[show_title]" value="0">
                            <input
                                type="checkbox"
                                name="settings[show_title]"
                                value="1"
                                {{ old('settings.show_title', true) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                            >
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Show form title</span>
                        </label>
                        <label class="flex items-center">
                            <input type="hidden" name="settings[show_description]" value="0">
                            <input
                                type="checkbox"
                                name="settings[show_description]"
                                value="1"
                                {{ old('settings.show_description', true) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                            >
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Show form description</span>
                        </label>

                        <div>
                            <label for="direction" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Direction
                            </label>
                            <select
                                name="settings[direction]"
                                id="direction"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                                <option value="auto" {{ old('settings.direction', 'ltr') === 'auto' ? 'selected' : '' }}>Auto</option>
                                <option value="ltr" {{ old('settings.direction', 'ltr') === 'ltr' ? 'selected' : '' }}>LTR</option>
                                <option value="rtl" {{ old('settings.direction') === 'rtl' ? 'selected' : '' }}>RTL</option>
                            </select>
                            @error('settings.direction')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Form Fields
                    </label>
                    @php
                        $listCustomFields = is_array($list->custom_fields) ? $list->custom_fields : [];

                        $allFieldMeta = [
                            'email' => ['label' => 'Email (required)', 'type' => 'email', 'required' => true],
                            'first_name' => ['label' => 'First Name', 'type' => 'text', 'required' => false],
                            'last_name' => ['label' => 'Last Name', 'type' => 'text', 'required' => false],
                        ];

                        foreach ($listCustomFields as $def) {
                            $cfKey = is_array($def) ? trim((string) ($def['key'] ?? '')) : '';
                            $cfLabel = is_array($def) ? trim((string) ($def['label'] ?? '')) : '';
                            if ($cfKey !== '' && $cfLabel !== '') {
                                $allFieldMeta['cf:' . $cfKey] = [
                                    'label' => $cfLabel,
                                    'type' => (string) (is_array($def) ? ($def['type'] ?? 'text') : 'text'),
                                    'required' => (bool) (is_array($def) ? ($def['required'] ?? false) : false),
                                ];
                            }
                        }

                        $enabledFields = old('fields_enabled', old('fields', ['email', 'first_name', 'last_name']));
                        if (!is_array($enabledFields)) {
                            $enabledFields = ['email', 'first_name', 'last_name'];
                        }
                        if (!in_array('email', $enabledFields, true)) {
                            $enabledFields[] = 'email';
                        }
                        $enabledSet = array_fill_keys($enabledFields, true);

                        $orderedFields = old('fields_order');
                        if (!is_array($orderedFields)) {
                            $orderedFields = array_values(array_unique(array_merge($enabledFields, array_keys($allFieldMeta))));
                        }

                        $orderedFields = array_values(array_filter($orderedFields, fn ($k) => is_string($k) && isset($allFieldMeta[$k])));
                        foreach (array_keys($allFieldMeta) as $k) {
                            if (!in_array($k, $orderedFields, true)) {
                                $orderedFields[] = $k;
                            }
                        }

                        $fieldStyles = old('settings.field_styles', []);
                        if (!is_array($fieldStyles)) {
                            $fieldStyles = [];
                        }

                        $fieldMeta = old('settings.field_meta', []);
                        if (!is_array($fieldMeta)) {
                            $fieldMeta = [];
                        }
                    @endphp

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6" data-mp-form-builder>
                        <div class="lg:col-span-3">
                            <div class="rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                                <div class="px-3 py-2 border-b border-gray-200 dark:border-gray-700">
                                    <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Fields</div>
                                </div>
                                <ul id="mp-field-list" class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($orderedFields as $fieldKey)
                                        @php
                                            $meta = $allFieldMeta[$fieldKey] ?? ['label' => $fieldKey, 'type' => 'text', 'required' => false];
                                            $isEmail = $fieldKey === 'email';
                                            $isEnabled = $isEmail || isset($enabledSet[$fieldKey]);
                                        @endphp
                                        <li data-mp-field-item data-field-key="{{ $fieldKey }}" data-field-label="{{ e((string) ($meta['label'] ?? $fieldKey)) }}" data-field-type="{{ e((string) ($meta['type'] ?? 'text')) }}" draggable="true" class="p-3">
                                            <input type="hidden" name="fields_order[]" value="{{ $fieldKey }}">
                                            @if($isEmail)
                                                <input type="hidden" name="fields_enabled[]" value="email">
                                            @endif

                                            @foreach(['normal', 'hover', 'focus'] as $stateKey)
                                                <input type="hidden" data-mp-style-input data-field-key="{{ $fieldKey }}" data-state="{{ $stateKey }}" data-prop="margin" name="settings[field_styles][{{ $fieldKey }}][{{ $stateKey }}][margin]" value="{{ data_get($fieldStyles, $fieldKey . '.' . $stateKey . '.margin', '') }}">
                                                <input type="hidden" data-mp-style-input data-field-key="{{ $fieldKey }}" data-state="{{ $stateKey }}" data-prop="padding" name="settings[field_styles][{{ $fieldKey }}][{{ $stateKey }}][padding]" value="{{ data_get($fieldStyles, $fieldKey . '.' . $stateKey . '.padding', '') }}">
                                                <input type="hidden" data-mp-style-input data-field-key="{{ $fieldKey }}" data-state="{{ $stateKey }}" data-prop="background" name="settings[field_styles][{{ $fieldKey }}][{{ $stateKey }}][background]" value="{{ data_get($fieldStyles, $fieldKey . '.' . $stateKey . '.background', '') }}">
                                                <input type="hidden" data-mp-style-input data-field-key="{{ $fieldKey }}" data-state="{{ $stateKey }}" data-prop="text_color" name="settings[field_styles][{{ $fieldKey }}][{{ $stateKey }}][text_color]" value="{{ data_get($fieldStyles, $fieldKey . '.' . $stateKey . '.text_color', '') }}">
                                                <input type="hidden" data-mp-style-input data-field-key="{{ $fieldKey }}" data-state="{{ $stateKey }}" data-prop="placeholder_color" name="settings[field_styles][{{ $fieldKey }}][{{ $stateKey }}][placeholder_color]" value="{{ data_get($fieldStyles, $fieldKey . '.' . $stateKey . '.placeholder_color', '') }}">
                                                <input type="hidden" data-mp-style-input data-field-key="{{ $fieldKey }}" data-state="{{ $stateKey }}" data-prop="border" name="settings[field_styles][{{ $fieldKey }}][{{ $stateKey }}][border]" value="{{ data_get($fieldStyles, $fieldKey . '.' . $stateKey . '.border', '') }}">
                                                <input type="hidden" data-mp-style-input data-field-key="{{ $fieldKey }}" data-state="{{ $stateKey }}" data-prop="border_radius" name="settings[field_styles][{{ $fieldKey }}][{{ $stateKey }}][border_radius]" value="{{ data_get($fieldStyles, $fieldKey . '.' . $stateKey . '.border_radius', '') }}">
                                                <input type="hidden" data-mp-style-input data-field-key="{{ $fieldKey }}" data-state="{{ $stateKey }}" data-prop="shadow" name="settings[field_styles][{{ $fieldKey }}][{{ $stateKey }}][shadow]" value="{{ data_get($fieldStyles, $fieldKey . '.' . $stateKey . '.shadow', '') }}">
                                            @endforeach

                                            <input type="hidden" data-mp-meta-input data-field-key="{{ $fieldKey }}" data-prop="show_label" name="settings[field_meta][{{ $fieldKey }}][show_label]" value="{{ data_get($fieldMeta, $fieldKey . '.show_label', true) ? '1' : '0' }}">
                                            <input type="hidden" data-mp-meta-input data-field-key="{{ $fieldKey }}" data-prop="icon" name="settings[field_meta][{{ $fieldKey }}][icon]" value="{{ data_get($fieldMeta, $fieldKey . '.icon', '') }}">
                                            <input type="hidden" data-mp-meta-input data-field-key="{{ $fieldKey }}" data-prop="icon_color" name="settings[field_meta][{{ $fieldKey }}][icon_color]" value="{{ data_get($fieldMeta, $fieldKey . '.icon_color', '') }}">
                                            <input type="hidden" data-mp-meta-input data-field-key="{{ $fieldKey }}" data-prop="icon_upload_url" name="settings[field_meta][{{ $fieldKey }}][icon_upload_url]" value="{{ data_get($fieldMeta, $fieldKey . '.icon_upload_url', '') }}">

                                            <button type="button" data-mp-select-field class="w-full text-left">
                                                <div class="flex items-center gap-2">
                                                    <div class="text-gray-400" data-mp-drag-handle>
                                                        <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                            <path d="M7 4a1 1 0 11-2 0 1 1 0 012 0zM7 10a1 1 0 11-2 0 1 1 0 012 0zM7 16a1 1 0 11-2 0 1 1 0 012 0zM15 4a1 1 0 11-2 0 1 1 0 012 0zM15 10a1 1 0 11-2 0 1 1 0 012 0zM15 16a1 1 0 11-2 0 1 1 0 012 0z" />
                                                        </svg>
                                                    </div>
                                                    <label class="flex items-center">
                                                        <input
                                                            type="checkbox"
                                                            name="fields_enabled[]"
                                                            value="{{ $fieldKey }}"
                                                            {{ $isEnabled ? 'checked' : '' }}
                                                            {{ $isEmail ? 'disabled' : '' }}
                                                            class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                                            data-mp-enabled
                                                        >
                                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $meta['label'] ?? $fieldKey }}</span>
                                                    </label>
                                                </div>
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <div class="lg:col-span-5">
                            <div class="rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                                <div class="px-3 py-2 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                                    <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Live Preview</div>
                                    <div class="text-xs text-gray-500" id="mp-preview-mode-label">Normal</div>
                                </div>
                                <div class="p-4">
                                    <div id="mp-preview" class="space-y-4"></div>
                                </div>
                            </div>
                        </div>

                        <div class="lg:col-span-4">
                            <div class="rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                                <div class="px-3 py-2 border-b border-gray-200 dark:border-gray-700">
                                    <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Properties</div>
                                    <div class="text-xs text-gray-500" id="mp-selected-field-label"></div>
                                </div>
                                <div class="p-3 space-y-3">
                                    <div class="grid grid-cols-3 gap-2">
                                        <button type="button" class="px-2 py-1 text-xs rounded border border-gray-200 dark:border-gray-700" data-mp-state-btn data-state="normal">Normal</button>
                                        <button type="button" class="px-2 py-1 text-xs rounded border border-gray-200 dark:border-gray-700" data-mp-state-btn data-state="hover">Hover</button>
                                        <button type="button" class="px-2 py-1 text-xs rounded border border-gray-200 dark:border-gray-700" data-mp-state-btn data-state="focus">Focus</button>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <label for="mp-prop-show_label" class="text-xs font-medium text-gray-700 dark:text-gray-300">Show label</label>
                                        <input id="mp-prop-show_label" type="checkbox" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                    </div>

                                    <div>
                                        <label for="mp-prop-icon" class="block text-xs font-medium text-gray-700 dark:text-gray-300">Icon</label>
                                        <select id="mp-prop-icon" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                            <option value="">None</option>
                                            <option value="mail">Mail</option>
                                            <option value="user">User</option>
                                            <option value="lock">Lock</option>
                                            <option value="tag">Tag</option>
                                            <option value="message">Message</option>
                                            <option value="phone">Phone</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Icon Color</label>
                                        <div class="mt-1 grid grid-cols-3 gap-2">
                                            <input id="mp-prop-icon_color_color" type="color" class="col-span-1 block w-full h-10 rounded-md border-gray-300 dark:border-gray-600 shadow-sm">
                                            <input id="mp-prop-icon_color" type="text" class="col-span-2 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-700 dark:text-gray-100 sm:text-sm" placeholder="#9ca3af">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Upload Icon (SVG/PNG)</label>
                                        <input id="mp-prop-icon_upload" type="file" accept="image/png,image/svg+xml" class="mt-1 block w-full text-xs text-gray-700 dark:text-gray-200">
                                        <div class="mt-2 flex items-center gap-2">
                                            <input id="mp-prop-icon_upload_url" type="text" readonly class="flex-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-700 dark:text-gray-100 text-xs" placeholder="No upload">
                                            <button id="mp-prop-icon_upload_remove" type="button" class="px-2 py-1 text-xs rounded border border-gray-200 dark:border-gray-700">Remove</button>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Margin</label>
                                        <input id="mp-prop-margin" type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Padding</label>
                                        <input id="mp-prop-padding" type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Background</label>
                                        <div class="mt-1 grid grid-cols-3 gap-2">
                                            <input id="mp-prop-background_color" type="color" class="col-span-1 block w-full h-10 rounded-md border-gray-300 dark:border-gray-600 shadow-sm">
                                            <input id="mp-prop-background" type="text" class="col-span-2 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-700 dark:text-gray-100 sm:text-sm" placeholder="#ffffff">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Text Color</label>
                                        <div class="mt-1 grid grid-cols-3 gap-2">
                                            <input id="mp-prop-text_color_color" type="color" class="col-span-1 block w-full h-10 rounded-md border-gray-300 dark:border-gray-600 shadow-sm">
                                            <input id="mp-prop-text_color" type="text" class="col-span-2 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-700 dark:text-gray-100 sm:text-sm" placeholder="#111827">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Placeholder Color</label>
                                        <div class="mt-1 grid grid-cols-3 gap-2">
                                            <input id="mp-prop-placeholder_color_color" type="color" class="col-span-1 block w-full h-10 rounded-md border-gray-300 dark:border-gray-600 shadow-sm">
                                            <input id="mp-prop-placeholder_color" type="text" class="col-span-2 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-700 dark:text-gray-100 sm:text-sm" placeholder="#9ca3af">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Border</label>
                                        <div class="mt-1 grid grid-cols-5 gap-2">
                                            <input id="mp-prop-border_width" type="number" min="0" class="col-span-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-700 dark:text-gray-100 sm:text-sm" placeholder="1">
                                            <select id="mp-prop-border_style" class="col-span-2 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                                <option value="">-</option>
                                                <option value="solid">Solid</option>
                                                <option value="dashed">Dashed</option>
                                                <option value="dotted">Dotted</option>
                                            </select>
                                            <input id="mp-prop-border_color" type="color" class="col-span-2 block w-full h-10 rounded-md border-gray-300 dark:border-gray-600 shadow-sm">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Border Radius</label>
                                        <input id="mp-prop-border_radius" type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-700 dark:text-gray-100 sm:text-sm" placeholder="8px">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Shadow</label>
                                        <div class="mt-1 grid grid-cols-4 gap-2">
                                            <input id="mp-prop-shadow_x" type="number" class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-700 dark:text-gray-100 sm:text-sm" placeholder="0">
                                            <input id="mp-prop-shadow_y" type="number" class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-700 dark:text-gray-100 sm:text-sm" placeholder="0">
                                            <input id="mp-prop-shadow_blur" type="number" class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-700 dark:text-gray-100 sm:text-sm" placeholder="0">
                                            <input id="mp-prop-shadow_spread" type="number" class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-700 dark:text-gray-100 sm:text-sm" placeholder="0">
                                        </div>
                                        <div class="mt-2 grid grid-cols-3 gap-2">
                                            <input id="mp-prop-shadow_color" type="color" class="col-span-1 block w-full h-10 rounded-md border-gray-300 dark:border-gray-600 shadow-sm">
                                            <input id="mp-prop-shadow" type="text" class="col-span-2 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-700 dark:text-gray-100 sm:text-sm" placeholder="0px 0px 0px 0px rgba(0,0,0,0.25)">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center">
                    <input
                        id="gdpr_checkbox"
                        name="gdpr_checkbox"
                        type="checkbox"
                        value="1"
                        {{ old('gdpr_checkbox') ? 'checked' : '' }}
                        class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                    >
                    <label for="gdpr_checkbox" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                        Show GDPR compliance checkbox
                    </label>
                </div>

                <div>
                    <label for="gdpr_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        GDPR Text
                    </label>
                    <textarea
                        name="gdpr_text"
                        id="gdpr_text"
                        rows="2"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                        placeholder="I agree to the processing of my personal data..."
                    >{{ old('gdpr_text', $list->gdpr_text) }}</textarea>
                </div>

                <div class="flex items-center">
                    <input
                        id="is_active"
                        name="is_active"
                        type="checkbox"
                        value="1"
                        {{ old('is_active', true) ? 'checked' : '' }}
                        class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                    >
                    <label for="is_active" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                        Activate form immediately
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('customer.lists.forms.index', $list) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    Create Form
                </button>
            </div>
        </form>
    </x-card>
</div>

<script>
    (function () {
        function setupBuilder() {
            var root = document.querySelector('[data-mp-form-builder]');
            if (!root) return;

            var list = document.getElementById('mp-field-list');
            var preview = document.getElementById('mp-preview');
            if (!list || !preview) return;

            var selectedFieldKey = null;
            var selectedState = 'normal';

            var labelEl = document.getElementById('mp-selected-field-label');
            var previewModeLabel = document.getElementById('mp-preview-mode-label');

            var propEls = {
                show_label: document.getElementById('mp-prop-show_label'),
                icon: document.getElementById('mp-prop-icon'),
                icon_color: document.getElementById('mp-prop-icon_color'),
                icon_color_color: document.getElementById('mp-prop-icon_color_color'),
                icon_upload: document.getElementById('mp-prop-icon_upload'),
                icon_upload_url: document.getElementById('mp-prop-icon_upload_url'),
                icon_upload_remove: document.getElementById('mp-prop-icon_upload_remove'),
                margin: document.getElementById('mp-prop-margin'),
                padding: document.getElementById('mp-prop-padding'),
                background: document.getElementById('mp-prop-background'),
                background_color: document.getElementById('mp-prop-background_color'),
                text_color: document.getElementById('mp-prop-text_color'),
                text_color_color: document.getElementById('mp-prop-text_color_color'),
                placeholder_color: document.getElementById('mp-prop-placeholder_color'),
                placeholder_color_color: document.getElementById('mp-prop-placeholder_color_color'),
                border_width: document.getElementById('mp-prop-border_width'),
                border_style: document.getElementById('mp-prop-border_style'),
                border_color: document.getElementById('mp-prop-border_color'),
                border_radius: document.getElementById('mp-prop-border_radius'),
                shadow_x: document.getElementById('mp-prop-shadow_x'),
                shadow_y: document.getElementById('mp-prop-shadow_y'),
                shadow_blur: document.getElementById('mp-prop-shadow_blur'),
                shadow_spread: document.getElementById('mp-prop-shadow_spread'),
                shadow_color: document.getElementById('mp-prop-shadow_color'),
                shadow: document.getElementById('mp-prop-shadow'),
            };

            function qsHidden(fieldKey, state, prop) {
                return document.querySelector('[data-mp-style-input][data-field-key="' + fieldKey.replace(/"/g, '\\"') + '"][data-state="' + state + '"][data-prop="' + prop + '"]');
            }

            function getMetaInput(fieldKey, prop) {
                return document.querySelector('[data-mp-meta-input][data-field-key="' + fieldKey.replace(/"/g, '\\"') + '"][data-prop="' + prop + '"]');
            }

            function getHiddenValue(fieldKey, state, prop) {
                var el = qsHidden(fieldKey, state, prop);
                return el ? (el.value || '') : '';
            }

            function setHiddenValue(fieldKey, state, prop, value) {
                var el = qsHidden(fieldKey, state, prop);
                if (el) el.value = value || '';
            }

            function getMetaValue(fieldKey, prop) {
                var input = getMetaInput(fieldKey, prop);
                if (!input) return '';
                return String(input.value || '');
            }

            function setMetaValue(fieldKey, prop, value) {
                var input = getMetaInput(fieldKey, prop);
                if (!input) return;
                input.value = value;
            }

            function iconSvg(name) {
                var icons = {
                    mail: '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16v16H4z" fill="none"></path><path d="M22 6l-10 7L2 6"></path></svg>',
                    user: '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>',
                    lock: '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>',
                    tag: '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41L11 3H4v7l9.59 9.59a2 2 0 0 0 2.82 0l4.18-4.18a2 2 0 0 0 0-2.82z"></path><circle cx="7.5" cy="7.5" r="1.5"></circle></svg>',
                    message: '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a4 4 0 0 1-4 4H7l-4 4V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"></path></svg>',
                    phone: '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.8 19.8 0 0 1 3 5.18 2 2 0 0 1 5.11 3h3a2 2 0 0 1 2 1.72c.12.86.31 1.7.57 2.5a2 2 0 0 1-.45 2.11L9.91 10.09a16 16 0 0 0 4 4l.76-.32a2 2 0 0 1 2.11.45c.8.26 1.64.45 2.5.57A2 2 0 0 1 22 16.92z"></path></svg>'
                };
                return icons[name] || '';
            }

            function isHexColor(value) {
                return /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(String(value || '').trim());
            }

            function parseBorder(str) {
                var s = String(str || '').trim();
                var m = s.match(/^\s*(\d+(?:\.\d+)?)px\s+(solid|dashed|dotted|double|groove|ridge|inset|outset|none|hidden)\s+(.+?)\s*$/i);
                if (!m) {
                    return { width: '', style: '', color: '' };
                }
                return { width: m[1] || '', style: (m[2] || '').toLowerCase(), color: m[3] || '' };
            }

            function buildBorder(width, style, color) {
                var w = String(width ?? '').trim();
                var s = String(style ?? '').trim();
                var c = String(color ?? '').trim();
                if (w === '' && s === '' && c === '') return '';
                return (w !== '' ? w : '1') + 'px ' + (s || 'solid') + ' ' + (c || '#d1d5db');
            }

            function parseShadow(str) {
                var s = String(str || '').trim();
                if (s === '') {
                    return { x: '', y: '', blur: '', spread: '', color: '' };
                }
                var parts = s.split(/\s+/);
                if (parts.length < 3) {
                    return { x: '', y: '', blur: '', spread: '', color: '' };
                }
                var x = (parts[0] || '').replace(/px$/i, '');
                var y = (parts[1] || '').replace(/px$/i, '');
                var blur = (parts[2] || '').replace(/px$/i, '');
                var spread = '';
                var color = '';
                if (parts.length >= 5) {
                    spread = (parts[3] || '').replace(/px$/i, '');
                    color = parts.slice(4).join(' ');
                } else if (parts.length === 4) {
                    color = parts[3];
                }
                return { x: x, y: y, blur: blur, spread: spread, color: color };
            }

            function buildShadow(x, y, blur, spread, color) {
                var sx = String(x ?? '').trim();
                var sy = String(y ?? '').trim();
                var sb = String(blur ?? '').trim();
                var ss = String(spread ?? '').trim();
                var sc = String(color ?? '').trim();
                if (sx === '' && sy === '' && sb === '' && ss === '' && sc === '') return '';
                var xx = sx !== '' ? sx : '0';
                var yy = sy !== '' ? sy : '0';
                var bb = sb !== '' ? sb : '0';
                var sp = ss !== '' ? ss : '0';
                var cc = sc || 'rgba(0,0,0,0.25)';
                return xx + 'px ' + yy + 'px ' + bb + 'px ' + sp + 'px ' + cc;
            }

            function buildInputCss(style) {
                var parts = [];
                var p = String(style.padding || '').trim();
                if (p) parts.push('padding:' + p + ' !important');
                var bg = String(style.background || '').trim();
                if (bg) parts.push('background:' + bg + ' !important');
                var tc = String(style.text_color || '').trim();
                if (tc) parts.push('color:' + tc + ' !important');
                var pc = String(style.placeholder_color || '').trim();
                if (pc) parts.push('--mp-placeholder-color:' + pc);
                var b = String(style.border || '').trim();
                if (b) parts.push('border:' + b + ' !important');
                var br = String(style.border_radius || '').trim();
                if (br) parts.push('border-radius:' + br + ' !important');
                var sh = String(style.shadow || '').trim();
                if (sh) parts.push('box-shadow:' + sh + ' !important');
                return parts.join(';');
            }

            function getItemMeta(li) {
                return {
                    key: li.getAttribute('data-field-key') || '',
                    label: li.getAttribute('data-field-label') || '',
                    type: li.getAttribute('data-field-type') || 'text',
                    enabled: (function () {
                        var cb = li.querySelector('[data-mp-enabled]');
                        if (!cb) return false;
                        return cb.checked || cb.disabled;
                    })(),
                };
            }

            function readStyle(fieldKey, state) {
                return {
                    margin: getHiddenValue(fieldKey, state, 'margin'),
                    padding: getHiddenValue(fieldKey, state, 'padding'),
                    background: getHiddenValue(fieldKey, state, 'background'),
                    text_color: getHiddenValue(fieldKey, state, 'text_color'),
                    placeholder_color: getHiddenValue(fieldKey, state, 'placeholder_color'),
                    border: getHiddenValue(fieldKey, state, 'border'),
                    border_radius: getHiddenValue(fieldKey, state, 'border_radius'),
                    shadow: getHiddenValue(fieldKey, state, 'shadow'),
                };
            }

            function renderPreview() {
                if (!document.getElementById('mp-preview-placeholder-style')) {
                    var stEl = document.createElement('style');
                    stEl.id = 'mp-preview-placeholder-style';
                    stEl.textContent = '#mp-preview input::placeholder,#mp-preview textarea::placeholder{color:var(--mp-placeholder-color) !important;}';
                    document.head.appendChild(stEl);
                }

                preview.innerHTML = '';
                list.querySelectorAll('[data-mp-field-item]').forEach(function (li) {
                    var meta = getItemMeta(li);
                    if (!meta.key || !meta.enabled) return;

                    var stateToUse = meta.key === selectedFieldKey ? selectedState : 'normal';
                    var style = readStyle(meta.key, stateToUse);

                    var wrapper = document.createElement('div');
                    var m = String(style.margin || '').trim();
                    if (m) wrapper.style.margin = m;

                    var showLabel = getMetaValue(meta.key, 'show_label') !== '0';
                    var iconName = getMetaValue(meta.key, 'icon');
                    var iconColor = getMetaValue(meta.key, 'icon_color');
                    var iconUploadUrl = getMetaValue(meta.key, 'icon_upload_url');

                    var label = document.createElement('label');
                    label.className = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
                    label.textContent = meta.label || meta.key;

                    var input;
                    if (meta.type === 'textarea') {
                        input = document.createElement('textarea');
                        input.rows = 3;
                    } else {
                        input = document.createElement('input');
                        input.type = meta.type === 'email' ? 'email' : 'text';
                    }

                    input.className = 'mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-gray-900 dark:text-gray-100 dark:bg-gray-700 focus:outline-none sm:text-sm';
                    input.setAttribute('style', buildInputCss(style));
                    input.placeholder = meta.label || meta.key;

                    if (showLabel) {
                        wrapper.appendChild(label);
                    }

                    var fieldWrap = document.createElement('div');
                    fieldWrap.className = showLabel ? 'mt-1 relative' : 'relative';
                    if (iconUploadUrl || iconName) {
                        var iconWrap = document.createElement('span');
                        iconWrap.className = 'text-gray-400';
                        iconWrap.style.position = 'absolute';
                        iconWrap.style.insetBlockStart = '0';
                        iconWrap.style.insetBlockEnd = '0';
                        iconWrap.style.insetInlineStart = '0.75rem';
                        iconWrap.style.display = 'flex';
                        iconWrap.style.alignItems = 'center';
                        iconWrap.style.pointerEvents = 'none';

                        if (iconUploadUrl) {
                            var img = document.createElement('img');
                            img.src = iconUploadUrl;
                            img.alt = '';
                            img.style.width = '1rem';
                            img.style.height = '1rem';
                            img.style.objectFit = 'contain';
                            iconWrap.appendChild(img);
                        } else {
                            var svg = iconSvg(iconName);
                            if (svg) {
                                if (iconColor) {
                                    iconWrap.style.color = iconColor;
                                }
                                iconWrap.innerHTML = svg;
                            }
                        }

                        fieldWrap.appendChild(iconWrap);

                        var pad = String(style.padding || '').trim();
                        var left = '0.75rem';
                        if (pad) {
                            var parts = pad.split(/\s+/);
                            if (parts.length === 1) left = parts[0];
                            else if (parts.length === 2) left = parts[1];
                            else if (parts.length === 3) left = parts[1];
                            else if (parts.length >= 4) left = parts[3];
                        }
                        input.style.cssText += ';padding-inline-start:calc(' + left + ' + 2rem) !important;';
                        input.classList.remove('mt-1');
                    }
                    fieldWrap.appendChild(input);
                    wrapper.appendChild(fieldWrap);
                    preview.appendChild(wrapper);
                });
            }

            function highlightSelected() {
                list.querySelectorAll('[data-mp-field-item]').forEach(function (li) {
                    li.classList.remove('ring-2', 'ring-primary-500', 'rounded-md');
                    if (li.getAttribute('data-field-key') === selectedFieldKey) {
                        li.classList.add('ring-2', 'ring-primary-500', 'rounded-md');
                    }
                });
            }

            function loadPropsFromHidden() {
                if (!selectedFieldKey) return;

                if (labelEl) {
                    var li = list.querySelector('[data-mp-field-item][data-field-key="' + selectedFieldKey.replace(/"/g, '\\"') + '"]');
                    labelEl.textContent = li ? (li.getAttribute('data-field-label') || selectedFieldKey) : selectedFieldKey;
                }
                if (previewModeLabel) {
                    previewModeLabel.textContent = selectedState.charAt(0).toUpperCase() + selectedState.slice(1);
                }

                if (propEls.margin) propEls.margin.value = getHiddenValue(selectedFieldKey, selectedState, 'margin');
                if (propEls.padding) propEls.padding.value = getHiddenValue(selectedFieldKey, selectedState, 'padding');
                if (propEls.border_radius) propEls.border_radius.value = getHiddenValue(selectedFieldKey, selectedState, 'border_radius');

                var bg = getHiddenValue(selectedFieldKey, selectedState, 'background');
                if (propEls.background) propEls.background.value = bg;
                if (propEls.background_color) {
                    propEls.background_color.value = isHexColor(bg) ? bg : '#ffffff';
                }

                var tc = getHiddenValue(selectedFieldKey, selectedState, 'text_color');
                if (propEls.text_color) propEls.text_color.value = tc;
                if (propEls.text_color_color) {
                    propEls.text_color_color.value = isHexColor(tc) ? tc : '#111827';
                }

                var pc = getHiddenValue(selectedFieldKey, selectedState, 'placeholder_color');
                if (propEls.placeholder_color) propEls.placeholder_color.value = pc;
                if (propEls.placeholder_color_color) {
                    propEls.placeholder_color_color.value = isHexColor(pc) ? pc : '#9ca3af';
                }

                var border = getHiddenValue(selectedFieldKey, selectedState, 'border');
                var bp = parseBorder(border);
                if (propEls.border_width) propEls.border_width.value = bp.width;
                if (propEls.border_style) propEls.border_style.value = bp.style;
                if (propEls.border_color) propEls.border_color.value = isHexColor(bp.color) ? bp.color : '#d1d5db';

                var shadow = getHiddenValue(selectedFieldKey, selectedState, 'shadow');
                var sp = parseShadow(shadow);
                if (propEls.shadow_x) propEls.shadow_x.value = sp.x;
                if (propEls.shadow_y) propEls.shadow_y.value = sp.y;
                if (propEls.shadow_blur) propEls.shadow_blur.value = sp.blur;
                if (propEls.shadow_spread) propEls.shadow_spread.value = sp.spread;
                if (propEls.shadow_color) propEls.shadow_color.value = isHexColor(sp.color) ? sp.color : '#000000';
                if (propEls.shadow) propEls.shadow.value = shadow;

                if (propEls.show_label) {
                    propEls.show_label.checked = getMetaValue(selectedFieldKey, 'show_label') !== '0';
                }
                if (propEls.icon) {
                    propEls.icon.value = getMetaValue(selectedFieldKey, 'icon');
                }

                var ic = getMetaValue(selectedFieldKey, 'icon_color');
                if (propEls.icon_color) propEls.icon_color.value = ic;
                if (propEls.icon_color_color) {
                    propEls.icon_color_color.value = isHexColor(ic) ? ic : '#9ca3af';
                }

                var iu = getMetaValue(selectedFieldKey, 'icon_upload_url');
                if (propEls.icon_upload_url) propEls.icon_upload_url.value = iu;
                if (propEls.icon_upload) propEls.icon_upload.value = '';
            }

            function selectField(fieldKey) {
                selectedFieldKey = fieldKey;
                highlightSelected();
                loadPropsFromHidden();
                renderPreview();
            }

            function setState(state) {
                selectedState = state;
                root.querySelectorAll('[data-mp-state-btn]').forEach(function (btn) {
                    btn.classList.remove('bg-primary-600', 'text-white');
                    if (btn.getAttribute('data-state') === selectedState) {
                        btn.classList.add('bg-primary-600', 'text-white');
                    }
                });
                loadPropsFromHidden();
                renderPreview();
            }

            function writeProp(prop, value) {
                if (!selectedFieldKey) return;
                setHiddenValue(selectedFieldKey, selectedState, prop, value);
                renderPreview();
            }

            function wireProps() {
                if (propEls.show_label) {
                    propEls.show_label.addEventListener('change', function () {
                        if (!selectedFieldKey) return;
                        setMetaValue(selectedFieldKey, 'show_label', propEls.show_label.checked ? '1' : '0');
                        renderPreview();
                    });
                }
                if (propEls.icon) {
                    propEls.icon.addEventListener('change', function () {
                        if (!selectedFieldKey) return;
                        setMetaValue(selectedFieldKey, 'icon', String(propEls.icon.value || ''));
                        renderPreview();
                    });
                }

                function syncIconColorFromText() {
                    var v = propEls.icon_color ? propEls.icon_color.value : '';
                    setMetaValue(selectedFieldKey, 'icon_color', String(v || ''));
                    if (propEls.icon_color_color && isHexColor(v)) {
                        propEls.icon_color_color.value = v;
                    }
                    renderPreview();
                }

                function syncIconColorFromColor() {
                    var v = propEls.icon_color_color ? propEls.icon_color_color.value : '';
                    if (propEls.icon_color) propEls.icon_color.value = v;
                    setMetaValue(selectedFieldKey, 'icon_color', String(v || ''));
                    renderPreview();
                }

                if (propEls.icon_color) propEls.icon_color.addEventListener('input', function () {
                    if (!selectedFieldKey) return;
                    syncIconColorFromText();
                });
                if (propEls.icon_color_color) {
                    propEls.icon_color_color.addEventListener('input', function () {
                        if (!selectedFieldKey) return;
                        syncIconColorFromColor();
                    });
                    propEls.icon_color_color.addEventListener('change', function () {
                        if (!selectedFieldKey) return;
                        syncIconColorFromColor();
                    });
                }

                var uploadUrl = @json(route('customer.lists.forms.upload-icon', $list));

                if (propEls.icon_upload) {
                    propEls.icon_upload.addEventListener('change', function () {
                        if (!selectedFieldKey) return;
                        var f = propEls.icon_upload.files && propEls.icon_upload.files[0];
                        if (!f) return;

                        var fd = new FormData();
                        fd.append('icon', f);

                        var tokenEl = document.querySelector('meta[name="csrf-token"]');
                        var token = tokenEl ? tokenEl.getAttribute('content') : '';

                        fetch(uploadUrl, {
                            method: 'POST',
                            headers: token ? { 'X-CSRF-TOKEN': token } : {},
                            body: fd,
                            credentials: 'same-origin'
                        }).then(function (r) {
                            return r.json();
                        }).then(function (data) {
                            var url = data && data.url ? String(data.url) : '';
                            setMetaValue(selectedFieldKey, 'icon_upload_url', url);
                            if (propEls.icon_upload_url) propEls.icon_upload_url.value = url;
                            if (propEls.icon_upload) propEls.icon_upload.value = '';
                            renderPreview();
                        }).catch(function () {
                            if (propEls.icon_upload) propEls.icon_upload.value = '';
                        });
                    });
                }

                if (propEls.icon_upload_remove) {
                    propEls.icon_upload_remove.addEventListener('click', function () {
                        if (!selectedFieldKey) return;
                        setMetaValue(selectedFieldKey, 'icon_upload_url', '');
                        if (propEls.icon_upload_url) propEls.icon_upload_url.value = '';
                        if (propEls.icon_upload) propEls.icon_upload.value = '';
                        renderPreview();
                    });
                }

                if (propEls.margin) {
                    propEls.margin.addEventListener('input', function () { writeProp('margin', propEls.margin.value); });
                }
                if (propEls.padding) {
                    propEls.padding.addEventListener('input', function () { writeProp('padding', propEls.padding.value); });
                }
                if (propEls.border_radius) {
                    propEls.border_radius.addEventListener('input', function () { writeProp('border_radius', propEls.border_radius.value); });
                }

                function syncBackgroundFromText() {
                    var v = propEls.background ? propEls.background.value : '';
                    writeProp('background', v);
                    if (propEls.background_color && isHexColor(v)) {
                        propEls.background_color.value = v;
                    }
                }

                function syncBackgroundFromColor() {
                    var v = propEls.background_color ? propEls.background_color.value : '';
                    if (propEls.background) propEls.background.value = v;
                    writeProp('background', v);
                }

                if (propEls.background) propEls.background.addEventListener('input', syncBackgroundFromText);
                if (propEls.background_color) {
                    propEls.background_color.addEventListener('input', syncBackgroundFromColor);
                    propEls.background_color.addEventListener('change', syncBackgroundFromColor);
                }

                function syncTextColorFromText() {
                    var v = propEls.text_color ? propEls.text_color.value : '';
                    writeProp('text_color', v);
                    if (propEls.text_color_color && isHexColor(v)) {
                        propEls.text_color_color.value = v;
                    }
                }

                function syncTextColorFromColor() {
                    var v = propEls.text_color_color ? propEls.text_color_color.value : '';
                    if (propEls.text_color) propEls.text_color.value = v;
                    writeProp('text_color', v);
                }

                if (propEls.text_color) propEls.text_color.addEventListener('input', syncTextColorFromText);
                if (propEls.text_color_color) {
                    propEls.text_color_color.addEventListener('input', syncTextColorFromColor);
                    propEls.text_color_color.addEventListener('change', syncTextColorFromColor);
                }

                function syncPlaceholderColorFromText() {
                    var v = propEls.placeholder_color ? propEls.placeholder_color.value : '';
                    writeProp('placeholder_color', v);
                    if (propEls.placeholder_color_color && isHexColor(v)) {
                        propEls.placeholder_color_color.value = v;
                    }
                }

                function syncPlaceholderColorFromColor() {
                    var v = propEls.placeholder_color_color ? propEls.placeholder_color_color.value : '';
                    if (propEls.placeholder_color) propEls.placeholder_color.value = v;
                    writeProp('placeholder_color', v);
                }

                if (propEls.placeholder_color) propEls.placeholder_color.addEventListener('input', syncPlaceholderColorFromText);
                if (propEls.placeholder_color_color) {
                    propEls.placeholder_color_color.addEventListener('input', syncPlaceholderColorFromColor);
                    propEls.placeholder_color_color.addEventListener('change', syncPlaceholderColorFromColor);
                }

                function syncBorderFromControls() {
                    var b = buildBorder(
                        propEls.border_width ? propEls.border_width.value : '',
                        propEls.border_style ? propEls.border_style.value : '',
                        propEls.border_color ? propEls.border_color.value : ''
                    );
                    writeProp('border', b);
                }
                if (propEls.border_width) propEls.border_width.addEventListener('input', syncBorderFromControls);
                if (propEls.border_style) propEls.border_style.addEventListener('change', syncBorderFromControls);
                if (propEls.border_color) {
                    propEls.border_color.addEventListener('input', syncBorderFromControls);
                    propEls.border_color.addEventListener('change', syncBorderFromControls);
                }

                function syncShadowFromControls() {
                    var s = buildShadow(
                        propEls.shadow_x ? propEls.shadow_x.value : '',
                        propEls.shadow_y ? propEls.shadow_y.value : '',
                        propEls.shadow_blur ? propEls.shadow_blur.value : '',
                        propEls.shadow_spread ? propEls.shadow_spread.value : '',
                        propEls.shadow_color ? propEls.shadow_color.value : ''
                    );
                    if (propEls.shadow) propEls.shadow.value = s;
                    writeProp('shadow', s);
                }
                if (propEls.shadow_x) propEls.shadow_x.addEventListener('input', syncShadowFromControls);
                if (propEls.shadow_y) propEls.shadow_y.addEventListener('input', syncShadowFromControls);
                if (propEls.shadow_blur) propEls.shadow_blur.addEventListener('input', syncShadowFromControls);
                if (propEls.shadow_spread) propEls.shadow_spread.addEventListener('input', syncShadowFromControls);
                if (propEls.shadow_color) {
                    propEls.shadow_color.addEventListener('input', syncShadowFromControls);
                    propEls.shadow_color.addEventListener('change', syncShadowFromControls);
                }

                if (propEls.shadow) {
                    propEls.shadow.addEventListener('input', function () {
                        writeProp('shadow', propEls.shadow.value);
                        var sp = parseShadow(propEls.shadow.value);
                        if (propEls.shadow_x) propEls.shadow_x.value = sp.x;
                        if (propEls.shadow_y) propEls.shadow_y.value = sp.y;
                        if (propEls.shadow_blur) propEls.shadow_blur.value = sp.blur;
                        if (propEls.shadow_spread) propEls.shadow_spread.value = sp.spread;
                        if (propEls.shadow_color && isHexColor(sp.color)) propEls.shadow_color.value = sp.color;
                    });
                }
            }

            function setupDragList() {
                var dragging = null;

                list.querySelectorAll('[data-mp-field-item]').forEach(function (item) {
                    item.addEventListener('dragstart', function (e) {
                        dragging = item;
                        e.dataTransfer && (e.dataTransfer.effectAllowed = 'move');
                        item.classList.add('opacity-60');
                    });

                    item.addEventListener('dragend', function () {
                        item.classList.remove('opacity-60');
                        dragging = null;
                        renderPreview();
                    });

                    item.addEventListener('dragover', function (e) {
                        if (!dragging || dragging === item) return;
                        e.preventDefault();
                        var rect = item.getBoundingClientRect();
                        var before = (e.clientY - rect.top) < rect.height / 2;
                        if (before) {
                            item.parentNode && item.parentNode.insertBefore(dragging, item);
                        } else {
                            item.parentNode && item.parentNode.insertBefore(dragging, item.nextSibling);
                        }
                    });

                    var selectBtn = item.querySelector('[data-mp-select-field]');
                    if (selectBtn) {
                        selectBtn.addEventListener('click', function () {
                            var k = item.getAttribute('data-field-key');
                            if (k) selectField(k);
                        });
                    }

                    var enabledCb = item.querySelector('[data-mp-enabled]');
                    if (enabledCb) {
                        enabledCb.addEventListener('change', function () {
                            renderPreview();
                        });
                    }
                });
            }

            root.querySelectorAll('[data-mp-state-btn]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var st = btn.getAttribute('data-state') || 'normal';
                    setState(st);
                });
            });

            wireProps();
            setupDragList();

            var firstItem = list.querySelector('[data-mp-field-item]');
            if (firstItem) {
                selectField(firstItem.getAttribute('data-field-key'));
            }
            setState('normal');
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupBuilder);
        } else {
            setupBuilder();
        }
    })();
</script>
@endsection

