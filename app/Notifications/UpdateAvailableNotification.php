<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UpdateAvailableNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $installedVersion,
        protected string $latestVersion
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'update_available',
            'title' => 'Update available',
            'message' => 'A new version is available: ' . $this->latestVersion . ' (installed: ' . $this->installedVersion . ').',
            'installed_version' => $this->installedVersion,
            'latest_version' => $this->latestVersion,
            'url' => '/admin/settings?category=updates',
        ];
    }
}
