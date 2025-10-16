<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CommentResource',
    title: 'Comment Resource',
    description: 'Ресурс комментария',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'content', type: 'string', example: 'Текст комментария'),
        new OA\Property(property: 'status', type: 'string', enum: ['pending', 'active', 'deleted'], example: 'active'),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(property: 'is_reply', type: 'boolean', example: false),
        new OA\Property(property: 'user', ref: '#/components/schemas/UserResource'),
        new OA\Property(property: 'post', ref: '#/components/schemas/PostResource'),
        new OA\Property(property: 'parent', ref: '#/components/schemas/CommentResource'),
        new OA\Property(property: 'replies_count', type: 'integer', example: 2),
        new OA\Property(property: 'replies', type: 'array', items: new OA\Items(ref: '#/components/schemas/CommentResource')),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
class CommentResource extends JsonResource
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
            'content' => $this->content,
            'status' => $this->status,
            'is_active' => $this->isActive(),
            'is_reply' => $this->isReply(),
            'user' => new UserResource($this->whenLoaded('user')),
            'post' => new PostResource($this->whenLoaded('post')),
            'parent' => new CommentResource($this->whenLoaded('parent')),
            'replies_count' => $this->whenCounted('replies'),
            'replies' => CommentResource::collection($this->whenLoaded('replies')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
