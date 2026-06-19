<?php

namespace App\Services;

use App\Exceptions\UsageLimitExceededException;
use App\Models\UsageLog;
use App\Models\User;

class UsageLimiter
{
    public const JOB_ANALYSIS = 'job_analysis';

    public const RESUME_GENERATION = 'resume_generation';

    public const LINKEDIN_ANALYSIS = 'linkedin_analysis';

    public function ensureCan(User $user, string $feature): void
    {
        $limit = $this->limitFor($user, $feature);
        $used = $this->usedThisMonth($user, $feature);

        if ($used >= $limit && $user->ai_credits_balance < 1) {
            throw new UsageLimitExceededException('Limite mensal atingido para este recurso.');
        }
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function consume(User $user, string $feature, array $metadata = []): void
    {
        $limit = $this->limitFor($user, $feature);
        $used = $this->usedThisMonth($user, $feature);

        if ($used >= $limit && $user->ai_credits_balance > 0) {
            $user->decrement('ai_credits_balance');
        }

        UsageLog::create([
            'user_id' => $user->id,
            'feature' => $feature,
            'status' => 'consumed',
            'metadata' => $metadata,
        ]);
    }

    private function limitFor(User $user, string $feature): int
    {
        return match ($feature) {
            self::JOB_ANALYSIS => (int) ($user->monthly_job_analysis_limit ?? 10),
            self::RESUME_GENERATION => (int) ($user->monthly_resume_generations_limit ?? 5),
            self::LINKEDIN_ANALYSIS => (int) ($user->monthly_linkedin_analysis_limit ?? 2),
            default => 0,
        };
    }

    private function usedThisMonth(User $user, string $feature): int
    {
        return UsageLog::query()
            ->where('user_id', $user->id)
            ->where('feature', $feature)
            ->where('status', 'consumed')
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();
    }
}
