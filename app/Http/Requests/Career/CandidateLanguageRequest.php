<?php

namespace App\Http\Requests\Career;

use Illuminate\Foundation\Http\FormRequest;

class CandidateLanguageRequest extends FormRequest
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
            'language' => ['required', 'string', 'max:120'],
            'proficiency' => ['required', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
