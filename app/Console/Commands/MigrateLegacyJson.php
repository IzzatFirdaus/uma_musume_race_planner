<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\RunStatus;
use App\Enums\SkillStatus;
use App\Enums\UmaClass;
use App\Models\Plan;
use App\Models\Skill;
use App\Models\Turn;
use App\Models\Umamusume;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Migrate legacy JSON data from uma-run-tracker format.
 */
class MigrateLegacyJson extends Command
{
    protected $signature = 'migrate:legacy-json
                            {file : Path to the JSON file to import}
                            {--target=local : Import target (local or account)}
                            {--dry-run : Validate without importing}
                            {--skip-duplicates : Skip duplicate detection}
                            {--create-characters : Auto-create missing characters}
                            {--user= : User ID for account imports}';

    protected $description = 'Import legacy data from uma-run-tracker JSON format';

    private array $errors = [];
    private array $stats = [
        'characters' => 0,
        'runs' => 0,
        'stats' => 0,
        'skills' => 0,
        'goals' => 0,
    ];

    public function handle(): int
    {
        $filePath = $this->argument('file');
        $isDryRun = $this->option('dry-run');
        $target = $this->option('target');

        if (! File::exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return Command::FAILURE;
        }

        $this->info('Reading JSON file...');
        $content = File::get($filePath);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Invalid JSON: ' . json_last_error_msg());
            return Command::FAILURE;
        }

        // Validate schema version
        $schemaVersion = $data['schema_version'] ?? '1.0.0';
        $this->info("Schema version: {$schemaVersion}");

        // Validate data structure
        if (! $this->validateStructure($data)) {
            $this->displayErrors();
            return Command::FAILURE;
        }

        $this->info('Validation passed.');

        if ($isDryRun) {
            $this->displayDryRunSummary($data);
            return Command::SUCCESS;
        }

        // Perform import
        $this->info('Starting import...');

        try {
            DB::beginTransaction();

            $this->importData($data, $target);

            DB::commit();

            $this->displayImportSummary();
            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Import failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function validateStructure(array $data): bool
    {
        if (! isset($data['runs']) || ! is_array($data['runs'])) {
            $this->errors[] = 'Missing or invalid "runs" array';
            return false;
        }

        foreach ($data['runs'] as $index => $run) {
            $this->validateRun($run, $index);
        }

        return empty($this->errors);
    }

    private function validateRun(array $run, int $index): void
    {
        $row = $index + 1;

        // Required fields
        if (empty($run['character']['name'] ?? null)) {
            $this->errors[] = "Row {$row}: Missing character name";
        }

        // Validate status
        $status = $run['status'] ?? '';
        if (! in_array($status, ['ongoing', 'finished', 'failed'])) {
            $this->errors[] = "Row {$row}: Invalid status '{$status}'";
        }

        // Validate stats
        foreach ($run['stats'] ?? [] as $statIndex => $stat) {
            if (($stat['turn'] ?? 0) < 1) {
                $this->errors[] = "Row {$row}, Stat {$statIndex}: Invalid turn number";
            }
        }

        // Validate skills
        foreach ($run['skills'] ?? [] as $skillIndex => $skill) {
            $skillStatus = $skill['status'] ?? '';
            if (! in_array($skillStatus, ['acquired', 'skipped', 'suggested'])) {
                $this->errors[] = "Row {$row}, Skill {$skillIndex}: Invalid status '{$skillStatus}'";
            }

            if ($skillStatus === 'acquired' && empty($skill['turn_acquired'])) {
                $this->errors[] = "Row {$row}, Skill {$skillIndex}: turn_acquired required for acquired skills";
            }
        }
    }

    private function importData(array $data, string $target): void
    {
        $userId = $target === 'account' ? ($this->option('user') ?? auth()->id()) : null;

        foreach ($data['runs'] as $runData) {
            $this->importRun($runData, $target, $userId);
        }
    }

    private function importRun(array $runData, string $target, ?int $userId): void
    {
        // Find or create character
        $character = $this->findOrCreateCharacter($runData['character']);

        // Check for duplicates
        if (! $this->option('skip-duplicates')) {
            $duplicate = $this->checkDuplicate($runData, (int) $character->id);
            if ($duplicate) {
                $this->warn("Skipping duplicate run for {$character->name}");
                return;
            }
        }

        // Create plan
        $plan = Plan::create([
            'umamusume_id' => (int) $character->id,
            'user_id' => $userId,
            'storage_mode' => $target,
            'local_uuid' => $target === 'local' ? Str::uuid()->toString() : null,
            'scenario' => $runData['scenario'] ?? 'URA',
            'year' => $runData['year'] ?? 'junior',
            'status' => $this->mapStatus($runData['status'] ?? 'ongoing'),
            'uma_class' => $this->mapClass($runData['class'] ?? 'debut'),
            'current_turn' => $runData['current_turn'] ?? 1,
            'total_sp_available' => $runData['sp_available'] ?? $runData['total_sp_available'] ?? 0,
            'stamina_percentage' => $runData['stamina_pct'] ?? $runData['stamina_percentage'] ?? 100,
            'mood' => $runData['mood'] ?? null,
            'conditions' => $runData['conditions'] ?? null,
            'notes' => $runData['notes'] ?? null,
            'created_at' => $runData['created_at'] ?? now(),
        ]);

        $this->stats['runs']++;

        // Import stats
        foreach ($runData['stats'] ?? [] as $statData) {
            $this->importStat($plan, $statData);
        }

        // Import skills
        foreach ($runData['skills'] ?? [] as $skillData) {
            $this->importSkill($plan, $skillData);
        }

        // Import goals
        foreach ($runData['goals'] ?? [] as $goalData) {
            $this->importGoal($plan, $goalData);
        }
    }

    private function findOrCreateCharacter(array $characterData): Umamusume
    {
        $character = Umamusume::where('name', $characterData['name'])->first();

        if (! $character && $this->option('create-characters')) {
            $character = Umamusume::create([
                'name' => $characterData['name'],
                'name_jp' => $characterData['name_jp'] ?? null,
                'aptitude_style' => $characterData['aptitude_style'] ?? [],
                'aptitude_distance' => $characterData['aptitude_distance'] ?? [],
                'aptitude_track' => $characterData['aptitude_track'] ?? [],
                'growth_speed' => $characterData['growth_speed'] ?? 0,
                'growth_stamina' => $characterData['growth_stamina'] ?? 0,
                'growth_power' => $characterData['growth_power'] ?? 0,
                'growth_guts' => $characterData['growth_guts'] ?? 0,
                'growth_wit' => $characterData['growth_wit'] ?? 0,
            ]);
            $this->stats['characters']++;
        }

        if (! $character) {
            throw new \RuntimeException("Character not found: {$characterData['name']}. Use --create-characters to auto-create.");
        }

        return $character;
    }

    private function checkDuplicate(array $runData, int $characterId): bool
    {
        return Plan::where('umamusume_id', $characterId)
            ->whereDate('created_at', $runData['created_at'] ?? now())
            ->exists();
    }

    private function importStat(Plan $plan, array $statData): void
    {
        Turn::create([
            'plan_id' => $plan->id,
            'turn_number' => $statData['turn'] ?? $statData['turn_number'],
            'speed' => $statData['speed'] ?? 0,
            'stamina' => $statData['stamina'] ?? 0,
            'power' => $statData['power'] ?? 0,
            'guts' => $statData['guts'] ?? 0,
            'wit' => $statData['wit'] ?? 0,
        ]);
        $this->stats['stats']++;
    }

    private function importSkill(Plan $plan, array $skillData): void
    {
        // Find skill reference by name
        $skillRef = Skill::where('name', $skillData['name'])
            ->orWhere('name_jp', $skillData['name'])
            ->first();

        if (! $skillRef) {
            $this->warn("Skill not found: {$skillData['name']}");
            return;
        }

        $plan->skills()->attach($skillRef->id, [
            'status' => $this->mapSkillStatus($skillData['status']),
            'turn_acquired' => $skillData['turn_acquired'] ?? null,
            'notes' => $skillData['notes'] ?? null,
        ]);
        $this->stats['skills']++;
    }

    private function importGoal(Plan $plan, array $goalData): void
    {
        $plan->goals()->create([
            'description' => $goalData['description'],
            'target_value' => $goalData['target_value'] ?? null,
            'achieved' => $goalData['achieved'] ?? false,
            'turn_achieved' => $goalData['turn_achieved'] ?? null,
        ]);
        $this->stats['goals']++;
    }

    private function mapStatus(string $status): RunStatus
    {
        return match (strtolower($status)) {
            'ongoing', 'active', 'in_progress' => RunStatus::Ongoing,
            'finished', 'complete', 'completed' => RunStatus::Finished,
            'failed', 'abandoned' => RunStatus::Failed,
            default => RunStatus::Ongoing,
        };
    }

    private function mapClass(string $class): UmaClass
    {
        return match (strtolower($class)) {
            'debut' => UmaClass::Debut,
            'maiden', 'pre_op' => UmaClass::Maiden,
            'beginner' => UmaClass::Beginner,
            'bronze', 'open' => UmaClass::Bronze,
            'silver', 'grade3', 'g3' => UmaClass::Silver,
            'gold', 'grade2', 'g2' => UmaClass::Gold,
            'platinum', 'grade1', 'g1' => UmaClass::Platinum,
            'star' => UmaClass::Star,
            'legend' => UmaClass::Legend,
            default => UmaClass::Debut,
        };
    }

    private function mapSkillStatus(string $status): SkillStatus
    {
        return match (strtolower($status)) {
            'acquired', 'learned' => SkillStatus::Acquired,
            'skipped', 'passed' => SkillStatus::Skipped,
            'suggested', 'planned' => SkillStatus::Suggested,
            default => SkillStatus::Suggested,
        };
    }

    private function displayErrors(): void
    {
        $this->error('Validation failed with ' . count($this->errors) . ' errors:');
        foreach ($this->errors as $error) {
            $this->line("  - {$error}");
        }
    }

    private function displayDryRunSummary(array $data): void
    {
        $runs = $data['runs'] ?? [];
        $totalStats = array_sum(array_map(fn($r) => count($r['stats'] ?? []), $runs));
        $totalSkills = array_sum(array_map(fn($r) => count($r['skills'] ?? []), $runs));
        $totalGoals = array_sum(array_map(fn($r) => count($r['goals'] ?? []), $runs));

        $this->info('');
        $this->info('Dry run summary:');
        $this->line("  Career runs: " . count($runs));
        $this->line("  Stat entries: {$totalStats}");
        $this->line("  Skills: {$totalSkills}");
        $this->line("  Goals: {$totalGoals}");
        $this->info('');
        $this->info('Run without --dry-run to import.');
    }

    private function displayImportSummary(): void
    {
        $this->info('');
        $this->info('Import completed successfully!');
        $this->line("  Characters created: {$this->stats['characters']}");
        $this->line("  Career runs: {$this->stats['runs']}");
        $this->line("  Stat entries: {$this->stats['stats']}");
        $this->line("  Skills: {$this->stats['skills']}");
        $this->line("  Goals: {$this->stats['goals']}");
    }
}
