<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * Export Service Class
 *
 * Handles data export to various formats (Excel, CSV, Markdown).
 * Implements FR-6.1, FR-6.2, FR-6.3: Export career runs to various formats.
 */
class ExportService
{
    /**
     * Current schema version for exports.
     */
    private const SCHEMA_VERSION = '1.0.0';

    /**
     * Export a single plan to array format.
     */
    public function planToArray(Plan $plan): array
    {
        $plan->load([
            'attributes',
            'skills.skillReference',
            'goals',
            'racePredictions',
            'turns',
            'careerSnapshots',
            'mood',
            'condition',
            'strategy',
        ]);

        return [
            'schema_version' => self::SCHEMA_VERSION,
            'exported_at' => now()->toIso8601String(),
            'plan' => [
                'id' => $plan->id,
                'title' => $plan->plan_title,
                'name' => $plan->name,
                'career_stage' => $plan->career_stage,
                'class' => $plan->class,
                'status' => $plan->status,
                'scenario' => $plan->scenario?->value,
                'storage_mode' => $plan->storage_mode?->value,
                'current_turn' => $plan->turn_before,
                'race_name' => $plan->race_name,
                'total_sp_available' => $plan->total_available_skill_points,
                'stamina_percentage' => $plan->stamina_percentage ?? $plan->energy,
                'mood' => $plan->mood?->label,
                'condition' => $plan->condition?->label,
                'strategy' => $plan->strategy?->label,
                'growth_rates' => [
                    'speed' => $plan->growth_rate_speed,
                    'stamina' => $plan->growth_rate_stamina,
                    'power' => $plan->growth_rate_power,
                    'guts' => $plan->growth_rate_guts,
                    'wit' => $plan->growth_rate_wit,
                ],
                'created_at' => $plan->created_at?->toIso8601String(),
                'updated_at' => $plan->updated_at?->toIso8601String(),
            ],
            'attributes' => $plan->attributes->map(fn($attr) => [
                'name' => $attr->attribute_name,
                'value' => $attr->value,
                'grade' => $attr->grade,
            ])->toArray(),
            'skills' => $plan->skills->map(fn($skill) => [
                'name' => $skill->skillReference?->skill_name,
                'status' => $skill->status?->value,
                'turn_acquired' => $skill->turn_acquired,
                'sp_cost' => $skill->sp_cost,
                'notes' => $skill->notes,
            ])->toArray(),
            'goals' => $plan->goals->map(fn($goal) => [
                'description' => $goal->goal,
                'result' => $goal->result,
            ])->toArray(),
            'race_predictions' => $plan->racePredictions->map(fn($race) => [
                'race_name' => $race->race_name,
                'distance_category' => $race->distance_category,
                'track_type' => $race->track_type,
                'venue' => $race->venue,
                'predicted_pos' => $race->predicted_pos,
                'actual_pos' => $race->actual_pos,
                'sort_order' => $race->sort_order,
            ])->toArray(),
            'stat_progress' => $plan->turns->map(fn($turn) => [
                'turn_number' => $turn->turn_number,
                'speed' => $turn->speed,
                'stamina' => $turn->stamina,
                'power' => $turn->power,
                'guts' => $turn->guts,
                'wit' => $turn->wit,
            ])->toArray(),
            'snapshots' => $plan->careerSnapshots->map(fn($snapshot) => [
                'turn_number' => $snapshot->turn_number,
                'race_name' => $snapshot->race_name,
                'speed' => $snapshot->speed,
                'stamina' => $snapshot->stamina,
                'power' => $snapshot->power,
                'guts' => $snapshot->guts,
                'wit' => $snapshot->wit,
                'created_at' => $snapshot->created_at?->toIso8601String(),
            ])->toArray(),
        ];
    }

    /**
     * Export multiple plans to array format.
     */
    public function plansToArray(Collection $plans): array
    {
        return [
            'schema_version' => self::SCHEMA_VERSION,
            'exported_at' => now()->toIso8601String(),
            'count' => $plans->count(),
            'plans' => $plans->map(fn(Plan $plan) => $this->planToArray($plan))->toArray(),
        ];
    }

    /**
     * Export plan to JSON string.
     */
    public function toJson(Plan $plan): string
    {
        return json_encode($this->planToArray($plan), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Export multiple plans to JSON string.
     */
    public function plansToJson(Collection $plans): string
    {
        return json_encode($this->plansToArray($plans), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Export plan to CSV format.
     * Implements FR-6.2: Export career runs to CSV (UTF-8 encoding, proper quoting).
     */
    public function toCsv(Plan $plan): string
    {
        $data = $this->planToArray($plan);
        $lines = [];

        // Add BOM for Excel UTF-8 compatibility
        $lines[] = "\xEF\xBB\xBF";

        // Header row
        $lines[] = $this->csvLine([
            'Plan Title',
            'Character Name',
            'Career Stage',
            'Class',
            'Status',
            'Current Turn',
            'Race Name',
            'SP Available',
            'Stamina %',
            'Mood',
            'Condition',
            'Strategy',
            'Created At'
        ]);

        // Plan data row
        $lines[] = $this->csvLine([
            $data['plan']['title'],
            $data['plan']['name'],
            $data['plan']['career_stage'],
            $data['plan']['class'],
            $data['plan']['status'],
            $data['plan']['current_turn'],
            $data['plan']['race_name'],
            $data['plan']['total_sp_available'],
            $data['plan']['stamina_percentage'],
            $data['plan']['mood'],
            $data['plan']['condition'],
            $data['plan']['strategy'],
            $data['plan']['created_at'],
        ]);

        // Empty line separator
        $lines[] = '';

        // Attributes section
        if (!empty($data['attributes'])) {
            $lines[] = $this->csvLine(['Attributes']);
            $lines[] = $this->csvLine(['Name', 'Value', 'Grade']);
            foreach ($data['attributes'] as $attr) {
                $lines[] = $this->csvLine([$attr['name'], $attr['value'], $attr['grade']]);
            }
            $lines[] = '';
        }

        // Skills section
        if (!empty($data['skills'])) {
            $lines[] = $this->csvLine(['Skills']);
            $lines[] = $this->csvLine(['Name', 'Status', 'Turn Acquired', 'SP Cost', 'Notes']);
            foreach ($data['skills'] as $skill) {
                $lines[] = $this->csvLine([
                    $skill['name'],
                    $skill['status'],
                    $skill['turn_acquired'],
                    $skill['sp_cost'],
                    $skill['notes'],
                ]);
            }
            $lines[] = '';
        }

        // Stat Progress section
        if (!empty($data['stat_progress'])) {
            $lines[] = $this->csvLine(['Stat Progress']);
            $lines[] = $this->csvLine(['Turn', 'Speed', 'Stamina', 'Power', 'Guts', 'Wit']);
            foreach ($data['stat_progress'] as $turn) {
                $lines[] = $this->csvLine([
                    $turn['turn_number'],
                    $turn['speed'],
                    $turn['stamina'],
                    $turn['power'],
                    $turn['guts'],
                    $turn['wit'],
                ]);
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Export plan to Markdown format.
     * Implements FR-6.3: Export career runs to plain text/markdown.
     */
    public function toMarkdown(Plan $plan): string
    {
        $data = $this->planToArray($plan);
        $md = [];

        // Title
        $md[] = "# {$data['plan']['title']}";
        $md[] = '';

        // Basic Info
        $md[] = '## Basic Information';
        $md[] = '';
        $md[] = "- **Character:** {$data['plan']['name']}";
        $md[] = "- **Career Stage:** {$data['plan']['career_stage']}";
        $md[] = "- **Class:** {$data['plan']['class']}";
        $md[] = "- **Status:** {$data['plan']['status']}";
        $md[] = "- **Current Turn:** {$data['plan']['current_turn']}";
        $md[] = "- **Race:** {$data['plan']['race_name']}";
        $md[] = "- **SP Available:** {$data['plan']['total_sp_available']}";
        $md[] = "- **Stamina:** {$data['plan']['stamina_percentage']}%";
        $md[] = "- **Mood:** {$data['plan']['mood']}";
        $md[] = "- **Condition:** {$data['plan']['condition']}";
        $md[] = "- **Strategy:** {$data['plan']['strategy']}";
        $md[] = '';

        // Growth Rates
        $md[] = '## Growth Rates';
        $md[] = '';
        $md[] = "| Stat | Rate |";
        $md[] = "|------|------|";
        foreach ($data['plan']['growth_rates'] as $stat => $rate) {
            $md[] = "| " . ucfirst($stat) . " | {$rate}% |";
        }
        $md[] = '';

        // Attributes
        if (!empty($data['attributes'])) {
            $md[] = '## Attributes';
            $md[] = '';
            $md[] = "| Attribute | Value | Grade |";
            $md[] = "|-----------|-------|-------|";
            foreach ($data['attributes'] as $attr) {
                $md[] = "| {$attr['name']} | {$attr['value']} | {$attr['grade']} |";
            }
            $md[] = '';
        }

        // Skills
        if (!empty($data['skills'])) {
            $md[] = '## Skills';
            $md[] = '';
            $md[] = "| Skill | Status | Turn | SP Cost |";
            $md[] = "|-------|--------|------|---------|";
            foreach ($data['skills'] as $skill) {
                $md[] = "| {$skill['name']} | {$skill['status']} | {$skill['turn_acquired']} | {$skill['sp_cost']} |";
            }
            $md[] = '';
        }

        // Goals
        if (!empty($data['goals'])) {
            $md[] = '## Goals';
            $md[] = '';
            foreach ($data['goals'] as $goal) {
                $status = $goal['result'] ? '✅' : '⬜';
                $md[] = "- {$status} {$goal['description']}";
            }
            $md[] = '';
        }

        // Race Predictions
        if (!empty($data['race_predictions'])) {
            $md[] = '## Race Predictions';
            $md[] = '';
            $md[] = "| Race | Distance | Track | Venue | Predicted | Actual |";
            $md[] = "|------|----------|-------|-------|-----------|--------|";
            foreach ($data['race_predictions'] as $race) {
                $md[] = "| {$race['race_name']} | {$race['distance_category']} | {$race['track_type']} | {$race['venue']} | {$race['predicted_pos']} | {$race['actual_pos']} |";
            }
            $md[] = '';
        }

        // Stat Progress (last 10 turns)
        if (!empty($data['stat_progress'])) {
            $md[] = '## Recent Stat Progress';
            $md[] = '';
            $md[] = "| Turn | Speed | Stamina | Power | Guts | Wit |";
            $md[] = "|------|-------|---------|-------|------|-----|";
            $recentTurns = array_slice($data['stat_progress'], -10);
            foreach ($recentTurns as $turn) {
                $md[] = "| {$turn['turn_number']} | {$turn['speed']} | {$turn['stamina']} | {$turn['power']} | {$turn['guts']} | {$turn['wit']} |";
            }
            $md[] = '';
        }

        // Footer
        $md[] = '---';
        $md[] = "*Exported on {$data['exported_at']} | Schema v{$data['schema_version']}*";

        return implode("\n", $md);
    }

    /**
     * Get export preview data.
     * Implements FR-13.1, FR-13.2: Export preview functionality.
     */
    public function getPreview(Plan $plan, string $format = 'json'): array
    {
        $content = match ($format) {
            'csv' => $this->toCsv($plan),
            'markdown', 'md' => $this->toMarkdown($plan),
            default => $this->toJson($plan),
        };

        return [
            'format' => $format,
            'content' => $content,
            'size' => strlen($content),
            'size_formatted' => $this->formatBytes(strlen($content)),
            'plan_title' => $plan->plan_title,
        ];
    }

    /**
     * Save export to file.
     */
    public function saveToFile(Plan $plan, string $format, string $directory = 'exports'): string
    {
        $content = match ($format) {
            'csv' => $this->toCsv($plan),
            'markdown', 'md' => $this->toMarkdown($plan),
            default => $this->toJson($plan),
        };

        $extension = match ($format) {
            'csv' => 'csv',
            'markdown', 'md' => 'md',
            default => 'json',
        };

        $filename = sprintf(
            '%s/%s_%s.%s',
            $directory,
            str_replace(' ', '_', $plan->plan_title ?? 'plan'),
            now()->format('Y-m-d_His'),
            $extension
        );

        Storage::disk('local')->put($filename, $content);

        return $filename;
    }

    /**
     * Format a CSV line with proper quoting.
     */
    private function csvLine(array $fields): string
    {
        return implode(',', array_map(function ($field) {
            $field = (string) ($field ?? '');
            // Quote if contains comma, quote, or newline
            if (str_contains($field, ',') || str_contains($field, '"') || str_contains($field, "\n")) {
                return '"' . str_replace('"', '""', $field) . '"';
            }
            return $field;
        }, $fields));
    }

    /**
     * Format bytes to human-readable size.
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get schema version.
     */
    public function getSchemaVersion(): string
    {
        return self::SCHEMA_VERSION;
    }
}
