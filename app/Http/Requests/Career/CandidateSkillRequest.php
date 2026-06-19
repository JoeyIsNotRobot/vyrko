<?php

namespace App\Http\Requests\Career;

use App\Models\CandidateSkill;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CandidateSkillRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:120'],
            'category' => ['required', Rule::in(CandidateSkill::CATEGORIES)],
            'proficiency_level' => ['nullable', 'string', 'max:80'],
            'years_of_experience' => ['nullable', 'integer', 'min:0', 'max:60'],
            'evidence_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
