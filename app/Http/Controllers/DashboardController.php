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
        // UPDATED: Queries are now global instead of user-specific.
        $plans = Plan::with([
            'attributes' => fn ($query) => $query->whereIn('attribute_name', ['SPEED', 'STAMINA', 'POWER', 'GUTS', 'WIT'])
        ])->latest()->get();

        $stats = [
            'total_plans' => Plan::count(),
            'active_plans' => Plan::where('status', 'Active')->count(),
            'finished_plans' => Plan::where('status', 'Finished')->count(),
        ];

        $activities = ActivityLog::latest()->take(7)->get();

        // Fetch lookup/option data for forms
        $moodOptions = Mood::all(['id', 'label']);
        $strategyOptions = Strategy::all(['id', 'label']);
        $conditionOptions = Condition::all(['id', 'label']);
        $skillTagOptions = \App\Models\SkillReference::distinct()->pluck('tag');

        // Define static options arrays
        $careerStageOptions = [['value' => 'predebut', 'text' => 'Pre-Debut'], ['value' => 'junior', 'text' => 'Junior Year'], ['value' => 'classic', 'text' => 'Classic Year'], ['value' => 'senior', 'text' => 'Senior Year'], ['value' => 'finale', 'text' => 'Finale Season']];
        $classOptions = [['value' => 'debut', 'text' => 'Debut'], ['value' => 'maiden', 'text' => 'Maiden'], ['value' => 'beginner', 'text' => 'Beginner'], ['value' => 'bronze', 'text' => 'Bronze'], ['value' => 'silver', 'text' => 'Silver'], ['value' => 'gold', 'text' => 'Gold'], ['value' => 'platinum', 'text' => 'Star'], ['value' => 'legend', 'text' => 'Legend']];
        $attributeGradeOptions = ['S+', 'S', 'A+', 'A', 'B+', 'B', 'C+', 'C', 'D+', 'D', 'E+', 'E', 'F+', 'F', 'G+', 'G'];
        $predictionIcons = ['◎', '⦾', '○', '△', 'X', '-'];

        return view('dashboard', compact(
            'plans', 'stats', 'activities', 'moodOptions', 'strategyOptions', 'conditionOptions',
            'skillTagOptions', 'careerStageOptions', 'classOptions', 'attributeGradeOptions', 'predictionIcons'
        ));
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
