<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Duplicate Detection Service
 *
 * Detects duplicate plans during import and conversion operations.
 * Implements FR-9C.4, FR-6B.7: Duplicate detection for convert/import.
 */
class DuplicateDetectionService
{
    /**
     * Similarity threshold for fuzzy matching (0-1).
     */
    private const SIMILARITY_THRESHOLD = 0.85;

    /**
     * Find a duplicate plan for the given local run data.
     * Implements FR-9C.4: Duplicate detection during conversion.
     */
    public function findDuplicate(array $localRunData, User $user): ?Plan
    {
        $careerRun = $localRunData['career_run'] ?? [];
        $title = $careerRun['plan_title'] ?? null;
        $name = $careerRun['name'] ?? null;
        $createdAt = $localRunData['created_at'] ?? null;

        // Build query for potential duplicates
        $query = Plan::where('user_id', $user->id);

        // Exact title match
        if (!empty($title)) {
            $exactTitleMatch = (clone $query)->where('plan_title', $title)->first();
            if ($exactTitleMatch !== null) {
                return $exactTitleMatch;
            }
        }

        // Exact name + date match
        if (!empty($name) && !empty($createdAt)) {
            $exactMatch = (clone $query)
                ->where('name', $name)
                ->whereDate('created_at', $this->parseDate($createdAt))
                ->first();

            if ($exactMatch !== null) {
                return $exactMatch;
            }
        }

        // Fuzzy title match
        if (!empty($title)) {
            $potentialMatches = $query->get();
            foreach ($potentialMatches as $plan) {
                if ($this->isSimilar($title, $plan->plan_title)) {
                    return $plan;
                }
            }
        }

        return null;
    }

    /**
     * Find duplicates for multiple plans.
     *
     * @return array<int, array{plan_index: int, existing: Plan, reason: string}>
     */
    public function findDuplicates(Collection $plans, User $user): array
    {
        $duplicates = [];

        foreach ($plans as $index => $planData) {
            $existing = $this->findDuplicateForPlanData($planData, $user);
            if ($existing !== null) {
                $duplicates[] = [
                    'plan_index' => $index,
                    'existing' => $existing,
                    'reason' => $this->getDuplicateReasonForPlanData($planData, $existing),
                ];
            }
        }

        return $duplicates;
    }

    /**
     * Find duplicate for parsed plan data (from import).
     */
    public function findDuplicateForPlanData(array $planData, User $user): ?Plan
    {
        $title = $planData['plan_title'] ?? null;
        $name = $planData['name'] ?? null;
        $createdAt = $planData['created_at'] ?? null;

        $query = Plan::where('user_id', $user->id);

        // Exact title match
        if (!empty($title)) {
            $exactTitleMatch = (clone $query)->where('plan_title', $title)->first();
            if ($exactTitleMatch !== null) {
                return $exactTitleMatch;
            }
        }

        // Exact name + date match
        if (!empty($name) && !empty($createdAt)) {
            $exactMatch = (clone $query)
                ->where('name', $name)
                ->whereDate('created_at', $this->parseDate($createdAt))
                ->first();

            if ($exactMatch !== null) {
                return $exactMatch;
            }
        }

        return null;
    }

    /**
     * Get the reason why a local run is considered a duplicate.
     */
    public function getDuplicateReason(array $localRunData, Plan $existing): string
    {
        $careerRun = $localRunData['career_run'] ?? [];
        $reasons = [];

        $title = $careerRun['plan_title'] ?? null;
        $name = $careerRun['name'] ?? null;

        if (!empty($title) && $title === $existing->plan_title) {
            $reasons[] = 'same title';
        } elseif (!empty($title) && $this->isSimilar($title, $existing->plan_title)) {
            $reasons[] = 'similar title';
        }

        if (!empty($name) && $name === $existing->name) {
            $reasons[] = 'same character';
        }

        if (empty($reasons)) {
            $reasons[] = 'matching criteria';
        }

        return 'Matches existing plan (ID: ' . $existing->id . '): ' . \implode(', ', $reasons);
    }

    /**
     * Get duplicate reason for plan data (from import).
     */
    public function getDuplicateReasonForPlanData(array $planData, Plan $existing): string
    {
        $reasons = [];

        $title = $planData['plan_title'] ?? null;
        $name = $planData['name'] ?? null;

        if (!empty($title) && $title === $existing->plan_title) {
            $reasons[] = 'same title';
        }

        if (!empty($name) && $name === $existing->name) {
            $reasons[] = 'same character';
        }

        if (empty($reasons)) {
            $reasons[] = 'matching criteria';
        }

        return 'Matches existing plan (ID: ' . $existing->id . '): ' . \implode(', ', $reasons);
    }

    /**
     * Check if two strings are similar using Levenshtein distance.
     */
    private function isSimilar(?string $str1, ?string $str2): bool
    {
        if ($str1 === null || $str2 === null) {
            return false;
        }

        $str1 = \strtolower(\trim($str1));
        $str2 = \strtolower(\trim($str2));

        if ($str1 === $str2) {
            return true;
        }

        $maxLen = \max(\strlen($str1), \strlen($str2));
        if ($maxLen === 0) {
            return true;
        }

        $distance = \levenshtein($str1, $str2);
        $similarity = 1 - ($distance / $maxLen);

        return $similarity >= self::SIMILARITY_THRESHOLD;
    }

    /**
     * Parse date from various formats.
     */
    private function parseDate(?string $dateString): ?string
    {
        if ($dateString === null) {
            return null;
        }

        try {
            $date = new \DateTime($dateString);
            return $date->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Check if a plan with the given local UUID already exists.
     */
    public function existsByLocalUuid(string $localUuid, User $user): bool
    {
        return Plan::where('user_id', $user->id)
            ->where('local_uuid', $localUuid)
            ->exists();
    }

    /**
     * Find plan by local UUID.
     */
    public function findByLocalUuid(string $localUuid, User $user): ?Plan
    {
        return Plan::where('user_id', $user->id)
            ->where('local_uuid', $localUuid)
            ->first();
    }

    /**
     * Get duplicate resolution options.
     * Implements FR-9C.5: Duplicate resolution options.
     *
     * @return array<string, string>
     */
    public function getResolutionOptions(): array
    {
        return [
            'create_duplicate' => 'Create as new plan (duplicate)',
            'cancel' => 'Cancel conversion',
            // 'merge' => 'Merge with existing (P2)', // Future feature
        ];
    }
}
