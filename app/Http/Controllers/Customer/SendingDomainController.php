<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\SendingDomain;
use App\Services\SendingDomainService;
use Illuminate\Http\Request;

class SendingDomainController extends Controller
{
    public function __construct(
        protected SendingDomainService $sendingDomainService
    ) {
        $this->middleware('customer.access:domains.sending_domains.permissions.can_access_sending_domains')->only([
            'index',
            'show',
        ]);
        $this->middleware('customer.access:domains.sending_domains.permissions.can_create_sending_domains')->only([
            'create',
            'store',
        ]);
        $this->middleware('customer.access:domains.sending_domains.permissions.can_edit_sending_domains')->only([
            'edit',
            'update',
            'verify',
            'markVerified',
        ]);
        $this->middleware('customer.access:domains.sending_domains.permissions.can_delete_sending_domains')->only([
            'destroy',
        ]);
    }

    protected function authorizeManage(SendingDomain $sendingDomain): SendingDomain
    {
        $customerId = auth('customer')->id();

        if (!$customerId || (int) $sendingDomain->customer_id !== (int) $customerId) {
            abort(404);
        }

        return $sendingDomain;
    }

    protected function authorizeView(SendingDomain $sendingDomain): SendingDomain
    {
        $customer = auth('customer')->user();
        if (!$customer) {
            abort(404);
        }

        if ((int) $sendingDomain->customer_id === (int) $customer->id) {
            return $sendingDomain;
        }

        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);
        $mustAddOwn = (bool) $customer->groupSetting('domains.sending_domains.must_add', false);

        if (!$mustAddOwn && $canUseSystem && $sendingDomain->customer_id === null) {
            return $sendingDomain;
        }

        abort(404);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status']);
        $sendingDomains = $this->sendingDomainService->getPaginated(auth('customer')->user(), $filters);

        return view('customer.sending-domains.index', compact('sendingDomains', 'filters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('customer.sending-domains.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $customer = auth('customer')->user();

        $customer->enforceGroupLimit('domains.sending_domains.max_sending_domains', $customer->sendingDomains()->count(), 'Sending domain limit reached.');
        
        $validated = $request->validate([
            'domain' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?)*$/',
            ],
            'spf_record' => ['nullable', 'string'],
            'dmarc_record' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $sendingDomain = $this->sendingDomainService->create($customer, $validated);

            return redirect()
                ->route('customer.sending-domains.show', $sendingDomain)
                ->with('success', 'Sending domain created successfully. Please verify it by adding the DNS records (SPF, DKIM, DMARC).');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['domain' => 'This domain has already been added. Please use a different domain or edit the existing one.']);
            }
            throw $e;
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'This domain has already been added.') {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['domain' => 'This domain has already been added. Please use a different domain or edit the existing one.']);
            }

            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SendingDomain $sendingDomain)
    {
        $this->authorizeView($sendingDomain);

        return view('customer.sending-domains.show', compact('sendingDomain'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SendingDomain $sendingDomain)
    {
        $this->authorizeManage($sendingDomain);

        return view('customer.sending-domains.edit', compact('sendingDomain'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SendingDomain $sendingDomain)
    {
        $this->authorizeManage($sendingDomain);

        $validated = $request->validate([
            'spf_record' => ['nullable', 'string'],
            'dmarc_record' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->sendingDomainService->update($sendingDomain, $validated);

        return redirect()
            ->route('customer.sending-domains.show', $sendingDomain)
            ->with('success', 'Sending domain updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SendingDomain $sendingDomain)
    {
        $this->authorizeManage($sendingDomain);

        $this->sendingDomainService->delete($sendingDomain);

        return redirect()
            ->route('customer.sending-domains.index')
            ->with('success', 'Sending domain deleted successfully.');
    }

    /**
     * Verify a sending domain.
     */
    public function verify(SendingDomain $sendingDomain)
    {
        $this->authorizeManage($sendingDomain);

        $results = $this->sendingDomainService->verify($sendingDomain);

        if ($results['dkim']) {
            return redirect()
                ->route('customer.sending-domains.show', $sendingDomain)
                ->with('success', 'Sending domain verified successfully! DKIM record found in DNS.');
        } else {
            $errorMessage = 'Domain verification failed. ';
            if (!empty($results['errors'])) {
                $errorMessage .= implode(' ', $results['errors']);
            } else {
                $errorMessage .= 'DKIM record not found in DNS. Please add the DKIM DNS record and try again.';
            }

            return redirect()
                ->route('customer.sending-domains.show', $sendingDomain)
                ->with('error', $errorMessage)
                ->with('verification_results', $results);
        }
    }

    public function markVerified(SendingDomain $sendingDomain)
    {
        $this->authorizeManage($sendingDomain);

        $sendingDomain->update([
            'status' => 'verified',
            'verified_at' => now(),
        ]);

        return redirect()
            ->route('customer.sending-domains.show', $sendingDomain)
            ->with('success', 'Sending domain marked as verified.');
    }
}
