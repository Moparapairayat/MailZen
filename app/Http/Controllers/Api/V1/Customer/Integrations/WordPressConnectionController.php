<?php

namespace App\Http\Controllers\Api\V1\Customer\Integrations;

use App\Http\Controllers\Controller;
use App\Models\WordPressIntegration;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WordPressConnectionController extends Controller
{
    public function show(Request $request)
    {
        $customer = $request->user('sanctum');
        abort_if(!$customer, 401);

        $integration = WordPressIntegration::query()->firstOrCreate(
            ['customer_id' => $customer->id],
            [
                'signing_secret' => Str::random(64),
                'last_rotated_at' => now(),
            ]
        );

        return response()->json([
            'data' => [
                'signing_secret' => (string) $integration->signing_secret,
                'last_rotated_at' => $integration->last_rotated_at,
            ],
        ]);
    }

    public function rotate(Request $request)
    {
        $customer = $request->user('sanctum');
        abort_if(!$customer, 401);

        $integration = WordPressIntegration::query()->firstOrCreate(
            ['customer_id' => $customer->id],
            [
                'signing_secret' => Str::random(64),
                'last_rotated_at' => now(),
            ]
        );

        $integration->update([
            'signing_secret' => Str::random(64),
            'last_rotated_at' => now(),
        ]);

        return response()->json([
            'data' => [
                'signing_secret' => (string) $integration->signing_secret,
                'last_rotated_at' => $integration->last_rotated_at,
            ],
        ]);
    }
}
