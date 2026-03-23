@extends('layouts.admin')

@section('title', 'Translation Strings')
@section('page-title', 'Translation Strings')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-2">
        <div class="text-sm text-admin-text-secondary">
            Locale: <span class="text-admin-text-primary font-medium">{{ $translation_locale->code }}</span> — {{ $translation_locale->name }}
        </div>
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="w-full lg:flex-1">
                <form method="GET" action="{{ route('admin.translations.locales.lines.index', $translation_locale) }}" class="flex flex-col gap-2 lg:flex-row lg:items-center">
                    <input
                        type="text"
                        name="q"
                        value="{{ $search }}"
                        placeholder="Search key/text..."
                        class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                    >
                    <select
                        name="group"
                        class="w-full lg:w-auto rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                    >
                        <option value="">All groups</option>
                        @foreach($availableGroups as $g)
                            <option value="{{ $g }}" {{ $group === $g ? 'selected' : '' }}>{{ $g }}</option>
                        @endforeach
                    </select>
                    <x-button type="submit" variant="primary" class="w-full lg:w-auto">Filter</x-button>
                </form>
            </div>
            <div class="flex w-full flex-col gap-2 lg:w-auto lg:flex-row lg:items-center">
                @admincan('admin.translations.create')
                    <x-button href="{{ route('admin.translations.locales.lines.create', $translation_locale) }}" variant="primary" class="w-full lg:w-auto">Add String</x-button>
                @endadmincan
                @admincan('admin.translations.edit')
                    <x-button href="{{ route('admin.translations.bulk.edit', $translation_locale) }}" variant="secondary" class="w-full lg:w-auto">Bulk Editor</x-button>
                @endadmincan
            </div>
        </div>
    </div>

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Group</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Key</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Text</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($lines as $line)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ $line->group }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ $line->key }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200 break-words">{{ $line->text }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    @admincan('admin.translations.edit')
                                        <x-button href="{{ route('admin.translations.locales.lines.edit', [$translation_locale, $line]) }}" variant="table" size="action" :pill="true" class="p-2" title="Edit" aria-label="Edit"><x-lucide name="pencil" class="h-4 w-4" /><span class="sr-only">Edit</span></x-button>
                                    @endadmincan
                                    @admincan('admin.translations.delete')
                                        <form method="POST" action="{{ route('admin.translations.locales.lines.destroy', [$translation_locale, $line]) }}" class="inline" onsubmit="return confirm('Delete this translation?');">
                                            @csrf
                                            @method('DELETE')
                                            <x-button type="submit" variant="table-danger" size="action" :pill="true" class="p-2" title="Delete" aria-label="Delete"><x-lucide name="trash-2" class="h-4 w-4" /><span class="sr-only">Delete</span></x-button>
                                        </form>
                                    @endadmincan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No translations found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($lines->hasPages())
            <div class="border-t border-gray-200 dark:border-gray-700">
                {{ $lines->links() }}
            </div>
        @endif
    </x-card>

    <div class="flex items-center justify-between">
        <x-button href="{{ route('admin.translations.locales.index') }}" variant="secondary">Back to Locales</x-button>
        <x-button href="{{ route('admin.settings.index') }}" variant="secondary">Back to Settings</x-button>
    </div>
</div>
@endsection
