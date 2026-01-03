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
 * Modal for exporting career runs in various formats.
 * Implements FR-6.1, FR-6.2, FR-6.3, FR-13.1, FR-13.2.
 */
class ExportModal extends Component
{
    public bool $show = false;

    public ?int $planId = null;

    public string $format = 'excel'; // excel, csv, markdown, json

    public bool $showPreview = false;

    public string $previewContent = '';

    #[On('open-export-modal')]
    public function openModal(?int $planId = null): void
    {
        $this->planId = $planId;
        $this->format = 'excel';
        $this->showPreview = false;
        $this->previewContent = '';
        $this->show = true;
    }

    public function closeModal(): void
    {
        $this->show = false;
        $this->planId = null;
        $this->showPreview = false;
        $this->previewContent = '';
    }

    #[Computed]
    public function plan(): ?Plan
    {
        return $this->planId ? Plan::with(['skills', 'goals', 'turns', 'racePredictions'])->find($this->planId) : null;
    }

    #[Computed]
    public function formats(): array
    {
        return [
            'excel' => [
                'label' => 'Excel (.xlsx)',
                'icon' => 'bi-file-earmark-excel',
                'description' => 'Best for spreadsheet analysis',
            ],
            'csv' => [
                'label' => 'CSV',
                'icon' => 'bi-filetype-csv',
                'description' => 'Universal format, UTF-8 encoded',
            ],
            'markdown' => [
                'label' => 'Markdown',
                'icon' => 'bi-markdown',
                'description' => 'Human-readable text format',
            ],
            'json' => [
                'label' => 'JSON',
                'icon' => 'bi-filetype-json',
                'description' => 'For import/backup purposes',
            ],
        ];
    }

    /**
     * Generate preview of export.
     * Implements FR-13.1, FR-13.2.
     */
    public function generatePreview(): void
    {
        if (!$this->plan) {
            return;
        }

        $this->previewContent = match ($this->format) {
            'markdown' => $this->generateMarkdownPreview(),
            'json' => $this->generateJsonPreview(),
            'csv' => $this->generateCsvPreview(),
            default => $this->generateTablePreview(),
        };

        $this->showPreview = true;
    }

    protected function generateMarkdownPreview(): string
    {
        $plan = $this->plan;
        $md = "# {$plan->name}\n\n";
        $md .= "**Status:** {$plan->status}\n";
        $md .= "**Turn:** {$plan->turn_before}\n\n";

        if ($plan->skills->count() > 0) {
            $md .= "## Skills\n\n";
            foreach ($plan->skills as $skill) {
                $skillName = $skill->skillReference?->name ?? 'Unknown';
                $md .= "- {$skillName} ({$skill->status})\n";
            }
        }

        return $md;
    }

    protected function generateJsonPreview(): string
    {
        return json_encode([
            'schema_version' => '1.0.0',
            'plan' => [
                'name' => $this->plan->name,
                'status' => $this->plan->status,
                'turn' => $this->plan->turn_before,
                'skills_count' => $this->plan->skills->count(),
            ],
        ], JSON_PRETTY_PRINT);
    }

    protected function generateCsvPreview(): string
    {
        $lines = ["Name,Status,Turn,Skills Count"];
        $lines[] = "\"{$this->plan->name}\",\"{$this->plan->status}\",{$this->plan->turn_before},{$this->plan->skills->count()}";
        return implode("\n", $lines);
    }

    protected function generateTablePreview(): string
    {
        return "Excel preview shows as table format in the modal.";
    }

    /**
     * Export the plan.
     */
    public function export(): void
    {
        if (!$this->plan) {
            return;
        }

        $filename = "uma-plan-{$this->plan->id}-" . now()->format('Y-m-d');

        $this->dispatch('start-export', [
            'planId' => $this->planId,
            'format' => $this->format,
            'filename' => $filename,
        ]);

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => "Export started for {$this->plan->name}",
        ]);

        $this->closeModal();
    }

    /**
     * Copy preview to clipboard.
     * Implements FR-6.4, FR-13.4.
     */
    public function copyToClipboard(): void
    {
        $this->dispatch('copy-to-clipboard', content: $this->previewContent);
        $this->dispatch('toast', [
            'type' => 'success',
            'message' => 'Copied to clipboard!',
        ]);
    }

    public function render()
    {
        return view('livewire.export.export-modal');
    }
}
