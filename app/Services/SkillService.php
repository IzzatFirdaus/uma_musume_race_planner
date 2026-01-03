<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SkillStatus;
use App\Models\Plan;
use App\Models\Skill;
use App\Models\SkillReference;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Skill Service Class
 *
 * Handles skill management with search caching and 3-state status management.
 * Implements FR-4.1, FR-4.2, FR-4B.7: Skill management and search functionality.
 */
class SkillService
{
    private const SEARCH_CACHE_TTL = 300; // 5 minutes
    private const SEARCH_CACHE_PREFIX = 'skill_search_';

    /**
     * Search skills by name (EN or JP).
     * Implements FR-4.4, FR-4B.1, FR-4B.3: Skill autocomplete with EN+JP matching.
     */
    public function search(string $query, int $limit = 10): Collection
    {
        $cacheKey = self::SEARCH_CACHE_PREFIX . md5(strtolower($query) . $limit);

        return Cache::remember($cacheKey, self::SEARCH_CACHE_TTL, function () use ($query, $limit) {
            return SkillReference::where('skill_name', 'LIKE', "%{$query}%")
                ->orWhere('description', 'LIKE', "%{$query}%")
                ->orderBy('skill_name')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get all skill references.
     */
    public function getAllReferences(): Collection
    {
        return Cache::remember('skill_references_all', 3600, function () {
            return SkillReference::orderBy('skill_name')->get();
        });
    }

    /**
     * Get skills for a plan.
     */
    public function getForPlan(Plan $plan): Collection
    {
        return $plan->skills()
            ->with('skillReference')
            ->orderBy('id')
            ->get();
    }

    /**
     * Get skills for a plan filtered by status.
     * Implements FR-4.7: Filter skills by status.
     */
    public function getForPlanByStatus(Plan $plan, SkillStatus $status): Collection
    {
        return $plan->skills()
            ->with('skillReference')
            ->where('status', $status)
            ->orderBy('id')
            ->get();
    }

    /**
     * Add a skill to a plan.
     * Implements FR-4.2, FR-4.3: Track skill acquisition with 3-state status.
     */
    public function addToPlan(Plan $plan, array $data): Skill
    {
        $skillReferenceId = $data['skill_reference_id'] ?? null;

        // If skill_name is provided instead of skill_reference_id, find or create the reference
        if (!$skillReferenceId && isset($data['skill_name'])) {
            $skillReference = SkillReference::firstOrCreate(
                ['skill_name' => $data['skill_name']],
                [
                    'description' => $data['description'] ?? 'User-added skill.',
                    'tag' => $data['tag'] ?? '📝',
                ]
            );
            $skillReferenceId = $skillReference->id;
        }

        $status = $data['status'] ?? SkillStatus::Suggested;
        if (is_string($status)) {
            $status = SkillStatus::from($status);
        }

        // Validate turn_acquired is required for acquired status
        $turnAcquired = $data['turn_acquired'] ?? null;
        if ($status === SkillStatus::Acquired && $turnAcquired === null) {
            throw new \InvalidArgumentException('turn_acquired is required when status is Acquired');
        }

        return $plan->skills()->create([
            'skill_reference_id' => $skillReferenceId,
            'status' => $status,
            'turn_acquired' => $turnAcquired,
            'sp_cost' => $data['sp_cost'] ?? null,
            'tag' => $data['tag'] ?? null,
            'notes' => $data['notes'] ?? null,
            'acquired' => $status === SkillStatus::Acquired ? 'yes' : 'no',
        ]);
    }

    /**
     * Update a skill's status.
     * Implements FR-4.5: Track turn when skill was acquired.
     */
    public function updateStatus(Skill $skill, SkillStatus $status, ?int $turnAcquired = null): Skill
    {
        // Validate turn_acquired is required for acquired status
        if ($status === SkillStatus::Acquired && $turnAcquired === null) {
            throw new \InvalidArgumentException('turn_acquired is required when status is Acquired');
        }

        $skill->update([
            'status' => $status,
            'turn_acquired' => $status === SkillStatus::Acquired ? $turnAcquired : null,
            'acquired' => $status === SkillStatus::Acquired ? 'yes' : 'no',
        ]);

        return $skill->fresh();
    }

    /**
     * Update a skill entry.
     */
    public function update(Skill $skill, array $data): Skill
    {
        $updateData = [];

        if (isset($data['status'])) {
            $status = is_string($data['status']) ? SkillStatus::from($data['status']) : $data['status'];
            $updateData['status'] = $status;
            $updateData['acquired'] = $status === SkillStatus::Acquired ? 'yes' : 'no';

            // Validate turn_acquired for acquired status
            if ($status === SkillStatus::Acquired) {
                if (!isset($data['turn_acquired']) && $skill->turn_acquired === null) {
                    throw new \InvalidArgumentException('turn_acquired is required when status is Acquired');
                }
                $updateData['turn_acquired'] = $data['turn_acquired'] ?? $skill->turn_acquired;
            } else {
                $updateData['turn_acquired'] = null;
            }
        }

        if (isset($data['notes'])) {
            $updateData['notes'] = $data['notes'];
        }

        if (isset($data['sp_cost'])) {
            $updateData['sp_cost'] = $data['sp_cost'];
        }

        if (isset($data['tag'])) {
            $updateData['tag'] = $data['tag'];
        }

        $skill->update($updateData);

        return $skill->fresh();
    }

    /**
     * Remove a skill from a plan.
     */
    public function removeFromPlan(Skill $skill): bool
    {
        return (bool) $skill->delete();
    }

    /**
     * Calculate SP totals for a plan.
     * Implements FR-4.6: Calculate SP totals (acquired vs suggested).
     */
    public function calculateSpTotals(Plan $plan): array
    {
        $skills = $plan->skills()
            ->with('skillReference')
            ->get();

        $acquiredTotal = 0;
        $suggestedTotal = 0;
        $skippedTotal = 0;

        foreach ($skills as $skill) {
            $spCost = (int) ($skill->sp_cost ?? 0);

            switch ($skill->status) {
                case SkillStatus::Acquired:
                    $acquiredTotal += $spCost;
                    break;
                case SkillStatus::Suggested:
                    $suggestedTotal += $spCost;
                    break;
                case SkillStatus::Skipped:
                    $skippedTotal += $spCost;
                    break;
            }
        }

        return [
            'acquired' => $acquiredTotal,
            'suggested' => $suggestedTotal,
            'skipped' => $skippedTotal,
            'total_planned' => $acquiredTotal + $suggestedTotal,
            'total_all' => $acquiredTotal + $suggestedTotal + $skippedTotal,
        ];
    }

    /**
     * Get skill counts by status for a plan.
     */
    public function getStatusCounts(Plan $plan): array
    {
        return [
            'acquired' => $plan->skills()->where('status', SkillStatus::Acquired)->count(),
            'suggested' => $plan->skills()->where('status', SkillStatus::Suggested)->count(),
            'skipped' => $plan->skills()->where('status', SkillStatus::Skipped)->count(),
            'total' => $plan->skills()->count(),
        ];
    }

    /**
     * Bulk update skill statuses.
     */
    public function bulkUpdateStatus(array $skillIds, SkillStatus $status, ?int $turnAcquired = null): int
    {
        if ($status === SkillStatus::Acquired && $turnAcquired === null) {
            throw new \InvalidArgumentException('turn_acquired is required when status is Acquired');
        }

        /** @var int $updated */
        $updated = Skill::whereIn('id', $skillIds)->update([
            'status' => $status,
            'turn_acquired' => $status === SkillStatus::Acquired ? $turnAcquired : null,
            'acquired' => $status === SkillStatus::Acquired ? 'yes' : 'no',
        ]);

        return $updated;
    }

    /**
     * Sync skills for a plan (replace all).
     */
    public function syncForPlan(Plan $plan, array $skillsData): Collection
    {
        return DB::transaction(function () use ($plan, $skillsData) {
            // Soft delete existing skills
            $plan->skills()->delete();

            // Create new skills
            $created = [];
            foreach ($skillsData as $skillData) {
                $created[] = $this->addToPlan($plan, $skillData);
            }

            return new Collection($created);
        });
    }

    /**
     * Clear search cache.
     */
    public function clearSearchCache(): void
    {
        // Note: In production, you might want to use cache tags for more efficient clearing
        Cache::forget('skill_references_all');
    }

    /**
     * Create or find a skill reference.
     */
    public function findOrCreateReference(string $skillName, array $attributes = []): SkillReference
    {
        return SkillReference::firstOrCreate(
            ['skill_name' => $skillName],
            array_merge([
                'description' => 'User-added skill.',
                'tag' => '📝',
            ], $attributes)
        );
    }
}
