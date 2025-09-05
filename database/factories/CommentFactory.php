<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'content' => $this->faker->paragraph(random_int(1, 3)),
            'user_id' => User::factory(),
            'post_id' => Post::factory(),
        ];
    }


    /**
     * Create a comment for a specific post
     */
    public function forPost(Post $post): static
    {
        return $this->state(fn(array $attributes) => [
            'post_id' => $post->id,
        ]);
    }

    /**
     * Create a comment by a specific user
     */
    public function byUser(User $user): static
    {
        return $this->state(fn(array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create a short comment
     */
    public function short(): static
    {
        return $this->state(fn(array $attributes) => [
            'content' => $this->faker->sentence(random_int(5, 15)),
        ]);
    }

    /**
     * Create a long comment
     */
    public function long(): static
    {
        return $this->state(fn(array $attributes) => [
            'content' => $this->faker->paragraphs(random_int(2, 5), true),
        ]);
    }

    /**
     * Create a comment with specific content
     */
    public function withContent(string $content): static
    {
        return $this->state(fn(array $attributes) => [
            'content' => $content,
        ]);
    }
}
