<?php

namespace App\Providers;

use App\Models\CandidateAchievement;
use App\Models\CandidateCertification;
use App\Models\CandidateEducation;
use App\Models\CandidateExperience;
use App\Models\CandidateLanguage;
use App\Models\CandidateProfile;
use App\Models\CandidateProject;
use App\Models\CandidateSkill;
use App\Models\JobMatchReport;
use App\Models\JobPost;
use App\Models\LinkedinAnalysisReport;
use App\Models\LinkedinProfile;
use App\Models\ResumeVersion;
use App\Models\SocialAccount;
use App\Policies\OwnedResourcePolicy;
use App\Services\Ai\AiClient;
use App\Services\Ai\FakeAiClient;
use App\Services\Ai\GeminiAiClient;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\LinkedIn\Provider as LinkedInProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AiClient::class, function () {
            return match (config('ai.provider', 'gemini')) {
                'fake' => app(FakeAiClient::class),
                'gemini' => app(GeminiAiClient::class),
                default => app(GeminiAiClient::class),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach ([
            CandidateProfile::class,
            CandidateExperience::class,
            CandidateAchievement::class,
            CandidateSkill::class,
            CandidateProject::class,
            CandidateEducation::class,
            CandidateCertification::class,
            CandidateLanguage::class,
            JobPost::class,
            JobMatchReport::class,
            ResumeVersion::class,
            LinkedinProfile::class,
            LinkedinAnalysisReport::class,
            SocialAccount::class,
        ] as $model) {
            Gate::policy($model, OwnedResourcePolicy::class);
        }

        Event::listen(function (SocialiteWasCalled $event): void {
            $event->extendSocialite('linkedin', LinkedInProvider::class);
        });
    }
}
