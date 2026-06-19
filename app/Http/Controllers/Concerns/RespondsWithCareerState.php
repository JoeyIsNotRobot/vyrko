<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait RespondsWithCareerState
{
    protected function careerResponse(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if (! $request->expectsJson()) {
            return redirect()->route('career.index')->with('status', $message);
        }

        $user = $this->careerUser($request);

        return response()->json([
            'message' => $message,
            'html' => view('career.partials.inventory-list', ['user' => $user])->render(),
            'experienceOptions' => view('career.partials.experience-options', [
                'experiences' => $user->candidateExperiences,
            ])->render(),
        ]);
    }

    protected function careerUser(Request $request): mixed
    {
        return $request->user()->fresh()->load([
            'candidateProfile',
            'candidateExperiences.achievements',
            'candidateAchievements.experience',
            'candidateSkills',
            'candidateProjects',
            'candidateEducations',
            'candidateCertifications',
            'candidateLanguages',
        ]);
    }
}
