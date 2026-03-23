<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Billing\StripePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class WebhookController extends Controller
{
    public function handleStripe(Request $request)
    {
        try {
            $stripe = app()->make(StripeClient::class);
            (new StripePaymentService($stripe))->handleWebhook($request);
        } catch (\Throwable $exception) {
            Log::error('Stripe webhook processing failed', ['message' => $exception->getMessage()]);
            return response()->json(['error' => 'Webhook processing failed'], 400);
        }

        return response()->json(['status' => 'ok']);
    }
}

