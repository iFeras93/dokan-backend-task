<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        try {
            $user = User::query()->create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $token = $user->createToken('auth-token')->plainTextToken;


            return $this->successResponse([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ], 'User registered successfully', 201);
        } catch (\Exception $exception) {
            return $this->errorResponse('Something went wrong: ' . $exception->getMessage());
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $user = User::query()->where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            // Revoke all existing tokens
            $user->tokens()->delete();

            $token = $user->createToken('auth-token')->plainTextToken;


            return $this->successResponse([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ], 'Login successful');
        } catch (\Exception $exception) {
            return $this->errorResponse('Something went wrong: ' . $exception->getMessage());
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return $this->successResponse([], 'Logged out successfully');
        } catch (\Exception $exception) {
            return $this->errorResponse('Something went wrong: ' . $exception->getMessage());
        }
    }

    public function user(Request $request)
    {
        try {
            return $this->successResponse([
                'user' => [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                ]
            ]);
        } catch (\Exception $exception) {
            return $this->errorResponse('Something went wrong: ' . $exception->getMessage());
        }
    }

    public function refresh(Request $request)
    {
        try {
            $user = $request->user();

            // Revoke current token
            $request->user()->currentAccessToken()->delete();

            // Create new token
            $token = $user->createToken('auth-token')->plainTextToken;

            return $this->successResponse([
                'token' => $token,
                'token_type' => 'Bearer',
            ], 'Token refreshed successfully');
        } catch (\Exception $exception) {
            return $this->errorResponse('Something went wrong: ' . $exception->getMessage());
        }
    }
}
