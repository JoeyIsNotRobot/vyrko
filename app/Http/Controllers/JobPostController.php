<?php

namespace App\Http\Controllers;

use App\Http\Requests\Jobs\JobPostRequest;
use App\Models\JobPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class JobPostController extends Controller
{
    public function index(Request $request): View
    {
        return view('jobs.index', [
            'jobs' => $request->user()->jobPosts()->latest()->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('jobs.create');
    }

    public function store(JobPostRequest $request): RedirectResponse
    {
        $jobPost = $request->user()->jobPosts()->create($request->validated());

        return redirect()->route('jobs.show', $jobPost)->with('status', 'Vaga cadastrada.');
    }

    public function show(JobPost $jobPost): View
    {
        Gate::authorize('view', $jobPost);
        $jobPost->load(['matchReports' => fn ($query) => $query->latest(), 'resumeVersions' => fn ($query) => $query->latest()]);

        return view('jobs.show', compact('jobPost'));
    }
}
