<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\ManualPayment;
use App\Models\Setting;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class ManualPaymentController extends Controller
{
    public function show(Request $request, Subscription $subscription)
    {
        $customer = $request->user('customer');
        abort_if(!$customer, 403, 'Please sign in to access billing.');
        abort_if((int) $subscription->customer_id !== (int) $customer->id, 403, 'This billing link does not belong to your account.');

        $manualPayment = ManualPayment::query()
            ->where('subscription_id', $subscription->id)
            ->first();

        $providers = Setting::get('billing_providers');
        $manualConfig = is_array($providers) ? (array) data_get($providers, 'manual', []) : [];

        return view('customer.billing.manual', compact('subscription', 'manualPayment', 'manualConfig'));
    }

    public function confirm(Request $request, Subscription $subscription)
    {
        $customer = $request->user('customer');
        abort_if(!$customer, 403, 'Please sign in to access billing.');
        abort_if((int) $subscription->customer_id !== (int) $customer->id, 403, 'This billing link does not belong to your account.');

        $manualPayment = ManualPayment::query()
            ->where('subscription_id', $subscription->id)
            ->firstOrFail();

        $validated = $request->validate([
            'transfer_reference' => ['nullable', 'string', 'max:190'],
            'payer_notes' => ['nullable', 'string', 'max:2000'],
            'proof' => ['nullable', 'image', 'max:4096'],
        ]);

        $proofPath = $manualPayment->proof_path;
        $proofUpload = $request->file('proof');
        if ($proofUpload instanceof UploadedFile && $proofUpload->isValid()) {
            $proofPath = $proofUpload->store('billing/manual/proofs', 'public');
        }

        $manualPayment->forceFill([
            'status' => 'submitted',
            'transfer_reference' => $validated['transfer_reference'] ?? null,
            'payer_notes' => $validated['payer_notes'] ?? null,
            'proof_path' => $proofPath,
            'submitted_at' => now(),
        ])->save();

        return redirect()
            ->route('customer.billing.manual.show', $subscription)
            ->with('success', __('Your confirmation was submitted. We will review it shortly.'));
    }
}
