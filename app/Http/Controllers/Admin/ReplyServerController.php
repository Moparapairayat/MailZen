<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReplyServer;
use Illuminate\Http\Request;

class ReplyServerController extends Controller
{
    public function index()
    {
        $servers = ReplyServer::latest()->paginate(15);
        return view('admin.reply-servers.index', compact('servers'));
    }

    public function create()
    {
        return view('admin.reply-servers.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        ReplyServer::create($data);

        return redirect()
            ->route('admin.reply-servers.index')
            ->with('success', 'Reply server created.');
    }

    public function show(ReplyServer $replyServer)
    {
        return view('admin.reply-servers.show', compact('replyServer'));
    }

    public function edit(ReplyServer $replyServer)
    {
        return view('admin.reply-servers.edit', compact('replyServer'));
    }

    public function update(Request $request, ReplyServer $replyServer)
    {
        $data = $this->validateData($request, true);
        if (!array_key_exists('password', $data) || trim((string) ($data['password'] ?? '')) === '') {
            unset($data['password']);
        }
        $replyServer->update($data);

        return redirect()
            ->route('admin.reply-servers.index')
            ->with('success', 'Reply server updated.');
    }

    public function destroy(ReplyServer $replyServer)
    {
        $replyServer->delete();

        return redirect()
            ->route('admin.reply-servers.index')
            ->with('success', 'Reply server deleted.');
    }

    protected function validateData(Request $request, bool $isUpdate = false): array
    {
        $rules = [
            'customer_id' => ['nullable', 'exists:customers,id'],
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
