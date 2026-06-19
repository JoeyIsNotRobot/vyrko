<?php

namespace App\Services\Ai;

use App\Models\User;

class CareerInventoryFormatter
{
    /**
     * @return array<string, mixed>
     */
    public function forUser(User $user): array
    {
        $user->load([
            'candidateProfile',
            'candidateExperiences.achievements',
            'candidateAchievements',
            'candidateSkills',
            'candidateProjects',
            'candidateEducations',
            'candidateCertifications',
            'candidateLanguages',
        ]);

        return [
            'profile' => $user->candidateProfile?->toArray(),
            'experiences' => $user->candidateExperiences->sortBy('sort_order')->values()->toArray(),
            'achievements' => $user->candidateAchievements->sortBy('sort_order')->values()->toArray(),
            'skills' => $user->candidateSkills->values()->toArray(),
            'projects' => $user->candidateProjects->values()->toArray(),
            'educations' => $user->candidateEducations->values()->toArray(),
            'certifications' => $user->candidateCertifications->values()->toArray(),
            'languages' => $user->candidateLanguages->values()->toArray(),
        ];
    }
}
