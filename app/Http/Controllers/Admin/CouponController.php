<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Stripe\StripeClient;

class CouponController extends Controller
{
    private function stripe(): StripeClient
    {
        return app(StripeClient::class);
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->get('q', ''));

        $coupons = Coupon::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where('code', 'like', '%' . strtoupper($search) . '%')
                    ->orWhere('name', 'like', '%' . $search . '%');
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.coupons.index', compact('coupons', 'search'));
    }

    public function create()
    {
        return view('admin.coupons.create', [
            'coupon' => new Coupon(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['code'] = strtoupper($data['code']);

        $coupon = Coupon::create($data);

        try {
            $this->syncStripe($coupon);
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.coupons.edit', $coupon)
                ->with('error', __('Coupon created locally, but Stripe sync failed: ') . $e->getMessage());
        }

        return redirect()->route('admin.coupons.index')->with('success', __('Coupon created.'));
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $data = $this->validateData($request, $coupon->id);
        $data['code'] = strtoupper($data['code']);

        $coupon->update($data);

        try {
            $this->syncStripe($coupon);
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.coupons.edit', $coupon)
                ->with('error', __('Coupon updated locally, but Stripe sync failed: ') . $e->getMessage());
        }

        return redirect()->route('admin.coupons.index')->with('success', __('Coupon updated.'));
    }

    public function destroy(Coupon $coupon)
    {
        try {
            $stripe = $this->stripe();
            if ($coupon->stripe_promotion_code_id) {
                $stripe->promotionCodes->update($coupon->stripe_promotion_code_id, [
                    'active' => false,
                ]);
            }
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.coupons.index')
                ->with('error', __('Failed to disable Stripe promotion code: ') . $e->getMessage());
        }

        $coupon->delete();

        return redirect()->route('admin.coupons.index')->with('success', __('Coupon deleted.'));
    }

    private function validateData(Request $request, ?int $couponId = null): array
    {
        $type = $request->input('type');

        $rules = [
            'code' => ['required', 'string', 'max:64', Rule::unique('coupons', 'code')->ignore($couponId)],
            'name' => ['nullable', 'string', 'max:255'],
            'type' => ['required', Rule::in(['percent', 'fixed'])],
            'duration' => ['required', Rule::in(['once', 'repeating', 'forever'])],
            'duration_in_months' => ['nullable', 'integer', 'min:1', 'max:120'],
            'max_redemptions' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['boolean'],
        ];

        if ($type === 'percent') {
            $rules['percent_off'] = ['required', 'numeric', 'min:0.01', 'max:100'];
            $rules['amount_off'] = ['nullable'];
            $rules['currency'] = ['nullable'];
        } else {
            $rules['amount_off'] = ['required', 'numeric', 'min:0.01'];
            $rules['currency'] = ['required', 'string', 'size:3'];
            $rules['percent_off'] = ['nullable'];
        }

        $data = $request->validate($rules);

        $data['currency'] = isset($data['currency']) ? strtoupper((string) $data['currency']) : null;

        if (($data['duration'] ?? null) !== 'repeating') {
            $data['duration_in_months'] = null;
        }

        if (($data['type'] ?? null) === 'percent') {
            $data['amount_off'] = null;
            $data['currency'] = null;
        } else {
            $data['percent_off'] = null;
        }

        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        return $data;
    }

    private function syncStripe(Coupon $coupon): void
    {
        $stripe = $this->stripe();
        if (!$coupon->stripe_coupon_id) {
            $payload = [
                'duration' => $coupon->duration,
                'name' => $coupon->name ?: $coupon->code,
                'metadata' => [
                    'local_coupon_id' => (string) $coupon->id,
                    'code' => $coupon->code,
                ],
            ];

            if ($coupon->type === 'percent') {
                $payload['percent_off'] = (float) $coupon->percent_off;
            } else {
                $payload['amount_off'] = (int) round(((float) $coupon->amount_off) * 100);
                $payload['currency'] = strtolower((string) $coupon->currency);
            }

            if ($coupon->duration === 'repeating' && $coupon->duration_in_months) {
                $payload['duration_in_months'] = $coupon->duration_in_months;
            }

            $stripeCoupon = $stripe->coupons->create($payload);

            $coupon->update([
                'stripe_coupon_id' => $stripeCoupon->id,
            ]);
        }

        if (!$coupon->stripe_promotion_code_id) {
            $promotionPayload = [
                'coupon' => $coupon->stripe_coupon_id,
                'code' => Str::upper($coupon->code),
                'active' => (bool) $coupon->is_active,
                'metadata' => [
                    'local_coupon_id' => (string) $coupon->id,
                ],
            ];

            if ($coupon->max_redemptions) {
                $promotionPayload['max_redemptions'] = (int) $coupon->max_redemptions;
            }

            if ($coupon->ends_at) {
                $promotionPayload['expires_at'] = $coupon->ends_at->timestamp;
            }

            $promotion = $stripe->promotionCodes->create($promotionPayload);

            $coupon->update([
                'stripe_promotion_code_id' => $promotion->id,
            ]);
        } else {
            $stripe->promotionCodes->update($coupon->stripe_promotion_code_id, [
                'active' => (bool) $coupon->is_active,
            ]);
        }
    }
}
