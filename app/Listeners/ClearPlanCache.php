<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\PlanCreated;
use App\Events\PlanUpdated;
use App\Services\CacheService;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Clear Plan Cache Listener
 *
 * Handles cache invalidation when plans are created or updated.
 * Implements ShouldQueue for background processing.
 */
class ClearPlanCache implements ShouldQueue
{
    /**
     * The cache service instance.
     */
    private CacheService $cacheService;

    /**
     * Create the event listener.
     *
     * @param  CacheService  $cacheService  The cache service
     */
    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Handle plan created events.
     *
     * @param  PlanCreated  $event  The event instance
     */
    public function handlePlanCreated(PlanCreated $event): void
    {
        // Clear statistics and recent plans cache
        $this->cacheService->clearPlanStatistics();
        $this->cacheService->clearRecentPlans();
    }

    /**
     * Handle plan updated events.
     *
     * @param  PlanUpdated  $event  The event instance
     */
    public function handlePlanUpdated(PlanUpdated $event): void
    {
        // Clear recent plans cache to reflect updates
        $this->cacheService->clearRecentPlans();
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @return array<string, string> Event to method mapping
     */
    public function subscribe(): array
    {
        return [
            PlanCreated::class => 'handlePlanCreated',
            PlanUpdated::class => 'handlePlanUpdated',
        ];
    }
}
