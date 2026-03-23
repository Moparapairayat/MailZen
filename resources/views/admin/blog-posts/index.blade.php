@extends('layouts.admin')

@section('title', __('Blog'))
@section('page-title', __('Blog'))

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between lg:gap-0">
        <div class="w-full lg:flex-1 lg:max-w-lg">
            <form method="GET" action="{{ route('admin.blog-posts.index') }}" class="flex flex-col gap-2 lg:flex-row lg:items-center">
                <input
                    type="text"
                    name="q"
                    value="{{ $search ?? '' }}"
                    placeholder="{{ __('Search posts...') }}"
                    class="block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary placeholder:text-admin-text-secondary/70 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                >
                <x-button type="submit" variant="primary" class="w-full lg:w-auto">{{ __('Search') }}</x-button>
            </form>
        </div>

        @admincan('admin.blog_posts.create')
            <x-button href="{{ route('admin.blog-posts.create') }}" variant="primary" class="w-full lg:w-auto">{{ __('Create Post') }}</x-button>
        @endadmincan
    </div>

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Title') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Slug') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('When') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($posts as $post)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                @if($post->status === 'publish' && $post->published_at && $post->published_at->lessThanOrEqualTo(now()))
                                    <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="hover:underline">
                                        {{ $post->title }}
                                    </a>
                                @else
                                    {{ $post->title }}
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $post->slug }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                @php
                                    $statusColors = [
                                        'publish' => 'bg-green-100 text-green-800',
                                        'draft' => 'bg-gray-100 text-gray-800',
                                        'schedule' => 'bg-yellow-100 text-yellow-800',
                                    ];
                                    $statusLabel = [
                                        'publish' => __('Published'),
                                        'draft' => __('Draft'),
                                        'schedule' => __('Scheduled'),
                                    ];
                                @endphp
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$post->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $statusLabel[$post->status] ?? ucfirst((string) $post->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                @if($post->status === 'schedule')
                                    {{ $post->scheduled_at ? $post->scheduled_at->format('M d, Y H:i') : '—' }}
                                @else
                                    {{ $post->published_at ? $post->published_at->format('M d, Y') : '—' }}
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    @if($post->status === 'publish' && $post->published_at && $post->published_at->lessThanOrEqualTo(now()))
                                        <x-button href="{{ route('blog.show', $post->slug) }}" target="_blank" variant="table" size="action" :pill="true" class="p-2" title="{{ __('View') }}" aria-label="{{ __('View') }}"><x-lucide name="eye" class="h-4 w-4" /><span class="sr-only">{{ __('View') }}</span></x-button>
                                    @endif
                                    @admincan('admin.blog_posts.edit')
                                        <x-button href="{{ route('admin.blog-posts.edit', $post) }}" variant="table" size="action" :pill="true" class="p-2" title="{{ __('Edit') }}" aria-label="{{ __('Edit') }}"><x-lucide name="pencil" class="h-4 w-4" /><span class="sr-only">{{ __('Edit') }}</span></x-button>
                                    @endadmincan

                                    @if($post->is_published)
                                        @admincan('admin.blog_posts.edit')
                                            <form method="POST" action="{{ route('admin.blog-posts.unpublish', $post) }}" class="inline">
                                                @csrf
                                                <x-button type="submit" variant="table" size="action" :pill="true">{{ __('Unpublish') }}</x-button>
                                            </form>
                                        @endadmincan
                                    @else
                                        @admincan('admin.blog_posts.edit')
                                            <form method="POST" action="{{ route('admin.blog-posts.publish', $post) }}" class="inline">
                                                @csrf
                                                <x-button type="submit" variant="table" size="action" :pill="true">{{ __('Publish') }}</x-button>
                                            </form>
                                        @endadmincan
                                    @endif

                                    @admincan('admin.blog_posts.delete')
                                        <form method="POST" action="{{ route('admin.blog-posts.destroy', $post) }}" class="inline" onsubmit="return confirm(@json(__('Delete post?')));">
                                            @csrf
                                            @method('DELETE')
                                            <x-button type="submit" variant="table-danger" size="action" :pill="true" class="p-2" title="{{ __('Delete') }}" aria-label="{{ __('Delete') }}"><x-lucide name="trash-2" class="h-4 w-4" /><span class="sr-only">{{ __('Delete') }}</span></x-button>
                                        </form>
                                    @endadmincan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('No posts found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($posts->hasPages())
            <div class="border-t border-gray-200 dark:border-gray-700">
                {{ $posts->links() }}
            </div>
        @endif
    </x-card>
</div>
@endsection
