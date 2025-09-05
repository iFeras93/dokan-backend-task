<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CommentController extends Controller implements HasMiddleware
{

    public function store(StoreCommentRequest $request, Post $post)
    {
        if (!$post->exists)
            return $this->errorResponse('Post not found', 404);

        $comment = Comment::query()->create([
            'content' => $request->input('content'), // to avoid conflict of $request object has {content} key
            'user_id' => auth()->id(),
            'post_id' => $post->id,
        ]);

        $comment->load('user');

        return $this->successResponse(new CommentResource($comment), 'Comment created successfully', 201);
    }

    public static function middleware()
    {
        return [
            'auth:sanctum',
        ];
    }
}
