<?php

namespace Tests\Feature\Api\V1;

use App\Models\Condition;
use App\Models\Mood;
use App\Models\Plan;
use App\Models\Strategy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed only the lookup tables we need
        $this->artisan('db:seed', ['--class' => 'Database\Seeders\LookupSeeder']);
    }

    /**
     * Test that plan API index returns proper JSON structure.
     */
    public function test_plan_index_returns_proper_json_structure(): void
    {
        // Create test user
        $user = User::factory()->create(['email' => 'test@example.com']);

        // Create test plans using existing lookup data without factory
        for ($i = 1; $i <= 3; $i++) {
            Plan::create([
                'user_id' => $user->id,
                'name' => "Test Horse {$i}",
                'plan_title' => "Test Plan {$i}",
                'career_stage' => 'senior',
                'class' => 'gold',
                'race_name' => 'Test Race',
                'status' => 'Planning',
                'mood_id' => Mood::first()?->id ?? 1,
                'condition_id' => Condition::first()?->id ?? 1,
                'strategy_id' => Strategy::first()?->id ?? 1,
            ]);
        }

        $response = $this->getJson('/api/v1/plans');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'plan_title',
                        'name',
                        'career_stage',
                        'class',
                        'created_at',
                        'updated_at',
                        'links' => [
                            'self',
                            'progress_chart',
                        ],
                    ],
                ],
                'meta' => [
                    'count',
                    'version',
                    'timestamp',
                ],
                'links' => [
                    'self',
                ],
                'status',
            ]);
    }

    /**
     * Test that plan API show returns proper JSON structure.
     */
    public function test_plan_show_returns_proper_json_structure(): void
    {
        // Create test user
        $user = User::factory()->create(['email' => 'test@example.com']);

        // Create test plan using existing lookup data without factory
        $plan = Plan::create([
            'user_id' => $user->id,
            'name' => 'Test Horse Show',
            'plan_title' => 'Test Plan Show',
            'career_stage' => 'senior',
            'class' => 'gold',
            'race_name' => 'Test Race',
            'status' => 'Planning',
            'mood_id' => Mood::first()?->id ?? 1,
            'condition_id' => Condition::first()?->id ?? 1,
            'strategy_id' => Strategy::first()?->id ?? 1,
        ]);

        $response = $this->getJson("/api/v1/plans/{$plan->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'plan_title',
                    'name',
                    'career_stage',
                    'class',
                    'created_at',
                    'updated_at',
                    'links' => [
                        'self',
                        'progress_chart',
                    ],
                ],
                'meta' => [
                    'version',
                    'timestamp',
                ],
            ])
            ->assertJson([
                'data' => [
                    'id' => $plan->id,
                    'plan_title' => $plan->plan_title,
                    'name' => $plan->name,
                ],
                'meta' => [
                    'version' => 'v1',
                ],
            ]);
    }

    /**
     * Test that API handles not found plans gracefully.
     */
    public function test_plan_show_returns_not_found_for_invalid_id(): void
    {
        $response = $this->getJson('/api/v1/plans/999999');

        $response->assertStatus(404);
    }
}
