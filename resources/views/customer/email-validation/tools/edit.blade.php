@extends('layouts.customer')

@section('title', 'Edit Email Validation Tool')
@section('page-title', 'Edit Email Validation Tool')

@section('content')
<x-card>
    <form method="POST" action="{{ route('customer.email-validation.tools.update', $tool) }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
            <input name="name" value="{{ old('name', $tool->name) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
            @error('name')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Provider</label>
                <select name="provider" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                    <option value="snapvalid" {{ old('provider', $tool->provider) === 'snapvalid' ? 'selected' : '' }}>Snapvalid</option>
                </select>
                @error('provider')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">API Key (leave blank to keep)</label>
                <div class="relative mt-1">
                    <input id="apiKeyInput" name="api_key" type="password" class="block w-full rounded-md border-gray-300 pr-10 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                    <button id="apiKeyToggle" type="button" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-gray-100" aria-label="Toggle API key visibility">
                        <svg id="apiKeyIconShow" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 3c-4.5 0-8.2 3.03-9.5 7 1.3 3.97 5 7 9.5 7s8.2-3.03 9.5-7c-1.3-3.97-5-7-9.5-7Zm0 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8Z" />
                        </svg>
                        <svg id="apiKeyIconHide" xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 5c5.05 0 9.27 3.11 10.76 7.5-.48 1.42-1.25 2.73-2.24 3.85l1.42 1.42A13.57 13.57 0 0 0 24 12.5C22.27 7.61 17.61 4 12 4c-1.57 0-3.08.28-4.47.8l1.66 1.66C9.99 5.17 10.98 5 12 5Zm-9.9-.27L3.52 6.15A13.45 13.45 0 0 0 0 12.5C1.73 17.39 6.39 21 12 21c2.04 0 3.96-.48 5.66-1.33l2.82 2.82 1.41-1.41L3.51 3.32 2.1 4.73ZM12 19c-5.05 0-9.27-3.11-10.76-7.5.6-1.78 1.64-3.38 3.02-4.65l2.17 2.17A5.98 5.98 0 0 0 6 12a6 6 0 0 0 6 6c1.05 0 2.04-.27 2.9-.74l1.55 1.55A9.48 9.48 0 0 1 12 19Zm-.64-9.64 2.28 2.28c.22-.4.36-.86.36-1.36a2 2 0 0 0-2-2c-.5 0-.96.14-1.36.36Zm-2.19 2.19A2 2 0 0 0 10 14a2 2 0 0 0 2.36-.83l-3.19-3.19Z" />
                        </svg>
                    </button>
                </div>
                @error('api_key')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex items-center gap-6">
            <label class="inline-flex items-center">
                <input type="hidden" name="active" value="0">
                <input type="checkbox" name="active" value="1" {{ old('active', $tool->active) ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</span>
            </label>
        </div>

        <div class="flex items-center justify-end gap-3">
            <x-button href="{{ route('customer.email-validation.tools.show', $tool) }}" variant="secondary">Cancel</x-button>
            @customercan('email_validation.permissions.can_edit_tools')
                <x-button type="submit" variant="primary">Save</x-button>
            @endcustomercan
        </div>
    </form>
</x-card>

<script>
    (function () {
        const input = document.getElementById('apiKeyInput');
        const toggle = document.getElementById('apiKeyToggle');
        const iconShow = document.getElementById('apiKeyIconShow');
        const iconHide = document.getElementById('apiKeyIconHide');

        if (!input || !toggle || !iconShow || !iconHide) return;

        toggle.addEventListener('click', function () {
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            iconShow.classList.toggle('hidden', show);
            iconHide.classList.toggle('hidden', !show);
        });
    })();
</script>
@endsection
