<?php

namespace App\Providers;

use App\Mail\DkimSigningPlugin;
use App\Mail\CustomMailManager;
use App\Models\DeliveryServer;
use App\Models\Setting;
use App\Services\DeliveryServerService;
use App\Services\DkimSigningService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;

class MailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Override the mail manager with our custom implementation
        $this->app->extend('mail.manager', function ($manager, $app) {
            return new CustomMailManager($app);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        try {
            $defaultMailer = config('mail.default');
            if (is_string($defaultMailer) && $defaultMailer !== '') {
                $trimmed = trim($defaultMailer);
                $normalized = strtolower($trimmed);

                if ($normalized !== $trimmed) {
                    $hasExact = config("mail.mailers.{$trimmed}") !== null;
                    $hasNormalized = config("mail.mailers.{$normalized}") !== null;

                    if (!$hasExact && ($hasNormalized || $normalized === 'smtp')) {
                        config(['mail.default' => $normalized]);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Ignore config failures during early bootstrap.
        }

        try {
            $smtpServer = DeliveryServer::query()
                ->where('status', 'active')
                ->where('type', 'smtp')
                ->where('use_for_transactional', true)
                ->orderByDesc('is_primary')
                ->orderBy('id')
                ->first();

            $preferred = $smtpServer;
            if (!$preferred) {
                $preferred = DeliveryServer::query()
                    ->where('status', 'active')
                    ->where('use_for_transactional', true)
                    ->orderByDesc('is_primary')
                    ->orderBy('id')
                    ->first();
            }

            if (!$preferred) {
                $preferred = DeliveryServer::query()
                    ->where('status', 'active')
                    ->orderByDesc('is_primary')
                    ->orderBy('id')
                    ->first();
            }

            if ($preferred) {
                if ($preferred->type === 'smtp') {
                    if (is_string($preferred->hostname) && trim($preferred->hostname) !== '') {
                        $encryption = $preferred->encryption;
                        if (!is_string($encryption) || trim($encryption) === '' || $encryption === 'none') {
                            $encryption = null;
                        }

                        config([
                            'mail.default' => 'smtp',
                            'mail.mailers.smtp.transport' => 'smtp',
                            'mail.mailers.smtp.host' => $preferred->hostname,
                            'mail.mailers.smtp.port' => $preferred->port ?? 587,
                            'mail.mailers.smtp.encryption' => $encryption,
                            'mail.mailers.smtp.username' => $preferred->username,
                            'mail.mailers.smtp.password' => $preferred->password,
                            'mail.mailers.smtp.timeout' => $preferred->timeout ?? 30,
                        ]);
                    }
                } else {
                    $type = $preferred->type;
                    if (!config("mail.mailers.{$type}")) {
                        config(["mail.mailers.{$type}" => ['transport' => $type]]);
                    }

                    try {
                        app(DeliveryServerService::class)->configureMailFromServer($preferred);
                    } catch (\Throwable $e) {
                        if ($smtpServer && $smtpServer->id !== $preferred->id) {
                            try {
                                app(DeliveryServerService::class)->configureMailFromServer($smtpServer);
                            } catch (\Throwable $e2) {
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
        }

        try {
            $fromAddress = Setting::get('from_email', env('MAIL_FROM_ADDRESS'));
            $fromName = Setting::get('from_name', env('MAIL_FROM_NAME', env('APP_NAME', 'MailZen')));

            if (is_string($fromAddress)) {
                $fromAddress = trim($fromAddress);
            }
            if (is_string($fromName)) {
                $fromName = trim($fromName);
            }

            if (is_string($fromAddress) && $fromAddress !== '') {
                config(['mail.from.address' => $fromAddress]);
                if (is_string($fromName) && $fromName !== '') {
                    config(['mail.from.name' => $fromName]);
                }
            }

            $applyTransactionalDeliveryServer = function (string $settingKey): void {
                try {
                    $selected = Setting::get($settingKey);
                    $selected = is_string($selected) ? trim($selected) : '';

                    if ($settingKey !== 'transactional_delivery_server_id' && ($selected === '' || $selected === 'inherit')) {
                        $selected = Setting::get('transactional_delivery_server_id');
                        $selected = is_string($selected) ? trim($selected) : '';
                    }

                    if ($selected === 'system') {
                        app(DeliveryServerService::class)->configureMailFromSystemSmtp();
                    } elseif ($selected !== '' && ctype_digit($selected)) {
                        $server = DeliveryServer::query()->whereKey((int) $selected)->first();
                        if ($server) {
                            app(DeliveryServerService::class)->configureMailFromServer($server);
                        }
                    }

                    try {
                        Mail::forgetMailers();
                    } catch (\Throwable $e) {
                        // ignore
                    }
                } catch (\Throwable $e) {
                    // ignore
                }
            };

            VerifyEmail::toMailUsing(function ($notifiable, $url) use ($applyTransactionalDeliveryServer) {
                $applyTransactionalDeliveryServer('verification_delivery_server_id');

                $line = Setting::get('email_verification_message', 'Please click the button below to verify your email address.');
                if (!is_string($line) || trim($line) === '') {
                    $line = 'Please click the button below to verify your email address.';
                }

                return (new MailMessage)
                    ->subject('Verify Email Address')
                    ->line($line)
                    ->action('Verify Email Address', $url)
                    ->line('If you did not create an account, no further action is required.');
            });

            ResetPassword::toMailUsing(function ($notifiable, $token) use ($applyTransactionalDeliveryServer) {
                $applyTransactionalDeliveryServer('password_reset_delivery_server_id');

                $line = Setting::get('password_reset_message', 'You are receiving this email because we received a password reset request for your account.');
                if (!is_string($line) || trim($line) === '') {
                    $line = 'You are receiving this email because we received a password reset request for your account.';
                }

                $email = null;
                try {
                    $email = $notifiable->getEmailForPasswordReset();
                } catch (\Throwable $e) {
                    $email = null;
                }

                try {
                    $url = url(route('password.reset', [
                        'token' => $token,
                        'email' => $email,
                    ], false));
                } catch (\Throwable $e) {
                    $url = url('/password/reset?token=' . urlencode((string) $token) . ($email ? '&email=' . urlencode((string) $email) : ''));
                }

                return (new MailMessage)
                    ->subject('Reset Password Notification')
                    ->line($line)
                    ->action('Reset Password', $url)
                    ->line('This password reset link will expire in :count minutes.', ['count' => config('auth.passwords.' . config('auth.defaults.passwords') . '.expire')])
                    ->line('If you did not request a password reset, no further action is required.');
            });
        } catch (\Throwable $e) {
            // Ignore settings failures during early bootstrap.
        }

        // Register DKIM signing plugin globally for all mailers
        // Use a more defensive approach to avoid errors when mailer is not configured
        $this->app->afterResolving('mail.manager', function ($mailManager) {
            try {
                // Only register plugin if we have a valid mailer configured
                $defaultMailer = config('mail.default', 'smtp');
                if (is_string($defaultMailer) && $defaultMailer !== '') {
                    $trimmed = trim($defaultMailer);
                    $normalized = strtolower($trimmed);

                    if ($normalized !== $trimmed) {
                        $hasExact = config("mail.mailers.{$trimmed}") !== null;
                        $hasNormalized = config("mail.mailers.{$normalized}") !== null;

                        if (!$hasExact && ($hasNormalized || $normalized === 'smtp')) {
                            $defaultMailer = $normalized;
                            config(['mail.default' => $normalized]);
                        }
                    }
                }
                
                // Ensure SMTP mailer exists in config
                if (!config('mail.mailers.smtp')) {
                    config(['mail.mailers.smtp' => [
                        'transport' => 'smtp',
                        'host' => env('MAIL_HOST', 'localhost'),
                        'port' => env('MAIL_PORT', 587),
                        'encryption' => env('MAIL_ENCRYPTION', 'tls'),
                        'username' => env('MAIL_USERNAME'),
                        'password' => env('MAIL_PASSWORD'),
                        'timeout' => 30,
                    ]]);
                }
                
                // Try to get the mailer and register plugin
                try {
                    $mailer = $mailManager->mailer($defaultMailer);
                    if ($mailer) {
                        if (method_exists($mailManager, 'getSwiftMailer')) {
                            $swiftMailer = $mailManager->getSwiftMailer();
                            if ($swiftMailer) {
                                $swiftMailer->registerPlugin(
                                    new DkimSigningPlugin(
                                        app(DkimSigningService::class)
                                    )
                                );
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Mailer not configured yet, skip plugin registration
                }
            } catch (\Exception $e) {
                // Silently fail if mailer is not configured yet
            }
        });
    }
}

