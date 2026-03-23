<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Models\EmailList;
use App\Models\ListSubscriber;
use App\Services\ListSubscriberService;
use Illuminate\Http\Request;

class ListSubscriberController extends Controller
{
    public function __construct(
        protected ListSubscriberService $listSubscriberService
    ) {
    }

    protected function customer(Request $request)
    {
        return $request->user('sanctum');
    }

    protected function authorizeListOwnership(Request $request, EmailList $list): EmailList
    {
        $customer = $this->customer($request);
        if (!$customer || (int) $list->customer_id !== (int) $customer->id) {
            abort(404);
        }

        return $list;
    }

    protected function authorizeSubscriberOwnership(EmailList $list, ListSubscriber $subscriber): ListSubscriber
    {
        if ((int) $subscriber->list_id !== (int) $list->id) {
            abort(404);
        }

        return $subscriber;
    }

    public function index(Request $request, EmailList $list)
    {
        $list = $this->authorizeListOwnership($request, $list);

        $filters = $request->only(['search', 'status']);
        $subscribers = $this->listSubscriberService->getPaginated($list, $filters);

        return response()->json([
            'data' => $subscribers->items(),
            'meta' => [
                'current_page' => $subscribers->currentPage(),
                'per_page' => $subscribers->perPage(),
                'total' => $subscribers->total(),
                'last_page' => $subscribers->lastPage(),
            ],
        ]);
    }

    public function store(Request $request, EmailList $list)
    {
        $list = $this->authorizeListOwnership($request, $list);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'array'],
            'custom_fields' => ['nullable', 'array'],
        ]);

        $validated['source'] = $validated['source'] ?? 'api';
        $validated['ip_address'] = $request->ip();

        $subscriber = $this->listSubscriberService->create($list, $validated);

        return response()->json(['data' => $subscriber], 201);
    }

    public function show(Request $request, EmailList $list, ListSubscriber $subscriber)
    {
        $list = $this->authorizeListOwnership($request, $list);
        $subscriber = $this->authorizeSubscriberOwnership($list, $subscriber);

        return response()->json(['data' => $subscriber]);
    }

    public function update(Request $request, EmailList $list, ListSubscriber $subscriber)
    {
        $list = $this->authorizeListOwnership($request, $list);
        $subscriber = $this->authorizeSubscriberOwnership($list, $subscriber);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:confirmed,unconfirmed,unsubscribed,blacklisted,bounced'],
            'tags' => ['nullable', 'array'],
            'custom_fields' => ['nullable', 'array'],
            'notes' => ['nullable', 'string'],
        ]);

        $updated = $this->listSubscriberService->update($subscriber, $validated);

        return response()->json(['data' => $updated]);
    }

    public function destroy(Request $request, EmailList $list, ListSubscriber $subscriber)
    {
        $list = $this->authorizeListOwnership($request, $list);
        $subscriber = $this->authorizeSubscriberOwnership($list, $subscriber);

        $this->listSubscriberService->delete($subscriber);

        return response()->json(['success' => true]);
    }
}
