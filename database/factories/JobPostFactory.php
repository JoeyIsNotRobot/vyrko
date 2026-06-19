<?php

namespace Database\Factories;

use App\Models\JobPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobPost>
 */
class JobPostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => 'Backend Developer Laravel',
            'company_name' => fake()->company(),
            'job_description' => 'Buscamos pessoa desenvolvedora senior com experiência em PHP, Laravel, MySQL, Redis, Docker, APIs REST e testes automatizados.',
            'target_language' => 'pt_BR',
            'resume_type' => 'tech',
            'notes' => null,
        ];
    }
}
