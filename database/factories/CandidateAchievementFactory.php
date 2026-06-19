<?php

namespace Database\Factories;

use App\Models\CandidateAchievement;
use App\Models\CandidateExperience;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CandidateAchievement>
 */
class CandidateAchievementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'candidate_experience_id' => null,
            'title' => 'Otimização de endpoints',
            'description' => 'Reduziu tempo de resposta de endpoints críticos ao otimizar queries MySQL e eager loading.',
            'impact_metric' => 'Tempo médio 35% menor',
            'evidence_tags' => ['Laravel', 'MySQL', 'performance'],
            'sort_order' => 1,
        ];
    }

    public function forExperience(CandidateExperience $experience): static
    {
        return $this->state(fn (): array => [
            'user_id' => $experience->user_id,
            'candidate_experience_id' => $experience->id,
        ]);
    }
}
