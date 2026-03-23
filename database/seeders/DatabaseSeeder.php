<?php

namespace Database\Seeders;

use Database\Seeders\DemoSeeder;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create default admin user
        if (env('SEED_DEFAULT_USERS', false)) {
            $admin = User::firstOrCreate(
                ['email' => 'admin@mailzen.com'],
                [
                    'first_name' => 'Admin',
                    'last_name' => 'User',
                    'password' => Hash::make('password'),
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );

            $adminGroup = UserGroup::firstOrCreate(
                ['name' => 'Admin'],
                [
                    'description' => 'Default admin access (configurable via Accessibility Control).',
                    'permissions' => ['admin.*'],
                    'is_system' => true,
                ]
            );

            $admin->userGroups()->syncWithoutDetaching([$adminGroup->id]);

            $superadminGroup = UserGroup::firstOrCreate(
                ['name' => 'Superadmin'],
                [
                    'description' => 'Full access to all admin actions.',
                    'permissions' => ['*'],
                    'is_system' => true,
                ]
            );

            $admin->userGroups()->detach($superadminGroup->id);

            $superadmin = User::firstOrCreate(
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

            $this->command->info('Default admin user created!');
            $this->command->info('Email: admin@mailzen.com');
            $this->command->info('Password: password');

            $this->command->info('Superadmin user created!');
            $this->command->info('Email: superadmin@mailzen.com');
            $this->command->info('Password: password');

            // Create default test customer
            $customer = \App\Models\Customer::firstOrCreate(
                ['email' => 'customer@mailzen.com'],
                [
                    'first_name' => 'Test',
                    'last_name' => 'Customer',
                    'password' => Hash::make('password'),
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );

            $this->command->info('Default test customer created!');
            $this->command->info('Email: customer@mailzen.com');
            $this->command->info('Password: password');
        }

        if (env('DEMO_SEED', false)) {
            $this->call(DemoSeeder::class);
        }
    }
}

