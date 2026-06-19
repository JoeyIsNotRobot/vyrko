<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ParsesCareerInput;
use App\Http\Controllers\Concerns\RespondsWithCareerState;
use App\Http\Requests\Career\CandidateAchievementRequest;
use App\Models\CandidateAchievement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CandidateAchievementController extends Controller
{
    use ParsesCareerInput, RespondsWithCareerState;

    public function index(): RedirectResponse
    {
        return redirect()->route('career.index');
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('career.index');
    }

    public function store(CandidateAchievementRequest $request): RedirectResponse|JsonResponse
    {
        $experienceId = $this->safeExperienceId($request);
        $request->user()->candidateAchievements()->create([
            ...$request->safe()->except(['evidence_tags', 'candidate_experience_id']),
            'candidate_experience_id' => $experienceId,
            'evidence_tags' => $this->listFrom($request->input('evidence_tags')),
            'sort_order' => (int) $request->input('sort_order', 0),
        ]);

        return $this->careerResponse($request, __('messages.career.ajax_ready'));
    }

    public function edit(CandidateAchievement $achievement): View
    {
        Gate::authorize('update', $achievement);

        return view('career.resources.achievement-edit', compact('achievement'));
    }

    public function update(CandidateAchievementRequest $request, CandidateAchievement $achievement): RedirectResponse|JsonResponse
    {
        Gate::authorize('update', $achievement);
        $achievement->update([
            ...$request->safe()->except(['evidence_tags', 'candidate_experience_id']),
            'candidate_experience_id' => $this->safeExperienceId($request),
            'evidence_tags' => $this->listFrom($request->input('evidence_tags')),
            'sort_order' => (int) $request->input('sort_order', 0),
        ]);

        return $this->careerResponse($request, __('messages.career.ajax_ready'));
    }

    public function destroy(Request $request, CandidateAchievement $achievement): RedirectResponse|JsonResponse
    {
        Gate::authorize('delete', $achievement);
        $achievement->delete();

        return $this->careerResponse($request, __('messages.career.ajax_ready'));
    }

    private function safeExperienceId(Request $request): ?int
    {
        $id = $request->integer('candidate_experience_id') ?: null;

        if (! $id) {
            return null;
        }

        return $request->user()->candidateExperiences()->whereKey($id)->exists() ? $id : null;
    }
}
