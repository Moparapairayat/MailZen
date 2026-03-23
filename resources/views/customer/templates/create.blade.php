@extends('layouts.customer-builder')

@section('title', 'Create Template')
@section('page-title', 'Create Template')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/grapesjs@0.21.7/dist/css/grapes.min.css">
<style>
    #gjs-editor {
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        height: 100%;
        min-height: 0;
    }
    .gjs-editor {
        height: 100%;
        min-height: 0;
    }
    .gjs-devices-c select{
        height: 26px;
        width: 100px;
        background-color: transparent !important;
        color: #fff !important;
        padding: 1px 10px;
    }
    .gjs-pn-views-container{
        width: 20%;
    }
    .gjs-pn-views{
        border-bottom: none;
    }
    .gjs-pn-options{
        z-index: 9;
    }
    [data-gjs-type="wrapper"] {
        width: 75% !important;
        margin: auto !important;
    }
    html:not(.dark) .customer-theme .gjs-radio-item .gjs-radio-item-label{
        color: #DDD !important;
        border-color: #555 !important;
    }
    .gjs-radio-item input:checked+.gjs-radio-item-label{
        color: #fff !important;
        border-color: #ddd !important;
    }

</style>
@endpush

@section('content')
<div class="h-screen overflow-hidden flex flex-col" x-data="templateImportModal(@js([
    "galleryUrl" => route('customer.templates.import.gallery'),
    "contentUrlBase" => url('/customer/templates/import'),
    "aiUrl" => route('customer.templates.ai-generate'),
    "csrfToken" => csrf_token(),
    "builder" => 'grapesjs',
    "canImport" => (bool) auth('customer')->user()?->groupAllows('templates.permissions.can_import_templates'),
    "canAi" => (bool) auth('customer')->user()?->groupAllows('templates.permissions.can_use_ai_creator'),
    "initialTab" => (bool) auth('customer')->user()?->groupAllows('templates.permissions.can_use_ai_creator') && !(bool) auth('customer')->user()?->groupAllows('templates.permissions.can_import_templates') ? 'ai' : 'templates',
]))">
    <form id="template-form" method="POST" action="{{ route('customer.templates.store') }}" class="shrink-0 space-y-6 px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-white/80 dark:bg-gray-900/80 backdrop-blur">
        @csrf
        
        <!-- Template Info -->
        <x-card title="Template Information">
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Template Name *</label>
                    <input type="text" name="name" id="name" required value="{{ old('name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                    <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">{{ old('description') }}</textarea>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                        <select name="type" id="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="email" {{ ($type ?? '') === 'email' ? 'selected' : '' }}>Email</option>
                            <option value="campaign" {{ ($type ?? '') === 'campaign' ? 'selected' : '' }}>Campaign</option>
                            <option value="transactional" {{ ($type ?? '') === 'transactional' ? 'selected' : '' }}>Transactional</option>
                            <option value="autoresponder" {{ ($type ?? '') === 'autoresponder' ? 'selected' : '' }}>Autoresponder</option>
                            <option value="footer" {{ ($type ?? '') === 'footer' ? 'selected' : '' }}>Footer Template</option>
                            <option value="signature" {{ ($type ?? '') === 'signature' ? 'selected' : '' }}>Signature</option>
                        </select>
                    </div>
                    
                    <div class="flex items-center pt-6">
                        <input type="checkbox" name="is_public" id="is_public" value="1" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                        <label for="is_public" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Make this template public</label>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- Template Builder -->
        <div class="hidden" aria-hidden="true"></div>

        <!-- Hidden fields for template data -->
        <input type="hidden" name="html_content" id="html_content" value="{{ old('html_content') }}">
        <input type="hidden" name="plain_text_content" id="plain_text_content" value="{{ old('plain_text_content') }}">
        <input type="hidden" name="grapesjs_data" id="grapesjs_data" value="{{ old('grapesjs_data') }}">
        <input type="hidden" name="settings[origin]" value="">

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
            @if(auth('customer')->user()?->groupAllows('templates.permissions.can_import_templates') || auth('customer')->user()?->groupAllows('templates.permissions.can_use_ai_creator'))
                <button type="button" @click="open()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
                    Import templates
                </button>
            @endif
            <a href="{{ route('customer.templates.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
                Cancel
            </a>
            @customercan('templates.permissions.can_create_templates')
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700">
                    Save Template
                </button>
            @endcustomercan
        </div>
    </form>

    <div class="flex-1 min-h-0 p-4">
        <div id="gjs-editor" class="h-full w-full"></div>
    </div>

    @if(auth('customer')->user()?->groupAllows('templates.permissions.can_import_templates') || auth('customer')->user()?->groupAllows('templates.permissions.can_use_ai_creator'))
        <div x-cloak x-show="($data.importOpen ?? false)" class="fixed inset-0 z-50 flex items-center justify-center" aria-modal="true" role="dialog">
            <div class="fixed inset-0 bg-black/50" @click="close()"></div>
            <div class="relative w-full max-w-6xl mx-4 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Import templates</h3>
                    <button type="button" @click="close()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <span class="sr-only">Close</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="px-6 pt-4">
                    <div class="flex items-center gap-6 border-b border-gray-200 dark:border-gray-700">
                        @customercan('templates.permissions.can_import_templates')
                            <button type="button" @click="setTab('templates')" class="py-3 text-sm font-medium" :class="($data.tab ?? '') === 'templates' ? 'text-primary-600 border-b-2 !border-primary-600' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'">Templates</button>
                        @endcustomercan
                        @customercan('templates.permissions.can_use_ai_creator')
                            <button type="button" @click="setTab('ai')" class="py-3 text-sm font-medium" :class="($data.tab ?? '') === 'ai' ? 'text-primary-600 border-b-2 !border-primary-600' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'">AI Creator</button>
                        @endcustomercan
                    </div>
                </div>

                <div class="px-6 py-4">
                    <template x-if="($data.error ?? '')">
                        <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-200" x-text="$data.error"></div>
                    </template>

                    <div x-show="($data.tab ?? '') !== 'ai'" class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Templates</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400" x-show="($data.loading ?? false)">Loading...</div>
                            </div>
                            <div class="max-h-[520px] overflow-auto divide-y divide-gray-200 dark:divide-gray-700">
                                <template x-for="item in ($data.gallery ?? [])" :key="item.id">
                                    <button type="button" class="w-full text-left px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700" @click="select(item)" :class="($data.selected && $data.selected.id === item.id) ? 'bg-gray-50 dark:bg-gray-700' : ''">
                                        <div class="flex items-center gap-2">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="item.name"></div>
                                            <template x-if="item && item.is_ai">
                                                <span class="inline-flex items-center rounded-full bg-primary-100 px-2 py-0.5 text-[10px] font-semibold text-primary-800 dark:bg-primary-900 dark:text-primary-200">AI</span>
                                            </template>
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400" x-text="item.description || ''"></div>
                                    </button>
                                </template>
                                <div x-show="!($data.loading ?? false) && (($data.gallery ?? []).length === 0)" class="px-4 py-6 text-sm text-gray-500 dark:text-gray-400">No templates found.</div>
                            </div>
                        </div>

                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Preview</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400" x-show="($data.previewLoading ?? false)">Loading...</div>
                            </div>
                            <div class="p-4 space-y-3">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="$data.preview?.name || 'Select a template'"></div>
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg bg-white overflow-hidden">
                                    <iframe class="w-full h-[420px] border-0" style="min-height: 420px;" loading="lazy" sandbox="allow-scripts allow-same-origin" x-bind:srcdoc="$data.preview?.html_content || '<p style=&quot;padding:12px&quot;>No preview</p>'"></iframe>
                                </div>
                            </div>
                            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-end gap-3">
                                <button type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600" @click="close()">Cancel</button>
                                <button type="button" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700 disabled:opacity-60" :disabled="!($data.preview ?? null) || ($data.importing ?? false)" @click="importSelected()">
                                    <span x-show="!($data.importing ?? false)">Import</span>
                                    <span x-show="($data.importing ?? false)">Importing...</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    @customercan('templates.permissions.can_use_ai_creator')
                        <template x-if="$data.ai">
                            <div x-show="($data.tab ?? '') === 'ai'" class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Provider</label>
                                    <select class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" x-model="$data.ai.provider">
                                        <option value="chatgpt">ChatGPT</option>
                                        <option value="gemini">Gemini</option>
                                        <option value="claude">Claude</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Model</label>
                                    <select class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" x-model="$data.ai.model">
                                        <option value="">Default</option>
                                        <template x-for="m in aiModelsForProvider()" :key="m">
                                            <option :value="m" x-text="m"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Prompt</label>
                                    <textarea rows="8" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" x-model="$data.ai.prompt" placeholder="Describe the email template you want..."></textarea>
                                </div>
                                <template x-if="($data.ai?.error ?? '')">
                                    <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-200" x-text="$data.ai.error"></div>
                                </template>
                                <div class="flex items-center justify-end gap-3">
                                    <button type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600" @click="close()">Cancel</button>
                                    <button type="button" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700 disabled:opacity-60" :disabled="($data.ai?.loading ?? false) || !($data.ai?.prompt ?? '')" @click="aiGenerate()">
                                        <span x-show="!($data.ai?.loading ?? false)">Generate</span>
                                        <span x-show="($data.ai?.loading ?? false)">Generating...</span>
                                    </button>
                                </div>
                            </div>

                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Preview</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400" x-show="Number.isFinite(Number($data.preview?.tokens_used))">Tokens: <span x-text="$data.preview?.tokens_used"></span></div>
                                </div>
                                <div class="p-4">
                                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg bg-white overflow-hidden">
                                        <iframe class="w-full h-[520px] border-0" style="min-height: 520px;" loading="lazy" sandbox="allow-scripts allow-same-origin" x-bind:srcdoc="$data.preview?.html_content || '<p style=&quot;padding:12px&quot;>Generate a template to preview</p>'"></iframe>
                                    </div>
                                </div>
                                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-end gap-3">
                                    <button type="button" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700 disabled:opacity-60" :disabled="!($data.preview ?? null) || ($data.importing ?? false)" @click="importSelected()">
                                        <span x-show="!($data.importing ?? false)">Import into builder</span>
                                        <span x-show="($data.importing ?? false)">Importing...</span>
                                    </button>
                                </div>
                            </div>
                            </div>
                        </template>
                    @endcustomercan
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/grapesjs@0.21.7/dist/grapes.min.js"></script>
<script>
(function() {
    console.log('Script loading...');
    
    function initEditor() {
        console.log('Initializing GrapesJS...');
        const container = document.getElementById('gjs-editor');
        console.log('Container exists:', container !== null);
        
        if (!container) {
            console.error('Container #gjs-editor not found!');
            return;
        }
        
        if (typeof grapesjs === 'undefined') {
            console.error('GrapesJS not loaded!');
            setTimeout(initEditor, 100);
            return;
        }

        console.log('GrapesJS available, initializing...');
        
        const editor = grapesjs.init({
            container: '#gjs-editor',
            height: '100%',
            width: '100%',
            fromElement: false,
            storageManager: false,
            deviceManager: {
                devices: [
                    { name: 'Desktop', width: '' },
                    { name: 'Tablet', width: '768px', widthMedia: '992px' },
                    { name: 'Mobile', width: '320px', widthMedia: '768px' }
                ]
            },
        });

        // NOTE: GrapesJS renders the canvas in an iframe, so page-level CSS won't affect the wrapper.
        // Inject canvas CSS here.
        editor.addStyle(`
            [data-gjs-type="wrapper"] {
                width: 75% !important;
                margin: auto !important;
            }
        `);

        console.log('GrapesJS initialized:', editor);
        console.log('BlockManager:', editor.BlockManager);
        console.log('Panels:', editor.Panels);

        window.__mailpurseGrapesEditor = editor;

        // Set initial content
        editor.setComponents('<div style="padding: 20px;">Start building your template...</div>');

        // Add some default blocks
        const blockManager = editor.BlockManager;

        const icons = {
            heading: '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6v12" /><path d="M20 6v12" /><path d="M4 12h16" /></svg>',
            text: '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16" /><path d="M4 12h10" /><path d="M4 18h14" /></svg>',
            link: '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 1 0-7l1-1a5 5 0 0 1 7 7l-1 1" /><path d="M14 11a5 5 0 0 1 0 7l-1 1a5 5 0 0 1-7-7l1-1" /></svg>',
            image: '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2" /><path d="M8 11a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" /><path d="m21 16-5-5-4 4-2-2-5 5" /></svg>',
            button: '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="8" width="16" height="8" rx="4" /><path d="M9 12h6" /></svg>',
            divider: '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12h16" /><path d="M6 9h0" /><path d="M18 15h0" /></svg>',
            section: '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="6" width="16" height="12" rx="2" /><path d="M7 9h10" /><path d="M7 12h7" /></svg>',
            col1: '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="6" width="16" height="12" rx="2" /></svg>',
            col2: '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="6" width="16" height="12" rx="2" /><path d="M12 6v12" /></svg>',
            col3: '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="6" width="16" height="12" rx="2" /><path d="M10 6v12" /><path d="M14 6v12" /></svg>',
        };

        blockManager.add('heading', {
            label: 'Heading',
            media: icons.heading,
            content: '<h1 style="margin: 0 0 12px; font-size: 28px; line-height: 1.2; font-weight: 700;">Heading</h1>',
            category: 'Basic',
        });
        
        blockManager.add('text', {
            label: 'Text',
            media: icons.text,
            content: '<div data-gjs-type="text">Insert your text here</div>',
            category: 'Basic',
        });

        blockManager.add('link', {
            label: 'Link',
            media: icons.link,
            content: {
                type: 'link',
                content: 'Link',
                attributes: { href: '#', target: '_blank' },
                style: { color: '#007bff', 'text-decoration': 'underline' }
            },
            category: 'Basic',
        });

        blockManager.add('image', {
            label: 'Image',
            media: icons.image,
            content: {
                type: 'image',
                src: 'https://via.placeholder.com/350x250/78c5d6/fff',
                style: { width: '100%' }
            },
            category: 'Basic',
        });

        blockManager.add('button', {
            label: 'Button',
            media: icons.button,
            content: '<a href="#" style="display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px;">Click me</a>',
            category: 'Basic',
        });

        blockManager.add('divider', {
            label: 'Divider',
            media: icons.divider,
            content: '<hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;">',
            category: 'Basic',
        });

        blockManager.add('section', {
            label: 'Section',
            media: icons.section,
            content: '<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background: #f5f5f5;"><tr><td style="padding: 20px;"><div>Section content</div></td></tr></table>',
            category: 'Layout',
        });

        blockManager.add('column1', {
            label: '1 Column',
            media: icons.col1,
            content: '<table width="100%" cellpadding="0" cellspacing="0" role="presentation"><tr><td style="padding: 10px;"><div style="border: 1px dashed #d1d5db; padding: 12px;">Column</div></td></tr></table>',
            category: 'Layout',
        });

        blockManager.add('column2', {
            label: '2 Columns',
            media: icons.col2,
            content: '<table width="100%" cellpadding="0" cellspacing="0" role="presentation"><tr><td width="50%" valign="top" style="padding: 10px;"><div style="border: 1px dashed #d1d5db; padding: 12px;">Column 1</div></td><td width="50%" valign="top" style="padding: 10px;"><div style="border: 1px dashed #d1d5db; padding: 12px;">Column 2</div></td></tr></table>',
            category: 'Layout',
        });

        blockManager.add('column3', {
            label: '3 Columns',
            media: icons.col3,
            content: '<table width="100%" cellpadding="0" cellspacing="0" role="presentation"><tr><td width="33.33%" valign="top" style="padding: 10px;"><div style="border: 1px dashed #d1d5db; padding: 12px;">Column 1</div></td><td width="33.33%" valign="top" style="padding: 10px;"><div style="border: 1px dashed #d1d5db; padding: 12px;">Column 2</div></td><td width="33.33%" valign="top" style="padding: 10px;"><div style="border: 1px dashed #d1d5db; padding: 12px;">Column 3</div></td></tr></table>',
            category: 'Layout',
        });

        console.log('Blocks added. Total blocks:', blockManager.getAll().length);
        console.log('All blocks:', blockManager.getAll());

        // Save template data before form submission
        const form = document.getElementById('template-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const html = editor.getHtml();
                const css = editor.getCss();
                const fullHtml = `<style>${css}</style>${html}`;
                
                document.getElementById('html_content').value = fullHtml;
                document.getElementById('plain_text_content').value = editor.getText();
                document.getElementById('grapesjs_data').value = JSON.stringify({
                    components: editor.getComponents().toJSON(),
                    styles: editor.getStyle(),
                });
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEditor);
    } else {
        initEditor();
    }
})();
</script>
@endpush

