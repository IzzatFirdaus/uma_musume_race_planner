<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * UmaMusume API Resource for consistent JSON responses.
 *
 * Transforms Umamusume model instances into structured JSON,
 * following Laravel best practices for API responses.
 */
class UmaMusumeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request  The incoming HTTP request
     * @return array<string, mixed> Structured character data
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'nickname' => $this->nickname,
            'team' => $this->team,
            'release_batch' => $this->release_batch,
            'cv' => $this->cv,
            'birthday' => $this->birthday,
            'height_cm' => $this->height_cm,
            'weight' => $this->weight,
            'three_sizes' => $this->three_sizes,
            'images' => $this->images,
            'rarity' => $this->rarity,
            'growth_rates' => $this->growth_rates,
            'aptitudes' => $this->aptitudes,
            'base_stats' => $this->base_stats,
            'unique_skill' => $this->unique_skill,
            'skills' => $this->skills,
            'career_goals' => $this->career_goals,
            'tags' => $this->tags,
            'ui' => $this->ui,
            'links' => $this->links,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // API metadata
            '_links' => [
                'self' => route('api.v1.umamusume.show', $this->id),
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
            'meta' => [
                'version' => 'v1',
                'timestamp' => now()->toISOString(),
            ],
        ];
    }
}
