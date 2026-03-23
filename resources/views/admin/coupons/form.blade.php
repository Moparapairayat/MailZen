@csrf
<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Code') }}</label>
            <input type="text" name="code" value="{{ old('code', $coupon->code ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300" placeholder="{{ __('WELCOME10') }}">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Name') }}</label>
            <input type="text" name="name" value="{{ old('name', $coupon->name ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300" placeholder="{{ __('Welcome discount') }}">
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Type') }}</label>
            <select name="type" class="mt-1 block w-full rounded-md border-gray-300" x-data x-on:change="">
                <option value="percent" @selected(old('type', $coupon->type ?? 'percent') === 'percent')>{{ __('Percent') }}</option>
                <option value="fixed" @selected(old('type', $coupon->type ?? '') === 'fixed')>{{ __('Fixed amount') }}</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Percent off') }}</label>
            <input type="number" step="0.01" name="percent_off" value="{{ old('percent_off', $coupon->percent_off ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300" placeholder="{{ __('10') }}">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Amount off') }}</label>
            <input type="number" step="0.01" name="amount_off" value="{{ old('amount_off', $coupon->amount_off ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300" placeholder="{{ __('5.00') }}">
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Currency (fixed)') }}</label>
            <input type="text" name="currency" value="{{ old('currency', $coupon->currency ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300" placeholder="{{ __('USD') }}">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Duration') }}</label>
            <select name="duration" class="mt-1 block w-full rounded-md border-gray-300">
                <option value="once" @selected(old('duration', $coupon->duration ?? 'once') === 'once')>{{ __('Once') }}</option>
                <option value="repeating" @selected(old('duration', $coupon->duration ?? '') === 'repeating')>{{ __('Repeating') }}</option>
                <option value="forever" @selected(old('duration', $coupon->duration ?? '') === 'forever')>{{ __('Forever') }}</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Duration (months)') }}</label>
            <input type="number" name="duration_in_months" value="{{ old('duration_in_months', $coupon->duration_in_months ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300" placeholder="{{ __('3') }}">
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Max redemptions') }}</label>
            <input type="number" name="max_redemptions" value="{{ old('max_redemptions', $coupon->max_redemptions ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300" placeholder="{{ __('100') }}">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Starts at') }}</label>
            <input type="datetime-local" name="starts_at" value="{{ old('starts_at', isset($coupon->starts_at) ? $coupon->starts_at->format('Y-m-d\TH:i') : '') }}" class="mt-1 block w-full rounded-md border-gray-300">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Ends at') }}</label>
            <input type="datetime-local" name="ends_at" value="{{ old('ends_at', isset($coupon->ends_at) ? $coupon->ends_at->format('Y-m-d\TH:i') : '') }}" class="mt-1 block w-full rounded-md border-gray-300">
        </div>
    </div>

    <div class="flex items-center gap-2">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $coupon->is_active ?? true))>
        <span class="text-sm text-gray-700">{{ __('Active') }}</span>
    </div>

    @if(isset($coupon))
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
            <div>
                <label class="block text-xs font-medium text-gray-600">{{ __('Stripe Coupon ID') }}</label>
                <div class="mt-1 text-sm text-gray-900">{{ $coupon->stripe_coupon_id ?? '—' }}</div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600">{{ __('Stripe Promotion Code ID') }}</label>
                <div class="mt-1 text-sm text-gray-900">{{ $coupon->stripe_promotion_code_id ?? '—' }}</div>
            </div>
        </div>
    @endif
</div>
