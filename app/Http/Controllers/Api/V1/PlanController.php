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
    const PUBLIC_USER_ID = 1;

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
            $planData = $validated['plan'];
            $planData['trainee_image_path'] = null;
            // UPDATED: Assign to the default public user
            $planData['user_id'] = self::PUBLIC_USER_ID;

            $plan = Plan::create($planData);

            if ($request->hasFile('trainee_image')) {
                $this->handleTraineeImageUpload($request, $plan);
            }

            if (!empty($validated['attributes'])) { $plan->attributes()->createMany($validated['attributes']); }
            if (!empty($validated['goals'])) { $plan->goals()->createMany($validated['goals']); }
            if (!empty($validated['racePredictions'])) { $plan->racePredictions()->createMany($validated['racePredictions']); }
            if (!empty($validated['turns'])) { $plan->turns()->createMany($validated['turns']); }
            if (!empty($validated['terrainGrades'])) { $plan->terrainGrades()->createMany($validated['terrainGrades']); }
            if (!empty($validated['distanceGrades'])) { $plan->distanceGrades()->createMany($validated['distanceGrades']); }
            if (!empty($validated['styleGrades'])) { $plan->styleGrades()->createMany($validated['styleGrades']); }

            $this->syncSkills($plan, $validated['skills'] ?? []);

            ActivityLog::create([
                'description' => "New plan created: {$plan->plan_title}",
                'icon_class' => 'bi-person-plus',
            ]);

            return $plan;
        });

        return response()->json($plan->load($this->getRelationshipsToLoad()), 201);
    }

    /**
     * Store a newly created resource using minimal data (Quick Create).
     * Replaces the 'quick_create' functionality of handle_plan_crud.php.
     *
     * @throws Throwable
     */
    public function storeQuick(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'trainee_name' => 'required|string|max:255',
            'career_stage' => 'required|string|in:predebut,junior,classic,senior,finale',
            'traineeClass' => 'required|string|in:debut,maiden,beginner,bronze,silver,gold,platinum,star,legend',
            'race_name' => 'nullable|string|max:255',
        ]);

        $plan = DB::transaction(function () use ($validated) {
            $plan = Plan::create([
                'user_id' => self::PUBLIC_USER_ID, // UPDATED: Assign to the default public user
                'name' => $validated['trainee_name'],
                'plan_title' => $validated['trainee_name'] . "'s New Plan",
                'career_stage' => $validated['career_stage'],
                'class' => $validated['traineeClass'],
                'race_name' => $validated['race_name'] ?? '',
                'status' => 'Planning',
                'mood_id' => \App\Models\Mood::where('label', 'NORMAL')->value('id') ?? 1,
                'strategy_id' => \App\Models\Strategy::where('label', 'PACE')->value('id') ?? 1,
                'condition_id' => \App\Models\Condition::where('label', 'N/A')->value('id') ?? 1,
            ]);

            $default_attributes = ['SPEED', 'STAMINA', 'POWER', 'GUTS', 'WIT'];
            $attributes_data = collect($default_attributes)->map(fn($name) => [
                'attribute_name' => $name, 'value' => 0, 'grade' => 'G'
            ])->all();
            $plan->attributes()->createMany($attributes_data);

            return $plan;
        });

        return response()->json($plan->load($this->getRelationshipsToLoad()), 201);
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
        // UPDATED: Authorization removed
        $validated = $request->validated();

        DB::transaction(function () use ($request, $plan, $validated) {
            $imagePath = $this->handleTraineeImageUpload($request, $plan);

            if (!empty($validated['plan'])) {
                $planData = $validated['plan'];
                $planData['trainee_image_path'] = $imagePath;
                $plan->update($planData);
            }

            if (isset($validated['attributes'])) {
                $plan->attributes()->delete();
                $plan->attributes()->createMany($validated['attributes']);
            }
            if (isset($validated['goals'])) {
                $plan->goals()->delete();
                $plan->goals()->createMany($validated['goals']);
            }
             if (isset($validated['racePredictions'])) {
                $plan->racePredictions()->delete();
                $plan->racePredictions()->createMany($validated['racePredictions']);
            }
            if (isset($validated['turns'])) {
                $plan->turns()->delete();
                $plan->turns()->createMany($validated['turns']);
            }
            if (isset($validated['terrainGrades'])) {
                $plan->terrainGrades()->delete();
                $plan->terrainGrades()->createMany($validated['terrainGrades']);
            }
            if (isset($validated['distanceGrades'])) {
                $plan->distanceGrades()->delete();
                $plan->distanceGrades()->createMany($validated['distanceGrades']);
            }
            if (isset($validated['styleGrades'])) {
                $plan->styleGrades()->delete();
                $plan->styleGrades()->createMany($validated['styleGrades']);
            }
            if (isset($validated['skills'])) {
                $this->syncSkills($plan, $validated['skills']);
            }

            ActivityLog::create([
                'description' => "Plan updated: {$plan->plan_title}",
                'icon_class' => 'bi-arrow-repeat',
            ]);
        });

        return response()->json($plan->load($this->getRelationshipsToLoad()));
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
        $output = $this->buildPlanText($plan);
        $safeFileName = preg_replace('/[^a-z0-9_]/i', '_', $plan->plan_title ?: 'plan');
        $fileName = "{$safeFileName}_{$plan->id}.txt";

        return new Response($output, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    private function buildPlanText(Plan $plan): string
    {
        $output = "## PLAN: {$plan->plan_title} ##\n\n";
        $divider = "\n" . str_repeat('=', 80) . "\n\n";

        $generalInfo = [
            ['Trainee Name:', $plan->name],
            ['Career Stage:', strtoupper("{$plan->career_stage} {$plan->month} {$plan->time_of_day}")],
            ['Class:', strtoupper($plan->class)],
        ];
        $maxKeyLength = max(array_map('strlen', array_column($generalInfo, 0)));
        foreach ($generalInfo as $row) {
            $output .= str_pad($row[0], $maxKeyLength) . " {$row[1]}\n";
        }

        $attrRows = $plan->attributes->map(fn ($attr) => [ucfirst(strtolower($attr->attribute_name)), $attr->value, $attr->grade])->toArray();
        $output .= $divider . "ATTRIBUTES\n" . $this->buildTextTable(['Attribute', 'Value', 'Grade'], $attrRows);

        $skillRows = $plan->skills->map(fn ($skill) => [$skill->skillReference->skill_name, $skill->sp_cost, $skill->acquired, $skill->notes])->toArray();
        $output .= $divider . "SKILLS\n" . $this->buildTextTable(['Name', 'Cost', 'Acquired', 'Notes'], $skillRows);

        return $output;
    }

    private function buildTextTable(array $headers, array $rows): string
    {
        if (empty($rows)) return "No data.\n";
        $widths = [];
        foreach ($headers as $i => $header) {
            $widths[$i] = max(strlen($header), ...array_map(fn ($row) => strlen($row[$i] ?? ''), $rows));
        }

        $headerRow = "| " . implode(' | ', array_map(fn ($h, $i) => str_pad($h, $widths[$i]), $headers, array_keys($headers))) . " |\n";
        $dividerRow = "|" . implode("|", array_map(fn ($w) => str_repeat('-', $w + 2), $widths)) . "|\n";
        $dataRows = implode("", array_map(function ($row) use ($widths) {
            return "| " . implode(' | ', array_map(fn ($item, $i) => str_pad((string)$item, $widths[$i]), $row, array_keys($row))) . " |\n";
        }, $rows));

        return $headerRow . $dividerRow . $dataRows;
    }

    private function syncSkills(Plan $plan, array $skillsData): void
    {
        $plan->skills()->delete();
        if (empty($skillsData)) return;

        $skillsToCreate = [];
        foreach ($skillsData as $skill) {
            $skillName = trim($skill['name'] ?? '');
            if (empty($skillName)) continue;

            $skillRef = SkillReference::firstOrCreate(
                ['skill_name' => $skillName],
                ['description' => $skill['notes'] ?? 'User-added skill.', 'tag' => $skill['tag'] ?? '📝']
            );

            $skillsToCreate[] = [
                'skill_reference_id' => $skillRef->id,
                'sp_cost' => (int) ($skill['sp_cost'] ?? 0),
                'acquired' => ($skill['acquired'] ?? 'no') === 'yes' ? 'yes' : 'no',
                'tag' => trim($skill['tag'] ?? ''),
                'notes' => trim($skill['notes'] ?? ''),
            ];
        }

        if (!empty($skillsToCreate)) {
            $plan->skills()->createMany($skillsToCreate);
        }
    }

    private function handleTraineeImageUpload(Request $request, Plan $plan): ?string
    {
        $currentPath = $plan->trainee_image_path;

        if ($request->boolean('clear_trainee_image')) {
            if ($currentPath) Storage::disk('public')->delete($currentPath);
            $currentPath = null;
        }

        if ($request->hasFile('trainee_image')) {
            if ($currentPath) Storage::disk('public')->delete($currentPath);
            $currentPath = $request->file('trainee_image')->store('trainee_images', 'public');
        }

        if ($currentPath !== $plan->trainee_image_path) {
            $plan->trainee_image_path = $currentPath;
            $plan->save();
        }

        return $currentPath;
    }
}
