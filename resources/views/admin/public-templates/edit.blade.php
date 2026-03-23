@extends('layouts.admin')

@section('title', __('Edit Template'))
@section('page-title', __('Edit Template'))

@push('styles')
<style>
    #editor-container {
        height: calc(100vh - 360px);
        min-height: 700px;
    }
</style>
@endpush

@section('content')
<div class="space-y-4" x-data="templateImportModal(@js([
    'galleryUrl' => route('admin.templates.import.gallery'),
    'contentUrlBase' => url('/admin/templates/import'),
    'aiUrl' => null,
    'csrfToken' => csrf_token(),
    'builder' => 'unlayer',
    'canImport' => true,
    'canAi' => false,
    'initialTab' => 'templates',
]))">
    <x-card>
        <form id="unlayer-form" method="POST" action="{{ route('admin.public-templates.update', $template) }}" class="space-y-4">
            @csrf
            @method('PUT')

            @include('admin.public-templates.form', [
                'template' => $template,
                'categories' => $categories,
                'customerGroups' => $customerGroups,
            ])

            <div class="flex items-center justify-end gap-2">
                <x-button href="{{ route('admin.public-templates.index') }}" variant="secondary">{{ __('Back') }}</x-button>
                <x-button type="button" variant="secondary" x-on:click="open()">{{ __('Import templates') }}</x-button>
                <x-button type="button" id="btn-save" variant="primary">{{ __('Update') }}</x-button>
            </div>

            <input type="hidden" name="html_content" id="html_content" value="{{ old('html_content', $template->html_content) }}">
            <input type="hidden" name="plain_text_content" id="plain_text_content" value="{{ old('plain_text_content', $template->plain_text_content) }}">
            <input type="hidden" name="grapesjs_data" id="grapesjs_data" value="{{ old('grapesjs_data', json_encode($template->builder_data)) }}">
        </form>
    </x-card>

    <div x-cloak x-show="($data.importOpen ?? false)" class="fixed inset-0 z-50 flex items-center justify-center" aria-modal="true" role="dialog">
        <div class="fixed inset-0 bg-black/50" @click="close()"></div>
        <div class="relative w-full max-w-6xl mx-4 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Import templates') }}</h3>
                <button type="button" @click="close()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <span class="sr-only">Close</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="px-6 py-4">
                <template x-if="($data.error ?? '')">
                    <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-200" x-text="$data.error"></div>
                </template>

                <div>
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
                                <span x-text="cat.name"></span>
                            </button>
                        </template>
                    </div>

                    <div x-show="($data.loading ?? false)" class="flex items-center justify-center py-12">
                        <svg class="animate-spin h-8 w-8 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>

                    <div x-show="!($data.loading ?? false)" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 max-h-[520px] overflow-y-auto pr-1">
                        <template x-for="item in ($data.filteredGallery ?? [])" :key="item.id">
                            <div class="group relative bg-gray-900 dark:bg-gray-950 rounded-lg overflow-hidden aspect-[4/3] cursor-pointer">
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

                                <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center gap-3">
                                    <button type="button" @click.stop="insertTemplate(item)" class="p-3 bg-primary-600 hover:bg-primary-700 rounded-full text-white transition-colors" title="Insert template">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </button>
                                    <button type="button" @click.stop="openPreviewModal(item)" class="p-3 bg-gray-600 hover:bg-gray-700 rounded-full text-white transition-colors" title="Preview template">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                </div>

                                <div class="absolute bottom-0 left-0 right-0 p-3 bg-gradient-to-t from-black/80 to-transparent">
                                    <div class="text-sm font-medium text-white truncate" x-text="item.name"></div>
                                </div>
                            </div>
                        </template>

                        <div x-show="!($data.loading ?? false) && (($data.filteredGallery ?? []).length === 0)" class="col-span-full flex flex-col items-center justify-center py-12 text-gray-500 dark:text-gray-400">
                            <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                            </svg>
                            <p class="text-sm">{{ __('No templates found in this category.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('Rendering preview...') }}</p>
                </div>
                <div x-show="!($data.previewModalLoading ?? false)" x-cloak class="h-[500px] border border-gray-200 dark:border-gray-700 rounded-lg bg-white overflow-hidden">
                    <iframe class="w-full h-full border-0" loading="lazy" sandbox="allow-scripts allow-same-origin" x-bind:srcdoc="$data.previewModalData?.html_content || '<p style=&quot;padding:12px&quot;>No preview available</p>'"></iframe>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-end gap-3">
                <button type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600" @click="closePreviewModal()">{{ __('Cancel') }}</button>
                <button type="button" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700 disabled:opacity-60" :disabled="!($data.previewModalData ?? null)" @click="insertFromPreviewModal()">
                    {{ __('Insert Template') }}
                </button>
            </div>
        </div>
    </div>

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
@endsection
