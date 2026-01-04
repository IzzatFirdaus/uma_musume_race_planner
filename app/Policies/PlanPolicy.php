<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Plan;
use App\Models\User;

/**
 * PlanPolicy - Enforces user data isolation for plans
 *
 * Property 1: User Data Isolation
 * For any authenticated user and any plan in the system, the user SHALL only
 * be able to access, modify, or delete plans where plan.user_id equals their own user ID.
 *
 * Validates: FR-BE-2.1, FR-BE-2.9, FR-BE-9.3, FR-BE-10.4
 */
class PlanPolicy
{
    /**
     * Determine whether the user can view any plans.
     * Users can only view their own plans list.
     */
    public function viewAny(User $user): bool
    {
        return true; // Filtering happens in controller/service
    }

    /**
     * Determine whether the user can view the plan.
     * Users can only view plans they own.
     * Public plans (user_id = 1) can be viewed by anyone.
     */
    public function view(?User $user, Plan $plan): bool
    {
        // Public plans (user_id = 1) can be viewed by anyone
        if ($plan->user_id === 1) {
            return true;
        }

        // Otherwise, user must be authenticated and own the plan
        return $user !== null && $user->id === $plan->user_id;
    }

    /**
     * Determine whether the user can create plans.
     * Any authenticated user can create plans.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the plan.
     * Users can only update plans they own.
     */
    public function update(User $user, Plan $plan): bool
    {
        return $user->id === $plan->user_id;
    }

    /**
     * Determine whether the user can delete the plan.
     * Users can only delete plans they own.
     */
    public function delete(User $user, Plan $plan): bool
    {
        return $user->id === $plan->user_id;
    }

    /**
     * Determine whether the user can restore the plan.
     * Users can only restore plans they own.
     */
    public function restore(User $user, Plan $plan): bool
    {
        return $user->id === $plan->user_id;
    }

    /**
     * Determine whether the user can permanently delete the plan.
     * Users can only force delete plans they own.
     */
    public function forceDelete(User $user, Plan $plan): bool
    {
        return $user->id === $plan->user_id;
    }
}
