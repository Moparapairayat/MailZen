<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $subscriptions = Subscription::query()
            ->with(['customer', 'plan'])
            ->when($q !== '', function ($query) use ($q) {
                $query->whereHas('customer', function ($sub) use ($q) {
                    $sub->where('email', 'like', "%{$q}%")
                        ->orWhere('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(25);

        return response()->json([
            'data' => $subscriptions->items(),
            'meta' => [
                'current_page' => $subscriptions->currentPage(),
                'per_page' => $subscriptions->perPage(),
                'total' => $subscriptions->total(),
                'last_page' => $subscriptions->lastPage(),
            ],
        ]);
    }

    public function show(Subscription $invoice)
    {
        return response()->json([
            'data' => $invoice->load(['customer', 'plan']),
        ]);
    }
}
