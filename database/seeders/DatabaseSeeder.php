<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create users
        $users = User::factory(10)->create();

        // Create categories
        $categories = Category::factory(5)->create();

        // Create posts with comments
        $posts = Post::factory(20)
            ->recycle($users)
            ->recycle($categories)
            ->create();

        // Create comments for posts
        $posts->each(function (Post $post) use ($users) {
            Comment::factory(random_int(0, 8))
                ->forPost($post)
                ->recycle($users)
                ->create();
        });

        // Create a specific test user
        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create posts for test user with comments
        Post::factory(3)
            ->byUser($testUser)
            ->recycle($categories)
            ->create()
            ->each(function (Post $post) use ($users) {
                Comment::factory(random_int(2, 5))
                    ->forPost($post)
                    ->recycle($users)
                    ->create();
            });
    }
}
