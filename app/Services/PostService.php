<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Str;

class PostService
{
    public function getAllPosts()
    {
        return Post::with(['user', 'category'])
            ->latest()
            ->paginate(10);
    }

    public function createPost(array $data, User $user)
    {
        $data['user_id'] = $user->id;
        $data['slug'] = $this->generateUniqueSlug($data['title']);
        
        if (isset($data['published']) && $data['published']) {
            $data['published_at'] = now();
        }

        return Post::create($data);
    }

    public function updatePost(Post $post, array $data)
    {
        if (isset($data['title'])) {
            $data['slug'] = $this->generateUniqueSlug($data['title'], $post->id);
        }

        if (isset($data['published']) && $data['published'] && !$post->published_at) {
            $data['published_at'] = now();
        }

        $post->update($data);
        return $post->fresh();
    }

    public function deletePost(Post $post)
    {
        return $post->delete();
    }

    private function generateUniqueSlug($title, $postId = null)
    {
        $slug = Str::slug($title);
        $count = 1;
        
        while (Post::where('slug', $slug)
            ->when($postId, function ($query) use ($postId) {
                return $query->where('id', '!=', $postId);
            })
            ->exists()) {
            $slug = Str::slug($title) . '-' . $count++;
        }
        
        return $slug;
    }
}
