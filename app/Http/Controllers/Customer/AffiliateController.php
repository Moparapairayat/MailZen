<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\AffiliateCommission;
use App\Models\AffiliateReferral;
use App\Models\AffiliatePayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AffiliateController extends Controller
{
    public function index(Request $request)
    {
        $customer = $request->user('customer');

        $affiliate = null;
        if ($customer) {
            $affiliate = Affiliate::query()->where('customer_id', $customer->id)->first();
        }

        if (!$affiliate) {
            $referralCount = 0;
            $commissionCount = 0;
            $activeReferrals = 0;
            $commissionRevenue = 0;
            $upcomingPayoutAmount = 0;
            $recentReferrals = collect();
            $chartLabels = [];
            $chartValues = [];

            return view('customer.affiliate.index', compact(
                'affiliate',
                'referralCount',
                'commissionCount',
                'activeReferrals',
                'commissionRevenue',
                'upcomingPayoutAmount',
                'recentReferrals',
                'chartLabels',
                'chartValues'
            ));
        }

        $range = trim((string) $request->query('range', 'today'));
        $range = in_array($range, ['today', '7d', '30d', '1y'], true) ? $range : 'today';

        $start = match ($range) {
            '7d' => now()->subDays(6)->startOfDay(),
            '30d' => now()->subDays(29)->startOfDay(),
            '1y' => now()->subDays(364)->startOfDay(),
            default => now()->startOfDay(),
        };

        $referralCount = AffiliateReferral::query()->where('affiliate_id', $affiliate->id)->count();
        $activeReferrals = AffiliateReferral::query()
            ->where('affiliate_id', $affiliate->id)
            ->whereNotNull('referred_customer_id')
            ->count();

        $commissionCount = AffiliateCommission::query()->where('affiliate_id', $affiliate->id)->count();
        $commissionRevenue = (float) AffiliateCommission::query()
            ->where('affiliate_id', $affiliate->id)
            ->sum('commission_amount');

        $upcomingPayoutAmount = (float) AffiliateCommission::query()
            ->where('affiliate_id', $affiliate->id)
            ->whereNull('payout_id')
            ->whereIn('status', ['pending', 'approved'])
            ->sum('commission_amount');

        $recentReferrals = AffiliateReferral::query()
            ->where('affiliate_id', $affiliate->id)
            ->with(['referredCustomer'])
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $rows = AffiliateCommission::query()
            ->select([
                DB::raw('DATE(created_at) as day'),
                DB::raw('SUM(commission_amount) as total'),
            ])
            ->where('affiliate_id', $affiliate->id)
            ->where('created_at', '>=', $start)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy(DB::raw('DATE(created_at)'))
            ->get();

        $chartLabels = $rows->pluck('day')->map(fn ($d) => is_string($d) ? $d : (string) $d)->values()->all();
        $chartValues = $rows->pluck('total')->map(fn ($v) => (float) $v)->values()->all();

        return view('customer.affiliate.index', compact(
            'affiliate',
            'range',
            'referralCount',
            'activeReferrals',
            'commissionCount',
            'commissionRevenue',
            'upcomingPayoutAmount',
            'recentReferrals',
            'chartLabels',
            'chartValues'
        ));
    }

    public function payments(Request $request)
    {
        $customer = $request->user('customer');
        abort_unless($customer, 403);

        $affiliate = Affiliate::query()->where('customer_id', $customer->id)->first();
        if (!$affiliate) {
            return redirect()->route('customer.affiliate.index');
        }

        $payouts = AffiliatePayout::query()
            ->where('affiliate_id', $affiliate->id)
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $commissions = AffiliateCommission::query()
            ->where('affiliate_id', $affiliate->id)
            ->orderByDesc('id')
            ->paginate(20, ['*'], 'commissions_page')
            ->withQueryString();

        return view('customer.affiliate.payments', compact('affiliate', 'payouts', 'commissions'));
    }

    public function updatePayoutSettings(Request $request)
    {
        $customer = $request->user('customer');
        abort_unless($customer, 403);

        $affiliate = Affiliate::query()->where('customer_id', $customer->id)->first();
        if (!$affiliate) {
            return redirect()->route('customer.affiliate.index');
        }

        $validated = $request->validate([
            'payout_method' => ['required', 'in:bank_transfer,paypal,payoneer'],

            'bank_account_holder_name' => ['nullable', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:255'],
            'bank_iban' => ['nullable', 'string', 'max:255'],
            'bank_swift' => ['nullable', 'string', 'max:255'],
            'bank_country' => ['nullable', 'string', 'max:255'],

            'paypal_email' => ['nullable', 'email', 'max:255'],

            'payoneer_email' => ['nullable', 'email', 'max:255'],
            'payoneer_account_id' => ['nullable', 'string', 'max:255'],
        ]);

        $method = (string) $validated['payout_method'];

        if ($method === 'bank_transfer') {
            if (trim((string) ($validated['bank_account_holder_name'] ?? '')) === ''
                || trim((string) ($validated['bank_name'] ?? '')) === ''
                || trim((string) ($validated['bank_account_number'] ?? '')) === '') {
                return back()->withErrors([
                    'bank_account_number' => __('Bank transfer requires account holder name, bank name, and account number.'),
                ])->withInput();
            }
        }

        if ($method === 'paypal') {
            if (trim((string) ($validated['paypal_email'] ?? '')) === '') {
                return back()->withErrors([
                    'paypal_email' => __('PayPal payout requires a PayPal email.'),
                ])->withInput();
            }
        }

        if ($method === 'payoneer') {
            if (trim((string) ($validated['payoneer_email'] ?? '')) === '' && trim((string) ($validated['payoneer_account_id'] ?? '')) === '') {
                return back()->withErrors([
                    'payoneer_email' => __('Payoneer payout requires an email or account id.'),
                ])->withInput();
            }
        }

        $payoutDetails = [
            'method' => $method,
            'bank' => [
                'account_holder_name' => $validated['bank_account_holder_name'] ?? null,
                'bank_name' => $validated['bank_name'] ?? null,
                'account_number' => $validated['bank_account_number'] ?? null,
                'iban' => $validated['bank_iban'] ?? null,
                'swift' => $validated['bank_swift'] ?? null,
                'country' => $validated['bank_country'] ?? null,
            ],
            'paypal' => [
                'email' => $validated['paypal_email'] ?? null,
            ],
            'payoneer' => [
                'email' => $validated['payoneer_email'] ?? null,
                'account_id' => $validated['payoneer_account_id'] ?? null,
            ],
        ];

        $affiliate->forceFill([
            'payout_details' => $payoutDetails,
        ])->save();

        return redirect()->route('customer.affiliate.payments')->with('success', __('Payout settings updated.'));
    }

    public function apply(Request $request)
    {
        $customer = $request->user('customer');

        abort_unless($customer, 403);

        $existing = Affiliate::query()->where('customer_id', $customer->id)->first();
        if ($existing) {
            return redirect()
                ->route('customer.affiliate.index')
                ->with('success', __('Affiliate profile already exists.'));
        }

        $code = null;
        for ($i = 0; $i < 10; $i++) {
            $candidate = Str::upper(Str::random(10));
            if (!Affiliate::query()->where('code', $candidate)->exists()) {
                $code = $candidate;
                break;
            }
        }

        if (!$code) {
            $code = Str::upper(Str::uuid()->toString());
        }

        Affiliate::create([
            'customer_id' => $customer->id,
            'code' => $code,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('customer.affiliate.index')
            ->with('success', __('Affiliate request submitted. Awaiting approval.'));
    }
}
