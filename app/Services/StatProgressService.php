<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Plan;
use App\Models\Turn;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Stat Progress Service Class
 *
 * Handles turn-by-turn stat tracking and progression analysis.
 * Implements FR-3.1: Log turn-by-turn stats (Speed, Stamina, Power, Guts, Wit).
 */
class StatProgressService
{
    /**
     * Stat attribute names.
     */
    private const STAT_ATTRIBUTES = ['speed', 'stamina', 'power', 'guts', 'wit'];

    /**
     * Get all stat progress entries for a plan.
     */
    public function getForPlan(Plan $plan): Collection
    {
        return $plan->turns()
            ->orderBy('turn_number')
            ->get();
    }

    /**
     * Get stat progress with pagination for large datasets.
     * Implements NFR-1.7: Virtualization consideration for 70-78 turn tables.
     */
    public function getForPlanPaginated(Plan $plan, int $perPage = 20): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $plan->turns()
            ->orderBy('turn_number')
            ->paginate($perPage);
    }

    /**
     * Log a new turn's stats.
     */
    public function logTurn(Plan $plan, array $stats): Turn
    {
        $turnNumber = $stats['turn_number'] ?? $this->getNextTurnNumber($plan);

        return $plan->turns()->create([
            'turn_number' => $turnNumber,
            'speed' => $stats['speed'] ?? 0,
            'stamina' => $stats['stamina'] ?? 0,
            'power' => $stats['power'] ?? 0,
            'guts' => $stats['guts'] ?? 0,
            'wit' => $stats['wit'] ?? 0,
        ]);
    }

    /**
     * Update an existing turn's stats.
     */
    public function updateTurn(Turn $turn, array $stats): Turn
    {
        $turn->update([
            'speed' => $stats['speed'] ?? $turn->speed,
            'stamina' => $stats['stamina'] ?? $turn->stamina,
            'power' => $stats['power'] ?? $turn->power,
            'guts' => $stats['guts'] ?? $turn->guts,
            'wit' => $stats['wit'] ?? $turn->wit,
        ]);

        return $turn->fresh();
    }

    /**
     * Delete a turn entry.
     */
    public function deleteTurn(Turn $turn): bool
    {
        return $turn->delete();
    }

    /**
     * Bulk create turns for a plan.
     */
    public function bulkCreate(Plan $plan, array $turnsData): Collection
    {
        $created = [];

        DB::transaction(function () use ($plan, $turnsData, &$created) {
            foreach ($turnsData as $turnData) {
                $created[] = $this->logTurn($plan, $turnData);
            }
        });

        return new Collection($created);
    }

    /**
     * Get the next turn number for a plan.
     */
    public function getNextTurnNumber(Plan $plan): int
    {
        $maxTurn = $plan->turns()->max('turn_number');

        return ($maxTurn ?? 0) + 1;
    }

    /**
     * Get stat totals for a plan.
     * Implements FR-3.4: Calculate and display stat totals and averages.
     */
    public function getStatTotals(Plan $plan): array
    {
        $latestTurn = $plan->turns()
            ->orderBy('turn_number', 'desc')
            ->first();

        if (!$latestTurn) {
            return [
                'speed' => 0,
                'stamina' => 0,
                'power' => 0,
                'guts' => 0,
                'wit' => 0,
                'total' => 0,
            ];
        }

        return [
            'speed' => $latestTurn->speed,
            'stamina' => $latestTurn->stamina,
            'power' => $latestTurn->power,
            'guts' => $latestTurn->guts,
            'wit' => $latestTurn->wit,
            'total' => $latestTurn->speed + $latestTurn->stamina + $latestTurn->power + $latestTurn->guts + $latestTurn->wit,
        ];
    }

    /**
     * Get stat averages across all turns.
     */
    public function getStatAverages(Plan $plan): array
    {
        $averages = $plan->turns()
            ->selectRaw('AVG(speed) as speed, AVG(stamina) as stamina, AVG(power) as power, AVG(guts) as guts, AVG(wit) as wit')
            ->first();

        if (!$averages) {
            return array_fill_keys(self::STAT_ATTRIBUTES, 0);
        }

        return [
            'speed' => round($averages->speed ?? 0, 1),
            'stamina' => round($averages->stamina ?? 0, 1),
            'power' => round($averages->power ?? 0, 1),
            'guts' => round($averages->guts ?? 0, 1),
            'wit' => round($averages->wit ?? 0, 1),
        ];
    }

    /**
     * Get stat growth per turn (delta between consecutive turns).
     */
    public function getStatGrowth(Plan $plan): array
    {
        $turns = $plan->turns()
            ->orderBy('turn_number')
            ->get();

        if ($turns->count() < 2) {
            return [];
        }

        $growth = [];
        $previousTurn = null;

        foreach ($turns as $turn) {
            if ($previousTurn) {
                $growth[] = [
                    'turn_number' => $turn->turn_number,
                    'speed' => $turn->speed - $previousTurn->speed,
                    'stamina' => $turn->stamina - $previousTurn->stamina,
                    'power' => $turn->power - $previousTurn->power,
                    'guts' => $turn->guts - $previousTurn->guts,
                    'wit' => $turn->wit - $previousTurn->wit,
                ];
            }
            $previousTurn = $turn;
        }

        return $growth;
    }

    /**
     * Get chart data for stat progression visualization.
     * Implements FR-3.2: Visualize stat progression with charts.
     */
    public function getChartData(Plan $plan): array
    {
        $turns = $plan->turns()
            ->orderBy('turn_number')
            ->get();

        $labels = $turns->pluck('turn_number')->toArray();

        $datasets = [];
        foreach (self::STAT_ATTRIBUTES as $stat) {
            $datasets[$stat] = $turns->pluck($stat)->toArray();
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }

    /**
     * Get stat at a specific turn.
     */
    public function getStatAtTurn(Plan $plan, int $turnNumber): ?Turn
    {
        return $plan->turns()
            ->where('turn_number', $turnNumber)
            ->first();
    }

    /**
     * Check if a turn number already exists for a plan.
     */
    public function turnExists(Plan $plan, int $turnNumber): bool
    {
        return $plan->turns()
            ->where('turn_number', $turnNumber)
            ->exists();
    }

    /**
     * Get stat summary for a plan (min, max, current).
     */
    public function getStatSummary(Plan $plan): array
    {
        $summary = [];

        foreach (self::STAT_ATTRIBUTES as $stat) {
            $summary[$stat] = [
                'min' => $plan->turns()->min($stat) ?? 0,
                'max' => $plan->turns()->max($stat) ?? 0,
                'current' => $plan->turns()->orderBy('turn_number', 'desc')->value($stat) ?? 0,
            ];
        }

        return $summary;
    }
}
