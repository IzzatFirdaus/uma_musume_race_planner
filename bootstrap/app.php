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
        // Security Headers
        $middleware->append(\Illuminate\Http\Middleware\HandleCors::class);

        // API Middleware Groups
        $middleware->group('api', [
            'throttle:api',
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\App\Exceptions\ApiException $e) {
            return $e->render();
        });

        // Handle validation exceptions for API routes
        // Uses standard Laravel validation error format for compatibility with tests
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        // Handle model not found exceptions for API routes
        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'message' => 'Resource not found',
                        'code' => 404,
                    ],
                    'meta' => [
                        'timestamp' => now()->toISOString(),
                        'version' => 'v1',
                    ],
                ], 404);
            }
        });
    })->create();
