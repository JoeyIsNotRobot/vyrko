<?php

namespace App\Http\Requests\Career;

use Illuminate\Foundation\Http\FormRequest;

class CareerProfileRequest extends FormRequest
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
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'headline' => ['nullable', 'string', 'max:180'],
            'location_city' => ['nullable', 'string', 'max:120'],
            'location_state' => ['nullable', 'string', 'max:120'],
            'location_country' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:80'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'github_url' => ['nullable', 'url', 'max:255'],
            'portfolio_url' => ['nullable', 'url', 'max:255'],
            'summary' => ['nullable', 'string', 'max:4000'],
            'target_role' => ['nullable', 'string', 'max:160'],
            'target_seniority' => ['nullable', 'string', 'max:80'],
            'professional_area' => ['nullable', 'string', 'max:120'],
            'preferred_language' => ['required', 'in:pt_BR,en'],
        ];
    }
}
