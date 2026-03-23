<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        return redirect()
            ->route('admin.login')
            ->with('error', 'Google authentication is not configured.');
    }

    public function callback(Request $request): RedirectResponse
    {
        return redirect()
            ->route('admin.login')
            ->with('error', 'Google authentication is not configured.');
    }
}
