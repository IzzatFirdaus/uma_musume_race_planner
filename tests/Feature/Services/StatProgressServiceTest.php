<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Enums\StorageMode;
use App\Models\Condition;
use App\Models\Mood;
use App\Models\Plan;
use App\Models\Strategy;
use App\Models\Turn;
use App\Services\StatProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * Feature tests for StatProgressService.
 * Task 6.1.2: Test service methods.
 */
class StatProgressServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StatProgressService $service;

    protected Plan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => \Database\Seeders\LookupSeeder::class]);

        $this->service = app(StatProgressService::class);

        $this->plan = Plan::create([
            'name' => 'Test Horse',
            'plan_title' => 'Test Plan',
            'career_stage' => 'senior',
            'class' => 'gold',
            'race_name' => 'Test Race',
            'status' => 'Planning',
            'storage_mode' => StorageMode::Account,
            'mood_id' => Mood::first()?->id,
            'condition_id' => Condition::first()?->id,
            'strategy_id' => Strategy::first()?->id,
        ]);
    }

    public function test_log_turn_successfully(): void
    {
        $turn = $this->service->logTurn($this->plan, [
            'turn_number' => 1,
            'speed' => 100,
            'stamina' => 90,
            'power' => 80,
            'guts' => 70,
            'wit' => 60,
        ]);

        $this->assertInstanceOf(Turn::class, $turn);
        $this->assertEquals(1, $turn->turn_number);
        $this->assertEquals(100, $turn->speed);
        $this->assertEquals(90, $turn->stamina);
        $this->assertEquals(80, $turn->power);
        $this->assertEquals(70, $turn->guts);
        $this->assertEquals(60, $turn->wit);
        $this->assertEquals(100, $turn->stamina_percentage);
    }

    public function test_log_turn_auto_increments_turn_number(): void
    {
        $this->service->logTurn($this->plan, [
            'turn_number' => 1,
            'speed' => 100,
            'stamina' => 100,
            'power' => 100,
            'guts' => 100,
            'wit' => 100,
        ]);

        $turn2 = $this->service->logTurn($this->plan, [
            'speed' => 150,
            'stamina' => 150,
            'power' => 150,
            'guts' => 150,
            'wit' => 150,
        ]);

        $this->assertEquals(2, $turn2->turn_number);
    }

    public function test_update_turn_successfully(): void
    {
        $turn = $this->service->logTurn($this->plan, [
            'turn_number' => 1,
            'speed' => 100,
            'stamina' => 100,
            'power' => 100,
            'guts' => 100,
            'wit' => 100,
        ]);

        $updated = $this->service->updateTurn($turn, [
            'speed' => 150,
            'stamina' => 140,
        ]);

        $this->assertEquals(150, $updated->speed);
        $this->assertEquals(140, $updated->stamina);
        $this->assertEquals(100, $updated->power);
    }

    public function test_delete_turn_successfully(): void
    {
        $turn = $this->service->logTurn($this->plan, [
            'turn_number' => 1,
            'speed' => 100,
            'stamina' => 100,
            'power' => 100,
            'guts' => 100,
            'wit' => 100,
        ]);

        $result = $this->service->deleteTurn($turn);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('turns', ['id' => $turn->id]);
    }

    public function test_get_stat_totals(): void
    {
        $this->service->logTurn($this->plan, [
            'turn_number' => 1,
            'speed' => 100,
            'stamina' => 90,
            'power' => 80,
            'guts' => 70,
            'wit' => 60,
        ]);

        $this->service->logTurn($this->plan, [
            'turn_number' => 2,
            'speed' => 200,
            'stamina' => 180,
            'power' => 160,
            'guts' => 140,
            'wit' => 120,
        ]);

        $totals = $this->service->getStatTotals($this->plan);

        $this->assertEquals(200, $totals['speed']);
        $this->assertEquals(180, $totals['stamina']);
        $this->assertEquals(160, $totals['power']);
        $this->assertEquals(140, $totals['guts']);
        $this->assertEquals(120, $totals['wit']);
        $this->assertEquals(800, $totals['total']);
    }

    public function test_get_stat_totals_returns_zeros_when_no_turns(): void
    {
        $totals = $this->service->getStatTotals($this->plan);

        $this->assertEquals(0, $totals['speed']);
        $this->assertEquals(0, $totals['stamina']);
        $this->assertEquals(0, $totals['power']);
        $this->assertEquals(0, $totals['guts']);
        $this->assertEquals(0, $totals['wit']);
        $this->assertEquals(0, $totals['total']);
    }

    public function test_get_stat_averages(): void
    {
        $this->service->logTurn($this->plan, [
            'turn_number' => 1,
            'speed' => 100,
            'stamina' => 100,
            'power' => 100,
            'guts' => 100,
            'wit' => 100,
        ]);

        $this->service->logTurn($this->plan, [
            'turn_number' => 2,
            'speed' => 200,
            'stamina' => 200,
            'power' => 200,
            'guts' => 200,
            'wit' => 200,
        ]);

        $averages = $this->service->getStatAverages($this->plan);

        $this->assertEquals(150.0, $averages['speed']);
        $this->assertEquals(150.0, $averages['stamina']);
        $this->assertEquals(150.0, $averages['power']);
        $this->assertEquals(150.0, $averages['guts']);
        $this->assertEquals(150.0, $averages['wit']);
    }

    public function test_get_stat_growth(): void
    {
        $this->service->logTurn($this->plan, [
            'turn_number' => 1,
            'speed' => 100,
            'stamina' => 100,
            'power' => 100,
            'guts' => 100,
            'wit' => 100,
        ]);

        $this->service->logTurn($this->plan, [
            'turn_number' => 2,
            'speed' => 120,
            'stamina' => 115,
            'power' => 110,
            'guts' => 105,
            'wit' => 108,
        ]);

        $growth = $this->service->getStatGrowth($this->plan);

        $this->assertCount(1, $growth);
        $this->assertEquals(2, $growth[0]['turn_number']);
        $this->assertEquals(20, $growth[0]['speed']);
        $this->assertEquals(15, $growth[0]['stamina']);
        $this->assertEquals(10, $growth[0]['power']);
        $this->assertEquals(5, $growth[0]['guts']);
        $this->assertEquals(8, $growth[0]['wit']);
    }

    public function test_get_chart_data(): void
    {
        $this->service->logTurn($this->plan, [
            'turn_number' => 1,
            'speed' => 100,
            'stamina' => 90,
            'power' => 80,
            'guts' => 70,
            'wit' => 60,
        ]);

        $this->service->logTurn($this->plan, [
            'turn_number' => 2,
            'speed' => 150,
            'stamina' => 140,
            'power' => 130,
            'guts' => 120,
            'wit' => 110,
        ]);

        $chartData = $this->service->getChartData($this->plan);

        $this->assertEquals([1, 2], $chartData['labels']);
        $this->assertEquals([100, 150], $chartData['datasets']['speed']);
        $this->assertEquals([90, 140], $chartData['datasets']['stamina']);
        $this->assertEquals([80, 130], $chartData['datasets']['power']);
        $this->assertEquals([70, 120], $chartData['datasets']['guts']);
        $this->assertEquals([60, 110], $chartData['datasets']['wit']);
    }

    public function test_get_stat_at_turn(): void
    {
        $this->service->logTurn($this->plan, [
            'turn_number' => 5,
            'speed' => 200,
            'stamina' => 180,
            'power' => 160,
            'guts' => 140,
            'wit' => 120,
        ]);

        $turn = $this->service->getStatAtTurn($this->plan, 5);

        $this->assertNotNull($turn);
        $this->assertEquals(200, $turn->speed);
    }

    public function test_get_stat_at_turn_returns_null_for_nonexistent(): void
    {
        $turn = $this->service->getStatAtTurn($this->plan, 999);

        $this->assertNull($turn);
    }

    public function test_turn_exists(): void
    {
        $this->service->logTurn($this->plan, [
            'turn_number' => 10,
            'speed' => 100,
            'stamina' => 100,
            'power' => 100,
            'guts' => 100,
            'wit' => 100,
        ]);

        $this->assertTrue($this->service->turnExists($this->plan, 10));
        $this->assertFalse($this->service->turnExists($this->plan, 11));
    }

    public function test_get_stat_summary(): void
    {
        $this->service->logTurn($this->plan, [
            'turn_number' => 1,
            'speed' => 100,
            'stamina' => 90,
            'power' => 80,
            'guts' => 70,
            'wit' => 60,
        ]);

        $this->service->logTurn($this->plan, [
            'turn_number' => 2,
            'speed' => 200,
            'stamina' => 180,
            'power' => 160,
            'guts' => 140,
            'wit' => 120,
        ]);

        $summary = $this->service->getStatSummary($this->plan);

        $this->assertEquals(100, $summary['speed']['min']);
        $this->assertEquals(200, $summary['speed']['max']);
        $this->assertEquals(200, $summary['speed']['current']);
    }

    public function test_bulk_create_turns(): void
    {
        $turnsData = [
            ['turn_number' => 1, 'speed' => 100, 'stamina' => 100, 'power' => 100, 'guts' => 100, 'wit' => 100],
            ['turn_number' => 2, 'speed' => 150, 'stamina' => 150, 'power' => 150, 'guts' => 150, 'wit' => 150],
            ['turn_number' => 3, 'speed' => 200, 'stamina' => 200, 'power' => 200, 'guts' => 200, 'wit' => 200],
        ];

        $created = $this->service->bulkCreate($this->plan, $turnsData);

        $this->assertCount(3, $created);
        $this->assertEquals(3, $this->plan->turns()->count());
    }

    public function test_get_next_turn_number(): void
    {
        $this->assertEquals(1, $this->service->getNextTurnNumber($this->plan));

        $this->service->logTurn($this->plan, [
            'turn_number' => 5,
            'speed' => 100,
            'stamina' => 100,
            'power' => 100,
            'guts' => 100,
            'wit' => 100,
        ]);

        $this->assertEquals(6, $this->service->getNextTurnNumber($this->plan));
    }

    public function test_log_turn_in_local_mode_returns_array(): void
    {
        $localPlan = Plan::create([
            'name' => 'Local Test Horse',
            'plan_title' => 'Local Test Plan',
            'career_stage' => 'junior',
            'class' => 'beginner',
            'race_name' => '',
            'status' => 'Planning',
            'storage_mode' => StorageMode::Local,
            'local_uuid' => (string) Str::uuid(),
            'mood_id' => Mood::first()?->id,
            'condition_id' => Condition::first()?->id,
            'strategy_id' => Strategy::first()?->id,
        ]);

        $result = $this->service->logTurn($localPlan, [
            'turn_number' => 1,
            'speed' => 100,
            'stamina' => 90,
            'power' => 80,
            'guts' => 70,
            'wit' => 60,
            'stamina_percentage' => 85,
        ]);

        $this->assertIsArray($result);
        $this->assertEquals(1, $result['turn_number']);
        $this->assertEquals(100, $result['speed']);
        $this->assertEquals(90, $result['stamina']);
        $this->assertEquals(80, $result['power']);
        $this->assertEquals(70, $result['guts']);
        $this->assertEquals(60, $result['wit']);
        $this->assertEquals(85, $result['stamina_percentage']);
        $this->assertEquals(StorageMode::Local->value, $result['storage_mode']);
    }

    public function test_log_turn_in_account_mode_persists_to_database(): void
    {
        $accountPlan = Plan::create([
            'name' => 'Account Test Horse',
            'plan_title' => 'Account Test Plan',
            'career_stage' => 'senior',
            'class' => 'gold',
            'race_name' => 'Test Race',
            'status' => 'Planning',
            'storage_mode' => StorageMode::Account,
            'mood_id' => Mood::first()?->id,
            'condition_id' => Condition::first()?->id,
            'strategy_id' => Strategy::first()?->id,
        ]);

        $turn = $this->service->logTurn($accountPlan, [
            'turn_number' => 1,
            'speed' => 150,
            'stamina' => 140,
            'power' => 130,
            'guts' => 120,
            'wit' => 110,
            'stamina_percentage' => 75,
        ]);

        $this->assertInstanceOf(Turn::class, $turn);
        $this->assertDatabaseHas('turns', [
            'id' => $turn->id,
            'plan_id' => $accountPlan->id,
            'turn_number' => 1,
            'speed' => 150,
            'stamina' => 140,
            'power' => 130,
            'guts' => 120,
            'wit' => 110,
            'stamina_percentage' => 75,
        ]);
    }

    public function test_validate_stats_throws_exception_for_invalid_values(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Stat');

        $this->service->logTurn($this->plan, [
            'speed' => 1500, // Exceeds MAX_STAT_VALUE
            'stamina' => 100,
            'power' => 100,
            'guts' => 100,
            'wit' => 100,
        ]);
    }

    public function test_validate_stats_throws_exception_for_negative_values(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Stat');

        $this->service->logTurn($this->plan, [
            'speed' => -10, // Below MIN_STAT_VALUE
            'stamina' => 100,
            'power' => 100,
            'guts' => 100,
            'wit' => 100,
        ]);
    }

    public function test_validate_stamina_percentage_throws_exception_for_invalid_values(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Stamina percentage');

        $this->service->logTurn($this->plan, [
            'speed' => 100,
            'stamina' => 100,
            'power' => 100,
            'guts' => 100,
            'wit' => 100,
            'stamina_percentage' => 150, // Exceeds 100
        ]);
    }

    public function test_recalculate_totals_updates_plan(): void
    {
        $this->service->logTurn($this->plan, [
            'turn_number' => 1,
            'speed' => 100,
            'stamina' => 90,
            'power' => 80,
            'guts' => 70,
            'wit' => 60,
            'stamina_percentage' => 85,
        ]);

        $totals = $this->service->recalculateTotals($this->plan);

        $this->assertEquals(100, $totals['speed']);
        $this->assertEquals(90, $totals['stamina']);
        $this->assertEquals(80, $totals['power']);
        $this->assertEquals(70, $totals['guts']);
        $this->assertEquals(60, $totals['wit']);
        $this->assertEquals(400, $totals['total']);
        $this->assertEquals(85, $totals['stamina_percentage']);

        $this->plan->refresh();
        $this->assertEquals(400, $this->plan->total_available_skill_points);
        $this->assertEquals(85, $this->plan->stamina_percentage);
    }

    public function test_recalculate_totals_with_no_turns_returns_zeros(): void
    {
        $emptyPlan = Plan::create([
            'name' => 'Empty Plan',
            'plan_title' => 'Empty Plan',
            'career_stage' => 'junior',
            'class' => 'beginner',
            'race_name' => '',
            'status' => 'Planning',
            'storage_mode' => StorageMode::Account,
            'mood_id' => Mood::first()?->id,
            'condition_id' => Condition::first()?->id,
            'strategy_id' => Strategy::first()?->id,
        ]);

        $totals = $this->service->recalculateTotals($emptyPlan);

        $this->assertEquals(0, $totals['speed']);
        $this->assertEquals(0, $totals['stamina']);
        $this->assertEquals(0, $totals['power']);
        $this->assertEquals(0, $totals['guts']);
        $this->assertEquals(0, $totals['wit']);
        $this->assertEquals(0, $totals['total']);
        $this->assertEquals(100, $totals['stamina_percentage']); // Default
    }

    public function test_log_turn_includes_stamina_percentage(): void
    {
        $turn = $this->service->logTurn($this->plan, [
            'turn_number' => 1,
            'speed' => 100,
            'stamina' => 100,
            'power' => 100,
            'guts' => 100,
            'wit' => 100,
            'stamina_percentage' => 50,
        ]);

        $this->assertEquals(50, $turn->stamina_percentage);
    }

    public function test_log_turn_defaults_stamina_percentage_to_100(): void
    {
        $turn = $this->service->logTurn($this->plan, [
            'turn_number' => 1,
            'speed' => 100,
            'stamina' => 100,
            'power' => 100,
            'guts' => 100,
            'wit' => 100,
        ]);

        $this->assertEquals(100, $turn->stamina_percentage);
    }

    public function test_update_turn_includes_stamina_percentage(): void
    {
        $turn = $this->service->logTurn($this->plan, [
            'turn_number' => 1,
            'speed' => 100,
            'stamina' => 100,
            'power' => 100,
            'guts' => 100,
            'wit' => 100,
        ]);

        $updated = $this->service->updateTurn($turn, [
            'stamina_percentage' => 75,
        ]);

        $this->assertEquals(75, $updated->stamina_percentage);
    }
}
