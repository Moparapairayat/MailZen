<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\ListSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Display the customer dashboard.
     */
    public function index()
    {
        $customer = auth()->guard('customer')->user();

        $customerTimezone = $customer->timezone ?: config('app.timezone');
        $customerLocalTime = now()->setTimezone($customerTimezone);

        $cacheKey = 'customer_dashboard:' . (int) $customer->id;

        $cached = Cache::remember($cacheKey, now()->addSeconds(30), function () use ($customer) {
            $listIds = $customer->emailLists()->pluck('id');

            // Basic stats
            $emailListsCount = $listIds->count();
            $subscribersCount = $listIds->isEmpty()
                ? 0
                : ListSubscriber::whereIn('list_id', $listIds)->count();
            $campaignsCount = $customer->campaigns()->count();

            // Recent activity
            $recentCampaigns = $customer->campaigns()
                ->latest()
                ->limit(5)
                ->get();

            $recentSubscribers = $listIds->isEmpty()
                ? collect()
                : ListSubscriber::whereIn('list_id', $listIds)
                    ->latest()
                    ->limit(5)
                    ->get();

            return [
                'emailListsCount' => $emailListsCount,
                'subscribersCount' => $subscribersCount,
                'campaignsCount' => $campaignsCount,
                'recentCampaigns' => $recentCampaigns,
                'recentSubscribers' => $recentSubscribers,
            ];
        });

        return view('customer.dashboard.index', [
            'emailListsCount' => $cached['emailListsCount'] ?? 0,
            'subscribersCount' => $cached['subscribersCount'] ?? 0,
            'campaignsCount' => $cached['campaignsCount'] ?? 0,
            'recentCampaigns' => $cached['recentCampaigns'] ?? [],
            'recentSubscribers' => $cached['recentSubscribers'] ?? [],
            'customerTimezone' => $customerTimezone,
            'customerLocalTime' => $customerLocalTime,
        ]);
    }
}

