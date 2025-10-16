<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Posts API',
    description: 'API для управления постами и комментариями'
)]
#[OA\Server(
    url: 'http://localhost:8000/api',
    description: 'API Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Sanctum'
)]
abstract class Controller
{
}