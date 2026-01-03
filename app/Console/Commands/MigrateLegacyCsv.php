<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\RunStatus;
use App\Enums\UmaClass;
use App\Models\Plan;
use App\Models\Turn;
use App\Models\Umamusume;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Migrate legacy CSV data from various tracker formats.
 */
class MigrateLegacyCsv extends Command
{
    protected $signature = 'migrate:legacy-csv
                            {file : Path to the CSV file to import}
                            {--target=local : Import target (local or account)}
                            {--dry-run : Validate without importing}
                            {--delimiter=, : CSV delimiter}
                            {--has-header : First row is header (default: true)}
                            {--encoding=UTF-8 : File encoding}
                            {--create-characters : Auto-create missing characters}
                            {--user= : User ID for account imports}';

    protected $description = 'Import legacy data from CSV format';

    private array $errors = [];
    private array $stats = [
        'characters' => 0,
        'runs' => 0,
        'stats' => 0,
    ];

    private array $headers = [];

    public function handle(): int
    {
        $filePath = $this->argument('file');
        $isDryRun = $this->option('dry-run');
        $delimiter = $this->option('delimiter');

        if (! File::exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return Command::FAILURE;
        }

        $this->info('Reading CSV file...');

        $rows = $this->parseCsv($filePath, $delimiter);

        if (empty($rows)) {
            $this->error('No data found in CSV file');
            return Command::FAILURE;
        }

        // Validate data
        if (! $this->validateRows($rows)) {
            $this->displayErrors();
            return Command::FAILURE;
        }

        $this->info('Validation passed.');

        if ($isDryRun) {
            $this->displayDryRunSummary($rows);
            return Command::SUCCESS;
        }

        // Perform import
        $this->info('Starting import...');

        try {
            DB::beginTransaction();

            $this->importRows($rows);

            DB::commit();

            $this->displayImportSummary();
            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Import failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function parseCsv(string $filePath, string $delimiter): array
    {
        $rows = [];
        $handle = fopen($filePath, 'r');

        if ($handle === false) {
            return [];
        }

        $lineNumber = 0;
        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $lineNumber++;

            // First row is header
            if ($lineNumber === 1 && $this->option('has-header') !== false) {
                $this->headers = array_map('strtolower', array_map('trim', $data));
                continue;
            }

            // Map to associative array if headers exist
            if (! empty($this->headers)) {
                $row = [];
                foreach ($this->headers as $index => $header) {
                    $row[$header] = $data[$index] ?? null;
                }
                $rows[] = $row;
            } else {
                $rows[] = $data;
            }
        }

        fclose($handle);
        return $rows;
    }

    private function validateRows(array $rows): bool
    {
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // Account for header row

            // Check required fields
            $characterName = $row['character_name'] ?? $row['name'] ?? $row[0] ?? null;
            if (empty($characterName)) {
                $this->errors[] = "Row {$rowNum}: Missing character name";
            }

            // Validate status if present
            $status = $row['status'] ?? null;
            if ($status && ! in_array(strtolower($status), ['ongoing', 'finished', 'failed'])) {
                $this->errors[] = "Row {$rowNum}: Invalid status '{$status}'";
            }

            // Validate numeric fields
            $numericFields = ['turn', 'speed', 'stamina', 'power', 'guts', 'wit', 'sp_available'];
            foreach ($numericFields as $field) {
                $value = $row[$field] ?? null;
                if ($value !== null && $value !== '' && ! is_numeric($value)) {
                    $this->errors[] = "Row {$rowNum}: Invalid numeric value for {$field}";
                }
            }
        }

        return empty($this->errors);
    }

    private function importRows(array $rows): void
    {
        $target = $this->option('target');
        $userId = $target === 'account' ? ($this->option('user') ?? auth()->id()) : null;

        // Group rows by character for run creation
        $grouped = $this->groupByCharacter($rows);

        foreach ($grouped as $characterName => $characterRows) {
            $this->importCharacterRuns($characterName, $characterRows, $target, $userId);
        }
    }

    private function groupByCharacter(array $rows): array
    {
        $grouped = [];

        foreach ($rows as $row) {
            $characterName = $row['character_name'] ?? $row['name'] ?? $row[0] ?? 'Unknown';
            $grouped[$characterName][] = $row;
        }

        return $grouped;
    }

    private function importCharacterRuns(string $characterName, array $rows, string $target, ?int $userId): void
    {
        // Find or create character
        $character = Umamusume::where('name', $characterName)->first();

        if (! $character && $this->option('create-characters')) {
            $character = Umamusume::create([
                'name' => $characterName,
            ]);
            $this->stats['characters']++;
        }

        if (! $character) {
            $this->warn("Character not found: {$characterName}. Use --create-characters to auto-create.");
            return;
        }

        // Determine if this is stat-per-row or run-per-row format
        $hasTurnColumn = isset($rows[0]['turn']) || isset($rows[0]['turn_number']);

        if ($hasTurnColumn) {
            // Stat-per-row format: create one run with multiple stats
            $this->importAsStatRows($character, $rows, $target, $userId);
        } else {
            // Run-per-row format: each row is a separate run
            foreach ($rows as $row) {
                $this->importAsRun($character, $row, $target, $userId);
            }
        }
    }

    private function importAsStatRows(Umamusume $character, array $rows, string $target, ?int $userId): void
    {
        // Get run metadata from first row
        $firstRow = $rows[0];
        $characterId = (int) $character->id;

        $plan = Plan::create([
            'umamusume_id' => $characterId,
            'user_id' => $userId,
            'storage_mode' => $target,
            'local_uuid' => $target === 'local' ? Str::uuid()->toString() : null,
            'scenario' => $firstRow['scenario'] ?? 'URA',
            'year' => $firstRow['year'] ?? 'junior',
            'status' => $this->mapStatus($firstRow['status'] ?? 'ongoing'),
            'uma_class' => $this->mapClass($firstRow['class'] ?? $firstRow['uma_class'] ?? 'debut'),
            'current_turn' => count($rows),
            'total_sp_available' => (int) ($firstRow['sp_available'] ?? 0),
            'stamina_percentage' => (int) ($firstRow['stamina_percentage'] ?? 100),
        ]);

        $this->stats['runs']++;

        // Import each row as a stat entry
        foreach ($rows as $row) {
            Turn::create([
                'plan_id' => $plan->id,
                'turn_number' => (int) ($row['turn'] ?? $row['turn_number'] ?? 1),
                'speed' => (int) ($row['speed'] ?? 0),
                'stamina' => (int) ($row['stamina'] ?? 0),
                'power' => (int) ($row['power'] ?? 0),
                'guts' => (int) ($row['guts'] ?? 0),
                'wit' => (int) ($row['wit'] ?? 0),
            ]);
            $this->stats['stats']++;
        }
    }

    private function importAsRun(Umamusume $character, array $row, string $target, ?int $userId): void
    {
        $plan = Plan::create([
            'umamusume_id' => $character->id,
            'user_id' => $userId,
            'storage_mode' => $target,
            'local_uuid' => $target === 'local' ? Str::uuid()->toString() : null,
            'scenario' => $row['scenario'] ?? 'URA',
            'year' => $row['year'] ?? 'junior',
            'status' => $this->mapStatus($row['status'] ?? 'ongoing'),
            'uma_class' => $this->mapClass($row['class'] ?? $row['uma_class'] ?? 'debut'),
            'current_turn' => (int) ($row['current_turn'] ?? 1),
            'total_sp_available' => (int) ($row['sp_available'] ?? 0),
            'stamina_percentage' => (int) ($row['stamina_percentage'] ?? 100),
            'notes' => $row['notes'] ?? null,
        ]);

        $this->stats['runs']++;

        // If stats are included in the row, create a single stat entry
        if (isset($row['speed']) || isset($row['stamina'])) {
            Turn::create([
                'plan_id' => $plan->id,
                'turn_number' => (int) ($row['current_turn'] ?? 1),
                'speed' => (int) ($row['speed'] ?? 0),
                'stamina' => (int) ($row['stamina'] ?? 0),
                'power' => (int) ($row['power'] ?? 0),
                'guts' => (int) ($row['guts'] ?? 0),
                'wit' => (int) ($row['wit'] ?? 0),
            ]);
            $this->stats['stats']++;
        }
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

    private function displayErrors(): void
    {
        $this->error('Validation failed with ' . count($this->errors) . ' errors:');
        foreach ($this->errors as $error) {
            $this->line("  - {$error}");
        }
    }

    private function displayDryRunSummary(array $rows): void
    {
        $this->info('');
        $this->info('Dry run summary:');
        $this->line("  Total rows: " . count($rows));
        $this->line("  Unique characters: " . count($this->groupByCharacter($rows)));
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
    }
}
