<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessAutomationRunJob;
use App\Models\Automation;
use App\Models\AutomationRun;
use App\Models\EmailList;
use App\Models\ListSubscriber;
use App\Services\AutomationTriggerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AutomationWebhookController extends Controller
{
    public function handle(Request $request, Automation $automation)
    {
        $token = trim((string) ($request->query('token') ?: $request->header('X-Automation-Token')));

        $settings = $this->triggerSettings($automation);
        $expectedToken = trim((string) ($settings['webhook_token'] ?? ''));

        if ($expectedToken === '' || $token === '' || !hash_equals($expectedToken, $token)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $listId = (int) ($settings['list_id'] ?? 0);

        $email = strtolower(trim((string) $request->input('email')));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['message' => 'Valid email is required'], 422);
        }

        $list = null;
        if ($listId > 0) {
            $list = EmailList::query()->whereKey($listId)->where('customer_id', $automation->customer_id)->first();
            if (!$list) {
                return response()->json(['message' => 'List not found'], 404);
            }
        } else {
            $list = $this->getOrCreateWebhookList($automation);
            if (!$list) {
                return response()->json(['message' => 'Failed to initialize webhook list'], 500);
            }
        }

        $subscriber = ListSubscriber::query()
            ->withTrashed()
            ->where('list_id', $list->id)
            ->where('email', $email)
            ->first();

        if (!$subscriber) {
            $subscriber = ListSubscriber::query()->create([
                'list_id' => $list->id,
                'email' => $email,
                'first_name' => (string) $request->input('first_name', ''),
                'last_name' => (string) $request->input('last_name', ''),
                'status' => 'confirmed',
                'source' => 'webhook',
                'custom_fields' => (array) $request->input('custom_fields', []),
                'tags' => (array) $request->input('tags', []),
                'ip_address' => $request->ip(),
                'subscribed_at' => now(),
                'confirmed_at' => now(),
            ]);
        } else {
            if ($subscriber->trashed()) {
                $subscriber->restore();
            }

            $subscriber->update([
                'first_name' => (string) ($request->input('first_name', $subscriber->first_name) ?? ''),
                'last_name' => (string) ($request->input('last_name', $subscriber->last_name) ?? ''),
            ]);
        }

        $context = [
            'payload' => $request->all(),
            'list_id' => $list->id,
            'headers' => [
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip(),
            ],
        ];

        app(AutomationTriggerService::class)->triggerAutomation($automation, 'webhook_received', $subscriber, $context);

        try {
            $run = AutomationRun::query()
                ->where('automation_id', $automation->id)
                ->where('subscriber_id', $subscriber->id)
                ->latest('id')
                ->first();

            if ($run) {
                ProcessAutomationRunJob::dispatch($run->id)->onQueue('automations');
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to dispatch automation run after webhook', [
                'automation_id' => $automation->id,
                'subscriber_id' => $subscriber->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json(['status' => 'ok']);
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

    private function getOrCreateWebhookList(Automation $automation): ?EmailList
    {
        $name = 'Webhook Contacts';

        $existing = EmailList::query()
            ->withTrashed()
            ->where('customer_id', $automation->customer_id)
            ->where('name', $name)
            ->first();

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }
            return $existing;
        }

        try {
            return EmailList::query()->create([
                'customer_id' => $automation->customer_id,
                'name' => $name,
                'status' => 'active',
                'opt_in' => 'single',
                'opt_out' => 'single',
                'welcome_email_enabled' => false,
                'unsubscribe_email_enabled' => false,
                'description' => 'System list for webhook-triggered automations',
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to create webhook contacts list', [
                'automation_id' => $automation->id,
                'customer_id' => $automation->customer_id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
