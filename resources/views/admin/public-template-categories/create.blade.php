@extends('layouts.admin')

@section('title', __('Create Template Category'))
@section('page-title', __('Create Template Category'))

@section('content')
<x-card>
    <form method="POST" action="{{ route('admin.public-template-categories.store') }}" class="space-y-6">
        @include('admin.public-template-categories.form', ['category' => $category])
        <div class="flex justify-end gap-2">
            <x-button href="{{ route('admin.public-template-categories.index') }}" variant="secondary">{{ __('Cancel') }}</x-button>
            <x-button type="submit" variant="primary">{{ __('Save') }}</x-button>
        </div>
    </form>
</x-card>
@endsection
