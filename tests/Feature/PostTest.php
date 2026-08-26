<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_post(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/posts', [
            'title' => 'Meu primeiro post',
            'content' => 'Este é o conteúdo do meu primeiro post.',
            'category_id' => $category->id,
            'published' => false,
        ]);

        $response->assertStatus(201);

        $response->assertJson([
            'title' => 'Meu primeiro post',
            'content' => 'Este é o conteúdo do meu primeiro post.',
            'category_id' => $category->id,
            'published' => false,
        ]);

        $this->assertDatabaseHas('posts', [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Meu primeiro post',
            'slug' => 'meu-primeiro-post',
        ]);
    }
}