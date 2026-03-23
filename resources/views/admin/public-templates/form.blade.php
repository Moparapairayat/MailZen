<div class="space-y-4">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('Template Name') }} *</label>
            <input type="text" name="name" required value="{{ old('name', $template->name ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('Type') }}</label>
            <select name="type" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                <option value="email" @selected(old('type', $template->type ?? 'email') === 'email')>{{ __('Email') }}</option>
                <option value="campaign" @selected(old('type', $template->type ?? '') === 'campaign')>{{ __('Campaign') }}</option>
                <option value="transactional" @selected(old('type', $template->type ?? '') === 'transactional')>{{ __('Transactional') }}</option>
                <option value="autoresponder" @selected(old('type', $template->type ?? '') === 'autoresponder')>{{ __('Autoresponder') }}</option>
            </select>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('Description') }}</label>
        <textarea name="description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">{{ old('description', $template->description ?? '') }}</textarea>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('Category') }}</label>
            <select name="category_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                <option value="">—</option>
                @foreach(($categories ?? []) as $cat)
                    <option value="{{ $cat->id }}" @selected((string) old('category_id', $template->category_id ?? '') === (string) $cat->id)>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('Thumbnail URL') }}</label>
            <input type="text" name="thumbnail" value="{{ old('thumbnail', $template->thumbnail ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-white" placeholder="https://...">
        </div>

        <div class="flex items-center gap-2 pt-7">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $template->is_active ?? true))>
            <span class="text-sm text-gray-700 dark:text-gray-200">{{ __('Active') }}</span>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('Visible to customer groups') }}</label>
        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('Leave all unchecked to make this template visible to all customers.') }}</div>

        @php
            $selectedGroups = old('customer_group_ids');
            if (!is_array($selectedGroups)) {
                $selectedGroups = isset($template) && $template->relationLoaded('customerGroups')
                    ? $template->customerGroups->pluck('id')->map(fn ($id) => (string) $id)->all()
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
</div>
