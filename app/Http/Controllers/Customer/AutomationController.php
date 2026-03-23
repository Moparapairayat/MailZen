<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Automation;
use App\Models\AutomationRun;
use App\Models\Campaign;
use App\Models\DeliveryServer;
use App\Models\EmailList;
use App\Models\Template;
use App\Services\DeliveryServerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AutomationController extends Controller
{
    protected function authorizeOwnership(Automation $automation): Automation
    {
        $customerId = auth('customer')->id();

        if (!$customerId || (int) $automation->customer_id !== (int) $customerId) {
            abort(404);
        }

        return $automation;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status']);

        $query = Automation::query()
            ->where('customer_id', auth('customer')->id());

        if (isset($filters['search']) && is_string($filters['search']) && trim($filters['search']) !== '') {
            $search = trim($filters['search']);
            $query->where('name', 'like', "%{$search}%");
        }

        if (isset($filters['status']) && is_string($filters['status']) && trim($filters['status']) !== '') {
            $query->where('status', trim($filters['status']));
        }

        $query
            ->withCount([
                'runs as runs_total',
                'runs as runs_active' => function ($q) {
                    $q->where('status', 'active');
                },
                'runs as runs_completed' => function ($q) {
                    $q->where('status', 'completed');
                },
                'runs as runs_stopped' => function ($q) {
                    $q->where('status', 'stopped');
                },
            ])
            ->addSelect([
                'last_triggered_at' => AutomationRun::query()
                    ->selectRaw('MAX(triggered_at)')
                    ->whereColumn('automation_id', 'automations.id'),
                'next_scheduled_for' => AutomationRun::query()
                    ->selectRaw('MIN(next_scheduled_for)')
                    ->whereColumn('automation_id', 'automations.id')
                    ->where('status', 'active')
                    ->whereNotNull('next_scheduled_for'),
            ]);

        $automations = $query->latest()->paginate(15);

        return view('customer.automations.index', compact('automations', 'filters'));
    }

    public function create()
    {
        return view('customer.automations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $customer = auth('customer')->user();

        $automation = Automation::create([
            'customer_id' => $customer->id,
            'name' => $validated['name'],
            'status' => 'draft',
            'graph' => [
                'nodes' => [
                    [
                        'id' => 'trigger_1',
                        'type' => 'trigger',
                        'label' => 'Trigger',
                        'x' => 40,
                        'y' => 40,
                        'settings' => [
                            'list_id' => null,
                            'trigger' => '',
                        ],
                    ],
                ],
                'edges' => [],
            ],
        ]);

        return redirect()
            ->route('customer.automations.edit', $automation)
            ->with('success', 'Automation created successfully.');
    }

    public function show(Automation $automation)
    {
        $this->authorizeOwnership($automation);

        return redirect()->route('customer.automations.edit', $automation);
    }

    public function edit(Automation $automation)
    {
        $this->authorizeOwnership($automation);

        $customer = auth('customer')->user();

        $emailLists = EmailList::query()
            ->where('customer_id', $customer->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $mustAddDelivery = (bool) $customer->groupSetting('servers.permissions.must_add_delivery_server', false);
        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);

        $selectableDeliveryServerIds = app(DeliveryServerService::class)
            ->getSelectableDeliveryServerIdsForCustomer($customer, $mustAddDelivery, $canUseSystem);

        $deliveryServers = app(DeliveryServerService::class)->getSelectableDeliveryServersForCustomer(
            $customer,
            $mustAddDelivery,
            $canUseSystem
        );

        $templates = Template::query()
            ->where(function ($q) use ($customer) {
                $q->where('customer_id', $customer->id)
                    ->orWhere(function ($subQ) {
                        $subQ->where('is_public', true)
                            ->where('is_system', false);
                    });
            })
            ->whereIn('type', ['email', 'autoresponder', 'campaign'])
            ->orderBy('name')
            ->get();

        $campaigns = Campaign::query()
            ->where('customer_id', $customer->id)
            ->orderBy('name')
            ->get();

        return view('customer.automations.edit', compact('automation', 'emailLists', 'templates', 'deliveryServers', 'campaigns'));
    }

    protected function validateGraph(array $graph): array
    {
        $nodes = isset($graph['nodes']) && is_array($graph['nodes']) ? $graph['nodes'] : [];
        $edges = isset($graph['edges']) && is_array($graph['edges']) ? $graph['edges'] : [];

        if (empty($nodes)) {
            return ['graph_json' => 'Automation must contain at least one node.'];
        }

        $nodeIds = [];
        foreach ($nodes as $n) {
            if (!is_array($n)) {
                return ['graph_json' => 'Automation nodes are invalid.'];
            }
            $id = isset($n['id']) ? (string) $n['id'] : '';
            if ($id === '') {
                return ['graph_json' => 'Automation node is missing an id.'];
            }
            $nodeIds[$id] = true;
        }

        if (!isset($nodeIds['trigger_1'])) {
            return ['graph_json' => 'Automation must include a trigger node.'];
        }

        foreach ($edges as $e) {
            if (!is_array($e)) {
                return ['graph_json' => 'Automation edges are invalid.'];
            }
            $from = isset($e['from']) ? (string) $e['from'] : '';
            $to = isset($e['to']) ? (string) $e['to'] : '';
            if ($from === '' || $to === '' || !isset($nodeIds[$from]) || !isset($nodeIds[$to])) {
                return ['graph_json' => 'Automation contains an invalid connection.'];
            }
        }

        $customer = auth('customer')->user();

        $allowedTriggers = [
            'subscriber_added',
            'subscriber_confirmed',
            'subscriber_unsubscribed',
            'webhook_received',
            'wp_user_registered',
            'wp_user_updated',
            'woo_customer_created',
            'woo_order_created',
            'woo_order_paid',
            'woo_order_completed',
            'woo_order_refunded',
            'woo_order_cancelled',
            'woo_abandoned_checkout',
            'campaign_opened',
            'campaign_clicked',
            'campaign_replied',
            'campaign_not_opened',
            'campaign_not_replied',
            'campaign_opened_not_clicked',
        ];
        $allowedNodeTypes = [
            'trigger',
            'email',
            'delay',
            'webhook',
            'condition',
            'run_campaign',
            'move_subscribers',
            'copy_subscribers',
        ];
        $allowedDelayUnits = ['minutes', 'hours', 'days', 'weeks'];
        $allowedWebhookMethods = ['GET', 'POST', 'PUT'];

        $mustAddDelivery = (bool) $customer->groupSetting('servers.permissions.must_add_delivery_server', false);
        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);

        foreach ($nodes as $node) {
            $type = isset($node['type']) ? (string) $node['type'] : '';
            $settings = (isset($node['settings']) && is_array($node['settings'])) ? $node['settings'] : [];
            $nodeLabel = isset($node['label']) ? (string) $node['label'] : 'Node';

            if ($type === '' || !in_array($type, $allowedNodeTypes, true)) {
                return ['graph_json' => $nodeLabel . ': Node type is invalid.'];
            }

            if ($type === 'trigger') {
                $trigger = isset($settings['trigger']) ? (string) $settings['trigger'] : '';
                $listId = isset($settings['list_id']) ? (int) $settings['list_id'] : 0;
                $campaignId = isset($settings['campaign_id']) ? (int) $settings['campaign_id'] : 0;
                $windowValue = isset($settings['window_value']) ? (int) $settings['window_value'] : 0;
                $windowUnit = isset($settings['window_unit']) ? (string) $settings['window_unit'] : '';

                if ($trigger === '' || !in_array($trigger, $allowedTriggers, true)) {
                    return ['graph_json' => $nodeLabel . ': Trigger event is required.'];
                }

                if (str_starts_with($trigger, 'subscriber_')) {
                    if ($listId <= 0 || !EmailList::query()->where('id', $listId)->where('customer_id', $customer->id)->exists()) {
                        return ['graph_json' => $nodeLabel . ': Email list is required.'];
                    }
                }

                if (str_starts_with($trigger, 'wp_') || str_starts_with($trigger, 'woo_')) {
                    if ($listId > 0 && !EmailList::query()->where('id', $listId)->where('customer_id', $customer->id)->exists()) {
                        return ['graph_json' => $nodeLabel . ': Email list is invalid.'];
                    }
                }

                if ($trigger === 'webhook_received') {
                    $webhookToken = isset($settings['webhook_token']) ? trim((string) $settings['webhook_token']) : '';
                    if ($webhookToken === '') {
                        return ['graph_json' => $nodeLabel . ': Webhook token is required.'];
                    }

                    if ($listId > 0 && !EmailList::query()->where('id', $listId)->where('customer_id', $customer->id)->exists()) {
                        return ['graph_json' => $nodeLabel . ': Email list is invalid.'];
                    }
                }

                if (str_starts_with($trigger, 'campaign_')) {
                    if ($campaignId <= 0 || !Campaign::query()->where('id', $campaignId)->where('customer_id', $customer->id)->exists()) {
                        return ['graph_json' => $nodeLabel . ': Campaign is required.'];
                    }

                    $negativeTriggers = ['campaign_not_opened', 'campaign_not_replied', 'campaign_opened_not_clicked'];
                    if (in_array($trigger, $negativeTriggers, true)) {
                        if ($windowValue <= 0) {
                            return ['graph_json' => $nodeLabel . ': Time window is required.'];
                        }
                        if ($windowUnit === '' || !in_array($windowUnit, $allowedDelayUnits, true)) {
                            return ['graph_json' => $nodeLabel . ': Time window unit is invalid.'];
                        }
                    }
                }
            }

            if ($type === 'delay') {
                $value = isset($settings['delay_value']) ? (int) $settings['delay_value'] : null;
                $unit = isset($settings['delay_unit']) ? (string) $settings['delay_unit'] : '';

                if ($value === null || $value < 0) {
                    return ['graph_json' => $nodeLabel . ': Delay value is required.'];
                }
                if ($unit === '' || !in_array($unit, $allowedDelayUnits, true)) {
                    return ['graph_json' => $nodeLabel . ': Delay unit is required.'];
                }
            }

            if ($type === 'email') {
                $subject = isset($settings['subject']) ? trim((string) $settings['subject']) : '';
                $templateId = isset($settings['template_id']) ? (int) $settings['template_id'] : 0;
                $deliveryServerId = isset($settings['delivery_server_id']) ? (int) $settings['delivery_server_id'] : 0;
                $fromEmail = isset($settings['from_email']) ? trim((string) $settings['from_email']) : '';
                $replyTo = isset($settings['reply_to']) ? trim((string) $settings['reply_to']) : '';

                if ($subject === '') {
                    return ['graph_json' => $nodeLabel . ': Subject is required.'];
                }

                if ($templateId <= 0 || !Template::query()->where('id', $templateId)->exists()) {
                    return ['graph_json' => $nodeLabel . ': Template is required.'];
                }

                if (!Template::query()
                    ->where('id', $templateId)
                    ->where(function ($q) use ($customer) {
                        $q->where('customer_id', $customer->id)
                            ->orWhere(function ($subQ) {
                                $subQ->where('is_public', true)
                                    ->where('is_system', false);
                            });
                    })
                    ->exists()) {
                    return ['graph_json' => $nodeLabel . ': Template is invalid.'];
                }

                if ($mustAddDelivery && $deliveryServerId <= 0) {
                    return ['graph_json' => $nodeLabel . ': Delivery server is required.'];
                }

                if ($deliveryServerId > 0) {
                    if (!in_array($deliveryServerId, $selectableDeliveryServerIds, true)) {
                        return ['graph_json' => $nodeLabel . ': Delivery server is invalid.'];
                    }
                }

                if ($fromEmail !== '' && !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
                    return ['graph_json' => $nodeLabel . ': From email is invalid.'];
                }

                if ($replyTo !== '' && !filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
                    return ['graph_json' => $nodeLabel . ': Reply-to email is invalid.'];
                }
            }

            if ($type === 'run_campaign') {
                $campaignId = isset($settings['campaign_id']) ? (int) $settings['campaign_id'] : 0;
                if ($campaignId <= 0 || !Campaign::query()->where('id', $campaignId)->where('customer_id', $customer->id)->exists()) {
                    return ['graph_json' => $nodeLabel . ': Campaign is required.'];
                }
            }

            if ($type === 'move_subscribers' || $type === 'copy_subscribers') {
                $targetListId = isset($settings['target_list_id']) ? (int) $settings['target_list_id'] : 0;
                if ($targetListId <= 0 || !EmailList::query()->where('id', $targetListId)->where('customer_id', $customer->id)->exists()) {
                    return ['graph_json' => $nodeLabel . ': Target email list is required.'];
                }
            }

            if ($type === 'webhook') {
                $url = isset($settings['url']) ? trim((string) $settings['url']) : '';
                $method = isset($settings['method']) ? strtoupper(trim((string) $settings['method'])) : '';

                if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
                    return ['graph_json' => $nodeLabel . ': Webhook URL is required.'];
                }

                if ($method === '' || !in_array($method, $allowedWebhookMethods, true)) {
                    return ['graph_json' => $nodeLabel . ': Webhook method is invalid.'];
                }
            }

            if ($type === 'condition') {
                $field = isset($settings['field']) ? trim((string) $settings['field']) : '';
                $operator = isset($settings['operator']) ? trim((string) $settings['operator']) : '';
                $value = isset($settings['value']) ? trim((string) $settings['value']) : '';

                if ($field === '' || $operator === '' || $value === '') {
                    return ['graph_json' => $nodeLabel . ': Condition settings are required.'];
                }
            }
        }

        return [];
    }

    protected function validateGraphDraftStructure(array $graph): array
    {
        $nodes = isset($graph['nodes']) && is_array($graph['nodes']) ? $graph['nodes'] : [];
        $edges = isset($graph['edges']) && is_array($graph['edges']) ? $graph['edges'] : [];

        if (empty($nodes)) {
            return ['graph_json' => 'Automation must contain at least one node.'];
        }

        $allowedNodeTypes = [
            'trigger',
            'email',
            'delay',
            'webhook',
            'condition',
            'run_campaign',
            'move_subscribers',
            'copy_subscribers',
        ];

        $nodeIds = [];
        foreach ($nodes as $n) {
            if (!is_array($n)) {
                return ['graph_json' => 'Automation nodes are invalid.'];
            }
            $id = isset($n['id']) ? (string) $n['id'] : '';
            if ($id === '') {
                return ['graph_json' => 'Automation node is missing an id.'];
            }
            $type = isset($n['type']) ? (string) $n['type'] : '';
            $nodeLabel = isset($n['label']) ? (string) $n['label'] : 'Node';
            if ($type === '' || !in_array($type, $allowedNodeTypes, true)) {
                return ['graph_json' => $nodeLabel . ': Node type is invalid.'];
            }
            $nodeIds[$id] = true;
        }

        if (!isset($nodeIds['trigger_1'])) {
            return ['graph_json' => 'Automation must include a trigger node.'];
        }

        foreach ($edges as $e) {
            if (!is_array($e)) {
                return ['graph_json' => 'Automation edges are invalid.'];
            }
            $from = isset($e['from']) ? (string) $e['from'] : '';
            $to = isset($e['to']) ? (string) $e['to'] : '';
            if ($from === '' || $to === '' || !isset($nodeIds[$from]) || !isset($nodeIds[$to])) {
                return ['graph_json' => 'Automation contains an invalid connection.'];
            }
        }

        return [];
    }

    public function update(Request $request, Automation $automation)
    {
        $this->authorizeOwnership($automation);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'in:draft,active,inactive'],
            'graph_json' => ['nullable', 'string'],
        ]);

        $graph = null;
        $warning = null;
        $nextStatus = $validated['status'] ?? $automation->status;

        if (isset($validated['graph_json']) && is_string($validated['graph_json']) && trim($validated['graph_json']) !== '') {
            $decoded = json_decode($validated['graph_json'], true);
            if (!is_array($decoded)) {
                return back()->withErrors(['graph_json' => 'Flow data is invalid JSON.'])->withInput();
            }

            $structureErrors = $this->validateGraphDraftStructure($decoded);
            if (!empty($structureErrors)) {
                return back()->withErrors($structureErrors)->withInput();
            }

            $activationErrors = $this->validateGraph($decoded);
            if (!empty($activationErrors)) {
                $message = $activationErrors['graph_json'] ?? reset($activationErrors);
                $reason = is_string($message) ? $message : 'Missing required settings.';

                if ($nextStatus === 'active') {
                    $warning = 'Automation saved as draft (not active): ' . $reason;
                    $nextStatus = 'draft';
                } else {
                    $warning = 'Automation saved, but cannot be activated yet: ' . $reason;
                }
            }

            $graph = $decoded;
        }

        DB::transaction(function () use ($automation, $validated, $graph, $nextStatus) {
            $automation->forceFill([
                'name' => $validated['name'],
                'status' => $nextStatus,
            ]);

            if ($graph !== null) {
                $automation->forceFill(['graph' => $graph]);
            }

            $automation->save();
        });

        $redirect = redirect()
            ->route('customer.automations.edit', $automation)
            ->with('success', 'Automation saved successfully.');

        if (is_string($warning) && $warning !== '') {
            $redirect->with('warning', $warning);
        }

        return $redirect;
    }

    public function destroy(Automation $automation)
    {
        $this->authorizeOwnership($automation);

        $automation->delete();

        return redirect()
            ->route('customer.automations.index')
            ->with('success', 'Automation deleted successfully.');
    }
}
