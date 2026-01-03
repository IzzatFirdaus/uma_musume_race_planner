<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\StatProgressResource;
use App\Models\Plan;
use App\Models\Turn;
use App\Services\StatProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Stat Progress API Controller
 *
 * Handles stat progression tracking via API.
 * Implements API Design requirements from design.md.
 */
class StatProgressController extends Controller
{
    public function __construct(
        private readonly StatProgressService $statProgressService
    ) {}

    /**
     * Get all stat progress entries for a plan.
     * GET /api/v1/plans/{plan}/stats
     */
    public function index(Plan $plan, Request $request): JsonResponse
    {
        $paginate = $request->boolean('paginate', false);
        $perPage = min((int) $request->input('per_page', 20), 100);

        if ($paginate) {
            $turns = $this->statProgressService->getForPlanPaginated($plan, $perPage);

            return response()->json([
                'data' => StatProgressResource::collection($turns),
                'meta' => [
                    'current_page' => $turns->currentPage(),
                    'last_page' => $turns->lastPage(),
                    'per_page' => $turns->perPage(),
                    'total' => $turns->total(),
                    'plan_id' => $plan->id,
                ],
                'links' => [
                    'first' => $turns->url(1),
                    'last' => $turns->url($turns->lastPage()),
                    'prev' => $turns->previousPageUrl(),
                    'next' => $turns->nextPageUrl(),
                ],
            ]);
        }

        $turns = $this->statProgressService->getForPlan($plan);

        return response()->json([
            'data' => StatProgressResource::collection($turns),
            'meta' => [
                'count' => $turns->count(),
                'plan_id' => $plan->id,
            ],
        ]);
    }

    /**
     * Store a new stat entry for a plan.
     * POST /api/v1/plans/{plan}/stats
     */
    public function store(Request $request, Plan $plan): StatProgressResource
    {
        $validated = $request->validate([
            'turn_number' => 'nullable|integer|min:1|max:78',
            'speed' => 'required|integer|min:0|max:2000',
            'stamina' => 'required|integer|min:0|max:2000',
            'power' => 'required|integer|min:0|max:2000',
            'guts' => 'required|integer|min:0|max:2000',
            'wit' => 'required|integer|min:0|max:2000',
        ]);

        $turn = $this->statProgressService->logTurn($plan, $validated);

        return new StatProgressResource($turn);
    }

    /**
     * Display a specific stat entry.
     * GET /api/v1/plans/{plan}/stats/{stat}
     */
    public function show(Plan $plan, Turn $stat): StatProgressResource|JsonResponse
    {
        // Verify the stat belongs to the plan
        if ($stat->plan_id !== $plan->id) {
            return response()->json([
                'error' => 'Stat entry not found',
                'message' => 'The stat entry does not belong to this plan.',
            ], 404);
        }

        return new StatProgressResource($stat);
    }

    /**
     * Update a stat entry.
     * PUT /api/v1/plans/{plan}/stats/{stat}
     */
    public function update(Request $request, Plan $plan, Turn $stat): StatProgressResource|JsonResponse
    {
        // Verify the stat belongs to the plan
        if ($stat->plan_id !== $plan->id) {
            return response()->json([
                'error' => 'Stat entry not found',
                'message' => 'The stat entry does not belong to this plan.',
            ], 404);
        }

        $validated = $request->validate([
            'speed' => 'sometimes|integer|min:0|max:2000',
            'stamina' => 'sometimes|integer|min:0|max:2000',
            'power' => 'sometimes|integer|min:0|max:2000',
            'guts' => 'sometimes|integer|min:0|max:2000',
            'wit' => 'sometimes|integer|min:0|max:2000',
        ]);

        $turn = $this->statProgressService->updateTurn($stat, $validated);

        return new StatProgressResource($turn);
    }

    /**
     * Delete a stat entry.
     * DELETE /api/v1/plans/{plan}/stats/{stat}
     */
    public function destroy(Plan $plan, Turn $stat): JsonResponse
    {
        // Verify the stat belongs to the plan
        if ($stat->plan_id !== $plan->id) {
            return response()->json([
                'error' => 'Stat entry not found',
                'message' => 'The stat entry does not belong to this plan.',
            ], 404);
        }

        $this->statProgressService->deleteTurn($stat);

        return response()->json(null, 204);
    }

    /**
     * Get stat totals for a plan.
     * GET /api/v1/plans/{plan}/stats/totals
     */
    public function totals(Plan $plan): JsonResponse
    {
        $totals = $this->statProgressService->getStatTotals($plan);

        return response()->json([
            'data' => $totals,
            'meta' => [
                'plan_id' => $plan->id,
            ],
        ]);
    }

    /**
     * Get stat averages for a plan.
     * GET /api/v1/plans/{plan}/stats/averages
     */
    public function averages(Plan $plan): JsonResponse
    {
        $averages = $this->statProgressService->getStatAverages($plan);

        return response()->json([
            'data' => $averages,
            'meta' => [
                'plan_id' => $plan->id,
            ],
        ]);
    }

    /**
     * Get chart data for stat progression.
     * GET /api/v1/plans/{plan}/stats/chart
     */
    public function chart(Plan $plan): JsonResponse
    {
        $chartData = $this->statProgressService->getChartData($plan);

        return response()->json([
            'data' => $chartData,
            'meta' => [
                'plan_id' => $plan->id,
            ],
        ]);
    }

    /**
     * Get stat summary (min, max, current).
     * GET /api/v1/plans/{plan}/stats/summary
     */
    public function summary(Plan $plan): JsonResponse
    {
        $summary = $this->statProgressService->getStatSummary($plan);

        return response()->json([
            'data' => $summary,
            'meta' => [
                'plan_id' => $plan->id,
            ],
        ]);
    }
}
