<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\BouncedEmail;
use App\Models\Campaign;
use App\Models\EmailList;
use Illuminate\Http\Request;

class BouncedEmailController extends Controller
{
    public function __construct()
    {
        $this->middleware('customer.access:bounced_emails.access');
    }

    public function index(Request $request)
    {
        $customer = auth('customer')->user();
        $filters = $request->only(['email', 'campaign_id', 'list_id', 'bounce_type']);

        $bounces = BouncedEmail::query()
            ->with(['campaign', 'emailList', 'deliveryServer', 'bounceServer'])
            ->where(function ($query) use ($customer) {
                $query
                    ->whereHas('campaign', fn ($q) => $q->where('customer_id', $customer->id))
                    ->orWhereHas('emailList', fn ($q) => $q->where('customer_id', $customer->id));
            })
            ->when($filters['email'] ?? null, fn ($query, $email) => $query->where('email', 'like', "%{$email}%"))
            ->when($filters['campaign_id'] ?? null, fn ($query, $campaignId) => $query->where('campaign_id', $campaignId))
            ->when($filters['list_id'] ?? null, fn ($query, $listId) => $query->where('list_id', $listId))
            ->when($filters['bounce_type'] ?? null, fn ($query, $bounceType) => $query->where('bounce_type', $bounceType))
            ->orderByDesc('last_bounced_at')
            ->paginate(25)
            ->withQueryString();

        $campaigns = Campaign::query()
            ->where('customer_id', $customer->id)
            ->select('id', 'name')
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        $lists = EmailList::query()
            ->where('customer_id', $customer->id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('customer.bounced-emails.index', compact('bounces', 'filters', 'campaigns', 'lists'));
    }

    public function show(BouncedEmail $bounced_email)
    {
        $customer = auth('customer')->user();

        $hasAccess = $bounced_email->campaign?->customer_id === $customer->id
            || $bounced_email->emailList?->customer_id === $customer->id;

        if (!$hasAccess) {
            abort(404);
        }

        $bounced_email->load([
            'campaign',
            'emailList',
            'bounceServer',
            'deliveryServer',
            'subscriber',
            'recipient',
        ]);

        return view('customer.bounced-emails.show', [
            'bounce' => $bounced_email,
        ]);
    }
}
