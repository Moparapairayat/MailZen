@extends('layouts.admin')

@section('title', __('Create Coupon'))
@section('page-title', __('Create Coupon'))

@section('content')
<x-card>
    <form method="POST" action="{{ route('admin.coupons.store') }}" class="space-y-6">
        @include('admin.coupons.form')
        <div class="flex justify-end gap-2">
            <x-button href="{{ route('admin.coupons.index') }}" variant="secondary">{{ __('Cancel') }}</x-button>
            <x-button type="submit" variant="primary">{{ __('Save') }}</x-button>
        </div>
    </form>
</x-card>
@endsection
