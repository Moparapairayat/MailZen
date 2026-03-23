@extends('layouts.admin')

@section('title', __('Vat/Tax'))
@section('page-title', __('Vat/Tax'))

@section('content')
<div class="space-y-6">
    <form method="POST" action="{{ route('admin.vat-tax.update') }}">
        @csrf

        <x-card>
            <div class="space-y-4">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="vat_enabled" value="1" {{ old('vat_enabled', $vat_enabled) ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('Enable VAT') }}</span>
                </label>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('VAT Rate (%)') }}</label>
                        <input type="number" step="0.01" name="vat_rate" value="{{ old('vat_rate', $vat_rate) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('VAT Number') }}</label>
                        <input name="vat_number" value="{{ old('vat_number', $vat_number) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Notes') }}</label>
                    <textarea name="tax_notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">{{ old('tax_notes', $tax_notes) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('US Sales Tax Rates (STATE=RATE)') }}</label>
                    <textarea name="us_sales_tax_rates" rows="6" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">{{ old('us_sales_tax_rates', collect($us_sales_tax_rates ?? [])->map(fn($v, $k) => strtoupper($k) . '=' . $v)->implode("\n")) }}</textarea>
                </div>

                <div class="flex items-center justify-end gap-4 pt-6 mt-6 border-t border-gray-200 dark:border-gray-700">
                    @admincan('admin.vat_tax.edit')
                        <x-button type="submit" variant="primary">{{ __('Save') }}</x-button>
                    @endadmincan
                </div>
            </div>
        </x-card>
    </form>
</div>
@endsection
