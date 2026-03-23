@extends('layouts.admin')

@php
    $isEditingPermission = !is_null(old('target_id', $selectedTargetId));
    $permissionVerb = $isEditingPermission ? 'Edit' : 'Create';
@endphp

@section('title', $permissionVerb . ' Permission')
@section('page-title', $permissionVerb . ' Permission')

@section('content')
<div class="space-y-6">
    <x-card :title="$permissionVerb . ' Permission'">
        <form method="POST" action="{{ route('admin.accessibility-control.update') }}">
            @csrf

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Target Type</label>
                    <select name="target_type" class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900" id="target_type">
                        <option value="admin" {{ old('target_type', $selectedTargetType) === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="superadmin" {{ old('target_type', $selectedTargetType) === 'superadmin' ? 'selected' : '' }}>SuperAdmin</option>
                        <option value="customer_group" {{ old('target_type', $selectedTargetType) === 'customer_group' ? 'selected' : '' }}>Customer groups</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Target</label>

                    <div id="target_admin_wrap">
                        <select name="target_id" class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900" id="target_admin">
                            @foreach($adminUsers as $u)
                                <option value="{{ $u->id }}" {{ (int) old('target_id', $selectedTargetId) === (int) $u->id ? 'selected' : '' }}>{{ $u->email }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="target_superadmin_wrap" class="hidden">
                        <select name="target_id" class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900" id="target_superadmin">
                            @foreach($superAdminUsers as $u)
                                <option value="{{ $u->id }}" {{ (int) old('target_id', $selectedTargetId) === (int) $u->id ? 'selected' : '' }}>{{ $u->email }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="target_customer_group_wrap" class="hidden">
                        <select name="target_id" class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900" id="target_customer_group">
                            @foreach($customerGroups as $g)
                                <option value="{{ $g->id }}" {{ (int) old('target_id', $selectedTargetId) === (int) $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            @if(($supportsAdminUserOverride ?? false) === true)
                <div class="mt-4" id="user_override_wrap">
                    <input type="hidden" name="admin_permissions_override" value="0">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="admin_permissions_override" value="1" {{ old('admin_permissions_override', $selectedUserOverride ? 1 : 0) ? 'checked' : '' }}>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Enable per-user override</span>
                    </label>
                </div>
            @else
                <div class="mt-4 hidden" id="user_override_wrap"></div>
            @endif

            <div class="mt-6 overflow-x-auto">
                @php
                    $currentPermissions = (array) old('permissions', $selectedPermissions);
                    $customerVisibleActions = array_values(array_filter(
                        $customerActions,
                        function (array $action) use ($customerModules, $customerSpecial): bool {
                            $actionKey = $action['key'] ?? null;
                            if (!is_string($actionKey) || trim($actionKey) === '') {
                                return false;
                            }

                            foreach ($customerModules as $module) {
                                if (data_get($module, 'perms.' . $actionKey)) {
                                    return true;
                                }
                            }

                            foreach ($customerSpecial as $row) {
                                if (data_get($row, 'perms.' . $actionKey)) {
                                    return true;
                                }
                            }

                            return false;
                        }
                    ));
                @endphp

                @if(($supportsAdminPermissions ?? false) !== true)
                    <div class="mb-4 rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-900">
                        Per-user admin permissions are not available because the <code>users.admin_permissions</code> column is missing.
                        Run migrations to enable this feature.
                    </div>
                @endif

                <div id="admin_permissions_wrap">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Module</th>
                                @foreach($actions as $action)
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        <div class="flex flex-col items-center gap-2">
                                            <span>{{ $action['label'] }}</span>
                                            <input type="checkbox" data-toggle-column="{{ $action['key'] }}">
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($modules as $module)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                        {{ $module['label'] }}
                                    </td>
                                    @foreach($actions as $action)
                                        @php
                                            $perm = $module['key'] . '.' . $action['key'];
                                        @endphp
                                        <td class="px-6 py-4 text-center">
                                            <input type="checkbox" data-action="{{ $action['key'] }}" name="permissions[]" value="{{ $perm }}" {{ in_array($perm, $currentPermissions, true) ? 'checked' : '' }} {{ (($supportsAdminPermissions ?? false) !== true) ? 'disabled' : '' }}>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach

                            @if(!empty($special))
                                @foreach($special as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                            {{ $item['label'] }}
                                        </td>
                                        @foreach($actions as $action)
                                            <td class="px-6 py-4 text-center">
                                                @if($loop->first)
                                                    <input type="checkbox" data-action="access" name="permissions[]" value="{{ $item['key'] }}" {{ in_array($item['key'], $currentPermissions, true) ? 'checked' : '' }} {{ (($supportsAdminPermissions ?? false) !== true) ? 'disabled' : '' }}>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>

                <div id="customer_permissions_wrap" class="hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Module</th>
                                @foreach($customerVisibleActions as $action)
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        <div class="flex flex-col items-center gap-2">
                                            <span>{{ $action['label'] }}</span>
                                            <input type="checkbox" data-toggle-column="{{ $action['key'] }}">
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($customerModules as $module)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                        {{ $module['label'] }}
                                    </td>
                                    @foreach($customerVisibleActions as $action)
                                        @php
                                            $perm = data_get($module, 'perms.' . $action['key']);
                                        @endphp
                                        <td class="px-6 py-4 text-center">
                                            @if($perm)
                                                <input type="checkbox" data-action="{{ $action['key'] }}" name="permissions[]" value="{{ $perm }}" {{ in_array($perm, $currentPermissions, true) ? 'checked' : '' }}>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach

                            @if(!empty($customerSpecial))
                                @foreach($customerSpecial as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                            {{ $item['label'] }}
                                        </td>
                                        @foreach($customerVisibleActions as $action)
                                            @php
                                                $perm = data_get($item, 'perms.' . $action['key']);
                                            @endphp
                                            <td class="px-6 py-4 text-center">
                                                @if($perm)
                                                    <input type="checkbox" data-action="{{ $action['key'] }}" name="permissions[]" value="{{ $perm }}" {{ in_array($perm, $currentPermissions, true) ? 'checked' : '' }}>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-between">
                <a href="{{ route('admin.accessibility-control.index') }}">
                    <x-button type="button" variant="secondary">Back</x-button>
                </a>
                <x-button type="submit" variant="primary">{{ $isEditingPermission ? 'Save' : 'Create' }}</x-button>
            </div>
        </form>
    </x-card>
 </div>

 <script>
     function initAccessibilityControlForm() {
         var type = document.getElementById('target_type');
         if (!type) return;
         if (type.dataset && type.dataset.bound === '1') return;
         if (type.dataset) type.dataset.bound = '1';

         var adminSelect = document.getElementById('target_admin');
         var adminWrap = document.getElementById('target_admin_wrap');
         var superAdminWrap = document.getElementById('target_superadmin_wrap');
         var superAdminSelect = document.getElementById('target_superadmin');
         var customerGroupWrap = document.getElementById('target_customer_group_wrap');
         var customerGroupSelect = document.getElementById('target_customer_group');
         var overrideWrap = document.getElementById('user_override_wrap');
         var adminPermissionsWrap = document.getElementById('admin_permissions_wrap');
         var customerPermissionsWrap = document.getElementById('customer_permissions_wrap');

        function setWrapEnabled(wrap, enabled) {
            if (!wrap) return;
            wrap.querySelectorAll('input[type="checkbox"]').forEach(function (cb) {
                cb.disabled = !enabled;
            });
        }

        function wireColumnToggles(wrap) {
            if (!wrap) return;
            wrap.querySelectorAll('[data-toggle-column]').forEach(function (toggle) {
                toggle.addEventListener('change', function () {
                    var action = toggle.getAttribute('data-toggle-column');
                    wrap.querySelectorAll('input[type="checkbox"][data-action="' + action + '"]').forEach(function (cb) {
                        if (!cb.disabled) {
                            cb.checked = toggle.checked;
                        }
                    });
                });
            });
        }

        function syncTarget() {
            var isUserTarget = type.value === 'admin' || type.value === 'superadmin';
            var isCustomerGroupTarget = type.value === 'customer_group';

            if (adminWrap) {
                adminWrap.classList.toggle('hidden', type.value !== 'admin');
            }
            if (superAdminWrap) {
                superAdminWrap.classList.toggle('hidden', type.value !== 'superadmin');
            }
            if (customerGroupWrap) {
                customerGroupWrap.classList.toggle('hidden', !isCustomerGroupTarget);
            }

            if (overrideWrap) {
                overrideWrap.classList.toggle('hidden', !isUserTarget);
            }

            if (adminPermissionsWrap) {
                adminPermissionsWrap.classList.toggle('hidden', !isUserTarget);
            }
            if (customerPermissionsWrap) {
                customerPermissionsWrap.classList.toggle('hidden', !isCustomerGroupTarget);
            }

            setWrapEnabled(adminPermissionsWrap, isUserTarget);
            setWrapEnabled(customerPermissionsWrap, isCustomerGroupTarget);

            if (adminSelect) {
                adminSelect.disabled = type.value !== 'admin';
            }
            if (superAdminSelect) {
                superAdminSelect.disabled = type.value !== 'superadmin';
            }
            if (customerGroupSelect) {
                customerGroupSelect.disabled = !isCustomerGroupTarget;
            }
        }

         type.addEventListener('change', syncTarget);

         wireColumnToggles(adminPermissionsWrap);
         wireColumnToggles(customerPermissionsWrap);
         syncTarget();
     }

     if (document.readyState === 'loading') {
         document.addEventListener('DOMContentLoaded', initAccessibilityControlForm);
     } else {
         initAccessibilityControlForm();
     }

     document.addEventListener('turbo:load', initAccessibilityControlForm);
 </script>
 @endsection
