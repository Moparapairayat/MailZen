@csrf
<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('Name') }}</label>
        <input type="text" name="name" value="{{ old('name', $plan->name ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('Slug') }}</label>
        <input type="text" name="slug" value="{{ old('slug', $plan->slug ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300">
    </div>
    <div class="grid grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Price') }}</label>
            <input type="number" step="0.01" name="price" value="{{ old('price', $plan->price ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Currency') }}</label>
            <input type="text" name="currency" value="{{ old('currency', $plan->currency ?? 'USD') }}" class="mt-1 block w-full rounded-md border-gray-300">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Billing Cycle') }}</label>
            <select name="billing_cycle" class="mt-1 block w-full rounded-md border-gray-300">
                <option value="monthly" @selected(old('billing_cycle', $plan->billing_cycle ?? 'monthly') === 'monthly')>{{ __('Monthly') }}</option>
                <option value="yearly" @selected(old('billing_cycle', $plan->billing_cycle ?? '') === 'yearly')>{{ __('Yearly') }}</option>
            </select>
        </div>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Trial Days') }}</label>
            <input type="number" name="trial_days" value="{{ old('trial_days', $plan->trial_days ?? 0) }}" class="mt-1 block w-full rounded-md border-gray-300">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Stripe Price ID') }}</label>
            <input type="text" name="stripe_price_id" value="{{ old('stripe_price_id', $plan->stripe_price_id ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300">
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('Description') }}</label>
        <textarea name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300">{{ old('description', $plan->description ?? '') }}</textarea>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('CTA Button Text') }}</label>
        <input type="text" name="cta_text" value="{{ old('cta_text', $plan->cta_text ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('Features') }}</label>
        @php
            $planFeatures = $plan->features ?? null;
            $initialPros = [];
            $initialCons = [];

            if (is_array($planFeatures) && (array_key_exists('pros', $planFeatures) || array_key_exists('cons', $planFeatures))) {
                $initialPros = is_array($planFeatures['pros'] ?? null) ? $planFeatures['pros'] : [];
                $initialCons = is_array($planFeatures['cons'] ?? null) ? $planFeatures['cons'] : [];
            } elseif (is_array($planFeatures)) {
                $initialPros = $planFeatures;
            }

            $initialPros = array_values(array_filter(array_map(fn ($v) => is_string($v) ? trim($v) : '', $initialPros), fn ($v) => $v !== ''));
            $initialCons = array_values(array_filter(array_map(fn ($v) => is_string($v) ? trim($v) : '', $initialCons), fn ($v) => $v !== ''));
        @endphp

        <div x-data="{
            pros: @js(array_values(old('features_pros', $initialPros))),
            cons: @js(array_values(old('features_cons', $initialCons))),
            prosInput: '',
            consInput: '',
            addPros() {
                const v = (this.prosInput || '').trim();
                if (!v) return;
                this.pros.push(v);
                this.prosInput = '';
            },
            addCons() {
                const v = (this.consInput || '').trim();
                if (!v) return;
                this.cons.push(v);
                this.consInput = '';
            },
            removePros(i) { this.pros.splice(i, 1); },
            removeCons(i) { this.cons.splice(i, 1); },
        }" class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="rounded-md border border-gray-200 p-3">
                <div class="text-sm font-medium text-gray-700">{{ __('Pros') }}</div>
                <div class="mt-2 flex gap-2">
                    <input type="text" x-model="prosInput" @keydown.enter.prevent="addPros" class="block w-full rounded-md border-gray-300" placeholder="{{ __('Add a pro...') }}">
                    <button type="button" @click="addPros" class="px-3 rounded-md border border-gray-300 text-sm">{{ __('Add') }}</button>
                </div>
                <div class="mt-3 space-y-2">
                    <template x-for="(item, i) in pros" :key="'pro-' + i">
                        <div class="flex items-center gap-2">
                            <input type="hidden" name="features_pros[]" :value="item">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <div class="text-sm text-gray-700 flex-1" x-text="item"></div>
                            <button type="button" class="text-sm text-gray-500" @click="removePros(i)">×</button>
                        </div>
                    </template>
                </div>
            </div>

            <div class="rounded-md border border-gray-200 p-3">
                <div class="text-sm font-medium text-gray-700">{{ __('Cons') }}</div>
                <div class="mt-2 flex gap-2">
                    <input type="text" x-model="consInput" @keydown.enter.prevent="addCons" class="block w-full rounded-md border-gray-300" placeholder="{{ __('Add a con...') }}">
                    <button type="button" @click="addCons" class="px-3 rounded-md border border-gray-300 text-sm">{{ __('Add') }}</button>
                </div>
                <div class="mt-3 space-y-2">
                    <template x-for="(item, i) in cons" :key="'con-' + i">
                        <div class="flex items-center gap-2">
                            <input type="hidden" name="features_cons[]" :value="item">
                            <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            <div class="text-sm text-gray-700 flex-1" x-text="item"></div>
                            <button type="button" class="text-sm text-gray-500" @click="removeCons(i)">×</button>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('Customer Group') }}</label>
        <select name="customer_group_id" class="mt-1 block w-full rounded-md border-gray-300">
            <option value="">{{ __('Select group') }}</option>
            @foreach($groups as $group)
                <option value="{{ $group->id }}" @selected(old('customer_group_id', $plan->customer_group_id ?? '') == $group->id)>{{ $group->name }}</option>
            @endforeach
        </select>
        <div class="mt-2">
            @admincan('admin.customer_groups.create')
                <x-button href="{{ route('admin.customer-groups.create') }}" variant="primary" class="w-full sm:w-auto">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ __('Create Customer Group') }}
                </x-button>
            @endadmincan
        </div>
    </div>
    <div class="flex items-center gap-2">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $plan->is_active ?? true))>
        <span class="text-sm text-gray-700">{{ __('Active') }}</span>
    </div>
    <div class="flex items-center gap-2">
        <input type="hidden" name="is_popular" value="0">
        <input type="checkbox" name="is_popular" value="1" @checked(old('is_popular', $plan->is_popular ?? false))>
        <span class="text-sm text-gray-700">{{ __('Popular') }}</span>
    </div>
</div>

