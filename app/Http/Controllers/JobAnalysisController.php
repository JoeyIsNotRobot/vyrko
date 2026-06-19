<?php

namespace App\Http\Controllers;

use App\Exceptions\UsageLimitExceededException;
use App\Models\JobPost;
use App\Services\Ai\JobMatchAnalyzer;
use App\Services\Ai\JobPostAnalyzer;
use App\Services\UsageLimiter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

class JobAnalysisController extends Controller
{
    public function __invoke(
        JobPost $jobPost,
        JobPostAnalyzer $jobPostAnalyzer,
        JobMatchAnalyzer $jobMatchAnalyzer,
        UsageLimiter $usageLimiter,
    ): RedirectResponse {
        Gate::authorize('update', $jobPost);
        $user = request()->user();

        try {
            $usageLimiter->ensureCan($user, UsageLimiter::JOB_ANALYSIS);
            $jobPost = $jobPostAnalyzer->analyze($jobPost);
            $jobMatchAnalyzer->analyze($user, $jobPost);
            $usageLimiter->consume($user, UsageLimiter::JOB_ANALYSIS, ['job_post_id' => $jobPost->id]);
        } catch (UsageLimitExceededException $exception) {
            abort(403, $exception->getMessage());
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['analysis' => $exception->getMessage()]);
        }

        return redirect()->route('jobs.show', $jobPost)->with('status', 'Análise da vaga criada.');
    }
}
