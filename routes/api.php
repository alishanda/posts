<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\Api\DocumentationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Документация API
Route::get('documentation', [DocumentationController::class, 'index']);
Route::get('swagger.json', function() {
    $controller = new \App\Http\Controllers\Api\DocumentationController();
    return response()->json(json_decode($controller->generateSwaggerJson()), 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
});

// Публичные маршруты
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Публичные маршруты для постов
Route::get('posts', [PostController::class, 'index']);
Route::get('posts/{post}', [PostController::class, 'show']);
Route::get('users/{userId}/posts/active', [PostController::class, 'getActivePostsByUser']);
Route::get('posts/{postId}/comments', [CommentController::class, 'getCommentsByPost']);

// Публичные маршруты для комментариев
Route::get('comments', [CommentController::class, 'index']);
Route::get('comments/replies', [CommentController::class, 'getAllReplies']);
Route::get('comments/{comment}', [CommentController::class, 'show']);
Route::get('users/{userId}/comments/active', [CommentController::class, 'getActiveCommentsByUser']);

// Защищенные маршруты
Route::middleware('auth:sanctum')->group(function () {
    // Аутентификация
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });

    // Посты (CRUD)
    Route::apiResource('posts', PostController::class)->except(['index', 'show']);
    Route::get('my/posts', [PostController::class, 'getMyPosts']);

    // Комментарии (CRUD)
    Route::post('comments/{post}', [CommentController::class, 'store']);
    Route::put('comments/{comment}', [CommentController::class, 'update']);
    Route::delete('comments/{comment}', [CommentController::class, 'destroy']);
    Route::get('my/comments', [CommentController::class, 'getMyComments']);
    
    // Ответы на комментарии
    Route::post('comments/{comment}/reply', [CommentController::class, 'replyToComment']);
    Route::get('comments/{comment}/replies', [CommentController::class, 'getRepliesForComment']);
});
