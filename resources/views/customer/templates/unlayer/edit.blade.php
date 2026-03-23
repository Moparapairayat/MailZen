@extends('layouts.customer-builder')

@section('title', 'Edit Template')
@section('page-title', 'Edit Template')

@push('styles')
<style>
    #editor-container {
        height: 100%;
        min-height: 0;
    }
</style>
@endpush

@section('content')
<div class="h-screen overflow-hidden flex flex-col" x-data="templateImportModal(@js([
    "galleryUrl" => route('customer.templates.import.gallery'),
    "contentUrlBase" => url('/customer/templates/import'),
    "aiUrl" => route('customer.templates.ai-generate'),
    "csrfToken" => csrf_token(),
    "builder" => 'unlayer',
    "canImport" => (bool) auth('customer')->user()?->groupAllows('templates.permissions.can_import_templates'),
    "canAi" => (bool) auth('customer')->user()?->groupAllows('templates.permissions.can_use_ai_creator'),
    "initialTab" => (bool) auth('customer')->user()?->groupAllows('templates.permissions.can_use_ai_creator') && !(bool) auth('customer')->user()?->groupAllows('templates.permissions.can_import_templates') ? 'ai' : 'templates',
]))">
    <form id="unlayer-form" method="POST" action="{{ route('customer.templates.unlayer.update', $template) }}" class="shrink-0 space-y-4 px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-white/80 dark:bg-gray-900/80 backdrop-blur">
        @csrf
        @method('PUT')

        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex-1 min-w-0">
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Template Name *</label>
                <input type="text" name="name" id="name" required value="{{ old('name', $template->name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="w-full lg:w-56">
                <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                <select name="type" id="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="email" {{ ($template->type === 'email') ? 'selected' : '' }}>Email</option>
                    <option value="campaign" {{ ($template->type === 'campaign') ? 'selected' : '' }}>Campaign</option>
                    <option value="transactional" {{ ($template->type === 'transactional') ? 'selected' : '' }}>Transactional</option>
                    <option value="autoresponder" {{ ($template->type === 'autoresponder') ? 'selected' : '' }}>Autoresponder</option>
                    <option value="footer" {{ ($template->type === 'footer') ? 'selected' : '' }}>Footer Template</option>
                    <option value="signature" {{ ($template->type === 'signature') ? 'selected' : '' }}>Signature</option>
                </select>
            </div>

            <div class="flex items-center justify-between lg:justify-end gap-3 pt-6">
                <div class="flex items-center">
                    <input type="checkbox" name="is_public" id="is_public" value="1" {{ $template->is_public ? 'checked' : '' }} class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                    <label for="is_public" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Public</label>
                </div>

                @if(auth('customer')->user()?->groupAllows('templates.permissions.can_import_templates') || auth('customer')->user()?->groupAllows('templates.permissions.can_use_ai_creator'))
                    <button type="button" @click="open()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
                        Import templates
                    </button>
                @endif

                <a href="{{ route('customer.templates.show', $template) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
                    Cancel
                </a>

                <button type="button" id="btn-save" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700">
                    Save
                </button>
            </div>
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
            <textarea name="description" id="description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">{{ old('description', $template->description) }}</textarea>
        </div>

        <input type="hidden" name="html_content" id="html_content" value="{{ old('html_content', $template->html_content) }}">
        <input type="hidden" name="plain_text_content" id="plain_text_content" value="{{ old('plain_text_content', $template->plain_text_content) }}">
        <input type="hidden" name="grapesjs_data" id="grapesjs_data" value="{{ old('grapesjs_data', json_encode($template->grapesjs_data)) }}">
        <input type="hidden" name="settings[origin]" value="{{ old('settings.origin', is_array($template->settings) ? ($template->settings['origin'] ?? '') : '') }}">
    </form>

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

                    <div x-show="($data.tab ?? '') !== 'ai'">
                        {{-- Category badges --}}
                        <div class="flex flex-wrap gap-2 mb-6">
                            <template x-for="cat in ($data.visibleCategories ?? [])" :key="cat.id">
                                <button
                                    type="button"
                                    @click="setCategory(cat.id)"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-full transition-colors"
                                    :class="($data.activeCategory ?? 'all') === cat.id
                                        ? 'bg-primary-600 text-white'
                                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'"
                                >
                                    <template x-if="cat.icon === 'grid'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                                    </template>
                                    <template x-if="cat.icon === 'megaphone'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                                    </template>
                                    <template x-if="cat.icon === 'zap'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                    </template>
                                    <template x-if="cat.icon === 'send'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                    </template>
                                    <template x-if="cat.icon === 'receipt'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                    </template>
                                    <template x-if="cat.icon === 'newspaper'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                                    </template>
                                    <template x-if="cat.icon === 'sparkles'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                                    </template>
                                    <template x-if="cat.icon === 'folder'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                                    </template>
                                    <template x-if="cat.icon === 'heart'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                                    </template>
                                    <template x-if="cat.icon === 'headphones'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 18v-6a9 9 0 0118 0v6M3 18a3 3 0 003 3h1a1 1 0 001-1v-4a1 1 0 00-1-1H4a3 3 0 00-1 3zm18 0a3 3 0 01-3 3h-1a1 1 0 01-1-1v-4a1 1 0 011-1h2a3 3 0 011 3z"></path></svg>
                                    </template>
                                    <template x-if="cat.icon === 'shopping'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                    </template>
                                    <span x-text="cat.name"></span>
                                </button>
                            </template>
                        </div>

                        {{-- Loading state --}}
                        <div x-show="($data.loading ?? false)" class="flex items-center justify-center py-12">
                            <svg class="animate-spin h-8 w-8 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>

                        {{-- Template grid --}}
                        <div x-show="!($data.loading ?? false)" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 max-h-[520px] overflow-y-auto pr-1">
                            <template x-for="item in ($data.filteredGallery ?? [])" :key="item.id">
                                <div class="group relative bg-gray-900 dark:bg-gray-950 rounded-lg overflow-hidden aspect-[4/3] cursor-pointer">
                                    {{-- Thumbnail or placeholder --}}
                                    <template x-if="item.thumbnail">
                                        <img :src="item.thumbnail" :alt="item.name" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!item.thumbnail">
                                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-700 to-gray-900">
                                            <svg class="w-12 h-12 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    </template>

                                    {{-- Hover overlay --}}
                                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center gap-3">
                                        <button
                                            type="button"
                                            @click.stop="insertTemplate(item)"
                                            class="p-3 bg-primary-600 hover:bg-primary-700 rounded-full text-white transition-colors"
                                            title="Insert template"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                            </svg>
                                        </button>
                                        <button
                                            type="button"
                                            @click.stop="openPreviewModal(item)"
                                            class="p-3 bg-gray-600 hover:bg-gray-700 rounded-full text-white transition-colors"
                                            title="Preview template"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </button>
                                    </div>

                                    {{-- Template name --}}
                                    <div class="absolute bottom-0 left-0 right-0 p-3 bg-gradient-to-t from-black/80 to-transparent">
                                        <div class="text-sm font-medium text-white truncate" x-text="item.name"></div>
                                        <template x-if="item.is_ai">
                                            <span class="inline-flex items-center mt-1 rounded-full bg-primary-500/80 px-2 py-0.5 text-[10px] font-semibold text-white">AI</span>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            {{-- Empty state --}}
                            <div x-show="!($data.loading ?? false) && (($data.filteredGallery ?? []).length === 0)" class="col-span-full flex flex-col items-center justify-center py-12 text-gray-500 dark:text-gray-400">
                                <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                </svg>
                                <p class="text-sm">No templates found in this category.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Preview Modal --}}
                    <div x-cloak x-show="($data.previewModalOpen ?? false)" class="fixed inset-0 z-[60] flex items-center justify-center">
                        <div class="fixed inset-0 bg-black/60" @click="closePreviewModal()"></div>
                        <div class="relative w-full max-w-4xl mx-4 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 max-h-[90vh] flex flex-col">
                            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="$data.previewModalItem?.name || 'Preview'"></h3>
                                <button type="button" @click="closePreviewModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div class="flex-1 overflow-hidden p-4">
                                <div x-show="($data.previewModalLoading ?? false)" class="flex flex-col items-center justify-center h-[500px] border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-900">
                                    <svg class="animate-spin h-10 w-10 text-primary-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Rendering preview...</p>
                                </div>
                                <div x-show="!($data.previewModalLoading ?? false)" x-cloak class="h-[500px] border border-gray-200 dark:border-gray-700 rounded-lg bg-white overflow-hidden">
                                    <iframe class="w-full h-full border-0" loading="lazy" sandbox="allow-scripts allow-same-origin" x-bind:srcdoc="$data.previewModalData?.html_content || '<p style=&quot;padding:12px&quot;>No preview available</p>'"></iframe>
                                </div>
                            </div>
                            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-end gap-3">
                                <button type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600" @click="closePreviewModal()">Cancel</button>
                                <button type="button" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700 disabled:opacity-60" :disabled="!($data.previewModalData ?? null)" @click="insertFromPreviewModal()">
                                    Insert Template
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
    <div class="flex-1 min-h-0 p-4">
    <div class="h-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
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
</div>
@endsection

