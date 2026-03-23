<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Models\AutoResponder;
use App\Models\AutoResponderDelivery;
use App\Models\AutoResponderRun;
use App\Models\AutoResponderStep;
use App\Models\DeliveryServer;
use App\Models\EmailList;
use App\Models\ListSubscriber;
use App\Models\Template;
use App\Services\AutoResponderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AutoResponderController extends Controller
{
    public function __construct(
        protected AutoResponderService $autoResponderService
    ) {
    }

    protected function customer(Request $request)
    {
        return $request->user('sanctum');
    }

    protected function authorizeOwnership(Request $request, AutoResponder $autoResponder): AutoResponder
    {
        $customer = $this->customer($request);

        if (!$customer || (int) $autoResponder->customer_id !== (int) $customer->id) {
            abort(404);
        }

        return $autoResponder;
    }

    protected function ensureCanSelectList(Request $request, int $listId): void
    {
        $customer = $this->customer($request);

        $exists = EmailList::query()
            ->whereKey($listId)
            ->where('customer_id', $customer->id)
            ->exists();

        if (!$exists) {
            throw ValidationException::withMessages([
                'list_id' => 'Selected list is invalid.',
            ]);
        }
    }

    protected function ensureCanSelectTemplate(Request $request, ?int $templateId): void
    {
        if ($templateId === null) {
            return;
        }

        $customer = $this->customer($request);

        $template = Template::query()
            ->whereKey($templateId)
            ->where(function ($q) use ($customer) {
                $q->where('customer_id', $customer->id)
                    ->orWhere(function ($sys) {
                        $sys->where('is_public', true)
                            ->where('is_system', false);
                    });
            })
            ->whereIn('type', ['email', 'autoresponder'])
            ->first();

        if (!$template) {
            throw ValidationException::withMessages([
                'template_id' => 'Selected template is invalid.',
            ]);
        }
    }

    protected function ensureCanSelectDeliveryServer(Request $request, ?int $deliveryServerId): void
    {
        if ($deliveryServerId === null) {
            return;
        }

        $customer = $this->customer($request);

        $deliveryServer = DeliveryServer::query()
            ->whereKey($deliveryServerId)
            ->where('status', 'active')
            ->where('use_for', true)
            ->first();

        if (!$deliveryServer) {
            throw ValidationException::withMessages([
                'delivery_server_id' => 'Selected delivery server is invalid.',
            ]);
        }

        if ((int) $deliveryServer->customer_id === (int) $customer->id) {
            return;
        }

        $mustAddDelivery = (bool) $customer->groupSetting('servers.permissions.must_add_delivery_server', false);
        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);

        if (!$mustAddDelivery && $canUseSystem && $deliveryServer->customer_id === null) {
            return;
        }

        throw ValidationException::withMessages([
            'delivery_server_id' => 'Selected delivery server is not available for your account.',
        ]);
    }

    public function index(Request $request)
    {
        $customer = $this->customer($request);

        $filters = $request->only(['search', 'status', 'trigger']);
        $autoResponders = $this->autoResponderService->getPaginated($customer, $filters);

        return response()->json([
            'data' => $autoResponders->items(),
            'meta' => [
                'current_page' => $autoResponders->currentPage(),
                'per_page' => $autoResponders->perPage(),
                'total' => $autoResponders->total(),
                'last_page' => $autoResponders->lastPage(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $customer = $this->customer($request);

        $customer->enforceGroupLimit('autoresponders.max_autoresponders', $customer->autoResponders()->count(), 'Auto responder limit reached.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'list_id' => ['required', 'exists:email_lists,id'],
            'delivery_server_id' => ['nullable', 'exists:delivery_servers,id'],
            'template_id' => ['nullable', 'exists:templates,id'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'from_email' => ['nullable', 'email', 'max:255'],
            'reply_to' => ['nullable', 'email', 'max:255'],
            'trigger' => ['required', 'in:subscriber_added,subscriber_confirmed,subscriber_unsubscribed,mail_opened,mail_clicked'],
            'trigger_settings' => ['nullable', 'array'],
            'delay_value' => ['nullable', 'integer', 'min:0'],
            'delay_unit' => ['nullable', 'in:minutes,hours,days,weeks'],
            'status' => ['nullable', 'in:active,inactive,draft'],
            'html_content' => ['nullable', 'string'],
            'plain_text_content' => ['nullable', 'string'],
            'track_opens' => ['nullable', 'boolean'],
            'track_clicks' => ['nullable', 'boolean'],
            'template_data' => ['nullable', 'array'],
        ]);

        $this->ensureCanSelectList($request, (int) $validated['list_id']);
        $this->ensureCanSelectTemplate($request, $validated['template_id'] ?? null);
        $this->ensureCanSelectDeliveryServer($request, $validated['delivery_server_id'] ?? null);

        $autoResponder = $this->autoResponderService->create($customer, $validated);

        return response()->json([
            'data' => $autoResponder,
        ], 201);
    }

    public function show(Request $request, AutoResponder $autoResponder)
    {
        $autoResponder = $this->authorizeOwnership($request, $autoResponder);
        $autoResponder->load(['emailList', 'steps']);

        return response()->json([
            'data' => $autoResponder,
        ]);
    }

    public function update(Request $request, AutoResponder $autoResponder)
    {
        $autoResponder = $this->authorizeOwnership($request, $autoResponder);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'list_id' => ['required', 'exists:email_lists,id'],
            'delivery_server_id' => ['nullable', 'exists:delivery_servers,id'],
            'template_id' => ['nullable', 'exists:templates,id'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'from_email' => ['nullable', 'email', 'max:255'],
            'reply_to' => ['nullable', 'email', 'max:255'],
            'trigger' => ['required', 'in:subscriber_added,subscriber_confirmed,subscriber_unsubscribed,mail_opened,mail_clicked'],
            'trigger_settings' => ['nullable', 'array'],
            'delay_value' => ['nullable', 'integer', 'min:0'],
            'delay_unit' => ['nullable', 'in:minutes,hours,days,weeks'],
            'status' => ['nullable', 'in:active,inactive,draft'],
            'html_content' => ['nullable', 'string'],
            'plain_text_content' => ['nullable', 'string'],
            'track_opens' => ['nullable', 'boolean'],
            'track_clicks' => ['nullable', 'boolean'],
            'template_data' => ['nullable', 'array'],
        ]);

        $this->ensureCanSelectList($request, (int) $validated['list_id']);
        $this->ensureCanSelectTemplate($request, $validated['template_id'] ?? null);
        $this->ensureCanSelectDeliveryServer($request, $validated['delivery_server_id'] ?? null);

        $updated = $this->autoResponderService->update($autoResponder, $validated);

        return response()->json([
            'data' => $updated->load(['emailList', 'steps']),
        ]);
    }

    public function destroy(Request $request, AutoResponder $autoResponder)
    {
        $autoResponder = $this->authorizeOwnership($request, $autoResponder);

        $this->autoResponderService->delete($autoResponder);

        return response()->json(['success' => true]);
    }

    public function start(Request $request, AutoResponder $autoResponder)
    {
        $autoResponder = $this->authorizeOwnership($request, $autoResponder);

        $autoResponder->update(['status' => 'active']);

        return response()->json([
            'data' => $autoResponder->fresh(),
        ]);
    }

    public function pause(Request $request, AutoResponder $autoResponder)
    {
        $autoResponder = $this->authorizeOwnership($request, $autoResponder);

        DB::transaction(function () use ($autoResponder) {
            $autoResponder->update(['status' => 'inactive']);

            AutoResponderRun::query()
                ->where('auto_responder_id', $autoResponder->id)
                ->where('status', 'active')
                ->update([
                    'status' => 'stopped',
                    'stopped_at' => now(),
                    'stop_reason' => 'paused',
                    'next_scheduled_for' => null,
                    'locked_at' => null,
                ]);
        });

        return response()->json([
            'data' => $autoResponder->fresh(),
        ]);
    }

    public function resume(Request $request, AutoResponder $autoResponder)
    {
        $autoResponder = $this->authorizeOwnership($request, $autoResponder);

        $autoResponder->update(['status' => 'active']);

        return response()->json([
            'data' => $autoResponder->fresh(),
        ]);
    }

    public function rerun(Request $request, AutoResponder $autoResponder)
    {
        $autoResponder = $this->authorizeOwnership($request, $autoResponder);

        $validated = $request->validate([
            'subscriber_id' => ['required', 'exists:list_subscribers,id'],
        ]);

        $subscriber = ListSubscriber::query()
            ->whereKey((int) $validated['subscriber_id'])
            ->where('list_id', $autoResponder->list_id)
            ->first();

        if (!$subscriber) {
            throw ValidationException::withMessages([
                'subscriber_id' => 'Subscriber is not part of this auto responder list.',
            ]);
        }

        $step1 = AutoResponderStep::query()
            ->where('auto_responder_id', $autoResponder->id)
            ->where('step_order', 1)
            ->where('status', 'active')
            ->first();

        if (!$step1) {
            return response()->json([
                'message' => 'Auto responder does not have an active step 1.',
            ], 422);
        }

        DB::transaction(function () use ($autoResponder, $subscriber, $step1) {
            AutoResponderDelivery::query()
                ->where('auto_responder_id', $autoResponder->id)
                ->where('subscriber_id', $subscriber->id)
                ->delete();

            AutoResponderRun::query()
                ->where('auto_responder_id', $autoResponder->id)
                ->where('subscriber_id', $subscriber->id)
                ->delete();

            $now = now();
            $nextScheduled = $now;
            $value = (int) ($step1->delay_value ?? 0);
            if ($value > 0) {
                $nextScheduled = match ((string) ($step1->delay_unit ?? 'hours')) {
                    'minutes' => $now->copy()->addMinutes($value),
                    'hours' => $now->copy()->addHours($value),
                    'days' => $now->copy()->addDays($value),
                    'weeks' => $now->copy()->addWeeks($value),
                    default => $now,
                };
            }

            AutoResponderRun::query()->create([
                'auto_responder_id' => $autoResponder->id,
                'subscriber_id' => $subscriber->id,
                'list_id' => $subscriber->list_id,
                'status' => 'active',
                'triggered_at' => $now,
                'next_step_order' => 1,
                'next_scheduled_for' => $nextScheduled,
                'completed_at' => null,
                'stopped_at' => null,
                'stop_reason' => null,
                'locked_at' => null,
            ]);
        });

        return response()->json([
            'success' => true,
        ]);
    }

    public function stats(Request $request, AutoResponder $autoResponder)
    {
        $autoResponder = $this->authorizeOwnership($request, $autoResponder);

        $runsByStatus = AutoResponderRun::query()
            ->where('auto_responder_id', $autoResponder->id)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $deliveriesByStatus = AutoResponderDelivery::query()
            ->where('auto_responder_id', $autoResponder->id)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return response()->json([
            'data' => [
                'sent_count' => (int) ($autoResponder->sent_count ?? 0),
                'opened_count' => (int) ($autoResponder->opened_count ?? 0),
                'clicked_count' => (int) ($autoResponder->clicked_count ?? 0),
                'runs' => $runsByStatus,
                'deliveries' => $deliveriesByStatus,
            ],
        ]);
    }

    public function recipients(Request $request, AutoResponder $autoResponder)
    {
        $autoResponder = $this->authorizeOwnership($request, $autoResponder);

        $filters = $request->only(['search', 'status']);

        $deliveries = AutoResponderDelivery::query()
            ->where('auto_responder_id', $autoResponder->id)
            ->with(['subscriber', 'step'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->whereHas('subscriber', function ($sub) use ($search) {
                    $sub->where('email', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(15);

        return response()->json([
            'data' => $deliveries->items(),
            'meta' => [
                'current_page' => $deliveries->currentPage(),
                'per_page' => $deliveries->perPage(),
                'total' => $deliveries->total(),
                'last_page' => $deliveries->lastPage(),
            ],
        ]);
    }
}
