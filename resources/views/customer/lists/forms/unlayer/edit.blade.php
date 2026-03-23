@extends('layouts.customer')

@section('title', 'Design Subscription Form')
@section('page-title', 'Design Form: ' . $form->name)

@push('styles')
<style>
    #editor-container {
        height: calc(100vh - 310px);
        min-height: 700px;
    }
</style>
@endpush

@section('content')
<div class="space-y-4">
    <form id="unlayer-form" method="POST" action="{{ route('customer.lists.forms.unlayer.update', [$list, $form]) }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex-1 min-w-0">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    Use placeholders in your design:
                    <span class="font-mono">{{ '{' }}{{ '{' }}fields{{ '}' }}{{ '}' }}</span>,
                    <span class="font-mono">{{ '{' }}{{ '{' }}gdpr{{ '}' }}{{ '}' }}</span>,
                    <span class="font-mono">{{ '{' }}{{ '{' }}submit{{ '}' }}{{ '}' }}</span>
                </div>
            </div>

            <div class="flex items-center justify-between lg:justify-end gap-3 pt-6">
                <a href="{{ route('customer.lists.forms.show', [$list, $form]) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
                    Cancel
                </a>

                <button type="button" id="btn-save" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700">
                    Save
                </button>
            </div>
        </div>

        <input type="hidden" name="html_content" id="html_content" value="{{ old('html_content', $form->html_content) }}">
        <input type="hidden" name="plain_text_content" id="plain_text_content" value="{{ old('plain_text_content', $form->plain_text_content) }}">
        <input type="hidden" name="builder_data" id="grapesjs_data" value="{{ old('builder_data', json_encode($form->builder_data)) }}">
    </form>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
        <script type="application/json" id="unlayer-design-json">@json($unlayerDesign)</script>
        @php
            $unlayerDisplayMode = $form->type === 'popup' ? 'popup' : 'web';

            $selectedFields = is_array($form->fields) ? $form->fields : ['email'];
            if (!in_array('email', $selectedFields, true)) {
                $selectedFields[] = 'email';
            }

            $customDefs = is_array($list->custom_fields) ? $list->custom_fields : [];
            $customDefsByKey = [];
            foreach ($customDefs as $def) {
                if (!is_array($def)) {
                    continue;
                }
                $k = trim((string) ($def['key'] ?? ''));
                if ($k === '') {
                    continue;
                }
                $customDefsByKey[$k] = $def;
            }

            $standardLabels = [
                'email' => 'Email',
                'first_name' => 'First Name',
                'last_name' => 'Last Name',
            ];

            $inputOptions = [];
            $textareaOptions = [];

            foreach ($selectedFields as $f) {
                if (!is_string($f) || trim($f) === '') {
                    continue;
                }

                if (isset($standardLabels[$f])) {
                    $inputOptions[] = ['label' => $standardLabels[$f], 'value' => $f];
                    continue;
                }

                if (!str_starts_with($f, 'cf:')) {
                    continue;
                }

                $key = trim(substr($f, 3));
                $def = $customDefsByKey[$key] ?? null;
                if (!$def) {
                    continue;
                }

                $label = trim((string) ($def['label'] ?? $key));
                $type = (string) ($def['type'] ?? 'text');
                $opt = ['label' => $label, 'value' => 'cf:' . $key];

                if ($type === 'textarea') {
                    $textareaOptions[] = $opt;
                } else {
                    $inputOptions[] = $opt;
                }
            }

            $unlayerFieldConfig = [
                'input' => ['options' => $inputOptions],
                'textarea' => ['options' => $textareaOptions],
            ];
        @endphp
        <script type="application/json" id="mailpurse-unlayer-fields-json">@json($unlayerFieldConfig)</script>
        <div
            id="editor-container"
            data-unlayer-editor
            data-unlayer-display-mode="{{ $unlayerDisplayMode }}"
            data-unlayer-project-id="{{ $unlayerProjectId }}"
            data-unlayer-design-script-id="unlayer-design-json"
            data-unlayer-fields-script-id="mailpurse-unlayer-fields-json"
            data-unlayer-form-action-url="{{ route('public.subscribe.api', $form->slug) }}"
        ></div>
    </div>
</div>
@endsection
