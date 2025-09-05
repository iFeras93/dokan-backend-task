<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function posts(Category $category)
    {
        try {
            if (!$category->exists) {
                return $this->errorResponse('Category not found', 404);
            }

            $posts = $category->posts()
                ->with(['user', 'category'])
                ->withCount('comments')
                ->latest()
                ->get(); // we can make pagination here use paginate() instead of get()

            return $this->successResponse(PostResource::collection($posts), 'data retrieved successfully!');
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }
}
