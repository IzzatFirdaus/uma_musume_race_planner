<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Stat Progress API Resource for consistent JSON responses.
 *
 * Transforms Turn model instances into structured JSON,
 * following Laravel best practices for API responses.
 */
class StatProgressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request  The incoming HTTP request
     * @return array<string, mixed> Structured stat progress data
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'plan_id' => $this->plan_id,
            'turn_number' => $this->turn_number,
            'speed' => $this->speed,
            'stamina' => $this->stamina,
            'power' => $this->power,
            'guts' => $this->guts,
            'wit' => $this->wit,
            'total' => $this->speed + $this->stamina + $this->power + $this->guts + $this->wit,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
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
            'meta' => [
                'version' => 'v1',
                'timestamp' => now()->toISOString(),
            ],
        ];
    }
}
