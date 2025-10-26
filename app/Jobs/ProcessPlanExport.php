<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Plan;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Process Plan Export Job
 *
 * Background job for processing plan exports to text files.
 * Handles heavy export operations without blocking the request.
 */
class ProcessPlanExport implements ShouldQueue
{
    use Queueable;

    /**
     * The plan to export.
     */
    private Plan $plan;

    /**
     * The export file name.
     */
    private string $fileName;

    /**
     * Create a new job instance.
     *
     * @param  Plan  $plan  The plan to export
     * @param  string  $fileName  The export file name
     */
    public function __construct(Plan $plan, string $fileName)
    {
        $this->plan = $plan;
        $this->fileName = $fileName;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Load all relationships needed for export
            $this->plan->load([
                'attributes', 'skills.skillReference', 'racePredictions',
                'goals', 'turns', 'terrainGrades', 'distanceGrades',
                'styleGrades', 'mood', 'condition', 'strategy',
            ]);

            // Generate export content
            $exportContent = $this->buildPlanText($this->plan);

            // Store the export file
            $filePath = 'exports/'.$this->fileName;
            Storage::disk('public')->put($filePath, $exportContent);

            Log::info('Plan export completed successfully', [
                'plan_id' => $this->plan->id,
                'file_name' => $this->fileName,
                'file_path' => $filePath,
            ]);
        } catch (\Exception $e) {
            Log::error('Plan export failed', [
                'plan_id' => $this->plan->id,
                'file_name' => $this->fileName,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Build the plan export text content.
     *
     * @param  Plan  $plan  The plan to export
     * @return string The formatted plan text
     */
    private function buildPlanText(Plan $plan): string
    {
        $divider = "\n".str_repeat('=', 80)."\n\n";

        // General Information
        $generalInfo = [
            ['Trainee Name:', $plan->name],
            ['Plan Title:', $plan->plan_title],
            ['Career Stage:', strtoupper("{$plan->career_stage} {$plan->month} {$plan->time_of_day}")],
            ['Class:', strtoupper($plan->class ?? 'N/A')],
        ];

        $maxKeyLength = max(array_map('strlen', array_column($generalInfo, 0)));
        $generalInfoText = '';
        foreach ($generalInfo as $row) {
            $generalInfoText .= str_pad($row[0], $maxKeyLength)." {$row[1]}\n";
        }

        return "## PLAN: {$plan->plan_title} ##\n\n".
               $generalInfoText.
               $divider.
               'Exported at: '.now()->toDateTimeString()."\n";
    }
}
