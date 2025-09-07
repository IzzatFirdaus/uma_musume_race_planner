<?php

namespace Tests\Feature\Services;

use App\Models\Condition;
use App\Models\Mood;
use App\Models\Plan;
use App\Models\Strategy;
use App\Services\PlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class PlanServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PlanService $planService;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed lookup tables
        $this->artisan('db:seed', ['--class' => \Database\Seeders\LookupSeeder::class]);

        // Initialize service
        $this->planService = app(PlanService::class);
    }

    public function test_create_quick_plan_successfully(): void
    {
        $planData = [
            'trainee_name' => 'Test Horse',
            'career_stage' => 'junior',
            'traineeClass' => 'beginner',
            'race_name' => 'Test Race',
        ];

        $plan = $this->planService->createQuickPlan($planData);

        $this->assertInstanceOf(Plan::class, $plan);
        $this->assertEquals('Test Horse', $plan->name);
        $this->assertEquals("Test Horse's New Plan", $plan->plan_title);
        $this->assertEquals('junior', $plan->career_stage);
        $this->assertEquals('beginner', $plan->class);
        $this->assertEquals('Test Race', $plan->race_name);

        // Check default attributes were created
        $this->assertEquals(5, $plan->attributes()->count());
        $this->assertEquals('SPEED', $plan->attributes()->where('attribute_name', 'SPEED')->first()->attribute_name);
    }

    public function test_create_detailed_plan_successfully(): void
    {
        $request = Request::create('/test', 'POST');

        $validated = [
            'plan' => [
                'plan_title' => 'Detailed Test Plan',
                'name' => 'Test Horse Detailed',
                'career_stage' => 'senior',
                'class' => 'gold',
                'race_name' => 'Detailed Test Race',
                'status' => 'Planning',
                'mood_id' => Mood::first()->id,
                'condition_id' => Condition::first()->id,
                'strategy_id' => Strategy::first()->id,
            ],
            'attributes' => [
                ['attribute_name' => 'SPEED', 'value' => 500, 'grade' => 'B'],
                ['attribute_name' => 'STAMINA', 'value' => 400, 'grade' => 'C'],
            ],
            'skills' => [
                ['name' => 'Test Skill', 'sp_cost' => 100, 'acquired' => 'no', 'notes' => 'Test notes'],
            ],
        ];

        $plan = $this->planService->createDetailedPlan($request, $validated);

        $this->assertInstanceOf(Plan::class, $plan);
        $this->assertEquals('Detailed Test Plan', $plan->plan_title);
        $this->assertEquals('Test Horse Detailed', $plan->name);

        // Check attributes were created
        $this->assertEquals(2, $plan->attributes()->count());
        $this->assertEquals('SPEED', $plan->attributes()->first()->attribute_name);

        // Check skills were created
        $this->assertEquals(1, $plan->skills()->count());
        $this->assertEquals('no', $plan->skills()->first()->acquired);
    }

    public function test_delete_plan_successfully(): void
    {
        // Create a plan manually with existing lookup data to avoid factory issues
        $plan = Plan::create([
            'name' => 'Test Plan for Deletion',
            'plan_title' => 'Test Plan for Deletion',
            'career_stage' => 'senior',
            'class' => 'gold',
            'race_name' => 'Test Race',
            'status' => 'Planning',
            'mood_id' => Mood::where('label', 'NORMAL')->first()->id,
            'condition_id' => Condition::where('label', 'N/A')->first()->id,
            'strategy_id' => Strategy::where('label', 'PACE')->first()->id,
        ]);

        $planId = $plan->id;

        $this->planService->deletePlan($plan);

        // Check plan is soft deleted
        $this->assertSoftDeleted('plans', ['id' => $planId]);
    }
}
