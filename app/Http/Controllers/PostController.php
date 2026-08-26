<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    protected $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    public function index()
    {
        $posts = Post::with(['user', 'category'])
            ->latest()
            ->paginate(10);
        
        return response()->json($posts);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'published' => 'boolean',
            'image' => 'nullable|string|url',
        ]);

        $post = $this->postService->createPost($validated, $request->user());

        return response()->json($post, 201);
    }

    public function show(Post $post)
    {
        return response()->json($post->load(['user', 'category']));
    }

    public function update(Request $request, Post $post)
    {
        Gate::authorize('update', $post);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'category_id' => 'nullable|exists:categories,id',
            'published' => 'boolean',
            'image' => 'nullable|string|url',
        ]);

        $post = $this->postService->updatePost($post, $validated);

        return response()->json($post);
    }

    public function destroy(Post $post)
    {
        Gate::authorize('delete', $post);

        $this->postService->deletePost($post);

        return response()->json(['message' => 'Post deleted successfully']);
    }
}