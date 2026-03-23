<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomerStoreRequest;
use App\Http\Requests\Admin\CustomerUpdateRequest;
use App\Models\Customer;
use App\Models\DeliveryServer;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function __construct(
        protected CustomerService $customerService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status', 'customer_group_id']);
        $customers = $this->customerService->getPaginated($filters);
        $customerGroups = $this->customerService->getCustomerGroupsForSelect();

        return view('admin.customers.index', compact('customers', 'customerGroups', 'filters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customerGroups = $this->customerService->getCustomerGroupsForSelect();
        $timezones = $this->customerService->getTimezones();
        $languages = $this->customerService->getLanguages();
        $plans = $this->customerService->getPlans();
        $deliveryServers = DeliveryServer::query()
            ->with('customer')
            ->orderBy('name')
            ->get();
        $allocatedDeliveryServerIds = [];
        
        return view('admin.customers.create', compact('customerGroups', 'timezones', 'languages', 'plans', 'deliveryServers', 'allocatedDeliveryServerIds'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CustomerStoreRequest $request)
    {
        $customer = $this->customerService->create($request->validated());

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        $customer->load('customerGroups');
        return view('admin.customers.show', compact('customer'));
    }

    public function updateEmailVerification(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'verified' => ['required', Rule::in(['0', '1', 0, 1, true, false])],
        ]);

        $verified = filter_var($validated['verified'], FILTER_VALIDATE_BOOLEAN);

        if ($verified) {
            $customer->forceFill(['email_verified_at' => now()])->save();
        } else {
            $customer->forceFill(['email_verified_at' => null])->save();
        }

        return back()->with('success', $verified ? 'Customer email marked as verified.' : 'Customer email marked as unverified.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        $customer->load('customerGroups');
        $customerGroups = $this->customerService->getCustomerGroupsForSelect();
        $deliveryServers = DeliveryServer::query()
            ->with('customer')
            ->orderBy('name')
            ->get();
        $allocatedDeliveryServerIds = $customer->allocatedDeliveryServers()
            ->pluck('delivery_servers.id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        return view('admin.customers.edit', compact('customer', 'customerGroups', 'deliveryServers', 'allocatedDeliveryServerIds'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CustomerUpdateRequest $request, Customer $customer)
    {
        $this->customerService->update($customer, $request->validated());

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $this->customerService->delete($customer);

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}

