<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BlogPostController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('q', ''));

        $posts = BlogPost::query()
            ->with(['author:id,email'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%');
            })
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.blog-posts.index', compact('posts', 'search'));
    }

    public function create()
    {
        return view('admin.blog-posts.create', [
            'post' => new BlogPost(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $data['author_id'] = auth('admin')->id();

        $data = $this->applyStatus($data, null);

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('blog/featured-images', 'public');
        }

        BlogPost::create($data);

        return redirect()->route('admin.blog-posts.index')->with('success', __('Blog post created.'));
    }

    public function edit(BlogPost $blog_post)
    {
        return view('admin.blog-posts.edit', [
            'post' => $blog_post,
        ]);
    }

    public function update(Request $request, BlogPost $blog_post)
    {
        $data = $this->validateData($request, $blog_post->id);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }


        $data = $this->applyStatus($data, $blog_post);

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('blog/featured-images', 'public');
        } else {
            unset($data['featured_image']);
        }

        $blog_post->update($data);

        return redirect()->route('admin.blog-posts.index')->with('success', __('Blog post updated.'));
    }

    public function publish(BlogPost $blog_post)
    {
        $blog_post->update([
            'status' => 'publish',
            'is_published' => true,
            'scheduled_at' => null,
            'published_at' => $blog_post->published_at ?? now(),
        ]);

        return redirect()->route('admin.blog-posts.index')->with('success', __('Blog post published.'));
    }

    public function unpublish(BlogPost $blog_post)
    {
        $blog_post->update([
            'status' => 'draft',
            'is_published' => false,
            'scheduled_at' => null,
            'published_at' => null,
        ]);

        return redirect()->route('admin.blog-posts.index')->with('success', __('Blog post unpublished.'));
    }

    public function destroy(BlogPost $blog_post)
    {
        $blog_post->delete();

        return redirect()->route('admin.blog-posts.index')->with('success', __('Blog post deleted.'));
    }

    private function validateData(Request $request, ?int $postId = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('blog_posts', 'slug')->ignore($postId)],
            'excerpt' => ['nullable', 'string'],
            'content' => ['required', 'string'],
            'status' => ['required', 'string', Rule::in(['draft', 'publish', 'schedule'])],
            'scheduled_at' => ['nullable', 'date', 'required_if:status,schedule'],
            'featured_image' => ['nullable', 'file', 'image', 'max:5120'],
        ]);

        return $data;
    }

    private function applyStatus(array $data, ?BlogPost $existing): array
    {
        $status = $data['status'] ?? 'draft';

        if ($status === 'publish') {
            $data['is_published'] = true;
            $data['scheduled_at'] = null;
            $data['published_at'] = $existing?->published_at ?? now();
            return $data;
        }

        if ($status === 'schedule') {
            $scheduledAt = isset($data['scheduled_at']) && $data['scheduled_at']
                ? now()->parse($data['scheduled_at'])
                : null;

            if ($scheduledAt && $scheduledAt->lessThanOrEqualTo(now())) {
                $data['status'] = 'publish';
                $data['is_published'] = true;
                $data['scheduled_at'] = null;
                $data['published_at'] = $existing?->published_at ?? now();
                return $data;
            }

            $data['is_published'] = false;
            $data['published_at'] = null;
            $data['scheduled_at'] = $scheduledAt;
            return $data;
        }

        $data['status'] = 'draft';
        $data['is_published'] = false;
        $data['scheduled_at'] = null;
        $data['published_at'] = null;
        return $data;
    }
}
