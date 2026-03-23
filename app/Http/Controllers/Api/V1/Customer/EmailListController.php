<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Models\EmailList;
use App\Services\EmailListService;
use Illuminate\Http\Request;

class EmailListController extends Controller
{
    public function __construct(
        protected EmailListService $emailListService
    ) {
    }

    protected function customer(Request $request)
    {
        return $request->user('sanctum');
    }

    protected function authorizeOwnership(Request $request, EmailList $list): EmailList
    {
        $customer = $this->customer($request);
        if (!$customer || (int) $list->customer_id !== (int) $customer->id) {
            abort(404);
        }

        return $list;
    }

    public function index(Request $request)
    {
        $customer = $this->customer($request);

        $filters = $request->only(['search', 'status']);
        $lists = $this->emailListService->getPaginated($customer, $filters);

        return response()->json([
            'data' => $lists->items(),
            'meta' => [
                'current_page' => $lists->currentPage(),
                'per_page' => $lists->perPage(),
                'total' => $lists->total(),
                'last_page' => $lists->lastPage(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $customer = $this->customer($request);

        $customer->enforceGroupLimit('lists.limits.max_lists', $customer->emailLists()->count(), 'Email list limit reached.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'from_email' => ['nullable', 'email', 'max:255'],
            'reply_to' => ['nullable', 'email', 'max:255'],
            'status' => ['nullable', 'in:active,inactive,pending'],
            'opt_in' => ['nullable', 'in:single,double'],
            'opt_out' => ['nullable', 'in:single,double'],
            'double_opt_in' => ['nullable', 'boolean'],
            'default_subject' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string'],
            'footer_text' => ['nullable', 'string'],
            'welcome_email_enabled' => ['nullable', 'boolean'],
            'welcome_email_subject' => ['nullable', 'string', 'max:255'],
            'welcome_email_content' => ['nullable', 'string'],
            'unsubscribe_email_enabled' => ['nullable', 'boolean'],
            'unsubscribe_email_subject' => ['nullable', 'string', 'max:255'],
            'unsubscribe_email_content' => ['nullable', 'string'],
            'unsubscribe_redirect_url' => ['nullable', 'string', 'max:2048'],
            'gdpr_enabled' => ['nullable', 'boolean'],
            'gdpr_text' => ['nullable', 'string'],
            'custom_fields' => ['nullable', 'array'],
            'tags' => ['nullable', 'array'],
        ]);

        $list = $this->emailListService->create($customer, $validated);

        return response()->json(['data' => $list], 201);
    }

    public function show(Request $request, EmailList $list)
    {
        $list = $this->authorizeOwnership($request, $list);

        return response()->json(['data' => $list]);
    }

    public function update(Request $request, EmailList $list)
    {
        $list = $this->authorizeOwnership($request, $list);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'from_email' => ['nullable', 'email', 'max:255'],
            'reply_to' => ['nullable', 'email', 'max:255'],
            'status' => ['nullable', 'in:active,inactive,pending'],
            'opt_in' => ['nullable', 'in:single,double'],
            'opt_out' => ['nullable', 'in:single,double'],
            'double_opt_in' => ['nullable', 'boolean'],
            'default_subject' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string'],
            'footer_text' => ['nullable', 'string'],
            'welcome_email_enabled' => ['nullable', 'boolean'],
            'welcome_email_subject' => ['nullable', 'string', 'max:255'],
            'welcome_email_content' => ['nullable', 'string'],
            'unsubscribe_email_enabled' => ['nullable', 'boolean'],
            'unsubscribe_email_subject' => ['nullable', 'string', 'max:255'],
            'unsubscribe_email_content' => ['nullable', 'string'],
            'unsubscribe_redirect_url' => ['nullable', 'string', 'max:2048'],
            'gdpr_enabled' => ['nullable', 'boolean'],
            'gdpr_text' => ['nullable', 'string'],
            'custom_fields' => ['nullable', 'array'],
            'tags' => ['nullable', 'array'],
        ]);

        $updated = $this->emailListService->update($list, $validated);

        return response()->json(['data' => $updated]);
    }

    public function destroy(Request $request, EmailList $list)
    {
        $list = $this->authorizeOwnership($request, $list);

        $this->emailListService->delete($list);

        return response()->json(['success' => true]);
    }
}
