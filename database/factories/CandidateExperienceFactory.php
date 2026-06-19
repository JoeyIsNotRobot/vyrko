<?php

namespace Database\Factories;

use App\Models\CandidateExperience;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CandidateExperience>
 */
class CandidateExperienceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'company_name' => fake()->company(),
            'role_title' => 'Desenvolvedor Backend',
            'employment_type' => 'CLT',
            'location' => 'Remoto',
            'start_date' => now()->subYears(2)->startOfMonth(),
            'end_date' => null,
            'is_current' => true,
            'description' => 'Desenvolvimento de APIs REST com Laravel, MySQL e Redis.',
            'responsibilities' => ['Construção de APIs REST', 'Otimização de consultas MySQL', 'Manutenção de filas e cache Redis'],
            'technologies' => ['PHP', 'Laravel', 'MySQL', 'Redis', 'Docker'],
            'sort_order' => 1,
        ];
    }
}
