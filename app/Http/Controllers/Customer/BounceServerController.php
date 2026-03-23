<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\BounceServer;
use Illuminate\Http\Request;

class BounceServerController extends Controller
{
    public function __construct()
    {
        $this->middleware('customer.access:servers.permissions.can_access_bounce_servers')->only(['index', 'show']);
        $this->middleware('customer.access:servers.permissions.can_add_bounce_servers')->only(['create', 'store']);
        $this->middleware('customer.access:servers.permissions.can_edit_bounce_servers')->only(['edit', 'update']);
        $this->middleware('customer.access:servers.permissions.can_delete_bounce_servers')->only(['destroy']);

        $this->middleware('demo.prevent')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    protected function authorizeManage(BounceServer $bounceServer): BounceServer
    {
        $customerId = auth('customer')->id();

        if (!$customerId || (int) $bounceServer->customer_id !== (int) $customerId) {
            abort(404);
        }

        return $bounceServer;
    }

    protected function authorizeView(BounceServer $bounceServer): BounceServer
    {
        $customer = auth('customer')->user();
        if (!$customer) {
            abort(404);
        }

        if ((int) $bounceServer->customer_id === (int) $customer->id) {
            return $bounceServer;
        }

        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);
        $mustAddOwn = (bool) $customer->groupSetting('servers.permissions.must_add_bounce_server', false);

        if (!$mustAddOwn && $canUseSystem && $bounceServer->customer_id === null) {
            return $bounceServer;
        }

        abort(404);
    }

    public function index(Request $request)
    {
        $customer = auth('customer')->user();
        $filters = $request->only(['search', 'active']);

        $mustAddOwn = (bool) $customer->groupSetting('servers.permissions.must_add_bounce_server', false);
        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);

        $servers = BounceServer::query()
            ->when($mustAddOwn, function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            }, function ($q) use ($customer, $canUseSystem) {
                $q->where(function ($sub) use ($customer, $canUseSystem) {
                    $sub->where('customer_id', $customer->id);
                    $sub->orWhereNull('customer_id');
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
            ->paginate(15)
            ->withQueryString();

        return view('customer.bounce-servers.index', compact('servers', 'filters'));
    }

    public function create()
    {
        return view('customer.bounce-servers.create');
    }

    public function store(Request $request)
    {
        $customer = auth('customer')->user();

        $data = $this->validateData($request, false);
        $data['customer_id'] = $customer->id;

        $bounceServer = BounceServer::create($data);

        return redirect()
            ->route('customer.bounce-servers.show', $bounceServer)
            ->with('success', 'Bounce server created.');
    }

    public function show(BounceServer $bounce_server)
    {
        $this->authorizeView($bounce_server);

        return view('customer.bounce-servers.show', ['bounceServer' => $bounce_server]);
    }

    public function edit(BounceServer $bounce_server)
    {
        $this->authorizeManage($bounce_server);

        return view('customer.bounce-servers.edit', ['bounceServer' => $bounce_server]);
    }

    public function update(Request $request, BounceServer $bounce_server)
    {
        $this->authorizeManage($bounce_server);

        $data = $this->validateData($request, true);

        if (!array_key_exists('password', $data) || trim((string) ($data['password'] ?? '')) === '') {
            unset($data['password']);
        }

        $bounce_server->update($data);

        return redirect()
            ->route('customer.bounce-servers.index')
            ->with('success', 'Bounce server updated.');
    }

    public function destroy(BounceServer $bounce_server)
    {
        $this->authorizeManage($bounce_server);

        $bounce_server->delete();

        return redirect()
            ->route('customer.bounce-servers.index')
            ->with('success', 'Bounce server deleted.');
    }

    protected function validateData(Request $request, bool $isUpdate = false): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'protocol' => ['required', 'in:imap,pop3'],
            'hostname' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'encryption' => ['required', 'in:ssl,tls,none'],
            'username' => ['required', 'string', 'max:255'],
            'password' => [$isUpdate ? 'nullable' : 'required', 'string'],
            'mailbox' => ['nullable', 'string', 'max:255'],
            'active' => ['nullable', 'boolean'],
            'delete_after_processing' => ['nullable', 'boolean'],
            'max_emails_per_batch' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'notes' => ['nullable', 'string'],
        ];

        $data = $request->validate($rules);
        $data['active'] = $request->boolean('active');
        $data['delete_after_processing'] = $request->boolean('delete_after_processing');

        return $data;
    }
}
