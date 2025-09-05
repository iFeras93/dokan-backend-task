<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase, WithFaker;


    #region test for register scenarios

    /** @test */
    public function user_can_register_with_valid_data(): void
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson(self::BASE_URL . '/auth/register', $userData);

        $response->assertCreated()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                    'token_type',
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);
    }

    /** @test */
    public function user_cannot_register_with_invalid_data(): void
    {
        $response = $this->postJson(self::BASE_URL . '/auth/register', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /** @test */
    public function user_cannot_register_with_existing_email(): void
    {
        $existingUser = User::factory()->create();

        $response = $this->postJson(self::BASE_URL . '/auth/register', [
            'name' => 'John Doe',
            'email' => $existingUser->email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }
    #endregion


    #region test for login scenarios
    /** @test */
    public function user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson(self::BASE_URL . '/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                    'token_type',
                ]
            ]);
    }

    /** @test */
    public function user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson(self::BASE_URL . '/auth/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertBadRequest();
    }
    #endregion


    /** @test */
    public function authenticated_user_can_get_user_info(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(self::BASE_URL . '/auth/user');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Success',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                ]
            ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_protected_routes(): void
    {
        $response = $this->getJson(self::BASE_URL . '/auth/user');
        $response->assertUnauthorized();

        $response = $this->postJson(self::BASE_URL . '/auth/logout');
        $response->assertUnauthorized();

        $response = $this->postJson(self::BASE_URL . '/auth/refresh');
        $response->assertUnauthorized();
    }
}
