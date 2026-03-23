@extends('layouts.admin')

@section('title', 'Bulk Translations')
@section('page-title', 'Bulk Translations')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-2">
        <div class="text-sm text-admin-text-secondary">
            Locale: <span class="text-admin-text-primary font-medium">{{ $translation_locale->code }}</span> — {{ $translation_locale->name }}
        </div>
        @if($sourceLocale)
            <div class="text-sm text-admin-text-secondary">
                Source: <span class="text-admin-text-primary font-medium">{{ $sourceLocale->code }}</span> — {{ $sourceLocale->name }}
            </div>
        @endif

        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="w-full lg:flex-1">
                <form method="GET" action="{{ route('admin.translations.bulk.edit', ['locale' => $translation_locale->code]) }}" class="flex flex-col gap-2 lg:flex-row lg:items-center">
                    @if(request()->boolean('refresh'))
                        <input type="hidden" name="refresh" value="1" />
                    @endif
                    @if(!empty($section))
                        <input type="hidden" name="section" value="{{ $section }}" />
                    @endif
                    <input
                        type="text"
                        name="q"
                        value="{{ $search }}"
                        placeholder="Search English/key..."
                        class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                    >
                    <x-button type="submit" variant="primary" class="w-full lg:w-auto">Search</x-button>
                </form>
            </div>

            <div class="flex w-full flex-col gap-2 lg:w-auto lg:flex-row lg:items-center">
                <form method="GET" action="{{ route('admin.translations.bulk.edit', ['locale' => $translation_locale->code]) }}" class="w-full lg:w-auto">
                    @if(request()->boolean('refresh'))
                        <input type="hidden" name="refresh" value="1" />
                    @endif
                    @if(!empty($search))
                        <input type="hidden" name="q" value="{{ $search }}" />
                    @endif
                    <select
                        name="section"
                        class="w-full lg:w-auto rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                        onchange="this.form.submit()"
                    >
                        @foreach(($sections ?? []) as $s)
                            <option value="{{ $s }}" {{ ($section ?? '') === $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </form>
                <x-button href="{{ route('admin.translations.bulk.edit', ['locale' => $translation_locale->code, 'refresh' => 1]) }}" variant="secondary" class="w-full lg:w-auto">Refresh Scan</x-button>
                <x-button href="{{ route('admin.translations.locales.index') }}" variant="secondary" class="w-full lg:w-auto">Back to Locales</x-button>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.translations.bulk.update', ['locale' => $translation_locale->code]) }}">
        @csrf
        @method('POST')

        @if(!empty($section))
            <input type="hidden" name="section" value="{{ $section }}" />
        @endif
        @if(!empty($search))
            <input type="hidden" name="q" value="{{ $search }}" />
        @endif

        <x-card :padding="false">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">English / Translation</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">English / Translation</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">English / Translation</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">English / Translation</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @if(!empty($section))
                            <tr class="bg-gray-50 dark:bg-gray-800">
                                <td colspan="4" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                                    {{ $section }}
                                </td>
                            </tr>
                        @endif

                        @forelse(collect($rows->items())->chunk(4) as $chunk)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                @foreach($chunk as $row)
                                    @php($existing = $targetLines->get($row['rawKey']))
                                    <td class="px-6 py-4 align-top w-1/4">
                                        <div class="text-sm text-admin-text-primary break-words">{{ $row['source'] }}</div>
                                        <textarea
                                            name="translations[{{ $row['rawKey'] }}]"
                                            rows="1"
                                            class="mt-1 block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                            placeholder="Leave empty to use English"
                                        >{{ old('translations.' . $row['rawKey'], $existing) }}</textarea>
                                    </td>
                                @endforeach

                                @for($i = $chunk->count(); $i < 4; $i++)
                                    <td class="px-6 py-4 align-top w-1/4"></td>
                                @endfor
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No source strings found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-3 space-y-3">
                @if($rows->hasPages())
                    {{ $rows->onEachSide(1)->links() }}
                @endif

                <div class="flex items-center justify-between">
                    <x-button href="{{ route('admin.translations.locales.index') }}" variant="secondary">Back</x-button>
                    @admincan('admin.translations.edit')
                        <x-button type="submit" variant="primary">Save</x-button>
                    @endadmincan
                </div>
            </div>
        </x-card>
    </form>
</div>
@endsection
