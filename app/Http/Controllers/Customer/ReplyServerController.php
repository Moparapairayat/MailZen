<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\ReplyServer;
use Illuminate\Http\Request;

class ReplyServerController extends Controller
{
    public function __construct()
    {
        $this->middleware('customer.access:servers.permissions.can_access_reply_servers')->only(['index', 'show']);
        $this->middleware('customer.access:servers.permissions.can_add_reply_servers')->only(['create', 'store']);
        $this->middleware('customer.access:servers.permissions.can_edit_reply_servers')->only(['edit', 'update']);
        $this->middleware('customer.access:servers.permissions.can_delete_reply_servers')->only(['destroy']);

        $this->middleware('demo.prevent')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    protected function authorizeManage(ReplyServer $replyServer): ReplyServer
    {
        $customerId = auth('customer')->id();

        if (!$customerId || (int) $replyServer->customer_id !== (int) $customerId) {
            abort(404);
        }

        return $replyServer;
    }

    protected function authorizeView(ReplyServer $replyServer): ReplyServer
    {
        $customer = auth('customer')->user();
        if (!$customer) {
            abort(404);
        }

        if ((int) $replyServer->customer_id === (int) $customer->id) {
            return $replyServer;
        }

        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);
        $mustAddOwn = (bool) $customer->groupSetting('servers.permissions.must_add_reply_server', false);

        if (!$mustAddOwn && $canUseSystem && $replyServer->customer_id === null) {
            return $replyServer;
        }

        abort(404);
    }

    public function index(Request $request)
    {
        $customer = auth('customer')->user();
        $filters = $request->only(['search', 'active']);

        $mustAddOwn = (bool) $customer->groupSetting('servers.permissions.must_add_reply_server', false);
        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);

        $servers = ReplyServer::query()
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
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('reply_domain', 'like', "%{$search}%");
                });
            })
            ->when(isset($filters['active']) && $filters['active'] !== '', function ($query) use ($filters) {
                $query->where('active', (bool) $filters['active']);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('customer.reply-servers.index', compact('servers', 'filters'));
    }

    public function create()
    {
        return view('customer.reply-servers.create');
    }

    public function store(Request $request)
    {
        $customer = auth('customer')->user();
        $customer->enforceGroupLimit('servers.limits.max_reply_servers', $customer->replyServers()->count(), 'Reply server limit reached.');

        $data = $this->validateData($request, false);
        $data['customer_id'] = $customer->id;

        $replyServer = ReplyServer::create($data);

        return redirect()
            ->route('customer.reply-servers.show', $replyServer)
            ->with('success', 'Reply server created.');
    }

    public function show(ReplyServer $reply_server)
    {
        $this->authorizeView($reply_server);

        return view('customer.reply-servers.show', ['replyServer' => $reply_server]);
    }

    public function edit(ReplyServer $reply_server)
    {
        $this->authorizeManage($reply_server);

        return view('customer.reply-servers.edit', ['replyServer' => $reply_server]);
    }

    public function update(Request $request, ReplyServer $reply_server)
    {
        $this->authorizeManage($reply_server);

        $data = $this->validateData($request, true);

        if (!array_key_exists('password', $data) || trim((string) ($data['password'] ?? '')) === '') {
            unset($data['password']);
        }

        $reply_server->update($data);

        return redirect()
            ->route('customer.reply-servers.index')
            ->with('success', 'Reply server updated.');
    }

    public function destroy(ReplyServer $reply_server)
    {
        $this->authorizeManage($reply_server);

        $reply_server->delete();

        return redirect()
            ->route('customer.reply-servers.index')
            ->with('success', 'Reply server deleted.');
    }

    protected function validateData(Request $request, bool $isUpdate = false): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'reply_domain' => ['nullable', 'string', 'max:255'],
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
