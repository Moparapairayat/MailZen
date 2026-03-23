<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Schedule campaign auto-start (runs every minute)
Schedule::command('campaigns:start-scheduled')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

// Schedule autoresponder processing (runs every minute)
Schedule::command('autoresponders:process-due')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('automations:process-due')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

// Schedule bounce processing (runs every 5 minutes)
Schedule::command('email:process-bounces --all')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// Schedule reply processing (runs every 5 minutes)
Schedule::command('email:process-replies')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// Schedule update check (runs every 6 hours)
Schedule::command('updates:check')
    ->everySixHours()
    ->withoutOverlapping()
    ->runInBackground();

// Schedule update install (DISABLED - causing storage bloat)
// Schedule::command('updates:install-if-available')
//     ->everyTenMinutes()
//     ->runInBackground();

// Queue Worker - Processes campaign jobs and other queued tasks
Schedule::command('queue:work --queue=campaigns,email-validation,default --sleep=1 --tries=3 --timeout=300 --max-time=55 --stop-when-empty')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('subscriber-imports:process')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

// Resume stalled campaigns (re-dispatch pending chunks for running campaigns with no progress)
Schedule::command('campaigns:resume-stalled --minutes=10')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// Auto pause-resume campaigns to prevent stalling
Schedule::command('campaigns:auto-pause-resume --seconds=60')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

// Auto pause-resume email validation runs to keep large lists progressing
Schedule::command('email-validation:auto-pause-resume --run-seconds=180 --pause-seconds=10')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

Artisan::command('admin:create {email} {--first=Admin} {--last=User} {--password=} {--verify}', function () {
    $email = (string) $this->argument('email');
    $firstName = (string) $this->option('first');
    $lastName = (string) $this->option('last');
    $password = (string) ($this->option('password') ?: '');
    $verify = (bool) $this->option('verify');

    if ($password === '') {
        $password = $this->secret('Password');
    }

    if ($password === '' || strlen($password) < 8) {
        $this->error('Password is required and must be at least 8 characters.');
        return 1;
    }

    $adminGroup = \App\Models\UserGroup::firstOrCreate(
        ['name' => 'admin'],
        [
            'description' => 'System administrators',
            'permissions' => ['*'],
            'is_system' => true,
        ]
    );

    $user = \App\Models\User::withTrashed()->where('email', $email)->first();
    if ($user) {
        if ($user->trashed()) {
            $user->restore();
        }

        $user->update([
            'first_name' => $firstName ?: $user->first_name,
            'last_name' => $lastName ?: $user->last_name,
            'password' => \Illuminate\Support\Facades\Hash::make($password),
            'status' => 'active',
        ]);
    } else {
        $user = \App\Models\User::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => \Illuminate\Support\Facades\Hash::make($password),
            'timezone' => 'UTC',
            'language' => 'en',
            'status' => 'active',
        ]);
    }

    $user->userGroups()->syncWithoutDetaching([$adminGroup->id]);

    if ($verify && $user->email_verified_at === null) {
        $user->forceFill(['email_verified_at' => now()])->save();
    }

    $this->info("Admin user ready: {$user->email}");
    $this->line("User ID: {$user->id}");
    $this->line("Admin group ID: {$adminGroup->id}");
    return 0;
})->purpose('Create/update an admin user and attach them to the admin user group');

