@extends('layouts.customer')

@section('title', 'New Subscription')
@section('page-title', 'New Subscription')

@section('content')
<div class="max-w-2xl">
    <x-card title="Select Plan">
        <form method="POST" action="{{ route('customer.subscriptions.store') }}" class="space-y-6">
            @csrf

            <div>
                <label for="plan_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Plan <span class="text-red-500">*</span></label>
                <select name="plan_id" id="plan_id" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    <option value="">Select a plan...</option>
                    @foreach($plans as $plan)
                        <option value="{{ $plan['id'] }}" data-name="{{ $plan['name'] }}" data-price="{{ $plan['price'] }}" data-cycle="{{ $plan['billing_cycle'] }}">
                            {{ $plan['name'] }} - ${{ $plan['price'] }}/{{ $plan['billing_cycle'] }}
                        </option>
                    @endforeach
                </select>
                @error('plan_id')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>

            <input type="hidden" name="plan_name" id="plan_name" value="{{ old('plan_name') }}">
            <input type="hidden" name="price" id="price" value="{{ old('price') }}">
            <input type="hidden" name="billing_cycle" id="billing_cycle" value="{{ old('billing_cycle', 'monthly') }}">

            <div>
                <label for="currency" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Currency</label>
                <input type="text" name="currency" id="currency" value="{{ old('currency', 'USD') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <x-button href="{{ route('customer.subscriptions.index') }}" variant="secondary">Cancel</x-button>
                <x-button type="submit" variant="primary">Subscribe</x-button>
            </div>
        </form>
    </x-card>
</div>

<script>
document.getElementById('plan_id').addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    if (option.value) {
        document.getElementById('plan_name').value = option.dataset.name;
        document.getElementById('price').value = option.dataset.price;
        document.getElementById('billing_cycle').value = option.dataset.cycle;
    }
});
</script>
@endsection

