<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function feed(Request $request): JsonResponse
    {
        $customer = auth('customer')->user();
        if (!$customer) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        Log::info('Customer notifications feed requested', [
            'customer_id' => $customer->id,
            'unread_count' => $customer->unreadNotifications()->count(),
        ]);

        $notifications = $customer->notifications()
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'is_read' => (bool) $notification->read_at,
                    'read_at' => $notification->read_at?->toIso8601String(),
                    'created_at' => $notification->created_at?->toIso8601String(),
                    'created_at_human' => $notification->created_at?->diffForHumans(),
                    'type' => $notification->data['type'] ?? null,
                    'title' => $notification->data['title'] ?? 'Notification',
                    'message' => $notification->data['message'] ?? null,
                    'data' => $notification->data,
                ];
            })
            ->values();

        return response()->json([
            'unread_count' => $customer->unreadNotifications()->count(),
            'notifications' => $notifications,
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $customer = auth('customer')->user();
        if (!$customer) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        Log::info('Customer notifications mark-all-read requested', [
            'customer_id' => $customer->id,
        ]);

        $customer->unreadNotifications->markAsRead();

        return response()->json([
            'unread_count' => 0,
        ]);
    }
}
