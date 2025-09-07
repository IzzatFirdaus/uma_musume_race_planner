<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Plan;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Plan Created Event
 *
 * Fired when a new plan is created in the system.
 * Used for cache invalidation and other post-creation tasks.
 */
class PlanCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The created plan instance.
     */
    public Plan $plan;

    /**
     * Create a new event instance.
     *
     * @param  Plan  $plan  The created plan
     */
    public function __construct(Plan $plan)
    {
        $this->plan = $plan;
    }
}
