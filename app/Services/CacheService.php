<?php

namespace App\Services;

use App\Models\Condition;
use App\Models\Mood;
use App\Models\Plan;
use App\Models\Strategy;
use Illuminate\Support\Facades\Cache;

/**
 * Cache Service Class
 *
 * Handles caching strategies for frequently accessed data
 * to improve application performance and reduce database queries.
 */
class CacheService
{
    /**
     * Cache duration in seconds (1 hour).
     */
    private const CACHE_DURATION = 3600;

    /**
     * Get cached lookup data (moods, conditions, strategies).
     *
     * @return array The cached lookup data
     */
    public function getLookupData(): array
    {
        return Cache::remember('lookup_data', self::CACHE_DURATION, function () {
            return [
                'moods' => Mood::all()->keyBy('id'),
                'conditions' => Condition::all()->keyBy('id'),
                'strategies' => Strategy::all()->keyBy('id'),
            ];
        });
    }

    /**
     * Get cached plan statistics.
     *
     * @return array The cached statistics
     */
    public function getPlanStatistics(): array
    {
        return Cache::remember('plan_statistics', self::CACHE_DURATION, function () {
            return [
                'total_plans' => Plan::count(),
                'plans_by_stage' => Plan::selectRaw('career_stage, COUNT(*) as count')
                    ->groupBy('career_stage')
                    ->pluck('count', 'career_stage')
                    ->toArray(),
                'plans_by_class' => Plan::selectRaw('class, COUNT(*) as count')
                    ->whereNotNull('class')
                    ->groupBy('class')
                    ->pluck('count', 'class')
                    ->toArray(),
                'recent_plans_count' => Plan::where('created_at', '>=', now()->subWeek())->count(),
            ];
        });
    }

    /**
     * Get cached recent plans list.
     *
     * @param  int  $limit  Number of plans to retrieve
     * @return \Illuminate\Database\Eloquent\Collection The cached recent plans
     */
    public function getRecentPlans(int $limit = 10)
    {
        $cacheKey = "recent_plans_{$limit}";

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($limit) {
            return Plan::with(['mood', 'condition', 'strategy'])
                ->latest()
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Clear all cached data.
     */
    public function clearAll(): void
    {
        $keys = [
            'lookup_data',
            'plan_statistics',
            'recent_plans_10',
            'recent_plans_20',
            'recent_plans_50',
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Clear lookup data cache.
     */
    public function clearLookupData(): void
    {
        Cache::forget('lookup_data');
    }

    /**
     * Clear plan statistics cache.
     */
    public function clearPlanStatistics(): void
    {
        Cache::forget('plan_statistics');
    }

    /**
     * Clear recent plans cache.
     */
    public function clearRecentPlans(): void
    {
        $keys = ['recent_plans_10', 'recent_plans_20', 'recent_plans_50'];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Warm up the cache with frequently accessed data.
     */
    public function warmUp(): void
    {
        // Preload lookup data
        $this->getLookupData();

        // Preload statistics
        $this->getPlanStatistics();

        // Preload recent plans
        $this->getRecentPlans(10);
        $this->getRecentPlans(20);
    }

    /**
     * Get cache statistics.
     *
     * @return array Cache usage statistics
     */
    public function getStatistics(): array
    {
        $keys = [
            'lookup_data',
            'plan_statistics',
            'recent_plans_10',
            'recent_plans_20',
        ];

        $stats = [
            'total_keys' => 0,
            'cached_keys' => 0,
            'cache_hit_rate' => 0,
        ];

        foreach ($keys as $key) {
            $stats['total_keys']++;
            if (Cache::has($key)) {
                $stats['cached_keys']++;
            }
        }

        $stats['cache_hit_rate'] = $stats['total_keys'] > 0
            ? round(($stats['cached_keys'] / $stats['total_keys']) * 100, 2)
            : 0;

        return $stats;
    }
}
