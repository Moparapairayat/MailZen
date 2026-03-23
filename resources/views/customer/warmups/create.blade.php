@extends('layouts.customer')

@section('title', 'Create Email Warmup')
@section('page-title', 'Create Email Warmup')

@section('content')
<div class="max-w-4xl mx-auto">
    <x-card>
        <form method="POST" action="{{ route('customer.warmups.store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="lg:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Warmup Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="e.g., New Domain Warmup" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    @error('name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="delivery_server_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Delivery Server <span class="text-red-500">*</span></label>
                    <select id="delivery_server_id" name="delivery_server_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm" required>
                        <option value="">Select a delivery server</option>
                        @foreach($deliveryServers as $server)
                            <option value="{{ $server->id }}" {{ old('delivery_server_id') == $server->id ? 'selected' : '' }}>
                                {{ $server->name }} ({{ $server->type }})
                            </option>
                        @endforeach
                    </select>
                    @error('delivery_server_id')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="email_list_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email List (Optional)</label>
                    <select id="email_list_id" name="email_list_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                        <option value="">Use seed emails instead</option>
                        @foreach($emailLists as $list)
                            <option value="{{ $list->id }}" {{ old('email_list_id') == $list->id ? 'selected' : '' }}>
                                {{ $list->name }} ({{ number_format($list->subscribers_count ?? 0) }} subscribers)
                            </option>
                        @endforeach
                    </select>
                    @error('email_list_id')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Select a list to send warmup emails to real subscribers, or leave empty to use seed emails.</p>
                </div>

                <div>
                    <label for="from_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">From Email <span class="text-red-500">*</span></label>
                    <input type="email" name="from_email" id="from_email" value="{{ old('from_email') }}" required placeholder="hello@yourdomain.com" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    @error('from_email')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="from_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">From Name</label>
                    <input type="text" name="from_name" id="from_name" value="{{ old('from_name') }}" placeholder="Your Name or Company" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    @error('from_name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <hr class="border-gray-200 dark:border-gray-700">

            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Warmup Schedule</h3>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div>
                    <label for="starting_volume" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Starting Volume (emails/day) <span class="text-red-500">*</span></label>
                    <input type="number" name="starting_volume" id="starting_volume" value="{{ old('starting_volume', 10) }}" min="1" max="100" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    @error('starting_volume')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Emails to send on day 1</p>
                </div>

                <div>
                    <label for="max_volume" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Volume (emails/day) <span class="text-red-500">*</span></label>
                    <input type="number" name="max_volume" id="max_volume" value="{{ old('max_volume', 500) }}" min="10" max="10000" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    @error('max_volume')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Maximum daily sending limit</p>
                </div>

                <div>
                    <label for="daily_increase_rate" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Daily Increase Rate <span class="text-red-500">*</span></label>
                    <input type="number" name="daily_increase_rate" id="daily_increase_rate" value="{{ old('daily_increase_rate', 1.20) }}" step="0.01" min="1.05" max="2.0" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    @error('daily_increase_rate')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">1.20 = 20% increase per day</p>
                </div>

                <div>
                    <label for="total_days" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Total Days <span class="text-red-500">*</span></label>
                    <input type="number" name="total_days" id="total_days" value="{{ old('total_days', 30) }}" min="7" max="90" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    @error('total_days')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Duration of warmup period</p>
                </div>

                <div>
                    <label for="send_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Daily Send Time <span class="text-red-500">*</span></label>
                    <input type="time" name="send_time" id="send_time" value="{{ old('send_time', '09:00') }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    @error('send_time')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="timezone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Timezone <span class="text-red-500">*</span></label>
                    <select id="timezone" name="timezone" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm" required>
                        @foreach(timezone_identifiers_list() as $tz)
                            <option value="{{ $tz }}" {{ old('timezone', 'UTC') === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                        @endforeach
                    </select>
                    @error('timezone')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <hr class="border-gray-200 dark:border-gray-700">

            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Seed Emails</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">If not using an email list, provide seed email addresses (one per line) to send warmup emails to.</p>

            <div>
                <label for="seed_emails" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Seed Email Addresses</label>
                <textarea id="seed_emails" name="seed_emails" rows="4" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm" placeholder="email1@example.com&#10;email2@example.com&#10;email3@example.com">{{ old('seed_emails') }}</textarea>
                @error('seed_emails')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">These should be email addresses you control or have permission to send to.</p>
            </div>

            <div class="flex items-center justify-end gap-4">
                <x-button href="{{ route('customer.warmups.index') }}" variant="secondary">Cancel</x-button>
                <x-button type="submit" variant="primary">Create Warmup</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
