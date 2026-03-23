<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status']);
        $subscriptions = $this->subscriptionService->getPaginated(auth('customer')->user(), $filters);

        return view('customer.subscriptions.index', compact('subscriptions', 'filters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // In a real app, you'd fetch available plans from a plans table
        $plans = [
            ['id' => 1, 'name' => 'Starter', 'price' => 29, 'billing_cycle' => 'monthly'],
            ['id' => 2, 'name' => 'Professional', 'price' => 79, 'billing_cycle' => 'monthly'],
            ['id' => 3, 'name' => 'Enterprise', 'price' => 199, 'billing_cycle' => 'monthly'],
        ];

        return view('customer.subscriptions.create', compact('plans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'integer'],
            'plan_name' => ['required', 'string', 'max:255'],
            'billing_cycle' => ['required', 'in:monthly,yearly'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:3'],
            'trial_ends_at' => ['nullable', 'date'],
            'features' => ['nullable', 'array'],
            'limits' => ['nullable', 'array'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'payment_gateway' => ['nullable', 'string', 'max:255'],
            'auto_renew' => ['nullable', 'boolean'],
        ]);

        $subscription = $this->subscriptionService->create(auth('customer')->user(), $validated);

        return redirect()
            ->route('customer.subscriptions.show', $subscription)
            ->with('success', 'Subscription created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Subscription $subscription)
    {
        return view('customer.subscriptions.show', compact('subscription'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subscription $subscription)
    {
        return view('customer.subscriptions.edit', compact('subscription'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subscription $subscription)
    {
        $validated = $request->validate([
            'auto_renew' => ['nullable', 'boolean'],
            'payment_method' => ['nullable', 'string', 'max:255'],
        ]);

        $this->subscriptionService->update($subscription, $validated);

        return redirect()
            ->route('customer.subscriptions.show', $subscription)
            ->with('success', 'Subscription updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subscription $subscription)
    {
        $this->subscriptionService->delete($subscription);

        return redirect()
            ->route('customer.subscriptions.index')
            ->with('success', 'Subscription deleted successfully.');
    }

    /**
     * Cancel a subscription.
     */
    public function cancel(Request $request, Subscription $subscription)
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $this->subscriptionService->cancel($subscription, $validated['reason'] ?? null);

        return redirect()
            ->route('customer.subscriptions.show', $subscription)
            ->with('success', 'Subscription cancelled successfully.');
    }

    /**
     * Renew a subscription.
     */
    public function renew(Subscription $subscription)
    {
        $this->subscriptionService->renew($subscription);

        return redirect()
            ->route('customer.subscriptions.show', $subscription)
            ->with('success', 'Subscription renewed successfully.');
    }
}
