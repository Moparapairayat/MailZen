@extends('layouts.customer')

@section('title', $template->name)
@section('page-title', $template->name)

@section('content')
<div class="space-y-6" x-data="{ testOpen: {{ ($errors->has('delivery_server_id') || $errors->has('to_email')) ? 'true' : 'false' }} }">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $template->name }}</h2>
                @if(is_array($template->settings) && (($template->settings['origin'] ?? null) === 'ai'))
                    <span class="inline-flex items-center rounded-full bg-primary-100 px-2 py-0.5 text-[10px] font-semibold text-primary-800 dark:bg-primary-900 dark:text-primary-200">AI</span>
                @endif
            </div>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $template->description ?? 'No description' }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('customer.templates.preview', $template) }}" target="_blank" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
                Preview
            </a>
            <button type="button" @click="testOpen = true" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
                Test
            </button>
            @if($template->customer_id === auth('customer')->id())
                @customercan('templates.permissions.can_edit_templates')
                    @php
                        $builder = is_array($template->grapesjs_data) ? ($template->grapesjs_data['builder'] ?? null) : null;
                        $editRoute = ($builder === 'unlayer') ? 'customer.templates.unlayer.edit' : 'customer.templates.edit';
                    @endphp
                    <a href="{{ route($editRoute, $template) }}" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700">
                        Edit
                    </a>
                @endcustomercan
                @customercan('templates.permissions.can_create_templates')
                    <form method="POST" action="{{ route('customer.templates.duplicate', $template) }}" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
                            Duplicate
                        </button>
                    </form>
                @endcustomercan
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-900/50 dark:bg-green-900/20 dark:text-green-200">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-200">
            {{ session('error') }}
        </div>
    @endif

    <div x-cloak x-show="testOpen" class="fixed inset-0 z-50 flex items-center justify-center" aria-modal="true" role="dialog">
        <div class="fixed inset-0 bg-black/50" @click="testOpen = false"></div>
        <div class="relative w-full max-w-3xl mx-4 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Send Test Email</h3>
                <button type="button" @click="testOpen = false" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <span class="sr-only">Close</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('customer.templates.test-email', $template) }}" class="px-6 py-4 space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Delivery Server</label>
                        <select name="delivery_server_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                            <option value="">Select a delivery server...</option>
                            @foreach(($deliveryServers ?? []) as $server)
                                <option value="{{ $server->id }}" {{ old('delivery_server_id') == $server->id ? 'selected' : '' }}>
                                    {{ $server->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('delivery_server_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">To Email</label>
                        <input type="email" name="to_email" value="{{ old('to_email') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm" placeholder="test@example.com" />
                        @error('to_email')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Preview</label>
                    <div class="mt-1 border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-white">
                        <iframe
                            srcdoc="{!! htmlspecialchars($template->html_content ?? '<p>No content</p>', ENT_QUOTES, 'UTF-8') !!}"
                            class="w-full h-[450px] border-0"
                            style="min-height: 450px;"
                            loading="lazy"
                            sandbox
                        ></iframe>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="testOpen = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700">
                        Send
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Template Details -->
    <x-card title="Template Information">
        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Type</dt>
                <dd class="mt-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200">
                        {{ ucfirst($template->type) }}
                    </span>
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Usage Count</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $template->usage_count }} times</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $template->created_at->format('M d, Y H:i') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $template->updated_at->format('M d, Y H:i') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Visibility</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    @if($template->is_public)
                        <span class="text-green-600">Public</span>
                    @else
                        <span class="text-gray-600">Private</span>
                    @endif
                </dd>
            </div>
        </dl>
    </x-card>

    <!-- Template Preview -->
    <x-card title="Template Preview">
        @php
            $rawBuilderData = is_array($template->grapesjs_data) ? $template->grapesjs_data : null;
            $jsonPayload = $rawBuilderData;
            if (is_array($rawBuilderData) && ($rawBuilderData['builder'] ?? null) === 'unlayer' && is_array($rawBuilderData['unlayer'] ?? null)) {
                $jsonPayload = $rawBuilderData['unlayer'];
            }
            $jsonPretty = $jsonPayload ? json_encode($jsonPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '';
        @endphp

        <div x-data="{ tab: 'design' }" class="space-y-3">
            <div class="flex items-center gap-6 border-b border-gray-200 dark:border-gray-700">
                <button type="button" @click="tab = 'design'" class="py-3 text-sm font-medium" :class="tab === 'design' ? 'text-primary-600 border-b-2 !border-primary-600' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'">
                    Design
                </button>
                <button type="button" @click="tab = 'json'" class="py-3 text-sm font-medium" :class="tab === 'json' ? 'text-primary-600 border-b-2 !border-primary-600' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'">
                    JSON
                </button>
                <button type="button" @click="tab = 'html'" class="py-3 text-sm font-medium" :class="tab === 'html' ? 'text-primary-600 border-b-2 !border-primary-600' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'">
                    HTML
                </button>
                <button type="button" @click="tab = 'plain'" class="py-3 text-sm font-medium" :class="tab === 'plain' ? 'text-primary-600 border-b-2 !border-primary-600' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'">
                    Plain Text
                </button>
            </div>

            <div x-show="tab === 'design'" class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-white">
                <iframe
                    srcdoc="{!! htmlspecialchars($template->html_content ?? '<p>No content</p>', ENT_QUOTES, 'UTF-8') !!}"
                    class="w-[800px] h-[600px] border-0"
                    style="min-height: 600px;margin:0 auto"
                    loading="lazy"
                    sandbox
                ></iframe>
            </div>

            <div x-show="tab === 'json'" class="border border-gray-200 dark:border-gray-700 rounded-lg bg-white p-4">
                <textarea readonly class="w-full h-[600px] border-0 bg-transparent text-xs font-mono text-gray-800 dark:text-gray-100 resize-none focus:outline-none">{{ $jsonPretty }}</textarea>
            </div>

            <div x-show="tab === 'html'" class="border border-gray-200 dark:border-gray-700 rounded-lg bg-white p-4">
                <textarea readonly class="w-full h-[600px] border-0 bg-transparent text-xs font-mono text-gray-800 dark:text-gray-100 resize-none focus:outline-none">{{ $template->html_content ?? '' }}</textarea>
            </div>

            <div x-show="tab === 'plain'" class="border border-gray-200 dark:border-gray-700 rounded-lg bg-white p-4">
                <textarea readonly class="w-full h-[600px] border-0 bg-transparent text-sm font-mono text-gray-800 dark:text-gray-100 resize-none focus:outline-none">{{ $template->plain_text_content ?? '' }}</textarea>
            </div>
        </div>
    </x-card>

    <!-- Actions -->
    @if($template->customer_id === auth('customer')->id())
        <x-card>
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Danger Zone</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Once you delete a template, there is no going back.</p>
                </div>
                @customercan('templates.permissions.can_delete_templates')
                    <form method="POST" action="{{ route('customer.templates.destroy', $template) }}" onsubmit="return confirm('Are you sure you want to delete this template?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700">
                            Delete Template
                        </button>
                    </form>
                @endcustomercan
            </div>
        </x-card>
    @endif
</div>
@endsection

