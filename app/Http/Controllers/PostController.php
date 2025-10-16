<?php

namespace App\Http\Controllers;

use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Http\Resources\ApiResponseResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PostController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Post::with(['user', 'comments.user']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $posts = $query->orderBy('created_at', 'desc')->paginate(15);

        return PostResource::collection($posts);
    }

    public function store(StorePostRequest $request): JsonResponse
    {
        $post = Post::create([
            'title' => $request->title,
            'content' => $request->content,
            'status' => $request->status ?? Post::STATUS_DRAFT,
            'user_id' => $request->user()->id,
        ]);

        $post->load(['user', 'comments.user']);

        return ApiResponseResource::success(new PostResource($post), 'Пост успешно создан', 201);
    }

    public function show(Post $post): JsonResponse
    {
        $post->load(['user', 'comments.user', 'comments.replies.user']);

        return ApiResponseResource::success(new PostResource($post));
    }

    public function update(UpdatePostRequest $request, Post $post): JsonResponse
    {
        if ($post->user_id !== $request->user()->id) {
            return ApiResponseResource::unauthorized('Недостаточно прав для редактирования этого поста');
        }

        $post->update($request->only(['title', 'content', 'status']));
        $post->load(['user', 'comments.user']);

        return ApiResponseResource::success(new PostResource($post), 'Пост успешно обновлен');
    }

    public function destroy(Request $request, Post $post): JsonResponse
    {
        if ($post->user_id !== $request->user()->id) {
            return ApiResponseResource::unauthorized('Недостаточно прав для удаления этого поста');
        }

        $post->delete();

        return ApiResponseResource::success(null, 'Пост успешно удален');
    }

    public function getActivePostsByUser(int $userId): AnonymousResourceCollection
    {
        $posts = Post::with(['user', 'comments.user'])
            ->where('user_id', $userId)
            ->active()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return PostResource::collection($posts);
    }

    public function getMyPosts(Request $request): AnonymousResourceCollection
    {
        $posts = $request->user()
            ->posts()
            ->with(['user', 'comments.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return PostResource::collection($posts);
    }
}
