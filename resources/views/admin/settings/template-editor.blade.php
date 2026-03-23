@extends('layouts.admin')

@section('title', 'Template Editor')
@section('page-title', 'Template Editor')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-md bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-md bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="flex items-start justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $template['name'] ?? 'Template' }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Edit text, brand color, and icons. Preview updates live.</p>
        </div>
        <a
            href="{{ route('admin.settings.index', ['category' => 'templates']) }}"
            class="inline-flex items-center rounded-md border border-gray-200 dark:border-gray-700 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800"
        >Back</a>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <x-card>
            <form method="POST" action="{{ route('admin.settings.templates.update', ['template' => $templateId]) }}" class="space-y-6" id="template-editor-form">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="brand_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Brand Color</label>
                        <div class="mt-2 flex items-center gap-3">
                            <input type="color" id="brand_color" name="brand_color" value="{{ $values['brand_color'] ?? '#3b82f6' }}" class="h-10 w-14 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
                            <input type="text" id="brand_color_text" value="{{ $values['brand_color'] ?? '#3b82f6' }}" readonly class="block w-32 rounded-md border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        </div>
                        @error('brand_color')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="hero_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Hero Title</label>
                        <div class="mt-2">
                            <input type="text" id="hero_title" name="hero_title" value="{{ old('hero_title', $values['hero_title'] ?? '') }}" class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        </div>
                        @error('hero_title')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="hero_subtitle" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Hero Subtitle</label>
                        <div class="mt-2">
                            <textarea id="hero_subtitle" name="hero_subtitle" rows="3" class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">{{ old('hero_subtitle', $values['hero_subtitle'] ?? '') }}</textarea>
                        </div>
                        @error('hero_subtitle')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="cta_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Primary CTA</label>
                        <div class="mt-2">
                            <input type="text" id="cta_text" name="cta_text" value="{{ old('cta_text', $values['cta_text'] ?? '') }}" class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        </div>
                        @error('cta_text')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="cta_secondary_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Secondary CTA</label>
                        <div class="mt-2">
                            <input type="text" id="cta_secondary_text" name="cta_secondary_text" value="{{ old('cta_secondary_text', $values['cta_secondary_text'] ?? '') }}" class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        </div>
                        @error('cta_secondary_text')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="stat_emails_sent" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stat: Emails Sent</label>
                        <div class="mt-2">
                            <input type="text" id="stat_emails_sent" name="stat_emails_sent" value="{{ old('stat_emails_sent', $values['stat_emails_sent'] ?? '') }}" class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        </div>
                        @error('stat_emails_sent')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="stat_users" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stat: Users</label>
                        <div class="mt-2">
                            <input type="text" id="stat_users" name="stat_users" value="{{ old('stat_users', $values['stat_users'] ?? '') }}" class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        </div>
                        @error('stat_users')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="stat_uptime" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stat: Uptime</label>
                        <div class="mt-2">
                            <input type="text" id="stat_uptime" name="stat_uptime" value="{{ old('stat_uptime', $values['stat_uptime'] ?? '') }}" class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        </div>
                        @error('stat_uptime')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Integration Icons</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Uses Lucide icon names (e.g. zap, message-square, globe).</p>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                        @for($i = 1; $i <= 6; $i++)
                            @php
                                $k = 'integration_' . $i . '_icon';
                            @endphp
                            <div>
                                <label for="{{ $k }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Integration {{ $i }} Icon</label>
                                <div class="mt-2">
                                    <input type="text" id="{{ $k }}" name="{{ $k }}" value="{{ old($k, $values[$k] ?? '') }}" class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                </div>
                                @error($k)
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        @endfor
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('admin.settings.index', ['category' => 'templates']) }}" class="inline-flex items-center rounded-md border border-gray-200 dark:border-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">Cancel</a>
                    <x-button type="submit" variant="primary">Save</x-button>
                </div>
            </form>
        </x-card>

        <x-card>
            <div class="space-y-3">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Live preview</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Preview updates as you type.</p>
                    </div>
                    <a id="open-preview" href="{{ route('admin.settings.templates.preview', ['template' => $templateId]) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center rounded-md border border-gray-200 dark:border-gray-700 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">Open</a>
                </div>

                <div class="rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                    <iframe id="template-preview" src="{{ route('admin.settings.templates.preview', ['template' => $templateId]) }}" class="w-full h-[760px] bg-white"></iframe>
                </div>
            </div>
        </x-card>
    </div>
</div>

<script>
    (function () {
        var form = document.getElementById('template-editor-form');
        var iframe = document.getElementById('template-preview');
        var openLink = document.getElementById('open-preview');
        var brandColor = document.getElementById('brand_color');
        var brandColorText = document.getElementById('brand_color_text');
        if (!form || !iframe) {
            return;
        }

        var updatePreview = function () {
            var fd = new FormData(form);
            fd.delete('_token');
            var params = new URLSearchParams(fd);
            var srcBase = iframe.getAttribute('data-base-src');
            if (!srcBase) {
                srcBase = iframe.getAttribute('src').split('?')[0];
                iframe.setAttribute('data-base-src', srcBase);
            }

            var url = srcBase + '?' + params.toString();
            iframe.setAttribute('src', url);
            if (openLink) {
                openLink.setAttribute('href', url);
            }
        };

        form.addEventListener('input', function (e) {
            if (brandColor && brandColorText && e.target === brandColor) {
                brandColorText.value = brandColor.value;
            }
            updatePreview();
        });

        form.addEventListener('change', function () {
            updatePreview();
        });

        if (brandColor && brandColorText) {
            brandColorText.value = brandColor.value;
        }

        updatePreview();
    })();
</script>
@endsection
