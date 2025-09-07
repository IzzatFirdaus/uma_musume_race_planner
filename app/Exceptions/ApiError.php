<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

/**
 * API Error for handling API-specific errors.
 *
 * Provides structured error responses for API endpoints
 * following consistent formatting standards.
 */
class ApiError extends Exception
{
    /**
     * @var int HTTP status code
     */
    protected int $statusCode;

    /**
     * @var array Additional error data
     */
    protected array $errorData;

    /**
     * Create a new API error instance.
     *
     * @param  string  $message  The error message
     * @param  int  $statusCode  The HTTP status code
     * @param  array  $errorData  Additional error data
     * @param  Exception|null  $previous  Previous exception
     */
    public function __construct(
        string $message = 'An API error occurred',
        int $statusCode = 500,
        array $errorData = [],
        ?Exception $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        $this->statusCode = $statusCode;
        $this->errorData = $errorData;
    }

    /**
     * Get the HTTP status code.
     *
     * @return int The status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get additional error data.
     *
     * @return array The error data
     */
    public function getErrorData(): array
    {
        return $this->errorData;
    }

    /**
     * Render the exception as an HTTP response.
     *
     * @return JsonResponse The JSON error response
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => [
                'message' => $this->getMessage(),
                'code' => $this->statusCode,
                'data' => $this->errorData,
            ],
            'meta' => [
                'timestamp' => now()->toISOString(),
                'version' => 'v1',
            ],
        ], $this->statusCode);
    }

    /**
     * Create a validation error response.
     *
     * @param  string  $message  The error message
     * @param  array  $errors  Validation errors
     * @return static The exception instance
     */
    public static function validation(string $message = 'Validation failed', array $errors = []): static
    {
    return new self($message, 422, ['validation_errors' => $errors]);
    }

    /**
     * Create a not found error response.
     *
     * @param  string  $message  The error message
     * @return static The exception instance
     */
    public static function notFound(string $message = 'Resource not found'): static
    {
    return new self($message, 404);
    }

    /**
     * Create an unauthorized error response.
     *
     * @param  string  $message  The error message
     * @return static The exception instance
     */
    public static function unauthorized(string $message = 'Unauthorized'): static
    {
    return new self($message, 401);
    }

    /**
     * Create a forbidden error response.
     *
     * @param  string  $message  The error message
     * @return static The exception instance
     */
    public static function forbidden(string $message = 'Forbidden'): static
    {
    return new self($message, 403);
    }

    /**
     * Create a rate limit exceeded error response.
     *
     * @param  string  $message  The error message
     * @return static The exception instance
     */
    public static function rateLimitExceeded(string $message = 'Rate limit exceeded'): static
    {
    return new self($message, 429);
    }

    /**
     * Create a server error response.
     *
     * @param  string  $message  The error message
     * @return static The exception instance
     */
    public static function serverError(string $message = 'Internal server error'): static
    {
    return new self($message, 500);
    }
}
