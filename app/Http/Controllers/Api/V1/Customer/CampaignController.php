<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Jobs\SendCampaignChunkJob;
use App\Jobs\StartCampaignJob;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\ReplyServer;
use App\Notifications\CampaignStatusUpdatedNotification;
use App\Services\CampaignService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CampaignController extends Controller
{
    public function __construct(
        protected CampaignService $campaignService
    ) {
    }

    protected function customer(Request $request)
    {
        return $request->user('sanctum');
    }

    protected function authorizeOwnership(Request $request, Campaign $campaign): Campaign
    {
        $customer = $this->customer($request);
        if (!$customer || (int) $campaign->customer_id !== (int) $customer->id) {
            abort(404);
        }

        return $campaign;
    }

    public function index(Request $request)
    {
        $customer = $this->customer($request);

        $filters = $request->only(['search', 'status', 'type']);
        $campaigns = $this->campaignService->getPaginated($customer, $filters);

        return response()->json([
            'data' => $campaigns->items(),
            'meta' => [
                'current_page' => $campaigns->currentPage(),
                'per_page' => $campaigns->perPage(),
                'total' => $campaigns->total(),
                'last_page' => $campaigns->lastPage(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $customer = $this->customer($request);

        $customer->enforceGroupLimit('campaigns.limits.max_campaigns', $customer->campaigns()->count(), 'Campaign limit reached.');

        $mustAddReply = (bool) $customer->groupSetting('servers.permissions.must_add_reply_server', false);
        $mustAddDelivery = (bool) $customer->groupSetting('servers.permissions.must_add_delivery_server', false);
        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'list_id' => ['nullable', 'exists:email_lists,id'],
            'template_id' => ['nullable', 'exists:templates,id'],
            'delivery_server_id' => [
                'nullable',
                Rule::exists('delivery_servers', 'id')->where(function ($q) use ($customer, $mustAddDelivery, $canUseSystem) {
                    $q->where('status', 'active')->where('use_for', true);

                    if ($mustAddDelivery || !$canUseSystem) {
                        $q->where('customer_id', $customer->id);
                        return;
                    }

                    $q->where(function ($sub) use ($customer) {
                        $sub->where('customer_id', $customer->id)
                            ->orWhereNull('customer_id');
                    });
                }),
            ],
            'reply_server_id' => [
                'nullable',
                Rule::exists('reply_servers', 'id')->where(function ($q) use ($customer, $mustAddReply, $canUseSystem) {
                    $q->where('active', true);

                    if ($mustAddReply || !$canUseSystem) {
                        $q->where('customer_id', $customer->id);
                        return;
                    }

                    $q->where(function ($sub) use ($customer) {
                        $sub->where('customer_id', $customer->id)
                            ->orWhereNull('customer_id');
                    });
                }),
            ],
            'sending_domain_id' => ['nullable', 'exists:sending_domains,id'],
            'tracking_domain_id' => ['nullable', 'exists:tracking_domains,id'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'from_email' => ['nullable', 'email', 'max:255'],
            'reply_to' => ['nullable', 'email', 'max:255'],
            'type' => ['nullable', 'in:regular,autoresponder,recurring'],
            'status' => ['nullable', 'in:draft,queued,scheduled,running,paused,completed,failed'],
            'html_content' => ['nullable', 'string'],
            'plain_text_content' => ['nullable', 'string'],
            'send_at' => ['nullable', 'date'],
            'scheduled_at' => ['nullable', 'date'],
            'recurring_interval_days' => ['nullable', 'integer', 'min:1'],
            'track_opens' => ['nullable', 'boolean'],
            'track_clicks' => ['nullable', 'boolean'],
            'template_data' => ['nullable', 'array'],
            'segments' => ['nullable', 'array'],
            'settings' => ['nullable', 'array'],
        ]);

        $customerTimezone = $customer->timezone ?? 'UTC';
        if (!empty($validated['send_at'])) {
            $validated['send_at'] = Carbon::parse($validated['send_at'], $customerTimezone)->utc();
        }
        if (!empty($validated['scheduled_at'])) {
            $validated['scheduled_at'] = Carbon::parse($validated['scheduled_at'], $customerTimezone)->utc();
        }

        foreach (['delivery_server_id', 'reply_server_id', 'sending_domain_id', 'tracking_domain_id', 'list_id', 'template_id'] as $key) {
            if (isset($validated[$key]) && $validated[$key] === '') {
                $validated[$key] = null;
            }
        }

        if (!empty($validated['send_at']) && empty($validated['scheduled_at'])) {
            $validated['scheduled_at'] = $validated['send_at'];
            $validated['status'] = 'scheduled';
        }

        if (($validated['type'] ?? 'regular') === 'recurring') {
            $settings = (array) ($validated['settings'] ?? []);
            $settings['recurring'] = array_merge((array) ($settings['recurring'] ?? []), [
                'interval_days' => (int) ($validated['recurring_interval_days'] ?? 7),
            ]);
            $validated['settings'] = $settings;

            if (empty($validated['scheduled_at'])) {
                $validated['scheduled_at'] = now();
            }
            $validated['status'] = 'scheduled';
        }

        unset($validated['recurring_interval_days']);

        $campaign = $this->campaignService->create($customer, $validated);

        return response()->json(['data' => $campaign], 201);
    }

    public function show(Request $request, Campaign $campaign)
    {
        $campaign = $this->authorizeOwnership($request, $campaign);
        $campaign->load(['emailList', 'trackingDomain', 'sendingDomain', 'deliveryServer', 'replyServer', 'variants']);

        return response()->json(['data' => $campaign]);
    }

    public function update(Request $request, Campaign $campaign)
    {
        $campaign = $this->authorizeOwnership($request, $campaign);

        $customer = $this->customer($request);
        $mustAddReply = (bool) $customer->groupSetting('servers.permissions.must_add_reply_server', false);
        $mustAddDelivery = (bool) $customer->groupSetting('servers.permissions.must_add_delivery_server', false);
        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'list_id' => ['nullable', 'exists:email_lists,id'],
            'template_id' => ['nullable', 'exists:templates,id'],
            'delivery_server_id' => [
                'nullable',
                Rule::exists('delivery_servers', 'id')->where(function ($q) use ($customer, $mustAddDelivery, $canUseSystem) {
                    $q->where('status', 'active')->where('use_for', true);

                    if ($mustAddDelivery || !$canUseSystem) {
                        $q->where('customer_id', $customer->id);
                        return;
                    }

                    $q->where(function ($sub) use ($customer) {
                        $sub->where('customer_id', $customer->id)
                            ->orWhereNull('customer_id');
                    });
                }),
            ],
            'reply_server_id' => [
                'nullable',
                Rule::exists('reply_servers', 'id')->where(function ($q) use ($customer, $mustAddReply, $canUseSystem) {
                    $q->where('active', true);

                    if ($mustAddReply || !$canUseSystem) {
                        $q->where('customer_id', $customer->id);
                        return;
                    }

                    $q->where(function ($sub) use ($customer) {
                        $sub->where('customer_id', $customer->id)
                            ->orWhereNull('customer_id');
                    });
                }),
            ],
            'sending_domain_id' => ['nullable', 'exists:sending_domains,id'],
            'tracking_domain_id' => ['nullable', 'exists:tracking_domains,id'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'from_email' => ['nullable', 'email', 'max:255'],
            'reply_to' => ['nullable', 'email', 'max:255'],
            'type' => ['nullable', 'in:regular,autoresponder,recurring'],
            'status' => ['nullable', 'in:draft,queued,scheduled,running,paused,completed,failed'],
            'html_content' => ['nullable', 'string'],
            'plain_text_content' => ['nullable', 'string'],
            'send_at' => ['nullable', 'date'],
            'scheduled_at' => ['nullable', 'date'],
            'recurring_interval_days' => ['nullable', 'integer', 'min:1'],
            'track_opens' => ['nullable', 'boolean'],
            'track_clicks' => ['nullable', 'boolean'],
            'template_data' => ['nullable', 'array'],
            'segments' => ['nullable', 'array'],
            'settings' => ['nullable', 'array'],
        ]);

        $customerTimezone = $customer->timezone ?? 'UTC';
        if (!empty($validated['send_at'])) {
            $validated['send_at'] = Carbon::parse($validated['send_at'], $customerTimezone)->utc();
        }
        if (!empty($validated['scheduled_at'])) {
            $validated['scheduled_at'] = Carbon::parse($validated['scheduled_at'], $customerTimezone)->utc();
        }

        foreach (['delivery_server_id', 'reply_server_id', 'sending_domain_id', 'tracking_domain_id', 'list_id', 'template_id'] as $key) {
            if (isset($validated[$key]) && $validated[$key] === '') {
                $validated[$key] = null;
            }
        }

        if (!empty($validated['send_at']) && empty($validated['scheduled_at'])) {
            $validated['scheduled_at'] = $validated['send_at'];
            $validated['status'] = 'scheduled';
        }

        if (($validated['type'] ?? $campaign->type) === 'recurring') {
            $settings = (array) ($validated['settings'] ?? $campaign->settings ?? []);
            $settings['recurring'] = array_merge((array) ($settings['recurring'] ?? []), [
                'interval_days' => (int) ($validated['recurring_interval_days'] ?? data_get($settings, 'recurring.interval_days', 7)),
            ]);
            $validated['settings'] = $settings;

            if (empty($validated['scheduled_at'])) {
                $validated['scheduled_at'] = $campaign->scheduled_at ?? now();
            }
            $validated['status'] = 'scheduled';
        }

        unset($validated['recurring_interval_days']);

        $updated = $this->campaignService->update($campaign, $validated);

        return response()->json(['data' => $updated]);
    }

    public function destroy(Request $request, Campaign $campaign)
    {
        $campaign = $this->authorizeOwnership($request, $campaign);
        $this->campaignService->delete($campaign);

        return response()->json(['success' => true]);
    }

    public function start(Request $request, Campaign $campaign)
    {
        $campaign = $this->authorizeOwnership($request, $campaign);

        if ($campaign->type === 'recurring') {
            if (!$campaign->scheduled_at || $campaign->scheduled_at->isPast()) {
                $campaign->update([
                    'status' => 'scheduled',
                    'scheduled_at' => now(),
                ]);
            } else {
                $campaign->update([
                    'status' => 'scheduled',
                ]);
            }

            return response()->json([
                'data' => $campaign->fresh(),
                'message' => 'Recurring campaign has been scheduled and will run automatically.',
            ]);
        }

        if (!$campaign->canStart()) {
            return response()->json([
                'message' => 'Campaign cannot be started. Only draft or scheduled campaigns can be started.',
            ], 422);
        }

        try {
            $this->campaignService->ensureCanRun($campaign);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        $campaign->loadMissing('emailList');

        if (!$campaign->list_id) {
            $campaign->update([
                'status' => 'failed',
                'failure_reason' => 'Campaign must have an email list selected.',
            ]);

            return response()->json([
                'message' => 'Campaign must have an email list selected.',
            ], 422);
        }

        if (!$campaign->html_content && !$campaign->plain_text_content) {
            $campaign->update([
                'status' => 'failed',
                'failure_reason' => 'Campaign must have content (HTML or plain text).',
            ]);

            return response()->json([
                'message' => 'Campaign must have content (HTML or plain text).',
            ], 422);
        }

        if ($campaign->emailList && $campaign->emailList->subscribers()->where('status', 'confirmed')->count() === 0) {
            $campaign->update([
                'status' => 'failed',
                'failure_reason' => 'Email list has no confirmed subscribers. Please add subscribers to the list first.',
            ]);

            return response()->json([
                'message' => 'Email list has no confirmed subscribers. Please add subscribers to the list first.',
            ], 422);
        }

        $queueConnection = config('queue.default', 'sync');
        if ($queueConnection === 'sync') {
            Log::warning(
                "Campaign {$campaign->id} started with sync queue. " .
                "Jobs will run synchronously. Consider using 'database' or 'redis' queue connection."
            );
        }

        if ($campaign->scheduled_at && $campaign->scheduled_at->isFuture() && $queueConnection !== 'sync') {
            $campaign->update([
                'status' => 'scheduled',
            ]);

            StartCampaignJob::dispatch($campaign)
                ->delay($campaign->scheduled_at)
                ->onQueue('campaigns');

            return response()->json([
                'data' => $campaign->fresh(),
                'message' => 'Campaign has been scheduled and will start automatically at the selected time.',
            ], 202);
        }

        try {
            $campaign->update([
                'status' => 'queued',
            ]);

            StartCampaignJob::dispatch($campaign)
                ->onQueue('campaigns');

            return response()->json([
                'data' => $campaign->fresh(),
                'message' => 'Campaign has been queued to start.',
            ], 202);
        } catch (\Throwable $e) {
            Log::error("Failed to dispatch StartCampaignJob for campaign {$campaign->id}: " . $e->getMessage());

            $campaign->update([
                'status' => 'failed',
                'failure_reason' => 'Failed to queue campaign: ' . $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to start campaign: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function pause(Request $request, Campaign $campaign)
    {
        $campaign = $this->authorizeOwnership($request, $campaign);

        if (!$campaign->canPause()) {
            return response()->json([
                'message' => 'Campaign cannot be paused. Only running campaigns can be paused.',
            ], 422);
        }

        DB::transaction(function () use ($campaign) {
            $oldStatus = $campaign->status;
            $campaign->update([
                'status' => 'paused',
            ]);

            if ($campaign->customer) {
                $campaign->customer->notify(
                    new CampaignStatusUpdatedNotification($campaign, $oldStatus, 'paused')
                );
            }
        });

        return response()->json([
            'data' => $campaign->fresh(),
            'message' => 'Campaign has been paused. Jobs will stop processing automatically.',
        ]);
    }

    public function resume(Request $request, Campaign $campaign)
    {
        $campaign = $this->authorizeOwnership($request, $campaign);

        if (!$campaign->canResume()) {
            return response()->json([
                'message' => 'Campaign cannot be resumed. Only paused campaigns can be resumed.',
            ], 422);
        }

        $campaign->syncStats();

        $pendingRecipients = $campaign->recipients()
            ->whereIn('status', ['pending', 'failed'])
            ->pluck('id')
            ->chunk(50);

        if ($pendingRecipients->isEmpty()) {
            $totalRecipients = $campaign->recipients()->count();
            if ($totalRecipients === 0) {
                return response()->json([
                    'message' => 'No recipients found for this campaign.',
                ], 422);
            }

            return response()->json([
                'message' => 'No pending or failed recipients to resume sending. All recipients have been processed.',
            ], 422);
        }

        $campaign->recipients()
            ->where('status', 'failed')
            ->update([
                'status' => 'pending',
                'failed_at' => null,
                'failure_reason' => null,
            ]);

        DB::transaction(function () use ($campaign) {
            $oldStatus = $campaign->status;
            $campaign->update([
                'status' => 'running',
            ]);

            if ($campaign->customer) {
                $campaign->customer->notify(
                    new CampaignStatusUpdatedNotification($campaign, $oldStatus, 'running')
                );
            }
        });

        foreach ($pendingRecipients as $chunk) {
            SendCampaignChunkJob::dispatch($campaign, $chunk->toArray())
                ->onQueue('campaigns');
        }

        return response()->json([
            'data' => $campaign->fresh(),
            'message' => 'Campaign has been resumed. Remaining emails will be sent.',
        ]);
    }

    public function rerun(Request $request, Campaign $campaign)
    {
        $campaign = $this->authorizeOwnership($request, $campaign);

        if (!($campaign->isFailed() || $campaign->isCompleted())) {
            return response()->json([
                'message' => 'Campaign cannot be rerun. Only failed or completed campaigns can be rerun.',
            ], 422);
        }

        DB::transaction(function () use ($campaign) {
            $oldStatus = $campaign->status;
            $campaign->update([
                'status' => 'draft',
                'failure_reason' => null,
                'started_at' => null,
                'finished_at' => null,
            ]);

            if ($campaign->customer) {
                $campaign->customer->notify(
                    new CampaignStatusUpdatedNotification($campaign, $oldStatus, 'draft')
                );
            }
        });

        return response()->json([
            'data' => $campaign->fresh(),
            'message' => 'Campaign has been reset. You can now start it again.',
        ]);
    }

    public function stats(Request $request, Campaign $campaign)
    {
        $campaign = $this->authorizeOwnership($request, $campaign);

        $campaign->syncStats();

        $delivered = max(0, (int) $campaign->sent_count - (int) $campaign->bounced_count);
        $openRate = $delivered > 0 ? round(((int) $campaign->opened_count / $delivered) * 100, 2) : 0;
        $clickRate = $delivered > 0 ? round(((int) $campaign->clicked_count / $delivered) * 100, 2) : 0;
        $bounceRate = (int) $campaign->sent_count > 0 ? round(((int) $campaign->bounced_count / (int) $campaign->sent_count) * 100, 2) : 0;
        $failureRate = (int) $campaign->sent_count > 0 ? round(((int) $campaign->failed_count / (int) $campaign->sent_count) * 100, 2) : 0;
        $deliveryRate = (int) $campaign->total_recipients > 0
            ? round(($delivered / (int) $campaign->total_recipients) * 100, 2)
            : 0;

        $recipientStatuses = $campaign->recipients()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $topLinks = $campaign->logs()
            ->where('event', 'clicked')
            ->whereNotNull('url')
            ->selectRaw('url, COUNT(*) as clicks')
            ->groupBy('url')
            ->orderByDesc('clicks')
            ->limit(10)
            ->get();

        $errorBreakdown = $campaign->recipients()
            ->where('status', 'failed')
            ->whereNotNull('failure_reason')
            ->selectRaw('failure_reason, COUNT(*) as count')
            ->groupBy('failure_reason')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $sendingSpeed = 0;
        if ($campaign->started_at && (int) $campaign->sent_count > 0) {
            $secondsElapsed = max(1, now()->diffInSeconds($campaign->started_at));
            $sendingSpeed = round(((int) $campaign->sent_count) / $secondsElapsed, 2);
        }

        return response()->json([
            'data' => [
                'total_recipients' => (int) ($campaign->total_recipients ?? 0),
                'sent_count' => (int) ($campaign->sent_count ?? 0),
                'delivered' => $delivered,
                'pending_count' => (int) ($recipientStatuses['pending'] ?? 0),
                'opened_count' => (int) ($campaign->opened_count ?? 0),
                'clicked_count' => (int) ($campaign->clicked_count ?? 0),
                'replied_count' => (int) ($campaign->replied_count ?? 0),
                'bounced_count' => (int) ($campaign->bounced_count ?? 0),
                'failed_count' => (int) ($campaign->failed_count ?? 0),
                'unsubscribed_count' => (int) ($campaign->unsubscribed_count ?? 0),
                'complained_count' => (int) ($campaign->complained_count ?? 0),
                'open_rate' => $openRate,
                'click_rate' => $clickRate,
                'bounce_rate' => $bounceRate,
                'failure_rate' => $failureRate,
                'delivery_rate' => $deliveryRate,
                'sending_speed' => $sendingSpeed,
                'recipient_statuses' => $recipientStatuses,
                'top_links' => $topLinks,
                'error_breakdown' => $errorBreakdown,
            ],
        ]);
    }

    public function recipients(Request $request, Campaign $campaign)
    {
        $campaign = $this->authorizeOwnership($request, $campaign);

        $query = CampaignRecipient::query()
            ->where('campaign_id', $campaign->id);

        $status = trim((string) $request->query('status', ''));
        if ($status !== '') {
            $query->where('status', $status);
        }

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $recipients = $query->latest('created_at')->paginate(50);

        return response()->json([
            'data' => $recipients->items(),
            'meta' => [
                'current_page' => $recipients->currentPage(),
                'per_page' => $recipients->perPage(),
                'total' => $recipients->total(),
                'last_page' => $recipients->lastPage(),
            ],
        ]);
    }
}
