<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\AutoResponder;
use App\Models\Automation;
use App\Models\DeliveryServer;
use App\Models\EmailList;
use App\Models\Template;
use App\Services\AutoResponderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AutoResponderController extends Controller
{
    public function __construct(
        protected AutoResponderService $autoResponderService
    ) {}

    protected function authorizeOwnership(AutoResponder $autoResponder): AutoResponder
    {
        $customerId = auth('customer')->id();

        if (!$customerId || (int) $autoResponder->customer_id !== (int) $customerId) {
            abort(404);
        }

        return $autoResponder;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status', 'trigger']);
        $autoResponders = $this->autoResponderService->getPaginated(auth('customer')->user(), $filters);

        return view('customer.auto-responders.index', compact('autoResponders', 'filters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customer = auth('customer')->user();
        $emailLists = EmailList::where('customer_id', $customer->id)
            ->where('status', 'active')
            ->get();

        return view('customer.auto-responders.create', compact('emailLists'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $customer = auth('customer')->user();
        $customer->enforceGroupLimit('autoresponders.max_autoresponders', $customer->autoResponders()->count(), 'Auto responder limit reached.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'list_id' => ['required', 'exists:email_lists,id'],
            'trigger' => ['required', 'in:subscriber_added,subscriber_confirmed,subscriber_unsubscribed'],
        ]);

        $autoResponder = DB::transaction(function () use ($customer, $validated) {
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
                            'x' => 60,
                            'y' => 60,
                            'settings' => [
                                'list_id' => (int) $validated['list_id'],
                                'trigger' => $validated['trigger'],
                            ],
                        ],
                    ],
                    'edges' => [],
                ],
            ]);

            return AutoResponder::create([
                'customer_id' => $customer->id,
                'automation_id' => $automation->id,
                'list_id' => $validated['list_id'],
                'name' => $validated['name'],
                'subject' => '',
                'trigger' => $validated['trigger'],
                'status' => 'draft',
            ]);
        });

        return redirect()
            ->route('customer.auto-responders.edit', $autoResponder)
            ->with('success', 'Auto responder created. Now build your workflow.');
    }

    /**
     * Display the specified resource.
     */
    public function show(AutoResponder $autoResponder)
    {
        $this->authorizeOwnership($autoResponder);
        
        return redirect()->route('customer.auto-responders.edit', $autoResponder);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AutoResponder $autoResponder)
    {
        $this->authorizeOwnership($autoResponder);
        $autoResponder->load('automation');

        if (!$autoResponder->automation) {
            $customer = auth('customer')->user();
            $automation = Automation::create([
                'customer_id' => $customer->id,
                'name' => $autoResponder->name,
                'status' => $autoResponder->status === 'active' ? 'active' : 'draft',
                'graph' => $this->buildGraphFromAutoResponder($autoResponder),
            ]);

            $autoResponder->update(['automation_id' => $automation->id]);
            $autoResponder->load('automation');
        }

        return redirect()->route('customer.automations.edit', $autoResponder->automation);
    }

    /**
     * Build automation graph from existing auto responder steps.
     */
    protected function buildGraphFromAutoResponder(AutoResponder $autoResponder): array
    {
        $nodes = [
            [
                'id' => 'trigger_1',
                'type' => 'trigger',
                'label' => 'Trigger',
                'x' => 60,
                'y' => 60,
                'settings' => [
                    'list_id' => (int) $autoResponder->list_id,
                    'trigger' => $autoResponder->trigger ?? 'subscriber_confirmed',
                ],
            ],
        ];
        $edges = [];

        $steps = $autoResponder->steps()->orderBy('step_order')->get();
        $prevNodeId = 'trigger_1';
        $yPos = 230;

        foreach ($steps as $index => $step) {
            $delayValue = (int) ($step->delay_value ?? 0);
            $delayUnit = $step->delay_unit ?? 'hours';

            if ($delayValue > 0) {
                $delayNodeId = 'delay_' . ($index + 1);
                $nodes[] = [
                    'id' => $delayNodeId,
                    'type' => 'delay',
                    'label' => 'Delay',
                    'x' => 60,
                    'y' => $yPos,
                    'settings' => [
                        'delay_value' => $delayValue,
                        'delay_unit' => $delayUnit,
                    ],
                ];
                $edges[] = ['from' => $prevNodeId, 'to' => $delayNodeId];
                $prevNodeId = $delayNodeId;
                $yPos += 170;
            }

            $emailNodeId = 'email_' . ($index + 1);
            $nodes[] = [
                'id' => $emailNodeId,
                'type' => 'email',
                'label' => $step->name ?? ('Email ' . ($index + 1)),
                'x' => 60,
                'y' => $yPos,
                'settings' => [
                    'subject' => $step->subject ?? '',
                    'template_id' => $step->template_id,
                    'delivery_server_id' => $step->delivery_server_id,
                    'from_name' => $step->from_name ?? '',
                    'from_email' => $step->from_email ?? '',
                    'reply_to' => $step->reply_to ?? '',
                    'track_opens' => (bool) ($step->track_opens ?? true),
                    'track_clicks' => (bool) ($step->track_clicks ?? true),
                ],
            ];
            $edges[] = ['from' => $prevNodeId, 'to' => $emailNodeId];
            $prevNodeId = $emailNodeId;
            $yPos += 170;
        }

        return ['nodes' => $nodes, 'edges' => $edges];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AutoResponder $autoResponder)
    {
        $this->authorizeOwnership($autoResponder);

        return redirect()->route('customer.auto-responders.edit', $autoResponder);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AutoResponder $autoResponder)
    {
        $this->authorizeOwnership($autoResponder);

        DB::transaction(function () use ($autoResponder) {
            if ($autoResponder->automation_id) {
                Automation::where('id', $autoResponder->automation_id)->delete();
            }
            $autoResponder->delete();
        });

        return redirect()
            ->route('customer.auto-responders.index')
            ->with('success', 'Auto responder deleted successfully.');
    }
}
