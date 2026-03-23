<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Request;

class PublicBlogController extends Controller
{
    public function index(Request $request)
    {
        $posts = BlogPost::query()
            ->where('status', 'publish')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at')
            ->paginate(12);

        return view('public.blog.index', compact('posts'));
    }

    public function show(string $slug)
    {
        $post = BlogPost::query()
            ->where('slug', $slug)
            ->where('status', 'publish')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->firstOrFail();

        return view('public.blog.show', compact('post'));
    }
}
