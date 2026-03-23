@extends('layouts.admin')

@section('title', __('Edit Coupon'))
@section('page-title', __('Edit Coupon'))

@section('content')
<x-card>
    <form method="POST" action="{{ route('admin.coupons.update', $coupon) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.coupons.form', ['coupon' => $coupon])
        <div class="flex justify-end gap-2">
            <x-button href="{{ route('admin.coupons.index') }}" variant="secondary">{{ __('Back') }}</x-button>
            <x-button type="submit" variant="primary">{{ __('Update') }}</x-button>
        </div>
    </form>
</x-card>
@endsection
