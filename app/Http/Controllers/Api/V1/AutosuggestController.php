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
        $suggestions = $this->getSuggestions($field, $query);

        return response()->json(['success' => true, 'suggestions' => $suggestions]);
    }

    private function getSuggestions(string $field, string $query)
    {
        if ($field === 'skill_name') {
            return $this->getSkillSuggestions($query);
        }
        if (in_array($field, ['name', 'race_name', 'goal'], true)) {
            return $this->getPlanSuggestions($field, $query);
        }

        return [];
    }

    private function getSkillSuggestions(string $query)
    {
        return SkillReference::where('skill_name', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get(['skill_name', 'description', 'tag']);
    }

    private function getPlanSuggestions(string $field, string $query)
    {
        return Plan::where($field, 'LIKE', "%{$query}%")
            ->whereNotNull($field)
            ->where($field, '!=', '')
            ->distinct()
            ->limit(10)
            ->pluck($field);
    }
}
