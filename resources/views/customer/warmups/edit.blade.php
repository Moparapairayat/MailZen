@extends('layouts.customer')

@section('title', 'Edit Email Warmup')
@section('page-title', 'Edit Email Warmup')

@section('content')
<div class="max-w-4xl mx-auto">
    <x-card>
        <form method="POST" action="{{ route('customer.warmups.update', $warmup) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="lg:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Warmup Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $warmup->name) }}" required placeholder="e.g., New Domain Warmup" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    @error('name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="delivery_server_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Delivery Server <span class="text-red-500">*</span></label>
                    <select id="delivery_server_id" name="delivery_server_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm" required>
                        <option value="">Select a delivery server</option>
                        @foreach($deliveryServers as $server)
                            <option value="{{ $server->id }}" {{ old('delivery_server_id', $warmup->delivery_server_id) == $server->id ? 'selected' : '' }}>
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
                            <option value="{{ $list->id }}" {{ old('email_list_id', $warmup->email_list_id) == $list->id ? 'selected' : '' }}>
                                {{ $list->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('email_list_id')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="from_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">From Email <span class="text-red-500">*</span></label>
                    <input type="email" name="from_email" id="from_email" value="{{ old('from_email', $warmup->from_email) }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    @error('from_email')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="from_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">From Name</label>
                    <input type="text" name="from_name" id="from_name" value="{{ old('from_name', $warmup->from_name) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    @error('from_name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <hr class="border-gray-200 dark:border-gray-700">

            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Warmup Schedule</h3>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div>
                    <label for="starting_volume" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Starting Volume (emails/day) <span class="text-red-500">*</span></label>
                    <input type="number" name="starting_volume" id="starting_volume" value="{{ old('starting_volume', $warmup->starting_volume) }}" min="1" max="100" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    @error('starting_volume')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="max_volume" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Volume (emails/day) <span class="text-red-500">*</span></label>
                    <input type="number" name="max_volume" id="max_volume" value="{{ old('max_volume', $warmup->max_volume) }}" min="10" max="10000" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    @error('max_volume')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="daily_increase_rate" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Daily Increase Rate <span class="text-red-500">*</span></label>
                    <input type="number" name="daily_increase_rate" id="daily_increase_rate" value="{{ old('daily_increase_rate', $warmup->daily_increase_rate) }}" step="0.01" min="1.05" max="2.0" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    @error('daily_increase_rate')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="total_days" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Total Days <span class="text-red-500">*</span></label>
                    <input type="number" name="total_days" id="total_days" value="{{ old('total_days', $warmup->total_days) }}" min="7" max="90" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    @error('total_days')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="send_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Daily Send Time <span class="text-red-500">*</span></label>
                    <input type="time" name="send_time" id="send_time" value="{{ old('send_time', substr($warmup->send_time, 0, 5)) }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                    @error('send_time')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="timezone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Timezone <span class="text-red-500">*</span></label>
                    <select id="timezone" name="timezone" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm" required>
                        @foreach(timezone_identifiers_list() as $tz)
                            <option value="{{ $tz }}" {{ old('timezone', $warmup->timezone) === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                        @endforeach
                    </select>
                    @error('timezone')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <hr class="border-gray-200 dark:border-gray-700">

            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Seed Emails</h3>

            <div>
                <label for="seed_emails" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Seed Email Addresses</label>
                <textarea id="seed_emails" name="seed_emails" rows="4" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">{{ old('seed_emails', implode("\n", $warmup->settings['seed_emails'] ?? [])) }}</textarea>
                @error('seed_emails')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center justify-end gap-4">
                <x-button href="{{ route('customer.warmups.show', $warmup) }}" variant="secondary">Cancel</x-button>
                <x-button type="submit" variant="primary">Update Warmup</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
