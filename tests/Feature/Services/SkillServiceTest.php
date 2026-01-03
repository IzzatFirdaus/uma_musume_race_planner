<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Enums\SkillStatus;
use App\Models\Condition;
use App\Models\Mood;
use App\Models\Plan;
use App\Models\Skill;
use App\Models\SkillReference;
use App\Models\Strategy;
use App\Services\SkillService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Feature tests for SkillService.
 * Task 6.1.2: Test service methods.
 */
class SkillServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SkillService $service;
    protected Plan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => \Database\Seeders\LookupSeeder::class]);

        $this->service = app(SkillService::class);

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

    public function test_search_skills_by_name(): void
    {
        SkillReference::create([
            'skill_name' => 'Speed Star',
            'description' => 'Increases speed',
            'tag' => '⚡',
        ]);
        SkillReference::create([
            'skill_name' => 'Stamina Keeper',
            'description' => 'Maintains stamina',
            'tag' => '💪',
        ]);

        Cache::flush();

        $results = $this->service->search('Speed');

        $this->assertCount(1, $results);
        $this->assertEquals('Speed Star', $results->first()->skill_name);
    }

    public function test_add_skill_to_plan_with_suggested_status(): void
    {
        $skillRef = SkillReference::create([
            'skill_name' => 'Test Skill',
            'description' => 'A test skill',
            'tag' => '📝',
        ]);

        $skill = $this->service->addToPlan($this->plan, [
            'skill_reference_id' => $skillRef->id,
            'status' => SkillStatus::Suggested,
            'sp_cost' => 100,
        ]);

        $this->assertInstanceOf(Skill::class, $skill);
        $this->assertEquals(SkillStatus::Suggested, $skill->status);
        $this->assertEquals('no', $skill->acquired);
        $this->assertNull($skill->turn_acquired);
    }

    public function test_add_skill_to_plan_with_acquired_status_requires_turn(): void
    {
        $skillRef = SkillReference::create([
            'skill_name' => 'Test Skill',
            'description' => 'A test skill',
            'tag' => '📝',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('turn_acquired is required when status is Acquired');

        $this->service->addToPlan($this->plan, [
            'skill_reference_id' => $skillRef->id,
            'status' => SkillStatus::Acquired,
            'sp_cost' => 100,
        ]);
    }

    public function test_add_skill_to_plan_with_acquired_status_and_turn(): void
    {
        $skillRef = SkillReference::create([
            'skill_name' => 'Test Skill',
            'description' => 'A test skill',
            'tag' => '📝',
        ]);

        $skill = $this->service->addToPlan($this->plan, [
            'skill_reference_id' => $skillRef->id,
            'status' => SkillStatus::Acquired,
            'turn_acquired' => 15,
            'sp_cost' => 100,
        ]);

        $this->assertEquals(SkillStatus::Acquired, $skill->status);
        $this->assertEquals('yes', $skill->acquired);
        $this->assertEquals(15, $skill->turn_acquired);
    }

    public function test_add_skill_by_name_creates_reference(): void
    {
        $skill = $this->service->addToPlan($this->plan, [
            'skill_name' => 'New Custom Skill',
            'status' => SkillStatus::Suggested,
            'sp_cost' => 50,
        ]);

        $this->assertNotNull($skill->skill_reference_id);
        $this->assertEquals('New Custom Skill', $skill->skillReference->skill_name);
    }

    public function test_update_skill_status(): void
    {
        $skillRef = SkillReference::create([
            'skill_name' => 'Test Skill',
            'description' => 'A test skill',
            'tag' => '📝',
        ]);

        $skill = $this->service->addToPlan($this->plan, [
            'skill_reference_id' => $skillRef->id,
            'status' => SkillStatus::Suggested,
        ]);

        $updated = $this->service->updateStatus($skill, SkillStatus::Acquired, 20);

        $this->assertEquals(SkillStatus::Acquired, $updated->status);
        $this->assertEquals(20, $updated->turn_acquired);
        $this->assertEquals('yes', $updated->acquired);
    }

    public function test_update_skill_status_to_skipped_clears_turn(): void
    {
        $skillRef = SkillReference::create([
            'skill_name' => 'Test Skill',
            'description' => 'A test skill',
            'tag' => '📝',
        ]);

        $skill = $this->service->addToPlan($this->plan, [
            'skill_reference_id' => $skillRef->id,
            'status' => SkillStatus::Acquired,
            'turn_acquired' => 15,
        ]);

        $updated = $this->service->updateStatus($skill, SkillStatus::Skipped);

        $this->assertEquals(SkillStatus::Skipped, $updated->status);
        $this->assertNull($updated->turn_acquired);
        $this->assertEquals('no', $updated->acquired);
    }

    public function test_remove_skill_from_plan(): void
    {
        $skillRef = SkillReference::create([
            'skill_name' => 'Test Skill',
            'description' => 'A test skill',
            'tag' => '📝',
        ]);

        $skill = $this->service->addToPlan($this->plan, [
            'skill_reference_id' => $skillRef->id,
            'status' => SkillStatus::Suggested,
        ]);

        $result = $this->service->removeFromPlan($skill);

        $this->assertTrue($result);
        $this->assertSoftDeleted('skills', ['id' => $skill->id]);
    }

    public function test_calculate_sp_totals(): void
    {
        $skillRef = SkillReference::create([
            'skill_name' => 'Test Skill',
            'description' => 'A test skill',
            'tag' => '📝',
        ]);

        $this->service->addToPlan($this->plan, [
            'skill_reference_id' => $skillRef->id,
            'status' => SkillStatus::Acquired,
            'turn_acquired' => 10,
            'sp_cost' => 100,
        ]);

        $this->service->addToPlan($this->plan, [
            'skill_reference_id' => $skillRef->id,
            'status' => SkillStatus::Suggested,
            'sp_cost' => 150,
        ]);

        $this->service->addToPlan($this->plan, [
            'skill_reference_id' => $skillRef->id,
            'status' => SkillStatus::Skipped,
            'sp_cost' => 50,
        ]);

        $totals = $this->service->calculateSpTotals($this->plan);

        $this->assertEquals(100, $totals['acquired']);
        $this->assertEquals(150, $totals['suggested']);
        $this->assertEquals(50, $totals['skipped']);
        $this->assertEquals(250, $totals['total_planned']);
        $this->assertEquals(300, $totals['total_all']);
    }

    public function test_get_status_counts(): void
    {
        $skillRef = SkillReference::create([
            'skill_name' => 'Test Skill',
            'description' => 'A test skill',
            'tag' => '📝',
        ]);

        $this->service->addToPlan($this->plan, [
            'skill_reference_id' => $skillRef->id,
            'status' => SkillStatus::Acquired,
            'turn_acquired' => 10,
        ]);

        $this->service->addToPlan($this->plan, [
            'skill_reference_id' => $skillRef->id,
            'status' => SkillStatus::Suggested,
        ]);

        $this->service->addToPlan($this->plan, [
            'skill_reference_id' => $skillRef->id,
            'status' => SkillStatus::Suggested,
        ]);

        $counts = $this->service->getStatusCounts($this->plan);

        $this->assertEquals(1, $counts['acquired']);
        $this->assertEquals(2, $counts['suggested']);
        $this->assertEquals(0, $counts['skipped']);
        $this->assertEquals(3, $counts['total']);
    }

    public function test_get_for_plan_by_status(): void
    {
        $skillRef = SkillReference::create([
            'skill_name' => 'Test Skill',
            'description' => 'A test skill',
            'tag' => '📝',
        ]);

        $this->service->addToPlan($this->plan, [
            'skill_reference_id' => $skillRef->id,
            'status' => SkillStatus::Acquired,
            'turn_acquired' => 10,
        ]);

        $this->service->addToPlan($this->plan, [
            'skill_reference_id' => $skillRef->id,
            'status' => SkillStatus::Suggested,
        ]);

        $acquired = $this->service->getForPlanByStatus($this->plan, SkillStatus::Acquired);
        $suggested = $this->service->getForPlanByStatus($this->plan, SkillStatus::Suggested);

        $this->assertCount(1, $acquired);
        $this->assertCount(1, $suggested);
    }

    public function test_bulk_update_status(): void
    {
        $skillRef = SkillReference::create([
            'skill_name' => 'Test Skill',
            'description' => 'A test skill',
            'tag' => '📝',
        ]);

        $skill1 = $this->service->addToPlan($this->plan, [
            'skill_reference_id' => $skillRef->id,
            'status' => SkillStatus::Suggested,
        ]);

        $skill2 = $this->service->addToPlan($this->plan, [
            'skill_reference_id' => $skillRef->id,
            'status' => SkillStatus::Suggested,
        ]);

        $updated = $this->service->bulkUpdateStatus(
            [$skill1->id, $skill2->id],
            SkillStatus::Acquired,
            25
        );

        $this->assertEquals(2, $updated);

        $skill1->refresh();
        $skill2->refresh();

        $this->assertEquals(SkillStatus::Acquired, $skill1->status);
        $this->assertEquals(SkillStatus::Acquired, $skill2->status);
        $this->assertEquals(25, $skill1->turn_acquired);
    }

    public function test_find_or_create_reference(): void
    {
        $ref1 = $this->service->findOrCreateReference('New Skill', [
            'description' => 'Custom description',
        ]);

        $ref2 = $this->service->findOrCreateReference('New Skill');

        $this->assertEquals($ref1->id, $ref2->id);
        $this->assertEquals('Custom description', $ref1->description);
    }
}
