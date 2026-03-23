<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $plans = Plan::query()
            ->with('customerGroup')
            ->when($q !== '', function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%");
            })
            ->orderByDesc('id')
            ->paginate(25);

        return response()->json([
            'data' => $plans->items(),
            'meta' => [
                'current_page' => $plans->currentPage(),
                'per_page' => $plans->perPage(),
                'total' => $plans->total(),
                'last_page' => $plans->lastPage(),
            ],
        ]);
    }

    public function show(Plan $plan)
    {
        return response()->json([
            'data' => $plan->load('customerGroup'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:plans,slug',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'billing_cycle' => 'nullable|string|in:monthly,yearly,lifetime',
            'trial_days' => 'nullable|integer|min:0',
            'features' => 'nullable|array',
            'limits' => 'nullable|array',
            'customer_group_id' => 'nullable|exists:customer_groups,id',
            'stripe_price_id' => 'nullable|string',
            'stripe_product_id' => 'nullable|string',
            'cta_text' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'is_popular' => 'nullable|boolean',
        ]);

        $plan = Plan::create($validated);

        return response()->json([
            'data' => $plan->load('customerGroup'),
            'message' => 'Plan created successfully.',
        ], 201);
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:plans,slug,' . $plan->id,
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'billing_cycle' => 'nullable|string|in:monthly,yearly,lifetime',
            'trial_days' => 'nullable|integer|min:0',
            'features' => 'nullable|array',
            'limits' => 'nullable|array',
            'customer_group_id' => 'nullable|exists:customer_groups,id',
            'stripe_price_id' => 'nullable|string',
            'stripe_product_id' => 'nullable|string',
            'cta_text' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'is_popular' => 'nullable|boolean',
        ]);

        $plan->update($validated);

        return response()->json([
            'data' => $plan->fresh()->load('customerGroup'),
            'message' => 'Plan updated successfully.',
        ]);
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();

        return response()->json([
            'message' => 'Plan deleted successfully.',
        ]);
    }
}
