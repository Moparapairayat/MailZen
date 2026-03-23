@extends('layouts.customer')

@section('title', 'Create Segment')
@section('page-title', 'Create Segment')

@section('content')
@php
    $initialConditions = old('conditions', [['field' => '', 'operator' => 'is', 'value' => '']]);
    if (!is_array($initialConditions) || count($initialConditions) === 0) {
        $initialConditions = [['field' => '', 'operator' => 'is', 'value' => '']];
    }
@endphp

<div class="space-y-6" x-data="segmentBuilder(@js($initialConditions))">
    <form method="POST" action="{{ route('customer.segments.store') }}" class="space-y-6">
        @csrf

        <x-card>
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-12">
                    <label for="list_ids" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Lists <span class="text-red-500">*</span></label>
                    <select id="list_ids" name="list_ids[]" multiple size="{{ min(6, max(3, $lists->count())) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm" required>
                        @foreach($lists as $list)
                            <option value="{{ $list->id }}" {{ in_array((string) $list->id, array_map('strval', (array) old('list_ids', $defaultListIds ?? [])), true) ? 'selected' : '' }}>
                                {{ $list->display_name ?? $list->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Hold Cmd/Ctrl to select multiple lists.</p>
                    @error('list_ids')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    @error('list_ids.*')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div class="lg:col-span-8">
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name <span class="text-red-500">*</span></label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    @error('name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div class="lg:col-span-4">
                    <label for="combine_operator" class="block text-sm font-medium text-gray-700 dark:text-gray-300">How to combine the conditions <span class="text-red-500">*</span></label>
                    <select id="combine_operator" name="combine_operator" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm" required>
                        <option value="all" {{ old('combine_operator', 'all') === 'all' ? 'selected' : '' }}>All</option>
                        <option value="any" {{ old('combine_operator') === 'any' ? 'selected' : '' }}>Any</option>
                    </select>
                    @error('combine_operator')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-6">
                <h3 class="text-base font-medium text-gray-900 dark:text-gray-100">Conditions</h3>

                <div class="mt-3 space-y-3">
                    <template x-for="(condition, index) in conditions" :key="index">
                        <div class="grid grid-cols-1 gap-3 md:grid-cols-12">
                            <div class="md:col-span-4">
                                <select :name="`conditions[${index}][field]`" x-model="condition.field" class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                    <option value="">Select field</option>
                                    @foreach($conditionFieldOptions as $groupLabel => $fields)
                                        <optgroup label="{{ $groupLabel }}">
                                            @foreach($fields as $field)
                                                <option value="{{ $field['value'] }}">{{ $field['label'] }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-3">
                                <select :name="`conditions[${index}][operator]`" x-model="condition.operator" class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                    @foreach($operatorOptions as $option)
                                        <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-4">
                                <input :name="`conditions[${index}][value]`" x-model="condition.value" type="text" placeholder="Value" class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                            </div>
                            <div class="md:col-span-1">
                                <button type="button" @click="removeCondition(index)" class="w-full inline-flex items-center justify-center rounded-md border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800" :disabled="conditions.length === 1">×</button>
                            </div>
                        </div>
                    </template>
                </div>

                @error('conditions')<p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror

                <div class="mt-4">
                    <button type="button" @click="addCondition" class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                        + Add condition
                    </button>
                </div>
            </div>
        </x-card>

        <div class="flex items-center gap-4 border-t border-gray-200 dark:border-gray-700 pt-4">
            <button type="submit" class="inline-flex items-center rounded-md bg-gray-700 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
                Save
            </button>
            <a href="{{ route('customer.lists.index', ['tab' => 'segments']) }}" class="text-sm font-medium text-gray-700 underline dark:text-gray-300">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function segmentBuilder(initialConditions) {
        return {
            conditions: Array.isArray(initialConditions) && initialConditions.length
                ? initialConditions.map((condition) => ({
                    field: condition?.field ?? '',
                    operator: condition?.operator ?? 'is',
                    value: condition?.value ?? '',
                }))
                : [{ field: '', operator: 'is', value: '' }],
            addCondition() {
                this.conditions.push({ field: '', operator: 'is', value: '' });
            },
            removeCondition(index) {
                if (this.conditions.length === 1) {
                    return;
                }

                this.conditions.splice(index, 1);
            },
        };
    }
</script>
@endpush
