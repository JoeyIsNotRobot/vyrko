<?php

namespace App\Http\Controllers;

use App\Exceptions\UsageLimitExceededException;
use App\Http\Requests\Linkedin\LinkedinAnalysisRequest;
use App\Models\LinkedinAnalysisReport;
use App\Services\Ai\AiClient;
use App\Services\Ai\CareerInventoryFormatter;
use App\Services\UsageLimiter;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;

class LinkedinAnalysisController extends Controller
{
    public function __invoke(
        LinkedinAnalysisRequest $request,
        UsageLimiter $usageLimiter,
        AiClient $aiClient,
        CareerInventoryFormatter $formatter,
    ): RedirectResponse {
        $user = $request->user();
        $profile = $user->linkedinProfiles()->latest()->first();

        if (! $profile) {
            return back()->withErrors(['linkedin' => 'Cadastre um texto manual do LinkedIn antes de analisar.']);
        }

        try {
            $usageLimiter->ensureCan($user, UsageLimiter::LINKEDIN_ANALYSIS);
        } catch (UsageLimitExceededException $exception) {
            abort(403, $exception->getMessage());
        }

        try {
            $result = $aiClient->completeJson('linkedin_analysis', [
                'target_role' => $request->validated('target_role'),
                'target_language' => $request->validated('target_language'),
                'linkedin_profile' => $profile->only(['headline', 'about', 'experiences_text', 'skills_text', 'raw_text']),
                'inventory' => $formatter->forUser($user),
            ]);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['linkedin' => $exception->getMessage()]);
        }

        LinkedinAnalysisReport::create([
            'user_id' => $user->id,
            'linkedin_profile_id' => $profile->id,
            'target_role' => $request->validated('target_role'),
            'target_language' => $request->validated('target_language'),
            'score' => max(0, min(100, (int) ($result['score'] ?? 0))),
            'strengths' => $this->stringList($result['strengths'] ?? []),
            'weaknesses' => $this->stringList($result['weaknesses'] ?? []),
            'recommendations' => $this->stringList($result['recommendations'] ?? []),
            'rewritten_headline' => is_scalar($result['rewritten_headline'] ?? null) ? trim((string) $result['rewritten_headline']) : $profile->headline,
            'rewritten_about' => is_scalar($result['rewritten_about'] ?? null) ? trim((string) $result['rewritten_about']) : $profile->about,
        ]);

        $usageLimiter->consume($user, UsageLimiter::LINKEDIN_ANALYSIS);

        return redirect()->route('linkedin.index')->with('status', 'Análise do LinkedIn criada com IA.');
    }

    /**
     * @return array<int, string>
     */
    private function stringList(mixed $items): array
    {
        return collect(is_array($items) ? $items : [])
            ->filter(fn (mixed $item): bool => is_scalar($item))
            ->map(fn (mixed $item): string => trim((string) $item))
            ->filter()
            ->values()
            ->all();
    }
}
