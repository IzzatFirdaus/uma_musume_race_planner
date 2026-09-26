<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Enums\ImportTarget;
use App\Models\Plan;
use App\Models\User;
use App\Services\ImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for ImportService.
 * Implements REQ-IMP-1: Import Wizard testing.
 */
class ImportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ImportService $service;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => \Database\Seeders\LookupSeeder::class]);

        $this->service = app(ImportService::class);

        $this->user = User::factory()->create();
    }

    public function test_detect_format_identifies_json(): void
    {
        $json = json_encode([
            'schema_version' => '1.0.0',
            'plans' => [
                [
                    'plan_title' => 'Test Plan',
                    'name' => 'Test Horse',
                    'career_stage' => 'junior',
                ],
            ],
        ]);

        $result = $this->service->detectFormat($json, 'test.json');

        $this->assertTrue($result['detected']);
        $this->assertNotNull($result['format']);
    }

    public function test_detect_format_identifies_csv(): void
    {
        $csv = "Plan Title,Character Name,Career Stage\nTest Plan,Test Horse,junior";

        $result = $this->service->detectFormat($csv, 'test.csv');

        $this->assertTrue($result['detected']);
        $this->assertNotNull($result['format']);
    }

    public function test_detect_format_returns_alternatives(): void
    {
        $json = json_encode(['plans' => []]);

        $result = $this->service->detectFormat($json);

        $this->assertIsArray($result['alternatives']);
    }

    public function test_preview_json_returns_parsed_data(): void
    {
        $json = json_encode([
            'schema_version' => '1.0.0',
            'plans' => [
                [
                    'plan_title' => 'Test Plan',
                    'name' => 'Test Horse',
                    'career_stage' => 'junior',
                    'status' => 'ongoing',
                ],
            ],
        ]);

        $preview = $this->service->preview($json);

        $this->assertTrue($preview['success']);
        $this->assertNotEmpty($preview['plans']);
        $this->assertEquals('Test Plan', $preview['plans'][0]['plan_title']);
    }

    public function test_preview_invalid_json_returns_error(): void
    {
        $invalidJson = '{ invalid json }';

        $preview = $this->service->preview($invalidJson);

        $this->assertFalse($preview['success']);
        $this->assertNotEmpty($preview['errors']);
    }

    public function test_preview_includes_field_mapping(): void
    {
        $json = json_encode([
            'plans' => [
                ['plan_title' => 'Test'],
            ],
        ]);

        $preview = $this->service->preview($json);

        $this->assertIsArray($preview['field_mapping']);
    }

    public function test_validate_returns_valid_for_correct_data(): void
    {
        $json = json_encode([
            'plans' => [
                [
                    'plan_title' => 'Test Plan',
                    'name' => 'Test Horse',
                    'career_stage' => 'junior',
                    'status' => 'ongoing',
                ],
            ],
        ]);

        $validation = $this->service->validate($json, null, ImportTarget::Local);

        $this->assertTrue($validation['valid']);
        $this->assertEmpty($validation['errors']);
    }

    public function test_validate_returns_errors_for_invalid_data(): void
    {
        $json = json_encode([
            'plans' => [
                [
                    'plan_title' => '', // Invalid: empty title
                ],
            ],
        ]);

        $validation = $this->service->validate($json, null, ImportTarget::Local);

        $this->assertFalse($validation['valid']);
        $this->assertNotEmpty($validation['errors']);
    }

    public function test_validate_detects_duplicates_for_account_import(): void
    {
        // Create an existing plan
        Plan::create([
            'user_id' => $this->user->id,
            'plan_title' => 'Existing Plan',
            'name' => 'Test Horse',
            'career_stage' => 'junior',
            'status' => 'ongoing',
        ]);

        $json = json_encode([
            'plans' => [
                [
                    'plan_title' => 'Existing Plan', // Duplicate title
                    'name' => 'Test Horse',
                    'career_stage' => 'junior',
                    'status' => 'ongoing',
                ],
            ],
        ]);

        $validation = $this->service->validate($json, null, ImportTarget::Account, $this->user);

        $this->assertNotEmpty($validation['duplicates']);
        $this->assertArrayHasKey('plan_index', $validation['duplicates'][0]);
        $this->assertArrayHasKey('existing_id', $validation['duplicates'][0]);
    }

    public function test_validate_summary_includes_counts(): void
    {
        $json = json_encode([
            'plans' => [
                ['plan_title' => 'Plan 1'],
                ['plan_title' => 'Plan 2'],
            ],
        ]);

        $validation = $this->service->validate($json, null, ImportTarget::Local);

        $this->assertArrayHasKey('summary', $validation);
        $this->assertArrayHasKey('total', $validation['summary']);
        $this->assertEquals(2, $validation['summary']['total']);
    }

    public function test_import_to_local_returns_imported_ids(): void
    {
        $json = json_encode([
            'plans' => [
                [
                    'plan_title' => 'Test Plan',
                    'name' => 'Test Horse',
                    'career_stage' => 'junior',
                    'status' => 'ongoing',
                ],
            ],
        ]);

        $result = $this->service->import($json, null, ImportTarget::Local);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['created']);
        $this->assertNotEmpty($result['imported_ids']);
        $this->assertEquals('local', $result['imported_ids'][0]['type']);
    }

    public function test_import_to_local_requires_no_authentication(): void
    {
        $json = json_encode([
            'plans' => [
                ['plan_title' => 'Test Plan'],
            ],
        ]);

        $result = $this->service->import($json, null, ImportTarget::Local, null);

        $this->assertTrue($result['success']);
    }

    public function test_import_to_account_requires_authentication(): void
    {
        $json = json_encode([
            'plans' => [
                ['plan_title' => 'Test Plan'],
            ],
        ]);

        $result = $this->service->import($json, null, ImportTarget::Account, null);

        $this->assertFalse($result['success']);
        $this->assertNotEmpty($result['errors']);
    }

    public function test_import_to_account_creates_plans(): void
    {
        $json = json_encode([
            'plans' => [
                [
                    'plan_title' => 'Test Plan',
                    'name' => 'Test Horse',
                    'career_stage' => 'junior',
                    'status' => 'ongoing',
                ],
            ],
        ]);

        $result = $this->service->import($json, null, ImportTarget::Account, $this->user);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['created']);

        // Verify plan was created in database
        $plan = Plan::where('user_id', $this->user->id)->first();
        $this->assertNotNull($plan);
        $this->assertEquals('Test Plan', $plan->plan_title);
    }

    public function test_import_to_account_skips_duplicates_when_configured(): void
    {
        // Create existing plan
        Plan::create([
            'user_id' => $this->user->id,
            'plan_title' => 'Existing Plan',
            'name' => 'Test Horse',
            'career_stage' => 'junior',
            'status' => 'ongoing',
        ]);

        $json = json_encode([
            'plans' => [
                [
                    'plan_title' => 'Existing Plan',
                    'name' => 'Test Horse',
                    'career_stage' => 'junior',
                    'status' => 'ongoing',
                ],
            ],
        ]);

        $result = $this->service->import($json, null, ImportTarget::Account, $this->user, [
            'skip_duplicates' => true,
        ]);

        $this->assertTrue($result['success']);
        $this->assertEquals(0, $result['created']);
        $this->assertEquals(1, $result['skipped']);
    }

    public function test_import_to_account_rolls_back_on_error(): void
    {
        // Invalid data that will cause error
        $json = json_encode([
            'plans' => [
                [
                    'plan_title' => 'Test Plan',
                    // Missing required fields
                ],
            ],
        ]);

        $initialCount = Plan::where('user_id', $this->user->id)->count();

        $result = $this->service->import($json, null, ImportTarget::Account, $this->user);

        $this->assertFalse($result['success']);

        // Verify no plans were created (transaction rolled back)
        $finalCount = Plan::where('user_id', $this->user->id)->count();
        $this->assertEquals($initialCount, $finalCount);
    }

    public function test_detect_duplicates_finds_matching_plans(): void
    {
        Plan::create([
            'user_id' => $this->user->id,
            'plan_title' => 'Existing Plan',
            'name' => 'Test Horse',
            'career_stage' => 'junior',
            'status' => 'ongoing',
        ]);

        $plans = collect([
            [
                'plan_title' => 'Existing Plan',
                'name' => 'Test Horse',
            ],
        ]);

        $duplicates = $this->service->detectDuplicates($plans, $this->user);

        $this->assertCount(1, $duplicates);
        $this->assertEquals(0, $duplicates[0]['plan_index']);
    }

    public function test_detect_duplicates_returns_empty_for_unique_plans(): void
    {
        $plans = collect([
            [
                'plan_title' => 'Unique Plan',
                'name' => 'Unique Horse',
            ],
        ]);

        $duplicates = $this->service->detectDuplicates($plans, $this->user);

        $this->assertEmpty($duplicates);
    }

    public function test_generate_error_report_returns_csv(): void
    {
        $errors = [
            ['row' => 1, 'field' => 'title', 'message' => 'Title is required'],
            ['row' => 2, 'field' => 'name', 'message' => 'Name is required'],
        ];

        $report = $this->service->generateErrorReport($errors);

        $this->assertStringContainsString('Row,Field,Message', $report);
        $this->assertStringContainsString('1,title,Title is required', $report);
        $this->assertStringContainsString('2,name,Name is required', $report);
    }

    public function test_get_supported_formats_returns_array(): void
    {
        $formats = $this->service->getSupportedFormats();

        $this->assertIsArray($formats);
        $this->assertNotEmpty($formats);
    }

    public function test_get_supported_extensions_returns_array(): void
    {
        $extensions = $this->service->getSupportedExtensions();

        $this->assertIsArray($extensions);
        $this->assertNotEmpty($extensions);
        $this->assertContains('.json', $extensions);
        $this->assertContains('.csv', $extensions);
    }

    public function test_import_to_local_includes_schema_version(): void
    {
        $json = json_encode([
            'plans' => [
                ['plan_title' => 'Test Plan'],
            ],
        ]);

        $result = $this->service->import($json, null, ImportTarget::Local);

        $this->assertTrue($result['success']);
        $importedData = $result['imported_ids'][0]['data'];
        $this->assertArrayHasKey('schema_version', $importedData);
        $this->assertEquals('1.0.0', $importedData['schema_version']);
    }

    public function test_import_to_local_prepares_correct_structure(): void
    {
        $json = json_encode([
            'plans' => [
                [
                    'plan_title' => 'Test Plan',
                    'name' => 'Test Horse',
                    'career_stage' => 'junior',
                    'status' => 'ongoing',
                    'turn_before' => 10,
                    'total_available_skill_points' => 500,
                ],
            ],
        ]);

        $result = $this->service->import($json, null, ImportTarget::Local);

        $this->assertTrue($result['success']);
        $importedData = $result['imported_ids'][0]['data'];

        $this->assertArrayHasKey('career_run', $importedData);
        $this->assertArrayHasKey('stat_progress', $importedData);
        $this->assertArrayHasKey('skills', $importedData);
        $this->assertArrayHasKey('goals', $importedData);
        $this->assertArrayHasKey('race_predictions', $importedData);
    }
}
