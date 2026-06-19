<?php

namespace App\Http\Controllers;

use App\Exceptions\UsageLimitExceededException;
use App\Models\JobPost;
use App\Models\ResumeVersion;
use App\Services\Ai\AtsChecklistAnalyzer;
use App\Services\Ai\JobMatchAnalyzer;
use App\Services\Ai\JobPostAnalyzer;
use App\Services\Ai\ResumeGenerator;
use App\Services\UsageLimiter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use InvalidArgumentException;

class ResumeVersionController extends Controller
{
    /**
     * @return array<string, array<string, string>>
     */
    public static function templateDefinitions(): array
    {
        return [
            'ats-classic' => [
                'name_pt' => 'ATS Clássico',
                'name_en' => 'ATS Classic',
                'description_pt' => 'Formato limpo, textual e seguro para sistemas ATS.',
                'description_en' => 'Clean, text-first format that stays ATS-safe.',
                'best_for_pt' => 'Vagas corporativas e processos com triagem automática.',
                'best_for_en' => 'Corporate roles and automated screening flows.',
                'tag_pt' => 'Mais seguro para ATS',
                'tag_en' => 'Safest for ATS',
            ],
            'tech-compact' => [
                'name_pt' => 'Tech Compacto',
                'name_en' => 'Tech Compact',
                'description_pt' => 'Denso, objetivo e orientado a stack para perfis técnicos.',
                'description_en' => 'Dense, focused, stack-oriented layout for tech profiles.',
                'best_for_pt' => 'Engenharia, dados, produto e liderança técnica.',
                'best_for_en' => 'Engineering, data, product, and technical leadership.',
                'tag_pt' => 'Ideal para vagas tech',
                'tag_en' => 'Ideal for tech roles',
            ],
            'international-clean' => [
                'name_pt' => 'Internacional Clean',
                'name_en' => 'International Clean',
                'description_pt' => 'Visual elegante com linguagem global e leitura humana forte.',
                'description_en' => 'Elegant global format with strong human readability.',
                'best_for_pt' => 'Aplicações internacionais, remotas e consultoria.',
                'best_for_en' => 'International, remote, and consulting applications.',
                'tag_pt' => 'Melhor para vagas internacionais',
                'tag_en' => 'Best for international roles',
            ],
        ];
    }

    public function index(Request $request): View
    {
        return view('resumes.index', [
            'resumes' => $request->user()->resumeVersions()->with('jobPost')->latest()->paginate(10),
        ]);
    }

    public function show(ResumeVersion $resumeVersion): View
    {
        Gate::authorize('view', $resumeVersion);
        $resumeVersion->load(['jobPost', 'jobMatchReport']);

        return view('resumes.show', [
            'resumeVersion' => $resumeVersion,
            'templates' => self::templateDefinitions(),
        ]);
    }

    public function templates(ResumeVersion $resumeVersion): View
    {
        Gate::authorize('view', $resumeVersion);
        $resumeVersion->load(['jobPost', 'jobMatchReport']);

        return view('resumes.templates', [
            'resumeVersion' => $resumeVersion,
            'templates' => self::templateDefinitions(),
        ]);
    }

    public function preview(ResumeVersion $resumeVersion, string $template): View
    {
        Gate::authorize('view', $resumeVersion);
        $resumeVersion->load(['jobPost', 'jobMatchReport']);

        return view('resumes.preview', [
            'resumeVersion' => $resumeVersion,
            'template' => $this->validatedTemplate($template),
            'templates' => self::templateDefinitions(),
        ]);
    }

    public function print(ResumeVersion $resumeVersion, string $template): View
    {
        Gate::authorize('view', $resumeVersion);
        $resumeVersion->load(['jobPost', 'jobMatchReport']);

        return view('resumes.print', [
            'resumeVersion' => $resumeVersion,
            'template' => $this->validatedTemplate($template),
            'templates' => self::templateDefinitions(),
        ]);
    }

    public function storeForJob(
        JobPost $jobPost,
        UsageLimiter $usageLimiter,
        JobPostAnalyzer $jobPostAnalyzer,
        JobMatchAnalyzer $jobMatchAnalyzer,
        ResumeGenerator $resumeGenerator,
        AtsChecklistAnalyzer $atsChecklistAnalyzer,
    ): RedirectResponse {
        Gate::authorize('update', $jobPost);
        $user = request()->user();

        try {
            $usageLimiter->ensureCan($user, UsageLimiter::RESUME_GENERATION);
        } catch (UsageLimitExceededException $exception) {
            abort(403, $exception->getMessage());
        }

        try {
            if (! $jobPost->parsed_requirements) {
                $jobPost = $jobPostAnalyzer->analyze($jobPost);
            }

            $report = $jobPost->matchReports()->latest()->first()
                ?: $jobMatchAnalyzer->analyze($user, $jobPost);

            $resume = $resumeGenerator->generate($user, $jobPost, $report);
            $atsChecklistAnalyzer->analyze($resume);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['generation' => $exception->getMessage()]);
        }

        $usageLimiter->consume($user, UsageLimiter::RESUME_GENERATION, ['job_post_id' => $jobPost->id, 'resume_version_id' => $resume->id]);

        return redirect()->route('resumes.templates', $resume)->with('status', 'Currículo gerado. Escolha um modelo para visualizar ou imprimir.');
    }

    public function runAtsCheck(ResumeVersion $resumeVersion, AtsChecklistAnalyzer $analyzer): RedirectResponse
    {
        Gate::authorize('update', $resumeVersion);

        try {
            $analyzer->analyze($resumeVersion);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['ats' => $exception->getMessage()]);
        }

        return redirect()->route('resumes.show', $resumeVersion)->with('status', 'Checklist ATS atualizado.');
    }

    private function validatedTemplate(string $template): string
    {
        abort_unless(array_key_exists($template, self::templateDefinitions()), 404);

        return $template;
    }
}
