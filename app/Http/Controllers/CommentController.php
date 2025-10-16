<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Http\Resources\ApiResponseResource;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CommentController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Comment::with(['user', 'post', 'parent']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('post_id')) {
            $query->where('post_id', $request->post_id);
        }

        if ($request->has('replies_only') && $request->replies_only) {
            $query->replies();
        }

        $comments = $query->orderBy('created_at', 'desc')->paginate(15);

        return CommentResource::collection($comments);
    }

    public function store(StoreCommentRequest $request, Post $post): JsonResponse
    {
        if (!$post->isActive()) {
            return ApiResponseResource::error('Нельзя комментировать неактивные посты', null, 403);
        }

        $comment = Comment::create([
            'content' => $request->content,
            'status' => Comment::STATUS_PENDING,
            'user_id' => $request->user()->id,         
            'post_id' => $post->id,
            'parent_id' => $request->parent_id,
        ]);

        $comment->load(['user', 'post', 'parent']);

        return ApiResponseResource::success(new CommentResource($comment), 'Комментарий успешно создан и ожидает модерации', 201);
    }

    public function show(Comment $comment): JsonResponse
    {
        $comment->load(['user', 'post', 'parent', 'replies.user']);

        return ApiResponseResource::success(new CommentResource($comment));
    }

    public function update(UpdateCommentRequest $request, Comment $comment): JsonResponse
    {
        if ($comment->user_id !== $request->user()->id) {
            return ApiResponseResource::unauthorized('Недостаточно прав для редактирования этого комментария');
        }

        $comment->update($request->only(['content']));
        $comment->load(['user', 'post', 'parent']);

        return ApiResponseResource::success(new CommentResource($comment), 'Комментарий успешно обновлен');
    }

    public function destroy(Request $request, Comment $comment): JsonResponse
    {
        if ($comment->user_id !== $request->user()->id) {
            return ApiResponseResource::unauthorized('Недостаточно прав для удаления этого комментария');
        }

        $comment->delete();

        return ApiResponseResource::success(null, 'Комментарий успешно удален');
    }

    public function getMyComments(Request $request): AnonymousResourceCollection
    {
        $comments = $request->user()
            ->comments()
            ->with(['user', 'post', 'parent'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return CommentResource::collection($comments);
    }

    public function getCommentsByPost(int $postId): AnonymousResourceCollection
    {
        $comments = Comment::with(['user', 'post', 'parent'])
            ->where('post_id', $postId)
            ->active()
            ->orderBy('created_at', 'asc')
            ->paginate(15);

        return CommentResource::collection($comments);
    }

    public function getRepliesForComment(Comment $comment): AnonymousResourceCollection
    {
        $replies = $comment->replies()
            ->with(['user', 'post', 'parent'])
            ->active()
            ->orderBy('created_at', 'asc')
            ->paginate(15);

        return CommentResource::collection($replies);
    }

    public function replyToComment(StoreCommentRequest $request, Comment $parentComment): JsonResponse
    {
        if (!$parentComment->isActive()) {
            return ApiResponseResource::error('Нельзя отвечать на неактивные комментарии', null, 403);
        }

        if (!$parentComment->post->isActive()) {
            return ApiResponseResource::error('Нельзя отвечать на комментарии к неактивным постам', null, 403);
        }

        $reply = Comment::create([
            'content' => $request->content,
            'status' => Comment::STATUS_PENDING,
            'user_id' => $request->user()->id,
            'post_id' => $parentComment->post_id,
            'parent_id' => $parentComment->id,
        ]);

        $reply->load(['user', 'post', 'parent']);

        return ApiResponseResource::success(new CommentResource($reply), 'Ответ на комментарий успешно создан и ожидает модерации', 201);
    }

    public function getAllReplies(Request $request): AnonymousResourceCollection
    {
        $replies = Comment::with(['user', 'post', 'parent'])
            ->replies()
            ->active()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return CommentResource::collection($replies);
    }

    public function getActiveCommentsByUser(int $userId): AnonymousResourceCollection
    {
        $comments = Comment::with(['user', 'post', 'parent'])
            ->where('user_id', $userId)
            ->active()
            ->whereHas('post', function ($query) {
                $query->where('status', Post::STATUS_PUBLISHED);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return CommentResource::collection($comments);
    }
}
