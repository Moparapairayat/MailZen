<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $status = $request->query('status');
        $priority = $request->query('priority');

        $tickets = SupportTicket::query()
            ->with(['customer', 'messages' => fn($q) => $q->latest()->limit(1)])
            ->when($q !== '', function ($query) use ($q) {
                $query->where('subject', 'like', "%{$q}%")
                    ->orWhereHas('customer', function ($sub) use ($q) {
                        $sub->where('email', 'like', "%{$q}%")
                            ->orWhere('first_name', 'like', "%{$q}%")
                            ->orWhere('last_name', 'like', "%{$q}%");
                    });
            })
            ->when($status, fn($query) => $query->where('status', $status))
            ->when($priority, fn($query) => $query->where('priority', $priority))
            ->orderByDesc('last_message_at')
            ->paginate(25);

        return response()->json([
            'data' => $tickets->items(),
            'meta' => [
                'current_page' => $tickets->currentPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
                'last_page' => $tickets->lastPage(),
            ],
        ]);
    }

    public function show(SupportTicket $supportTicket)
    {
        return response()->json([
            'data' => $supportTicket->load(['customer', 'messages']),
        ]);
    }

    public function update(Request $request, SupportTicket $supportTicket)
    {
        $validated = $request->validate([
            'status' => 'sometimes|string|in:open,pending,resolved,closed',
            'priority' => 'sometimes|string|in:low,medium,high,urgent',
        ]);

        if (isset($validated['status']) && $validated['status'] === 'closed' && !$supportTicket->closed_at) {
            $validated['closed_at'] = now();
        }

        $supportTicket->update($validated);

        return response()->json([
            'data' => $supportTicket->fresh()->load(['customer', 'messages']),
            'message' => 'Ticket updated successfully.',
        ]);
    }

    public function reply(Request $request, SupportTicket $supportTicket)
    {
        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $message = $supportTicket->messages()->create([
            'sender_type' => 'admin',
            'sender_id' => $request->user()->id,
            'message' => $validated['message'],
        ]);

        $supportTicket->update([
            'last_message_at' => now(),
            'status' => 'pending',
        ]);

        return response()->json([
            'data' => $message,
            'message' => 'Reply sent successfully.',
        ], 201);
    }

    public function destroy(SupportTicket $supportTicket)
    {
        $supportTicket->messages()->delete();
        $supportTicket->delete();

        return response()->json([
            'message' => 'Ticket deleted successfully.',
        ]);
    }
}
