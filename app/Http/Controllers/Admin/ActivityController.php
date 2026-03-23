<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Customer;
use App\Models\EmailList;
use App\Models\ListSubscriber;
use App\Models\Subscription;
use App\Models\Template;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ActivityController extends Controller
{
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

        $perSourceLimit = 100;

        $items = collect()
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
            ->values();

        $perPage = 20;
        $page = max(1, (int) $request->query('page', 1));
        $slice = $items->slice(($page - 1) * $perPage, $perPage)->values();

        $activities = new LengthAwarePaginator(
            $slice,
            $items->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('admin.activities.index', [
            'range' => $range,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'activities' => $activities,
        ]);
    }
}
