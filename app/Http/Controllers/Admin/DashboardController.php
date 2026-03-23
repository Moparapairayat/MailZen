<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Customer;
use App\Models\CustomerLoginEvent;
use App\Models\EmailList;
use App\Models\IpGeolocation;
use App\Models\ListSubscriber;
use App\Models\Subscription;
use App\Models\Template;
use App\Models\User;
use App\Models\Setting;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    /**
     * Get currency symbol for given currency code.
     */
    private function getCurrencySymbol(string $currencyCode): string
    {
        return match (strtoupper($currencyCode)) {
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'CNY' => '¥',
            'INR' => '₹',
            'AUD' => 'A$',
            'CAD' => 'C$',
            'CHF' => 'Fr',
            'SEK' => 'kr',
            'NOK' => 'kr',
            'DKK' => 'kr',
            'PLN' => 'zł',
            'CZK' => 'Kč',
            'HUF' => 'Ft',
            'RON' => 'lei',
            'BGN' => 'лв',
            'HRK' => 'kn',
            'RUB' => '₽',
            'TRY' => '₺',
            'ILS' => '₪',
            'THB' => '฿',
            'SGD' => 'S$',
            'HKD' => 'HK$',
            'NZD' => 'NZ$',
            'ZAR' => 'R',
            'MXN' => '$',
            'BRL' => 'R$',
            'ARS' => '$',
            'CLP' => '$',
            'COP' => '$',
            'PEN' => 'S/',
            'UYU' => '$',
            'KRW' => '₩',
            'VND' => '₫',
            'IDR' => 'Rp',
            'MYR' => 'RM',
            'PHP' => '₱',
            default => $currencyCode . ' ',
        };
    }

    /**
     * Display the admin dashboard.
     */
    public function index(Request $request)
    {
        $range = (string) $request->query('range', '7d');
        $now = now();

        $startDate = null;
        $endDate = null;

        if ($range === '30d') {
            $startDate = $now->copy()->subDays(29)->startOfDay();
            $endDate = $now->copy()->endOfDay();
        } elseif ($range === 'custom') {
            $from = $request->query('from');
            $to = $request->query('to');

            $startDate = $from ? Carbon::parse($from)->startOfDay() : $now->copy()->subDays(6)->startOfDay();
            $endDate = $to ? Carbon::parse($to)->endOfDay() : $now->copy()->endOfDay();
        } else {
            $range = '7d';
            $startDate = $now->copy()->subDays(6)->startOfDay();
            $endDate = $now->copy()->endOfDay();
        }

        if ($startDate->greaterThan($endDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
            $range = 'custom';
        }

        $cacheKey = 'admin_dashboard:' . md5(implode('|', [
            $range,
            $startDate->toDateTimeString(),
            $endDate->toDateTimeString(),
        ]));

        $cached = Cache::remember($cacheKey, now()->addSeconds(30), function () use ($startDate, $endDate) {
            $usersCount = Customer::whereBetween('created_at', [$startDate, $endDate])->count();
            $subscribersCount = ListSubscriber::whereBetween('created_at', [$startDate, $endDate])->count();

            $earnings = (float) Subscription::query()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('status', ['active', 'trialing', 'past_due'])
                ->sum('price');

            $campaignsCreated = Campaign::whereBetween('created_at', [$startDate, $endDate])->count();
            $campaignsRan = Campaign::whereNotNull('started_at')
                ->whereBetween('started_at', [$startDate, $endDate])
                ->count();
            $listsCreated = EmailList::whereBetween('created_at', [$startDate, $endDate])->count();
            $subscriptionsCancelled = Subscription::whereNotNull('cancelled_at')
                ->whereBetween('cancelled_at', [$startDate, $endDate])
                ->count();
            $templatesCreated = Template::whereBetween('created_at', [$startDate, $endDate])->count();

            $dayTotals = Subscription::query()
                ->whereIn('status', ['active', 'trialing', 'past_due'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DATE(created_at) as day, SUM(price) as total')
                ->groupBy('day')
                ->pluck('total', 'day');

            $days = CarbonPeriod::create($startDate->copy()->startOfDay(), '1 day', $endDate->copy()->startOfDay());
            $earningsChartLabels = [];
            $earningsChartValues = [];

            foreach ($days as $day) {
                $key = $day->toDateString();
                $earningsChartLabels[] = $day->format('M j');
                $earningsChartValues[] = (float) ($dayTotals[$key] ?? 0);
            }

            $perSourceLimit = 10;

            $recentActivity = collect()
                ->merge(
                    Customer::query()
                        ->whereBetween('created_at', [$startDate, $endDate])
                        ->latest()
                        ->limit($perSourceLimit)
                        ->get(['id', 'created_at', 'email'])
                        ->map(fn ($c) => [
                            'at' => $c->created_at,
                            'label' => 'New user',
                            'detail' => $c->email,
                        ])
                )
                ->merge(
                    ListSubscriber::query()
                        ->whereBetween('created_at', [$startDate, $endDate])
                        ->latest()
                        ->limit($perSourceLimit)
                        ->get(['id', 'created_at', 'email'])
                        ->map(fn ($s) => [
                            'at' => $s->created_at,
                            'label' => 'New subscriber',
                            'detail' => $s->email,
                        ])
                )
                ->merge(
                    Campaign::query()
                        ->whereBetween('created_at', [$startDate, $endDate])
                        ->latest()
                        ->limit($perSourceLimit)
                        ->get(['id', 'created_at', 'name'])
                        ->map(fn ($c) => [
                            'at' => $c->created_at,
                            'label' => 'Campaign created',
                            'detail' => $c->name,
                        ])
                )
                ->merge(
                    EmailList::query()
                        ->whereBetween('created_at', [$startDate, $endDate])
                        ->latest()
                        ->limit($perSourceLimit)
                        ->get(['id', 'created_at', 'name'])
                        ->map(fn ($l) => [
                            'at' => $l->created_at,
                            'label' => 'Email list created',
                            'detail' => $l->name,
                        ])
                )
                ->merge(
                    Template::query()
                        ->whereBetween('created_at', [$startDate, $endDate])
                        ->latest()
                        ->limit($perSourceLimit)
                        ->get(['id', 'created_at', 'name'])
                        ->map(fn ($t) => [
                            'at' => $t->created_at,
                            'label' => 'Template created',
                            'detail' => $t->name,
                        ])
                )
                ->merge(
                    Subscription::query()
                        ->whereNotNull('cancelled_at')
                        ->whereBetween('cancelled_at', [$startDate, $endDate])
                        ->latest('cancelled_at')
                        ->limit($perSourceLimit)
                        ->get(['id', 'cancelled_at', 'plan_name'])
                        ->map(fn ($s) => [
                            'at' => $s->cancelled_at,
                            'label' => 'Subscription cancelled',
                            'detail' => $s->plan_name,
                        ])
                )
                ->filter(fn ($item) => $item['at'] !== null)
                ->sortByDesc('at')
                ->take(5)
                ->values();

            $worldVisitors = [];
            $worldVisitorsCacheKey = 'admin_dashboard_world_visitors:' . md5(implode('|', [
                $startDate->toDateTimeString(),
                $endDate->toDateTimeString(),
            ]));

            $worldVisitors = Cache::remember($worldVisitorsCacheKey, now()->addSeconds(60), function () use ($startDate, $endDate) {
                if (!Schema::hasTable('customer_login_events') || !Schema::hasTable('ip_geolocations')) {
                    return [];
                }

                $visitorRows = CustomerLoginEvent::query()
                    ->whereNotNull('ip_geolocation_id')
                    ->whereBetween('logged_in_at', [$startDate, $endDate])
                    ->selectRaw('ip_geolocation_id, COUNT(*) as visitors, MAX(logged_in_at) as last_seen')
                    ->groupBy('ip_geolocation_id')
                    ->orderByDesc('last_seen')
                    ->limit(60)
                    ->get();

                $geoById = IpGeolocation::query()
                    ->whereIn('id', $visitorRows->pluck('ip_geolocation_id')->filter()->unique()->values())
                    ->get()
                    ->keyBy('id');

                return $visitorRows
                    ->map(function ($row) use ($geoById) {
                        $geo = $geoById->get((int) $row->ip_geolocation_id);
                        if (!$geo || $geo->latitude === null || $geo->longitude === null) {
                            return null;
                        }

                        $labelParts = array_values(array_filter([
                            $geo->city,
                            $geo->region,
                            $geo->country_code,
                        ], fn ($v) => is_string($v) && trim($v) !== ''));

                        return [
                            'lat' => (float) $geo->latitude,
                            'lng' => (float) $geo->longitude,
                            'country' => $geo->country_code,
                            'label' => $labelParts ? implode(', ', $labelParts) : ($geo->country_code ?: 'Visitor'),
                            'visitors' => (int) ($row->visitors ?? 0),
                        ];
                    })
                    ->filter()
                    ->values()
                    ->all();
            });

            return [
                'usersCount' => $usersCount,
                'subscribersCount' => $subscribersCount,
                'earnings' => $earnings,
                'campaignsCreated' => $campaignsCreated,
                'campaignsRan' => $campaignsRan,
                'listsCreated' => $listsCreated,
                'subscriptionsCancelled' => $subscriptionsCancelled,
                'templatesCreated' => $templatesCreated,
                'earningsChartLabels' => $earningsChartLabels,
                'earningsChartValues' => $earningsChartValues,
                'recentActivity' => $recentActivity,
                'worldVisitors' => $worldVisitors,
                'totalUsers' => User::count(),
            ];
        });

        // Get currency setting
        $currency = Setting::get('billing_currency', 'USD');
        $currencySymbol = $this->getCurrencySymbol($currency);

        return view('admin.dashboard.index', [
            'range' => $range,
            'startDate' => $startDate,
            'endDate' => $endDate,

            'usersCount' => $cached['usersCount'] ?? 0,
            'subscribersCount' => $cached['subscribersCount'] ?? 0,
            'earnings' => $cached['earnings'] ?? 0,
            'campaignsCreated' => $cached['campaignsCreated'] ?? 0,
            'campaignsRan' => $cached['campaignsRan'] ?? 0,
            'listsCreated' => $cached['listsCreated'] ?? 0,
            'subscriptionsCancelled' => $cached['subscriptionsCancelled'] ?? 0,
            'templatesCreated' => $cached['templatesCreated'] ?? 0,

            'earningsChartLabels' => $cached['earningsChartLabels'] ?? [],
            'earningsChartValues' => $cached['earningsChartValues'] ?? [],
            'recentActivity' => $cached['recentActivity'] ?? [],
            'worldVisitors' => $cached['worldVisitors'] ?? [],

            'totalUsers' => $cached['totalUsers'] ?? 0,
            'currencySymbol' => $currencySymbol,
        ]);
    }
}

