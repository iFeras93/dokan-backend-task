<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Resources\PostResource;
use App\Http\Resources\PostWithCommentsResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

// Correct import

class PostController extends Controller implements HasMiddleware
{
    public function index()
    {
        $posts = Post::query()
            ->with(['user', 'category'])
            ->withCount('comments')
            ->latest()
            ->get(); // we can make pagination here use paginate() instead of get()

        return $this->successResponse(PostResource::collection($posts), 'data retrieved successfully!');
    }

    public function store(StorePostRequest $request)
    {

        if (!Gate::allows('create', Post::class))
            return $this->errorResponse('Unauthorized to update this post.', 401);


        $post = Post::query()->create([
            'title' => $request->title,
            'content' => $request->input('content'), // to avoid conflict of $request object has {content} key
            'category_id' => $request->category_id,
            'user_id' => auth()->id(),
        ]);

        $post->load(['user', 'category']);

        return $this->successResponse(new PostResource($post), 'Post created successfully', 201);
    }

    public function show(Post $post)
    {
        if (!$post->exists)
            return $this->errorResponse('Post not found', 404);

        $post->load(['user', 'category', 'comments.user']);
        return $this->successResponse(new PostWithCommentsResource($post), 'data retrieved successfully!');
    }

    // I used the same StorePostRequest because in update we need the same validation
    public function update(StorePostRequest $request, Post $post)
    {
        try {
            if (!$post->exists)
                return $this->errorResponse('Post not found', 404);

            if (!Gate::allows('update', $post))
                return $this->errorResponse('Unauthorized to update this post.', 401);

            $post->update($request->validated());
            return $this->successResponse(null, 'Post updated successfully!');
        } catch (\Exception $exception) {
            return $this->errorResponse('Something went wrong: ' . $exception->getMessage(), 500);
        }
    }

    public function destroy(Post $post)
    {
        try {
            if (!$post->exists)
                return $this->errorResponse('Post not found', 404);

            if (!Gate::allows('delete', $post))
                return $this->errorResponse('Unauthorized to update this post.', 401);

            $post->delete();
            return $this->successResponse(null, 'Post deleted successfully!');
        } catch (\Exception $exception) {
            return $this->errorResponse('Something went wrong: ' . $exception->getMessage(), 500);
        }
    }

    public static function middleware()
    {
        return [
            new Middleware('auth:sanctum', only: ['store', 'update', 'destroy']),
        ];
    }
}
