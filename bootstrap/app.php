<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Remove CSRF protection for API routes
        $middleware->api(remove: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Ошибки валидации.',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (\Exception $e, $request) {
            if ($request->is('api/*') && !($e instanceof \Illuminate\Validation\ValidationException)) {
                return response()->json([
                    'message' => 'Произошла ошибка сервера.',
                    'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error',
                ], 500);
            }
        });
    })->create();
