<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Post;
use App\Policies\PostPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Post::class, PostPolicy::class);

        Route::bind('post', function ($value) {
            return Post::query()->find($value) ?: new Post();
        });

        Route::bind('category', function ($value) {
            return Category::query()->find($value) ?: new Category();
        });
    }
}
