@extends('layouts.admin')

@section('title', __('Create Blog Post'))
@section('page-title', __('Create Blog Post'))

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/trix@2.1.1/dist/trix.css">
    <style>
        trix-editor {
            min-height: 480px;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/trix@2.1.1/dist/trix.umd.min.js" defer></script>
@endpush

@section('content')
<x-card>
    <form method="POST" action="{{ route('admin.blog-posts.store') }}" class="space-y-6" enctype="multipart/form-data">
        @include('admin.blog-posts.form')
        <div class="flex justify-end gap-2">
            <x-button href="{{ route('admin.blog-posts.index') }}" variant="secondary">{{ __('Cancel') }}</x-button>
            <x-button type="submit" variant="primary">{{ __('Save') }}</x-button>
        </div>
    </form>
</x-card>
@endsection
