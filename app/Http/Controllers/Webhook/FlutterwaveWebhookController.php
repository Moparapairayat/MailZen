<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\Billing\FlutterwavePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FlutterwaveWebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {
            $service = app()->make(FlutterwavePaymentService::class);
            $service->handleWebhook($request);
        } catch (\Throwable $exception) {
            Log::error('Flutterwave webhook processing failed', ['message' => $exception->getMessage()]);
            return response()->json(['error' => 'Webhook processing failed'], 400);
        }

        return response()->json(['status' => 'ok']);
    }
}
