<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Services\Billing\FlutterwavePaymentService;
use Illuminate\Http\Request;

class FlutterwaveCallbackController extends Controller
{
    public function __invoke(Request $request)
    {
        $transactionId = $request->query('transaction_id');
        if (!is_string($transactionId) || trim($transactionId) === '') {
            return redirect()->route('customer.billing.index')->with('error', 'Missing transaction id.');
        }

        try {
            $service = app(FlutterwavePaymentService::class);
            $verified = $service->verifyTransactionAndSync($transactionId);
            $subscription = $service->applyVerifiedTransactionData($verified);
        } catch (\Throwable $e) {
            return redirect()->route('customer.billing.index')->with('error', $e->getMessage());
        }

        $meta = $this->normalizeMeta($verified['meta'] ?? null);
        $successUrl = $meta['success_url'] ?? null;
        $cancelUrl = $meta['cancel_url'] ?? null;

        if (!$subscription) {
            return redirect()->route('customer.billing.index')->with('error', 'Unable to match subscription for this transaction.');
        }

        $status = (string) ($verified['status'] ?? '');

        if ($status === 'successful') {
            $target = is_string($successUrl) && trim($successUrl) !== '' ? $successUrl : route('customer.billing.index');
            return redirect()->to($target)->with('success', 'Payment successful.');
        }

        $target = is_string($cancelUrl) && trim($cancelUrl) !== '' ? $cancelUrl : route('customer.billing.index');
        return redirect()->to($target)->with('error', 'Payment not completed.');
    }

    private function normalizeMeta(mixed $meta): array
    {
        if (!is_array($meta)) {
            return [];
        }

        $allStringKeys = true;
        foreach (array_keys($meta) as $k) {
            if (!is_string($k)) {
                $allStringKeys = false;
                break;
            }
        }

        if ($allStringKeys) {
            return $meta;
        }

        $normalized = [];
        foreach ($meta as $item) {
            if (is_array($item) && isset($item['name'], $item['value'])) {
                $normalized[(string) $item['name']] = $item['value'];
            }
        }

        return $normalized;
    }
}
