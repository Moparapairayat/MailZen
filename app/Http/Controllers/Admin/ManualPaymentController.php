<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ManualPayment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\AffiliateCommissionService;
use Illuminate\Http\Request;

class ManualPaymentController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));

        $query = ManualPayment::query()->with(['customer', 'subscription']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('transfer_reference', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($c) use ($search) {
                        $c->where('email', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        $manualPayments = $query
            ->orderByRaw("CASE WHEN status = 'submitted' THEN 0 WHEN status = 'initiated' THEN 1 ELSE 2 END")
            ->orderByDesc('submitted_at')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.manual-payments.index', compact('manualPayments', 'search', 'status'));
    }

    public function show(Request $request, ManualPayment $manual_payment)
    {
        $manualPayment = $manual_payment->load(['customer', 'subscription', 'plan', 'reviewedByAdmin']);

        return view('admin.manual-payments.show', compact('manualPayment'));
    }

    public function approve(Request $request, ManualPayment $manual_payment)
    {
        $admin = $request->user('admin');

        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $manualPayment = $manual_payment->load(['subscription', 'customer']);
        $subscription = $manualPayment->subscription;

        abort_unless($subscription instanceof Subscription, 404);

        $plan = $subscription->plan;
        if (!$plan && $subscription->plan_db_id) {
            $plan = Plan::query()->with('customerGroup')->find($subscription->plan_db_id);
        }

        $periodStart = now();
        $periodEnd = ($subscription->billing_cycle === 'yearly') ? now()->addYear() : now()->addMonth();

        $subscription->update([
            'provider' => 'manual',
            'payment_gateway' => 'manual',
            'status' => $subscription->trial_ends_at && $subscription->trial_ends_at->isFuture() ? 'trialing' : 'active',
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'cancel_at_period_end' => false,
            'auto_renew' => false,
            'payment_reference' => $manualPayment->transfer_reference ?: $subscription->payment_reference,
            'last_payment_status' => 'approved',
        ]);

        if ($plan && $plan->customerGroup) {
            $limits = [
                'emails_sent_this_month' => $plan->customerGroup?->limit('sending_quota.monthly_quota', 0),
                'subscribers_count' => $plan->customerGroup?->limit('lists.limits.max_subscribers', 0),
                'campaigns_count' => $plan->customerGroup?->limit('campaigns.limits.max_campaigns', 0),
            ];

            $subscription->update([
                'features' => $plan->customerGroup?->permissions,
                'limits' => array_filter($limits, fn ($v) => $v !== null),
            ]);

            $customer = $subscription->customer;
            if ($customer && $plan->customer_group_id) {
                $customer->customerGroups()->syncWithoutDetaching([(int) $plan->customer_group_id]);
            }
        }

        $manualPayment->forceFill([
            'status' => 'approved',
            'reviewed_by_admin_id' => $admin?->id,
            'reviewed_at' => now(),
            'admin_notes' => $validated['admin_notes'] ?? null,
        ])->save();

        app(AffiliateCommissionService::class)->createCommissionForSubscriptionPayment(
            $subscription,
            'manual',
            'manual_payment:' . (string) $manualPayment->id,
            is_numeric($manualPayment->amount) ? (float) $manualPayment->amount : null,
            is_string($manualPayment->currency) ? strtoupper(trim((string) $manualPayment->currency)) : null,
            (int) $manualPayment->id
        );

        return redirect()
            ->route('admin.manual-payments.show', $manualPayment)
            ->with('success', __('Manual payment approved and subscription activated.'));
    }

    public function reject(Request $request, ManualPayment $manual_payment)
    {
        $admin = $request->user('admin');

        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $manualPayment = $manual_payment->load(['subscription']);
        $subscription = $manualPayment->subscription;

        if ($subscription instanceof Subscription) {
            $subscription->update([
                'status' => 'suspended',
                'last_payment_status' => 'rejected',
            ]);
        }

        $manualPayment->forceFill([
            'status' => 'rejected',
            'reviewed_by_admin_id' => $admin?->id,
            'reviewed_at' => now(),
            'admin_notes' => $validated['admin_notes'] ?? null,
        ])->save();

        return redirect()
            ->route('admin.manual-payments.show', $manualPayment)
            ->with('success', __('Manual payment rejected.'));
    }
}
