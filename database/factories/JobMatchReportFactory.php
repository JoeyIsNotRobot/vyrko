<?php

namespace Database\Factories;

use App\Models\JobMatchReport;
use App\Models\JobPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobMatchReport>
 */
class JobMatchReportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'job_post_id' => JobPost::factory(),
            'overall_score' => 82,
            'technical_score' => 85,
            'experience_score' => 80,
            'seniority_score' => 75,
            'keyword_score' => 80,
            'ats_format_score' => 88,
            'human_readability_score' => 82,
            'strengths' => ['Laravel com evidência forte.'],
            'gaps' => ['critical' => [], 'acceptable' => ['AWS']],
            'warnings' => ['Não mencionar AWS sem evidência.'],
            'recommendations' => ['Reforçar conquistas com métricas reais.'],
            'evidence_map' => [
                'Laravel' => [
                    'status' => 'strong_match',
                    'evidence' => [['type' => 'skill', 'id' => 1, 'label' => 'Laravel']],
                ],
            ],
        ];
    }
}
