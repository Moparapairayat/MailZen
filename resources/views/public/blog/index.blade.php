@extends('layouts.public')

@section('title', 'Blog')

@section('content')
<section class="bg-white dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex items-end justify-between gap-6">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Blog</h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Latest updates, guides, and product news.</p>
            </div>
        </div>

        <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($posts as $post)
                <a href="{{ route('blog.show', $post->slug) }}" class="group overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                    @if($post->featured_image)
                        <div class="aspect-[16/9] overflow-hidden bg-gray-100 dark:bg-gray-800">
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($post->featured_image, '/')) }}" alt="{{ $post->title }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.02]">
                        </div>
                    @endif

                    <div class="p-5">
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $post->published_at ? $post->published_at->format('M d, Y') : '' }}
                        </div>
                        <div class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $post->title }}
                        </div>
                        @if($post->excerpt)
                            <div class="mt-2 text-sm text-gray-600 dark:text-gray-300 line-clamp-3">
                                {{ $post->excerpt }}
                            </div>
                        @endif
                        <div class="mt-4 inline-flex items-center text-sm font-semibold text-primary-600 group-hover:text-primary-700 dark:text-primary-400">
                            Read more
                            <svg class="ml-1 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14" />
                                <path d="m12 5 7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full rounded-xl border border-gray-200 bg-white p-8 text-center text-sm text-gray-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                    No posts yet.
                </div>
            @endforelse
        </div>

        @if($posts->hasPages())
            <div class="mt-10">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</section>
@endsection
