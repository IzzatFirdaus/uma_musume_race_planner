<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Condition;
use App\Models\Mood;
use App\Models\Plan;
use App\Models\Strategy;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the main application dashboard with all necessary initial data.
     */
    public function index(): View
    {
        $plans = $this->getPlans();
        $stats = $this->getStatsArray();
        $activities = $this->getActivitiesArray();
        $options = $this->getOptions();

        return view('dashboard', array_merge(
            [
                'plans' => $plans,
                'stats' => $stats,
                'activities' => $activities,
            ],
            $options
        ));
    }

    private function getPlans()
    {
        return Plan::with([
            'attributes' => fn ($query) => $query->whereIn('attribute_name', ['SPEED', 'STAMINA', 'POWER', 'GUTS', 'WIT']),
        ])->latest()->get();
    }

    private function getStatsArray()
    {
        return [
            'total_plans' => Plan::count(),
            'active_plans' => Plan::where('status', 'Active')->count(),
            'finished_plans' => Plan::where('status', 'Finished')->count(),
        ];
    }

    private function getActivitiesArray()
    {
        return ActivityLog::latest()->take(7)->get();
    }

    private function getOptions(): array
    {
        return [
            'moodOptions' => Mood::all(['id', 'label']),
            'strategyOptions' => Strategy::all(['id', 'label']),
            'conditionOptions' => Condition::all(['id', 'label']),
            'skillTagOptions' => \App\Models\SkillReference::distinct()->pluck('tag'),
            'careerStageOptions' => $this->getCareerStageOptions(),
            'classOptions' => $this->getClassOptions(),
            'attributeGradeOptions' => ['S+', 'S', 'A+', 'A', 'B+', 'B', 'C+', 'C', 'D+', 'D', 'E+', 'E', 'F+', 'F', 'G+', 'G'],
            'predictionIcons' => ['◎', '⦾', '○', '△', 'X', '-'],
        ];
    }

    private function getCareerStageOptions(): array
    {
        return [
            ['value' => 'predebut', 'text' => 'Pre-Debut'],
            ['value' => 'junior', 'text' => 'Junior Year'],
            ['value' => 'classic', 'text' => 'Classic Year'],
            ['value' => 'senior', 'text' => 'Senior Year'],
            ['value' => 'finale', 'text' => 'Finale Season'],
        ];
    }

    private function getClassOptions(): array
    {
        return [
            ['value' => 'debut', 'text' => 'Debut'],
            ['value' => 'maiden', 'text' => 'Maiden'],
            ['value' => 'beginner', 'text' => 'Beginner'],
            ['value' => 'bronze', 'text' => 'Bronze'],
            ['value' => 'silver', 'text' => 'Silver'],
            ['value' => 'gold', 'text' => 'Gold'],
            ['value' => 'platinum', 'text' => 'Star'],
            ['value' => 'legend', 'text' => 'Legend'],
        ];
    }

    /**
     * API endpoint to get dashboard stats.
     */
    public function getStats(): JsonResponse
    {
        // UPDATED: Queries are now global.
        $stats = [
            'total_plans' => Plan::count(),
            'active_plans' => Plan::where('status', 'Active')->count(),
            'finished_plans' => Plan::where('status', 'Finished')->count(),
        ];

        return response()->json($stats);
    }

    /**
     * API endpoint to get recent activities.
     */
    public function getActivities(): JsonResponse
    {
        $activities = ActivityLog::latest()->take(7)->get();

        return response()->json($activities);
    }
}
