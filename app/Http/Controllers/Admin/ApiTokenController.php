<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ApiTokenController extends Controller
{
    public function index(Request $request)
    {
        $user = auth('admin')->user();
        abort_if(!$user, 403);

        $tokens = $user->tokens()->latest()->get();

        return view('admin.api.index', compact('tokens'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth('admin')->user();
        abort_if(!$user, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'abilities' => ['nullable', 'array'],
            'abilities.*' => ['string', 'max:255'],
        ]);

        $abilities = array_values(array_unique(array_filter(
            (array) ($validated['abilities'] ?? ['*']),
            fn ($v) => is_string($v) && trim($v) !== ''
        )));

        $token = $user->createToken($validated['name'], $abilities);

        return redirect()
            ->route('admin.api.index')
            ->with('success', 'API key created. Copy it now — it will not be shown again.')
            ->with('plain_text_token', $token->plainTextToken);
    }

    public function destroy(Request $request, int $tokenId): RedirectResponse
    {
        $user = auth('admin')->user();
        abort_if(!$user, 403);

        $token = $user->tokens()->where('id', $tokenId)->firstOrFail();
        $token->delete();

        return redirect()
            ->route('admin.api.index')
            ->with('success', 'API key revoked.');
    }
}
