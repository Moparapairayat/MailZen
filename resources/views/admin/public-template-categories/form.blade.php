@csrf
<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('Name') }}</label>
            <input type="text" name="name" value="{{ old('name', $category->name ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('Icon') }}</label>
            <input type="text" name="icon" value="{{ old('icon', $category->icon ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" placeholder="folder">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('Description') }}</label>
        <textarea name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">{{ old('description', $category->description ?? '') }}</textarea>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('Sort order') }}</label>
            <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
        </div>
        <div class="flex items-center gap-2 pt-7">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true))>
            <span class="text-sm text-gray-700 dark:text-gray-200">{{ __('Active') }}</span>
        </div>
    </div>
</div>
