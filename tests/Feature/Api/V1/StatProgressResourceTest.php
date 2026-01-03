<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Condition;
use App\Models\Mood;
use App\Models\Plan;
use App\Models\Strategy;
use App\Models\Turn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for StatProgress API endpoints.
 * Task 6.2.1: Test API functionality.
 */
class StatProgressResourceTest extends TestCase
{
    use RefreshDatabase;

    protected Plan $plan;

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
    }

    public function test_index_returns_turns_for_plan(): void
    {
        Turn::create([
            'plan_id' => $this->plan->id,
            'turn_number' => 1,
            'speed' => 100,
            'stamina' => 90,
            'power' => 80,
            'guts' => 70,
            'wit' => 60,
        ]);

        $response = $this->getJson("/api/v1/plans/{$this->plan->id}/stats");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'turn_number',
                        'speed',
                        'stamina',
                        'power',
                        'guts',
                        'wit',
                    ],
                ],
            ]);
    }

    public function test_store_creates_new_turn(): void
    {
        $data = [
            'turn_number' => 1,
            'speed' => 150,
            'stamina' => 140,
            'power' => 130,
            'guts' => 120,
            'wit' => 110,
        ];

        $response = $this->postJson("/api/v1/plans/{$this->plan->id}/stats", $data);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'turn_number' => 1,
                    'speed' => 150,
                    'stamina' => 140,
                    'power' => 130,
                    'guts' => 120,
                    'wit' => 110,
                ],
            ]);

        $this->assertDatabaseHas('turns', [
            'plan_id' => $this->plan->id,
            'turn_number' => 1,
            'speed' => 150,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->postJson("/api/v1/plans/{$this->plan->id}/stats", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['speed', 'stamina', 'power', 'guts', 'wit']);
    }

    public function test_show_returns_single_turn(): void
    {
        $turn = Turn::create([
            'plan_id' => $this->plan->id,
            'turn_number' => 5,
            'speed' => 200,
            'stamina' => 180,
            'power' => 160,
            'guts' => 140,
            'wit' => 120,
        ]);

        $response = $this->getJson("/api/v1/plans/{$this->plan->id}/stats/{$turn->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'turn_number' => 5,
                    'speed' => 200,
                ],
            ]);
    }

    public function test_update_modifies_turn(): void
    {
        $turn = Turn::create([
            'plan_id' => $this->plan->id,
            'turn_number' => 1,
            'speed' => 100,
            'stamina' => 100,
            'power' => 100,
            'guts' => 100,
            'wit' => 100,
        ]);

        $response = $this->putJson("/api/v1/plans/{$this->plan->id}/stats/{$turn->id}", [
            'speed' => 150,
            'stamina' => 140,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'speed' => 150,
                    'stamina' => 140,
                ],
            ]);
    }

    public function test_destroy_deletes_turn(): void
    {
        $turn = Turn::create([
            'plan_id' => $this->plan->id,
            'turn_number' => 1,
            'speed' => 100,
            'stamina' => 100,
            'power' => 100,
            'guts' => 100,
            'wit' => 100,
        ]);

        $response = $this->deleteJson("/api/v1/plans/{$this->plan->id}/stats/{$turn->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('turns', ['id' => $turn->id]);
    }

    public function test_totals_returns_stat_totals(): void
    {
        Turn::create([
            'plan_id' => $this->plan->id,
            'turn_number' => 1,
            'speed' => 100,
            'stamina' => 90,
            'power' => 80,
            'guts' => 70,
            'wit' => 60,
        ]);

        Turn::create([
            'plan_id' => $this->plan->id,
            'turn_number' => 2,
            'speed' => 200,
            'stamina' => 180,
            'power' => 160,
            'guts' => 140,
            'wit' => 120,
        ]);

        $response = $this->getJson("/api/v1/plans/{$this->plan->id}/stats/totals");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'speed' => 200,
                    'stamina' => 180,
                    'power' => 160,
                    'guts' => 140,
                    'wit' => 120,
                    'total' => 800,
                ],
            ]);
    }

    public function test_averages_returns_stat_averages(): void
    {
        Turn::create([
            'plan_id' => $this->plan->id,
            'turn_number' => 1,
            'speed' => 100,
            'stamina' => 100,
            'power' => 100,
            'guts' => 100,
            'wit' => 100,
        ]);

        Turn::create([
            'plan_id' => $this->plan->id,
            'turn_number' => 2,
            'speed' => 200,
            'stamina' => 200,
            'power' => 200,
            'guts' => 200,
            'wit' => 200,
        ]);

        $response = $this->getJson("/api/v1/plans/{$this->plan->id}/stats/averages");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'speed' => 150.0,
                    'stamina' => 150.0,
                    'power' => 150.0,
                    'guts' => 150.0,
                    'wit' => 150.0,
                ],
            ]);
    }

    public function test_chart_returns_chart_data(): void
    {
        Turn::create([
            'plan_id' => $this->plan->id,
            'turn_number' => 1,
            'speed' => 100,
            'stamina' => 90,
            'power' => 80,
            'guts' => 70,
            'wit' => 60,
        ]);

        $response = $this->getJson("/api/v1/plans/{$this->plan->id}/stats/chart");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'labels',
                    'datasets' => [
                        'speed',
                        'stamina',
                        'power',
                        'guts',
                        'wit',
                    ],
                ],
            ]);
    }

    public function test_summary_returns_stat_summary(): void
    {
        Turn::create([
            'plan_id' => $this->plan->id,
            'turn_number' => 1,
            'speed' => 100,
            'stamina' => 90,
            'power' => 80,
            'guts' => 70,
            'wit' => 60,
        ]);

        $response = $this->getJson("/api/v1/plans/{$this->plan->id}/stats/summary");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'speed' => ['min', 'max', 'current'],
                    'stamina' => ['min', 'max', 'current'],
                    'power' => ['min', 'max', 'current'],
                    'guts' => ['min', 'max', 'current'],
                    'wit' => ['min', 'max', 'current'],
                ],
            ]);
    }
}
