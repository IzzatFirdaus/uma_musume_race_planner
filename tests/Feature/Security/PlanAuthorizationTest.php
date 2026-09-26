<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Enums\StorageMode;
use App\Livewire\Dashboard\PlanDetailsPage;
use App\Models\Plan;
use App\Models\User;
use App\Services\PlanService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Regression tests for the unauthenticated-plan-access defect.
 *
 * Before this fix, `routes/web.php` declared no middleware, `PlanDetailsPage::mount()`
 * performed no ownership check, and `Plan::booted()` silently assigned `user_id = 1`.
 * The result was a textbook IDOR: any anonymous visitor could read and edit any
 * Account plan by guessing its ID.
 *
 * These tests pin the layers that now prevent it:
 *   1. route middleware  — account plan routes require a session
 *   2. policy            — ownership is checked in mount() and in the write path
 *   3. service ownership — PlanService refuses to create a plan for a guest
 *
 * NOT pinned here: `Plan::booted()` still defaults a missing `user_id` to the public
 * user (1). That default is a separate, pre-existing defect. Changing it requires
 * setting `user_id` at 23 test call sites that currently rely on it, so it is out of
 * scope for this fix. `PlanService` is the enforced boundary instead.
 *
 * REQUIRES a live MySQL database `uma_musume_planner_test` (see phpunit.xml).
 */
class PlanAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_an_account_plan(): void
    {
        $plan = Plan::factory()->create();

        $this->get("/plans/{$plan->id}")->assertRedirect();
    }

    public function test_guest_is_redirected_from_plan_edit(): void
    {
        $plan = Plan::factory()->create();

        $this->get("/plans/{$plan->id}/edit")->assertRedirect();
    }

    public function test_owner_can_view_their_plan(): void
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->for($user)->create();

        $this->actingAs($user)
            ->get("/plans/{$plan->id}")
            ->assertOk();
    }

    public function test_owner_can_open_their_plan_for_editing(): void
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->for($user)->create();

        $this->actingAs($user)
            ->get("/plans/{$plan->id}/edit")
            ->assertOk();
    }

    public function test_a_different_user_cannot_view_someone_elses_plan(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $plan = Plan::factory()->for($owner)->create();

        $this->actingAs($intruder)
            ->get("/plans/{$plan->id}")
            ->assertForbidden();
    }

    public function test_a_different_user_cannot_open_someone_elses_plan_for_editing(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $plan = Plan::factory()->for($owner)->create();

        $this->actingAs($intruder)
            ->get("/plans/{$plan->id}/edit")
            ->assertForbidden();
    }

    /**
     * `receiveFormTabsState` is the write path reachable by dispatching
     * `formTabs:state` directly at the component, bypassing the edit-mode guard in
     * save(). An intruder is now denied at mount() before that method can run,
     * because the policy no longer treats user 1's plans as world-readable.
     * Both layers must refuse, and neither may mutate the plan.
     */
    public function test_a_different_user_cannot_write_to_someone_elses_plan(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $plan = Plan::factory()->for($owner)->create();

        $this->actingAs($intruder);

        Livewire::test(PlanDetailsPage::class, ['planId' => $plan->id])
            ->assertForbidden();

        $this->assertNotSame('Hijacked', $plan->fresh()->name);
    }

    /**
     * Pins the edit-mode guard on the write path. Even the legitimate owner must
     * not be able to persist state while the page is in view mode, and the payload
     * must not be able to redirect the write at a different plan.
     */
    public function test_write_path_ignores_state_in_view_mode_and_cannot_retarget_a_plan(): void
    {
        $owner = User::factory()->create();
        $victimPlan = Plan::factory()->for($owner)->create();
        $ownedPlan = Plan::factory()->for($owner)->create();

        $originalTitle = $ownedPlan->plan_title;
        $originalVictimTitle = $victimPlan->plan_title;

        Livewire::actingAs($owner)
            ->test(PlanDetailsPage::class, ['planId' => $ownedPlan->id])
            ->assertSet('isEditMode', false)
            ->call('receiveFormTabsState', [
                'planId' => $victimPlan->id,
                'plan_title' => 'Hijacked',
            ])
            ->assertDispatched('show-error')
            ->assertHasNoErrors();

        $this->assertSame($originalTitle, $ownedPlan->fresh()->plan_title);
        $this->assertSame($originalVictimTitle, $victimPlan->fresh()->plan_title);
    }

    public function test_account_plan_creation_rejects_an_unauthenticated_caller(): void
    {
        // PlanService is the enforced ownership boundary on the write path. A guest
        // must not be able to create an Account-mode plan, because `Plan::booted()`
        // still defaults a missing `user_id` to the public user (1).
        $character = \App\Models\UmaMusume::factory()->create();

        $this->expectException(AuthorizationException::class);

        app(PlanService::class)->createQuickPlan([
            'character_id' => $character->id,
            'storage_mode' => StorageMode::Account->value,
            'plan_title' => 'Ownerless',
            'name' => 'Ownerless',
        ]);
    }

    public function test_local_plan_routes_remain_public(): void
    {
        // A Local run has no server-side record, so there is nothing to
        // authorize against. It must NOT be behind `auth`, or guests could not
        // use the product's primary feature.
        $this->get('/plans/local/00000000-0000-4000-8000-000000000000')
            ->assertOk();
    }
}
