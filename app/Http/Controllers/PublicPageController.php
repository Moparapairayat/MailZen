<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;

class PublicPageController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $page = Page::query()
            ->whereIn('type', ['page', 'homepage'])
            ->where('status', 'publish')
            ->where('slug', $slug)
            ->firstOrFail();

        return view('public.page', [
            'page' => $page,
        ]);
    }
}
