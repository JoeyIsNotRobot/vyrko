<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsWithCareerState;
use App\Http\Requests\Career\CandidateSkillRequest;
use App\Models\CandidateSkill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CandidateSkillController extends Controller
{
    use RespondsWithCareerState;

    public function index(): RedirectResponse
    {
        return redirect()->route('career.index');
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('career.index');
    }

    public function store(CandidateSkillRequest $request): RedirectResponse|JsonResponse
    {
        $request->user()->candidateSkills()->create($request->validated());

        return $this->careerResponse($request, __('messages.career.ajax_ready'));
    }

    public function edit(CandidateSkill $skill): View
    {
        Gate::authorize('update', $skill);

        return view('career.resources.skill-edit', compact('skill'));
    }

    public function update(CandidateSkillRequest $request, CandidateSkill $skill): RedirectResponse|JsonResponse
    {
        Gate::authorize('update', $skill);
        $skill->update($request->validated());

        return $this->careerResponse($request, __('messages.career.ajax_ready'));
    }

    public function destroy(Request $request, CandidateSkill $skill): RedirectResponse|JsonResponse
    {
        Gate::authorize('delete', $skill);
        $skill->delete();

        return $this->careerResponse($request, __('messages.career.ajax_ready'));
    }
}
