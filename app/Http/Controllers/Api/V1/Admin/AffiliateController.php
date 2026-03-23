<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use Illuminate\Http\Request;

class AffiliateController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $status = $request->query('status');

        $affiliates = Affiliate::query()
            ->with(['customer', 'referrals', 'commissions'])
            ->withCount(['referrals', 'commissions'])
            ->withSum('commissions', 'amount')
            ->when($q !== '', function ($query) use ($q) {
                $query->where('code', 'like', "%{$q}%")
                    ->orWhereHas('customer', function ($sub) use ($q) {
                        $sub->where('email', 'like', "%{$q}%")
                            ->orWhere('first_name', 'like', "%{$q}%")
                            ->orWhere('last_name', 'like', "%{$q}%");
                    });
            })
            ->when($status, fn($query) => $query->where('status', $status))
            ->orderByDesc('id')
            ->paginate(25);

        return response()->json([
            'data' => $affiliates->items(),
            'meta' => [
                'current_page' => $affiliates->currentPage(),
                'per_page' => $affiliates->perPage(),
                'total' => $affiliates->total(),
                'last_page' => $affiliates->lastPage(),
            ],
        ]);
    }

    public function show(Affiliate $affiliate)
    {
        return response()->json([
            'data' => $affiliate->load(['customer', 'referrals.customer', 'commissions', 'payouts']),
        ]);
    }

    public function update(Request $request, Affiliate $affiliate)
    {
        $validated = $request->validate([
            'status' => 'sometimes|string|in:pending,approved,blocked',
            'commission_rate_percent' => 'sometimes|numeric|min:0|max:100',
            'destination_url' => 'nullable|url',
            'payout_details' => 'nullable|array',
        ]);

        if (isset($validated['status'])) {
            if ($validated['status'] === 'approved' && !$affiliate->approved_at) {
                $validated['approved_at'] = now();
                $validated['blocked_at'] = null;
            } elseif ($validated['status'] === 'blocked') {
                $validated['blocked_at'] = now();
            }
        }

        $affiliate->update($validated);

        return response()->json([
            'data' => $affiliate->fresh()->load(['customer', 'referrals', 'commissions']),
            'message' => 'Affiliate updated successfully.',
        ]);
    }

    public function destroy(Affiliate $affiliate)
    {
        $affiliate->delete();

        return response()->json([
            'message' => 'Affiliate deleted successfully.',
        ]);
    }
}
