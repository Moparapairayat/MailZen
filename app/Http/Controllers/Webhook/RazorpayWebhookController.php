<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\Billing\RazorpayPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RazorpayWebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {
            $service = app()->make(RazorpayPaymentService::class);
            $service->handleWebhook($request);
        } catch (\Throwable $exception) {
            Log::error('Razorpay webhook processing failed', ['message' => $exception->getMessage()]);
            return response()->json(['error' => 'Webhook processing failed'], 400);
        }

        return response()->json(['status' => 'ok']);
    }
}
