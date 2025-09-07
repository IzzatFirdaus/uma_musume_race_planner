<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Plan Collection API Resource for handling multiple plan resources.
 *
 * This collection provides structured JSON responses for multiple plans,
 * including pagination metadata and consistent formatting.
 */
class PlanCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request  $request  The incoming HTTP request
     * @return array<string, mixed> Structured collection data
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => PlanResource::collection($this->collection),
            'meta' => [
                'count' => $this->collection->count(),
                'version' => 'v1',
                'timestamp' => now()->toISOString(),
            ],
            'links' => [
                'self' => route('api.v1.plans.index'),
            ],
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param  Request  $request  The incoming HTTP request
     * @return array<string, mixed> Additional response data
     */
    public function with(Request $request): array
    {
        return [
            'status' => 'success',
        ];
    }
}
