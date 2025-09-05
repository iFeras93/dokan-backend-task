<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(random_int(4, 8)),
            'content' => $this->faker->paragraphs(random_int(3, 6), true),
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
        ];
    }


    /**
     * Create a post with comments
     */
    public function withComments(int $count = 3): static
    {
        return $this->hasComments($count);
    }

    /**
     * Create a post by a specific user
     */
    public function byUser(User $user): static
    {
        return $this->state(fn(array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create a post in a specific category
     */
    public function inCategory(Category $category): static
    {
        return $this->state(fn(array $attributes) => [
            'category_id' => $category->id,
        ]);
    }

    /**
     * Create a short post
     */
    public function short(): static
    {
        return $this->state(fn(array $attributes) => [
            'title' => $this->faker->sentence(3),
            'content' => $this->faker->paragraph(),
        ]);
    }

    /**
     * Create a long post
     */
    public function long(): static
    {
        return $this->state(fn(array $attributes) => [
            'title' => $this->faker->sentence(random_int(8, 12)),
            'content' => $this->faker->paragraphs(random_int(8, 12), true),
        ]);
    }
}
