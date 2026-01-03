<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Skill API Resource for consistent JSON responses.
 *
 * Transforms Skill model instances into structured JSON,
 * following Laravel best practices for API responses.
 */
class SkillResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request  The incoming HTTP request
     * @return array<string, mixed> Structured skill data
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'plan_id' => $this->plan_id,
            'skill_reference_id' => $this->skill_reference_id,
            'status' => $this->status?->value,
            'turn_acquired' => $this->turn_acquired,
            'sp_cost' => $this->sp_cost,
            'tag' => $this->tag,
            'notes' => $this->notes,
            'acquired' => $this->acquired,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Include skill reference details when loaded
            'skill_reference' => $this->whenLoaded('skillReference', function () {
                return [
                    'id' => $this->skillReference->id,
                    'name' => $this->skillReference->skill_name,
                    'description' => $this->skillReference->description,
                    'tag' => $this->skillReference->tag,
                ];
            }),
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
