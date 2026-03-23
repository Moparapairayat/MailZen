<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Models\SendingDomain;
use App\Services\SendingDomainService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class SendingDomainController extends Controller
{
    public function __construct(
        protected SendingDomainService $sendingDomainService
    ) {
    }

    protected function customer(Request $request)
    {
        return $request->user('sanctum');
    }

    protected function authorizeManage(Request $request, SendingDomain $sendingDomain): SendingDomain
    {
        $customer = $this->customer($request);

        if (!$customer || (int) $sendingDomain->customer_id !== (int) $customer->id) {
            abort(404);
        }

        return $sendingDomain;
    }

    protected function authorizeView(Request $request, SendingDomain $sendingDomain): SendingDomain
    {
        $customer = $this->customer($request);
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

    protected function sanitizeSendingDomain(SendingDomain $sendingDomain): array
    {
        $sendingDomain->makeHidden(['dkim_private_key', 'verification_token']);
        return $sendingDomain->toArray();
    }

    public function index(Request $request)
    {
        $customer = $this->customer($request);
        $filters = $request->only(['search', 'status']);

        $sendingDomains = $this->sendingDomainService->getPaginated($customer, $filters);

        $items = array_map(function ($row) {
            $domain = $row instanceof SendingDomain ? $row : null;
            if (!$domain) {
                return $row;
            }
            return $this->sanitizeSendingDomain($domain);
        }, $sendingDomains->items());

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $sendingDomains->currentPage(),
                'per_page' => $sendingDomains->perPage(),
                'total' => $sendingDomains->total(),
                'last_page' => $sendingDomains->lastPage(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $customer = $this->customer($request);

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

            return response()->json([
                'data' => $this->sanitizeSendingDomain($sendingDomain),
            ], 201);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'This domain has already been added. Please use a different domain or edit the existing one.',
                    'errors' => [
                        'domain' => ['This domain has already been added. Please use a different domain or edit the existing one.'],
                    ],
                ], 422);
            }
            throw $e;
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'This domain has already been added.') {
                return response()->json([
                    'message' => 'This domain has already been added. Please use a different domain or edit the existing one.',
                    'errors' => [
                        'domain' => ['This domain has already been added. Please use a different domain or edit the existing one.'],
                    ],
                ], 422);
            }

            throw $e;
        }
    }

    public function show(Request $request, SendingDomain $sendingDomain)
    {
        $sendingDomain = $this->authorizeView($request, $sendingDomain);

        return response()->json([
            'data' => $this->sanitizeSendingDomain($sendingDomain),
        ]);
    }

    public function update(Request $request, SendingDomain $sendingDomain)
    {
        $sendingDomain = $this->authorizeManage($request, $sendingDomain);

        $validated = $request->validate([
            'spf_record' => ['nullable', 'string'],
            'dmarc_record' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $updated = $this->sendingDomainService->update($sendingDomain, $validated);

        return response()->json([
            'data' => $this->sanitizeSendingDomain($updated),
        ]);
    }

    public function destroy(Request $request, SendingDomain $sendingDomain)
    {
        $sendingDomain = $this->authorizeManage($request, $sendingDomain);

        $this->sendingDomainService->delete($sendingDomain);

        return response()->json(['success' => true]);
    }

    public function verify(Request $request, SendingDomain $sendingDomain)
    {
        $sendingDomain = $this->authorizeManage($request, $sendingDomain);

        $results = $this->sendingDomainService->verify($sendingDomain);

        return response()->json([
            'data' => [
                'sending_domain' => $this->sanitizeSendingDomain($sendingDomain->fresh()),
                'results' => $results,
            ],
        ]);
    }

    public function markVerified(Request $request, SendingDomain $sendingDomain)
    {
        $sendingDomain = $this->authorizeManage($request, $sendingDomain);

        $sendingDomain->update([
            'status' => 'verified',
            'verified_at' => now(),
        ]);

        return response()->json([
            'data' => $this->sanitizeSendingDomain($sendingDomain->fresh()),
        ]);
    }
}
