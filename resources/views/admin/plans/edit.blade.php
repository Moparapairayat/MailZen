@extends('layouts.admin')

@section('title', __('Edit Plan'))
@section('page-title', __('Edit Plan'))

@section('content')
<x-card>
    <form method="POST" action="{{ route('admin.plans.update', $plan) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.plans.form', ['plan' => $plan])
        <div class="flex justify-end">
            <x-button type="submit" variant="primary">{{ __('Update') }}</x-button>
        </div>
    </form>
</x-card>
@endsection

