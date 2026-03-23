@extends('layouts.customer')

@section('title', 'Subscription Forms')
@section('page-title', 'Subscription Forms: ' . $list->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Subscription Forms</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $list->display_name ?? $list->name }}</p>
        </div>
        <a href="{{ route('customer.forms.create', ['list_id' => $list->id]) }}" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700">
            Create Form
        </a>
    </div>

    @include('customer.lists.partials.subnav', ['list' => $list])

    <!-- Forms List -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($forms as $form)
            <x-card>
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $form->name }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ ucfirst($form->type) }}</p>
                        <div class="mt-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $form->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}
                            ">
                                {{ $form->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            {{ number_format($form->submissions_count) }} submissions
                        </p>
                    </div>
                </div>
                <div class="mt-4 flex gap-2">
                    <a href="{{ route('customer.lists.forms.show', [$list, $form]) }}" class="flex-1 text-center px-3 py-2 text-sm font-medium text-primary-600 bg-primary-50 rounded-md hover:bg-primary-100 dark:bg-primary-900 dark:text-primary-200">
                        View
                    </a>
                    <a href="{{ route('customer.lists.forms.edit', [$list, $form]) }}" class="flex-1 text-center px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">
                        Edit
                    </a>
                </div>
            </x-card>
        @empty
            <div class="col-span-full">
                <x-card>
                    <div class="text-center py-8">
                        <p class="text-sm text-gray-500 dark:text-gray-400">No subscription forms created yet.</p>
                        <a href="{{ route('customer.forms.create', ['list_id' => $list->id]) }}" class="mt-4 inline-block px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700">
                            Create Your First Form
                        </a>
                    </div>
                </x-card>
            </div>
        @endforelse
    </div>
</div>
@endsection

