<?php

namespace App\Http\Controllers\Api\V1\Customer\Integrations;

use App\Http\Controllers\Controller;
use App\Models\AutomationRun;
use App\Models\EmailList;
use App\Models\ListSubscriber;
use App\Services\AutomationTriggerService;
use App\Services\ListSubscriberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WordPressEventController extends Controller
{
    public function __construct(
        protected ListSubscriberService $listSubscriberService
    ) {
    }

    public function store(Request $request)
    {
        $customer = $request->user('sanctum');
        abort_if(!$customer, 401);

        $validated = $request->validate([
            'event' => ['required', 'string', 'max:255', 'regex:/^(wp|woo)_[a-z0-9_]+$/'],
            'external_id' => ['required', 'string', 'max:255'],
            'occurred_at' => ['nullable', 'date'],

            'list_id' => ['nullable', 'integer', 'min:1'],
            'email' => ['required', 'email', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],

            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:255'],

            'custom_fields' => ['nullable', 'array'],
            'payload' => ['nullable', 'array'],
            'site' => ['nullable', 'array'],
        ]);

        $listId = isset($validated['list_id']) ? (int) $validated['list_id'] : 0;
        $list = $listId > 0
            ? EmailList::query()->whereKey($listId)->where('customer_id', $customer->id)->firstOrFail()
            : $this->systemListForCustomer($customer->id);

        $email = strtolower(trim((string) $validated['email']));

        $subscriber = ListSubscriber::query()
            ->withTrashed()
            ->where('list_id', $list->id)
            ->where('email', $email)
            ->first();

        $tags = (array) ($validated['tags'] ?? []);
        $tags = array_values(array_unique(array_filter($tags, fn ($v) => is_string($v) && trim($v) !== '')));

        $payload = (array) ($validated['payload'] ?? []);

        if (str_starts_with((string) $validated['event'], 'woo_')) {
            $payloadTags = $this->tagsFromWooPayload($payload);
            $tags = array_values(array_unique(array_merge($tags, $payloadTags)));
        }
        $customFields = (array) ($validated['custom_fields'] ?? []);

        if (!$subscriber) {
            $subscriber = $this->listSubscriberService->create($list, [
                'email' => $email,
                'first_name' => $validated['first_name'] ?? null,
                'last_name' => $validated['last_name'] ?? null,
                'source' => 'wordpress',
                'ip_address' => $request->ip(),
                'subscribed_at' => isset($validated['occurred_at']) ? $validated['occurred_at'] : now(),
                'tags' => $tags,
                'custom_fields' => $customFields,
            ]);
        } else {
            if (method_exists($subscriber, 'trashed') && $subscriber->trashed()) {
                $subscriber->restore();
            }

            $existingTags = is_array($subscriber->tags) ? $subscriber->tags : [];
            $mergedTags = array_values(array_unique(array_filter(array_merge($existingTags, $tags), fn ($v) => is_string($v) && trim($v) !== '')));

            $existingCustom = is_array($subscriber->custom_fields) ? $subscriber->custom_fields : [];
            $mergedCustom = array_merge($existingCustom, $customFields);

            $subscriber->update([
                'first_name' => $validated['first_name'] ?? $subscriber->first_name,
                'last_name' => $validated['last_name'] ?? $subscriber->last_name,
                'tags' => $mergedTags,
                'custom_fields' => $mergedCustom,
            ]);

            $subscriber = $subscriber->fresh();
        }

        $context = [
            'external_id' => $validated['external_id'] ?? null,
            'occurred_at' => $validated['occurred_at'] ?? null,
            'payload' => $payload,
            'site' => $validated['site'] ?? [],
            'list_id' => $list->id,
            'headers' => [
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip(),
            ],
        ];

        app(AutomationTriggerService::class)->triggerSubscriberEvent((string) $validated['event'], $subscriber, $context);

        $claimedIds = [];

        DB::transaction(function () use (&$claimedIds, $subscriber, $validated) {
            $runs = AutomationRun::query()
                ->where('subscriber_id', $subscriber->id)
                ->where('trigger_event', (string) $validated['event'])
                ->where('status', 'active')
                ->whereNotNull('next_scheduled_for')
                ->where('next_scheduled_for', '<=', now())
                ->whereNull('locked_at')
                ->orderBy('id')
                ->limit(50)
                ->lockForUpdate()
                ->get();

            foreach ($runs as $run) {
                $run->update(['locked_at' => now()]);
                $claimedIds[] = (int) $run->id;
            }
        });

        foreach ($claimedIds as $runId) {
            \App\Jobs\ProcessAutomationRunJob::dispatch($runId)->onQueue('automations');
        }

        return response()->json([
            'success' => true,
            'data' => [
                'subscriber_id' => $subscriber->id,
                'queued_runs' => count($claimedIds),
            ],
        ], 202);
    }

    private function tagsFromWooPayload(array $payload): array
    {
        $tags = [];

        $orderId = $payload['order_id'] ?? null;
        if (is_scalar($orderId) && (string) $orderId !== '') {
            $tags[] = 'woo_order:' . (string) $orderId;
        }

        $currency = $payload['currency'] ?? null;
        if (is_scalar($currency) && (string) $currency !== '') {
            $tags[] = 'currency:' . strtolower((string) $currency);
        }

        $items = $payload['items'] ?? null;
        if (is_array($items)) {
            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $pid = $item['product_id'] ?? null;
                if (is_scalar($pid) && (string) $pid !== '') {
                    $tags[] = 'product:' . (string) $pid;
                }
                $sku = $item['sku'] ?? null;
                if (is_scalar($sku) && trim((string) $sku) !== '') {
                    $tags[] = 'sku:' . strtolower(trim((string) $sku));
                }
                $categories = $item['categories'] ?? null;
                if (is_array($categories)) {
                    foreach ($categories as $cat) {
                        if (is_scalar($cat) && trim((string) $cat) !== '') {
                            $tags[] = 'category:' . strtolower(trim((string) $cat));
                        }
                    }
                }
            }
        }

        return array_values(array_unique($tags));
    }

    private function systemListForCustomer(int $customerId): EmailList
    {
        return EmailList::query()->firstOrCreate(
            [
                'customer_id' => $customerId,
                'name' => 'System: WordPress/Woo Events',
            ],
            [
                'display_name' => 'System: WordPress/Woo Events',
                'status' => 'active',
                'opt_in' => 'single',
                'opt_out' => 'single',
                'double_opt_in' => false,
                'welcome_email_enabled' => false,
                'unsubscribe_email_enabled' => true,
                'tags' => ['system', 'wordpress'],
                'custom_fields' => [
                    'wp_user_id' => 'number',
                    'woo_customer_id' => 'number',
                ],
            ]
        );
    }
}
