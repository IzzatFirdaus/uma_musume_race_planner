<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\SkillReference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AutosuggestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'field' => 'required|string|in:name,race_name,skill_name,goal',
            'query' => 'required|string|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()], 400);
        }

        $field = $request->input('field');
        $query = $request->input('query');
        $suggestions = [];

        switch ($field) {
            case 'skill_name':
                $suggestions = SkillReference::where('skill_name', 'LIKE', "%{$query}%")
                    ->limit(10)
                    ->get(['skill_name', 'description', 'tag']);
                break;

            case 'name':
            case 'race_name':
            case 'goal':
                // UPDATED: Query now searches all plans, not just the user's.
                $suggestions = Plan::where($field, 'LIKE', "%{$query}%")
                    ->whereNotNull($field)
                    ->where($field, '!=', '')
                    ->distinct()
                    ->limit(10)
                    ->pluck($field);
                break;
        }

        return response()->json(['success' => true, 'suggestions' => $suggestions]);
    }
}
