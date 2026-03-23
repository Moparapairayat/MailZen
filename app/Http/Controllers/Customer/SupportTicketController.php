<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('customer.access:support.permissions.can_access_support')->only(['index', 'show']);
        $this->middleware('customer.access:support.permissions.can_create_tickets')->only(['create', 'store']);
        $this->middleware('customer.access:support.permissions.can_reply_tickets')->only(['reply']);
        $this->middleware('customer.access:support.permissions.can_close_tickets')->only(['close']);

        $this->middleware('demo.prevent')->only(['store', 'reply', 'close']);
    }

    public function index(Request $request)
    {
        $customer = $request->user('customer');

        $tickets = SupportTicket::query()
            ->where('customer_id', $customer->id)
            ->orderByDesc('last_message_at')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('customer.support-tickets.index', compact('tickets'));
    }

    public function create()
    {
        return view('customer.support-tickets.create');
    }

    public function store(Request $request)
    {
        $customer = $request->user('customer');

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'priority' => ['nullable', 'in:low,normal,high'],
        ]);

        $ticket = SupportTicket::create([
            'customer_id' => $customer->id,
            'subject' => $validated['subject'],
            'priority' => $validated['priority'] ?? 'normal',
            'status' => 'open',
            'last_message_at' => now(),
        ]);

        SupportTicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_type' => get_class($customer),
            'sender_id' => $customer->id,
            'body' => SupportTicketMessage::sanitizeBody($validated['body']),
        ]);

        return redirect()
            ->route('customer.support-tickets.show', $ticket)
            ->with('success', 'Support ticket created successfully.');
    }

    public function show(Request $request, SupportTicket $support_ticket)
    {
        $ticket = $this->authorizeOwnership($request, $support_ticket);

        $messages = $ticket->messages()
            ->with('sender')
            ->orderBy('created_at')
            ->get();

        return view('customer.support-tickets.show', compact('ticket', 'messages'));
    }

    public function reply(Request $request, SupportTicket $support_ticket)
    {
        $customer = $request->user('customer');
        $ticket = $this->authorizeOwnership($request, $support_ticket);

        $validated = $request->validate([
            'body' => ['required', 'string'],
        ]);

        SupportTicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_type' => get_class($customer),
            'sender_id' => $customer->id,
            'body' => SupportTicketMessage::sanitizeBody($validated['body']),
        ]);

        $ticket->forceFill([
            'last_message_at' => now(),
            'status' => 'open',
            'closed_at' => null,
        ])->save();

        return redirect()
            ->route('customer.support-tickets.show', $ticket)
            ->with('success', 'Reply sent successfully.');
    }

    public function close(Request $request, SupportTicket $support_ticket)
    {
        $ticket = $this->authorizeOwnership($request, $support_ticket);

        $ticket->forceFill([
            'status' => 'closed',
            'closed_at' => now(),
        ])->save();

        return redirect()
            ->route('customer.support-tickets.show', $ticket)
            ->with('success', 'Ticket closed.');
    }

    private function authorizeOwnership(Request $request, SupportTicket $ticket): SupportTicket
    {
        $customerId = $request->user('customer')?->id;

        if (!$customerId || (int) $ticket->customer_id !== (int) $customerId) {
            abort(404);
        }

        return $ticket;
    }
}
