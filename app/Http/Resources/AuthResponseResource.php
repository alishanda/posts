<?php

namespace App\Http\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AuthResponseResource',
    title: 'Authentication Response Resource',
    description: 'Ресурс ответа аутентификации',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Пользователь успешно авторизован'),
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'user', ref: '#/components/schemas/UserResource'),
        new OA\Property(property: 'token', type: 'string', example: '1|abcdef123456789'),
    ]
)]
class AuthResponseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'message' => $this->resource['message'] ?? 'Операция выполнена успешно',
            'success' => $this->resource['success'] ?? true,
            'user' => new UserResource($this->resource['user']),
            'token' => $this->resource['token'] ?? null,
        ];
    }

    public static function success($user, string $token, string $message = 'Операция выполнена успешно', int $status = 200): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'success' => true,
            'user' => new UserResource($user),
            'token' => $token,
        ], $status);
    }

    public static function registered($user, string $token): JsonResponse
    {
        return self::success($user, $token, 'Пользователь успешно зарегистрирован', 201);
    }

    public static function loggedIn($user, string $token): JsonResponse
    {
        return self::success($user, $token, 'Успешный вход в систему');
    }

    public static function loggedOut(): JsonResponse
    {
        return response()->json([
            'message' => 'Успешный выход из системы',
            'success' => true,
        ]);
    }
}
