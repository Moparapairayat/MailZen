@extends('layouts.admin')

@section('title', $page->exists ? __('Edit Page') : __('Create Page'))
@section('page-title', $page->exists ? __('Edit Page') : __('Create Page'))

@push('styles')
<style>
    #editor-container {
        height: calc(100vh - 360px);
        min-height: 700px;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <x-card>
        <form id="unlayer-form" method="POST" action="{{ $page->exists ? route('admin.pages.update', $page) : route('admin.pages.store') }}" class="space-y-6">
            @csrf
            @if($page->exists)
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-8 space-y-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-admin-text-primary">{{ __('Title') }} *</label>
                        <input type="text" name="title" id="title" required value="{{ old('title', $page->title) }}" class="mt-1 block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-admin-text-primary">{{ __('Slug') }}</label>
                        <input type="text" name="slug" id="slug" value="{{ old('slug', $page->slug) }}" class="mt-1 block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                        @error('slug')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="lg:col-span-4 space-y-4">
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 dark:text-admin-text-primary">{{ __('Type') }}</label>
                        <select name="type" id="type" class="mt-1 block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                            <option value="page" {{ old('type', $page->type) === 'page' ? 'selected' : '' }}>{{ __('Page') }}</option>
                            <option value="homepage" {{ old('type', $page->type) === 'homepage' ? 'selected' : '' }}>{{ __('Homepage') }}</option>
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="variant_key" class="block text-sm font-medium text-gray-700 dark:text-admin-text-primary">{{ __('Homepage Variant Key') }}</label>
                        <input type="text" name="variant_key" id="variant_key" value="{{ old('variant_key', $page->variant_key) }}" class="mt-1 block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                        @error('variant_key')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-admin-text-primary">{{ __('Status') }}</label>
                        <select name="status" id="status" class="mt-1 block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                            <option value="draft" {{ old('status', $page->status) === 'draft' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                            <option value="publish" {{ old('status', $page->status) === 'publish' ? 'selected' : '' }}>{{ __('Published') }}</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        @if($page->exists)
                            <x-button href="{{ route('admin.pages.index') }}" variant="secondary">{{ __('Back') }}</x-button>
                        @else
                            <x-button href="{{ route('admin.pages.index') }}" variant="secondary">{{ __('Cancel') }}</x-button>
                        @endif
                        <x-button type="button" id="btn-save" variant="primary">{{ __('Save') }}</x-button>
                    </div>
                </div>
            </div>

            <input type="hidden" name="html_content" id="html_content" value="{{ old('html_content', $page->html_content) }}">
            <input type="hidden" name="builder_data" id="builder_data" value="{{ old('builder_data', is_array($page->builder_data) ? json_encode($page->builder_data) : '') }}">
        </form>
    </x-card>

    <x-card :padding="false">
        <div id="editor-container"></div>
    </x-card>
</div>
@endsection

@push('scripts')
<script src="https://editor.unlayer.com/embed.js"></script>
<script>
(function () {
    const projectId = @json($unlayerProjectId);
    const existingDesign = @json($unlayerDesign);
    const uploadUrl = @json(route('admin.pages.unlayer.upload-image'));
    const csrfToken = @json(csrf_token());

    const supportedFamilies = @json(config('mailzen.fonts.supported_google_families', []));

    const buildFontsConfig = () => {
        if (!Array.isArray(supportedFamilies) || supportedFamilies.length === 0) {
            return null;
        }

        const makeWeights = () => ([
            { label: 'Regular', value: 400 },
            { label: 'Medium', value: 500 },
            { label: 'Semi Bold', value: 600 },
            { label: 'Bold', value: 700 },
        ]);

        const buildGoogleFontUrl = (family) => {
            const encoded = encodeURIComponent(String(family || '')).replace(/%20/g, '+');
            return `https://fonts.googleapis.com/css2?family=${encoded}:wght@400;500;600;700&display=swap`;
        };

        const customFonts = supportedFamilies
            .map((v) => (typeof v === 'string' ? v.trim() : ''))
            .filter((v) => v)
            .map((family) => {
                const safeFamily = String(family).replace(/'/g, "\\'");
                return {
                    label: String(family),
                    value: `'${safeFamily}', sans-serif`,
                    url: buildGoogleFontUrl(family),
                    weights: makeWeights(),
                };
            });

        if (!customFonts.length) {
            return null;
        }

        return {
            showDefaultFonts: true,
            customFonts,
        };
    };

    const initOptions = {
        id: 'editor-container',
        displayMode: 'web',
    };

    const fontsCfg = buildFontsConfig();
    if (fontsCfg) {
        initOptions.fonts = fontsCfg;
    }

    if (projectId) {
        initOptions.projectId = Number(projectId);
    }

    unlayer.init(initOptions);

    if (existingDesign) {
        try {
            unlayer.loadDesign(existingDesign);
        } catch (e) {
            console.warn('Failed to load existing Unlayer design', e);
        }
    }

    unlayer.addEventListener('editor:ready', function () {
        if (existingDesign) {
            try {
                unlayer.loadDesign(existingDesign);
            } catch (e) {
                console.warn('Failed to load existing Unlayer design', e);
            }
        }
    });

    unlayer.registerCallback('image', function (file, done) {
        if (!file || !file.attachments || !file.attachments[0]) {
            done({ progress: 100, error: 'No file selected' });
            return;
        }
        const formData = new FormData();
        formData.append('file', file.attachments[0]);

        fetch(uploadUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: formData,
        })
        .then(function (res) {
            return res.json();
        })
        .then(function (data) {
            if (data && data.filelink) {
                done({ progress: 100, url: data.filelink });
            } else {
                done({ progress: 100, error: 'Upload failed' });
            }
        })
        .catch(function () {
            done({ progress: 100, error: 'Upload failed' });
        });
    });

    const saveButton = document.getElementById('btn-save');
    const form = document.getElementById('unlayer-form');

    if (!saveButton || !form) {
        return;
    }

    saveButton.addEventListener('click', function () {
        unlayer.exportHtml(function (data) {
            document.getElementById('html_content').value = data.html || '';
            document.getElementById('builder_data').value = JSON.stringify(data.design || null);
            form.submit();
        });
    });
})();
</script>
@endpush
