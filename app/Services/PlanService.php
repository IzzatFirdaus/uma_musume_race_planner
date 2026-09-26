<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\StorageMode;
use App\Events\PlanCreated;
use App\Events\PlanUpdated;
use App\Models\ActivityLog;
use App\Models\Condition;
use App\Models\Mood;
use App\Models\Plan;
use App\Models\SkillReference;
use App\Models\Strategy;
use App\Models\Umamusume;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Plan Service Class
 *
 * Handles complex business logic for plan operations,
 * following Laravel best practices for service-oriented architecture.
 */
class PlanService
{
    public function __construct(
        private readonly LocalRunStorageService $localRunStorageService,
        private readonly UmaMusumeService $umaMusumeService,
    ) {}

    /**
     * Create a detailed plan with all relationships.
     *
     * @param  Request  $request  The HTTP request containing files
     * @param  array  $validated  The validated data
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

            event(new PlanCreated($plan));

            return $plan;
        });
    }

    /**
     * Create a quick plan with minimal data, branching on storage_mode.
     *
     * @param  array{
     *     title: string,
     *     character_id: string,
     *     storage_mode: string,
     *     career_stage?: string,
     *     class?: string
     * }  $validated
     * @return array{
     *     storage_mode: string,
     *     redirect_url: string,
     *     plan: Plan|null,
     *     local_payload: array|null,
     *     uuid: string|null
     * }
     *
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function createQuickPlan(array $validated): array
    {
        $validated = $this->normalizeQuickPlanInput($validated);
        $storageMode = StorageMode::from($validated['storage_mode']);
        $character = $this->umaMusumeService->findOrFail($validated['character_id']);

        if ($storageMode === StorageMode::Local) {
            $payload = $this->localRunStorageService->buildQuickPlanPayload($validated, $character);
            $uuid = $payload['id'];

            return [
                'storage_mode' => StorageMode::Local->value,
                'redirect_url' => route('plans.local.edit', ['uuid' => $uuid]),
                'plan' => null,
                'local_payload' => $payload,
                'uuid' => $uuid,
            ];
        }

        if (! auth()->check()) {
            throw new AuthorizationException('You must be signed in to create account plans.');
        }

        $plan = DB::transaction(function () use ($validated, $character): Plan {
            $plan = $this->createAccountQuickPlan($validated, $character);
            $this->createDefaultAttributes($plan);
            $this->logActivity("New plan created: {$plan->plan_title}", 'bi-person-plus');

            event(new PlanCreated($plan));

            return $plan;
        });

        return [
            'storage_mode' => StorageMode::Account->value,
            'redirect_url' => route('plans.edit', ['planId' => $plan->id]),
            'plan' => $plan,
            'local_payload' => null,
            'uuid' => null,
        ];
    }

    /**
     * Resolve a plan by numeric ID or local UUID.
     *
     * @throws ModelNotFoundException
     */
    public function getPlanByIdOrUuid(string|int $identifier): Plan
    {
        if (is_int($identifier) || (is_string($identifier) && ctype_digit($identifier))) {
            return Plan::query()->findOrFail((int) $identifier);
        }

        if (Str::isUuid((string) $identifier)) {
            return Plan::query()
                ->where('local_uuid', $identifier)
                ->firstOrFail();
        }

        throw (new ModelNotFoundException)->setModel(Plan::class, [(string) $identifier]);
    }

    /**
     * Update an existing plan with new data.
     *
     * @param  Request  $request  The HTTP request containing files
     * @param  Plan  $plan  The plan to update
     * @param  array  $validated  The validated data
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

            event(new PlanUpdated($plan));

            return $plan;
        });
    }

    /**
     * Delete a plan and cleanup associated resources.
     *
     * Local-mode plans stored only in the browser are removed client-side; when a
     * Plan record exists (including converted runs), it is soft-deleted here.
     *
     * @throws Throwable
     */
    public function deletePlan(Plan $plan): void
    {
        if ($plan->isLocal()) {
            $this->deleteLocalPlanRecord($plan);

            return;
        }

        $this->deleteAccountPlan($plan);
    }

    /**
     * Resolve the owner for a newly created Account plan.
     *
     * An Account plan is server-side data, so it must have a real owner. This
     * previously fell back to user 1, but user 1 is the *public* user that
     * PlanPolicy treats as world-readable — that fallback made an anonymous
     * write indistinguishable from a deliberately public plan.
     *
     * Callers are expected to have already verified authentication before
     * reaching a create path; this is the backstop that turns a missed check
     * into a 403 rather than a silent public write.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    private function resolveOwnerId(): int
    {
        $userId = auth()->id();

        if ($userId === null) {
            throw new \Illuminate\Auth\Access\AuthorizationException(
                'You must be signed in to create an Account career run.'
            );
        }

        return (int) $userId;
    }

    /**
     * Create a plan with basic data.
     *
     * @param  array  $planData  The plan data
     * @return Plan The created plan
     */
    private function createPlanWithData(array $planData): Plan
    {
        $planData['trainee_image_path'] = null;
        $planData['user_id'] = $this->resolveOwnerId();

        return Plan::create($planData);
    }

    /**
     * Create a basic account plan from quick create data.
     *
     * @param  array{
     *     title: string,
     *     character_id: string,
     *     storage_mode: string,
     *     career_stage?: string,
     *     class?: string
     * }  $validated
     */
    private function createAccountQuickPlan(array $validated, Umamusume $character): Plan
    {
        $growthRates = $character->growth_rates ?? [];

        return Plan::create([
            'user_id' => $this->resolveOwnerId(),
            'name' => $character->name,
            'plan_title' => $validated['title'],
            'career_stage' => $validated['career_stage'] ?? 'junior',
            'class' => $validated['class'] ?? 'beginner',
            'race_name' => '',
            'status' => 'Planning',
            'storage_mode' => StorageMode::Account,
            'acquire_skill' => 'NO',
            'growth_rate_speed' => (int) ($growthRates['speed'] ?? 0),
            'growth_rate_stamina' => (int) ($growthRates['stamina'] ?? 0),
            'growth_rate_power' => (int) ($growthRates['power'] ?? 0),
            'growth_rate_guts' => (int) ($growthRates['guts'] ?? 0),
            'growth_rate_wit' => (int) ($growthRates['wisdom'] ?? $growthRates['wit'] ?? 0),
            'mood_id' => Mood::query()->where('label', 'NORMAL')->value('id') ?? 1,
            'strategy_id' => Strategy::query()->where('label', 'PACE')->value('id') ?? 1,
            'condition_id' => Condition::query()->where('label', 'N/A')->value('id') ?? 1,
        ]);
    }

    /**
     * Normalize quick-create input and support legacy field names.
     *
     * @param  array<string, mixed>  $validated
     * @return array{
     *     title: string,
     *     character_id: string,
     *     storage_mode: string,
     *     career_stage: string,
     *     class: string
     * }
     */
    private function normalizeQuickPlanInput(array $validated): array
    {
        $title = trim((string) ($validated['title'] ?? $validated['trainee_name'] ?? ''));

        return [
            'title' => $title,
            'character_id' => (string) ($validated['character_id'] ?? $validated['characterId'] ?? ''),
            'storage_mode' => (string) ($validated['storage_mode'] ?? $validated['storageMode'] ?? StorageMode::Local->value),
            'career_stage' => (string) ($validated['career_stage'] ?? $validated['careerStage'] ?? 'junior'),
            'class' => (string) ($validated['class'] ?? $validated['traineeClass'] ?? 'beginner'),
        ];
    }

    /**
     * Delete an account-backed plan and cleanup associated resources.
     */
    private function deleteAccountPlan(Plan $plan): void
    {
        $planTitle = $plan->plan_title;

        DB::transaction(function () use ($plan, $planTitle): void {
            $imagePath = $plan->trainee_image_path;
            $plan->delete();

            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            $this->logActivity("Plan deleted: {$planTitle}", 'bi-trash');
        });
    }

    /**
     * Delete a local-mode plan record when one exists in the database.
     */
    private function deleteLocalPlanRecord(Plan $plan): void
    {
        $planTitle = $plan->plan_title ?? $plan->name;

        DB::transaction(function () use ($plan, $planTitle): void {
            $imagePath = $plan->trainee_image_path;
            $plan->delete();

            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            $this->logActivity("Local plan deleted: {$planTitle}", 'bi-trash');
        });
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
            'attributes',
            'goals',
            'racePredictions',
            'turns',
            'terrainGrades',
            'distanceGrades',
            'styleGrades',
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
            'attributes',
            'goals',
            'racePredictions',
            'turns',
            'terrainGrades',
            'distanceGrades',
            'styleGrades',
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
