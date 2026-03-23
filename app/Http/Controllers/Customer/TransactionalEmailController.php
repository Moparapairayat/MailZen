<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\TransactionalEmail;
use App\Services\TransactionalEmailService;
use Illuminate\Http\Request;

class TransactionalEmailController extends Controller
{
    public function __construct(
        protected TransactionalEmailService $transactionalEmailService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status']);
        $transactionalEmails = $this->transactionalEmailService->getPaginated(auth('customer')->user(), $filters);

        return view('customer.transactional-emails.index', compact('transactionalEmails', 'filters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('customer.transactional-emails.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'key' => ['nullable', 'string', 'max:255', 'unique:transactional_emails,key'],
            'subject' => ['required', 'string', 'max:255'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'from_email' => ['nullable', 'email', 'max:255'],
            'reply_to' => ['nullable', 'email', 'max:255'],
            'html_content' => ['nullable', 'string'],
            'plain_text_content' => ['nullable', 'string'],
            'template_variables' => ['nullable', 'array'],
            'status' => ['nullable', 'in:active,inactive'],
            'description' => ['nullable', 'string'],
            'track_opens' => ['nullable', 'boolean'],
            'track_clicks' => ['nullable', 'boolean'],
        ]);

        $transactionalEmail = $this->transactionalEmailService->create(auth('customer')->user(), $validated);

        return redirect()
            ->route('customer.transactional-emails.show', $transactionalEmail)
            ->with('success', 'Transactional email created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(TransactionalEmail $transactionalEmail)
    {
        return view('customer.transactional-emails.show', compact('transactionalEmail'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TransactionalEmail $transactionalEmail)
    {
        return view('customer.transactional-emails.edit', compact('transactionalEmail'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TransactionalEmail $transactionalEmail)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'key' => ['nullable', 'string', 'max:255', 'unique:transactional_emails,key,' . $transactionalEmail->id],
            'subject' => ['required', 'string', 'max:255'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'from_email' => ['nullable', 'email', 'max:255'],
            'reply_to' => ['nullable', 'email', 'max:255'],
            'html_content' => ['nullable', 'string'],
            'plain_text_content' => ['nullable', 'string'],
            'template_variables' => ['nullable', 'array'],
            'status' => ['nullable', 'in:active,inactive'],
            'description' => ['nullable', 'string'],
            'track_opens' => ['nullable', 'boolean'],
            'track_clicks' => ['nullable', 'boolean'],
        ]);

        $this->transactionalEmailService->update($transactionalEmail, $validated);

        return redirect()
            ->route('customer.transactional-emails.show', $transactionalEmail)
            ->with('success', 'Transactional email updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TransactionalEmail $transactionalEmail)
    {
        $this->transactionalEmailService->delete($transactionalEmail);

        return redirect()
            ->route('customer.transactional-emails.index')
            ->with('success', 'Transactional email deleted successfully.');
    }
}
