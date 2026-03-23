<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BounceServer;
use Illuminate\Http\Request;

class BounceServerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $servers = BounceServer::latest()->paginate(15);
        return view('admin.bounce-servers.index', compact('servers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.bounce-servers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $this->validateData($request);
        BounceServer::create($data);

        return redirect()
            ->route('admin.bounce-servers.index')
            ->with('success', 'Bounce server created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(BounceServer $bounceServer)
    {
        return view('admin.bounce-servers.show', compact('bounceServer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BounceServer $bounceServer)
    {
        return view('admin.bounce-servers.edit', compact('bounceServer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BounceServer $bounceServer)
    {
        $data = $this->validateData($request, true);
        // If password left blank, keep existing
        if (!array_key_exists('password', $data) || trim((string) ($data['password'] ?? '')) === '') {
            unset($data['password']);
        }
        $bounceServer->update($data);

        return redirect()
            ->route('admin.bounce-servers.index')
            ->with('success', 'Bounce server updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BounceServer $bounceServer)
    {
        $bounceServer->delete();

        return redirect()
            ->route('admin.bounce-servers.index')
            ->with('success', 'Bounce server deleted.');
    }

    /**
     * Validate request data.
     */
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
        // Normalize booleans
        $data['active'] = $request->boolean('active');
        $data['delete_after_processing'] = $request->boolean('delete_after_processing');

        return $data;
    }
}
