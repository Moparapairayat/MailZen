@extends('layouts.customer')

@section('title', 'Edit Subscriber')
@section('page-title', 'Edit Subscriber')

@section('content')
<div class="max-w-2xl">
    <x-card title="Edit Subscriber">
        <form method="POST" action="{{ route('customer.lists.subscribers.update', [$list, $subscriber]) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="list_context" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        List
                    </label>
                    <select
                        id="list_context"
                        disabled
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 shadow-sm sm:text-sm"
                    >
                        <option>{{ $list->display_name ?? $list->name }}</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label for="tags" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tags
                    </label>
                    <textarea
                        name="tags"
                        id="tags"
                        rows="2"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                        placeholder="vip, engaged, trial"
                    >{{ old('tags', is_array($subscriber->tags ?? null) ? implode(', ', $subscriber->tags) : '') }}</textarea>
                    @error('tags')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror

                    @if(is_array($list->tags ?? null) && count($list->tags) > 0)
                        <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center">
                            <label for="list_tag_dropdown" class="text-xs font-medium text-gray-500 dark:text-gray-400">List Tags</label>
                            <div class="flex items-center gap-2">
                                <select
                                    id="list_tag_dropdown"
                                    class="block rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                                >
                                    <option value="">Select a tag</option>
                                    @foreach($list->tags as $tag)
                                        <option value="{{ $tag }}">{{ $tag }}</option>
                                    @endforeach
                                </select>
                                <button
                                    type="button"
                                    class="px-3 py-2 text-xs font-medium rounded-md bg-primary-50 text-primary-700 hover:bg-primary-100 dark:bg-primary-900/40 dark:text-primary-200"
                                    onclick="addSelectedListTag()"
                                >
                                    Add Tag
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ old('email', $subscriber->email) }}"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Status
                    </label>
                    <select
                        name="status"
                        id="status"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >
                        <option value="confirmed" {{ old('status', $subscriber->status) === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="unconfirmed" {{ old('status', $subscriber->status) === 'unconfirmed' ? 'selected' : '' }}>Unconfirmed</option>
                        <option value="unsubscribed" {{ old('status', $subscriber->status) === 'unsubscribed' ? 'selected' : '' }}>Unsubscribed</option>
                        <option value="bounced" {{ old('status', $subscriber->status) === 'bounced' ? 'selected' : '' }}>Bounced</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        First Name
                    </label>
                    <input
                        type="text"
                        name="first_name"
                        id="first_name"
                        value="{{ old('first_name', $subscriber->first_name) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >
                    @error('first_name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Last Name
                    </label>
                    <input
                        type="text"
                        name="last_name"
                        id="last_name"
                        value="{{ old('last_name', $subscriber->last_name) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >
                    @error('last_name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Notes
                    </label>
                    <textarea
                        name="notes"
                        id="notes"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >{{ old('notes', $subscriber->notes) }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('customer.lists.subscribers.show', [$list, $subscriber]) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    Update Subscriber
                </button>
            </div>
        </form>
    </x-card>
</div>

<script>
function addSelectedListTag() {
    const dropdown = document.getElementById('list_tag_dropdown');
    const tagsField = document.getElementById('tags');

    if (!dropdown || !tagsField || !dropdown.value) {
        return;
    }

    const selected = dropdown.value.trim();
    const current = tagsField.value
        .split(',')
        .map((value) => value.trim())
        .filter((value) => value.length > 0);

    if (!current.includes(selected)) {
        current.push(selected);
    }

    tagsField.value = current.join(', ');
    dropdown.value = '';
}
</script>
@endsection

