<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ParsesCareerInput;
use App\Http\Controllers\Concerns\RespondsWithCareerState;
use App\Http\Requests\Career\CandidateExperienceRequest;
use App\Models\CandidateExperience;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CandidateExperienceController extends Controller
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

    public function store(CandidateExperienceRequest $request): RedirectResponse|JsonResponse
    {
        $data = $this->payload($request);
        $request->user()->candidateExperiences()->create($data);

        return $this->careerResponse($request, __('messages.career.ajax_ready'));
    }

    public function edit(CandidateExperience $experience): View
    {
        Gate::authorize('update', $experience);

        return view('career.resources.experience-edit', compact('experience'));
    }

    public function update(CandidateExperienceRequest $request, CandidateExperience $experience): RedirectResponse|JsonResponse
    {
        Gate::authorize('update', $experience);
        $experience->update($this->payload($request));

        return $this->careerResponse($request, __('messages.career.ajax_ready'));
    }

    public function destroy(Request $request, CandidateExperience $experience): RedirectResponse|JsonResponse
    {
        Gate::authorize('delete', $experience);
        $experience->delete();

        return $this->careerResponse($request, __('messages.career.ajax_ready'));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Request $request): array
    {
        return [
            ...$request->safe()->except(['responsibilities', 'technologies', 'is_current']),
            'responsibilities' => $this->listFrom($request->input('responsibilities')),
            'technologies' => $this->listFrom($request->input('technologies')),
            'is_current' => $request->boolean('is_current'),
            'sort_order' => (int) $request->input('sort_order', 0),
        ];
    }
}
