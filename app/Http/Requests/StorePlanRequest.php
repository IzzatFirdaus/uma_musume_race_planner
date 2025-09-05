<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization is handled by the controller's middleware and policies.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'plan' => 'required|array',
            'plan.plan_title' => 'nullable|string|max:255',
            'plan.name' => 'required|string|max:255',
            'plan.career_stage' => ['required', Rule::in(['predebut', 'junior', 'classic', 'senior', 'finale'])],
            'plan.class' => ['nullable', Rule::in(['debut', 'maiden', 'beginner', 'bronze', 'silver', 'gold', 'platinum', 'star', 'legend'])],
            // Add other 'plan.*' rules as needed...

            'attributes' => 'nullable|array',
            'attributes.*.attribute_name' => 'required|string|max:50',
            'attributes.*.value' => 'required|integer',
            'attributes.*.grade' => 'nullable|string|max:10',

            'skills' => 'nullable|array',
            'skills.*.skill_reference_id' => 'required|integer|exists:skill_reference,id',
            'skills.*.sp_cost' => 'nullable|string|max:50',
            'skills.*.acquired' => ['required', Rule::in(['yes', 'no'])],
            'skills.*.notes' => 'nullable|string',

            // Add rules for goals, racePredictions, grades, etc. in a similar wildcard format.
            'goals' => 'nullable|array',
            'goals.*.goal' => 'required|string|max:255',
            'goals.*.result' => 'nullable|string|max:255',
        ];
    }
}
