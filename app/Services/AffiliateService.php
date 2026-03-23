<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\AffiliateReferral;
use App\Models\Customer;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class AffiliateService
{
    public const REF_COOKIE_NAME = 'mp_affiliate_ref';

    public function captureReferral(Request $request): void
    {
        $enabled = filter_var(Setting::get('affiliate_enabled', false), FILTER_VALIDATE_BOOLEAN);
        if (!$enabled) {
            return;
        }

        $code = $request->query('ref');
        if (!is_string($code) || trim($code) === '') {
            return;
        }

        $code = trim($code);

        $affiliate = Affiliate::query()
            ->where('code', $code)
            ->where('status', 'approved')
            ->first();

        if (!$affiliate) {
            return;
        }

        $days = (int) (Setting::get('affiliate_cookie_days', 30) ?? 30);
        if ($days <= 0) {
            $days = 30;
        }

        $request->session()->put('affiliate_visitor_id', $request->session()->get('affiliate_visitor_id') ?: Str::uuid()->toString());
        $visitorId = (string) $request->session()->get('affiliate_visitor_id');

        Cookie::queue(self::REF_COOKIE_NAME, (string) $affiliate->id, $days * 24 * 60);

        AffiliateReferral::create([
            'affiliate_id' => $affiliate->id,
            'visitor_id' => $visitorId,
            'landing_url' => $request->fullUrl(),
            'referrer_url' => $request->headers->get('referer'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    public function attributeCustomerFromRequest(Customer $customer, Request $request): void
    {
        if ($customer->referred_by_affiliate_id) {
            return;
        }

        $enabled = filter_var(Setting::get('affiliate_enabled', false), FILTER_VALIDATE_BOOLEAN);
        if (!$enabled) {
            return;
        }

        $affiliateId = $request->cookie(self::REF_COOKIE_NAME);
        if (!is_string($affiliateId) || trim($affiliateId) === '') {
            return;
        }

        $affiliateId = (int) $affiliateId;
        if ($affiliateId <= 0) {
            return;
        }

        $affiliate = Affiliate::query()
            ->whereKey($affiliateId)
            ->where('status', 'approved')
            ->first();

        if (!$affiliate) {
            return;
        }

        $customer->forceFill([
            'referred_by_affiliate_id' => $affiliate->id,
            'referred_at' => now(),
        ])->save();

        $visitorId = $request->session()->get('affiliate_visitor_id');
        if (is_string($visitorId) && trim($visitorId) !== '') {
            $ref = AffiliateReferral::query()
                ->where('affiliate_id', $affiliate->id)
                ->where('visitor_id', $visitorId)
                ->whereNull('referred_customer_id')
                ->latest('id')
                ->first();

            if ($ref) {
                $ref->update([
                    'referred_customer_id' => $customer->id,
                    'referred_at' => now(),
                ]);
            }
        }
    }
}
