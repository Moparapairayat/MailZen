@extends('layouts.public')

@section('title', $post->title)

@if($post->excerpt)
@section('metaDescription', $post->excerpt)
@endif

@if($post->featured_image)
@section('metaImage', \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($post->featured_image, '/')))
@section('ogType', 'article')
@endif

@section('content')
<article class="bg-white dark:bg-gray-900">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <a href="{{ route('blog.index') }}" class="inline-flex items-center text-sm font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400">
            <svg class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 12H5" />
                <path d="m12 19-7-7 7-7" />
            </svg>
            Back to Blog
        </a>

        <header class="mt-6">
            <div class="text-sm text-gray-500 dark:text-gray-400">
                {{ $post->published_at ? $post->published_at->format('M d, Y') : '' }}
            </div>
            <h1 class="mt-3 text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                {{ $post->title }}
            </h1>
            @if($post->excerpt)
                <p class="mt-4 text-base text-gray-600 dark:text-gray-300">
                    {{ $post->excerpt }}
                </p>
            @endif
        </header>

        @if($post->featured_image)
            <div class="mt-8 overflow-hidden rounded-2xl border border-gray-200 bg-gray-100 dark:border-gray-800 dark:bg-gray-800">
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($post->featured_image, '/')) }}" alt="{{ $post->title }}" class="h-full w-full object-cover">
            </div>
        @endif

        <div class="prose prose-gray mt-10 max-w-none dark:prose-invert">
            {!! $post->content !!}
        </div>
    </div>
</article>
@endsection
