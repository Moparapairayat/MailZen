@csrf

@php
    $statusValue = old('status', $post->status ?? ($post->is_published ?? false ? 'publish' : 'draft'));
@endphp

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3" x-data="{ status: @js($statusValue) }">
    <div class="space-y-4 lg:col-span-2">
        <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Title') }}</label>
            <input type="text" name="title" value="{{ old('title', $post->title ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Slug') }}</label>
            <input type="text" name="slug" value="{{ old('slug', $post->slug ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300">
            <p class="mt-1 text-xs text-gray-500">{{ __('Leave blank to auto-generate from title.') }}</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Content') }}</label>
            <input id="content" type="hidden" name="content" value="{{ old('content', $post->content ?? '') }}">
            <trix-editor input="content" class="mt-1 bg-white rounded-md border border-gray-300"></trix-editor>
        </div>
    </div>

    <div class="space-y-4 lg:col-span-1">
        <div class="rounded-lg border border-gray-200 bg-white p-4">
            <div class="text-sm font-semibold text-gray-900">{{ __('Post Status') }}</div>
            <div class="mt-3 space-y-2">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="radio" name="status" value="draft" x-model="status" @checked($statusValue === 'draft')>
                    <span>{{ __('Draft') }}</span>
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="radio" name="status" value="publish" x-model="status" @checked($statusValue === 'publish')>
                    <span>{{ __('Publish') }}</span>
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="radio" name="status" value="schedule" x-model="status" @checked($statusValue === 'schedule')>
                    <span>{{ __('Schedule') }}</span>
                </label>
            </div>
 
            <div class="mt-3" x-cloak x-show="status === 'schedule'">
                <label class="block text-sm font-medium text-gray-700">{{ __('Schedule Date/Time') }}</label>
                <input
                    type="datetime-local"
                    name="scheduled_at"
                    :disabled="status !== 'schedule'"
                    value="{{ old('scheduled_at', isset($post->scheduled_at) && $post->scheduled_at ? $post->scheduled_at->format('Y-m-d\\TH:i') : '') }}"
                    class="mt-1 block w-full rounded-md border-gray-300"
                >
                <p class="mt-1 text-xs text-gray-500">{{ __('Only used when Status is Schedule.') }}</p>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-4">
            <label class="block text-sm font-medium text-gray-700">{{ __('Excerpt') }}</label>
            <textarea name="excerpt" rows="5" class="mt-1 block w-full rounded-md border-gray-300">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-4">
            <label class="block text-sm font-medium text-gray-700">{{ __('Featured Image') }}</label>
            @if(!empty($post->featured_image))
                <div class="mt-2">
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($post->featured_image, '/')) }}" alt="{{ __('Featured image') }}" class="w-full h-auto rounded-md border border-gray-200">
                </div>
            @endif
            <input type="file" name="featured_image" accept="image/*" class="mt-3 block w-full text-sm">
        </div>
    </div>
</div>
