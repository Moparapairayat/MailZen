@extends('layouts.customer')

@section('title', 'Create Email List')
@section('page-title', 'Create Email List')

@section('content')
<div class="max-w-4xl">
    <x-card title="List Information">
        <form method="POST" action="{{ route('customer.lists.store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        List Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name') }}"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="display_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Display Name
                    </label>
                    <input
                        type="text"
                        name="display_name"
                        id="display_name"
                        value="{{ old('display_name') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >
                    @error('display_name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Description
                    </label>
                    <textarea
                        name="description"
                        id="description"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="from_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        From Name
                    </label>
                    <input
                        type="text"
                        name="from_name"
                        id="from_name"
                        value="{{ old('from_name') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >
                    @error('from_name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="from_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        From Email
                    </label>
                    <input
                        type="email"
                        name="from_email"
                        id="from_email"
                        value="{{ old('from_email', auth('customer')->user()->email) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >
                    @error('from_email')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="reply_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Reply To Email
                    </label>
                    <input
                        type="email"
                        name="reply_to"
                        id="reply_to"
                        value="{{ old('reply_to') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >
                    @error('reply_to')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="default_subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Default Subject
                    </label>
                    <input
                        type="text"
                        name="default_subject"
                        id="default_subject"
                        value="{{ old('default_subject') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >
                    @error('default_subject')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="company_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Company Name
                    </label>
                    <input
                        type="text"
                        name="company_name"
                        id="company_name"
                        value="{{ old('company_name') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >
                    @error('company_name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="company_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Company Address (for compliance)
                    </label>
                    <textarea
                        name="company_address"
                        id="company_address"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >{{ old('company_address') }}</textarea>
                    @error('company_address')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="footer_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Footer Text
                    </label>
                    <textarea
                        name="footer_text"
                        id="footer_text"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                        placeholder="This text will be automatically added to all emails sent from this list"
                    >{{ old('footer_text') }}</textarea>
                    @error('footer_text')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="opt_in" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Opt-in Type
                    </label>
                    <select
                        name="opt_in"
                        id="opt_in"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >
                        <option value="single" {{ old('opt_in', 'double') === 'single' ? 'selected' : '' }}>Single (No confirmation)</option>
                        <option value="double" {{ old('opt_in', 'double') === 'double' ? 'selected' : '' }}>Double (Requires confirmation)</option>
                    </select>
                    @error('opt_in')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Status
                    </label>
                    <select
                        name="status"
                        id="status"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                    >
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <div class="flex items-center">
                        <input
                            id="double_opt_in"
                            name="double_opt_in"
                            type="checkbox"
                            value="1"
                            {{ old('double_opt_in', true) ? 'checked' : '' }}
                            class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                        >
                        <label for="double_opt_in" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                            Enable Double Opt-in (Recommended for compliance)
                        </label>
                    </div>
                    @error('double_opt_in')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('customer.lists.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                    Cancel
                </a>
                @customercan('lists.permissions.can_create_lists')
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Create List
                    </button>
                @endcustomercan
            </div>
        </form>
    </x-card>
</div>
@endsection

