<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Criar usuário admin
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@blog.com',
        ]);

        // Criar mais 10 usuários
        User::factory(10)->create();

        // Criar 5 categorias
        $categories = Category::factory(5)->create();

        // Criar 20 posts (10 publicados, 5 rascunhos, 5 arquivados)
        Post::factory(10)->published()->create();
        Post::factory(5)->create(['status' => 'draft']);
        Post::factory(5)->create(['status' => 'archived']);

        // Adicionar categorias aleatórias aos posts
        Post::all()->each(function ($post) use ($categories) {
            $post->update([
                'category_id' => $categories->random()->id,
            ]);
        });
    }
}