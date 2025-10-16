<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DocumentationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/documentation",
     *     summary="API Documentation",
     *     tags={"Documentation"},
     *     @OA\Response(
     *         response=200,
     *         description="Swagger UI documentation page"
     *     )
     * )
     */
    public function index()
    {
        $swaggerJson = $this->generateSwaggerJson();
        
        return response()->view('swagger', ['swaggerJson' => $swaggerJson]);
    }

    public function generateSwaggerJson()
    {
        return json_encode([
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'Posts API',
                'version' => '1.0.0',
                'description' => 'API для управления постами и комментариями',
            ],
            'servers' => [
                ['url' => 'http://localhost:8000/api', 'description' => 'Local server']
            ],
            'paths' => [
                '/auth/register' => [
                    'post' => [
                        'tags' => ['Authentication'],
                        'summary' => 'Регистрация пользователя',
                        'parameters' => [
                            ['name' => 'Accept', 'in' => 'header', 'required' => false, 'schema' => ['type' => 'string', 'default' => 'application/json'], 'description' => 'Рекомендуется для получения JSON ответов']
                        ],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['name', 'email', 'password', 'password_confirmation'],
                                        'properties' => [
                                            'name' => ['type' => 'string'],
                                            'email' => ['type' => 'string', 'format' => 'email'],
                                            'password' => ['type' => 'string', 'minLength' => 6],
                                            'password_confirmation' => ['type' => 'string', 'minLength' => 6]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'responses' => [
                            '201' => [
                                'description' => 'Пользователь создан',
                                'content' => [
                                    'application/json' => [
                                        'schema' => ['$ref' => '#/components/schemas/AuthResponseResource']
                                    ]
                                ]
                            ],
                            '422' => [
                                'description' => 'Ошибка валидации',
                                'content' => [
                                    'application/json' => [
                                        'schema' => ['$ref' => '#/components/schemas/ApiResponseResource']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                '/auth/login' => [
                    'post' => [
                        'tags' => ['Authentication'],
                        'summary' => 'Авторизация пользователя',
                        'parameters' => [
                            ['name' => 'Accept', 'in' => 'header', 'required' => false, 'schema' => ['type' => 'string', 'default' => 'application/json'], 'description' => 'Рекомендуется для получения JSON ответов']
                        ],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['email', 'password'],
                                        'properties' => [
                                            'email' => ['type' => 'string', 'format' => 'email'],
                                            'password' => ['type' => 'string']
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'responses' => [
                            '200' => ['description' => 'Успешная авторизация'],
                            '401' => ['description' => 'Неверные учетные данные']
                        ]
                    ]
                ],
                '/posts' => [
                    'get' => [
                        'tags' => ['Posts'],
                        'summary' => 'Получить список постов',
                        'responses' => [
                            '200' => ['description' => 'Список постов']
                        ]
                    ],
                    'post' => [
                        'tags' => ['Posts'],
                        'summary' => 'Создать пост',
                        'security' => [['sanctum' => []]],
                        'parameters' => [
                            ['name' => 'Accept', 'in' => 'header', 'required' => false, 'schema' => ['type' => 'string', 'default' => 'application/json'], 'description' => 'Рекомендуется для получения JSON ответов']
                        ],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['title', 'content'],
                                        'properties' => [
                                            'title' => ['type' => 'string'],
                                            'content' => ['type' => 'string'],
                                            'status' => ['type' => 'string', 'enum' => ['draft', 'published', 'archived']]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'responses' => [
                            '201' => ['description' => 'Пост создан'],
                            '401' => ['description' => 'Не авторизован']
                        ]
                    ]
                ],
                '/posts/{id}' => [
                    'get' => [
                        'tags' => ['Posts'],
                        'summary' => 'Получить пост по ID',
                        'parameters' => [
                            ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]
                        ],
                        'responses' => [
                            '200' => ['description' => 'Пост найден'],
                            '404' => ['description' => 'Пост не найден']
                        ]
                    ],
                    'put' => [
                        'tags' => ['Posts'],
                        'summary' => 'Обновить пост',
                        'security' => [['sanctum' => []]],
                        'parameters' => [
                            ['name' => 'Accept', 'in' => 'header', 'required' => false, 'schema' => ['type' => 'string', 'default' => 'application/json'], 'description' => 'Рекомендуется для получения JSON ответов'],
                            ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]
                        ],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'title' => ['type' => 'string', 'description' => 'Заголовок поста'],
                                            'content' => ['type' => 'string', 'description' => 'Содержимое поста'],
                                            'status' => ['type' => 'string', 'enum' => ['draft', 'published', 'archived'], 'description' => 'Статус поста']
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Пост обновлен',
                                'content' => [
                                    'application/json' => [
                                        'schema' => ['$ref' => '#/components/schemas/ApiResponseResource']
                                    ]
                                ]
                            ],
                            '403' => [
                                'description' => 'Нет прав на редактирование',
                                'content' => [
                                    'application/json' => [
                                        'schema' => ['$ref' => '#/components/schemas/ApiResponseResource']
                                    ]
                                ]
                            ],
                            '422' => [
                                'description' => 'Ошибка валидации',
                                'content' => [
                                    'application/json' => [
                                        'schema' => ['$ref' => '#/components/schemas/ApiResponseResource']
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'delete' => [
                        'tags' => ['Posts'],
                        'summary' => 'Удалить пост',
                        'security' => [['sanctum' => []]],
                        'parameters' => [
                            ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]
                        ],
                        'responses' => [
                            '204' => ['description' => 'Пост удален'],
                            '403' => ['description' => 'Нет прав на удаление']
                        ]
                    ]
                ],
                '/comments' => [
                    'get' => [
                        'tags' => ['Comments'],
                        'summary' => 'Получить список комментариев',
                        'responses' => [
                            '200' => ['description' => 'Список комментариев']
                        ]
                    ]
                ],
                '/comments/{postId}' => [
                    'post' => [
                        'tags' => ['Comments'],
                        'summary' => 'Создать комментарий к посту',
                        'security' => [['sanctum' => []]],
                        'parameters' => [
                            ['name' => 'Accept', 'in' => 'header', 'required' => false, 'schema' => ['type' => 'string', 'default' => 'application/json'], 'description' => 'Рекомендуется для получения JSON ответов'],
                            ['name' => 'postId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]
                        ],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['content'],
                                        'properties' => [
                                            'content' => ['type' => 'string', 'example' => 'Это отличный пост!'],
                                            'parent_id' => ['type' => 'integer', 'nullable' => true, 'description' => 'ID родительского комментария для создания ответа. Оставьте пустым или null для обычного комментария. Используйте существующий ID комментария для создания ответа.']
                                        ],
                                        'examples' => [
                                            'ordinary_comment' => [
                                                'summary' => 'Обычный комментарий',
                                                'value' => [
                                                    'content' => 'Это отличный пост!'
                                                ]
                                            ],
                                            'reply_comment' => [
                                                'summary' => 'Ответ на комментарий',
                                                'value' => [
                                                    'content' => 'Согласен с вашим мнением!',
                                                    'parent_id' => 1
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'responses' => [
                            '201' => ['description' => 'Комментарий создан'],
                            '401' => ['description' => 'Не авторизован'],
                            '403' => ['description' => 'Нельзя комментировать неактивные посты'],
                            '404' => ['description' => 'Пост не найден']
                        ]
                    ]
                ],
                '/comments/{id}' => [
                    'get' => [
                        'tags' => ['Comments'],
                        'summary' => 'Получить комментарий по ID',
                        'parameters' => [
                            ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]
                        ],
                        'responses' => [
                            '200' => ['description' => 'Комментарий найден'],
                            '404' => ['description' => 'Комментарий не найден']
                        ]
                    ],
                    'put' => [
                        'tags' => ['Comments'],
                        'summary' => 'Обновить комментарий',
                        'security' => [['sanctum' => []]],
                        'parameters' => [
                            ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]
                        ],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['content'],
                                        'properties' => [
                                            'content' => ['type' => 'string']
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'responses' => [
                            '200' => ['description' => 'Комментарий обновлен'],
                            '403' => ['description' => 'Нет прав на редактирование']
                        ]
                    ],
                    'delete' => [
                        'tags' => ['Comments'],
                        'summary' => 'Удалить комментарий',
                        'security' => [['sanctum' => []]],
                        'parameters' => [
                            ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]
                        ],
                        'responses' => [
                            '204' => ['description' => 'Комментарий удален'],
                            '403' => ['description' => 'Нет прав на удаление']
                        ]
                    ]
                ],
                '/comments/{id}/replies' => [
                    'get' => [
                        'tags' => ['Comments'],
                        'summary' => 'Получить ответы на комментарий',
                        'parameters' => [
                            ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]
                        ],
                        'responses' => [
                            '200' => ['description' => 'Список ответов на комментарий']
                        ]
                    ]
                ],
                '/comments/{id}/reply' => [
                    'post' => [
                        'tags' => ['Comments'],
                        'summary' => 'Ответить на комментарий',
                        'security' => [['sanctum' => []]],
                        'parameters' => [
                            ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]
                        ],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['content'],
                                        'properties' => [
                                            'content' => ['type' => 'string']
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'responses' => [
                            '201' => ['description' => 'Ответ на комментарий создан'],
                            '401' => ['description' => 'Не авторизован'],
                            '403' => ['description' => 'Нельзя отвечать на неактивные комментарии']
                        ]
                    ]
                ],
                '/my/posts' => [
                    'get' => [
                        'tags' => ['Posts'],
                        'summary' => 'Получить мои посты',
                        'security' => [['sanctum' => []]],
                        'responses' => [
                            '200' => ['description' => 'Список постов текущего пользователя']
                        ]
                    ]
                ],
                '/my/comments' => [
                    'get' => [
                        'tags' => ['Comments'],
                        'summary' => 'Получить мои комментарии',
                        'security' => [['sanctum' => []]],
                        'responses' => [
                            '200' => ['description' => 'Список комментариев текущего пользователя']
                        ]
                    ]
                ],
                '/users/{userId}/posts/active' => [
                    'get' => [
                        'tags' => ['Posts'],
                        'summary' => 'Получить активные посты пользователя',
                        'parameters' => [
                            ['name' => 'userId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]
                        ],
                        'responses' => [
                            '200' => ['description' => 'Список активных постов пользователя']
                        ]
                    ]
                ],
                        '/posts/{postId}/comments' => [
                            'get' => [
                                'tags' => ['Comments'],
                                'summary' => 'Получить комментарии поста',
                                'parameters' => [
                                    ['name' => 'postId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]
                                ],
                                'responses' => [
                                    '200' => ['description' => 'Список комментариев поста']
                                ]
                            ]
                        ],
                        '/comments/replies' => [
                            'get' => [
                                'tags' => ['Comments'],
                                'summary' => 'Получить все ответы на комментарии',
                                'responses' => [
                                    '200' => ['description' => 'Список всех ответов на комментарии']
                                ]
                            ]
                        ],
                        '/users/{userId}/comments/active' => [
                            'get' => [
                                'tags' => ['Comments'],
                                'summary' => 'Получить активные комментарии пользователя к активным постам',
                                'parameters' => [
                                    ['name' => 'userId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]
                                ],
                                'responses' => [
                                    '200' => ['description' => 'Список активных комментариев пользователя к активным постам']
                                ]
                            ]
                        ],
            ],
            'components' => [
                'parameters' => [
                    'AcceptHeader' => [
                        'name' => 'Accept',
                        'in' => 'header',
                        'required' => false,
                        'schema' => ['type' => 'string', 'default' => 'application/json'],
                        'description' => 'Рекомендуется для получения JSON ответов вместо HTML'
                    ]
                ],
                'securitySchemes' => [
                    'sanctum' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'JWT'
                    ]
                ],
                'schemas' => [
                    'UserResource' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer', 'example' => 1],
                            'name' => ['type' => 'string', 'example' => 'Иван Иванов'],
                            'email' => ['type' => 'string', 'format' => 'email', 'example' => 'ivan@example.com'],
                            'email_verified_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                            'created_at' => ['type' => 'string', 'format' => 'date-time'],
                            'updated_at' => ['type' => 'string', 'format' => 'date-time'],
                        ]
                    ],
                    'PostResource' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer', 'example' => 1],
                            'title' => ['type' => 'string', 'example' => 'Заголовок поста'],
                            'content' => ['type' => 'string', 'example' => 'Содержимое поста'],
                            'status' => ['type' => 'string', 'enum' => ['draft', 'published', 'archived'], 'example' => 'published'],
                            'is_active' => ['type' => 'boolean', 'example' => true],
                            'user' => ['$ref' => '#/components/schemas/UserResource'],
                            'comments_count' => ['type' => 'integer', 'example' => 5],
                            'comments' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/CommentResource']],
                            'created_at' => ['type' => 'string', 'format' => 'date-time'],
                            'updated_at' => ['type' => 'string', 'format' => 'date-time'],
                        ]
                    ],
                    'CommentResource' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer', 'example' => 1],
                            'content' => ['type' => 'string', 'example' => 'Текст комментария'],
                            'status' => ['type' => 'string', 'enum' => ['pending', 'active', 'deleted'], 'example' => 'active'],
                            'is_active' => ['type' => 'boolean', 'example' => true],
                            'is_reply' => ['type' => 'boolean', 'example' => false],
                            'user' => ['$ref' => '#/components/schemas/UserResource'],
                            'post' => ['$ref' => '#/components/schemas/PostResource'],
                            'parent' => ['$ref' => '#/components/schemas/CommentResource'],
                            'replies_count' => ['type' => 'integer', 'example' => 2],
                            'replies' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/CommentResource']],
                            'created_at' => ['type' => 'string', 'format' => 'date-time'],
                            'updated_at' => ['type' => 'string', 'format' => 'date-time'],
                        ]
                    ],
                    'AuthResponseResource' => [
                        'type' => 'object',
                        'properties' => [
                            'message' => ['type' => 'string', 'example' => 'Пользователь успешно зарегистрирован'],
                            'success' => ['type' => 'boolean', 'example' => true],
                            'user' => ['$ref' => '#/components/schemas/UserResource'],
                            'token' => ['type' => 'string', 'example' => '1|abcdef123456789'],
                        ]
                    ],
                    'ApiResponseResource' => [
                        'type' => 'object',
                        'properties' => [
                            'message' => ['type' => 'string', 'example' => 'Операция выполнена успешно'],
                            'success' => ['type' => 'boolean', 'example' => true],
                            'data' => ['type' => 'object'],
                            'errors' => ['type' => 'array', 'items' => ['type' => 'string']],
                        ]
                    ]
                ]
            ]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
