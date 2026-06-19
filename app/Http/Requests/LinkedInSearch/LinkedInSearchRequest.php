<?php

namespace App\Http\Requests\LinkedInSearch;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class LinkedInSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'titles'        => ['array', 'max:10'],
            'titles.*'      => ['string', 'max:80'],
            'skills'        => ['array', 'max:15'],
            'skills.*'      => ['string', 'max:80'],
            'seniorities'   => ['array'],
            'seniorities.*' => ['string', 'max:50'],
            'work_modes'    => ['array'],
            'work_modes.*'  => ['string', 'max:50'],
            'locations'     => ['array', 'max:8'],
            'locations.*'   => ['string', 'max:80'],
            'language'      => ['required', Rule::in(['pt', 'en', 'both'])],
            'excluded'      => ['array', 'max:10'],
            'excluded.*'    => ['string', 'max:80'],
            'niche'         => ['nullable', 'string', 'max:50'],
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'The given data was invalid.',
                'errors'  => $validator->errors()->toArray(),
            ], 422)
        );
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $v): void {
            $titles = array_filter((array) $this->input('titles', []));
            $skills = array_filter((array) $this->input('skills', []));

            if (empty($titles) && empty($skills)) {
                $v->errors()->add('titles', 'Adicione pelo menos um cargo ou uma habilidade.');
            }
        });
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'language.required' => 'Selecione o idioma das vagas.',
            'language.in'       => 'Idioma inválido.',
        ];
    }
}
