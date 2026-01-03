<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\SkillStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\SkillResource;
use App\Models\Plan;
use App\Models\Skill;
use App\Models\SkillReference;
use App\Services\SkillService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;

/**
 * Skill API Controller
 *
 * Handles skill management and search via API.
 * Implements API Design requirements and FR-4B.7: Rate-limited search endpoint.
 */
class SkillController extends Controller
{
    public function __construct(
        private readonly SkillService $skillService
    ) {}

    /**
     * List all skill references.
     * GET /api/v1/skills
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->input('per_page', 50), 100);

        $skills = SkillReference::orderBy('skill_name')
            ->paginate($perPage);

        return response()->json([
            'data' => $skills->items(),
            'meta' => [
                'current_page' => $skills->currentPage(),
                'last_page' => $skills->lastPage(),
                'per_page' => $skills->perPage(),
                'total' => $skills->total(),
            ],
            'links' => [
                'first' => $skills->url(1),
                'last' => $skills->url($skills->lastPage()),
                'prev' => $skills->previousPageUrl(),
                'next' => $skills->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Search skills by name (EN or JP).
     * GET /api/v1/skills/search?q={query}
     *
     * Rate limited: 60 requests/minute per IP (FR-4B.7)
     */
    public function search(Request $request): JsonResponse
    {
        // Rate limiting: 60 requests per minute per IP
        $key = 'skill-search:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 60)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'error' => 'Too many requests',
                'message' => "Please wait {$seconds} seconds before searching again.",
                'retry_after' => $seconds,
            ], 429);
        }

        RateLimiter::hit($key, 60);

        $validated = $request->validate([
            'q' => 'required|string|min:1|max:100',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $query = $validated['q'];
        $limit = $validated['limit'] ?? 10;

        $skills = $this->skillService->search($query, $limit);

        return response()->json([
            'data' => $skills->map(fn($skill) => [
                'id' => $skill->id,
                'name' => $skill->skill_name,
                'description' => $skill->description,
                'tag' => $skill->tag,
            ]),
            'meta' => [
                'query' => $query,
                'count' => $skills->count(),
                'cached' => true,
            ],
        ]);
    }

    /**
     * Get skills for a specific plan.
     * GET /api/v1/plans/{plan}/skills
     */
    public function forPlan(Plan $plan, Request $request): JsonResponse
    {
        $status = $request->input('status');

        if ($status) {
            $skillStatus = SkillStatus::tryFrom($status);
            if (!$skillStatus) {
                return response()->json([
                    'error' => 'Invalid status',
                    'message' => 'Status must be one of: acquired, skipped, suggested',
                ], 400);
            }
            $skills = $this->skillService->getForPlanByStatus($plan, $skillStatus);
        } else {
            $skills = $this->skillService->getForPlan($plan);
        }

        return response()->json([
            'data' => SkillResource::collection($skills),
            'meta' => [
                'plan_id' => $plan->id,
                'count' => $skills->count(),
                'sp_totals' => $this->skillService->calculateSpTotals($plan),
                'status_counts' => $this->skillService->getStatusCounts($plan),
            ],
        ]);
    }

    /**
     * Add a skill to a plan.
     * POST /api/v1/plans/{plan}/skills
     */
    public function store(Request $request, Plan $plan): SkillResource|JsonResponse
    {
        $validated = $request->validate([
            'skill_reference_id' => 'required_without:skill_name|nullable|exists:skill_reference,id',
            'skill_name' => 'required_without:skill_reference_id|nullable|string|max:255',
            'status' => ['required', Rule::enum(SkillStatus::class)],
            'turn_acquired' => 'nullable|integer|min:1|max:78',
            'sp_cost' => 'nullable|integer|min:0',
            'tag' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $skill = $this->skillService->addToPlan($plan, $validated);

            return new SkillResource($skill->load('skillReference'));
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update a skill entry.
     * PUT /api/v1/plans/{plan}/skills/{skill}
     */
    public function update(Request $request, Plan $plan, Skill $skill): SkillResource|JsonResponse
    {
        // Verify the skill belongs to the plan
        if ($skill->plan_id !== $plan->id) {
            return response()->json([
                'error' => 'Skill not found',
                'message' => 'The skill does not belong to this plan.',
            ], 404);
        }

        $validated = $request->validate([
            'status' => ['sometimes', Rule::enum(SkillStatus::class)],
            'turn_acquired' => 'nullable|integer|min:1|max:78',
            'sp_cost' => 'nullable|integer|min:0',
            'tag' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $skill = $this->skillService->update($skill, $validated);

            return new SkillResource($skill->load('skillReference'));
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove a skill from a plan.
     * DELETE /api/v1/plans/{plan}/skills/{skill}
     */
    public function destroy(Plan $plan, Skill $skill): JsonResponse
    {
        // Verify the skill belongs to the plan
        if ($skill->plan_id !== $plan->id) {
            return response()->json([
                'error' => 'Skill not found',
                'message' => 'The skill does not belong to this plan.',
            ], 404);
        }

        $this->skillService->removeFromPlan($skill);

        return response()->json(null, 204);
    }

    /**
     * Get SP totals for a plan.
     * GET /api/v1/plans/{plan}/skills/totals
     */
    public function totals(Plan $plan): JsonResponse
    {
        return response()->json([
            'data' => $this->skillService->calculateSpTotals($plan),
            'meta' => [
                'plan_id' => $plan->id,
            ],
        ]);
    }
}
