@extends('layouts.admin')

@section('title', 'Translations')
@section('page-title', 'Translations')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="w-full lg:flex-1 lg:max-w-lg">
            <form method="GET" action="{{ route('admin.translations.locales.index') }}" class="flex flex-col gap-2 lg:flex-row lg:items-center">
                <input
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    placeholder="Search locales..."
                    class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                >
                <x-button type="submit" variant="primary" class="w-full lg:w-auto">Search</x-button>
            </form>
        </div>
        <div class="flex w-full flex-col gap-2 lg:w-auto lg:flex-row lg:items-center">
            <x-button href="{{ route('admin.translations.locales.download_main') }}" variant="secondary" class="w-full lg:w-auto">Download Main JSON file</x-button>
            @admincan('admin.translations.create')
                <form method="POST" action="{{ route('admin.translations.locales.upload') }}" enctype="multipart/form-data" class="flex w-full flex-col gap-2 lg:w-auto lg:flex-row lg:items-center">
                    @csrf
                    <input type="file" name="file" accept="application/json,.json" class="block w-full lg:w-64 text-sm text-gray-900 dark:text-gray-100 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-gray-700 dark:file:text-gray-100 dark:hover:file:bg-gray-600" required>
                    <x-button type="submit" variant="primary" class="w-full lg:w-auto">Upload Locale JSON</x-button>
                </form>
            @endadmincan
        </div>
    </div>

    <div class="text-sm text-admin-text-secondary">
        Upload rule: locale name must match filename. Example: locale <span class="text-admin-text-primary font-medium">en_EN</span> must be uploaded as <span class="text-admin-text-primary font-medium">en_EN.json</span>.
    </div>

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Active</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($locales as $locale)
                        @php($isMain = $locale->code === 'en')
                        @php($isActive = $isMain ? true : (($activeLocalesSet === null) ? true : isset($activeLocalesSet[$locale->code])))
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ $locale->code }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                <div class="flex items-center gap-2">
                                    @if(!empty($locale->flag))
                                        <img
                                            src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($locale->flag, '/')) }}"
                                            alt="{{ $locale->code }}"
                                            class="h-5 w-5 rounded border border-gray-300 dark:border-gray-700"
                                        >
                                    @endif
                                    <span>{{ $locale->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                {{ $locale->code === 'en' ? 'Main' : 'Locale file' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                @if($isMain)
                                    <span class="text-gray-500 dark:text-gray-400">Main</span>
                                @else
                                    @admincan('admin.translations.edit')
                                        <form method="POST" action="{{ route('admin.translations.locales.active', ['locale' => $locale->code]) }}">
                                            @csrf
                                            <input type="hidden" name="active" value="0" />
                                            <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                                                <input
                                                    type="checkbox"
                                                    name="active"
                                                    value="1"
                                                    {{ $isActive ? 'checked' : '' }}
                                                    onchange="this.form.submit()"
                                                    class="h-4 w-4 rounded border-admin-border bg-white/5 text-primary-600 focus:ring-primary-500"
                                                >
                                                <span class="text-sm {{ $isActive ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400' }}">
                                                    {{ $isActive ? 'Active' : 'Inactive' }}
                                                </span>
                                            </label>
                                        </form>
                                    @else
                                        <span class="{{ $isActive ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400' }}">
                                            {{ $isActive ? 'Active' : 'Inactive' }}
                                        </span>
                                    @endadmincan
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    @admincan('admin.translations.edit')
                                        <x-button href="{{ route('admin.translations.locales.download', ['locale' => $locale->code]) }}" variant="table" size="action" :pill="true">Download</x-button>
                                        <x-button href="{{ route('admin.translations.locales.edit', ['locale' => $locale->code]) }}" variant="table" size="action" :pill="true" class="p-2" title="Edit" aria-label="Edit"><x-lucide name="pencil" class="h-4 w-4" /><span class="sr-only">Edit</span></x-button>
                                    @endadmincan
                                    <x-button href="{{ route('admin.translations.bulk.edit', ['locale' => $locale->code]) }}" variant="table" size="action" :pill="true">Strings</x-button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No locales found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </x-card>
</div>
@endsection
