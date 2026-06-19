<?php

namespace App\Http\Requests\Career;

use Illuminate\Foundation\Http\FormRequest;

class CandidateExperienceRequest extends FormRequest
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
            'company_name' => ['required', 'string', 'max:160'],
            'role_title' => ['required', 'string', 'max:160'],
            'employment_type' => ['nullable', 'string', 'max:80'],
            'location' => ['nullable', 'string', 'max:160'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_current' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:4000'],
            'responsibilities' => ['nullable'],
            'technologies' => ['nullable'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
