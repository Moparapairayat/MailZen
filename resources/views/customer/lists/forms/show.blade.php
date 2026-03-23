@extends('layouts.customer')

@section('title', 'Subscription Form: ' . $form->name)
@section('page-title', 'Subscription Form: ' . $form->name)

@section('content')
<div class="max-w-4xl space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $form->name }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ ucfirst($form->type) }} Form</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('customer.lists.forms.edit', [$list, $form]) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
                Edit
            </a>
        </div>
    </div>

    <!-- Form Details -->
    <x-card title="Form Information">
        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Type</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($form->type) }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Public Title</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $form->title ?: '-' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                <dd class="mt-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $form->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}
                    ">
                        {{ $form->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Submissions</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ number_format($form->submissions_count) }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Form Slug</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">{{ $form->slug }}</dd>
            </div>
        </dl>
    </x-card>

    <!-- Embed Code -->
    @if($form->type === 'embedded')
        <x-card title="Embed Code">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Copy and paste this code into your website to embed the subscription form:
            </p>
            <div class="bg-gray-900 rounded-lg p-4 relative overflow-x-auto">
                <button
                    type="button"
                    class="absolute top-3 right-3 inline-flex items-center justify-center h-8 w-8 rounded-md bg-gray-800/60 hover:bg-gray-800 text-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-900 focus:ring-blue-500"
                    data-copy-target="form-embed-code"
                    aria-label="Copy"
                    title="Copy"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
                    </svg>
                </button>
                <code id="form-embed-code" class="text-sm text-green-400">
                    &lt;iframe src="{{ url('/subscribe/' . $form->slug) }}" width="100%" height="400" frameborder="0"&gt;&lt;/iframe&gt;
                </code>
            </div>
        </x-card>
    @endif

    <!-- Popup Code -->
    @if($form->type === 'popup')
        <x-card title="Popup Code">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Copy and paste this code into your website. The form will automatically open after the configured delay.
            </p>
            <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto relative">
                <button
                    type="button"
                    class="absolute top-3 right-3 inline-flex items-center justify-center h-8 w-8 rounded-md bg-gray-800/60 hover:bg-gray-800 text-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-900 focus:ring-blue-500"
                    data-copy-target="form-popup-code"
                    aria-label="Copy"
                    title="Copy"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
                    </svg>
                </button>
                <code id="form-popup-code" class="text-sm text-green-400 whitespace-pre">
&lt;script src=&quot;{{ route('public.subscribe.popup', $form->slug) }}&quot;&gt;&lt;/script&gt;
                </code>
            </div>
        </x-card>
    @endif

    <!-- API Endpoint -->
    @if($form->type === 'api')
        <x-card title="API Endpoint">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Use this endpoint to subscribe users via API:
            </p>
            <div class="bg-gray-900 rounded-lg p-4 relative overflow-x-auto">
                <button
                    type="button"
                    class="absolute top-3 right-3 inline-flex items-center justify-center h-8 w-8 rounded-md bg-gray-800/60 hover:bg-gray-800 text-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-900 focus:ring-blue-500"
                    data-copy-target="form-api-code"
                    aria-label="Copy"
                    title="Copy"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
                    </svg>
                </button>
                <code id="form-api-code" class="text-sm text-green-400">
                    POST {{ url('/subscribe/' . $form->slug . '/api') }}<br>
                    Content-Type: application/json<br><br>
                    {<br>
                    &nbsp;&nbsp;"email": "user@example.com",<br>
                    &nbsp;&nbsp;"first_name": "John",<br>
                    &nbsp;&nbsp;"last_name": "Doe"<br>
                    }
                </code>
            </div>
        </x-card>
    @endif

    @if($form->description)
        <x-card title="Description">
            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $form->description }}</p>
        </x-card>
    @endif
</div>

@push('scripts')
    <script>
        (function () {
            function fallbackCopyText(text) {
                var textarea = document.createElement('textarea');
                textarea.value = text;
                textarea.setAttribute('readonly', '');
                textarea.style.position = 'fixed';
                textarea.style.top = '-9999px';
                textarea.style.left = '-9999px';
                document.body.appendChild(textarea);
                textarea.select();
                try {
                    document.execCommand('copy');
                } finally {
                    textarea.remove();
                }
            }

            async function copyText(text) {
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(text);
                    return;
                }
                fallbackCopyText(text);
            }

            document.addEventListener('click', async function (e) {
                var btn = e.target && e.target.closest ? e.target.closest('[data-copy-target]') : null;
                if (!btn) return;

                var targetId = btn.getAttribute('data-copy-target');
                var el = document.getElementById(targetId);
                if (!el) return;

                var text = (el.innerText || el.textContent || '').trim();
                if (!text) return;

                var originalTitle = btn.getAttribute('title') || '';
                try {
                    btn.disabled = true;
                    await copyText(text);
                    btn.setAttribute('title', 'Copied!');
                    btn.setAttribute('aria-label', 'Copied');
                    setTimeout(function () {
                        btn.disabled = false;
                        btn.setAttribute('title', originalTitle || 'Copy');
                        btn.setAttribute('aria-label', 'Copy');
                    }, 1200);
                } catch (err) {
                    btn.disabled = false;
                }
            });
        })();
    </script>
@endpush
@endsection

