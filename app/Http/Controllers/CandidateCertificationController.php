<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsWithCareerState;
use App\Http\Requests\Career\CandidateCertificationRequest;
use App\Models\CandidateCertification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CandidateCertificationController extends Controller
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

    public function store(CandidateCertificationRequest $request): RedirectResponse|JsonResponse
    {
        $request->user()->candidateCertifications()->create($request->validated());

        return $this->careerResponse($request, __('messages.career.ajax_ready'));
    }

    public function edit(CandidateCertification $certification): View
    {
        Gate::authorize('update', $certification);

        return view('career.resources.certification-edit', compact('certification'));
    }

    public function update(CandidateCertificationRequest $request, CandidateCertification $certification): RedirectResponse|JsonResponse
    {
        Gate::authorize('update', $certification);
        $certification->update($request->validated());

        return $this->careerResponse($request, __('messages.career.ajax_ready'));
    }

    public function destroy(Request $request, CandidateCertification $certification): RedirectResponse|JsonResponse
    {
        Gate::authorize('delete', $certification);
        $certification->delete();

        return $this->careerResponse($request, __('messages.career.ajax_ready'));
    }
}
