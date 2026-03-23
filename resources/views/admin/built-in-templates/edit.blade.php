@extends('layouts.admin')

@section('title', __('Edit Built-in Template'))
@section('page-title', __('Edit Built-in Template'))

@section('content')
<div class="space-y-4">
    <x-card>
        <form method="POST" action="{{ route('admin.built-in-templates.update', $setting) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('Name') }}</label>
                    <input type="text" value="{{ $setting->name }}" disabled class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-700 dark:text-white">
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $setting->relative_path }}</div>
                </div>

                <div class="flex items-center gap-2 pt-7">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $setting->is_active))>
                    <span class="text-sm text-gray-700 dark:text-gray-200">{{ __('Active') }}</span>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('Visible to customer groups') }}</label>
                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('Leave all unchecked to make this template visible to all customers.') }}</div>

                @php
                    $selectedGroups = old('customer_group_ids');
                    if (!is_array($selectedGroups)) {
                        $selectedGroups = $setting->relationLoaded('customerGroups')
                            ? $setting->customerGroups->pluck('id')->map(fn ($id) => (string) $id)->all()
                            : [];
                    } else {
                        $selectedGroups = array_map(fn ($id) => (string) $id, $selectedGroups);
                    }
                @endphp

                <div class="mt-3 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                    @foreach(($customerGroups ?? []) as $group)
                        <label class="flex items-center gap-2 rounded-md border border-gray-200 dark:border-gray-700 px-3 py-2">
                            <input type="checkbox" name="customer_group_ids[]" value="{{ $group->id }}" @checked(in_array((string) $group->id, $selectedGroups, true))>
                            <span class="text-sm text-gray-700 dark:text-gray-200">{{ $group->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-end gap-2">
                <x-button href="{{ route('admin.public-templates.index') }}" variant="secondary">{{ __('Back') }}</x-button>
                <x-button type="submit" variant="primary">{{ __('Save') }}</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
