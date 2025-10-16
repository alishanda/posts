<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PostResource',
    title: 'Post Resource',
    description: 'Ресурс поста',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: 'Заголовок поста'),
        new OA\Property(property: 'content', type: 'string', example: 'Содержимое поста'),
        new OA\Property(property: 'status', type: 'string', enum: ['draft', 'published', 'archived'], example: 'published'),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(property: 'user', ref: '#/components/schemas/UserResource'),
        new OA\Property(property: 'comments_count', type: 'integer', example: 5),
        new OA\Property(property: 'comments', type: 'array', items: new OA\Items(ref: '#/components/schemas/CommentResource')),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
class PostResource extends JsonResource
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
            'status' => $this->status,
            'is_active' => $this->isActive(),
            'user' => new UserResource($this->whenLoaded('user')),
            'comments_count' => $this->whenCounted('comments'),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
