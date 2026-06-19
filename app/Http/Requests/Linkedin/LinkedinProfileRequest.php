<?php

namespace App\Http\Requests\Linkedin;

use Illuminate\Foundation\Http\FormRequest;

class LinkedinProfileRequest extends FormRequest
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
            'headline' => ['nullable', 'string', 'max:220'],
            'about' => ['nullable', 'string', 'max:5000'],
            'experiences_text' => ['nullable', 'string', 'max:12000'],
            'skills_text' => ['nullable', 'string', 'max:5000'],
            'raw_text' => ['nullable', 'string', 'max:20000'],
        ];
    }
}
