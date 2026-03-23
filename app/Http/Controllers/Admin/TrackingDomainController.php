<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\TrackingDomain;
use App\Services\TrackingDomainService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TrackingDomainController extends Controller
{
    public function __construct(
        protected TrackingDomainService $trackingDomainService
    ) {}

    public function index(Request $request)
    {
        $query = TrackingDomain::query()->with('customer');

        $filters = $request->only(['search', 'status']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('domain', 'like', "%{$search}%");
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $trackingDomains = $query->latest()->paginate(15)->withQueryString();

        return view('admin.tracking-domains.index', compact('trackingDomains', 'filters'));
    }

    public function create()
    {
        $customers = Customer::query()->orderBy('email')->get(['id', 'email', 'first_name', 'last_name']);

        return view('admin.tracking-domains.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'domain' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?)*$/',
            ],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $domain = strtolower(trim($validated['domain']));

        $existing = TrackingDomain::withTrashed()
            ->where('domain', $domain)
            ->first();

        if ($existing !== null) {
            if ($existing->trashed()) {
                $existing->restore();
                $existing->update([
                    'customer_id' => $validated['customer_id'] ?? null,
                    'status' => 'pending',
                    'verification_token' => Str::random(32),
                    'notes' => $validated['notes'] ?? null,
                    'dns_records' => [],
                    'verified_at' => null,
                    'verification_data' => null,
                ]);

                return redirect()
                    ->route('admin.tracking-domains.show', $existing->fresh())
                    ->with('success', 'Tracking domain created successfully.');
            }

            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['domain' => 'This domain has already been added.']);
        }

        $trackingDomain = TrackingDomain::create([
            'customer_id' => $validated['customer_id'] ?? null,
            'domain' => $domain,
            'status' => 'pending',
            'verification_token' => Str::random(32),
            'notes' => $validated['notes'] ?? null,
            'dns_records' => [],
        ]);

        return redirect()
            ->route('admin.tracking-domains.show', $trackingDomain)
            ->with('success', 'Tracking domain created successfully.');
    }

    public function show(TrackingDomain $trackingDomain)
    {
        $trackingDomain->loadMissing('customer');

        return view('admin.tracking-domains.show', compact('trackingDomain'));
    }

    public function edit(TrackingDomain $trackingDomain)
    {
        $trackingDomain->loadMissing('customer');

        $customers = Customer::query()->orderBy('email')->get(['id', 'email', 'first_name', 'last_name']);

        return view('admin.tracking-domains.edit', compact('trackingDomain', 'customers'));
    }

    public function update(Request $request, TrackingDomain $trackingDomain)
    {
        $validated = $request->validate([
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $trackingDomain->update([
            'customer_id' => $validated['customer_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('admin.tracking-domains.show', $trackingDomain)
            ->with('success', 'Tracking domain updated successfully.');
    }

    public function destroy(TrackingDomain $trackingDomain)
    {
        $trackingDomain->delete();

        return redirect()
            ->route('admin.tracking-domains.index')
            ->with('success', 'Tracking domain deleted successfully.');
    }

    public function verify(TrackingDomain $trackingDomain)
    {
        $results = $this->trackingDomainService->verify($trackingDomain);

        if (!empty($results['cname'])) {
            return redirect()
                ->route('admin.tracking-domains.show', $trackingDomain)
                ->with('success', 'Tracking domain verified successfully! CNAME record found in DNS.');
        }

        $errorMessage = 'Domain verification failed. ';
        if (!empty($results['errors'])) {
            $errorMessage .= implode(' ', $results['errors']);
        } else {
            $errorMessage .= 'CNAME record not found in DNS. Please add the CNAME record and try again.';
        }

        return redirect()
            ->route('admin.tracking-domains.show', $trackingDomain)
            ->with('error', $errorMessage)
            ->with('verification_results', $results);
    }

    public function markVerified(TrackingDomain $trackingDomain)
    {
        $trackingDomain->update([
            'status' => 'verified',
            'verified_at' => now(),
        ]);

        return redirect()
            ->route('admin.tracking-domains.show', $trackingDomain)
            ->with('success', 'Tracking domain marked as verified.');
    }
}
