<?php

namespace App\Http\Requests\Career;

use Illuminate\Foundation\Http\FormRequest;

class CandidateAchievementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'candidate_experience_id' => ['nullable', 'integer', 'exists:candidate_experiences,id'],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string', 'max:4000'],
            'impact_metric' => ['nullable', 'string', 'max:180'],
            'evidence_tags' => ['nullable'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
