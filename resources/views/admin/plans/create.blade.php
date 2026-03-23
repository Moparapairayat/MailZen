@extends('layouts.admin')

@section('title', __('Create Plan'))
@section('page-title', __('Create Plan'))

@section('content')
<x-card>
    <form method="POST" action="{{ route('admin.plans.store') }}" class="space-y-6">
        @include('admin.plans.form', ['plan' => $plan ?? new \App\Models\Plan()])
        <div class="flex justify-end">
            <x-button type="submit" variant="primary">{{ __('Save') }}</x-button>
        </div>
    </form>
</x-card>
@endsection

