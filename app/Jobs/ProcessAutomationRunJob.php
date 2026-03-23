<?php

namespace App\Jobs;

use App\Models\AutomationRun;
use App\Models\Campaign;
use App\Models\Customer;
use App\Models\DeliveryServer;
use App\Models\EmailList;
use App\Models\ListSubscriber;
use App\Models\Template;
use App\Services\PersonalizationService;
use App\Services\CampaignService;
use App\Services\AutomationTriggerService;
use App\Services\DeliveryServerService;
use App\Services\ZeptoMailApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessAutomationRunJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;
    public int $timeout = 180;

    public function __construct(
        public int $runId
    ) {
    }

    public function handle(): void
    {
        $run = AutomationRun::query()->find($this->runId);
        if (!$run || $run->status !== 'active') {
            return;
        }

        $run->loadMissing('automation', 'subscriber.list');

        $automation = $run->automation;
        $subscriber = $run->subscriber;

        if (!$automation || !$subscriber) {
            $this->unlock($run);
            return;
        }

        $graph = (array) ($automation->graph ?? []);
        $nodes = (array) ($graph['nodes'] ?? []);
        $edges = (array) ($graph['edges'] ?? []);

        $byId = [];
        foreach ($nodes as $n) {
            if (is_array($n) && isset($n['id'])) {
                $byId[(string) $n['id']] = $n;
            }
        }

        $currentId = (string) ($run->current_node_id ?? 'trigger_1');
        $current = $byId[$currentId] ?? null;
        if (!$current) {
            $this->complete($run);
            return;
        }

        $type = (string) ($current['type'] ?? '');
        $settings = is_array($current['settings'] ?? null) ? $current['settings'] : [];

        if ($type === 'trigger') {
            $next = $this->firstOutgoing($edges, $currentId, null);
            if (!$next) {
                $this->complete($run);
                return;
            }

            $this->advanceTo($run, $next, now());
            return;
        }

        if ($type === 'delay') {
            $value = (int) ($settings['delay_value'] ?? 0);
            $unit = (string) ($settings['delay_unit'] ?? 'hours');

            $next = $this->firstOutgoing($edges, $currentId, null);
            if (!$next) {
                $this->complete($run);
                return;
            }

            $scheduledFor = $this->applyDelay(now(), $value, $unit);
            $this->advanceTo($run, $next, $scheduledFor);
            return;
        }

        if ($type === 'condition') {
            $result = $this->evaluateCondition($subscriber, $settings, (array) ($run->trigger_context ?? []));
            $branch = $result ? 'yes' : 'no';

            $next = $this->firstOutgoing($edges, $currentId, $branch);
            $next = $next ?: $this->firstOutgoing($edges, $currentId, null);

            if (!$next) {
                $this->complete($run);
                return;
            }

            $this->advanceTo($run, $next, now());
            return;
        }

        if ($type === 'email') {
            $this->sendAutomationEmail($automation->customer_id, $subscriber, $settings);
            $next = $this->firstOutgoing($edges, $currentId, null);
            if (!$next) {
                $this->complete($run);
                return;
            }
            $this->advanceTo($run, $next, now());
            return;
        }

        if ($type === 'webhook') {
            $this->sendWebhook($subscriber, $settings, (array) ($run->trigger_context ?? []));
            $next = $this->firstOutgoing($edges, $currentId, null);
            if (!$next) {
                $this->complete($run);
                return;
            }
            $this->advanceTo($run, $next, now());
            return;
        }

        if ($type === 'run_campaign') {
            $this->sendCampaignToSubscriber($subscriber, $settings);
            $next = $this->firstOutgoing($edges, $currentId, null);
            if (!$next) {
                $this->complete($run);
                return;
            }
            $this->advanceTo($run, $next, now());
            return;
        }

        if ($type === 'move_subscribers' || $type === 'copy_subscribers') {
            $this->transferSubscriber($subscriber, $settings, $type === 'move_subscribers');
            $next = $this->firstOutgoing($edges, $currentId, null);
            if (!$next) {
                $this->complete($run);
                return;
            }
            $this->advanceTo($run, $next, now());
            return;
        }

        $next = $this->firstOutgoing($edges, $currentId, null);
        if (!$next) {
            $this->complete($run);
            return;
        }

        $this->advanceTo($run, $next, now());
    }

    private function firstOutgoing(array $edges, string $from, ?string $branch): ?string
    {
        foreach ($edges as $e) {
            if (!is_array($e)) continue;
            if ((string) ($e['from'] ?? '') !== $from) continue;
            $b = $e['branch'] ?? null;
            $b = is_string($b) && $b !== '' ? $b : null;
            if ($branch === null) {
                if ($b === null) {
                    return (string) ($e['to'] ?? '');
                }
                continue;
            }

            if ($b === $branch) {
                return (string) ($e['to'] ?? '');
            }
        }

        return null;
    }

    private function advanceTo(AutomationRun $run, string $nodeId, $scheduledFor): void
    {
        $run->update([
            'current_node_id' => $nodeId,
            'next_scheduled_for' => $scheduledFor,
            'locked_at' => null,
        ]);
    }

    private function complete(AutomationRun $run): void
    {
        $run->update([
            'status' => 'completed',
            'next_scheduled_for' => null,
            'locked_at' => null,
        ]);
    }

    private function unlock(AutomationRun $run): void
    {
        $run->update([
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

    private function evaluateCondition(ListSubscriber $subscriber, array $settings, array $context): bool
    {
        $field = trim((string) ($settings['field'] ?? ''));
        $operator = trim((string) ($settings['operator'] ?? 'equals'));
        $value = trim((string) ($settings['value'] ?? ''));

        if ($field === '' || $value === '') {
            return false;
        }

        $actual = null;

        if (str_starts_with($field, 'custom_fields.')) {
            $key = substr($field, strlen('custom_fields.'));
            $custom = is_array($subscriber->custom_fields) ? $subscriber->custom_fields : [];
            $actual = $custom[$key] ?? null;
        } elseif (str_starts_with($field, 'payload.') || str_starts_with($field, 'context.payload.')) {
            $path = str_starts_with($field, 'payload.')
                ? substr($field, strlen('payload.'))
                : substr($field, strlen('context.payload.'));

            $current = $context['payload'] ?? [];
            foreach (explode('.', $path) as $segment) {
                $segment = trim((string) $segment);
                if ($segment === '' || !is_array($current) || !array_key_exists($segment, $current)) {
                    $current = null;
                    break;
                }
                $current = $current[$segment];
            }
            $actual = $current;
        } else {
            $actual = $subscriber->{$field} ?? null;
        }

        if (is_scalar($actual)) {
            $actual = (string) $actual;
        } elseif (is_array($actual)) {
            $actual = json_encode($actual) ?: '';
        } else {
            $actual = '';
        }

        if ($operator === 'contains') {
            return stripos($actual, $value) !== false;
        }

        return strcasecmp($actual, $value) === 0;
    }

    private function sendAutomationEmail(int $customerId, ListSubscriber $subscriber, array $settings): void
    {
        $subject = trim((string) ($settings['subject'] ?? ''));
        $templateId = (int) ($settings['template_id'] ?? 0);
        $deliveryServerId = (int) ($settings['delivery_server_id'] ?? 0);

        $customer = Customer::query()->find($customerId);
        $mustAddDelivery = $customer ? (bool) $customer->groupSetting('servers.permissions.must_add_delivery_server', false) : false;
        $canUseSystem = $customer ? (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false) : false;

        $template = $templateId > 0 ? Template::query()->find($templateId) : null;
        if (!$template) {
            throw new \RuntimeException('Template not found');
        }

        $html = (string) ($template->html_content ?? '');
        $text = (string) ($template->plain_text_content ?? strip_tags($html));

        $html = $this->personalize($html, $subscriber);
        $text = $this->personalize($text, $subscriber);
        $subject = $this->personalize($subject, $subscriber);

        $unsubscribeUrl = $this->unsubscribeUrl($subscriber);
        if ($html !== '') {
            $footer = '<p style="font-size: 12px; color: #999; margin-top: 20px; text-align:center;"><a href="' . $unsubscribeUrl . '" style="color: #999;">Unsubscribe</a></p>';
            if (stripos($html, '</body>') !== false) {
                $html = str_ireplace('</body>', $footer . '</body>', $html);
            } else {
                $html .= $footer;
            }
        }
        $text = rtrim($text) . "\n\n---\nUnsubscribe: {$unsubscribeUrl}";

        $server = null;
        if ($customer) {
            $server = app(DeliveryServerService::class)->resolveDeliveryServerForCustomer(
                $customer,
                $deliveryServerId > 0 ? $deliveryServerId : null,
                $mustAddDelivery,
                $canUseSystem
            );
        } elseif ($deliveryServerId > 0) {
            $server = DeliveryServer::query()->with('bounceServer')->find($deliveryServerId);
        }

        if (!$server && $mustAddDelivery) {
            throw new \RuntimeException('Delivery server is required');
        }

        if ($server && $server->type === 'zeptomail-api') {
            $message = [
                'from_email' => (string) ($settings['from_email'] ?? ($server->from_email ?? config('mail.from.address'))),
                'from_name' => (string) ($settings['from_name'] ?? ($server->from_name ?? config('mail.from.name'))),
                'to_email' => (string) $subscriber->email,
                'to_name' => trim((string) (($subscriber->first_name ?? '') . ' ' . ($subscriber->last_name ?? ''))),
                'subject' => $subject,
                'htmlbody' => $html,
                'textbody' => $text,
                'client_reference' => 'automation-email-' . $subscriber->id . '-' . now()->timestamp,
            ];

            if (empty(($server->settings ?? [])['bounce_address']) && !empty($server->bounceServer?->username)) {
                $message['bounce_address'] = (string) $server->bounceServer->username;
            }

            app(ZeptoMailApiService::class)->sendRaw($server, $message);
            return;
        }

        if ($server) {
            app(DeliveryServerService::class)->configureMailFromServer($server);
        }

        Mail::raw($text, function ($message) use ($subscriber, $subject, $html, $settings, $server) {
            $fromEmail = (string) ($settings['from_email'] ?? ($server?->from_email ?? config('mail.from.address')));
            $fromName = (string) ($settings['from_name'] ?? ($server?->from_name ?? config('mail.from.name')));

            $message->to($subscriber->email)
                ->subject($subject)
                ->from($fromEmail, $fromName);

            $replyTo = trim((string) ($settings['reply_to'] ?? ''));
            if ($replyTo !== '') {
                $message->replyTo($replyTo);
            }

            if ($server && $server->bounceServer && $server->bounceServer->username) {
                $message->returnPath($server->bounceServer->username);
            }

            if ($html !== '') {
                $message->html($html);
            }
        });
    }

    private function sendWebhook(ListSubscriber $subscriber, array $settings, array $context): void
    {
        $url = trim((string) ($settings['url'] ?? ''));
        $method = strtoupper(trim((string) ($settings['method'] ?? 'POST')));

        if ($url === '') {
            return;
        }

        $payload = [
            'subscriber' => [
                'id' => $subscriber->id,
                'email' => $subscriber->email,
                'first_name' => $subscriber->first_name,
                'last_name' => $subscriber->last_name,
                'list_id' => $subscriber->list_id,
                'status' => $subscriber->status,
                'custom_fields' => $subscriber->custom_fields,
                'tags' => $subscriber->tags,
            ],
            'context' => $context,
        ];

        Http::timeout(10)->send($method, $url, [
            'json' => $payload,
        ]);
    }

    private function sendCampaignToSubscriber(ListSubscriber $subscriber, array $settings): void
    {
        $campaignId = (int) ($settings['campaign_id'] ?? 0);
        if ($campaignId <= 0) {
            return;
        }

        $campaign = Campaign::query()->find($campaignId);
        if (!$campaign) {
            return;
        }

        $recipient = CampaignRecipient::query()->create([
            'campaign_id' => $campaign->id,
            'email' => $subscriber->email,
            'first_name' => $subscriber->first_name,
            'last_name' => $subscriber->last_name,
            'status' => 'pending',
        ]);

        $campaign->loadMissing('deliveryServer.bounceServer');
        $server = $campaign->deliveryServer;

        if ($server) {
            app(DeliveryServerService::class)->configureMailFromServer($server);
        }

        $mailerName = config('mail.default', 'smtp');
        $mailer = $mailerName ? Mail::mailer($mailerName) : Mail::mailer();
        $mailer->to($recipient->email)->send(new \App\Mail\CampaignMailable($campaign, $recipient));

        $recipient->markAsSent();

        try {
            app(AutomationTriggerService::class)->scheduleNegativeCampaignTriggersForRecipient($campaign, $recipient);
        } catch (\Throwable $e) {
            Log::warning('Failed scheduling negative campaign automation triggers (automation run_campaign node)', [
                'campaign_id' => $campaign->id,
                'recipient_id' => $recipient->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function transferSubscriber(ListSubscriber $subscriber, array $settings, bool $removeFromOriginal): void
    {
        $targetListId = (int) ($settings['target_list_id'] ?? 0);
        if ($targetListId <= 0) {
            return;
        }

        $targetList = EmailList::query()->find($targetListId);
        if (!$targetList) {
            return;
        }

        $service = app(\App\Services\ListSubscriberService::class);
        $service->create($targetList, [
            'email' => $subscriber->email,
            'first_name' => $subscriber->first_name,
            'last_name' => $subscriber->last_name,
            'status' => 'confirmed',
            'source' => 'automation',
            'custom_fields' => $subscriber->custom_fields ?? [],
            'tags' => $subscriber->tags ?? [],
        ]);

        if ($removeFromOriginal) {
            $service->unsubscribe($subscriber);
        }
    }

    private function personalize(string $content, ListSubscriber $subscriber): string
    {
        return app(PersonalizationService::class)->personalizeForSubscriber($content, $subscriber);
    }

    private function unsubscribeUrl(ListSubscriber $subscriber): string
    {
        $token = hash('sha256', $subscriber->email . $subscriber->list_id . config('app.key'));

        return route('public.unsubscribe', [
            'list' => $subscriber->list_id,
            'email' => $subscriber->email,
            'token' => $token,
        ]);
    }
}
