<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->category = Category::factory()->create();
    }

    public function test_authenticated_user_can_create_post(): void
    {
        $postData = [
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraphs(3, true),
            'category_id' => $this->category->id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson(self::BASE_URL . '/posts', $postData);

        $response->assertCreated()
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'title',
                    'user',
                    'category',
                    'comments_count',
                ]
            ]);

        $this->assertDatabaseHas('posts', [
            'title' => $postData['title'],
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_create_post(): void
    {
        $response = $this->postJson(self::BASE_URL . '/posts', [
            'title' => 'Test Post',
            'content' => 'This is a test post content.',
            'category_id' => $this->category->id,
        ]);

        $response->assertStatus(401);
    }

    public function test_user_can_view_post_with_comments(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->getJson(self::BASE_URL . "/posts/{$post->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'title',
                    'content',
                    'user' => ['id', 'name'],
                    'category' => ['id', 'name'],
                    'comments' => [
                        '*' => [
                            'id',
                            'content',
                            'user',
                            'created_at',
                        ]
                    ],
                ]
            ]);
    }

    public function test_authenticated_user_can_add_comment_to_post(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->postJson(self::BASE_URL . "/posts/{$post->id}/comments", [
            'content' => 'This is a test comment.',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'content',
                    'user' => ['id', 'name'],
                ]
            ]);

        $this->assertDatabaseHas('comments', [
            'content' => 'This is a test comment.',
            'user_id' => $this->user->id,
            'post_id' => $post->id,
        ]);
    }
    public function test_unauthenticated_user_cannot_add_comment_to_post(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->postJson(self::BASE_URL . "/posts/{$post->id}/comments", [
            'content' => 'This is a test comment.',
        ]);

       $response->assertStatus(401);
    }
}
