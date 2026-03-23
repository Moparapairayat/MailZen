<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\EmailList;
use App\Models\ListSubscriber;
use App\Models\Template;
use App\Models\TransactionalEmail;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Show global search results for the authenticated customer.
     */
    public function index(Request $request)
    {
        $customer = $request->user('customer');
        $query = trim((string) $request->get('q', ''));

        $results = [
            'campaigns' => collect(),
            'lists' => collect(),
            'subscribers' => collect(),
            'templates' => collect(),
            'transactionalEmails' => collect(),
        ];

        if ($query !== '') {
            $results['campaigns'] = Campaign::where('customer_id', $customer->id)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', '%' . $query . '%')
                        ->orWhere('subject', 'like', '%' . $query . '%');
                })
                ->latest()
                ->limit(10)
                ->get();

            $results['lists'] = EmailList::where('customer_id', $customer->id)
                ->where('name', 'like', '%' . $query . '%')
                ->latest()
                ->limit(10)
                ->get();

            $results['subscribers'] = ListSubscriber::whereHas('emailList', function ($q) use ($customer) {
                    $q->where('customer_id', $customer->id);
                })
                ->with('emailList')
                ->where(function ($q) use ($query) {
                    $q->where('email', 'like', '%' . $query . '%')
                        ->orWhere('first_name', 'like', '%' . $query . '%')
                        ->orWhere('last_name', 'like', '%' . $query . '%');
                })
                ->latest()
                ->limit(10)
                ->get();

            $results['templates'] = Template::where('customer_id', $customer->id)
                ->where('name', 'like', '%' . $query . '%')
                ->latest()
                ->limit(10)
                ->get();

            $results['transactionalEmails'] = TransactionalEmail::where('customer_id', $customer->id)
                ->where(function ($q) use ($query) {
                    $q->where('subject', 'like', '%' . $query . '%')
                        ->orWhere('name', 'like', '%' . $query . '%')
                        ->orWhere('key', 'like', '%' . $query . '%');
                })
                ->latest()
                ->limit(10)
                ->get();
        }

        return view('customer.search.index', [
            'query' => $query,
            'results' => $results,
        ]);
    }

    public function suggest(Request $request)
    {
        $customer = $request->user('customer');
        $query = trim((string) $request->get('q', ''));

        if ($query === '') {
            return response()->json([
                'items' => [],
            ]);
        }

        $items = [];

        $campaigns = Campaign::where('customer_id', $customer->id)
            ->where(function ($q) use ($query) {
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
                'url' => route('customer.campaigns.show', $campaign),
            ];
        }

        $lists = EmailList::where('customer_id', $customer->id)
            ->where('name', 'like', '%' . $query . '%')
            ->latest()
            ->limit(5)
            ->get();

        foreach ($lists as $list) {
            $items[] = [
                'type' => 'List',
                'label' => $list->name,
                'description' => (string) (number_format($list->subscribers_count ?? 0) . ' subscribers'),
                'url' => route('customer.lists.show', $list),
            ];
        }

        $subscribers = ListSubscriber::whereHas('emailList', function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            })
            ->with('emailList')
            ->where(function ($q) use ($query) {
                $q->where('email', 'like', '%' . $query . '%')
                    ->orWhere('first_name', 'like', '%' . $query . '%')
                    ->orWhere('last_name', 'like', '%' . $query . '%');
            })
            ->latest()
            ->limit(5)
            ->get();

        foreach ($subscribers as $subscriber) {
            if (!$subscriber->emailList) {
                continue;
            }

            $items[] = [
                'type' => 'Subscriber',
                'label' => $subscriber->email,
                'description' => (string) ($subscriber->emailList->name ?? ''),
                'url' => route('customer.lists.subscribers.show', [$subscriber->emailList, $subscriber]),
            ];
        }

        $templates = Template::where('customer_id', $customer->id)
            ->where('name', 'like', '%' . $query . '%')
            ->latest()
            ->limit(5)
            ->get();

        foreach ($templates as $template) {
            $items[] = [
                'type' => 'Template',
                'label' => $template->name,
                'description' => (string) ($template->type ?? ''),
                'url' => route('customer.templates.show', $template),
            ];
        }

        $transactionalEmails = TransactionalEmail::where('customer_id', $customer->id)
            ->where(function ($q) use ($query) {
                $q->where('subject', 'like', '%' . $query . '%')
                    ->orWhere('name', 'like', '%' . $query . '%')
                    ->orWhere('key', 'like', '%' . $query . '%');
            })
            ->latest()
            ->limit(5)
            ->get();

        foreach ($transactionalEmails as $email) {
            $items[] = [
                'type' => 'Transactional email',
                'label' => (string) ($email->subject ?: $email->name),
                'description' => (string) ($email->key ?? ''),
                'url' => route('customer.transactional-emails.show', $email),
            ];
        }

        return response()->json([
            'items' => $items,
        ]);
    }
}


