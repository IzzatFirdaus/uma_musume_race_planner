<?php

declare(strict_types=1);

namespace App\Livewire\Export;

use App\Models\Plan;
use App\Services\ExportService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Export Modal Component
 *
 * Modal for exporting career runs in various formats with preview.
 * Implements Requirements 15.1, 15.2, 15.3, 22.1, 22.2, 22.3, 24.1, 24.2, 24.3, 24.4, 68.9.
 */
class ExportModal extends Component
{
    public bool $show = false;

    public ?int $planId = null;

    public string $format = 'json'; // json, text, markdown

    public bool $showPreview = false;

    public string $previewContent = '';

    public string $exportSize = '';

    protected ExportService $exportService;

    public function boot(ExportService $exportService): void
    {
        $this->exportService = $exportService;
    }

    #[On('open-export-modal')]
    public function openModal(?int $planId = null): void
    {
        $this->planId = $planId;
        $this->format = 'json';
        $this->showPreview = false;
        $this->previewContent = '';
        $this->exportSize = '';
        $this->show = true;
    }

    public function closeModal(): void
    {
        $this->show = false;
        $this->planId = null;
        $this->showPreview = false;
        $this->previewContent = '';
        $this->exportSize = '';
    }

    #[Computed]
    public function plan(): ?Plan
    {
        return $this->planId ? Plan::with([
            'attributes',
            'skills.skillReference',
            'goals',
            'turns',
            'racePredictions',
            'careerSnapshots',
            'mood',
            'condition',
            'strategy',
            'terrainGrades',
            'distanceGrades',
            'styleGrades',
        ])->find($this->planId) : null;
    }

    #[Computed]
    public function formats(): array
    {
        return [
            'json' => [
                'label' => 'JSON',
                'icon' => 'bi-filetype-json',
                'description' => 'For import/backup purposes with schema version',
            ],
            'text' => [
                'label' => 'Plain Text',
                'icon' => 'bi-file-text',
                'description' => 'Simple text format for sharing',
            ],
            'markdown' => [
                'label' => 'Markdown',
                'icon' => 'bi-markdown',
                'description' => 'Formatted text for forums and documentation',
            ],
        ];
    }

    /**
     * Generate preview of export.
     * Implements Requirements 22.1, 22.2, 22.3.
     */
    public function generatePreview(): void
    {
        if (! $this->plan) {
            return;
        }

        $this->previewContent = match ($this->format) {
            'markdown' => $this->exportService->toMarkdown($this->plan),
            'text' => $this->generateTextExport(),
            default => $this->exportService->toJson($this->plan),
        };

        $this->exportSize = $this->formatBytes(strlen($this->previewContent));
        $this->showPreview = true;
    }

    /**
     * Generate plain text export format.
     * Implements Requirements 24.1, 24.2.
     */
    protected function generateTextExport(): string
    {
        $plan = $this->plan;
        $lines = [];

        // Header
        $lines[] = str_repeat('=', 60);
        $lines[] = strtoupper($plan->plan_title ?? $plan->name ?? 'Untitled Plan');
        $lines[] = str_repeat('=', 60);
        $lines[] = '';

        // Basic Info
        $lines[] = 'BASIC INFORMATION';
        $lines[] = str_repeat('-', 40);
        $lines[] = sprintf('%-20s %s', 'Character:', $plan->name ?? 'N/A');
        $lines[] = sprintf('%-20s %s', 'Career Stage:', $plan->career_stage ?? 'N/A');
        $lines[] = sprintf('%-20s %s', 'Class:', $plan->class ?? 'N/A');
        $lines[] = sprintf('%-20s %s', 'Status:', $plan->status ?? 'N/A');
        $lines[] = sprintf('%-20s %s', 'Current Turn:', $plan->turn_before ?? 'N/A');
        $lines[] = sprintf('%-20s %s', 'Race:', $plan->race_name ?? 'N/A');
        $lines[] = sprintf('%-20s %s', 'SP Available:', $plan->total_available_skill_points ?? 'N/A');
        $lines[] = sprintf('%-20s %s%%', 'Stamina:', $plan->stamina_percentage ?? $plan->energy ?? 'N/A');
        $lines[] = sprintf('%-20s %s', 'Mood:', $plan->mood?->label ?? 'N/A');
        $lines[] = sprintf('%-20s %s', 'Condition:', $plan->condition?->label ?? 'N/A');
        $lines[] = sprintf('%-20s %s', 'Strategy:', $plan->strategy?->label ?? 'N/A');
        $lines[] = '';

        // Growth Rates
        $lines[] = 'GROWTH RATES';
        $lines[] = str_repeat('-', 40);
        $lines[] = sprintf('%-12s %-12s %-12s %-12s %-12s', 'Speed', 'Stamina', 'Power', 'Guts', 'Wit');
        $lines[] = sprintf(
            '%-12s %-12s %-12s %-12s %-12s',
            ($plan->growth_rate_speed ?? 0).'%',
            ($plan->growth_rate_stamina ?? 0).'%',
            ($plan->growth_rate_power ?? 0).'%',
            ($plan->growth_rate_guts ?? 0).'%',
            ($plan->growth_rate_wit ?? 0).'%'
        );
        $lines[] = '';

        // Attributes
        if ($plan->attributes->count() > 0) {
            $lines[] = 'ATTRIBUTES';
            $lines[] = str_repeat('-', 40);
            foreach ($plan->attributes as $attr) {
                $lines[] = sprintf('%-15s %5d  [%s]', $attr->attribute_name, $attr->value ?? 0, $attr->grade ?? '-');
            }
            $lines[] = '';
        }

        // Skills
        if ($plan->skills->count() > 0) {
            $lines[] = 'SKILLS';
            $lines[] = str_repeat('-', 40);
            $lines[] = sprintf('%-30s %-10s %-8s %-6s', 'Name', 'Status', 'Turn', 'SP');
            $lines[] = str_repeat('-', 60);
            foreach ($plan->skills as $skill) {
                $skillName = $skill->skillReference?->skill_name ?? 'Unknown';
                $lines[] = sprintf(
                    '%-30s %-10s %-8s %-6s',
                    substr($skillName, 0, 30),
                    $skill->status?->value ?? 'N/A',
                    $skill->turn_acquired ?? '-',
                    $skill->sp_cost ?? '-'
                );
            }
            $lines[] = '';
        }

        // Goals
        if ($plan->goals->count() > 0) {
            $lines[] = 'GOALS';
            $lines[] = str_repeat('-', 40);
            foreach ($plan->goals as $goal) {
                $status = $goal->result ? '[X]' : '[ ]';
                $lines[] = "{$status} {$goal->goal}";
            }
            $lines[] = '';
        }

        // Race Predictions
        if ($plan->racePredictions->count() > 0) {
            $lines[] = 'RACE PREDICTIONS';
            $lines[] = str_repeat('-', 40);
            foreach ($plan->racePredictions as $race) {
                $predicted = $race->predicted_pos ? "Predicted: #{$race->predicted_pos}" : '';
                $actual = $race->actual_pos ? "Actual: #{$race->actual_pos}" : '';
                $lines[] = "- {$race->race_name} ({$race->distance_category}/{$race->track_type})";
                if ($predicted || $actual) {
                    $lines[] = "  {$predicted} {$actual}";
                }
            }
            $lines[] = '';
        }

        // Recent Stat Progress (last 10 turns)
        if ($plan->turns->count() > 0) {
            $lines[] = 'RECENT STAT PROGRESS';
            $lines[] = str_repeat('-', 40);
            $lines[] = sprintf('%-6s %-8s %-8s %-8s %-8s %-8s', 'Turn', 'Speed', 'Stamina', 'Power', 'Guts', 'Wit');
            $lines[] = str_repeat('-', 50);
            $recentTurns = $plan->turns->sortByDesc('turn_number')->take(10)->reverse();
            foreach ($recentTurns as $turn) {
                $lines[] = sprintf(
                    '%-6s %-8s %-8s %-8s %-8s %-8s',
                    $turn->turn_number,
                    $turn->speed ?? '-',
                    $turn->stamina ?? '-',
                    $turn->power ?? '-',
                    $turn->guts ?? '-',
                    $turn->wit ?? '-'
                );
            }
            $lines[] = '';
        }

        // Footer
        $lines[] = str_repeat('=', 60);
        $lines[] = 'Exported: '.now()->format('Y-m-d H:i:s');
        $lines[] = 'Schema Version: '.$this->exportService->getSchemaVersion();

        return implode("\n", $lines);
    }

    /**
     * Export the plan and trigger browser download.
     * Implements Requirements 15.1, 15.2, 15.3, 68.9.
     */
    public function export(): void
    {
        if (! $this->plan) {
            return;
        }

        // Generate full export content
        $content = match ($this->format) {
            'markdown' => $this->exportService->toMarkdown($this->plan),
            'text' => $this->generateTextExport(),
            default => $this->exportService->toJson($this->plan),
        };

        $extension = match ($this->format) {
            'markdown' => 'md',
            'text' => 'txt',
            default => 'json',
        };

        $mimeType = match ($this->format) {
            'markdown' => 'text/markdown',
            'text' => 'text/plain',
            default => 'application/json',
        };

        $planTitle = preg_replace('/[^a-zA-Z0-9_-]/', '_', $this->plan->plan_title ?? $this->plan->name ?? 'plan');
        $filename = "uma-plan-{$planTitle}-".now()->format('Y-m-d').".{$extension}";

        // Dispatch event to trigger browser download
        $this->dispatch('trigger-download', [
            'content' => $content,
            'filename' => $filename,
            'mimeType' => $mimeType,
        ]);

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => "Export completed for {$this->plan->name}",
        ]);

        $this->closeModal();
    }

    /**
     * Copy preview content to clipboard.
     * Implements Requirements 24.3, 24.4.
     */
    public function copyToClipboard(): void
    {
        if (empty($this->previewContent)) {
            $this->generatePreview();
        }

        $this->dispatch('copy-to-clipboard', content: $this->previewContent);
        $this->dispatch('toast', [
            'type' => 'success',
            'message' => 'Copied to clipboard!',
        ]);
    }

    /**
     * Format bytes to human-readable size.
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    public function render()
    {
        return view('livewire.export.export-modal');
    }
}
