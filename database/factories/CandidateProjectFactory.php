<?php

namespace Database\Factories;

use App\Models\CandidateProject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CandidateProject>
 */
class CandidateProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => 'Plataforma SaaS interna',
            'description' => 'Projeto de API e painel administrativo para produto SaaS.',
            'role' => 'Backend Developer',
            'technologies' => ['Laravel', 'MySQL', 'Redis'],
            'url' => null,
            'repository_url' => null,
            'start_date' => now()->subYear()->startOfMonth(),
            'end_date' => null,
            'is_current' => true,
            'highlights' => ['APIs REST', 'Cache Redis', 'Testes automatizados'],
        ];
    }
}
