<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * UmaMusume Collection API Resource for handling multiple character resources.
 *
 * Provides structured JSON responses for multiple characters,
 * including pagination metadata and consistent formatting.
 */
class UmaMusumeCollection extends ResourceCollection
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
            'data' => UmaMusumeResource::collection($this->collection),
            'meta' => [
                'current_page' => $this->currentPage(),
                'last_page' => $this->lastPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
                'version' => 'v1',
                'timestamp' => now()->toISOString(),
            ],
            'links' => [
                'self' => route('api.v1.umamusume.index'),
                'first' => $this->url(1),
                'last' => $this->url($this->lastPage()),
                'prev' => $this->previousPageUrl(),
                'next' => $this->nextPageUrl(),
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
