<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostWithCommentsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ],
            'comments' => CommentResource::collection($this->comments),
            'created_at' => $this->created_at,
            'human_created_at' => $this->created_at->diffForHumans(),
            'updated_at' => $this->updated_at,
            'human_updated_at' => $this->updated_at->diffForHumans(),
        ];
    }
}
