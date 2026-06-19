<?php

namespace App\Services\Ai;

use App\Models\AiRun;
use App\Models\JobPost;
use InvalidArgumentException;

class JobPostAnalyzer
{
    public function __construct(private readonly AiClient $aiClient) {}

    public function analyze(JobPost $jobPost): JobPost
    {
        $payload = [
            'title' => $jobPost->title,
            'company_name' => $jobPost->company_name,
            'job_description' => $jobPost->job_description,
            'target_language' => $jobPost->target_language,
            'resume_type' => $jobPost->resume_type,
        ];

        $result = $this->aiClient->completeJson('job_post_parse', $payload);
        $this->validate($result);

        $jobPost->forceFill([
            'company_name' => $jobPost->company_name ?: $result['company_name'],
            'parsed_requirements' => $result,
            'parsed_keywords' => $result['keywords'],
            'parsed_responsibilities' => $result['responsibilities'],
            'parsed_seniority' => $result['seniority'],
        ])->save();

        AiRun::create([
            'user_id' => $jobPost->user_id,
            'feature' => 'job_post_parse',
            'provider' => config('ai.provider', 'fake'),
            'model' => config('ai.model'),
            'prompt_hash' => hash('sha256', $jobPost->job_description),
            'input_tokens' => str_word_count($jobPost->job_description),
            'output_tokens' => str_word_count(json_encode($result, JSON_THROW_ON_ERROR)),
            'cost_estimate' => 0,
            'status' => 'succeeded',
            'created_at' => now(),
        ]);

        return $jobPost->refresh();
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function validate(array $result): void
    {
        foreach ([
            'job_title',
            'company_name',
            'seniority',
            'required_skills',
            'preferred_skills',
            'responsibilities',
            'soft_skills',
            'keywords',
            'ats_keywords',
            'language_requirements',
            'education_requirements',
            'hidden_signals',
        ] as $key) {
            if (! array_key_exists($key, $result)) {
                throw new InvalidArgumentException('A análise da vaga retornou um formato inválido.');
            }
        }
    }
}
