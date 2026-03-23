<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Customer;
use App\Models\EmailList;
use App\Models\User;
use App\Models\DeliveryServer;
use App\Models\BounceServer;
use App\Models\Plan;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Show global search results for admin.
     */
    public function index(Request $request)
    {
        $query = trim((string) $request->get('q', ''));

        $results = [
            'users' => collect(),
            'customers' => collect(),
            'campaigns' => collect(),
            'lists' => collect(),
            'deliveryServers' => collect(),
            'bounceServers' => collect(),
            'plans' => collect(),
        ];

        if ($query !== '') {
            $results['users'] = User::where(function ($q) use ($query) {
                    $q->where('first_name', 'like', '%' . $query . '%')
                        ->orWhere('last_name', 'like', '%' . $query . '%')
                        ->orWhere('email', 'like', '%' . $query . '%');
                })
                ->latest()
                ->limit(10)
                ->get();

            $results['customers'] = Customer::where(function ($q) use ($query) {
                    $q->where('first_name', 'like', '%' . $query . '%')
                        ->orWhere('last_name', 'like', '%' . $query . '%')
                        ->orWhere('email', 'like', '%' . $query . '%')
                        ->orWhere('company_name', 'like', '%' . $query . '%');
                })
                ->latest()
                ->limit(10)
                ->get();

            $results['campaigns'] = Campaign::where(function ($q) use ($query) {
                    $q->where('name', 'like', '%' . $query . '%')
                        ->orWhere('subject', 'like', '%' . $query . '%');
                })
                ->latest()
                ->limit(10)
                ->get();

            $results['lists'] = EmailList::where('name', 'like', '%' . $query . '%')
                ->latest()
                ->limit(10)
                ->get();

            $results['deliveryServers'] = DeliveryServer::where('name', 'like', '%' . $query . '%')
                ->whereNull('customer_id')
                ->orWhere(function ($q) use ($query) {
                    $q->whereNull('customer_id')
                        ->where('hostname', 'like', '%' . $query . '%');
                })
                ->latest()
                ->limit(10)
                ->get();

            $results['bounceServers'] = BounceServer::where('name', 'like', '%' . $query . '%')
                ->orWhere('hostname', 'like', '%' . $query . '%')
                ->latest()
                ->limit(10)
                ->get();

            $results['plans'] = Plan::where('name', 'like', '%' . $query . '%')
                ->latest()
                ->limit(10)
                ->get();
        }

        return view('admin.search.index', [
            'query' => $query,
            'results' => $results,
        ]);
    }

    public function suggest(Request $request)
    {
        $query = trim((string) $request->get('q', ''));

        if ($query === '') {
            return response()->json([
                'items' => [],
            ]);
        }

        $items = [];

        $users = User::where(function ($q) use ($query) {
                $q->where('first_name', 'like', '%' . $query . '%')
                    ->orWhere('last_name', 'like', '%' . $query . '%')
                    ->orWhere('email', 'like', '%' . $query . '%');
            })
            ->latest()
            ->limit(5)
            ->get();

        foreach ($users as $user) {
            $items[] = [
                'type' => 'User',
                'label' => $user->full_name,
                'description' => $user->email,
                'url' => route('admin.users.show', $user),
            ];
        }

        $customers = Customer::where(function ($q) use ($query) {
                $q->where('first_name', 'like', '%' . $query . '%')
                    ->orWhere('last_name', 'like', '%' . $query . '%')
                    ->orWhere('email', 'like', '%' . $query . '%')
                    ->orWhere('company_name', 'like', '%' . $query . '%');
            })
            ->latest()
            ->limit(5)
            ->get();

        foreach ($customers as $customer) {
            $items[] = [
                'type' => 'Customer',
                'label' => $customer->full_name,
                'description' => $customer->email,
                'url' => route('admin.customers.show', $customer),
            ];
        }

        $campaigns = Campaign::where(function ($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                    ->orWhere('subject', 'like', '%' . $query . '%');
            })
            ->latest()
            ->limit(5)
            ->get();

        foreach ($campaigns as $campaign) {
            $items[] = [
                'type' => 'Campaign',
                'label' => $campaign->name,
                'description' => $campaign->subject,
                'url' => route('admin.campaigns.show', $campaign),
            ];
        }

        $lists = EmailList::where('name', 'like', '%' . $query . '%')
            ->latest()
            ->limit(5)
            ->get();

        foreach ($lists as $list) {
            $items[] = [
                'type' => 'List',
                'label' => $list->name,
                'description' => (string) ($list->display_name ?? ''),
                'url' => route('admin.lists.show', $list),
            ];
        }

        $deliveryServers = DeliveryServer::where('name', 'like', '%' . $query . '%')
            ->whereNull('customer_id')
            ->orWhere(function ($q) use ($query) {
                $q->whereNull('customer_id')
                    ->where('hostname', 'like', '%' . $query . '%');
            })
            ->latest()
            ->limit(5)
            ->get();

        foreach ($deliveryServers as $server) {
            $items[] = [
                'type' => 'Delivery server',
                'label' => $server->name,
                'description' => (string) ($server->hostname ?? ''),
                'url' => route('admin.delivery-servers.show', $server),
            ];
        }

        $bounceServers = BounceServer::where('name', 'like', '%' . $query . '%')
            ->orWhere('hostname', 'like', '%' . $query . '%')
            ->latest()
            ->limit(5)
            ->get();

        foreach ($bounceServers as $server) {
            $items[] = [
                'type' => 'Bounce server',
                'label' => $server->name,
                'description' => (string) ($server->hostname ?? ''),
                'url' => route('admin.bounce-servers.show', $server),
            ];
        }

        $plans = Plan::where('name', 'like', '%' . $query . '%')
            ->latest()
            ->limit(5)
            ->get();

        foreach ($plans as $plan) {
            $items[] = [
                'type' => 'Plan',
                'label' => $plan->name,
                'description' => '',
                'url' => route('admin.plans.show', $plan),
            ];
        }

        return response()->json([
            'items' => $items,
        ]);
    }
}

