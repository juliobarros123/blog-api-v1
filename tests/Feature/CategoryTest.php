<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_categories()
    {
        Sanctum::actingAs(User::factory()->create());

        Category4::factory()->count(3)->create();

        $response = $this->get('/api/categories');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_can_create_category()
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->post('/api/categories', [
            'name' => 'Tecnologia',
            'slug' => 'tecnologia',
            'description' => 'Posts sobre tecnologia',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'name' => 'Tecnologia',
                'slug' => 'tecnologia',
            ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Tecnologia',
            'slug' => 'tecnologia',
        ]);
    }
}