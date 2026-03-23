<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\DeliveryServer;
use App\Models\EmailList;
use App\Models\EmailWarmup;
use App\Services\DeliveryServerService;
use App\Services\EmailWarmupService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EmailWarmupController extends Controller
{
    public function __construct(
        protected EmailWarmupService $warmupService
    ) {
        $this->middleware('demo.prevent')->only(['store', 'update', 'destroy', 'start', 'pause']);
    }

    protected function authorizeManage(EmailWarmup $warmup): EmailWarmup
    {
        $customerId = auth('customer')->id();

        if (!$customerId || (int) $warmup->customer_id !== (int) $customerId) {
            abort(404);
        }

        return $warmup;
    }

    public function index(Request $request)
    {
        $customer = auth('customer')->user();
        $filters = $request->only(['search', 'status']);

        $warmups = $this->warmupService->getPaginated($customer->id, $filters);

        return view('customer.warmups.index', compact('warmups', 'filters'));
    }

    public function create()
    {
        $customer = auth('customer')->user();

        $mustAddDelivery = (bool) $customer->groupSetting('servers.permissions.must_add_delivery_server', false);
        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);

        $deliveryServers = app(DeliveryServerService::class)->getSelectableDeliveryServersForCustomer(
            $customer,
            $mustAddDelivery,
            $canUseSystem
        );

        $emailLists = EmailList::where('customer_id', $customer->id)
            ->orderBy('name')
            ->get();

        $defaultTemplates = $this->warmupService->getDefaultTemplates();

        return view('customer.warmups.create', compact('deliveryServers', 'emailLists', 'defaultTemplates'));
    }

    public function store(Request $request)
    {
        $customer = auth('customer')->user();

        $mustAddDelivery = (bool) $customer->groupSetting('servers.permissions.must_add_delivery_server', false);
        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);
        $selectableDeliveryServerIds = app(DeliveryServerService::class)
            ->getSelectableDeliveryServerIdsForCustomer($customer, $mustAddDelivery, $canUseSystem);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'delivery_server_id' => ['required', 'integer', \Illuminate\Validation\Rule::in($selectableDeliveryServerIds)],
            'email_list_id' => ['nullable', 'exists:email_lists,id'],
            'from_email' => ['required', 'email', 'max:255'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'starting_volume' => ['required', 'integer', 'min:1', 'max:100'],
            'max_volume' => ['required', 'integer', 'min:10', 'max:10000'],
            'daily_increase_rate' => ['required', 'numeric', 'min:1.05', 'max:2.0'],
            'total_days' => ['required', 'integer', 'min:7', 'max:90'],
            'send_time' => ['required', 'date_format:H:i'],
            'timezone' => ['required', 'string', 'max:100'],
            'seed_emails' => ['nullable', 'string'],
            'email_templates' => ['nullable', 'array'],
            'email_templates.*.subject' => ['required_with:email_templates', 'string', 'max:255'],
            'email_templates.*.body' => ['required_with:email_templates', 'string'],
        ]);

        $deliveryServer = app(DeliveryServerService::class)->resolveDeliveryServerForCustomer(
            $customer,
            (int) $validated['delivery_server_id'],
            $mustAddDelivery,
            $canUseSystem
        );

        if (!$deliveryServer) {
            throw ValidationException::withMessages([
                'delivery_server_id' => 'Invalid delivery server selected.',
            ]);
        }

        if (!empty($validated['email_list_id'])) {
            $emailList = EmailList::find($validated['email_list_id']);
            if ((int) $emailList->customer_id !== (int) $customer->id) {
                throw ValidationException::withMessages([
                    'email_list_id' => 'Invalid email list selected.',
                ]);
            }
        }

        $settings = [];
        if (!empty($validated['seed_emails'])) {
            $seedEmails = array_filter(
                array_map('trim', explode("\n", $validated['seed_emails'])),
                fn($email) => filter_var($email, FILTER_VALIDATE_EMAIL)
            );
            $settings['seed_emails'] = array_values($seedEmails);
        }
        $settings['auto_pause_on_high_bounce'] = true;
        $settings['bounce_threshold'] = 5;

        $warmup = $this->warmupService->create([
            'customer_id' => $customer->id,
            'delivery_server_id' => $validated['delivery_server_id'],
            'email_list_id' => $validated['email_list_id'] ?? null,
            'name' => $validated['name'],
            'from_email' => $validated['from_email'],
            'from_name' => $validated['from_name'] ?? null,
            'starting_volume' => $validated['starting_volume'],
            'max_volume' => $validated['max_volume'],
            'daily_increase_rate' => $validated['daily_increase_rate'],
            'total_days' => $validated['total_days'],
            'send_time' => $validated['send_time'] . ':00',
            'timezone' => $validated['timezone'],
            'email_templates' => $validated['email_templates'] ?? null,
            'settings' => $settings,
        ]);

        return redirect()
            ->route('customer.warmups.show', $warmup)
            ->with('success', 'Email warmup created successfully.');
    }

    public function show(EmailWarmup $warmup)
    {
        $this->authorizeManage($warmup);
        $warmup->load(['deliveryServer', 'emailList', 'logs' => function ($q) {
            $q->orderByDesc('day_number')->limit(30);
        }]);

        $stats = $this->warmupService->getStats($warmup);

        return view('customer.warmups.show', compact('warmup', 'stats'));
    }

    public function edit(EmailWarmup $warmup)
    {
        $this->authorizeManage($warmup);

        if ($warmup->isActive()) {
            return redirect()
                ->route('customer.warmups.show', $warmup)
                ->with('error', 'Cannot edit an active warmup. Please pause it first.');
        }

        $customer = auth('customer')->user();

        $mustAddDelivery = (bool) $customer->groupSetting('servers.permissions.must_add_delivery_server', false);
        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);

        $deliveryServers = app(DeliveryServerService::class)->getSelectableDeliveryServersForCustomer(
            $customer,
            $mustAddDelivery,
            $canUseSystem
        );

        $emailLists = EmailList::where('customer_id', $customer->id)
            ->orderBy('name')
            ->get();

        return view('customer.warmups.edit', compact('warmup', 'deliveryServers', 'emailLists'));
    }

    public function update(Request $request, EmailWarmup $warmup)
    {
        $this->authorizeManage($warmup);

        if ($warmup->isActive()) {
            return redirect()
                ->route('customer.warmups.show', $warmup)
                ->with('error', 'Cannot update an active warmup. Please pause it first.');
        }

        $customer = auth('customer')->user();

        $mustAddDelivery = (bool) $customer->groupSetting('servers.permissions.must_add_delivery_server', false);
        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);
        $selectableDeliveryServerIds = app(DeliveryServerService::class)
            ->getSelectableDeliveryServerIdsForCustomer($customer, $mustAddDelivery, $canUseSystem);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'delivery_server_id' => ['required', 'integer', \Illuminate\Validation\Rule::in($selectableDeliveryServerIds)],
            'email_list_id' => ['nullable', 'exists:email_lists,id'],
            'from_email' => ['required', 'email', 'max:255'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'starting_volume' => ['required', 'integer', 'min:1', 'max:100'],
            'max_volume' => ['required', 'integer', 'min:10', 'max:10000'],
            'daily_increase_rate' => ['required', 'numeric', 'min:1.05', 'max:2.0'],
            'total_days' => ['required', 'integer', 'min:7', 'max:90'],
            'send_time' => ['required', 'date_format:H:i'],
            'timezone' => ['required', 'string', 'max:100'],
            'seed_emails' => ['nullable', 'string'],
            'email_templates' => ['nullable', 'array'],
        ]);

        $deliveryServer = app(DeliveryServerService::class)->resolveDeliveryServerForCustomer(
            $customer,
            (int) $validated['delivery_server_id'],
            $mustAddDelivery,
            $canUseSystem
        );

        if (!$deliveryServer) {
            throw ValidationException::withMessages([
                'delivery_server_id' => 'Invalid delivery server selected.',
            ]);
        }

        $settings = $warmup->settings ?? [];
        if (!empty($validated['seed_emails'])) {
            $seedEmails = array_filter(
                array_map('trim', explode("\n", $validated['seed_emails'])),
                fn($email) => filter_var($email, FILTER_VALIDATE_EMAIL)
            );
            $settings['seed_emails'] = array_values($seedEmails);
        }

        $this->warmupService->update($warmup, [
            'delivery_server_id' => $validated['delivery_server_id'],
            'email_list_id' => $validated['email_list_id'] ?? null,
            'name' => $validated['name'],
            'from_email' => $validated['from_email'],
            'from_name' => $validated['from_name'] ?? null,
            'starting_volume' => $validated['starting_volume'],
            'max_volume' => $validated['max_volume'],
            'daily_increase_rate' => $validated['daily_increase_rate'],
            'total_days' => $validated['total_days'],
            'send_time' => $validated['send_time'] . ':00',
            'timezone' => $validated['timezone'],
            'email_templates' => $validated['email_templates'] ?? $warmup->email_templates,
            'settings' => $settings,
        ]);

        return redirect()
            ->route('customer.warmups.show', $warmup)
            ->with('success', 'Email warmup updated successfully.');
    }

    public function destroy(EmailWarmup $warmup)
    {
        $this->authorizeManage($warmup);

        if ($warmup->isActive()) {
            return redirect()
                ->route('customer.warmups.show', $warmup)
                ->with('error', 'Cannot delete an active warmup. Please pause it first.');
        }

        $this->warmupService->delete($warmup);

        return redirect()
            ->route('customer.warmups.index')
            ->with('success', 'Email warmup deleted successfully.');
    }

    public function start(EmailWarmup $warmup)
    {
        $this->authorizeManage($warmup);

        try {
            $this->warmupService->start($warmup);
            return redirect()
                ->route('customer.warmups.show', $warmup)
                ->with('success', 'Email warmup started successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->route('customer.warmups.show', $warmup)
                ->with('error', 'Failed to start warmup: ' . $e->getMessage());
        }
    }

    public function pause(EmailWarmup $warmup)
    {
        $this->authorizeManage($warmup);

        try {
            $this->warmupService->pause($warmup);
            return redirect()
                ->route('customer.warmups.show', $warmup)
                ->with('success', 'Email warmup paused successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->route('customer.warmups.show', $warmup)
                ->with('error', 'Failed to pause warmup: ' . $e->getMessage());
        }
    }

    public function stats(EmailWarmup $warmup)
    {
        $this->authorizeManage($warmup);

        $stats = $this->warmupService->getStats($warmup);

        return response()->json($stats);
    }
}
