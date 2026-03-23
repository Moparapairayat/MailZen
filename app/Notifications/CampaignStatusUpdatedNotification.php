<?php

namespace App\Notifications;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CampaignStatusUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Campaign $campaign,
        protected string $oldStatus,
        protected string $newStatus,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'campaign.status',
            'title' => 'Campaign status updated',
            'message' => "Campaign \"{$this->campaign->name}\" status changed from {$this->oldStatus} to {$this->newStatus}.",
            'campaign_id' => $this->campaign->id,
            'campaign_name' => $this->campaign->name,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
        ];
    }
}


