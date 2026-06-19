<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'password',
    'plan_name',
    'monthly_resume_generations_limit',
    'monthly_job_analysis_limit',
    'monthly_linkedin_analysis_limit',
    'ai_credits_balance',
    'password_set_at',
    'onboarding_completed_at',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'password_set_at' => 'datetime',
            'onboarding_completed_at' => 'datetime',
            'monthly_resume_generations_limit' => 'integer',
            'monthly_job_analysis_limit' => 'integer',
            'monthly_linkedin_analysis_limit' => 'integer',
            'ai_credits_balance' => 'integer',
        ];
    }

    public function candidateProfile(): HasOne
    {
        return $this->hasOne(CandidateProfile::class);
    }

    public function candidateExperiences(): HasMany
    {
        return $this->hasMany(CandidateExperience::class);
    }

    public function candidateAchievements(): HasMany
    {
        return $this->hasMany(CandidateAchievement::class);
    }

    public function candidateSkills(): HasMany
    {
        return $this->hasMany(CandidateSkill::class);
    }

    public function candidateProjects(): HasMany
    {
        return $this->hasMany(CandidateProject::class);
    }

    public function candidateEducations(): HasMany
    {
        return $this->hasMany(CandidateEducation::class);
    }

    public function candidateCertifications(): HasMany
    {
        return $this->hasMany(CandidateCertification::class);
    }

    public function candidateLanguages(): HasMany
    {
        return $this->hasMany(CandidateLanguage::class);
    }

    public function jobPosts(): HasMany
    {
        return $this->hasMany(JobPost::class);
    }

    public function jobMatchReports(): HasMany
    {
        return $this->hasMany(JobMatchReport::class);
    }

    public function resumeVersions(): HasMany
    {
        return $this->hasMany(ResumeVersion::class);
    }

    public function linkedinProfiles(): HasMany
    {
        return $this->hasMany(LinkedinProfile::class);
    }

    public function linkedinAnalysisReports(): HasMany
    {
        return $this->hasMany(LinkedinAnalysisReport::class);
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function usageLogs(): HasMany
    {
        return $this->hasMany(UsageLog::class);
    }

    public function hasPasswordLogin(): bool
    {
        return filled($this->password) && filled($this->password_set_at);
    }

    public function canDisconnectSocialAccount(?SocialAccount $account = null): bool
    {
        if ($this->hasPasswordLogin()) {
            return true;
        }

        return $this->socialAccounts()
            ->when($account, fn ($query) => $query->where('id', '!=', $account->getKey()))
            ->exists();
    }
}
