<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Setting::firstOrCreate(
            ['key' => 'app_name'],
            [
                'category' => 'general',
                'value' => env('APP_NAME', 'MailZen'),
                'type' => 'string',
                'description' => 'Application name.',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'app_logo'],
            [
                'category' => 'general',
                'value' => null,
                'type' => 'string',
                'description' => 'Application logo path (stored in public disk).',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'from_email'],
            [
                'category' => 'email',
                'value' => env('MAIL_FROM_ADDRESS', 'noreply@example.com'),
                'type' => 'email',
                'description' => 'Default From email address for system emails.',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'from_name'],
            [
                'category' => 'email',
                'value' => env('MAIL_FROM_NAME', env('APP_NAME', 'MailZen')),
                'type' => 'string',
                'description' => 'Default From name for system emails.',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'email_verification_message'],
            [
                'category' => 'email',
                'value' => 'Please click the button below to verify your email address.',
                'type' => 'string',
                'description' => 'Email verification message shown in the verification email.',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'password_reset_message'],
            [
                'category' => 'email',
                'value' => 'You are receiving this email because we received a password reset request for your account.',
                'type' => 'string',
                'description' => 'Password reset message shown in the reset email.',
                'is_public' => false,
            ]
        );
    }

    public function down(): void
    {
        Setting::whereIn('key', [
            'app_name',
            'app_logo',
            'from_email',
            'from_name',
            'email_verification_message',
            'password_reset_message',
        ])->delete();
    }
};
