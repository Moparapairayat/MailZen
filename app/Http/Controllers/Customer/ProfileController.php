<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Show the profile edit page.
     */
    public function edit(Request $request)
    {
        $customer = $request->user('customer');

        return view('customer.profile.edit', [
            'customer' => $customer,
        ]);
    }

    /**
     * Update the authenticated customer's profile.
     */
    public function update(Request $request)
    {
        $customer = $request->user('customer');

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'twitter_url' => ['nullable', 'url', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'tax_id' => ['nullable', 'string', 'max:255'],
            'billing_address.address_line_1' => ['nullable', 'string', 'max:255'],
            'billing_address.address_line_2' => ['nullable', 'string', 'max:255'],
            'billing_address.city' => ['nullable', 'string', 'max:255'],
            'billing_address.state' => ['nullable', 'string', 'max:255'],
            'billing_address.postal_code' => ['nullable', 'string', 'max:255'],
            'billing_address.country' => ['nullable', 'string', 'max:255'],
        ]);

        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($customer->avatar_path) {
                Storage::disk('public')->delete($customer->avatar_path);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar_path'] = $path;
        }

        if (isset($validated['billing_address']) && is_array($validated['billing_address'])) {
            $validated['billing_address'] = array_filter($validated['billing_address'], fn ($v) => $v !== null && $v !== '');
        }

        $customer->update($validated);

        return redirect()
            ->route('customer.profile.edit')
            ->with('success', 'Profile updated successfully.');
    }
}


