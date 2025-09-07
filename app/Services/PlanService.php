<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Plan;
use App\Models\SkillReference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Plan Service Class
 *
 * Handles complex business logic for plan operations,
 * following Laravel best practices for service-oriented architecture.
 */
class PlanService
{
    /**
     * Create a detailed plan with all relationships.
     *
     * @param  Request  $request  The HTTP request containing files
     * @param  array  $validated  The validated data
     *
     * @return Plan The created plan
     *
     * @throws Throwable
     */
    public function createDetailedPlan(Request $request, array $validated): Plan
    {
        return DB::transaction(function () use ($request, $validated) {
            $plan = $this->createPlanWithData($validated['plan']);
            $this->processTraineeImage($request, $plan);
            $this->createPlanRelations($plan, $validated);
            $this->syncSkills($plan, $validated['skills'] ?? []);
            $this->logActivity("New plan created: {$plan->plan_title}", 'bi-person-plus');

            return $plan;
        });
    }

    /**
     * Create a quick plan with minimal data.
     *
     * @param  array  $validated  The validated data
     *
     * @return Plan The created plan
     *
     * @throws Throwable
     */
    public function createQuickPlan(array $validated): Plan
    {
        return DB::transaction(function () use ($validated) {
            $plan = $this->createBasicPlan($validated);
            $this->createDefaultAttributes($plan);
            $this->logActivity("New plan created: {$plan->plan_title}", 'bi-person-plus');

            return $plan;
        });
    }

    /**
     * Update an existing plan with new data.
     *
     * @param  Request  $request  The HTTP request containing files
     * @param  Plan  $plan  The plan to update
     * @param  array  $validated  The validated data
     *
     * @return Plan The updated plan
     *
     * @throws Throwable
     */
    public function updatePlan(Request $request, Plan $plan, array $validated): Plan
    {
        return DB::transaction(function () use ($request, $plan, $validated) {
            $this->updatePlanData($request, $plan, $validated);
            $this->updatePlanRelations($plan, $validated);
            $this->logActivity("Plan updated: {$plan->plan_title}", 'bi-arrow-repeat');

            return $plan;
        });
    }

    /**
     * Delete a plan and cleanup associated resources.
     *
     * @param  Plan  $plan  The plan to delete
     *
     * @throws Throwable
     */
    public function deletePlan(Plan $plan): void
    {
        $planTitle = $plan->plan_title;

        DB::transaction(function () use ($plan, $planTitle): void {
            $imagePath = $plan->trainee_image_path;
            $plan->delete(); // Soft delete

            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            $this->logActivity("Plan deleted: {$planTitle}", 'bi-trash');
        });
    }

    /**
     * Create a plan with basic data.
     *
     * @param  array  $planData  The plan data
     *
     * @return Plan The created plan
     */
    private function createPlanWithData(array $planData): Plan
    {
        $planData['trainee_image_path'] = null;
        $planData['user_id'] = 1; // Public user ID

        return Plan::create($planData);
    }

    /**
     * Create a basic plan from quick create data.
     *
     * @param  array  $validated  The validated data
     *
     * @return Plan The created plan
     */
    private function createBasicPlan(array $validated): Plan
    {
        return Plan::create([
            'user_id' => 1, // Public user ID
            'name' => $validated['trainee_name'],
            'plan_title' => $validated['trainee_name']."'s New Plan",
            'career_stage' => $validated['career_stage'],
            'class' => $validated['traineeClass'],
            'race_name' => $validated['race_name'] ?? '',
            'status' => 'Planning',
            'mood_id' => \App\Models\Mood::where('label', 'NORMAL')->value('id') ?? 1,
            'strategy_id' => \App\Models\Strategy::where('label', 'PACE')->value('id') ?? 1,
            'condition_id' => \App\Models\Condition::where('label', 'N/A')->value('id') ?? 1,
        ]);
    }

    /**
     * Create default attributes for a new plan.
     *
     * @param  Plan  $plan  The plan to add attributes to
     */
    private function createDefaultAttributes(Plan $plan): void
    {
        $default_attributes = ['SPEED', 'STAMINA', 'POWER', 'GUTS', 'WIT'];
        $attributes_data = collect($default_attributes)->map(fn ($name) => [
            'attribute_name' => $name,
            'value' => 0,
            'grade' => 'G',
        ])->all();
        $plan->attributes()->createMany($attributes_data);
    }

    /**
     * Process trainee image upload.
     *
     * @param  Request  $request  The HTTP request
     * @param  Plan  $plan  The plan to associate the image with
     */
    private function processTraineeImage(Request $request, Plan $plan): void
    {
        if ($request->hasFile('trainee_image')) {
            $this->handleTraineeImageUpload($request, $plan);
        }
    }

    /**
     * Create plan relationships.
     *
     * @param  Plan  $plan  The plan to add relationships to
     * @param  array  $validated  The validated data
     */
    private function createPlanRelations(Plan $plan, array $validated): void
    {
        $relations = [
            'attributes', 'goals', 'racePredictions', 'turns',
            'terrainGrades', 'distanceGrades', 'styleGrades',
        ];

        foreach ($relations as $relation) {
            if (isset($validated[$relation]) && count($validated[$relation]) > 0) {
                $plan->{$relation}()->createMany($validated[$relation]);
            }
        }
    }

    /**
     * Update plan data including image handling.
     *
     * @param  Request  $request  The HTTP request
     * @param  Plan  $plan  The plan to update
     * @param  array  $validated  The validated data
     */
    private function updatePlanData(Request $request, Plan $plan, array $validated): void
    {
        $imagePath = $this->handleTraineeImageUpload($request, $plan);

        if (isset($validated['plan']) && count($validated['plan']) > 0) {
            $planData = $validated['plan'];
            $planData['trainee_image_path'] = $imagePath ? $imagePath : $plan->trainee_image_path;
            $plan->update($planData);
        }
    }

    /**
     * Update plan relationships.
     *
     * @param  Plan  $plan  The plan to update
     * @param  array  $validated  The validated data
     */
    private function updatePlanRelations(Plan $plan, array $validated): void
    {
        $relations = [
            'attributes', 'goals', 'racePredictions', 'turns',
            'terrainGrades', 'distanceGrades', 'styleGrades',
        ];

        foreach ($relations as $relation) {
            if (isset($validated[$relation])) {
                $plan->{$relation}()->delete();
                $plan->{$relation}()->createMany($validated[$relation]);
            }
        }

        if (isset($validated['skills'])) {
            $this->syncSkills($plan, $validated['skills']);
        }
    }

    /**
     * Sync skills with a plan.
     *
     * @param  Plan  $plan  The plan to sync skills with
     * @param  array  $skillsData  The skills data
     */
    private function syncSkills(Plan $plan, array $skillsData): void
    {
        $plan->skills()->delete();

        if (count($skillsData) === 0) {
            return;
        }

        $skillsToCreate = array_map([$this, 'buildSkillData'], $skillsData);
        $skillsToCreate = array_filter($skillsToCreate);

        if (count($skillsToCreate) > 0) {
            $plan->skills()->createMany($skillsToCreate);
        }
    }

    /**
     * Build skill data for creation.
     *
     * @param  array  $skill  The skill data
     *
     * @return array|null The formatted skill data or null
     */
    private function buildSkillData(array $skill): ?array
    {
        $skillName = trim($skill['name'] ?? '');

        if ($skillName === '') {
            return null;
        }

        $skillRef = SkillReference::firstOrCreate(
            ['skill_name' => $skillName],
            [
                'description' => $skill['notes'] ?? 'User-added skill.',
                'tag' => $skill['tag'] ?? '📝',
            ]
        );

        return [
            'skill_reference_id' => $skillRef->id,
            'acquired' => isset($skill['acquired']) && $skill['acquired'] === 'yes' ? 'yes' : 'no',
            'tag' => trim($skill['tag'] ?? ''),
            'notes' => trim($skill['notes'] ?? ''),
        ];
    }

    /**
     * Handle trainee image upload.
     *
     * @param  Request  $request  The HTTP request
     * @param  Plan  $plan  The plan to associate the image with
     *
     * @return string|null The image path
     */
    private function handleTraineeImageUpload(Request $request, Plan $plan): ?string
    {
        $currentPath = $plan->trainee_image_path;

        if ($request->boolean('clear_trainee_image')) {
            $this->deleteImageIfExists($currentPath);
            $currentPath = null;
        }

        if ($request->hasFile('trainee_image')) {
            $this->deleteImageIfExists($currentPath);
            $currentPath = $request->file('trainee_image')->store('trainee_images', 'public');
        }

        if ($currentPath !== $plan->trainee_image_path) {
            $plan->trainee_image_path = $currentPath;
            $plan->save();
        }

        return $currentPath;
    }

    /**
     * Delete image file if it exists.
     *
     * @param  string|null  $path  The image path
     */
    private function deleteImageIfExists(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Log an activity.
     *
     * @param  string  $description  The activity description
     * @param  string  $iconClass  The icon class
     */
    private function logActivity(string $description, string $iconClass): void
    {
        ActivityLog::create([
            'description' => $description,
            'icon_class' => $iconClass,
        ]);
    }
}
