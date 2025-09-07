<?php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Configure API rate limiting
            RateLimiter::for('api', function (Request $request) {
                return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
            });
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Security Headers
        $middleware->append(\Illuminate\Http\Middleware\HandleCors::class);

        // API Middleware Groups
        $middleware->group('api', [
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\App\Exceptions\ApiException $e) {
            return $e->render();
        });

        // Handle validation exceptions for API routes
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'message' => 'Validation failed',
                        'code' => 422,
                        'data' => [
                            'validation_errors' => $e->errors(),
                        ],
                    ],
                    'meta' => [
                        'timestamp' => now()->toISOString(),
                        'version' => 'v1',
                    ],
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
