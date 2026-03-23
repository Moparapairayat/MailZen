<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\AffiliateCommission;
use App\Models\AffiliatePayout;
use App\Models\AffiliateReferral;
use App\Models\Customer;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AffiliateController extends Controller
{
    private function affiliateNavItems(): array
    {
        return [
            ['label' => __('Dashboard'), 'route' => 'admin.affiliates.index', 'active' => request()->routeIs('admin.affiliates.index')],
            ['label' => __('Referrals'), 'route' => 'admin.affiliates.referrals', 'active' => request()->routeIs('admin.affiliates.referrals')],
            ['label' => __('Commissions'), 'route' => 'admin.affiliates.commissions', 'active' => request()->routeIs('admin.affiliates.commissions')],
            ['label' => __('Payouts'), 'route' => 'admin.affiliates.payouts', 'active' => request()->routeIs('admin.affiliates.payouts')],
            ['label' => __('Settings'), 'route' => 'admin.affiliates.settings', 'active' => request()->routeIs('admin.affiliates.settings')],
        ];
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $affiliates = Affiliate::query()
            ->with(['customer'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where('code', 'like', '%' . $search . '%')
                    ->orWhereHas('customer', function ($c) use ($search) {
                        $c->where('email', 'like', '%' . $search . '%')
                            ->orWhere('first_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%');
                    });
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'affiliates' => Affiliate::query()->count(),
            'referrals' => AffiliateReferral::query()->count(),
            'commissions' => AffiliateCommission::query()->count(),
            'payouts' => AffiliatePayout::query()->count(),
        ];

        $days = 30;
        $since = now()->subDays($days - 1)->startOfDay();
        $chartRows = Affiliate::query()
            ->select([
                DB::raw('DATE(created_at) as day'),
                DB::raw('COUNT(*) as total'),
            ])
            ->where('created_at', '>=', $since)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy(DB::raw('DATE(created_at)'))
            ->get();

        $chartLabels = $chartRows->pluck('day')->map(fn ($d) => is_string($d) ? $d : (string) $d)->values()->all();
        $chartValues = $chartRows->pluck('total')->map(fn ($v) => (int) $v)->values()->all();

        $navItems = $this->affiliateNavItems();

        return view('admin.affiliates.index', compact('affiliates', 'search', 'stats', 'chartLabels', 'chartValues', 'navItems'));
    }

    public function referrals(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $referrals = AffiliateReferral::query()
            ->with(['affiliate.customer', 'referredCustomer'])
            ->when($search !== '', function ($q) use ($search) {
                $q->whereHas('affiliate', function ($a) use ($search) {
                    $a->where('code', 'like', '%' . $search . '%')
                        ->orWhereHas('customer', function ($c) use ($search) {
                            $c->where('email', 'like', '%' . $search . '%')
                                ->orWhere('first_name', 'like', '%' . $search . '%')
                                ->orWhere('last_name', 'like', '%' . $search . '%');
                        });
                })->orWhereHas('referredCustomer', function ($c) use ($search) {
                    $c->where('email', 'like', '%' . $search . '%')
                        ->orWhere('first_name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%');
                });
            })
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        $navItems = $this->affiliateNavItems();

        return view('admin.affiliates.referrals', compact('referrals', 'search', 'navItems'));
    }

    public function commissions(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $commissions = AffiliateCommission::query()
            ->with(['affiliate.customer', 'referredCustomer', 'subscription', 'payout'])
            ->when($search !== '', function ($q) use ($search) {
                $q->whereHas('affiliate', function ($a) use ($search) {
                    $a->where('code', 'like', '%' . $search . '%')
                        ->orWhereHas('customer', function ($c) use ($search) {
                            $c->where('email', 'like', '%' . $search . '%')
                                ->orWhere('first_name', 'like', '%' . $search . '%')
                                ->orWhere('last_name', 'like', '%' . $search . '%');
                        });
                })->orWhereHas('referredCustomer', function ($c) use ($search) {
                    $c->where('email', 'like', '%' . $search . '%')
                        ->orWhere('first_name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%');
                })->orWhere('event_key', 'like', '%' . $search . '%');
            })
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        $navItems = $this->affiliateNavItems();

        return view('admin.affiliates.commissions', compact('commissions', 'search', 'navItems'));
    }

    public function payouts(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $payouts = AffiliatePayout::query()
            ->with(['affiliate.customer'])
            ->withCount(['commissions'])
            ->when($search !== '', function ($q) use ($search) {
                $q->whereHas('affiliate', function ($a) use ($search) {
                    $a->where('code', 'like', '%' . $search . '%')
                        ->orWhereHas('customer', function ($c) use ($search) {
                            $c->where('email', 'like', '%' . $search . '%')
                                ->orWhere('first_name', 'like', '%' . $search . '%')
                                ->orWhere('last_name', 'like', '%' . $search . '%');
                        });
                })->orWhere('status', 'like', '%' . $search . '%');
            })
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        $navItems = $this->affiliateNavItems();

        return view('admin.affiliates.payouts', compact('payouts', 'search', 'navItems'));
    }

    public function settings(Request $request)
    {
        $navItems = $this->affiliateNavItems();

        $enabled = (bool) Setting::get('affiliate_enabled', false);
        $cookieDays = (int) (Setting::get('affiliate_cookie_days', 30) ?? 30);
        $commissionScope = (string) (Setting::get('affiliate_commission_scope', 'first_payment') ?? 'first_payment');
        $commissionType = (string) (Setting::get('affiliate_commission_type', 'percent') ?? 'percent');
        $commissionRatePercent = (float) (Setting::get('affiliate_commission_rate_percent', 20) ?? 20);
        $commissionFixedAmount = (string) (Setting::get('affiliate_commission_fixed_amount', '10.00') ?? '10.00');
        $minPayoutAmount = (int) (Setting::get('affiliate_min_payout_amount', 50) ?? 50);

        return view('admin.affiliates.settings', compact(
            'navItems',
            'enabled',
            'cookieDays',
            'commissionScope',
            'commissionType',
            'commissionRatePercent',
            'commissionFixedAmount',
            'minPayoutAmount'
        ));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'affiliate_enabled' => ['nullable', 'boolean'],
            'affiliate_cookie_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'affiliate_commission_scope' => ['required', 'in:first_payment,recurring'],
            'affiliate_commission_type' => ['required', 'in:percent,fixed'],
            'affiliate_commission_rate_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'affiliate_commission_fixed_amount' => ['nullable', 'numeric', 'min:0'],
            'affiliate_min_payout_amount' => ['required', 'integer', 'min:0'],
        ]);

        $enabled = (bool) ($validated['affiliate_enabled'] ?? false);

        Setting::set('affiliate_enabled', $enabled ? 1 : 0, 'affiliate', 'boolean');
        Setting::set('affiliate_cookie_days', (int) $validated['affiliate_cookie_days'], 'affiliate', 'integer');
        Setting::set('affiliate_commission_scope', (string) $validated['affiliate_commission_scope'], 'affiliate', 'string');
        Setting::set('affiliate_commission_type', (string) $validated['affiliate_commission_type'], 'affiliate', 'string');

        if ((string) $validated['affiliate_commission_type'] === 'fixed') {
            Setting::set('affiliate_commission_fixed_amount', (string) ($validated['affiliate_commission_fixed_amount'] ?? '0'), 'affiliate', 'string');
        } else {
            Setting::set('affiliate_commission_rate_percent', (string) ($validated['affiliate_commission_rate_percent'] ?? 0), 'affiliate', 'string');
        }

        Setting::set('affiliate_min_payout_amount', (int) $validated['affiliate_min_payout_amount'], 'affiliate', 'integer');

        return redirect()->route('admin.affiliates.settings')->with('success', __('Affiliate settings updated.'));
    }

    public function create(Request $request)
    {
        $navItems = $this->affiliateNavItems();

        return view('admin.affiliates.create', compact('navItems'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_email' => ['required', 'email', 'exists:customers,email'],
            'code' => ['nullable', 'string', 'max:64'],
            'destination_url' => ['nullable', 'string', 'max:2048'],
            'commission_rate_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', 'in:pending,approved,blocked'],
            'payout_method' => ['nullable', 'in:bank_transfer,paypal,payoneer'],

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

        $customer = Customer::query()->where('email', $validated['customer_email'])->first();
        abort_unless($customer, 422);

        $code = isset($validated['code']) && is_string($validated['code']) ? trim($validated['code']) : '';
        if ($code === '') {
            for ($i = 0; $i < 10; $i++) {
                $candidate = Str::upper(Str::random(10));
                if (!Affiliate::query()->where('code', $candidate)->exists()) {
                    $code = $candidate;
                    break;
                }
            }
        }

        if ($code === '') {
            $code = Str::upper(Str::uuid()->toString());
        }

        $method = isset($validated['payout_method']) && is_string($validated['payout_method'])
            ? (string) $validated['payout_method']
            : 'bank_transfer';

        if ($method === 'paypal' && trim((string) ($validated['paypal_email'] ?? '')) === '') {
            return back()->withErrors(['paypal_email' => __('PayPal payout requires a PayPal email.')])->withInput();
        }

        if ($method === 'payoneer' && trim((string) ($validated['payoneer_email'] ?? '')) === '' && trim((string) ($validated['payoneer_account_id'] ?? '')) === '') {
            return back()->withErrors(['payoneer_email' => __('Payoneer payout requires an email or account id.')])->withInput();
        }

        if ($method === 'bank_transfer') {
            if (trim((string) ($validated['bank_account_holder_name'] ?? '')) === ''
                || trim((string) ($validated['bank_name'] ?? '')) === ''
                || trim((string) ($validated['bank_account_number'] ?? '')) === '') {
                return back()->withErrors(['bank_account_number' => __('Bank transfer requires account holder name, bank name, and account number.')])->withInput();
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

        $status = (string) $validated['status'];

        Affiliate::updateOrCreate(
            ['customer_id' => $customer->id],
            [
                'code' => $code,
                'status' => $status,
                'destination_url' => $validated['destination_url'] ?? null,
                'commission_rate_percent' => $validated['commission_rate_percent'] ?? null,
                'payout_details' => $payoutDetails,
                'approved_at' => $status === 'approved' ? now() : null,
                'blocked_at' => $status === 'blocked' ? now() : null,
            ]
        );

        return redirect()->route('admin.affiliates.index')->with('success', __('Affiliate saved.'));
    }

    public function approve(Request $request, Affiliate $affiliate)
    {
        if ($affiliate->status !== 'approved') {
            $affiliate->forceFill([
                'status' => 'approved',
                'approved_at' => now(),
                'blocked_at' => null,
            ])->save();
        }

        return redirect()
            ->route('admin.affiliates.index')
            ->with('success', __('Affiliate approved.'));
    }
}
