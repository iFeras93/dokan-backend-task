<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

// Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);

        // you must be authenticated
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/user', [AuthController::class, 'user']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
        });
    });

// => Bonus route
    Route::get('/categories/{category}/posts', [CategoryController::class, 'posts']);

    Route::prefix('posts')->group(function () {
        Route::get('/', [PostController::class, 'index']);
        Route::get('/{post}', [PostController::class, 'show']);
        Route::post('/', [PostController::class, 'store']);
        Route::post('/{post}/comments', [CommentController::class, 'store']);
        Route::delete('/{post}/delete', [PostController::class, 'destroy']);
    });

});

