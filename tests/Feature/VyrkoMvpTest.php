<?php

namespace Tests\Feature;

use App\Models\CandidateExperience;
use App\Models\CandidateProfile;
use App\Models\CandidateSkill;
use App\Models\JobMatchReport;
use App\Models\JobPost;
use App\Models\ResumeVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class VyrkoMvpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_usuario_nao_autenticado_nao_acessa_dashboard(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_usuario_nao_acessa_inventario_de_outro_usuario(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $experience = CandidateExperience::factory()->for($owner)->create();

        $this->actingAs($other)
            ->get(route('experiences.edit', $experience))
            ->assertForbidden();
    }

    public function test_usuario_consegue_criar_experiencia(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('experiences.store'), [
                'company_name' => 'Acme SaaS',
                'role_title' => 'Backend Developer',
                'employment_type' => 'PJ',
                'location' => 'Remoto',
                'start_date' => '2024-01-01',
                'description' => 'Desenvolvimento de APIs Laravel.',
                'responsibilities' => "Criar APIs\nOtimizar queries",
                'technologies' => 'Laravel, MySQL, Redis',
            ])
            ->assertRedirect(route('career.index'));

        $this->assertDatabaseHas('candidate_experiences', [
            'user_id' => $user->id,
            'company_name' => 'Acme SaaS',
            'role_title' => 'Backend Developer',
        ]);
    }

    public function test_usuario_consegue_criar_skill(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('skills.store'), [
                'name' => 'Laravel',
                'category' => 'backend',
                'proficiency_level' => 'advanced',
                'years_of_experience' => 4,
                'evidence_notes' => 'APIs REST em produção.',
            ])
            ->assertRedirect(route('career.index'));

        $this->assertDatabaseHas('candidate_skills', [
            'user_id' => $user->id,
            'name' => 'Laravel',
        ]);
    }

    public function test_usuario_consegue_cadastrar_vaga(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('jobs.store'), $this->jobPayload())
            ->assertRedirect();

        $this->assertDatabaseHas('job_posts', [
            'user_id' => $user->id,
            'title' => 'Backend Laravel',
        ]);
    }

    public function test_analise_de_vaga_salva_json_estruturado(): void
    {
        $user = User::factory()->create();
        CandidateSkill::factory()->for($user)->create(['name' => 'Laravel']);
        $jobPost = JobPost::factory()->for($user)->create($this->jobPayload());

        $this->actingAs($user)
            ->post(route('jobs.analyze', $jobPost))
            ->assertRedirect(route('jobs.show', $jobPost));

        $jobPost->refresh();

        $this->assertNotNull($jobPost->parsed_requirements);
        $this->assertArrayHasKey('required_skills', $jobPost->parsed_requirements);
        $this->assertContains('Laravel', $jobPost->parsed_requirements['required_skills']);
    }

    public function test_relatorio_de_match_e_criado(): void
    {
        $user = User::factory()->create();
        CandidateSkill::factory()->for($user)->create(['name' => 'Laravel']);
        CandidateExperience::factory()->for($user)->create();
        $jobPost = JobPost::factory()->for($user)->create($this->jobPayload());

        $this->actingAs($user)
            ->post(route('jobs.analyze', $jobPost))
            ->assertRedirect(route('jobs.show', $jobPost));

        $this->assertDatabaseHas('job_match_reports', [
            'user_id' => $user->id,
            'job_post_id' => $jobPost->id,
        ]);

        $report = JobMatchReport::first();
        $this->assertArrayHasKey('Laravel', $report->evidence_map);
    }

    public function test_geracao_de_curriculo_cria_resume_versions(): void
    {
        [$user, $jobPost] = $this->userWithInventoryAndAnalyzedJob();

        $this->actingAs($user)
            ->post(route('jobs.generate-resume', $jobPost))
            ->assertRedirect();

        $this->assertDatabaseHas('resume_versions', [
            'user_id' => $user->id,
            'job_post_id' => $jobPost->id,
            'status' => 'generated',
        ]);

        $this->assertNotNull(ResumeVersion::first()->ats_checklist);
    }

    public function test_curriculo_gerado_nao_usa_habilidade_ausente(): void
    {
        $user = User::factory()->create();
        CandidateProfile::factory()->for($user)->create();
        CandidateSkill::factory()->for($user)->create(['name' => 'Laravel']);
        CandidateExperience::factory()->for($user)->create([
            'description' => 'APIs REST com Laravel e MySQL.',
            'technologies' => ['Laravel', 'MySQL'],
        ]);
        $jobPost = JobPost::factory()->for($user)->create([
            ...$this->jobPayload(),
            'job_description' => 'Vaga para pessoa backend com Laravel, MySQL e AWS em ambiente cloud.',
        ]);

        $this->actingAs($user)->post(route('jobs.analyze', $jobPost));
        $this->actingAs($user)->post(route('jobs.generate-resume', $jobPost));

        $resume = ResumeVersion::first();

        $this->assertStringContainsString('Laravel', $resume->plain_text);
        $this->assertStringNotContainsString('AWS', $resume->plain_text);
    }

    public function test_limite_de_uso_bloqueia_geracao_quando_excedido(): void
    {
        [$user, $jobPost] = $this->userWithInventoryAndAnalyzedJob([
            'monthly_resume_generations_limit' => 0,
            'ai_credits_balance' => 0,
        ]);

        $this->actingAs($user)
            ->post(route('jobs.generate-resume', $jobPost))
            ->assertForbidden();
    }

    public function test_inventario_cria_skill_via_ajax_sem_recarregar(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('skills.store'), [
                'name' => 'PHPUnit',
                'category' => 'testing',
                'proficiency_level' => 'advanced',
                'years_of_experience' => 3,
                'evidence_notes' => 'Testes unitários e de integração.',
            ]);

        $response->assertOk()
            ->assertJsonStructure(['message', 'html', 'experienceOptions'])
            ->assertSee('PHPUnit', false);

        $this->assertDatabaseHas('candidate_skills', [
            'user_id' => $user->id,
            'name' => 'PHPUnit',
        ]);
    }

    public function test_importacao_de_curriculo_txt_absorve_informacoes(): void
    {
        $user = User::factory()->create(['email' => 'hectorcoelho@hotmail.com']);
        $file = UploadedFile::fake()->createWithContent('hector.txt', $this->hectorResumeText());

        $response = $this->actingAs($user)
            ->post(route('career.import'), ['resume' => $file], ['Accept' => 'application/json']);

        $response->assertOk()->assertJsonFragment([
            'message' => __('messages.career.imported'),
        ]);

        $this->assertDatabaseHas('candidate_profiles', [
            'user_id' => $user->id,
            'first_name' => 'Héctor',
            'last_name' => 'Coelho',
        ]);
        $this->assertDatabaseHas('candidate_experiences', [
            'user_id' => $user->id,
            'company_name' => 'Prime Coaching',
        ]);
        $this->assertDatabaseHas('candidate_skills', [
            'user_id' => $user->id,
            'name' => 'Laravel',
        ]);
        $this->assertDatabaseHas('candidate_languages', [
            'user_id' => $user->id,
            'language' => 'Inglês',
        ]);
    }

    public function test_interface_traduz_labels_tecnicos(): void
    {
        $user = User::factory()->create();
        $jobPost = JobPost::factory()->for($user)->create();
        $report = JobMatchReport::factory()->for($user)->for($jobPost)->create([
            'evidence_map' => [
                'Laravel' => [
                    'status' => 'strong_match',
                    'evidence' => [],
                ],
            ],
        ]);
        $resume = ResumeVersion::factory()->for($user)->for($jobPost)->for($report, 'jobMatchReport')->create([
            'ats_checklist' => [
                'score' => 90,
                'items' => [
                    ['key' => 'no_tables', 'status' => 'passed', 'message' => 'Sem tabelas no conteúdo gerado.'],
                ],
            ],
        ]);

        $this->actingAs($user)
            ->withSession(['locale' => 'pt_BR'])
            ->get(route('resumes.show', $resume))
            ->assertOk()
            ->assertSee('Sem tabelas')
            ->assertSee('Aprovado')
            ->assertSee('Evidência forte')
            ->assertDontSee('no_tables')
            ->assertDontSee('strong_match');
    }

    public function test_usuario_visualiza_modelos_preview_e_impressao_do_curriculo(): void
    {
        $user = User::factory()->create();
        $jobPost = JobPost::factory()->for($user)->create();
        $report = JobMatchReport::factory()->for($user)->for($jobPost)->create();
        $resume = ResumeVersion::factory()->for($user)->for($jobPost)->for($report, 'jobMatchReport')->create([
            'content' => [
                'header' => [
                    'name' => 'Héctor Coelho',
                    'headline' => 'Back-End Software Engineer',
                    'email' => 'hector@example.com',
                    'location' => 'Arapongas, PR',
                ],
                'summary' => 'Desenvolvedor Back-End PHP/Laravel com experiência em SaaS.',
                'skills' => [['category' => 'Backend', 'items' => ['PHP', 'Laravel']]],
                'experiences' => [[
                    'role' => 'Back End Engineer',
                    'company' => 'Prime Coaching',
                    'period' => '2025 - Atual',
                    'bullets' => [['text' => 'Desenvolvimento de APIs REST em Laravel.']],
                ]],
            ],
        ]);

        $this->actingAs($user)
            ->withSession(['locale' => 'pt_BR'])
            ->get(route('resumes.templates', $resume))
            ->assertOk()
            ->assertSee('ATS Clássico')
            ->assertSee('Tech Compacto')
            ->assertSee('Internacional Clean');

        $this->actingAs($user)
            ->get(route('resumes.preview', [$resume, 'tech-compact']))
            ->assertOk()
            ->assertSee('Héctor Coelho')
            ->assertSee('Prime Coaching');

        $this->actingAs($user)
            ->get(route('resumes.print', [$resume, 'international-clean']))
            ->assertOk()
            ->assertSee('Salvar como PDF');

        $this->actingAs($user)
            ->get(route('resumes.preview', [$resume, 'modelo-inexistente']))
            ->assertNotFound();
    }

    /**
     * @param  array<string, mixed>  $userOverrides
     * @return array{0: User, 1: JobPost}
     */
    private function userWithInventoryAndAnalyzedJob(array $userOverrides = []): array
    {
        $user = User::factory()->create($userOverrides);
        CandidateProfile::factory()->for($user)->create();
        CandidateSkill::factory()->for($user)->create(['name' => 'Laravel']);
        CandidateExperience::factory()->for($user)->create();
        $jobPost = JobPost::factory()->for($user)->create($this->jobPayload());

        $this->actingAs($user)->post(route('jobs.analyze', $jobPost));

        return [$user, $jobPost->refresh()];
    }

    /**
     * @return array<string, mixed>
     */
    private function jobPayload(): array
    {
        return [
            'title' => 'Backend Laravel',
            'company_name' => 'Acme',
            'job_description' => 'Buscamos pessoa desenvolvedora senior com PHP, Laravel, MySQL, Redis, Docker, APIs REST e testes automatizados.',
            'target_language' => 'pt_BR',
            'resume_type' => 'tech',
            'notes' => null,
        ];
    }

    private function hectorResumeText(): string
    {
        return <<<'TEXT'
HÉCTOR COELHO
Back-End Software Engineer | PHP, Laravel, SaaS, APIs REST
Arapongas, PR, Brasil | 4+ anos de experiência desde mar/2022 | LinkedIn: linkedin.com/in/hectorcoel

RESUMO PROFISSIONAL
Desenvolvedor Back-End PHP/Laravel com 4+ anos de experiência em aplicações web e plataformas SaaS em produção.

COMPETÊNCIAS TÉCNICAS
Backend: PHP 8, Laravel, MVC, Eloquent ORM, Composer, JavaScript
APIs e integrações: APIs RESTful, JSON, autenticação/autorização
Banco e performance: SQL, bancos relacionais, otimização de consultas, cache estratégico, eager loading
Qualidade: PHPUnit, testes unitários e de integração, refatoração, debugging
Ferramentas: Git, GitHub, IA aplicada ao desenvolvimento

EXPERIÊNCIA PROFISSIONAL
Prime Coaching | Back End Engineer - Mid Level | fev/2025 - atual
Applicativa Technologies | Back End Engineer | mar/2023 - fev/2025
Applicativa Technologies | Estagiário de Desenvolvimento | mar/2022 - fev/2023

FORMAÇÃO E IDIOMAS
UNOPAR - Universidade Norte do Paraná | Análise e Desenvolvimento de Sistemas (ADS) | mar/2022 - fev/2023
Português: nativo/fluente | Inglês: básico a intermediário, com leitura técnica e comunicação escrita em evolução
TEXT;
    }
}
