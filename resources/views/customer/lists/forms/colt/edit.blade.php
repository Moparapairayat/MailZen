@extends('layouts.customer')

@section('title', 'Design Subscription Form')
@section('page-title', 'Design Form: ' . $form->name)

@push('styles')
<style>
    #form-builder-container {
        height: calc(100vh - 310px);
        min-height: 700px;
    }
    
    .builder-panel {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
        background: white;
    }
    
    .builder-sidebar {
        width: 280px;
        background: #f9fafb;
        border-right: 1px solid #e5e7eb;
        overflow-y: auto;
    }
    
    .builder-canvas {
        flex: 1;
        background: white;
        position: relative;
        overflow: auto;
    }
    
    .builder-properties {
        width: 300px;
        background: #f9fafb;
        border-left: 1px solid #e5e7eb;
        overflow-y: auto;
    }
    
    .field-type {
        padding: 12px;
        margin: 8px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        background: white;
        cursor: move;
        transition: all 0.2s;
    }
    
    .field-type:hover {
        border-color: #6366f1;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .field-type.dragging {
        opacity: 0.5;
    }
    
    .form-field-element {
        padding: 12px;
        margin: 8px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        background: white;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .form-field-element:hover {
        border-color: #6366f1;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .form-field-element.selected {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
    
    .drop-zone {
        min-height: 400px;
        border: 2px dashed #e5e7eb;
        border-radius: 8px;
        margin: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        transition: all 0.2s;
    }
    
    .drop-zone.drag-over {
        border-color: #6366f1;
        background: rgba(99, 102, 241, 0.05);
    }
    
    .popup-preview {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 1000;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }
    
    .popup-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
    }
    
    /* Dark mode support */
    .dark .builder-panel {
        background: #1f2937;
        border-color: #374151;
    }
    
    .dark .builder-sidebar,
    .dark .builder-properties {
        background: #374151;
        border-color: #4b5563;
    }
    
    .dark .field-type,
    .dark .form-field-element {
        background: #1f2937;
        border-color: #4b5563;
    }
    
    .dark .field-type:hover,
    .dark .form-field-element:hover {
        border-color: #6366f1;
    }
    
    .dark .drop-zone {
        border-color: #4b5563;
        color: #9ca3af;
    }
</style>
@endpush

@section('content')
<div class="space-y-4">
    <form id="form-builder-form" method="POST" action="{{ route('customer.lists.forms.colt.update', [$list, $form]) }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex-1 min-w-0">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    Design your form using the drag-and-drop interface. Add fields, configure their properties, and preview your form.
                </div>
            </div>

            <div class="flex items-center justify-between lg:justify-end gap-3 pt-6">
                <button type="button" id="btn-preview" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
                    Preview
                </button>
                
                <a href="{{ route('customer.lists.forms.show', [$list, $form]) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
                    Cancel
                </a>

                <button type="button" id="btn-save" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700">
                    Save
                </button>
            </div>
        </div>

        <!-- Popup Settings (only for popup type) -->
        @if($form->type === 'popup')
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium mb-4">Popup Settings</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Width</label>
                    <input type="number" id="popup_width" value="{{ data_get($form->settings, 'popup_width', 600) }}" min="300" max="1200" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Height</label>
                    <input type="number" id="popup_height" value="{{ data_get($form->settings, 'popup_height', 400) }}" min="200" max="800" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Background Color</label>
                    <input type="color" id="popup_bg_color" value="{{ data_get($form->settings, 'popup_bg_color', '#ffffff') }}" class="w-full h-9 rounded-md border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Overlay Color</label>
                    <input type="color" id="popup_overlay_color" value="{{ data_get($form->settings, 'popup_overlay_color', '#000000') }}" class="w-full h-9 rounded-md border-gray-300">
                </div>
            </div>
        </div>
        @endif

        <input type="hidden" name="html_content" id="html_content">
        <input type="hidden" name="builder_data" id="builder_data">
        <input type="hidden" name="settings[popup_width]" id="settings_popup_width">
        <input type="hidden" name="settings[popup_height]" id="settings_popup_height">
        <input type="hidden" name="settings[popup_bg_color]" id="settings_popup_bg_color">
        <input type="hidden" name="settings[popup_overlay_color]" id="settings_popup_overlay_color">
    </form>

    <!-- Form Builder Interface -->
    <div class="builder-panel bg-white dark:bg-gray-800 rounded-lg overflow-hidden" style="height: calc(100vh - 310px);">
        <div class="flex h-full">
            <!-- Sidebar - Field Types -->
            <div class="builder-sidebar">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-medium text-gray-900 dark:text-gray-100">Form Fields</h3>
                </div>
                <div class="p-2">
                    <div class="field-type" data-field-type="text">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            <span>Text Input</span>
                        </div>
                    </div>
                    <div class="field-type" data-field-type="email">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <span>Email</span>
                        </div>
                    </div>
                    <div class="field-type" data-field-type="textarea">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span>Textarea</span>
                        </div>
                    </div>
                    <div class="field-type" data-field-type="select">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                            <span>Dropdown</span>
                        </div>
                    </div>
                    <div class="field-type" data-field-type="checkbox">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Checkbox</span>
                        </div>
                    </div>
                    <div class="field-type" data-field-type="radio">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                            <span>Radio Button</span>
                        </div>
                    </div>
                    <div class="field-type" data-field-type="submit">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            <span>Submit Button</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Canvas -->
            <div class="builder-canvas">
                <div id="form-canvas" class="p-4">
                    <div class="drop-zone" id="drop-zone">
                        <div class="text-center">
                            <svg class="w-12 h-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            <p>Drag form fields here to start building</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Properties Panel -->
            <div class="builder-properties">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-medium text-gray-900 dark:text-gray-100">Properties</h3>
                </div>
                <div id="properties-panel" class="p-4">
                    <p class="text-sm text-gray-500">Select a field to edit its properties</p>
                </div>
            </div>
        </div>
    </div>

<!-- Preview Modal -->
<div id="preview-modal" class="hidden fixed inset-0 z-50">
    <div id="preview-overlay" class="popup-overlay"></div>
    <div id="preview-content" class="popup-preview bg-white rounded-lg">
        <!-- Preview content will be inserted here -->
    </div>
</div>
</div>

@push('scripts')
<script>
window.formBuilderData = @json($form->builder_data ?: []);
window.formFields = @json($form->fields ?: []);
window.formType = '{{ $form->type }}';
</script>
<script src="{{ Vite::asset('resources/js/form-builder.js') }}"></script>
@endpush

@endsection
