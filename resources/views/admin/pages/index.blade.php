@extends('layouts.admin')

@section('title', __('Pages'))
@section('page-title', __('Pages'))

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between lg:gap-0">
        <div class="w-full lg:flex-1 lg:max-w-lg">
            <form method="GET" action="{{ route('admin.pages.index') }}" class="flex flex-col gap-2 lg:flex-row lg:items-center">
                <input
                    type="text"
                    name="q"
                    value="{{ $search ?? '' }}"
                    placeholder="{{ __('Search pages...') }}"
                    class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                >
                <x-button type="submit" variant="primary" class="w-full lg:w-auto">{{ __('Search') }}</x-button>
            </form>
        </div>

        @admincan('admin.pages.create')
            <div class="flex flex-col gap-2 w-full lg:w-auto lg:flex-row">
                <x-button href="{{ route('admin.pages.create', ['type' => 'page']) }}" variant="primary" class="w-full lg:w-auto">{{ __('Create Page') }}</x-button>
                <x-button href="{{ route('admin.pages.create', ['type' => 'homepage']) }}" variant="secondary" class="w-full lg:w-auto">{{ __('Create Homepage') }}</x-button>
            </div>
        @endadmincan
    </div>

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Title') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Type') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Slug / Variant') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($pages as $page)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $page->title }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $page->type }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                @if($page->type === 'homepage')
                                    {{ $page->variant_key ?? '—' }}
                                @else
                                    <span class="font-mono">{{ $page->slug }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $page->status === 'publish' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $page->status === 'publish' ? __('Published') : __('Draft') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    @if(in_array($page->type, ['page', 'homepage']) && $page->status === 'publish')
                                        <x-button href="{{ route('pages.show', $page->slug) }}" target="_blank" variant="table" size="action" :pill="true">{{ __('View') }}</x-button>
                                    @endif

                                    @admincan('admin.pages.edit')
                                        <x-button href="{{ route('admin.pages.edit', $page) }}" variant="table" size="action" :pill="true">{{ __('Edit') }}</x-button>
                                    @endadmincan

                                    @admincan('admin.pages.delete')
                                        <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" class="inline" onsubmit="return confirm(@json(__('Delete page?')));">
                                            @csrf
                                            @method('DELETE')
                                            <x-button type="submit" variant="table-danger" size="action" :pill="true">{{ __('Delete') }}</x-button>
                                        </form>
                                    @endadmincan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('No pages found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pages->hasPages())
            <div class="px-6 py-4">
                {{ $pages->links() }}
            </div>
        @endif
    </x-card>
</div>
@endsection
