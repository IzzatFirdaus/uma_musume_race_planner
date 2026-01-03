<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SkillStatus;
use App\Models\CareerSnapshot;
use App\Models\Plan;
use App\Models\RacePrediction;
use Illuminate\Database\Eloquent\Collection;

/**
 * Snapshot Service Class
 *
 * Handles immutable race-day snapshot creation and management.
 * Implements FR-12.1, FR-12.2: Store immutable snapshot of run state at a given turn/race.
 */
class SnapshotService
{
    /**
     * Create a snapshot from a plan's current state.
     * Implements FR-12.3: User can click "Create Snapshot" from RunDetail at any time.
     */
    public function createFromPlan(
        Plan $plan,
        int $turnNumber,
        ?string $raceName = null,
        ?RacePrediction $racePrediction = null,
        ?string $notes = null
    ): CareerSnapshot {
        // Get current stats from the plan's latest turn
        $latestTurn = $plan->turns()
            ->where('turn_number', '<=', $turnNumber)
            ->orderBy('turn_number', 'desc')
            ->first();

        // Get acquired skills at this point
        $acquiredSkills = $this->getAcquiredSkillsSnapshot($plan, $turnNumber);

        return CareerSnapshot::create([
            'plan_id' => $plan->id,
            'race_prediction_id' => $racePrediction?->id,
            'turn_number' => $turnNumber,
            'race_name' => $raceName ?? $racePrediction?->race_name,
            'speed' => $latestTurn?->speed ?? 0,
            'stamina' => $latestTurn?->stamina ?? 0,
            'power' => $latestTurn?->power ?? 0,
            'guts' => $latestTurn?->guts ?? 0,
            'wit' => $latestTurn?->wit ?? 0,
            'total_sp_available' => $plan->total_available_skill_points,
            'stamina_percentage' => $plan->stamina_percentage ?? $plan->energy,
            'mood' => $plan->mood?->label ?? $plan->mood?->name,
            'conditions' => $plan->condition?->label ?? $plan->condition?->name,
            'skills_snapshot' => $acquiredSkills,
            'notes' => $notes,
        ]);
    }

    /**
     * Get all snapshots for a plan.
     * Implements FR-12.6: Snapshot list shows turn number, race name, timestamp.
     */
    public function getForPlan(Plan $plan): Collection
    {
        return $plan->careerSnapshots()
            ->orderBy('turn_number')
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Get a specific snapshot.
     */
    public function findById(int $id): ?CareerSnapshot
    {
        return CareerSnapshot::find($id);
    }

    /**
     * Get a specific snapshot or fail.
     */
    public function findOrFail(int $id): CareerSnapshot
    {
        return CareerSnapshot::findOrFail($id);
    }

    /**
     * Delete a snapshot.
     * Implements FR-12.4: Snapshots are immutable once created (no edits, only delete).
     */
    public function delete(CareerSnapshot $snapshot): bool
    {
        return (bool) $snapshot->delete();
    }

    /**
     * Get snapshots for a specific race prediction.
     */
    public function getForRacePrediction(RacePrediction $racePrediction): Collection
    {
        return CareerSnapshot::where('race_prediction_id', $racePrediction->id)
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Get the latest snapshot for a plan.
     */
    public function getLatestForPlan(Plan $plan): ?CareerSnapshot
    {
        return $plan->careerSnapshots()
            ->orderBy('turn_number', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Get snapshot at a specific turn.
     */
    public function getAtTurn(Plan $plan, int $turnNumber): ?CareerSnapshot
    {
        return $plan->careerSnapshots()
            ->where('turn_number', $turnNumber)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Export snapshot data for external use.
     * Implements FR-12.5: Snapshots can be exported individually or with run.
     */
    public function exportSnapshot(CareerSnapshot $snapshot): array
    {
        return [
            'id' => $snapshot->id,
            'plan_id' => $snapshot->plan_id,
            'turn_number' => $snapshot->turn_number,
            'race_name' => $snapshot->race_name,
            'stats' => [
                'speed' => $snapshot->speed,
                'stamina' => $snapshot->stamina,
                'power' => $snapshot->power,
                'guts' => $snapshot->guts,
                'wit' => $snapshot->wit,
                'total' => $snapshot->total_stats,
            ],
            'total_sp_available' => $snapshot->total_sp_available,
            'stamina_percentage' => $snapshot->stamina_percentage,
            'mood' => $snapshot->mood,
            'conditions' => $snapshot->conditions,
            'skills' => $snapshot->skills_snapshot,
            'notes' => $snapshot->notes,
            'created_at' => $snapshot->created_at?->toIso8601String(),
        ];
    }

    /**
     * Export all snapshots for a plan.
     */
    public function exportAllForPlan(Plan $plan): array
    {
        return $this->getForPlan($plan)
            ->map(fn(CareerSnapshot $snapshot) => $this->exportSnapshot($snapshot))
            ->toArray();
    }

    /**
     * Compare two snapshots side-by-side.
     * Implements FR-12.7: Compare snapshots side-by-side (optional P2 feature).
     */
    public function compareSnapshots(CareerSnapshot $snapshot1, CareerSnapshot $snapshot2): array
    {
        return [
            'snapshot1' => $this->exportSnapshot($snapshot1),
            'snapshot2' => $this->exportSnapshot($snapshot2),
            'differences' => [
                'turn_difference' => $snapshot2->turn_number - $snapshot1->turn_number,
                'stat_changes' => [
                    'speed' => $snapshot2->speed - $snapshot1->speed,
                    'stamina' => $snapshot2->stamina - $snapshot1->stamina,
                    'power' => $snapshot2->power - $snapshot1->power,
                    'guts' => $snapshot2->guts - $snapshot1->guts,
                    'wit' => $snapshot2->wit - $snapshot1->wit,
                    'total' => $snapshot2->total_stats - $snapshot1->total_stats,
                ],
                'sp_change' => ($snapshot2->total_sp_available ?? 0) - ($snapshot1->total_sp_available ?? 0),
                'stamina_change' => ($snapshot2->stamina_percentage ?? 0) - ($snapshot1->stamina_percentage ?? 0),
            ],
        ];
    }

    /**
     * Get acquired skills snapshot at a specific turn.
     */
    private function getAcquiredSkillsSnapshot(Plan $plan, int $turnNumber): array
    {
        return $plan->skills()
            ->where('status', SkillStatus::Acquired)
            ->where(function ($query) use ($turnNumber) {
                $query->whereNull('turn_acquired')
                    ->orWhere('turn_acquired', '<=', $turnNumber);
            })
            ->with('skillReference')
            ->get()
            ->map(fn($skill) => [
                'id' => $skill->skill_reference_id,
                'name' => $skill->skillReference?->skill_name,
                'turn_acquired' => $skill->turn_acquired,
                'sp_cost' => $skill->sp_cost,
            ])
            ->toArray();
    }

    /**
     * Get snapshot count for a plan.
     */
    public function getCountForPlan(Plan $plan): int
    {
        return $plan->careerSnapshots()->count();
    }
}
