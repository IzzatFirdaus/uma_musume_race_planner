<?php

namespace App\Events;

use App\Models\Plan;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Plan Updated Event
 *
 * Fired when an existing plan is updated in the system.
 * Used for cache invalidation and other post-update tasks.
 */
class PlanUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The updated plan instance.
     */
    public Plan $plan;

    /**
     * Create a new event instance.
     *
     * @param  Plan  $plan  The updated plan
     */
    public function __construct(Plan $plan)
    {
        $this->plan = $plan;
    }
}
