<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailList;
use App\Services\EmailListService;
use Illuminate\Http\Request;

class EmailListController extends Controller
{
    public function __construct(
        protected EmailListService $emailListService
    ) {}

    /**
     * Display a listing of all email lists.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status', 'customer_id']);
        $emailLists = EmailList::with(['customer'])
            ->withCount(['subscribers', 'confirmedSubscribers'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('display_name', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn($query, $status) => $query->where('status', $status))
            ->when($filters['customer_id'] ?? null, fn($query, $customerId) => $query->where('customer_id', $customerId))
            ->latest()
            ->paginate(15);

        $customers = \App\Models\Customer::select('id', 'first_name', 'last_name', 'email')->get();

        return view('admin.lists.index', compact('emailLists', 'filters', 'customers'));
    }

    /**
     * Display the specified email list.
     */
    public function show(EmailList $list)
    {
        $list->load(['customer', 'subscribers' => function ($query) {
            $query->latest()->limit(20);
        }]);
        
        return view('admin.lists.show', compact('list'));
    }

    /**
     * Remove the specified email list.
     */
    public function destroy(EmailList $list)
    {
        $this->emailListService->delete($list);

        return redirect()
            ->route('admin.lists.index')
            ->with('success', 'Email list deleted successfully.');
    }
}

