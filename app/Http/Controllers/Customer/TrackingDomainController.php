<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\TrackingDomain;
use App\Services\TrackingDomainService;
use Illuminate\Http\Request;

class TrackingDomainController extends Controller
{
    public function __construct(
        protected TrackingDomainService $trackingDomainService
    ) {
        $this->middleware('customer.access:domains.tracking_domains.permissions.can_access_tracking_domains')->only([
            'index',
            'show',
        ]);
        $this->middleware('customer.access:domains.tracking_domains.permissions.can_create_tracking_domains')->only([
            'create',
            'store',
        ]);
        $this->middleware('customer.access:domains.tracking_domains.permissions.can_edit_tracking_domains')->only([
            'edit',
            'update',
            'verify',
            'markVerified',
        ]);
        $this->middleware('customer.access:domains.tracking_domains.permissions.can_delete_tracking_domains')->only([
            'destroy',
        ]);
    }

    protected function authorizeManage(TrackingDomain $trackingDomain): TrackingDomain
    {
        $customerId = auth('customer')->id();

        if (!$customerId || (int) $trackingDomain->customer_id !== (int) $customerId) {
            abort(404);
        }

        return $trackingDomain;
    }

    protected function authorizeView(TrackingDomain $trackingDomain): TrackingDomain
    {
        $customer = auth('customer')->user();
        if (!$customer) {
            abort(404);
        }

        if ((int) $trackingDomain->customer_id === (int) $customer->id) {
            return $trackingDomain;
        }

        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);
        $mustAddOwn = (bool) $customer->groupSetting('domains.tracking_domains.must_add', false);

        if (!$mustAddOwn && $canUseSystem && $trackingDomain->customer_id === null) {
            return $trackingDomain;
        }

        abort(404);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status']);
        $trackingDomains = $this->trackingDomainService->getPaginated(auth('customer')->user(), $filters);

        return view('customer.tracking-domains.index', compact('trackingDomains', 'filters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('customer.tracking-domains.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $customer = auth('customer')->user();
        $customer->enforceGroupLimit('domains.tracking_domains.max_tracking_domains', $customer->trackingDomains()->count(), 'Tracking domain limit reached.');

        $validated = $request->validate([
            'domain' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?)*$/'],
            'notes' => ['nullable', 'string'],
        ]);

        $trackingDomain = $this->trackingDomainService->create($customer, $validated);

        return redirect()
            ->route('customer.tracking-domains.show', $trackingDomain)
            ->with('success', 'Tracking domain created successfully. Please verify it by adding the DNS records.');
    }

    /**
     * Display the specified resource.
     */
    public function show(TrackingDomain $trackingDomain)
    {
        $this->authorizeView($trackingDomain);
        return view('customer.tracking-domains.show', compact('trackingDomain'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TrackingDomain $trackingDomain)
    {
        $this->authorizeManage($trackingDomain);
        return view('customer.tracking-domains.edit', compact('trackingDomain'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TrackingDomain $trackingDomain)
    {
        $this->authorizeManage($trackingDomain);
        $validated = $request->validate([
            'notes' => ['nullable', 'string'],
        ]);

        $this->trackingDomainService->update($trackingDomain, $validated);

        return redirect()
            ->route('customer.tracking-domains.show', $trackingDomain)
            ->with('success', 'Tracking domain updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TrackingDomain $trackingDomain)
    {
        $this->authorizeManage($trackingDomain);
        $this->trackingDomainService->delete($trackingDomain);

        return redirect()
            ->route('customer.tracking-domains.index')
            ->with('success', 'Tracking domain deleted successfully.');
    }

    /**
     * Verify a tracking domain.
     */
    public function verify(TrackingDomain $trackingDomain)
    {
        $this->authorizeManage($trackingDomain);

        $results = $this->trackingDomainService->verify($trackingDomain);

        if (!empty($results['cname'])) {
            return redirect()
                ->route('customer.tracking-domains.show', $trackingDomain)
                ->with('success', 'Tracking domain verified successfully! CNAME record found in DNS.');
        }

        $errorMessage = 'Domain verification failed. ';
        if (!empty($results['errors'])) {
            $errorMessage .= implode(' ', $results['errors']);
        } else {
            $errorMessage .= 'CNAME record not found in DNS. Please add the CNAME record and try again.';
        }

        return redirect()
            ->route('customer.tracking-domains.show', $trackingDomain)
            ->with('error', $errorMessage)
            ->with('verification_results', $results);
    }

    public function markVerified(TrackingDomain $trackingDomain)
    {
        $this->authorizeManage($trackingDomain);

        $trackingDomain->update([
            'status' => 'verified',
            'verified_at' => now(),
        ]);

        return redirect()
            ->route('customer.tracking-domains.show', $trackingDomain)
            ->with('success', 'Tracking domain marked as verified.');
    }
}
