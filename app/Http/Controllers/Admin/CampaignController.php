<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Services\CampaignService;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function __construct(
        protected CampaignService $campaignService
    ) {}

    /**
     * Display a listing of all campaigns.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status', 'type', 'customer_id']);
        $campaigns = Campaign::with(['customer', 'emailList'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn($query, $status) => $query->where('status', $status))
            ->when($filters['type'] ?? null, fn($query, $type) => $query->where('type', $type))
            ->when($filters['customer_id'] ?? null, fn($query, $customerId) => $query->where('customer_id', $customerId))
            ->latest()
            ->paginate(15);

        $customers = \App\Models\Customer::select('id', 'first_name', 'last_name', 'email')->get();

        return view('admin.campaigns.index', compact('campaigns', 'filters', 'customers'));
    }

    /**
     * Display the specified campaign.
     */
    public function show(Campaign $campaign)
    {
        $campaign->load(['customer', 'emailList', 'trackingDomain']);
        
        return view('admin.campaigns.show', compact('campaign'));
    }

    /**
     * Remove the specified campaign.
     */
    public function destroy(Campaign $campaign)
    {
        $this->campaignService->delete($campaign);

        return redirect()
            ->route('admin.campaigns.index')
            ->with('success', 'Campaign deleted successfully.');
    }
}

