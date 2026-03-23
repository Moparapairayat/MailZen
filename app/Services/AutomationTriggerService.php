<?php

namespace App\Services;

use App\Jobs\CheckAutomationNegativeCampaignTriggerJob;
use App\Models\Automation;
use App\Models\AutomationRun;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\ListSubscriber;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutomationTriggerService
{
    public function triggerAutomation(Automation $automation, string $event, ListSubscriber $subscriber, array $context = []): void
    {
        if ($automation->status !== 'active') {
            return;
        }

        try {
            if (!$this->automationMatchesEvent($automation, $event, $subscriber, $context)) {
                return;
            }

            $this->startRun($automation, $subscriber, $event, $context);
        } catch (\Throwable $e) {
            Log::error('Failed to trigger automation', [
                'automation_id' => $automation->id,
                'subscriber_id' => $subscriber->id,
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function triggerSubscriberEvent(string $event, ListSubscriber $subscriber, array $context = []): void
    {
        $subscriber->loadMissing('list');

        $customerId = (int) ($subscriber->list?->customer_id ?? 0);
        if ($customerId <= 0) {
            return;
        }

        $automations = Automation::query()
            ->where('customer_id', $customerId)
            ->where('status', 'active')
            ->get();

        foreach ($automations as $automation) {
            try {
                if (!$this->automationMatchesEvent($automation, $event, $subscriber, $context)) {
                    continue;
                }

                $this->startRun($automation, $subscriber, $event, $context);
            } catch (\Throwable $e) {
                Log::error('Failed to trigger automation', [
                    'automation_id' => $automation->id,
                    'subscriber_id' => $subscriber->id,
                    'event' => $event,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function scheduleNegativeCampaignTriggersForRecipient(Campaign $campaign, CampaignRecipient $recipient): void
    {
        $campaign->loadMissing('customer');
        $customerId = (int) ($campaign->customer_id ?? 0);
        if ($customerId <= 0) {
            return;
        }

        $subscriber = ListSubscriber::query()
            ->where('list_id', $campaign->list_id)
            ->where('email', strtolower(trim((string) ($recipient->email ?? ''))))
            ->first();

        if (!$subscriber) {
            return;
        }

        $negative = ['campaign_not_opened', 'campaign_not_replied', 'campaign_opened_not_clicked'];

        $automations = Automation::query()
            ->where('customer_id', $customerId)
            ->where('status', 'active')
            ->get();

        foreach ($automations as $automation) {
            $trigger = $this->triggerSettings($automation)['trigger'] ?? '';
            if (!in_array($trigger, $negative, true)) {
                continue;
            }

            $settings = $this->triggerSettings($automation);
            $campaignId = (int) ($settings['campaign_id'] ?? 0);
            if ($campaignId <= 0 || $campaignId !== (int) $campaign->id) {
                continue;
            }

            $windowValue = (int) ($settings['window_value'] ?? 0);
            $windowUnit = (string) ($settings['window_unit'] ?? 'hours');
            if ($windowValue <= 0) {
                continue;
            }

            $delayUntil = $this->applyDelay(now(), $windowValue, $windowUnit);

            CheckAutomationNegativeCampaignTriggerJob::dispatch(
                $automation->id,
                $subscriber->id,
                (int) $campaign->id,
                (int) $recipient->id,
                $trigger
            )
                ->delay($delayUntil)
                ->onQueue('automations');
        }
    }

    private function automationMatchesEvent(Automation $automation, string $event, ListSubscriber $subscriber, array $context): bool
    {
        $settings = $this->triggerSettings($automation);

        if (($settings['trigger'] ?? '') !== $event) {
            return false;
        }

        if ($event === 'webhook_received') {
            $listId = (int) ($settings['list_id'] ?? 0);
            if ($listId <= 0) {
                return true;
            }
            return $listId === (int) $subscriber->list_id;
        }

        if (str_starts_with($event, 'subscriber_')) {
            return (int) ($settings['list_id'] ?? 0) === (int) $subscriber->list_id;
        }

        if (str_starts_with($event, 'wp_') || str_starts_with($event, 'woo_')) {
            $listId = (int) ($settings['list_id'] ?? 0);
            if ($listId <= 0) {
                return true;
            }
            return $listId === (int) $subscriber->list_id;
        }

        if (str_starts_with($event, 'campaign_')) {
            $campaignId = (int) ($settings['campaign_id'] ?? 0);
            $ctxCampaignId = (int) ($context['campaign_id'] ?? 0);
            if ($campaignId <= 0 || $ctxCampaignId <= 0) {
                return false;
            }

            if ($campaignId !== $ctxCampaignId) {
                return false;
            }

            return true;
        }

        return false;
    }

    private function triggerSettings(Automation $automation): array
    {
        $graph = (array) ($automation->graph ?? []);
        $nodes = (array) ($graph['nodes'] ?? []);

        foreach ($nodes as $node) {
            if (!is_array($node)) {
                continue;
            }
            if (($node['id'] ?? '') === 'trigger_1') {
                $settings = $node['settings'] ?? [];
                return is_array($settings) ? $settings : [];
            }
        }

        return [];
    }

    private function startRun(Automation $automation, ListSubscriber $subscriber, string $event, array $context): void
    {
        DB::transaction(function () use ($automation, $subscriber, $event, $context) {
            AutomationRun::query()->create([
                'automation_id' => $automation->id,
                'subscriber_id' => $subscriber->id,
                'status' => 'active',
                'trigger_event' => $event,
                'trigger_context' => $context,
                'current_node_id' => 'trigger_1',
                'triggered_at' => now(),
                'next_scheduled_for' => now(),
                'locked_at' => null,
            ]);
        });
    }

    private function applyDelay($base, int $value, string $unit)
    {
        if ($value <= 0) {
            return $base;
        }

        return match ($unit) {
            'minutes' => $base->copy()->addMinutes($value),
            'hours' => $base->copy()->addHours($value),
            'days' => $base->copy()->addDays($value),
            'weeks' => $base->copy()->addWeeks($value),
            default => $base,
        };
    }
}
