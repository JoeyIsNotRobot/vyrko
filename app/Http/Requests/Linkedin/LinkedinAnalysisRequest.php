<?php

namespace App\Http\Requests\Linkedin;

use Illuminate\Foundation\Http\FormRequest;

class LinkedinAnalysisRequest extends FormRequest
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
            'target_role' => ['nullable', 'string', 'max:180'],
            'target_language' => ['required', 'in:pt_BR,en'],
        ];
    }
}
