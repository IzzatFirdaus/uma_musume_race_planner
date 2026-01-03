<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Condition;
use App\Models\Mood;
use App\Models\Plan;
use App\Models\Strategy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for Export API endpoints.
 * Task 6.2.2: Test export functionality.
 */
class ExportResourceTest extends TestCase
{
    use RefreshDatabase;

    protected Plan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => \Database\Seeders\LookupSeeder::class]);

        $this->plan = Plan::create([
            'name' => 'Test Horse',
            'plan_title' => 'Test Plan Export',
            'career_stage' => 'senior',
            'class' => 'gold',
            'race_name' => 'Test Race',
            'status' => 'Planning',
            'mood_id' => Mood::first()?->id,
            'condition_id' => Condition::first()?->id,
            'strategy_id' => Strategy::first()?->id,
        ]);
    }

    public function test_export_json_returns_valid_json(): void
    {
        $response = $this->getJson("/api/v1/plans/{$this->plan->id}/export/json");

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure([
                'schema_version',
                'exported_at',
                'plan' => [
                    'id',
                    'title',
                    'name',
                    'career_stage',
                    'class',
                ],
            ]);
    }

    public function test_export_csv_returns_csv_content(): void
    {
        $response = $this->get("/api/v1/plans/{$this->plan->id}/export/csv");

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $content = $response->getContent();
        $this->assertStringContainsString('Plan Title', $content);
        $this->assertStringContainsString('Test Plan Export', $content);
    }

    public function test_export_markdown_returns_markdown_content(): void
    {
        $response = $this->get("/api/v1/plans/{$this->plan->id}/export/markdown");

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');

        $content = $response->getContent();
        $this->assertStringContainsString('# Test Plan Export', $content);
        $this->assertStringContainsString('## Basic Information', $content);
    }

    public function test_export_preview_returns_preview_data(): void
    {
        $response = $this->getJson("/api/v1/plans/{$this->plan->id}/export/preview?format=json");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'format',
                    'content',
                    'size',
                    'size_formatted',
                    'plan_title',
                ],
            ])
            ->assertJson([
                'data' => [
                    'format' => 'json',
                    'plan_title' => 'Test Plan Export',
                ],
            ]);
    }

    public function test_export_preview_supports_csv_format(): void
    {
        $response = $this->getJson("/api/v1/plans/{$this->plan->id}/export/preview?format=csv");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'format' => 'csv',
                ],
            ]);

        $content = $response->json('data.content');
        $this->assertStringContainsString('Plan Title', $content);
    }

    public function test_export_preview_supports_markdown_format(): void
    {
        $response = $this->getJson("/api/v1/plans/{$this->plan->id}/export/preview?format=markdown");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'format' => 'markdown',
                ],
            ]);

        $content = $response->json('data.content');
        $this->assertStringContainsString('# Test Plan Export', $content);
    }

    public function test_export_returns_404_for_nonexistent_plan(): void
    {
        $response = $this->getJson('/api/v1/plans/999999/export/json');

        $response->assertStatus(404);
    }

    public function test_bulk_export_json_returns_multiple_plans(): void
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

        $response = $this->getJson("/api/v1/export/plans?ids={$this->plan->id},{$plan2->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'schema_version',
                'exported_at',
                'count',
                'plans',
            ])
            ->assertJson([
                'count' => 2,
            ]);
    }

    public function test_bulk_export_validates_ids_parameter(): void
    {
        $response = $this->getJson('/api/v1/export/plans');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    }
}
