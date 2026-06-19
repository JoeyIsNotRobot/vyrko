<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $bestReport = $user->jobMatchReports()->orderByDesc('overall_score')->first();
        $profile = $user->candidateProfile;
        $missingInventory = collect([
            'profile' => ! $profile,
            'experiences' => $user->candidateExperiences()->count() === 0,
            'skills' => $user->candidateSkills()->count() === 0,
            'achievements' => $user->candidateAchievements()->count() === 0,
            'languages' => $user->candidateLanguages()->count() === 0,
        ])->filter()->keys()->values();

        return view('dashboard.index', [
            'resumeCount' => $user->resumeVersions()->count(),
            'jobCount' => $user->jobPosts()->count(),
            'latestResume' => $user->resumeVersions()->latest()->first(),
            'latestReport' => $user->jobMatchReports()->latest()->first(),
            'bestReport' => $bestReport,
            'missingInventory' => $missingInventory,
            'latestJobs' => $user->jobPosts()->latest()->limit(5)->get(),
            'latestResumes' => $user->resumeVersions()->with('jobPost')->latest()->limit(5)->get(),
        ]);
    }
}
