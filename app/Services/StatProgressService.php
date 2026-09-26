<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\StorageMode;
use App\Models\Plan;
use App\Models\Turn;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

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
     * Maximum stat value cap.
     */
    private const MAX_STAT_VALUE = 1200;

    /**
     * Minimum stat value.
     */
    private const MIN_STAT_VALUE = 0;

    public function __construct(
        private readonly LocalRunStorageService $localRunStorageService,
    ) {}

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
     * Log a new turn's stats with dual-storage branching.
     *
     * @param  Plan  $plan  The plan to log the turn for
     * @param  array{
     *     turn_number?: int,
     *     speed: int,
     *     stamina: int,
     *     power: int,
     *     guts: int,
     *     wit: int,
     *     stamina_percentage?: int
     * }  $stats  The stat data for the turn
     * @return array{
     *     turn_number: int,
     *     speed: int,
     *     stamina: int,
     *     power: int,
     *     guts: int,
     *     wit: int,
     *     stamina_percentage: int,
     *     storage_mode: string
     * }|Turn The logged turn data (array for local, Turn model for account)
     *
     * @throws InvalidArgumentException
     * @throws Throwable
     */
    public function logTurn(Plan $plan, array $stats): Turn|array
    {
        $this->validateStats($stats);

        $turnNumber = $stats['turn_number'] ?? $this->getNextTurnNumber($plan);
        $staminaPercentage = $stats['stamina_percentage'] ?? 100;

        $turnData = [
            'turn_number' => $turnNumber,
            'speed' => $stats['speed'] ?? 0,
            'stamina' => $stats['stamina'] ?? 0,
            'power' => $stats['power'] ?? 0,
            'guts' => $stats['guts'] ?? 0,
            'wit' => $stats['wit'] ?? 0,
            'stamina_percentage' => $staminaPercentage,
        ];

        if ($plan->storage_mode === StorageMode::Local) {
            return $this->logTurnLocal($plan, $turnData);
        }

        return $this->logTurnAccount($plan, $turnData);
    }

    /**
     * Update an existing turn's stats.
     */
    public function updateTurn(Turn $turn, array $stats): Turn
    {
        $this->validateStats($stats);

        $turn->update([
            'speed' => $stats['speed'] ?? $turn->speed,
            'stamina' => $stats['stamina'] ?? $turn->stamina,
            'power' => $stats['power'] ?? $turn->power,
            'guts' => $stats['guts'] ?? $turn->guts,
            'wit' => $stats['wit'] ?? $turn->wit,
            'stamina_percentage' => $stats['stamina_percentage'] ?? $turn->stamina_percentage,
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

        if (! $latestTurn) {
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

        if (! $averages) {
            return array_fill_keys(self::STAT_ATTRIBUTES, 0);
        }

        // Cast to float to handle SQLite returning strings for AVG()
        return [
            'speed' => round((float) ($averages->speed ?? 0), 1),
            'stamina' => round((float) ($averages->stamina ?? 0), 1),
            'power' => round((float) ($averages->power ?? 0), 1),
            'guts' => round((float) ($averages->guts ?? 0), 1),
            'wit' => round((float) ($averages->wit ?? 0), 1),
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

    /**
     * Recalculate stat totals and update the parent Plan record.
     * This aggregates the latest turn's stats and updates the plan's current state.
     *
     * @param  Plan  $plan  The plan to recalculate totals for
     * @return array{
     *     speed: int,
     *     stamina: int,
     *     power: int,
     *     guts: int,
     *     wit: int,
     *     total: int,
     *     stamina_percentage: int
     * } The recalculated totals
     *
     * @throws Throwable
     */
    public function recalculateTotals(Plan $plan): array
    {
        $totals = $this->getStatTotals($plan);
        $latestTurn = $plan->turns()
            ->orderBy('turn_number', 'desc')
            ->first();

        $staminaPercentage = $latestTurn?->stamina_percentage ?? 100;

        DB::transaction(function () use ($plan, $totals, $staminaPercentage): void {
            $plan->update([
                'total_available_skill_points' => $totals['total'],
                'stamina_percentage' => $staminaPercentage,
            ]);
        });

        return [
            'speed' => $totals['speed'],
            'stamina' => $totals['stamina'],
            'power' => $totals['power'],
            'guts' => $totals['guts'],
            'wit' => $totals['wit'],
            'total' => $totals['total'],
            'stamina_percentage' => $staminaPercentage,
        ];
    }

    /**
     * Validate stat data to ensure values are within allowed range.
     *
     * @param  array  $stats  The stats to validate
     *
     * @throws InvalidArgumentException
     */
    private function validateStats(array $stats): void
    {
        foreach (self::STAT_ATTRIBUTES as $stat) {
            if (isset($stats[$stat])) {
                $value = (int) $stats[$stat];
                if ($value < self::MIN_STAT_VALUE || $value > self::MAX_STAT_VALUE) {
                    throw new InvalidArgumentException(
                        "Stat '{$stat}' must be between {self::MIN_STAT_VALUE} and {self::MAX_STAT_VALUE}, got {$value}."
                    );
                }
            }
        }

        if (isset($stats['stamina_percentage'])) {
            $value = (int) $stats['stamina_percentage'];
            if ($value < 0 || $value > 100) {
                throw new InvalidArgumentException(
                    "Stamina percentage must be between 0 and 100, got {$value}."
                );
            }
        }
    }

    /**
     * Log a turn for local storage mode (delegates to LocalRunStorageService).
     * Note: This returns the turn data structure for client-side persistence.
     *
     * @param  Plan  $plan  The plan to log the turn for
     * @param  array  $turnData  The turn data
     * @return array The turn data structure for local storage
     */
    private function logTurnLocal(Plan $plan, array $turnData): array
    {
        // For local storage, we return the data structure that the client
        // will append to the stat_progress array in the local JSON payload.
        // The actual persistence is handled client-side via IndexedDB/localStorage.
        return [
            'turn_number' => $turnData['turn_number'],
            'speed' => $turnData['speed'],
            'stamina' => $turnData['stamina'],
            'power' => $turnData['power'],
            'guts' => $turnData['guts'],
            'wit' => $turnData['wit'],
            'stamina_percentage' => $turnData['stamina_percentage'],
            'storage_mode' => StorageMode::Local->value,
        ];
    }

    /**
     * Log a turn for account storage mode (persists to database).
     *
     * @param  Plan  $plan  The plan to log the turn for
     * @param  array  $turnData  The turn data
     * @return Turn The created Turn model
     *
     * @throws Throwable
     */
    private function logTurnAccount(Plan $plan, array $turnData): Turn
    {
        return DB::transaction(function () use ($plan, $turnData): Turn {
            $turn = $plan->turns()->create($turnData);

            // Recalculate totals after logging a new turn
            $this->recalculateTotals($plan);

            return $turn;
        });
    }
}
