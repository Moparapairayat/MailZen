@extends('layouts.admin')

@section('title', __('Profile'))
@section('page-title', __('Profile'))

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-admin-text-primary">{{ __('Profile') }}</h2>
            <p class="mt-1 text-sm text-admin-text-secondary">
                {{ __('Update your personal information, bio and social links.') }}
            </p>
        </div>

        <div class="bg-admin-sidebar shadow-sm rounded-xl border border-admin-border">
            <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <!-- Avatar -->
                <div>
                    <h3 class="text-sm font-medium text-admin-text-primary">{{ __('Avatar') }}</h3>
                    <p class="mt-1 text-xs text-admin-text-secondary">
                        {{ __('This will be used in your account header.') }}
                    </p>
                    <div class="mt-4 flex items-center gap-4">
                        @php
                            $avatarUrl = $user->avatar_path ? \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($user->avatar_path, '/')) : null;
                        @endphp
                        <div class="w-16 h-16 rounded-full bg-white/10 flex items-center justify-center overflow-hidden">
                            @if($avatarUrl)
                                <img src="{{ $avatarUrl }}" alt="{{ $user->full_name }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-sm font-semibold text-admin-text-primary">
                                    {{ strtoupper(Str::substr($user->first_name, 0, 1) . Str::substr($user->last_name, 0, 1)) }}
                                </span>
                            @endif
                        </div>
                        <div>
                            <label class="inline-flex items-center px-3 py-2 border border-admin-border text-sm font-medium rounded-md shadow-sm text-admin-text-primary bg-white/5 hover:bg-white/10 cursor-pointer">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586A2 2 0 0118.828 12H20a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-2a2 2 0 012-2h1.172a2 2 0 001.414-.586L8 14m4-10h.01M12 4a2 2 0 11-.01 4.01A2 2 0 0112 4z" />
                                </svg>
                                <span>{{ __('Upload new') }}</span>
                                <input type="file" name="avatar" class="hidden" accept="image/*">
                            </label>
                            <p class="mt-1 text-xs text-admin-text-secondary">
                                {{ __('PNG or JPG up to 2MB.') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Basic info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-admin-text-secondary">
                            {{ __('First name') }}
                        </label>
                        <input
                            type="text"
                            name="first_name"
                            id="first_name"
                            value="{{ old('first_name', $user->first_name) }}"
                            class="mt-1 block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            required
                        >
                        @error('first_name')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-admin-text-secondary">
                            {{ __('Last name') }}
                        </label>
                        <input
                            type="text"
                            name="last_name"
                            id="last_name"
                            value="{{ old('last_name', $user->last_name) }}"
                            class="mt-1 block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            required
                        >
                        @error('last_name')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Bio -->
                <div>
                    <label for="bio" class="block text-sm font-medium text-admin-text-secondary">
                        {{ __('Bio') }}
                    </label>
                    <textarea
                        name="bio"
                        id="bio"
                        rows="4"
                        class="mt-1 block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                        placeholder="{{ __('Tell a bit about yourself.') }}"
                    >{{ old('bio', $user->bio) }}</textarea>
                    @error('bio')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Social links -->
                <div>
                    <h3 class="text-sm font-medium text-admin-text-primary">{{ __('Social links') }}</h3>
                    <p class="mt-1 text-xs text-admin-text-secondary">
                        {{ __('These links can be used in your profile areas.') }}
                    </p>
                    <div class="mt-4 grid grid-cols-1 gap-4">
                        <div>
                            <label for="website_url" class="block text-xs font-medium text-admin-text-secondary">
                                {{ __('Website') }}
                            </label>
                            <input
                                type="url"
                                name="website_url"
                                id="website_url"
                                value="{{ old('website_url', $user->website_url) }}"
                                placeholder="https://example.com"
                                class="mt-1 block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            >
                            @error('website_url')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="twitter_url" class="block text-xs font-medium text-admin-text-secondary">
                                {{ __('X / Twitter') }}
                            </label>
                            <input
                                type="url"
                                name="twitter_url"
                                id="twitter_url"
                                value="{{ old('twitter_url', $user->twitter_url) }}"
                                placeholder="https://x.com/username"
                                class="mt-1 block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            >
                            @error('twitter_url')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="facebook_url" class="block text-xs font-medium text-admin-text-secondary">
                                {{ __('Facebook') }}
                            </label>
                            <input
                                type="url"
                                name="facebook_url"
                                id="facebook_url"
                                value="{{ old('facebook_url', $user->facebook_url) }}"
                                placeholder="https://facebook.com/username"
                                class="mt-1 block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            >
                            @error('facebook_url')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="linkedin_url" class="block text-xs font-medium text-admin-text-secondary">
                                {{ __('LinkedIn') }}
                            </label>
                            <input
                                type="url"
                                name="linkedin_url"
                                id="linkedin_url"
                                value="{{ old('linkedin_url', $user->linkedin_url) }}"
                                placeholder="https://linkedin.com/in/username"
                                class="mt-1 block w-full rounded-md border-admin-border bg-white/5 text-admin-text-primary shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            >
                            @error('linkedin_url')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-admin-border flex justify-end">
                    <x-button type="submit" variant="primary">{{ __('Save changes') }}</x-button>
                </div>
            </form>
        </div>
    </div>
@endsection

