<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsWithCareerState;
use App\Http\Requests\Career\CandidateEducationRequest;
use App\Models\CandidateEducation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CandidateEducationController extends Controller
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

    public function store(CandidateEducationRequest $request): RedirectResponse|JsonResponse
    {
        $request->user()->candidateEducations()->create([
            ...$request->safe()->except('is_current'),
            'is_current' => $request->boolean('is_current'),
        ]);

        return $this->careerResponse($request, __('messages.career.ajax_ready'));
    }

    public function edit(CandidateEducation $education): View
    {
        Gate::authorize('update', $education);

        return view('career.resources.education-edit', compact('education'));
    }

    public function update(CandidateEducationRequest $request, CandidateEducation $education): RedirectResponse|JsonResponse
    {
        Gate::authorize('update', $education);
        $education->update([
            ...$request->safe()->except('is_current'),
            'is_current' => $request->boolean('is_current'),
        ]);

        return $this->careerResponse($request, __('messages.career.ajax_ready'));
    }

    public function destroy(Request $request, CandidateEducation $education): RedirectResponse|JsonResponse
    {
        Gate::authorize('delete', $education);
        $education->delete();

        return $this->careerResponse($request, __('messages.career.ajax_ready'));
    }
}
