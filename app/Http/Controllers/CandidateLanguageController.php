<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsWithCareerState;
use App\Http\Requests\Career\CandidateLanguageRequest;
use App\Models\CandidateLanguage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CandidateLanguageController extends Controller
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

    public function store(CandidateLanguageRequest $request): RedirectResponse|JsonResponse
    {
        $request->user()->candidateLanguages()->create($request->validated());

        return $this->careerResponse($request, __('messages.career.ajax_ready'));
    }

    public function edit(CandidateLanguage $language): View
    {
        Gate::authorize('update', $language);

        return view('career.resources.language-edit', compact('language'));
    }

    public function update(CandidateLanguageRequest $request, CandidateLanguage $language): RedirectResponse|JsonResponse
    {
        Gate::authorize('update', $language);
        $language->update($request->validated());

        return $this->careerResponse($request, __('messages.career.ajax_ready'));
    }

    public function destroy(Request $request, CandidateLanguage $language): RedirectResponse|JsonResponse
    {
        Gate::authorize('delete', $language);
        $language->delete();

        return $this->careerResponse($request, __('messages.career.ajax_ready'));
    }
}
