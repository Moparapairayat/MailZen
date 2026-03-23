<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogPostController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $status = $request->query('status');

        $posts = BlogPost::query()
            ->with('author')
            ->when($q !== '', function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%");
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderByDesc('id')
            ->paginate(25);

        return response()->json([
            'data' => $posts->items(),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
                'last_page' => $posts->lastPage(),
            ],
        ]);
    }

    public function show(BlogPost $blogPost)
    {
        return response()->json([
            'data' => $blogPost->load('author'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts,slug',
            'excerpt' => 'nullable|string',
            'content' => 'nullable|string',
            'featured_image' => 'nullable|string',
            'status' => 'nullable|string|in:draft,published,scheduled',
            'scheduled_at' => 'nullable|date',
            'is_published' => 'nullable|boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $validated['author_id'] = $request->user()->id;

        if (($validated['is_published'] ?? false) && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $post = BlogPost::create($validated);

        return response()->json([
            'data' => $post->load('author'),
            'message' => 'Blog post created successfully.',
        ], 201);
    }

    public function update(Request $request, BlogPost $blogPost)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts,slug,' . $blogPost->id,
            'excerpt' => 'nullable|string',
            'content' => 'nullable|string',
            'featured_image' => 'nullable|string',
            'status' => 'nullable|string|in:draft,published,scheduled',
            'scheduled_at' => 'nullable|date',
            'is_published' => 'nullable|boolean',
        ]);

        if (($validated['is_published'] ?? false) && !$blogPost->published_at) {
            $validated['published_at'] = now();
        }

        $blogPost->update($validated);

        return response()->json([
            'data' => $blogPost->fresh()->load('author'),
            'message' => 'Blog post updated successfully.',
        ]);
    }

    public function destroy(BlogPost $blogPost)
    {
        $blogPost->delete();

        return response()->json([
            'message' => 'Blog post deleted successfully.',
        ]);
    }
}
