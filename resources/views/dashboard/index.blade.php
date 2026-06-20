@extends('layouts.app')

@section('content')
    @php
        $en = app()->getLocale() === 'en';
        $missingLabels = $missingInventory->map(fn (string $item): string => match ($item) {
            'profile' => $en ? 'Profile' : 'Perfil',
            'experiences' => $en ? 'Experiences' : 'Experiências',
            'skills' => $en ? 'Skills' : 'Habilidades',
            'achievements' => $en ? 'Achievements' : 'Conquistas',
            'languages' => $en ? 'Languages' : 'Idiomas',
            default => ucfirst($item),
        });
    @endphp

    <x-ui.page-header
        eyebrow="Dashboard"
        :title="$en ? 'Your resume command center' : 'Seu centro de comando de currículos'"
        :subtitle="$en ? 'Analyze jobs, see fit signals and generate focused resumes from real career evidence.' : 'Analise vagas, gere currículos precisos e acompanhe sua candidatura.'"
    >
        <x-slot:actions>
            <a class="btn secondary" href="{{ route('career.index') }}">{{ $en ? 'Edit inventory' : 'Editar inventário' }}</a>
            <a class="btn" href="{{ route('jobs.create') }}">{{ $en ? 'Analyze new job' : 'Analisar nova vaga' }}</a>
        </x-slot:actions>
    </x-ui.page-header>

    <section class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
        <x-ui.metric-card
            :label="$en ? 'Analyzed jobs' : 'Vagas analisadas'"
            :value="$jobCount"
            :meta="$en ? 'View all jobs →' : 'Ver todas as vagas →'"
        />
        <x-ui.metric-card
            :label="$en ? 'Generated resumes' : 'Currículos gerados'"
            :value="$resumeCount"
            :meta="$en ? 'View all resumes →' : 'Ver todos os currículos →'"
        />
        {{-- Completude: inline porque metric-card não tem slot CTA --}}
        <article class="metric-card {{ $completeness >= 80 ? 'metric-card-success' : 'metric-card-warning' }} min-h-[112px]">
            <p class="metric-label">{{ $en ? 'Inventory completeness' : 'Completude do inventário' }}</p>
            <div class="metric-value">{{ $completeness }}<small>%</small></div>
            <p class="metric-meta">
                @if ($completeness < 80)
                    <a class="text-sm font-bold text-blue-400 hover:text-blue-300 underline-offset-2 hover:underline"
                       href="{{ route('career.index') }}">Complete seu perfil →</a>
                @else
                    <span class="text-emerald-400">Inventário completo ✓</span>
                @endif
            </p>
        </article>
    </section>

    <section class="content-grid">
        <article class="card stack-lg">
            <div class="panel-title">
                <div>
                    <p class="eyebrow">{{ $en ? 'Pipeline' : 'Pipeline' }}</p>
                    <h2>{{ $en ? 'Latest job analyses' : 'Últimas análises de vaga' }}</h2>
                    <p>{{ $en ? 'Open a workspace to reanalyze, generate a resume or inspect evidence.' : 'Abra um workspace para reanalisar, gerar currículo ou inspecionar evidências.' }}</p>
                </div>
                <a class="btn secondary" href="{{ route('jobs.index') }}">{{ $en ? 'View all' : 'Ver todas' }}</a>
            </div>

            @forelse ($latestJobs as $job)
                <div class="list-card">
                    <div class="list-card-main">
                        <div class="list-card-title">{{ $job->title }}</div>
                        <p class="list-card-meta">
                            {{ $job->company_name ?: ($en ? 'No company' : 'Sem empresa') }}
                            · {{ $job->created_at->format('d/m/Y') }}
                            · <span class="badge">{{ $job->parsed_seniority ?: ($en ? 'Not analyzed' : 'Não analisada') }}</span>
                        </p>
                    </div>
                    <a class="btn secondary" href="{{ route('jobs.show', $job) }}">{{ $en ? 'Open' : 'Abrir' }}</a>
                </div>
            @empty
                <x-ui.empty-state
                    :title="$en ? 'No job workspace yet' : 'Nenhum workspace de vaga ainda'"
                    :description="$en ? 'Paste a complete job description to extract requirements, keywords and gaps.' : 'Cole uma descrição completa de vaga para extrair requisitos, palavras-chave e gaps.'"
                    :cta-href="route('jobs.create')"
                    :cta-label="$en ? 'Analyze first job' : 'Analisar primeira vaga'"
                    :example="$en ? 'Example: Senior Laravel Engineer · fintech · remote' : 'Exemplo: Desenvolvedor Laravel Sênior · fintech · remoto'"
                />
            @endforelse
        </article>

        <aside class="stack-lg">
            <article class="card stack">
                <div>
                    <p class="eyebrow">{{ $en ? 'Next best action' : 'Próxima melhor ação' }}</p>
                    <h2>{{ $missingInventory->isNotEmpty() ? ($en ? 'Strengthen your evidence base' : 'Fortaleça sua base de evidências') : ($en ? 'Analyze another role' : 'Analise outra vaga') }}</h2>
                    <p>{{ $missingInventory->isNotEmpty() ? ($en ? 'Completing these blocks improves match quality and resume specificity.' : 'Completar estes blocos melhora a qualidade do match e a especificidade dos currículos.') : ($en ? 'Your core inventory looks ready. Create another job workspace.' : 'Seu inventário base parece pronto. Crie outro workspace de vaga.') }}</p>
                </div>

                @if ($missingInventory->isNotEmpty())
                    <div class="keyword-cloud">
                        @foreach ($missingLabels as $label)
                            <span class="badge warning">{{ $label }}</span>
                        @endforeach
                    </div>
                    <a class="btn" href="{{ route('career.index') }}">{{ $en ? 'Complete inventory' : 'Completar inventário' }}</a>
                @else
                    <a class="btn" href="{{ route('jobs.create') }}">{{ $en ? 'Analyze new job' : 'Analisar nova vaga' }}</a>
                @endif
            </article>

            <article class="card stack">
                <div class="panel-title">
                    <div>
                        <p class="eyebrow">{{ $en ? 'Outputs' : 'Saídas' }}</p>
                        <h2>{{ $en ? 'Latest resumes' : 'Últimos currículos' }}</h2>
                    </div>
                    <a class="btn secondary" href="{{ route('resumes.index') }}">{{ $en ? 'View all' : 'Ver todos' }}</a>
                </div>

                @forelse ($latestResumes as $resume)
                    <div class="list-card">
                        <div class="list-card-main">
                            <div class="list-card-title">{{ $resume->title }}</div>
                            <p class="list-card-meta">{{ $resume->jobPost?->title ?: '—' }} · {{ $resume->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <a class="btn secondary" href="{{ route('resumes.templates', $resume) }}">{{ $en ? 'Templates' : 'Modelos' }}</a>
                    </div>
                @empty
                    <x-ui.empty-state
                        :title="$en ? 'No resume generated yet' : 'Nenhum currículo gerado ainda'"
                        :description="$en ? 'Generate a resume from a job analysis to see versions here.' : 'Gere um currículo a partir de uma análise de vaga para ver versões aqui.'"
                        :cta-href="route('jobs.create')"
                        :cta-label="$en ? 'Start with a job' : 'Começar por uma vaga'"
                        :example="$en ? 'The best flow is: job → match → resume → template.' : 'O melhor fluxo é: vaga → match → currículo → modelo.'"
                    />
                @endforelse
            </article>
        </aside>
    </section>
@endsection
