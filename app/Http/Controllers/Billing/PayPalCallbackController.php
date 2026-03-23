<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Services\Billing\PayPalPaymentService;
use Illuminate\Http\Request;

class PayPalCallbackController extends Controller
{
    public function __invoke(Request $request)
    {
        $orderId = $request->query('token');
        if (!is_string($orderId) || trim($orderId) === '') {
            $orderId = $request->query('order_id');
        }

        if (!is_string($orderId) || trim($orderId) === '') {
            return redirect()->route('customer.billing.index')->with('error', 'Missing PayPal order id.');
        }

        try {
            $service = app(PayPalPaymentService::class);
            $captured = $service->captureOrderAndSync($orderId);
            $subscription = $service->applyCapturedOrderData($captured);
        } catch (\Throwable $e) {
            return redirect()->route('customer.billing.index')->with('error', $e->getMessage());
        }

        if (!$subscription) {
            return redirect()->route('customer.billing.index')->with('error', 'Unable to match subscription for this PayPal order.');
        }

        $status = (string) ($captured['status'] ?? '');

        if (in_array($status, ['COMPLETED', 'APPROVED'], true)) {
            return redirect()->route('customer.billing.success', ['provider' => 'paypal', 'order_id' => $orderId]);
        }

        return redirect()->route('customer.billing.index')->with('error', 'Payment not completed.');
    }
}
