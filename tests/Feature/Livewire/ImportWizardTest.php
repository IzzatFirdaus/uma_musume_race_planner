<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Import\ImportWizard;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Feature tests for ImportWizard Livewire component.
 * Implements REQ-IMP-1: Import Wizard testing.
 */
class ImportWizardTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => \Database\Seeders\LookupSeeder::class]);

        $this->user = User::factory()->create();
    }

    #[Test]
    public function it_starts_on_step_1(): void
    {
        Livewire::test(ImportWizard::class)
            ->assertSet('currentStep', 1)
            ->assertSet('detectedFormat', null)
            ->assertSet('file', null);
    }

    #[Test]
    public function it_opens_wizard_on_event(): void
    {
        Livewire::test(ImportWizard::class)
            ->dispatch('open-import-wizard')
            ->assertSet('currentStep', 1)
            ->assertSet('file', null);
    }

    #[Test]
    public function it_validates_file_upload(): void
    {
        Livewire::test(ImportWizard::class)
            ->set('file', null)
            ->call('uploadAndDetect')
            ->assertHasErrors(['file' => 'required']);
    }

    #[Test]
    public function it_detects_json_format(): void
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

        $file = UploadedFile::fake()->createWithContent('test.json', $json);

        Livewire::test(ImportWizard::class)
            ->set('file', $file)
            ->call('uploadAndDetect')
            ->assertSet('detectedFormat', 'json')
            ->assertSet('currentStep', 2);
    }

    #[Test]
    public function it_detects_csv_format(): void
    {
        $csv = "Plan Title,Character Name,Career Stage\nTest Plan,Test Horse,junior";

        $file = UploadedFile::fake()->createWithContent('test.csv', $csv);

        Livewire::test(ImportWizard::class)
            ->set('file', $file)
            ->call('uploadAndDetect')
            ->assertSet('detectedFormat', 'csv')
            ->assertSet('currentStep', 2);
    }

    #[Test]
    public function it_shows_error_for_invalid_file(): void
    {
        $invalidContent = 'invalid content that is not json or csv';
        $file = UploadedFile::fake()->createWithContent('test.txt', $invalidContent);

        Livewire::test(ImportWizard::class)
            ->set('file', $file)
            ->call('uploadAndDetect')
            ->assertSet('currentStep', 1) // Should stay on step 1
            ->assertDispatched('toast', type: 'error');
    }

    #[Test]
    public function it_populates_preview_data_after_detection(): void
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

        $file = UploadedFile::fake()->createWithContent('test.json', $json);

        Livewire::test(ImportWizard::class)
            ->set('file', $file)
            ->call('uploadAndDetect')
            ->assertNotEmpty('previewData')
            ->assertArrayHasKey('plans', Livewire::test(ImportWizard::class)->get('previewData'));
    }

    #[Test]
    public function it_validates_import_data(): void
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

        $file = UploadedFile::fake()->createWithContent('test.json', $json);

        Livewire::test(ImportWizard::class)
            ->set('file', $file)
            ->call('uploadAndDetect')
            ->call('validateImport')
            ->assertNotEmpty('validationSummary');
    }

    #[Test]
    public function it_detects_duplicates_for_account_import(): void
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

        $file = UploadedFile::fake()->createWithContent('test.json', $json);

        Livewire::test(ImportWizard::class)
            ->actingAs($this->user)
            ->set('file', $file)
            ->call('uploadAndDetect')
            ->set('target', 'account')
            ->call('validateImport')
            ->assertNotEmpty('duplicates')
            ->assertSet('currentStep', 3); // Should go to conflict resolution
    }

    #[Test]
    public function it_skips_conflict_step_for_unique_plans(): void
    {
        $json = json_encode([
            'plans' => [
                [
                    'plan_title' => 'Unique Plan',
                    'name' => 'Unique Horse',
                    'career_stage' => 'junior',
                    'status' => 'ongoing',
                ],
            ],
        ]);

        $file = UploadedFile::fake()->createWithContent('test.json', $json);

        Livewire::test(ImportWizard::class)
            ->actingAs($this->user)
            ->set('file', $file)
            ->call('uploadAndDetect')
            ->set('target', 'account')
            ->call('validateImport')
            ->assertEmpty('duplicates')
            ->assertSet('currentStep', 4); // Should skip to confirm step
    }

    #[Test]
    public function it_resolves_conflicts_and_proceeds(): void
    {
        Livewire::test(ImportWizard::class)
            ->set('currentStep', 3)
            ->set('skipDuplicates', true)
            ->call('resolveConflicts')
            ->assertSet('currentStep', 4);
    }

    #[Test]
    public function it_executes_import_to_local(): void
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

        $file = UploadedFile::fake()->createWithContent('test.json', $json);

        Livewire::test(ImportWizard::class)
            ->set('file', $file)
            ->call('uploadAndDetect')
            ->call('validateImport')
            ->call('executeImport')
            ->assertSet('importSuccess', true)
            ->assertSet('currentStep', 5)
            ->assertSet('importResults.created', 1);
    }

    #[Test]
    public function it_executes_import_to_account(): void
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

        $file = UploadedFile::fake()->createWithContent('test.json', $json);

        Livewire::test(ImportWizard::class)
            ->actingAs($this->user)
            ->set('file', $file)
            ->call('uploadAndDetect')
            ->set('target', 'account')
            ->call('validateImport')
            ->call('executeImport')
            ->assertSet('importSuccess', true)
            ->assertSet('currentStep', 5);

        // Verify plan was created in database
        $plan = Plan::where('user_id', $this->user->id)->first();
        $this->assertNotNull($plan);
        $this->assertEquals('Test Plan', $plan->plan_title);
    }

    #[Test]
    public function it_fails_account_import_without_authentication(): void
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

        $file = UploadedFile::fake()->createWithContent('test.json', $json);

        Livewire::test(ImportWizard::class)
            ->set('file', $file)
            ->call('uploadAndDetect')
            ->set('target', 'account')
            ->call('validateImport')
            ->call('executeImport')
            ->assertSet('importSuccess', false)
            ->assertNotEmpty('importResults.errors');
    }

    #[Test]
    public function it_navigates_to_previous_step(): void
    {
        Livewire::test(ImportWizard::class)
            ->set('currentStep', 3)
            ->call('previousStep')
            ->assertSet('currentStep', 2);
    }

    #[Test]
    public function it_does_not_go_below_step_1(): void
    {
        Livewire::test(ImportWizard::class)
            ->set('currentStep', 1)
            ->call('previousStep')
            ->assertSet('currentStep', 1);
    }

    #[Test]
    public function it_resets_wizard_state(): void
    {
        Livewire::test(ImportWizard::class)
            ->set('currentStep', 5)
            ->set('detectedFormat', 'json')
            ->set('file', UploadedFile::fake()->create('test.json'))
            ->call('resetWizard')
            ->assertSet('currentStep', 1)
            ->assertSet('detectedFormat', null)
            ->assertSet('file', null);
    }

    #[Test]
    public function it_closes_wizard(): void
    {
        Livewire::test(ImportWizard::class)
            ->set('currentStep', 5)
            ->call('closeWizard')
            ->assertDispatched('close-modal')
            ->assertSet('currentStep', 1);
    }

    #[Test]
    public function it_returns_step_labels(): void
    {
        $component = Livewire::test(ImportWizard::class);

        $this->assertEquals('Upload File', $component->call('getStepLabel', 1));
        $this->assertEquals('Preview', $component->call('getStepLabel', 2));
        $this->assertEquals('Resolve Conflicts', $component->call('getStepLabel', 3));
        $this->assertEquals('Confirm Import', $component->call('getStepLabel', 4));
        $this->assertEquals('Results', $component->call('getStepLabel', 5));
    }

    #[Test]
    public function it_returns_step_descriptions(): void
    {
        $component = Livewire::test(ImportWizard::class);

        $description = $component->call('getStepDescription', 1);
        $this->assertStringContainsString('Select a file', $description);
    }

    #[Test]
    public function it_checks_step_completion(): void
    {
        Livewire::test(ImportWizard::class)
            ->set('detectedFormat', 'json')
            ->assertMethod('isStepCompleted', [1], true)
            ->assertMethod('isStepCompleted', [2], false);
    }

    #[Test]
    public function it_returns_supported_formats(): void
    {
        $component = Livewire::test(ImportWizard::class);

        $formats = $component->call('getSupportedFormats');
        $this->assertIsArray($formats);
        $this->assertNotEmpty($formats);
    }

    #[Test]
    public function it_returns_supported_extensions(): void
    {
        $component = Livewire::test(ImportWizard::class);

        $extensions = $component->call('getSupportedExtensions');
        $this->assertIsArray($extensions);
        $this->assertContains('.json', $extensions);
        $this->assertContains('.csv', $extensions);
    }

    #[Test]
    public function it_dispatches_refresh_plans_after_account_import(): void
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

        $file = UploadedFile::fake()->createWithContent('test.json', $json);

        Livewire::test(ImportWizard::class)
            ->actingAs($this->user)
            ->set('file', $file)
            ->call('uploadAndDetect')
            ->set('target', 'account')
            ->call('validateImport')
            ->call('executeImport')
            ->assertDispatched('refresh-plans');
    }

    #[Test]
    public function it_dispatches_refresh_local_storage_after_local_import(): void
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

        $file = UploadedFile::fake()->createWithContent('test.json', $json);

        Livewire::test(ImportWizard::class)
            ->set('file', $file)
            ->call('uploadAndDetect')
            ->set('target', 'local')
            ->call('validateImport')
            ->call('executeImport')
            ->assertDispatched('refresh-local-storage');
    }

    #[Test]
    public function it_handles_skip_duplicates_option(): void
    {
        Livewire::test(ImportWizard::class)
            ->set('skipDuplicates', true)
            ->call('resolveConflicts')
            ->assertSet('overwriteDuplicates', false);
    }

    #[Test]
    public function it_disables_account_target_without_auth(): void
    {
        Livewire::test(ImportWizard::class)
            ->set('target', 'account')
            ->assertSet('target', 'account');
    }
}
