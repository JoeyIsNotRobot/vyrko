<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ParsesCareerInput;
use App\Http\Controllers\Concerns\RespondsWithCareerState;
use App\Http\Requests\Career\CandidateProjectRequest;
use App\Models\CandidateProject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CandidateProjectController extends Controller
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

    public function store(CandidateProjectRequest $request): RedirectResponse|JsonResponse
    {
        $request->user()->candidateProjects()->create($this->payload($request));

        return $this->careerResponse($request, __('messages.career.ajax_ready'));
    }

    public function edit(CandidateProject $project): View
    {
        Gate::authorize('update', $project);

        return view('career.resources.project-edit', compact('project'));
    }

    public function update(CandidateProjectRequest $request, CandidateProject $project): RedirectResponse|JsonResponse
    {
        Gate::authorize('update', $project);
        $project->update($this->payload($request));

        return $this->careerResponse($request, __('messages.career.ajax_ready'));
    }

    public function destroy(Request $request, CandidateProject $project): RedirectResponse|JsonResponse
    {
        Gate::authorize('delete', $project);
        $project->delete();

        return $this->careerResponse($request, __('messages.career.ajax_ready'));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Request $request): array
    {
        return [
            ...$request->safe()->except(['technologies', 'highlights', 'is_current']),
            'technologies' => $this->listFrom($request->input('technologies')),
            'highlights' => $this->listFrom($request->input('highlights')),
            'is_current' => $request->boolean('is_current'),
        ];
    }
}
