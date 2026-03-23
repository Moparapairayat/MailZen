@extends('layouts.admin')

@section('title', __('Templates'))
@section('page-title', __('Templates'))

@section('content')
<div class="space-y-4">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between lg:gap-0">
        <form method="GET" action="{{ route('admin.public-templates.index') }}" class="w-full lg:w-auto flex flex-col gap-2 lg:flex-row lg:items-center">
            <input
                type="text"
                name="q"
                value="{{ $search }}"
                placeholder="{{ __('Search by name/slug') }}"
                class="block w-full lg:w-72 rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
            >
            <x-button type="submit" variant="secondary" class="w-full lg:w-auto">{{ __('Search') }}</x-button>
            <x-button href="{{ route('admin.public-templates.index') }}" variant="secondary" class="w-full lg:w-auto">{{ __('Reset') }}</x-button>
        </form>

        @admincan('admin.public_templates.create')
            <x-button href="{{ route('admin.public-templates.create') }}" variant="primary" class="w-full lg:w-auto">{{ __('Create Template') }}</x-button>
        @endadmincan
    </div>

    <div class="text-sm text-gray-600 dark:text-gray-300">{{ __('Your templates library (editable):') }}</div>
    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Name') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Category') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Type') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Builder') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Active') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($templates as $template)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ $template->name }}
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $template->slug }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                {{ $template->category?->name ?? '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ $template->type }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ $template->builder }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $template->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $template->is_active ? __('Active') : __('Disabled') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    @admincan('admin.public_templates.edit')
                                        <x-button href="{{ route('admin.public-templates.edit', $template) }}" variant="table" size="action" :pill="true" class="p-2" title="{{ __('Edit') }}" aria-label="{{ __('Edit') }}"><x-lucide name="pencil" class="h-4 w-4" /><span class="sr-only">{{ __('Edit') }}</span></x-button>
                                    @endadmincan
                                    @admincan('admin.public_templates.delete')
                                        <form method="POST" action="{{ route('admin.public-templates.destroy', $template) }}" class="inline" onsubmit="return confirm(@json(__('Delete template?')));">
                                            @csrf
                                            @method('DELETE')
                                            <x-button type="submit" variant="table-danger" size="action" :pill="true" class="p-2" title="{{ __('Delete') }}" aria-label="{{ __('Delete') }}"><x-lucide name="trash-2" class="h-4 w-4" /><span class="sr-only">{{ __('Delete') }}</span></x-button>
                                        </form>
                                    @endadmincan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('No templates found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($templates->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">{{ $templates->links() }}</div>
        @endif
    </x-card>

    <div class="text-sm text-gray-600 dark:text-gray-300">{{ __('Built-in templates (file gallery):') }}</div>
    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Name') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Category') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Builder') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Active') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($fileTemplates as $template)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ $template['name'] ?? '—' }}
                                @if(!empty($template['description']))
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $template['description'] }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ $template['category'] ?? 'other' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ $template['builder'] ?? 'unlayer' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                @php
                                    $isActive = (bool) ($template['is_active'] ?? true);
                                @endphp
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $isActive ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $isActive ? __('Active') : __('Disabled') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    @if(!empty($template['edit_url']))
                                        <x-button href="{{ $template['edit_url'] }}" variant="table" size="action" :pill="true" class="p-2" title="{{ __('Edit') }}" aria-label="{{ __('Edit') }}"><x-lucide name="pencil" class="h-4 w-4" /><span class="sr-only">{{ __('Edit') }}</span></x-button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('No file templates found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    @php
        $hasSystemTemplates = isset($systemTemplates) && count($systemTemplates) > 0;
    @endphp

    @if($hasSystemTemplates)
        <div class="text-sm text-gray-600 dark:text-gray-300">{{ __('Built-in templates (system):') }}</div>
        <x-card :padding="false">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Name') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Type') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Builder') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('System') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($systemTemplates as $template)
                            @php
                                $rawBuilderData = $template->grapesjs_data;
                                $builder = is_array($rawBuilderData) ? ($rawBuilderData['builder'] ?? 'grapesjs') : 'grapesjs';
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $template->name }}
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $template->slug }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ $template->type }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ $builder }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">{{ __('Yes') }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>
    @endif
</div>
@endsection
