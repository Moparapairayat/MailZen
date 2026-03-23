<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Models\BounceServer;
use Illuminate\Http\Request;

class BounceServerController extends Controller
{
    protected function customer(Request $request)
    {
        return $request->user('sanctum');
    }

    protected function authorizeManage(Request $request, BounceServer $bounceServer): BounceServer
    {
        $customer = $this->customer($request);

        if (!$customer || (int) $bounceServer->customer_id !== (int) $customer->id) {
            abort(404);
        }

        return $bounceServer;
    }

    protected function authorizeView(Request $request, BounceServer $bounceServer): BounceServer
    {
        $customer = $this->customer($request);
        if (!$customer) {
            abort(404);
        }

        if ((int) $bounceServer->customer_id === (int) $customer->id) {
            return $bounceServer;
        }

        $mustAddOwn = (bool) $customer->groupSetting('servers.permissions.must_add_bounce_server', false);
        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);
        if (!$mustAddOwn && $canUseSystem && $bounceServer->customer_id === null) {
            return $bounceServer;
        }

        abort(404);
    }

    protected function sanitizeBounceServer(BounceServer $bounceServer): array
    {
        $bounceServer->makeHidden(['password']);
        return $bounceServer->toArray();
    }

    public function index(Request $request)
    {
        $customer = $this->customer($request);
        $filters = $request->only(['search', 'active']);

        $mustAddOwn = (bool) $customer->groupSetting('servers.permissions.must_add_bounce_server', false);
        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);

        $servers = BounceServer::query()
            ->when($mustAddOwn, function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            }, function ($q) use ($customer, $canUseSystem) {
                $q->where(function ($sub) use ($customer, $canUseSystem) {
                    $sub->where('customer_id', $customer->id);
                    if ($canUseSystem) {
                        $sub->orWhereNull('customer_id');
                    }
                });
            })
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('hostname', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->when(isset($filters['active']) && $filters['active'] !== '', function ($query) use ($filters) {
                $query->where('active', (bool) $filters['active']);
            })
            ->latest()
            ->paginate(15);

        $items = array_map(function ($row) {
            $server = $row instanceof BounceServer ? $row : null;
            if (!$server) {
                return $row;
            }
            return $this->sanitizeBounceServer($server);
        }, $servers->items());

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $servers->currentPage(),
                'per_page' => $servers->perPage(),
                'total' => $servers->total(),
                'last_page' => $servers->lastPage(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $customer = $this->customer($request);
        $customer->enforceGroupLimit('servers.limits.max_bounce_servers', $customer->bounceServers()->count(), 'Bounce server limit reached.');

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'protocol' => ['required', 'in:imap,pop3'],
            'hostname' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'encryption' => ['required', 'in:ssl,tls,none'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'mailbox' => ['nullable', 'string', 'max:255'],
            'active' => ['nullable', 'boolean'],
            'delete_after_processing' => ['nullable', 'boolean'],
            'max_emails_per_batch' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'notes' => ['nullable', 'string'],
        ];

        $data = $request->validate($rules);
        $data['active'] = $request->boolean('active');
        $data['delete_after_processing'] = $request->boolean('delete_after_processing');
        $data['customer_id'] = $customer->id;

        $bounceServer = BounceServer::create($data);

        return response()->json([
            'data' => $this->sanitizeBounceServer($bounceServer),
        ], 201);
    }

    public function show(Request $request, BounceServer $bounceServer)
    {
        $bounceServer = $this->authorizeView($request, $bounceServer);

        return response()->json([
            'data' => $this->sanitizeBounceServer($bounceServer),
        ]);
    }

    public function update(Request $request, BounceServer $bounceServer)
    {
        $bounceServer = $this->authorizeManage($request, $bounceServer);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'protocol' => ['required', 'in:imap,pop3'],
            'hostname' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'encryption' => ['required', 'in:ssl,tls,none'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string'],
            'mailbox' => ['nullable', 'string', 'max:255'],
            'active' => ['nullable', 'boolean'],
            'delete_after_processing' => ['nullable', 'boolean'],
            'max_emails_per_batch' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'notes' => ['nullable', 'string'],
        ];

        $data = $request->validate($rules);
        $data['active'] = $request->boolean('active');
        $data['delete_after_processing'] = $request->boolean('delete_after_processing');

        if (!array_key_exists('password', $data) || trim((string) ($data['password'] ?? '')) === '') {
            unset($data['password']);
        }

        $bounceServer->update($data);

        return response()->json([
            'data' => $this->sanitizeBounceServer($bounceServer->fresh()),
        ]);
    }

    public function destroy(Request $request, BounceServer $bounceServer)
    {
        $bounceServer = $this->authorizeManage($request, $bounceServer);
        $bounceServer->delete();

        return response()->json(['success' => true]);
    }
}
