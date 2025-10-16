<?php

namespace App\Http\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ApiResponseResource',
    title: 'API Response Resource',
    description: 'Стандартный ответ API',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Операция выполнена успешно'),
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'data', type: 'object'),
        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(type: 'string')),
    ]
)]
class ApiResponseResource extends JsonResource
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
            'data' => $this->resource['data'] ?? null,
            'errors' => $this->resource['errors'] ?? null,
        ];
    }

    public static function success($data = null, string $message = 'Операция выполнена успешно', int $status = 200): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'success' => true,
            'data' => $data,
        ], $status);
    }

    
    public static function error(string $message = 'Произошла ошибка', $errors = null, int $status = 400): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'success' => false,
            'errors' => $errors,
        ], $status);
    }

    public static function validationError($errors, string $message = 'Ошибки валидации'): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'success' => false,
            'errors' => $errors,
        ], 422);
    }

   
    public static function unauthorized(string $message = 'Недостаточно прав доступа'): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'success' => false,
        ], 403);
    }

    
    public static function notFound(string $message = 'Ресурс не найден'): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'success' => false,
        ], 404);
    }
}
