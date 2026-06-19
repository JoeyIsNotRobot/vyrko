<?php

namespace Database\Factories;

use App\Models\CandidateSkill;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CandidateSkill>
 */
class CandidateSkillFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => 'Laravel',
            'category' => 'backend',
            'proficiency_level' => 'advanced',
            'years_of_experience' => 4,
            'evidence_notes' => 'Usado em APIs REST, SaaS e testes automatizados.',
        ];
    }
}
