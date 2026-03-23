<?php

namespace App\Jobs;

use App\Models\Automation;
use App\Models\ListSubscriber;
use App\Models\CampaignRecipient;
use App\Services\AutomationTriggerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckAutomationNegativeCampaignTriggerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public int $automationId,
        public int $subscriberId,
        public int $campaignId,
        public int $recipientId,
        public string $trigger
    ) {
    }

    public function handle(): void
    {
        $automation = Automation::query()->find($this->automationId);
        if (!$automation || $automation->status !== 'active') {
            return;
        }

        $subscriber = ListSubscriber::query()->find($this->subscriberId);
        if (!$subscriber) {
            return;
        }

        $recipient = CampaignRecipient::query()->find($this->recipientId);
        if (!$recipient || (int) $recipient->campaign_id !== (int) $this->campaignId) {
            return;
        }

        $shouldTrigger = false;

        if ($this->trigger === 'campaign_not_opened') {
            $shouldTrigger = $recipient->opened_at === null;
        } elseif ($this->trigger === 'campaign_not_replied') {
            $shouldTrigger = $recipient->replied_at === null;
        } elseif ($this->trigger === 'campaign_opened_not_clicked') {
            $shouldTrigger = $recipient->opened_at !== null && $recipient->clicked_at === null;
        }

        if (!$shouldTrigger) {
            return;
        }

        app(AutomationTriggerService::class)->triggerSubscriberEvent($this->trigger, $subscriber, [
            'campaign_id' => $this->campaignId,
            'recipient_id' => $this->recipientId,
        ]);
    }
}
