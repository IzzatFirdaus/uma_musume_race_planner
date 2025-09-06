<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlanRequest extends FormRequest
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
        // 'sometimes' allows for partial updates; only validates fields that are present.
        return [
            'plan' => 'sometimes|required|array',
            'plan.plan_title' => 'nullable|string|max:255',
            'plan.name' => 'sometimes|required|string|max:255',
            'plan.career_stage' => ['sometimes', 'required', Rule::in(['predebut', 'junior', 'classic', 'senior', 'finale'])],
            'plan.class' => ['nullable', Rule::in(['debut', 'maiden', 'beginner', 'bronze', 'silver', 'gold', 'platinum', 'star', 'legend'])],

            'attributes' => 'sometimes|nullable|array',
            'attributes.*.attribute_name' => 'required|string|max:50',
            'attributes.*.value' => 'required|integer',
            'attributes.*.grade' => 'nullable|string|max:10',

            'skills' => 'sometimes|nullable|array',
            'skills.*.skill_reference_id' => 'required|integer|exists:skill_reference,id',
            'skills.*.sp_cost' => 'nullable|string|max:50',
            'skills.*.acquired' => ['required', Rule::in(['yes', 'no'])],
            'skills.*.notes' => 'nullable|string',

            'goals' => 'sometimes|nullable|array',
            'goals.*.goal' => 'required|string|max:255',
            'goals.*.result' => 'nullable|string|max:255',
        ];
    }
}
