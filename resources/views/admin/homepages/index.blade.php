@extends('layouts.admin')

@section('title', __('Homepages'))
@section('page-title', __('Homepages'))

@section('content')
<div class="space-y-6">
    <x-card>
        <div class="space-y-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Manage editable texts for homepage variants.') }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($variants as $variant)
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 flex items-center justify-between">
                        <div>
                            <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $variant['label'] }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Edit homepage strings one by one.') }}</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-button href="{{ route('home.variant', ['variant' => (int) $variant['key']]) }}" target="_blank" variant="secondary" size="sm">{{ __('Preview') }}</x-button>
                            <x-button href="{{ route('admin.homepages.edit', ['variant' => $variant['key']]) }}" variant="primary" size="sm">{{ __('Edit strings') }}</x-button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </x-card>
</div>
@endsection
