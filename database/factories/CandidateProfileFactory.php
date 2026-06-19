<?php

namespace Database\Factories;

use App\Models\CandidateProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CandidateProfile>
 */
class CandidateProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'headline' => 'Desenvolvedor Backend PHP/Laravel',
            'location_city' => 'São Paulo',
            'location_state' => 'SP',
            'location_country' => 'Brasil',
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'linkedin_url' => 'https://www.linkedin.com/in/demo',
            'github_url' => 'https://github.com/demo',
            'portfolio_url' => null,
            'summary' => 'Desenvolvedor backend com experiência em SaaS, APIs REST e performance.',
            'target_role' => 'Backend Developer',
            'target_seniority' => 'senior',
            'preferred_language' => 'pt_BR',
        ];
    }
}
