<?php

namespace Database\Factories;

use App\Models\JobMatchReport;
use App\Models\JobPost;
use App\Models\ResumeVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResumeVersion>
 */
class ResumeVersionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'job_post_id' => JobPost::factory(),
            'job_match_report_id' => JobMatchReport::factory(),
            'title' => 'Curriculo para Backend Developer Laravel',
            'language' => 'pt_BR',
            'resume_type' => 'tech',
            'content' => [
                'header' => ['name' => 'Demo User', 'headline' => 'Backend Developer', 'email' => 'demo@example.com'],
                'summary' => 'Resumo profissional.',
                'skills' => [['category' => 'backend', 'items' => ['Laravel']]],
                'experiences' => [],
            ],
            'plain_text' => 'Demo User Backend Developer Laravel',
            'status' => 'generated',
            'ats_checklist' => null,
        ];
    }
}
