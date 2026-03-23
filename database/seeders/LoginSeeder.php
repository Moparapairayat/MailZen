<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LoginSeeder extends Seeder
{
    public function run(): void
    {
        $adminGroup = UserGroup::firstOrCreate(
            ['name' => 'Admin'],
            [
                'description' => 'Default admin access (configurable via Accessibility Control).',
                'permissions' => ['admin.*'],
                'is_system' => true,
            ]
        );

        $superadminGroup = UserGroup::firstOrCreate(
            ['name' => 'Superadmin'],
            [
                'description' => 'Full access to all admin actions.',
                'permissions' => ['*'],
                'is_system' => true,
            ]
        );

        $admin = User::updateOrCreate(
            ['email' => 'admin@mailzen.com'],
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $admin->userGroups()->syncWithoutDetaching([$adminGroup->id]);
        $admin->userGroups()->detach($superadminGroup->id);

        $superadmin = User::updateOrCreate(
            ['email' => 'superadmin@mailzen.com'],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $superadmin->userGroups()->syncWithoutDetaching([$superadminGroup->id]);

        $customer = Customer::updateOrCreate(
            ['email' => 'customer@mailzen.com'],
            [
                'first_name' => 'Test',
                'last_name' => 'Customer',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $newRegisteredGroupId = Setting::get('new_registered_customer_group_id');
        $fallbackGroupId = Setting::get('default_customer_group_id');
        $groupId = $newRegisteredGroupId ?: $fallbackGroupId;

        if ($groupId && CustomerGroup::query()->whereKey((int) $groupId)->exists()) {
            $customer->customerGroups()->syncWithoutDetaching([(int) $groupId]);
        }

        $this->command?->info('LoginSeeder created/updated:');
        $this->command?->info('Admin: admin@mailzen.com / password');
        $this->command?->info('Superadmin: superadmin@mailzen.com / password');
        $this->command?->info('Customer: customer@mailzen.com / password');
    }
}
