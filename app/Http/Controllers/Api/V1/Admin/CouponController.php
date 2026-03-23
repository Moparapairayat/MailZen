<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $coupons = Coupon::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('code', 'like', "%{$q}%")
                    ->orWhere('name', 'like', "%{$q}%");
            })
            ->orderByDesc('id')
            ->paginate(25);

        return response()->json([
            'data' => $coupons->items(),
            'meta' => [
                'current_page' => $coupons->currentPage(),
                'per_page' => $coupons->perPage(),
                'total' => $coupons->total(),
                'last_page' => $coupons->lastPage(),
            ],
        ]);
    }

    public function show(Coupon $coupon)
    {
        return response()->json([
            'data' => $coupon,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:coupons,code',
            'name' => 'nullable|string|max:255',
            'type' => 'required|string|in:percent,amount',
            'percent_off' => 'nullable|numeric|min:0|max:100',
            'amount_off' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'duration' => 'nullable|string|in:once,repeating,forever',
            'duration_in_months' => 'nullable|integer|min:1',
            'max_redemptions' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
            'is_active' => 'nullable|boolean',
        ]);

        $coupon = Coupon::create($validated);

        return response()->json([
            'data' => $coupon,
            'message' => 'Coupon created successfully.',
        ], 201);
    }

    public function update(Request $request, Coupon $coupon)
    {
        $validated = $request->validate([
            'code' => 'sometimes|required|string|max:255|unique:coupons,code,' . $coupon->id,
            'name' => 'nullable|string|max:255',
            'type' => 'sometimes|required|string|in:percent,amount',
            'percent_off' => 'nullable|numeric|min:0|max:100',
            'amount_off' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'duration' => 'nullable|string|in:once,repeating,forever',
            'duration_in_months' => 'nullable|integer|min:1',
            'max_redemptions' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);

        $coupon->update($validated);

        return response()->json([
            'data' => $coupon->fresh(),
            'message' => 'Coupon updated successfully.',
        ]);
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return response()->json([
            'message' => 'Coupon deleted successfully.',
        ]);
    }
}
