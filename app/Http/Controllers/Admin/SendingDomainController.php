<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SendingDomain;
use App\Services\SendingDomainService;
use Illuminate\Http\Request;

class SendingDomainController extends Controller
{
    public function __construct(
        protected SendingDomainService $sendingDomainService
    ) {}

    public function index(Request $request)
    {
        $query = SendingDomain::query()->with('customer');

        $filters = $request->only(['search', 'status']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('domain', 'like', "%{$search}%");
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $sendingDomains = $query->latest()->paginate(15)->withQueryString();

        return view('admin.sending-domains.index', compact('sendingDomains', 'filters'));
    }

    public function show(SendingDomain $sendingDomain)
    {
        $sendingDomain->loadMissing('customer');

        return view('admin.sending-domains.show', compact('sendingDomain'));
    }

    public function edit(SendingDomain $sendingDomain)
    {
        $sendingDomain->loadMissing('customer');

        return view('admin.sending-domains.edit', compact('sendingDomain'));
    }

    public function update(Request $request, SendingDomain $sendingDomain)
    {
        $validated = $request->validate([
            'spf_record' => ['nullable', 'string'],
            'dmarc_record' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->sendingDomainService->update($sendingDomain, $validated);

        return redirect()
            ->route('admin.sending-domains.show', $sendingDomain)
            ->with('success', 'Sending domain updated successfully.');
    }

    public function destroy(SendingDomain $sendingDomain)
    {
        $this->sendingDomainService->delete($sendingDomain);

        return redirect()
            ->route('admin.sending-domains.index')
            ->with('success', 'Sending domain deleted successfully.');
    }

    public function makePrimary(SendingDomain $sendingDomain)
    {
        $this->sendingDomainService->setPrimary($sendingDomain);

        return redirect()
            ->back()
            ->with('success', 'Primary sending domain updated successfully.');
    }

    public function create()
    {
        return view('admin.sending-domains.create');
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
            'spf_record' => ['nullable', 'string'],
            'dmarc_record' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $sendingDomain = $this->sendingDomainService->create(null, $validated);

            return redirect()
                ->route('admin.sending-domains.show', $sendingDomain)
                ->with('success', 'Sending domain created successfully. Please verify it by adding the DNS records (SPF, DKIM, DMARC).');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['domain' => 'This domain has already been added.']);
            }

            throw $e;
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'This domain has already been added.') {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['domain' => 'This domain has already been added.']);
            }

            throw $e;
        }
    }

    public function verify(SendingDomain $sendingDomain)
    {
        $results = $this->sendingDomainService->verify($sendingDomain);

        if ($results['dkim']) {
            return redirect()
                ->route('admin.sending-domains.show', $sendingDomain)
                ->with('success', 'Sending domain verified successfully! DKIM record found in DNS.');
        }

        $errorMessage = 'Domain verification failed. ';
        if (!empty($results['errors'])) {
            $errorMessage .= implode(' ', $results['errors']);
        } else {
            $errorMessage .= 'DKIM record not found in DNS. Please add the DKIM DNS record and try again.';
        }

        return redirect()
            ->route('admin.sending-domains.show', $sendingDomain)
            ->with('error', $errorMessage)
            ->with('verification_results', $results);
    }

    public function markVerified(SendingDomain $sendingDomain)
    {
        $sendingDomain->update([
            'status' => 'verified',
            'verified_at' => now(),
        ]);

        return redirect()
            ->route('admin.sending-domains.show', $sendingDomain)
            ->with('success', 'Sending domain marked as verified.');
    }
}
