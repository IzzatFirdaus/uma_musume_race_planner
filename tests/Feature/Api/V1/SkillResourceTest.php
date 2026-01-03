<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Enums\SkillStatus;
use App\Models\Condition;
use App\Models\Mood;
use App\Models\Plan;
use App\Models\Skill;
use App\Models\SkillReference;
use App\Models\Strategy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for Skill API endpoints.
 * Task 6.2.1: Test API functionality.
 */
class SkillResourceTest extends TestCase
{
    use RefreshDatabase;

    protected Plan $plan;
    protected SkillReference $skillRef;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => \Database\Seeders\LookupSeeder::class]);

        $this->plan = Plan::create([
            'name' => 'Test Horse',
            'plan_title' => 'Test Plan',
            'career_stage' => 'senior',
            'class' => 'gold',
            'race_name' => 'Test Race',
            'status' => 'Planning',
            'mood_id' => Mood::first()?->id,
            'condition_id' => Condition::first()?->id,
            'strategy_id' => Strategy::first()?->id,
        ]);

        $this->skillRef = SkillReference::create([
            'skill_name' => 'Speed Star',
            'description' => 'Increases speed',
            'tag' => '⚡',
        ]);
    }

    public function test_index_returns_skills_for_plan(): void
    {
        Skill::create([
            'plan_id' => $this->plan->id,
            'skill_reference_id' => $this->skillRef->id,
            'status' => SkillStatus::Suggested,
            'acquired' => 'no',
            'sp_cost' => 100,
        ]);

        $response = $this->getJson("/api/v1/plans/{$this->plan->id}/skills");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'skill_name',
                        'status',
                        'turn_acquired',
                        'sp_cost',
                    ],
                ],
            ]);
    }

    public function test_store_creates_skill_with_suggested_status(): void
    {
        $data = [
            'skill_reference_id' => $this->skillRef->id,
            'status' => 'suggested',
            'sp_cost' => 150,
        ];

        $response = $this->postJson("/api/v1/plans/{$this->plan->id}/skills", $data);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'status' => 'suggested',
                    'sp_cost' => 150,
                ],
            ]);
    }

    public function test_store_creates_skill_with_acquired_status_and_turn(): void
    {
        $data = [
            'skill_reference_id' => $this->skillRef->id,
            'status' => 'acquired',
            'turn_acquired' => 15,
            'sp_cost' => 100,
        ];

        $response = $this->postJson("/api/v1/plans/{$this->plan->id}/skills", $data);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'status' => 'acquired',
                    'turn_acquired' => 15,
                ],
            ]);
    }

    public function test_store_validates_turn_required_for_acquired(): void
    {
        $data = [
            'skill_reference_id' => $this->skillRef->id,
            'status' => 'acquired',
            'sp_cost' => 100,
        ];

        $response = $this->postJson("/api/v1/plans/{$this->plan->id}/skills", $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['turn_acquired']);
    }

    public function test_show_returns_single_skill(): void
    {
        $skill = Skill::create([
            'plan_id' => $this->plan->id,
            'skill_reference_id' => $this->skillRef->id,
            'status' => SkillStatus::Acquired,
            'turn_acquired' => 20,
            'acquired' => 'yes',
            'sp_cost' => 100,
        ]);

        $response = $this->getJson("/api/v1/plans/{$this->plan->id}/skills/{$skill->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => 'acquired',
                    'turn_acquired' => 20,
                ],
            ]);
    }

    public function test_update_modifies_skill(): void
    {
        $skill = Skill::create([
            'plan_id' => $this->plan->id,
            'skill_reference_id' => $this->skillRef->id,
            'status' => SkillStatus::Suggested,
            'acquired' => 'no',
            'sp_cost' => 100,
        ]);

        $response = $this->putJson("/api/v1/plans/{$this->plan->id}/skills/{$skill->id}", [
            'status' => 'acquired',
            'turn_acquired' => 25,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => 'acquired',
                    'turn_acquired' => 25,
                ],
            ]);
    }

    public function test_destroy_soft_deletes_skill(): void
    {
        $skill = Skill::create([
            'plan_id' => $this->plan->id,
            'skill_reference_id' => $this->skillRef->id,
            'status' => SkillStatus::Suggested,
            'acquired' => 'no',
        ]);

        $response = $this->deleteJson("/api/v1/plans/{$this->plan->id}/skills/{$skill->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted('skills', ['id' => $skill->id]);
    }

    public function test_search_finds_skill_references(): void
    {
        SkillReference::create([
            'skill_name' => 'Stamina Keeper',
            'description' => 'Maintains stamina',
            'tag' => '💪',
        ]);

        $response = $this->getJson('/api/v1/skills/search?q=Speed');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_search_requires_query_parameter(): void
    {
        $response = $this->getJson('/api/v1/skills/search');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['q']);
    }

    public function test_totals_returns_sp_totals(): void
    {
        Skill::create([
            'plan_id' => $this->plan->id,
            'skill_reference_id' => $this->skillRef->id,
            'status' => SkillStatus::Acquired,
            'turn_acquired' => 10,
            'acquired' => 'yes',
            'sp_cost' => 100,
        ]);

        Skill::create([
            'plan_id' => $this->plan->id,
            'skill_reference_id' => $this->skillRef->id,
            'status' => SkillStatus::Suggested,
            'acquired' => 'no',
            'sp_cost' => 150,
        ]);

        $response = $this->getJson("/api/v1/plans/{$this->plan->id}/skills/totals");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'acquired' => 100,
                    'suggested' => 150,
                    'total_planned' => 250,
                ],
            ]);
    }
}
