@extends('install.layout', ['title' => 'Setup', 'step' => 3])

@section('content')
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Setup</h1>

    <form method="POST" action="{{ route('install.setup.store') }}" class="space-y-6">
        @csrf

        <div class="rounded-xl border border-gray-200 p-5">
            <div class="font-semibold text-gray-800 mb-4">Environment</div>

            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">App Name</label>
                    <input name="app_name" value="{{ old('app_name', $guessedAppName ?? config('app.name', 'MailZen')) }}" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-[#1E5FEA] focus:border-[#1E5FEA]" required />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">App URL</label>
                    <input name="app_url" value="{{ old('app_url', $guessedAppUrl ?? config('app.url')) }}" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-[#1E5FEA] focus:border-[#1E5FEA]" required />
                    <div class="mt-2 text-xs text-gray-500">Do not add a trailing slash.</div>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 p-5">
            <div class="font-semibold text-gray-800 mb-4">Database</div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Database Connection</label>
                    <select name="db_connection" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-[#1E5FEA] focus:border-[#1E5FEA]">
                        <option value="mysql" {{ old('db_connection', 'mysql') === 'mysql' ? 'selected' : '' }}>mysql</option>
                        <option value="pgsql" {{ old('db_connection') === 'pgsql' ? 'selected' : '' }}>pgsql</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Database Host</label>
                    <input name="db_host" value="{{ old('db_host', '127.0.0.1') }}" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-[#1E5FEA] focus:border-[#1E5FEA]" required />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Database Port</label>
                    <input name="db_port" value="{{ old('db_port', '3306') }}" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-[#1E5FEA] focus:border-[#1E5FEA]" required />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Database Name</label>
                    <input name="db_database" value="{{ old('db_database') }}" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-[#1E5FEA] focus:border-[#1E5FEA]" required />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Database Username</label>
                    <input name="db_username" value="{{ old('db_username') }}" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-[#1E5FEA] focus:border-[#1E5FEA]" required />
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Database Password</label>
                    <input type="password" name="db_password" value="{{ old('db_password') }}" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-[#1E5FEA] focus:border-[#1E5FEA]" />
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 p-5">
            <div class="font-semibold text-gray-800 mb-4">Admin Account</div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Admin Email</label>
                    <input name="admin_email" value="{{ old('admin_email') }}" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-[#1E5FEA] focus:border-[#1E5FEA]" required />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <input type="password" name="admin_password" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-[#1E5FEA] focus:border-[#1E5FEA]" required />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                    <input type="password" name="admin_password_confirmation" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-[#1E5FEA] focus:border-[#1E5FEA]" required />
                </div>
            </div>
        </div>

        <button type="submit" class="inline-flex items-center justify-center w-full rounded-xl bg-[#1E5FEA] px-5 py-3 text-white font-semibold hover:bg-[#184FC6]">
            Install
        </button>

        <div class="text-xs text-gray-500">This will write your environment settings, run migrations, and create the first admin account.</div>
    </form>
@endsection
