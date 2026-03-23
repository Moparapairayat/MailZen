@extends('layouts.admin')

@section('title', 'Create User')
@section('page-title', 'Create User')

@section('content')
<div class="max-w-4xl">
    <x-card>
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf

            <div class="space-y-6">
                <!-- Personal Information -->
                <div>
                    <h3 class="text-lg font-medium text-admin-text-primary mb-4">Personal Information</h3>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-admin-text-secondary">First Name *</label>
                            <input
                                type="text"
                                name="first_name"
                                id="first_name"
                                value="{{ old('first_name') }}"
                                required
                                class="mt-1 block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                            >
                            @error('first_name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="last_name" class="block text-sm font-medium text-admin-text-secondary">Last Name *</label>
                            <input
                                type="text"
                                name="last_name"
                                id="last_name"
                                value="{{ old('last_name') }}"
                                required
                                class="mt-1 block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                            >
                            @error('last_name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="email" class="block text-sm font-medium text-admin-text-secondary">Email *</label>
                            <input
                                type="email"
                                name="email"
                                id="email"
                                value="{{ old('email') }}"
                                required
                                class="mt-1 block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                            >
                            @error('email')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-admin-text-secondary">Password *</label>
                            <input
                                type="password"
                                name="password"
                                id="password"
                                required
                                class="mt-1 block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                            >
                            @error('password')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-admin-text-secondary">Confirm Password *</label>
                            <input
                                type="password"
                                name="password_confirmation"
                                id="password_confirmation"
                                required
                                class="mt-1 block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                            >
                        </div>
                    </div>
                </div>

                <!-- Account Settings -->
                <div>
                    <h3 class="text-lg font-medium text-admin-text-primary mb-4">Account Settings</h3>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="status" class="block text-sm font-medium text-admin-text-secondary">Status *</label>
                            <select
                                name="status"
                                id="status"
                                required
                                class="mt-1 block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                            >
                                <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="banned" {{ old('status') === 'banned' ? 'selected' : '' }}>Banned</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="timezone" class="block text-sm font-medium text-admin-text-secondary">Timezone</label>
                            <select
                                name="timezone"
                                id="timezone"
                                class="mt-1 block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                            >
                                @php
                                    $selectedTimezone = (string) old('timezone', 'UTC');
                                @endphp
                                @foreach(($timezones ?? []) as $tz)
                                    <option value="{{ $tz }}" {{ (string) $tz === $selectedTimezone ? 'selected' : '' }}>{{ $tz }}</option>
                                @endforeach
                            </select>
                            @error('timezone')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="language" class="block text-sm font-medium text-admin-text-secondary">Language</label>
                            <input
                                type="text"
                                name="language"
                                id="language"
                                value="{{ old('language', 'en') }}"
                                class="mt-1 block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                            >
                            @error('language')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- User Groups -->
                <div>
                    <h3 class="text-lg font-medium text-admin-text-primary mb-4">User Groups</h3>
                    @php
                        $selectedGroupId = (string) (old('user_group_ids')[0] ?? '');

                        $adminGroupId = null;
                        $superAdminGroupId = null;

                        foreach ($userGroups as $groupId => $groupName) {
                            $normalized = strtolower(trim((string) $groupName));

                            if ($superAdminGroupId === null && in_array($normalized, ['superadmin', 'super admin', 'super-admin'], true)) {
                                $superAdminGroupId = (string) $groupId;
                                continue;
                            }

                            if ($adminGroupId === null && $normalized === 'admin') {
                                $adminGroupId = (string) $groupId;
                                continue;
                            }

                            if ($adminGroupId === null && str_contains($normalized, 'admin') && !str_contains($normalized, 'super')) {
                                $adminGroupId = (string) $groupId;
                                continue;
                            }
                        }
                    @endphp
                    <select
                        name="user_group_ids[]"
                        class="mt-1 block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                    >
                        <option value="">No group</option>

                        @if($adminGroupId)
                            <option value="{{ $adminGroupId }}" {{ (string) $adminGroupId === $selectedGroupId ? 'selected' : '' }}>Admin</option>
                        @endif

                        @if($superAdminGroupId)
                            <option value="{{ $superAdminGroupId }}" {{ (string) $superAdminGroupId === $selectedGroupId ? 'selected' : '' }}>Superadmin</option>
                        @endif
                    </select>
                    @error('user_group_ids')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-4 pt-6 border-t border-admin-border">
                    <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-admin-text-secondary hover:text-admin-text-primary">
                        Cancel
                    </a>
                    <x-button type="submit" variant="primary">Create User</x-button>
                </div>
            </div>
        </form>
    </x-card>
</div>
@endsection
