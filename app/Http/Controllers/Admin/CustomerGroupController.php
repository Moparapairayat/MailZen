<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomerGroupStoreRequest;
use App\Http\Requests\Admin\CustomerGroupUpdateRequest;
use App\Models\CustomerGroup;
use App\Models\DeliveryServer;
use App\Services\CustomerGroupService;
use Illuminate\Http\Request;

class CustomerGroupController extends Controller
{
    public function __construct(
        protected CustomerGroupService $customerGroupService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search']);
        $customerGroups = $this->customerGroupService->getPaginated($filters);

        return view('admin.customer-groups.index', compact('customerGroups', 'filters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $defaultSettings = $this->customerGroupService->getDefaultSettings();
        $allGroups = CustomerGroup::orderBy('name')->get();
        $deliveryServers = DeliveryServer::query()
            ->with('customer')
            ->orderBy('name')
            ->get();
        $allocatedDeliveryServerIds = [];
        
        return view('admin.customer-groups.create_v2', compact('defaultSettings', 'allGroups', 'deliveryServers', 'allocatedDeliveryServerIds'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CustomerGroupStoreRequest $request)
    {
        $customerGroup = $this->customerGroupService->create($request->validated());

        return redirect()
            ->route('admin.customer-groups.index')
            ->with('success', 'Customer group created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CustomerGroup $customerGroup)
    {
        $customerGroup->load('customers');
        $settings = $this->customerGroupService->getEffectiveSettings($customerGroup);
        
        return view('admin.customer-groups.show', compact('customerGroup', 'settings'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CustomerGroup $customerGroup)
    {
        $settings = $this->customerGroupService->getEffectiveSettings($customerGroup);
        $allGroups = CustomerGroup::where('id', '!=', $customerGroup->id)->orderBy('name')->get();
        $deliveryServers = DeliveryServer::query()
            ->with('customer')
            ->orderBy('name')
            ->get();
        $allocatedDeliveryServerIds = $customerGroup->allocatedDeliveryServers()
            ->pluck('delivery_servers.id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
        
        return view('admin.customer-groups.edit', compact('customerGroup', 'settings', 'allGroups', 'deliveryServers', 'allocatedDeliveryServerIds'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CustomerGroupUpdateRequest $request, CustomerGroup $customerGroup)
    {
        $this->customerGroupService->update($customerGroup, $request->validated());

        return redirect()
            ->route('admin.customer-groups.index')
            ->with('success', 'Customer group updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomerGroup $customerGroup)
    {
        try {
            $this->customerGroupService->delete($customerGroup);

            return redirect()
                ->route('admin.customer-groups.index')
                ->with('success', 'Customer group deleted successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.customer-groups.index')
                ->with('error', $e->getMessage());
        }
    }
}

