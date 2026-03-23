<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Models\ReplyServer;
use Illuminate\Http\Request;

class ReplyServerController extends Controller
{
    protected function customer(Request $request)
    {
        return $request->user('sanctum');
    }

    protected function authorizeManage(Request $request, ReplyServer $replyServer): ReplyServer
    {
        $customer = $this->customer($request);

        if (!$customer || (int) $replyServer->customer_id !== (int) $customer->id) {
            abort(404);
        }

        return $replyServer;
    }

    protected function authorizeView(Request $request, ReplyServer $replyServer): ReplyServer
    {
        $customer = $this->customer($request);
        if (!$customer) {
            abort(404);
        }

        if ((int) $replyServer->customer_id === (int) $customer->id) {
            return $replyServer;
        }

        $mustAddOwn = (bool) $customer->groupSetting('servers.permissions.must_add_reply_server', false);
        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);

        if (!$mustAddOwn && $canUseSystem && $replyServer->customer_id === null) {
            return $replyServer;
        }

        abort(404);
    }

    protected function sanitizeReplyServer(ReplyServer $replyServer): array
    {
        $replyServer->makeHidden(['password']);
        return $replyServer->toArray();
    }

    public function index(Request $request)
    {
        $customer = $this->customer($request);
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
            ->paginate(15);

        $items = array_map(function ($row) {
            $server = $row instanceof ReplyServer ? $row : null;
            if (!$server) {
                return $row;
            }
            return $this->sanitizeReplyServer($server);
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
        $customer->enforceGroupLimit('servers.limits.max_reply_servers', $customer->replyServers()->count(), 'Reply server limit reached.');

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'reply_domain' => ['nullable', 'string', 'max:255'],
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

        $replyServer = ReplyServer::create($data);

        return response()->json([
            'data' => $this->sanitizeReplyServer($replyServer),
        ], 201);
    }

    public function show(Request $request, ReplyServer $replyServer)
    {
        $replyServer = $this->authorizeView($request, $replyServer);

        return response()->json([
            'data' => $this->sanitizeReplyServer($replyServer),
        ]);
    }

    public function update(Request $request, ReplyServer $replyServer)
    {
        $replyServer = $this->authorizeManage($request, $replyServer);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'reply_domain' => ['nullable', 'string', 'max:255'],
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

        $replyServer->update($data);

        return response()->json([
            'data' => $this->sanitizeReplyServer($replyServer->fresh()),
        ]);
    }

    public function destroy(Request $request, ReplyServer $replyServer)
    {
        $replyServer = $this->authorizeManage($request, $replyServer);
        $replyServer->delete();

        return response()->json(['success' => true]);
    }
}
