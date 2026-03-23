@extends('layouts.admin')

@section('title', 'View User')
@section('page-title', 'View User')

@section('content')
<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex items-center justify-end gap-4">
        <x-button href="{{ route('admin.users.edit', $user) }}" variant="primary">
            Edit User
        </x-button>
    </div>

    <!-- User Details -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Main Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Personal Information -->
            <x-card title="Personal Information">
                <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">First Name</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->first_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Name</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->last_name }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Timezone</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->timezone }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Language</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->language }}</dd>
                    </div>
                </dl>
            </x-card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Status Card -->
            <x-card title="Account Status">
                <div class="space-y-4">
                    <div>
                        @php
                            $statusColors = [
                                'active' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                'inactive' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                'banned' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                            ];
                        @endphp
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $statusColors[$user->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($user->status) }}
                        </span>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->created_at->format('M d, Y') }}</dd>
                    </div>
                    @if($user->email_verified_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email Verified</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->email_verified_at->format('M d, Y') }}</dd>
                        </div>
                    @endif
                </div>
            </x-card>

            <!-- User Groups -->
            @if($user->userGroups->count() > 0)
                <x-card title="User Groups">
                    <ul class="space-y-2">
                        @foreach($user->userGroups as $group)
                            <li class="text-sm text-gray-900 dark:text-gray-100">{{ $group->name }}</li>
                        @endforeach
                    </ul>
                </x-card>
            @endif
        </div>
    </div>
</div>
@endsection

