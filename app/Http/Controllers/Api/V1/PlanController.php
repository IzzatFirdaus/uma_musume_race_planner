<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlanRequest;
use App\Http\Requests\UpdatePlanRequest;
use App\Models\ActivityLog;
use App\Models\Plan;
use App\Models\SkillReference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PlanController extends Controller
{
    // The ID of the default user for all public plans.
    public const PUBLIC_USER_ID = 1;

    /**
     * Defines the relationships to be eager-loaded with every plan response.
     */
    protected function getRelationshipsToLoad(): array
    {
        // UPDATED: 'user' relationship removed from eager loading
        return [
            'attributes', 'skills.skillReference', 'racePredictions', 'goals', 'turns',
            'terrainGrades', 'distanceGrades', 'styleGrades', 'mood', 'condition', 'strategy',
        ];
    }

    /**
     * Display a listing of all public resources.
     * Replaces the functionality of get_plans.php.
     */
    public function index(): JsonResponse
    {
        // UPDATED: Fetches all plans globally
        $plans = Plan::with($this->getRelationshipsToLoad())->latest()->get();

        return response()->json($plans);
    }

    /**
     * Store a newly created resource in storage (Detailed Plan).
     * Replaces the 'create' functionality of handle_plan_crud.php.
     *
     * @throws Throwable
     */
    public function store(StorePlanRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $plan = DB::transaction(function () use ($request, $validated) {
            $plan = $this->createPlanWithData($validated['plan']);
            $this->processTraineeImage($request, $plan);
            $this->createPlanRelations($plan, $validated);
            $this->syncSkills($plan, $validated['skills'] ?? []);
            $this->logPlanCreation($plan);

            return $plan;
        });

        return response()->json($plan->load($this->getRelationshipsToLoad()), 201);
    }

    private function createPlanWithData(array $planData): Plan
    {
        $planData['trainee_image_path'] = null;
        $planData['user_id'] = self::PUBLIC_USER_ID;

        return Plan::create($planData);
    }

    private function processTraineeImage(Request $request, Plan $plan): void
    {
        if ($request->hasFile('trainee_image')) {
            $this->handleTraineeImageUpload($request, $plan);
        }
    }

    private function createPlanRelations(Plan $plan, array $validated): void
    {
        $relations = [
            'attributes', 'goals', 'racePredictions', 'turns', 'terrainGrades', 'distanceGrades', 'styleGrades',
        ];
        foreach ($relations as $relation) {
            if (isset($validated[$relation]) && count($validated[$relation]) > 0) {
                $plan->{$relation}()->createMany($validated[$relation]);
            }
        }
    }

    private function logPlanCreation(Plan $plan): void
    {
        ActivityLog::create([
            'description' => "New plan created: {$plan->plan_title}",
            'icon_class' => 'bi-person-plus',
        ]);
    }

    /**
     * Store a newly created resource using minimal data (Quick Create).
     * Replaces the 'quick_create' functionality of handle_plan_crud.php.
     *
     * @throws Throwable
     */
    public function storeQuick(Request $request): JsonResponse
    {
        $validated = $this->validateQuickCreateRequest($request);

        $plan = DB::transaction(function () use ($validated) {
            $plan = $this->createQuickPlan($validated);
            $this->createDefaultAttributes($plan);
            $this->logPlanCreation($plan);

            return $plan;
        });

        return response()->json($plan->load($this->getRelationshipsToLoad()), 201);
    }

    private function validateQuickCreateRequest(Request $request): array
    {
        return $request->validate([
            'trainee_name' => 'required|string|max:255',
            'career_stage' => 'required|string|in:predebut,junior,classic,senior,finale',
            'traineeClass' => 'required|string|in:debut,maiden,beginner,bronze,silver,gold,platinum,star,legend',
            'race_name' => 'nullable|string|max:255',
        ]);
    }

    private function createQuickPlan(array $validated): Plan
    {
        return Plan::create([
            'user_id' => self::PUBLIC_USER_ID, // UPDATED: Assign to the default public user
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
     * Display the specified resource.
     * Replaces functionality from fetch_plan_details.php and all get_plan_*.php files.
     */
    public function show(Plan $plan): JsonResponse
    {
        // UPDATED: Authorization removed
        return response()->json($plan->load($this->getRelationshipsToLoad()));
    }

    /**
     * Update the specified resource in storage.
     * Replaces the 'update' functionality of handle_plan_crud.php.
     *
     * @throws Throwable
     */
    public function update(UpdatePlanRequest $request, Plan $plan): JsonResponse
    {
        $validated = $request->validated();
        DB::transaction(function () use ($request, $plan, $validated) {
            $this->updatePlanData($request, $plan, $validated);
            $this->updatePlanRelations($plan, $validated);
            ActivityLog::create([
                'description' => "Plan updated: {$plan->plan_title}",
                'icon_class' => 'bi-arrow-repeat',
            ]);
        });

        return response()->json($plan->load($this->getRelationshipsToLoad()));
    }

    private function updatePlanData(Request $request, Plan $plan, array $validated): void
    {
        $imagePath = $this->handleTraineeImageUpload($request, $plan);
        if (isset($validated['plan']) && count($validated['plan']) > 0) {
            $planData = $validated['plan'];
            $planData['trainee_image_path'] = $imagePath ? $imagePath : $plan->trainee_image_path;
            $plan->update($planData);
        }
    }

    private function updatePlanRelations(Plan $plan, array $validated): void
    {
        $relations = [
            'attributes', 'goals', 'racePredictions', 'turns', 'terrainGrades', 'distanceGrades', 'styleGrades',
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
     * Remove the specified resource from storage.
     * Replaces the 'delete' functionality of handle_plan_crud.php.
     */
    public function destroy(Plan $plan): JsonResponse
    {
        // UPDATED: Authorization removed
        $planTitle = $plan->plan_title;

        DB::transaction(function () use ($plan, $planTitle) {
            $imagePath = $plan->trainee_image_path;
            $plan->delete(); // Soft delete

            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            ActivityLog::create([
                'description' => "Plan deleted: {$planTitle}",
                'icon_class' => 'bi-trash',
            ]);
        });

        return response()->json(null, 204);
    }

    /**
     * Provide data for the progress chart.
     * This method replaces the functionality of get_progress_chart_data.php.
     */
    public function progressChart(Plan $plan): JsonResponse
    {
        // UPDATED: Authorization removed
        $turns = $plan->turns()->orderBy('turn_number')->get(['turn_number as turn', 'speed', 'stamina', 'power', 'guts', 'wit']);

        return response()->json(['success' => true, 'data' => $turns]);
    }

    /**
     * Export a plan's details as a formatted text file.
     * This method replaces the functionality of export_plan_data.php.
     */
    public function export(Plan $plan): Response
    {
        // UPDATED: Authorization removed
        $plan->load($this->getRelationshipsToLoad());
        $safeFileName = preg_replace('/[^a-z0-9_]/i', '_', $plan->plan_title ? $plan->plan_title : 'plan');
        $fileName = "{$safeFileName}_{$plan->id}.txt";

        return new Response($this->buildPlanText($plan), 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    private function buildPlanText(Plan $plan): string
    {
        $divider = "\n".str_repeat('=', 80)."\n\n";

        $generalInfo = [
            ['Trainee Name:', $plan->name],
            ['Career Stage:', strtoupper("{$plan->career_stage} {$plan->month} {$plan->time_of_day}")],
            ['Class:', strtoupper($plan->class)],
        ];
        $maxKeyLength = max(array_map('strlen', array_column($generalInfo, 0)));
        $generalInfoText = '';
        foreach ($generalInfo as $row) {
            $generalInfoText .= str_pad($row[0], $maxKeyLength)." {$row[1]}\n";
        }

        $attrRows = $plan->attributes->map(fn ($attr) => [ucfirst(strtolower($attr->attribute_name)), $attr->value, $attr->grade])->toArray();
        $attributesText = $divider."ATTRIBUTES\n".$this->buildTextTable(['Attribute', 'Value', 'Grade'], $attrRows);

        $skillRows = $plan->skills->map(fn ($skill) => [$skill->skillReference->skill_name, $skill->sp_cost, $skill->acquired, $skill->notes])->toArray();
        $skillsText = $divider."SKILLS\n".$this->buildTextTable(['Name', 'Cost', 'Acquired', 'Notes'], $skillRows);

        return "## PLAN: {$plan->plan_title} ##\n\n".$generalInfoText.$attributesText.$skillsText;
    }

    private function buildTextTable(array $headers, array $rows): string
    {
        if (count($rows) === 0) {
            return "No data.\n";
        }
        $widths = [];
        foreach ($headers as $i => $header) {
            $widths[$i] = max(strlen($header), ...array_map(fn ($row) => strlen($row[$i] ?? ''), $rows));
        }

        $headerRow = '| '.implode(' | ', array_map(fn ($h, $i) => str_pad($h, $widths[$i]), $headers, array_keys($headers)))." |\n";
        $dividerRow = '|'.implode('|', array_map(fn ($w) => str_repeat('-', $w + 2), $widths))."|\n";
        $dataRows = implode('', array_map(fn ($row) => '| '.implode(' | ', array_map(fn ($item, $i) => str_pad((string) $item, $widths[$i]), $row, array_keys($row)))." |\n", $rows));

        return $headerRow.$dividerRow.$dataRows;
    }

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

    private function buildSkillData(array $skill): ?array
    {
        $skillName = trim($skill['name'] ?? '');
        if ($skillName === '') {
            return null;
        }

        $skillRef = SkillReference::firstOrCreate(
            ['skill_name' => $skillName],
            ['description' => $skill['notes'] ?? 'User-added skill.', 'tag' => $skill['tag'] ?? '📝']
        );

        $acquired = 'no';
        if (isset($skill['acquired']) && $skill['acquired'] === 'yes') {
            $acquired = 'yes';
        }

        return [
            'skill_reference_id' => $skillRef->id,
            'acquired' => $acquired,
            'tag' => trim($skill['tag'] ?? ''),
            'notes' => trim($skill['notes'] ?? ''),
        ];
    }

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

    private function deleteImageIfExists(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
