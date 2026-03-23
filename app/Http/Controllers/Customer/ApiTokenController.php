<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ApiTokenController extends Controller
{
    public function __construct()
    {
        $this->middleware('customer.access:api.permissions.can_access_api')->only(['index']);
        $this->middleware('customer.access:api.permissions.can_create_api_keys')->only(['store']);
        $this->middleware('customer.access:api.permissions.can_delete_api_keys')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $customer = auth('customer')->user();
        abort_if(!$customer, 403);

        $tokens = $customer->tokens()->latest()->get();

        return view('customer.api.index', compact('tokens'));
    }

    public function store(Request $request): RedirectResponse
    {
        $customer = auth('customer')->user();
        abort_if(!$customer, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'abilities' => ['nullable', 'array'],
            'abilities.*' => ['string', 'max:255'],
        ]);

        $abilities = array_values(array_unique(array_filter(
            (array) ($validated['abilities'] ?? ['*']),
            fn ($v) => is_string($v) && trim($v) !== ''
        )));

        $token = $customer->createToken($validated['name'], $abilities);

        return redirect()
            ->route('customer.api.index')
            ->with('success', 'API key created. Copy it now — it will not be shown again.')
            ->with('plain_text_token', $token->plainTextToken);
    }

    public function destroy(Request $request, int $tokenId): RedirectResponse
    {
        $customer = auth('customer')->user();
        abort_if(!$customer, 403);

        $token = $customer->tokens()->where('id', $tokenId)->firstOrFail();
        $token->delete();

        return redirect()
            ->route('customer.api.index')
            ->with('success', 'API key revoked.');
    }
}
