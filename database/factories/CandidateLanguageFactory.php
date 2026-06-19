<?php

namespace Database\Factories;

use App\Models\CandidateLanguage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CandidateLanguage>
 */
class CandidateLanguageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'language' => 'Português',
            'proficiency' => 'Nativo/fluente',
            'notes' => null,
        ];
    }
}
