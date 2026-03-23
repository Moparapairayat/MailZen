<?php

namespace App\Services;

use App\Models\AutoResponder;
use App\Models\AutoResponderRun;
use App\Models\AutoResponderStep;
use App\Models\ListSubscriber;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoResponderTriggerService
{
    public function triggerSubscriberEvent(string $event, ListSubscriber $subscriber, array $context = []): void
    {
        $subscriber->loadMissing('list');

        $autoResponders = AutoResponder::query()
            ->where('list_id', $subscriber->list_id)
            ->where('status', 'active')
            ->where('trigger', $event)
            ->get();

        foreach ($autoResponders as $autoResponder) {
            try {
                if (!$this->standardTriggerMatches($autoResponder, $subscriber, $event, $context)) {
                    continue;
                }

                $this->startRun($autoResponder, $subscriber);
            } catch (\Throwable $e) {
                Log::error('Failed to trigger autoresponder', [
                    'auto_responder_id' => $autoResponder->id,
                    'subscriber_id' => $subscriber->id,
                    'event' => $event,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function standardTriggerMatches(AutoResponder $autoResponder, ListSubscriber $subscriber, string $event, array $context): bool
    {
        if ($autoResponder->trigger !== $event) {
            return true;
        }

        if ($event !== 'subscriber_added') {
            return true;
        }

        $settings = (array) ($autoResponder->trigger_settings ?? []);
        $source = (string) ($subscriber->source ?? '');

        $sources = $settings['sources'] ?? null;
        if (is_string($sources) && $sources !== '') {
            $sources = [$sources];
        }

        if (is_array($sources) && !empty($sources)) {
            return in_array($source, $sources, true);
        }

        $singleSource = (string) ($settings['source'] ?? '');
        if ($singleSource !== '') {
            return $source === $singleSource;
        }

        return true;
    }

    private function startRun(AutoResponder $autoResponder, ListSubscriber $subscriber): void
    {
        $autoResponder->loadMissing('customer');

        DB::transaction(function () use ($autoResponder, $subscriber) {
            $existing = AutoResponderRun::query()
                ->where('auto_responder_id', $autoResponder->id)
                ->where('subscriber_id', $subscriber->id)
                ->first();

            if ($existing && $existing->status === 'active') {
                return;
            }

            if ($existing && in_array($existing->status, ['completed', 'stopped'], true)) {
                return;
            }

            $step1 = AutoResponderStep::query()
                ->where('auto_responder_id', $autoResponder->id)
                ->where('step_order', 1)
                ->where('status', 'active')
                ->first();

            if (!$step1) {
                return;
            }

            $now = now();
            $nextScheduled = $this->applyDelay($now, (int) $step1->delay_value, (string) $step1->delay_unit);

            AutoResponderRun::query()->updateOrCreate(
                [
                    'auto_responder_id' => $autoResponder->id,
                    'subscriber_id' => $subscriber->id,
                ],
                [
                    'list_id' => $subscriber->list_id,
                    'status' => 'active',
                    'triggered_at' => $now,
                    'next_step_order' => 1,
                    'next_scheduled_for' => $nextScheduled,
                    'completed_at' => null,
                    'stopped_at' => null,
                    'stop_reason' => null,
                    'locked_at' => null,
                ]
            );
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
