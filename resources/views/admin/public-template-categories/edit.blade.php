@extends('layouts.admin')

@section('title', __('Edit Template Category'))
@section('page-title', __('Edit Template Category'))

@section('content')
<x-card>
    <form method="POST" action="{{ route('admin.public-template-categories.update', $category) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.public-template-categories.form', ['category' => $category])
        <div class="flex justify-end gap-2">
            <x-button href="{{ route('admin.public-template-categories.index') }}" variant="secondary">{{ __('Back') }}</x-button>
            <x-button type="submit" variant="primary">{{ __('Update') }}</x-button>
        </div>
    </form>
</x-card>
@endsection
