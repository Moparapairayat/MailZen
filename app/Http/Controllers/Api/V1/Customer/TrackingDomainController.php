<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Models\TrackingDomain;
use App\Services\TrackingDomainService;
use Illuminate\Http\Request;

class TrackingDomainController extends Controller
{
    public function __construct(
        protected TrackingDomainService $trackingDomainService
    ) {
    }

    protected function customer(Request $request)
    {
        return $request->user('sanctum');
    }

    protected function authorizeManage(Request $request, TrackingDomain $trackingDomain): TrackingDomain
    {
        $customer = $this->customer($request);

        if (!$customer || (int) $trackingDomain->customer_id !== (int) $customer->id) {
            abort(404);
        }

        return $trackingDomain;
    }

    protected function authorizeView(Request $request, TrackingDomain $trackingDomain): TrackingDomain
    {
        $customer = $this->customer($request);
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

    protected function sanitizeTrackingDomain(TrackingDomain $trackingDomain): array
    {
        $trackingDomain->makeHidden(['verification_token']);
        return $trackingDomain->toArray();
    }

    public function index(Request $request)
    {
        $customer = $this->customer($request);
        $filters = $request->only(['search', 'status']);

        $trackingDomains = $this->trackingDomainService->getPaginated($customer, $filters);

        $items = array_map(function ($row) {
            $domain = $row instanceof TrackingDomain ? $row : null;
            if (!$domain) {
                return $row;
            }
            return $this->sanitizeTrackingDomain($domain);
        }, $trackingDomains->items());

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $trackingDomains->currentPage(),
                'per_page' => $trackingDomains->perPage(),
                'total' => $trackingDomains->total(),
                'last_page' => $trackingDomains->lastPage(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $customer = $this->customer($request);

        $customer->enforceGroupLimit('domains.tracking_domains.max_tracking_domains', $customer->trackingDomains()->count(), 'Tracking domain limit reached.');

        $validated = $request->validate([
            'domain' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?)*$/',
            ],
            'notes' => ['nullable', 'string'],
        ]);

        $trackingDomain = $this->trackingDomainService->create($customer, $validated);

        return response()->json([
            'data' => $this->sanitizeTrackingDomain($trackingDomain),
        ], 201);
    }

    public function show(Request $request, TrackingDomain $trackingDomain)
    {
        $trackingDomain = $this->authorizeView($request, $trackingDomain);

        return response()->json([
            'data' => $this->sanitizeTrackingDomain($trackingDomain),
        ]);
    }

    public function update(Request $request, TrackingDomain $trackingDomain)
    {
        $trackingDomain = $this->authorizeManage($request, $trackingDomain);

        $validated = $request->validate([
            'notes' => ['nullable', 'string'],
        ]);

        $updated = $this->trackingDomainService->update($trackingDomain, $validated);

        return response()->json([
            'data' => $this->sanitizeTrackingDomain($updated),
        ]);
    }

    public function destroy(Request $request, TrackingDomain $trackingDomain)
    {
        $trackingDomain = $this->authorizeManage($request, $trackingDomain);

        $this->trackingDomainService->delete($trackingDomain);

        return response()->json(['success' => true]);
    }

    public function verify(Request $request, TrackingDomain $trackingDomain)
    {
        $trackingDomain = $this->authorizeManage($request, $trackingDomain);

        $results = $this->trackingDomainService->verify($trackingDomain);

        return response()->json([
            'data' => $this->sanitizeTrackingDomain($trackingDomain->fresh()),
            'meta' => [
                'results' => $results,
            ],
        ]);
    }

    public function markVerified(Request $request, TrackingDomain $trackingDomain)
    {
        $trackingDomain = $this->authorizeManage($request, $trackingDomain);

        $trackingDomain->update([
            'status' => 'verified',
            'verified_at' => now(),
        ]);

        return response()->json([
            'data' => $this->sanitizeTrackingDomain($trackingDomain->fresh()),
        ]);
    }
}
