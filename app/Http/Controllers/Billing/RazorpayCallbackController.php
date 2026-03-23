<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RazorpayCallbackController extends Controller
{
    public function __invoke(Request $request)
    {
        $status = $request->query('payment_link_status');

        if (is_string($status) && strtolower($status) === 'paid') {
            return redirect()->route('customer.billing.index')->with('success', 'Payment successful.');
        }

        if (is_string($status) && strtolower($status) === 'cancelled') {
            return redirect()->route('customer.billing.index')->with('error', 'Payment cancelled.');
        }

        return redirect()->route('customer.billing.index')->with('success', 'Payment received.');
    }
}
