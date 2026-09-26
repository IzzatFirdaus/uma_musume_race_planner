<?php

declare(strict_types=1);

namespace App\Livewire\Import;

use App\Enums\ImportTarget;
use App\Services\ImportService;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Import Wizard Component
 *
 * Multi-step import flow: upload → preview → resolve conflicts → execute → results.
 * Implements REQ-IMP-1: Import Wizard with format detection, preview, and conflict resolution.
 */
class ImportWizard extends Component
{
    use WithFileUploads;

    // Wizard state
    public int $currentStep = 1;

    // File upload
    public $file;

    // Import data
    public ?string $detectedFormat = null;

    public ?string $confidence = null;

    public array $alternatives = [];

    public array $previewData = [];

    public array $fieldMapping = [];

    public array $validationErrors = [];

    public array $validationWarnings = [];

    public array $validationSummary = [];

    public array $duplicates = [];

    // Import options
    public string $target = 'local'; // local or account

    public bool $skipDuplicates = false;

    public bool $overwriteDuplicates = false;

    // Import results
    public array $importResults = [];

    public bool $importSuccess = false;

    protected ImportService $importService;

    public function boot(ImportService $importService): void
    {
        $this->importService = $importService;
    }

    #[On('open-import-wizard')]
    public function openWizard(): void
    {
        $this->resetWizard();
        $this->currentStep = 1;
    }

    public function resetWizard(): void
    {
        $this->file = null;
        $this->detectedFormat = null;
        $this->confidence = null;
        $this->alternatives = [];
        $this->previewData = [];
        $this->fieldMapping = [];
        $this->validationErrors = [];
        $this->validationWarnings = [];
        $this->validationSummary = [];
        $this->duplicates = [];
        $this->target = 'local';
        $this->skipDuplicates = false;
        $this->overwriteDuplicates = false;
        $this->importResults = [];
        $this->importSuccess = false;
    }

    /**
     * Step 1: Upload file and detect format.
     */
    public function uploadAndDetect(): void
    {
        $this->validate([
            'file' => 'required|file|max:10240', // Max 10MB
        ]);

        try {
            $content = file_get_contents($this->file->getRealPath());
            $filename = $this->file->getClientOriginalName();

            $detection = $this->importService->detectFormat($content, $filename);

            if (! $detection['detected']) {
                $this->dispatch('toast', [
                    'type' => 'error',
                    'message' => 'Unable to detect file format. Please ensure the file is a valid JSON or CSV.',
                ]);

                return;
            }

            $this->detectedFormat = $detection['format'];
            $this->confidence = $detection['confidence'];
            $this->alternatives = $detection['alternatives'];

            // Generate preview
            $preview = $this->importService->preview($content, $filename);

            if (! $preview['success']) {
                $this->dispatch('toast', [
                    'type' => 'error',
                    'message' => 'Failed to parse file: '.($preview['errors'][0]['message'] ?? 'Unknown error'),
                ]);

                return;
            }

            $this->previewData = [
                'plans' => $preview['plans']->toArray(),
                'characters' => $preview['characters']->toArray(),
                'metadata' => $preview['metadata'],
            ];
            $this->fieldMapping = $preview['field_mapping'];
            $this->validationWarnings = $preview['warnings'];

            $this->currentStep = 2;
        } catch (\Exception $e) {
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'Error processing file: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Step 2: Validate import data.
     */
    public function validateImport(): void
    {
        try {
            $content = file_get_contents($this->file->getRealPath());
            $filename = $this->file->getClientOriginalName();

            $target = ImportTarget::from($this->target);
            $user = $this->target === 'account' ? auth()->user() : null;

            $validation = $this->importService->validate($content, $filename, $target, $user);

            $this->validationErrors = $validation['errors'];
            $this->validationWarnings = array_merge($this->validationWarnings, $validation['warnings']);
            $this->validationSummary = $validation['summary'];
            $this->duplicates = $validation['duplicates'];

            if (! $validation['valid'] && ! empty($this->validationErrors)) {
                $this->dispatch('toast', [
                    'type' => 'error',
                    'message' => 'Validation failed. Please review the errors below.',
                ]);

                return;
            }

            // If there are duplicates, go to conflict resolution step
            if (! empty($this->duplicates)) {
                $this->currentStep = 3;
            } else {
                // Skip to execution step
                $this->currentStep = 4;
            }
        } catch (\Exception $e) {
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'Validation error: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Step 3: Resolve conflicts and proceed.
     */
    public function resolveConflicts(): void
    {
        if ($this->skipDuplicates) {
            $this->overwriteDuplicates = false;
        }

        $this->currentStep = 4;
    }

    /**
     * Step 4: Execute import.
     */
    public function executeImport(): void
    {
        try {
            $content = file_get_contents($this->file->getRealPath());
            $filename = $this->file->getClientOriginalName();

            $target = ImportTarget::from($this->target);
            $user = $this->target === 'account' ? auth()->user() : null;

            $options = [
                'skip_duplicates' => $this->skipDuplicates,
                'overwrite_duplicates' => $this->overwriteDuplicates,
            ];

            $result = $this->importService->import($content, $filename, $target, $user, $options);

            $this->importResults = $result;
            $this->importSuccess = $result['success'];

            if ($this->importSuccess) {
                $this->currentStep = 5;

                $this->dispatch('toast', [
                    'type' => 'success',
                    'message' => "Import completed successfully! Created {$result['created']} plans.",
                ]);

                // Refresh plan list if importing to account
                if ($this->target === 'account') {
                    $this->dispatch('refresh-plans');
                }
                // Refresh local storage if importing to local
                if ($this->target === 'local') {
                    $this->dispatch('refresh-local-storage');
                }
            } else {
                $this->dispatch('toast', [
                    'type' => 'error',
                    'message' => 'Import failed. Please review the errors.',
                ]);
            }
        } catch (\Exception $e) {
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'Import error: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Step 5: Close wizard after successful import.
     */
    public function closeWizard(): void
    {
        $this->dispatch('close-modal');
        $this->resetWizard();
    }

    /**
     * Go to previous step.
     */
    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    /**
     * Get step label.
     */
    public function getStepLabel(int $step): string
    {
        return match ($step) {
            1 => 'Upload File',
            2 => 'Preview',
            3 => 'Resolve Conflicts',
            4 => 'Confirm Import',
            5 => 'Results',
            default => 'Unknown',
        };
    }

    /**
     * Get step description.
     */
    public function getStepDescription(int $step): string
    {
        return match ($step) {
            1 => 'Select a file to import (JSON or CSV)',
            2 => 'Review the detected data and field mapping',
            3 => 'Resolve duplicate plan conflicts',
            4 => 'Confirm import settings and target',
            5 => 'View import results',
            default => '',
        };
    }

    /**
     * Check if step is completed.
     */
    public function isStepCompleted(int $step): bool
    {
        return match ($step) {
            1 => $this->detectedFormat !== null,
            2 => ! empty($this->validationSummary),
            3 => $this->skipDuplicates || $this->overwriteDuplicates || empty($this->duplicates),
            4 => false, // Execution step
            5 => $this->importSuccess,
            default => false,
        };
    }

    /**
     * Get supported formats.
     */
    public function getSupportedFormats(): array
    {
        return $this->importService->getSupportedFormats();
    }

    /**
     * Get supported file extensions.
     */
    public function getSupportedExtensions(): array
    {
        return $this->importService->getSupportedExtensions();
    }

    public function render()
    {
        return view('livewire.import.import-wizard');
    }
}
