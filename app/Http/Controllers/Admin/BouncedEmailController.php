<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BouncedEmail;
use App\Models\Campaign;
use App\Models\EmailList;
use Illuminate\Http\Request;

class BouncedEmailController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['email', 'campaign_id', 'list_id', 'bounce_type']);

        $bounces = BouncedEmail::query()
            ->with(['campaign', 'emailList'])
            ->when($filters['email'] ?? null, function ($query, $email) {
                $query->where('email', 'like', "%{$email}%");
            })
            ->when($filters['campaign_id'] ?? null, function ($query, $campaignId) {
                $query->where('campaign_id', $campaignId);
            })
            ->when($filters['list_id'] ?? null, function ($query, $listId) {
                $query->where('list_id', $listId);
            })
            ->when($filters['bounce_type'] ?? null, function ($query, $bounceType) {
                $query->where('bounce_type', $bounceType);
            })
            ->orderByDesc('last_bounced_at')
            ->paginate(25)
            ->withQueryString();

        $campaigns = Campaign::select('id', 'name')->orderByDesc('id')->limit(200)->get();
        $lists = EmailList::select('id', 'name')->orderBy('name')->get();

        return view('admin.bounced-emails.index', compact('bounces', 'filters', 'campaigns', 'lists'));
    }

    public function show(BouncedEmail $bounced_email)
    {
        $bounced_email->load([
            'campaign',
            'emailList',
            'bounceServer',
            'deliveryServer',
            'subscriber',
            'recipient',
        ]);

        return view('admin.bounced-emails.show', [
            'bounce' => $bounced_email,
        ]);
    }
}
