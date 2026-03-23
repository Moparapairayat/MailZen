@extends('layouts.admin')

@section('title', __('Accessibility Control'))
@section('page-title', __('Accessibility Control'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        @admincan('admin.accessibility_control.access')
            <a href="{{ route('admin.accessibility-control.create') }}">
                <x-button type="button" variant="primary">{{ __('Create Permission') }}</x-button>
            </a>
        @endadmincan
    </div>

    <x-card title="{{ __('Permissions') }}" :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Type') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Target') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Override') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Permissions') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($superAdminUsers as $u)
                        @php
                            $override = (bool) ($u->admin_permissions_override ?? false);
                            $perms = $override ? (array) ($u->admin_permissions ?? []) : [];
                        @endphp
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ __('SuperAdmin') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ $u->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ $override ? __('Yes') : __('No') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ count($perms) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                @admincan('admin.accessibility_control.access')
                                    <a href="{{ route('admin.accessibility-control.create', ['target_type' => 'superadmin', 'target_id' => $u->id]) }}">
                                        <x-button type="button" variant="secondary">{{ __('Edit') }}</x-button>
                                    </a>
                                @endadmincan
                            </td>
                        </tr>
                    @endforeach

                    @foreach($adminUsers as $u)
                        @php
                            $override = (bool) ($u->admin_permissions_override ?? false);
                            $perms = $override ? (array) ($u->admin_permissions ?? []) : [];
                        @endphp
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ __('Admin') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ $u->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ $override ? __('Yes') : __('No') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ count($perms) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                @admincan('admin.accessibility_control.access')
                                    <a href="{{ route('admin.accessibility-control.create', ['target_type' => 'admin', 'target_id' => $u->id]) }}">
                                        <x-button type="button" variant="secondary">{{ __('Edit') }}</x-button>
                                    </a>
                                @endadmincan
                            </td>
                        </tr>
                    @endforeach

                    @foreach($customerGroups as $g)
                        @php
                            $perms = (array) ($g->permissions ?? []);
                        @endphp
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ __('Customer Group') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ $g->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">-</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ count($perms) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                @admincan('admin.accessibility_control.access')
                                    <a href="{{ route('admin.accessibility-control.create', ['target_type' => 'customer_group', 'target_id' => $g->id]) }}">
                                        <x-button type="button" variant="secondary">{{ __('Edit') }}</x-button>
                                    </a>
                                @endadmincan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-card>
</div>
@endsection
