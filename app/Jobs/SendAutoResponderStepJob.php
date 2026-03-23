<?php

namespace App\Jobs;

use App\Mail\AutoResponderStepMailable;
use App\Models\AutoResponder;
use App\Models\AutoResponderDelivery;
use App\Models\AutoResponderRun;
use App\Models\AutoResponderStep;
use App\Models\DeliveryServer;
use App\Models\SuppressionList;
use App\Services\DeliveryServerService;
use App\Services\ZeptoMailApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAutoResponderStepJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;
    public int $timeout = 120;

    public function __construct(
        public int $runId
    ) {
    }

    public function handle(): void
    {
        $run = AutoResponderRun::query()->find($this->runId);
        if (!$run || $run->status !== 'active') {
            return;
        }

        $run->loadMissing('subscriber', 'autoResponder.customer', 'list');

        $subscriber = $run->subscriber;
        $autoResponder = $run->autoResponder;

        if (!$subscriber || !$autoResponder) {
            $this->unlock($run);
            return;
        }

        if ($subscriber->isBounced() || $subscriber->isComplained() || $subscriber->isSuppressed()) {
            $this->stopRun($run, 'suppressed');
            return;
        }

        if (SuppressionList::isSuppressed($subscriber->email, $autoResponder->customer_id)) {
            $this->stopRun($run, 'suppression_list');
            return;
        }

        if ($subscriber->isUnsubscribed() && $autoResponder->trigger !== 'subscriber_unsubscribed') {
            $this->stopRun($run, 'unsubscribed');
            return;
        }

        $stepOrder = (int) $run->next_step_order;

        $step = AutoResponderStep::query()
            ->where('auto_responder_id', $autoResponder->id)
            ->where('step_order', $stepOrder)
            ->where('status', 'active')
            ->first();

        if (!$step) {
            $this->completeRun($run);
            return;
        }

        $delivery = AutoResponderDelivery::query()->firstOrCreate(
            [
                'auto_responder_id' => $autoResponder->id,
                'subscriber_id' => $subscriber->id,
                'step_order' => $stepOrder,
            ],
            [
                'auto_responder_run_id' => $run->id,
                'auto_responder_step_id' => $step->id,
                'list_id' => $run->list_id,
                'status' => 'pending',
                'triggered_at' => $run->triggered_at,
                'scheduled_for' => $run->next_scheduled_for,
            ]
        );

        if ($delivery->status === 'sent') {
            $this->advanceRun($run, $stepOrder);
            return;
        }

        try {
            $mailerConfig = $this->configureMailer($autoResponder, $step);
            $deliveryServer = $mailerConfig['server'] ?? null;

            if ($deliveryServer && $deliveryServer->type === 'zeptomail-api') {
                $mailable = new AutoResponderStepMailable($autoResponder, $step, $subscriber);

                $fromEmail = (string) ($step->from_email ?? $autoResponder->from_email ?? ($deliveryServer->from_email ?? config('mail.from.address')));
                $fromName = (string) ($step->from_name ?? $autoResponder->from_name ?? ($deliveryServer->from_name ?? config('mail.from.name')));

                $zepto = app(ZeptoMailApiService::class);
                $message = [
                    'from_email' => $fromEmail,
                    'from_name' => $fromName,
                    'to_email' => (string) $subscriber->email,
                    'to_name' => trim((string) (($subscriber->first_name ?? '') . ' ' . ($subscriber->last_name ?? ''))),
                    'subject' => $mailable->envelope()->subject,
                    'htmlbody' => $mailable->content()->with['htmlContent'] ?? '',
                    'textbody' => $mailable->content()->with['plainTextContent'] ?? '',
                    'client_reference' => 'autoresponder-' . $autoResponder->id . '-run-' . $run->id,
                ];

                $deliveryServer->loadMissing('bounceServer');
                if (empty(($deliveryServer->settings ?? [])['bounce_address']) && !empty($deliveryServer->bounceServer?->username)) {
                    $message['bounce_address'] = (string) $deliveryServer->bounceServer->username;
                }

                $zepto->sendRaw($deliveryServer, $message);
            } else {
                $mailerName = $mailerConfig['mailer'] ?? null;
                $mailer = $mailerName ? Mail::mailer($mailerName) : Mail::mailer();

                $mailer->to($subscriber->email)
                    ->send(new AutoResponderStepMailable($autoResponder, $step, $subscriber));
            }

            DB::transaction(function () use ($delivery, $autoResponder, $run) {
                $delivery->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'failure_reason' => null,
                ]);

                $autoResponder->increment('sent_count');

                $run->update([
                    'last_sent_at' => now(),
                ]);
            });

            $this->advanceRun($run, $stepOrder);
        } catch (\Throwable $e) {
            Log::error('Failed to send autoresponder email', [
                'run_id' => $run->id,
                'auto_responder_id' => $autoResponder->id,
                'subscriber_id' => $subscriber->id,
                'step_order' => $stepOrder,
                'error' => $e->getMessage(),
            ]);

            $delivery->update([
                'status' => 'failed',
                'failure_reason' => $e->getMessage(),
            ]);

            $run->update([
                'next_scheduled_for' => now()->addMinutes(15),
                'locked_at' => null,
            ]);

            throw $e;
        }
    }

    private function configureMailer(AutoResponder $autoResponder, AutoResponderStep $step): array
    {
        $requestedServerId = $step->delivery_server_id ?? $autoResponder->delivery_server_id;

        $autoResponder->loadMissing('customer');
        $customer = $autoResponder->customer;

        $deliveryServer = null;

        if ($customer) {
            $mustAddDelivery = (bool) $customer->groupSetting('servers.permissions.must_add_delivery_server', false);
            $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);

            $deliveryServer = app(DeliveryServerService::class)->resolveDeliveryServerForCustomer(
                $customer,
                $requestedServerId ? (int) $requestedServerId : null,
                $mustAddDelivery,
                $canUseSystem
            );

            if (!$deliveryServer && $mustAddDelivery) {
                throw new \RuntimeException('Delivery server is required');
            }
        } else {
            $deliveryServer = DeliveryServer::query()
                ->with('bounceServer')
                ->where('status', 'active')
                ->where('use_for', true)
                ->when($requestedServerId, function ($q) use ($requestedServerId) {
                    $q->whereKey($requestedServerId);
                })
                ->when(!$requestedServerId, function ($q) {
                    $q->orderBy('id');
                })
                ->first();
        }

        if ($deliveryServer) {
            app(DeliveryServerService::class)->configureMailFromServer($deliveryServer);
        }

        return [
            'mailer' => config('mail.default', 'smtp'),
            'server' => $deliveryServer,
        ];
    }

    private function advanceRun(AutoResponderRun $run, int $currentStepOrder): void
    {
        $autoResponder = $run->autoResponder;

        $nextOrder = $currentStepOrder + 1;

        $nextStep = AutoResponderStep::query()
            ->where('auto_responder_id', $autoResponder->id)
            ->where('step_order', $nextOrder)
            ->where('status', 'active')
            ->first();

        if (!$nextStep) {
            $this->completeRun($run);
            return;
        }

        $nextScheduled = $this->applyDelay(now(), (int) $nextStep->delay_value, (string) $nextStep->delay_unit);

        $run->update([
            'next_step_order' => $nextOrder,
            'next_scheduled_for' => $nextScheduled,
            'locked_at' => null,
        ]);
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

    private function completeRun(AutoResponderRun $run): void
    {
        $run->update([
            'status' => 'completed',
            'completed_at' => now(),
            'next_scheduled_for' => null,
            'locked_at' => null,
        ]);
    }

    private function stopRun(AutoResponderRun $run, string $reason): void
    {
        $run->update([
            'status' => 'stopped',
            'stopped_at' => now(),
            'stop_reason' => $reason,
            'next_scheduled_for' => null,
            'locked_at' => null,
        ]);
    }

    private function unlock(AutoResponderRun $run): void
    {
        $run->update([
            'locked_at' => null,
        ]);
    }
}
