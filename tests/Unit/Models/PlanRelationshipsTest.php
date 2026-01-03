<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\Scenario;
use App\Enums\SkillStatus;
use App\Enums\StorageMode;
use App\Models\Attribute;
use App\Models\CareerSnapshot;
use App\Models\Condition;
use App\Models\Goal;
use App\Models\Mood;
use App\Models\Plan;
use App\Models\RacePrediction;
use App\Models\Skill;
use App\Models\SkillReference;
use App\Models\Strategy;
use App\Models\Turn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for Plan model relationships.
 * Task 6.1.1: Test all model relationships.
 */
class PlanRelationshipsTest extends TestCase
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

    public function test_plan_has_many_attributes(): void
    {
        Attribute::create([
            'plan_id' => $this->plan->id,
            'attribute_name' => 'SPEED',
            'value' => 500,
            'grade' => 'B',
        ]);

        $this->assertCount(1, $this->plan->attributes);
        $this->assertInstanceOf(Attribute::class, $this->plan->attributes->first());
    }

    public function test_plan_has_many_skills(): void
    {
        $skillRef = SkillReference::create([
            'skill_name' => 'Test Skill',
            'description' => 'A test skill',
            'tag' => '📝',
        ]);

        Skill::create([
            'plan_id' => $this->plan->id,
            'skill_reference_id' => $skillRef->id,
            'status' => SkillStatus::Suggested,
            'acquired' => 'no',
        ]);

        $this->assertCount(1, $this->plan->skills);
        $this->assertInstanceOf(Skill::class, $this->plan->skills->first());
    }

    public function test_plan_has_many_goals(): void
    {
        Goal::create([
            'plan_id' => $this->plan->id,
            'goal' => 'Win the race',
            'result' => false,
        ]);

        $this->assertCount(1, $this->plan->goals);
        $this->assertInstanceOf(Goal::class, $this->plan->goals->first());
    }

    public function test_plan_has_many_race_predictions(): void
    {
        RacePrediction::create([
            'plan_id' => $this->plan->id,
            'race_name' => 'Test Race',
            'distance_category' => 'middle',
            'track_type' => 'turf',
            'venue' => 'Tokyo',
        ]);

        $this->assertCount(1, $this->plan->racePredictions);
        $this->assertInstanceOf(RacePrediction::class, $this->plan->racePredictions->first());
    }

    public function test_plan_has_many_turns(): void
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

        $this->assertCount(1, $this->plan->turns);
        $this->assertInstanceOf(Turn::class, $this->plan->turns->first());
    }

    public function test_plan_has_many_career_snapshots(): void
    {
        CareerSnapshot::create([
            'plan_id' => $this->plan->id,
            'turn_number' => 10,
            'race_name' => 'Snapshot Race',
            'speed' => 500,
            'stamina' => 400,
            'power' => 300,
            'guts' => 200,
            'wit' => 100,
            'snapshot_data' => json_encode(['test' => 'data']),
        ]);

        $this->assertCount(1, $this->plan->careerSnapshots);
        $this->assertInstanceOf(CareerSnapshot::class, $this->plan->careerSnapshots->first());
    }

    public function test_plan_belongs_to_mood(): void
    {
        $this->assertInstanceOf(Mood::class, $this->plan->mood);
    }

    public function test_plan_belongs_to_condition(): void
    {
        $this->assertInstanceOf(Condition::class, $this->plan->condition);
    }

    public function test_plan_belongs_to_strategy(): void
    {
        $this->assertInstanceOf(Strategy::class, $this->plan->strategy);
    }

    public function test_skill_belongs_to_plan(): void
    {
        $skillRef = SkillReference::create([
            'skill_name' => 'Test Skill',
            'description' => 'A test skill',
            'tag' => '📝',
        ]);

        $skill = Skill::create([
            'plan_id' => $this->plan->id,
            'skill_reference_id' => $skillRef->id,
            'status' => SkillStatus::Suggested,
            'acquired' => 'no',
        ]);

        $this->assertInstanceOf(Plan::class, $skill->plan);
        $this->assertEquals($this->plan->id, $skill->plan->id);
    }

    public function test_skill_belongs_to_skill_reference(): void
    {
        $skillRef = SkillReference::create([
            'skill_name' => 'Test Skill',
            'description' => 'A test skill',
            'tag' => '📝',
        ]);

        $skill = Skill::create([
            'plan_id' => $this->plan->id,
            'skill_reference_id' => $skillRef->id,
            'status' => SkillStatus::Suggested,
            'acquired' => 'no',
        ]);

        $this->assertInstanceOf(SkillReference::class, $skill->skillReference);
        $this->assertEquals('Test Skill', $skill->skillReference->skill_name);
    }

    public function test_turn_belongs_to_plan(): void
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

        $this->assertInstanceOf(Plan::class, $turn->plan);
        $this->assertEquals($this->plan->id, $turn->plan->id);
    }
}
