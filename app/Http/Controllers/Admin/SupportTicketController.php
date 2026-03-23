<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'search']);

        if (!$request->has('status')) {
            $filters['status'] = 'open';
        }

        $baseQuery = SupportTicket::query()->with('customer');

        if (($filters['search'] ?? '') !== '') {
            $search = $filters['search'];
            $baseQuery->where(function ($sub) use ($search) {
                $sub->where('subject', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($c) use ($search) {
                        $c->where('email', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        $openCount = (clone $baseQuery)->where('status', 'open')->count();
        $closedCount = (clone $baseQuery)->where('status', 'closed')->count();

        $tickets = $baseQuery
            ->when(in_array(($filters['status'] ?? ''), ['open', 'closed'], true), function ($q) use ($filters) {
                $q->where('status', $filters['status']);
            })
            ->orderByDesc('last_message_at')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.support-tickets.index', compact('tickets', 'filters', 'openCount', 'closedCount'));
    }

    public function drawer(Request $request, SupportTicket $support_ticket)
    {
        $ticket = $support_ticket->load('customer');

        $messages = $ticket->messages()
            ->with('sender')
            ->orderBy('created_at')
            ->get();

        return view('admin.support-tickets._drawer', compact('ticket', 'messages'));
    }

    public function show(Request $request, SupportTicket $support_ticket)
    {
        $ticket = $support_ticket->load('customer');

        $messages = $ticket->messages()
            ->with('sender')
            ->orderBy('created_at')
            ->get();

        return view('admin.support-tickets.show', compact('ticket', 'messages'));
    }

    public function reply(Request $request, SupportTicket $support_ticket)
    {
        $admin = $request->user('admin');

        $validated = $request->validate([
            'body' => ['required', 'string'],
        ]);

        SupportTicketMessage::create([
            'support_ticket_id' => $support_ticket->id,
            'sender_type' => get_class($admin),
            'sender_id' => $admin->id,
            'body' => SupportTicketMessage::sanitizeBody($validated['body']),
        ]);

        $support_ticket->forceFill([
            'last_message_at' => now(),
            'status' => 'open',
            'closed_at' => null,
        ])->save();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->noContent();
        }

        return redirect()
            ->route('admin.support-tickets.show', $support_ticket)
            ->with('success', 'Reply sent successfully.');
    }

    public function setStatus(Request $request, SupportTicket $support_ticket)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:open,closed'],
        ]);

        $support_ticket->status = $validated['status'];
        $support_ticket->closed_at = $validated['status'] === 'closed' ? now() : null;
        $support_ticket->save();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->noContent();
        }

        return redirect()
            ->route('admin.support-tickets.show', $support_ticket)
            ->with('success', 'Ticket updated successfully.');
    }

    public function setPriority(Request $request, SupportTicket $support_ticket)
    {
        $validated = $request->validate([
            'priority' => ['required', 'in:low,normal,high'],
        ]);

        $support_ticket->priority = $validated['priority'];
        $support_ticket->save();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->noContent();
        }

        return redirect()
            ->route('admin.support-tickets.show', $support_ticket)
            ->with('success', 'Ticket updated successfully.');
    }
}
