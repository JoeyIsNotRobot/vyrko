<?php

namespace Database\Factories;

use App\Models\CandidateEducation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CandidateEducation>
 */
class CandidateEducationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'institution' => fake()->company(),
            'degree' => 'Análise e Desenvolvimento de Sistemas',
            'field_of_study' => 'Tecnologia',
            'start_date' => now()->subYears(3)->startOfMonth(),
            'end_date' => now()->subYears(2)->startOfMonth(),
            'is_current' => false,
            'description' => null,
        ];
    }
}
