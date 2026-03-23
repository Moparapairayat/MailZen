@extends('layouts.admin')

@section('title', __('Pages'))
@section('page-title', __('Pages'))

@section('content')
<div class="space-y-6">
    <x-card>
        <div class="space-y-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Manage public pages using structured, section-wise editors.') }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($pages as $page)
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 flex items-center justify-between">
                        <div>
                            <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $page['title'] }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $page['description'] }}</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-button href="{{ $page['preview_url'] }}" target="_blank" variant="secondary" size="sm">{{ __('Preview') }}</x-button>
                            <x-button href="{{ $page['edit_url'] }}" variant="primary" size="sm">{{ __('Edit strings') }}</x-button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </x-card>
</div>
@endsection
