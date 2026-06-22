<?php

namespace App\Http\Requests\Jobs;

use Illuminate\Foundation\Http\FormRequest;

class JobPostRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:180'],
            'company_name' => ['nullable', 'string', 'max:180'],
            'job_description' => ['required', 'string', 'min:20', 'max:30000'],
            'target_language' => ['required', 'in:pt_BR,en'],
            'resume_type' => ['required', 'in:national_brazil,international,tech,executive'],
            'notes' => ['nullable', 'string', 'max:4000'],
            'linkedin_url' => ['nullable', 'string', 'url', 'max:500', 'regex:/linkedin\.com\/jobs/'],
        ];
    }
}
