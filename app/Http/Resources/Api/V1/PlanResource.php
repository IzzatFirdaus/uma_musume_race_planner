<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Plan API Resource for consistent JSON responses.
 *
 * This resource transforms Plan model instances into structured JSON,
 * following Laravel best practices for API responses.
 */
class PlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request  The incoming HTTP request
     * @return array<string, mixed> Structured plan data
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'plan_title' => $this->plan_title,
            'name' => $this->name,
            'career_stage' => $this->career_stage,
            'class' => $this->class,
            'race_name' => $this->race_name,
            'status' => $this->status,
            'month' => $this->month,
            'time_of_day' => $this->time_of_day,
            'turn_before' => $this->turn_before,
            'turn_after' => $this->turn_after,
            'motivation_level' => $this->motivation_level,
            'trainee_image_path' => $this->trainee_image_path,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Relationships - conditionally loaded
            'mood' => $this->whenLoaded('mood'),
            'condition' => $this->whenLoaded('condition'),
            'strategy' => $this->whenLoaded('strategy'),
            'attributes' => $this->whenLoaded('attributes'),
            'skills' => $this->whenLoaded('skills', function () {
                return $this->skills->map(function ($skill) {
                    return [
                        'id' => $skill->id,
                        'skill_reference_id' => $skill->skill_reference_id,
                        'sp_cost' => $skill->sp_cost,
                        'acquired' => $skill->acquired,
                        'tag' => $skill->tag,
                        'notes' => $skill->notes,
                        'skill_reference' => $this->whenLoaded('skills.skillReference', $skill->skillReference),
                    ];
                });
            }),
            'goals' => $this->whenLoaded('goals'),
            'race_predictions' => $this->whenLoaded('racePredictions'),
            'terrain_grades' => $this->whenLoaded('terrainGrades'),
            'distance_grades' => $this->whenLoaded('distanceGrades'),
            'style_grades' => $this->whenLoaded('styleGrades'),
            'turns' => $this->whenLoaded('turns'),

            // API metadata
            'links' => [
                'self' => route('api.v1.plans.show', $this->id),
                'progress_chart' => route('api.v1.plans.progressChart', $this->id),
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
