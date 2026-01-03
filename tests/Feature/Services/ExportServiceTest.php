<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Models\Attribute;
use App\Models\Condition;
use App\Models\Goal;
use App\Models\Mood;
use App\Models\Plan;
use App\Models\SkillReference;
use App\Models\Strategy;
use App\Models\Turn;
use App\Services\ExportService;
use App\Services\SkillService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for ExportService.
 * Task 6.2.2: Test export functionality.
 */
class ExportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ExportService $service;
    protected SkillService $skillService;
    protected Plan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => \Database\Seeders\LookupSeeder::class]);

        $this->service = app(ExportService::class);
        $this->skillService = app(SkillService::class);

        $this->plan = Plan::create([
            'name' => 'Test Horse',
            'plan_title' => 'Test Plan Export',
            'career_stage' => 'senior',
            'class' => 'gold',
            'race_name' => 'Test Race',
            'status' => 'Planning',
            'total_available_skill_points' => 500,
            'mood_id' => Mood::first()?->id,
            'condition_id' => Condition::first()?->id,
            'strategy_id' => Strategy::first()?->id,
        ]);

        // Add some test data
        Attribute::create([
            'plan_id' => $this->plan->id,
            'attribute_name' => 'SPEED',
            'value' => 500,
            'grade' => 'B',
        ]);

        Turn::create([
            'plan_id' => $this->plan->id,
            'turn_number' => 1,
            'speed' => 100,
            'stamina' => 90,
            'power' => 80,
            'guts' => 70,
            'wit' => 60,
        ]);

        Goal::create([
            'plan_id' => $this->plan->id,
            'goal' => 'Win the race',
            'result' => false,
        ]);
    }

    public function test_plan_to_array_includes_schema_version(): void
    {
        $data = $this->service->planToArray($this->plan);

        $this->assertArrayHasKey('schema_version', $data);
        $this->assertEquals('1.0.0', $data['schema_version']);
    }

    public function test_plan_to_array_includes_exported_at(): void
    {
        $data = $this->service->planToArray($this->plan);

        $this->assertArrayHasKey('exported_at', $data);
        $this->assertNotEmpty($data['exported_at']);
    }

    public function test_plan_to_array_includes_plan_data(): void
    {
        $data = $this->service->planToArray($this->plan);

        $this->assertArrayHasKey('plan', $data);
        $this->assertEquals('Test Plan Export', $data['plan']['title']);
        $this->assertEquals('Test Horse', $data['plan']['name']);
        $this->assertEquals('senior', $data['plan']['career_stage']);
        $this->assertEquals('gold', $data['plan']['class']);
    }

    public function test_plan_to_array_includes_attributes(): void
    {
        $data = $this->service->planToArray($this->plan);

        $this->assertArrayHasKey('attributes', $data);
        $this->assertCount(1, $data['attributes']);
        $this->assertEquals('SPEED', $data['attributes'][0]['name']);
        $this->assertEquals(500, $data['attributes'][0]['value']);
        $this->assertEquals('B', $data['attributes'][0]['grade']);
    }

    public function test_plan_to_array_includes_stat_progress(): void
    {
        $data = $this->service->planToArray($this->plan);

        $this->assertArrayHasKey('stat_progress', $data);
        $this->assertCount(1, $data['stat_progress']);
        $this->assertEquals(1, $data['stat_progress'][0]['turn_number']);
        $this->assertEquals(100, $data['stat_progress'][0]['speed']);
    }

    public function test_plan_to_array_includes_goals(): void
    {
        $data = $this->service->planToArray($this->plan);

        $this->assertArrayHasKey('goals', $data);
        $this->assertCount(1, $data['goals']);
        $this->assertEquals('Win the race', $data['goals'][0]['description']);
    }

    public function test_to_json_returns_valid_json(): void
    {
        $json = $this->service->toJson($this->plan);

        $this->assertJson($json);

        $decoded = json_decode($json, true);
        $this->assertArrayHasKey('schema_version', $decoded);
        $this->assertArrayHasKey('plan', $decoded);
    }

    public function test_to_csv_includes_bom(): void
    {
        $csv = $this->service->toCsv($this->plan);

        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
    }

    public function test_to_csv_includes_headers(): void
    {
        $csv = $this->service->toCsv($this->plan);

        $this->assertStringContainsString('Plan Title', $csv);
        $this->assertStringContainsString('Character Name', $csv);
        $this->assertStringContainsString('Career Stage', $csv);
    }

    public function test_to_csv_includes_plan_data(): void
    {
        $csv = $this->service->toCsv($this->plan);

        $this->assertStringContainsString('Test Plan Export', $csv);
        $this->assertStringContainsString('Test Horse', $csv);
        $this->assertStringContainsString('senior', $csv);
    }

    public function test_to_csv_includes_attributes_section(): void
    {
        $csv = $this->service->toCsv($this->plan);

        $this->assertStringContainsString('Attributes', $csv);
        $this->assertStringContainsString('SPEED', $csv);
    }

    public function test_to_csv_includes_stat_progress_section(): void
    {
        $csv = $this->service->toCsv($this->plan);

        $this->assertStringContainsString('Stat Progress', $csv);
        $this->assertStringContainsString('Turn', $csv);
        $this->assertStringContainsString('Speed', $csv);
    }

    public function test_to_markdown_includes_title(): void
    {
        $md = $this->service->toMarkdown($this->plan);

        $this->assertStringContainsString('# Test Plan Export', $md);
    }

    public function test_to_markdown_includes_basic_info(): void
    {
        $md = $this->service->toMarkdown($this->plan);

        $this->assertStringContainsString('## Basic Information', $md);
        $this->assertStringContainsString('**Character:** Test Horse', $md);
        $this->assertStringContainsString('**Career Stage:** senior', $md);
    }

    public function test_to_markdown_includes_attributes_table(): void
    {
        $md = $this->service->toMarkdown($this->plan);

        $this->assertStringContainsString('## Attributes', $md);
        $this->assertStringContainsString('| Attribute | Value | Grade |', $md);
        $this->assertStringContainsString('| SPEED | 500 | B |', $md);
    }

    public function test_to_markdown_includes_goals(): void
    {
        $md = $this->service->toMarkdown($this->plan);

        $this->assertStringContainsString('## Goals', $md);
        $this->assertStringContainsString('Win the race', $md);
    }

    public function test_to_markdown_includes_stat_progress(): void
    {
        $md = $this->service->toMarkdown($this->plan);

        $this->assertStringContainsString('## Recent Stat Progress', $md);
        $this->assertStringContainsString('| Turn | Speed | Stamina | Power | Guts | Wit |', $md);
    }

    public function test_to_markdown_includes_footer(): void
    {
        $md = $this->service->toMarkdown($this->plan);

        $this->assertStringContainsString('Schema v1.0.0', $md);
    }

    public function test_get_preview_returns_correct_structure(): void
    {
        $preview = $this->service->getPreview($this->plan, 'json');

        $this->assertArrayHasKey('format', $preview);
        $this->assertArrayHasKey('content', $preview);
        $this->assertArrayHasKey('size', $preview);
        $this->assertArrayHasKey('size_formatted', $preview);
        $this->assertArrayHasKey('plan_title', $preview);

        $this->assertEquals('json', $preview['format']);
        $this->assertEquals('Test Plan Export', $preview['plan_title']);
    }

    public function test_get_preview_for_csv(): void
    {
        $preview = $this->service->getPreview($this->plan, 'csv');

        $this->assertEquals('csv', $preview['format']);
        $this->assertStringContainsString('Plan Title', $preview['content']);
    }

    public function test_get_preview_for_markdown(): void
    {
        $preview = $this->service->getPreview($this->plan, 'markdown');

        $this->assertEquals('markdown', $preview['format']);
        $this->assertStringContainsString('# Test Plan Export', $preview['content']);
    }

    public function test_get_schema_version(): void
    {
        $version = $this->service->getSchemaVersion();

        $this->assertEquals('1.0.0', $version);
    }

    public function test_plans_to_array_exports_multiple_plans(): void
    {
        $plan2 = Plan::create([
            'name' => 'Second Horse',
            'plan_title' => 'Second Plan',
            'career_stage' => 'junior',
            'class' => 'bronze',
            'race_name' => 'Another Race',
            'status' => 'Planning',
            'mood_id' => Mood::first()?->id,
            'condition_id' => Condition::first()?->id,
            'strategy_id' => Strategy::first()?->id,
        ]);

        $plans = Plan::whereIn('id', [$this->plan->id, $plan2->id])->get();
        $data = $this->service->plansToArray($plans);

        $this->assertArrayHasKey('count', $data);
        $this->assertEquals(2, $data['count']);
        $this->assertArrayHasKey('plans', $data);
        $this->assertCount(2, $data['plans']);
    }

    public function test_csv_properly_quotes_fields_with_commas(): void
    {
        $this->plan->update(['plan_title' => 'Plan, with comma']);

        $csv = $this->service->toCsv($this->plan);

        $this->assertStringContainsString('"Plan, with comma"', $csv);
    }
}
