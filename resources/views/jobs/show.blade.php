@extends('layouts.app')

@section('content')
    @php
        $en = app()->getLocale() === 'en';
        $latestReport = $jobPost->matchReports->first();
        $requirements = $jobPost->parsed_requirements ?? [];
        $requiredSkills = $requirements['required_skills'] ?? [];
        $atsKeywords = $requirements['ats_keywords'] ?? $jobPost->parsed_keywords ?? [];
    @endphp

    <x-ui.page-header
        :eyebrow="$en ? 'Job match' : 'Match da vaga'"
        :title="$jobPost->title"
        :subtitle="($jobPost->company_name ?: ($en ? 'Company not informed' : 'Empresa não informada')).' · '.$jobPost->resume_type.' · '.$jobPost->target_language"
    >
        <x-slot:actions>
            <form method="POST" action="{{ route('jobs.analyze', $jobPost) }}" data-loading>
                @csrf
                <button class="btn secondary" type="submit" data-loading-text="{{ $en ? 'Analyzing...' : 'Analisando...' }}">
                    {{ $jobPost->parsed_requirements ? ($en ? 'Reanalyze' : 'Reanalisar') : ($en ? 'Analyze job' : 'Analisar vaga') }}
                </button>
                <p class="loading-hint">{{ $en ? 'Extracting requirements and recalculating evidence.' : 'Extraindo requisitos e recalculando evidências.' }}</p>
            </form>
            <form method="POST" action="{{ route('jobs.generate-resume', $jobPost) }}" data-loading>
                @csrf
                <button class="btn" type="submit" data-loading-text="{{ $en ? 'Generating...' : 'Gerando...' }}">{{ $en ? 'Generate resume' : 'Gerar currículo' }}</button>
                <p class="loading-hint">{{ $en ? 'Creating a resume version and ATS checklist.' : 'Criando uma versão do currículo e checklist ATS.' }}</p>
            </form>
        </x-slot:actions>
    </x-ui.page-header>

    <?php if ($latestReport): ?>
        <?php
            $scoreCards = [
                [$en ? 'Overall' : 'Geral', $latestReport->overall_score, 'success'],
                [$en ? 'Technical' : 'Técnico', $latestReport->technical_score, null],
                [$en ? 'Experience' : 'Experiência', $latestReport->experience_score, null],
                [$en ? 'Keywords' : 'Palavras-chave', $latestReport->keyword_score, null],
                ['ATS', $latestReport->ats_format_score, null],
                [$en ? 'Readability' : 'Leitura', $latestReport->human_readability_score, null],
            ];
        ?>
        <section class="summary-grid">
            <?php foreach ($scoreCards as $scoreCard): ?>
                <?php
                    $label = $scoreCard[0];
                    $safeScore = max(0, min(100, (int) $scoreCard[1]));
                    $tone = $scoreCard[2];
                ?>
                <x-ui.metric-card :label="$label" :value="$safeScore" suffix="/100" :tone="$tone" :meta="$safeScore >= 80 ? ($en ? 'Strong signal' : 'Sinal forte') : ($en ? 'Needs attention' : 'Pede atenção')" />
            <?php endforeach; ?>
        </section>
    <?php else: ?>
        <section style="margin-bottom:18px">
            <x-ui.empty-state
                :title="$en ? 'No match report yet' : 'Ainda não há relatório de match'"
                :description="$en ? 'Run the analysis to extract requirements, map evidence and identify gaps.' : 'Execute a análise para extrair requisitos, mapear evidências e identificar gaps.'"
                :example="$en ? 'You will get scores, ATS keywords, strengths and recommendations.' : 'Você receberá scores, palavras-chave ATS, pontos fortes e recomendações.'"
            />
        </section>
    <?php endif; ?>

    <section class="content-grid">
        <article class="card stack">
            <div class="panel-title">
                <div>
                    <p class="eyebrow">{{ $en ? 'Original input' : 'Entrada original' }}</p>
                    <h2>{{ __('messages.fields.description') }}</h2>
                    <p>{{ $en ? 'The source text used for requirement extraction.' : 'O texto fonte usado para extrair requisitos.' }}</p>
                </div>
                <span class="badge">{{ strlen($jobPost->job_description) }} chars</span>
            </div>
            <div class="scroll-panel">
                <p style="white-space:pre-line">{{ $jobPost->job_description }}</p>
            </div>
        </article>

        <aside class="card stack">
            <div>
                <p class="eyebrow">{{ $en ? 'Structured analysis' : 'Análise estruturada' }}</p>
                <h2>{{ $en ? 'Extracted signals' : 'Sinais extraídos' }}</h2>
                <p>{{ $en ? 'Review the data before generating a tailored resume.' : 'Revise os dados antes de gerar um currículo direcionado.' }}</p>
            </div>

            @if ($jobPost->parsed_requirements)
                <div class="keyword-cloud">
                    <span class="badge">{{ $en ? 'Seniority' : 'Senioridade' }}: {{ $jobPost->parsed_seniority ?: '—' }}</span>
                    <span class="badge">{{ $jobPost->resume_type }}</span>
                    <span class="badge">{{ $jobPost->target_language }}</span>
                </div>

                <div>
                    <h3>{{ $en ? 'Required skills' : 'Habilidades requeridas' }}</h3>
                    <div class="keyword-cloud" style="margin-top:10px">
                        @forelse ($requiredSkills as $skill)
                            <span class="badge">{{ $skill }}</span>
                        @empty
                            <span class="badge warning">{{ $en ? 'No skills extracted' : 'Nenhuma habilidade extraída' }}</span>
                        @endforelse
                    </div>
                </div>

                <div>
                    <h3>{{ $en ? 'ATS keywords' : 'Palavras-chave ATS' }}</h3>
                    <p class="mono-data">{{ implode(', ', $atsKeywords) ?: '—' }}</p>
                </div>

                <div>
                    <h3>{{ __('messages.fields.responsibilities') }}</h3>
                    <ul class="split-list">
                        @forelse ($jobPost->parsed_responsibilities ?? [] as $item)
                            <li>{{ $item }}</li>
                        @empty
                            <li>{{ $en ? 'No responsibilities extracted.' : 'Nenhuma responsabilidade extraída.' }}</li>
                        @endforelse
                    </ul>
                </div>
            @else
                <form method="POST" action="{{ route('jobs.analyze', $jobPost) }}" data-loading>
                    @csrf
                    <x-ui.empty-state
                        :title="$en ? 'Analysis not created yet' : 'Análise ainda não criada'"
                        :description="$en ? 'Extract requirements and keywords to unlock scores and evidence mapping.' : 'Extraia requisitos e palavras-chave para destravar scores e mapa de evidências.'"
                        :example="$en ? 'Tip: complete job descriptions generate better matches.' : 'Dica: descrições completas geram matches melhores.'"
                    />
                    <button class="btn" type="submit" data-loading-text="{{ $en ? 'Analyzing...' : 'Analisando...' }}" style="margin-top:12px">{{ $en ? 'Analyze now' : 'Analisar agora' }}</button>
                    <p class="loading-hint">{{ $en ? 'Building the requirement map...' : 'Construindo o mapa de requisitos...' }}</p>
                </form>
            @endif
        </aside>
    </section>

    @if ($latestReport)
        @php
            $criticalGaps = $latestReport->gaps['critical'] ?? [];
            $acceptableGaps = $latestReport->gaps['acceptable'] ?? [];
            $moderateGaps = $latestReport->warnings ?? [];
        @endphp

        <section class="grid grid-3" style="margin-top:18px">
            <article class="card stack">
                <div>
                    <p class="eyebrow">{{ $en ? 'Match quality' : 'Qualidade do match' }}</p>
                    <h2>{{ $en ? 'Strengths' : 'Pontos fortes' }}</h2>
                </div>
                <ul class="split-list">
                    @forelse ($latestReport->strengths ?? [] as $item)
                        <li>{{ $item }}</li>
                    @empty
                        <li>{{ $en ? 'No explicit strengths yet.' : 'Nenhum ponto forte explícito ainda.' }}</li>
                    @endforelse
                </ul>
            </article>

            <article class="card stack">
                <div>
                    <p class="eyebrow">{{ $en ? 'Risk areas' : 'Áreas de risco' }}</p>
                    <h2>{{ $en ? 'Gaps' : 'Gaps' }}</h2>
                </div>
                <div class="stack">
                    <div>
                        <p><span class="badge danger">{{ $en ? 'Critical' : 'Críticos' }}</span></p>
                        <ul class="split-list">
                            @forelse ($criticalGaps as $item)
                                <li>{{ $item }}</li>
                            @empty
                                <li>{{ $en ? 'No critical gaps.' : 'Nenhum gap crítico.' }}</li>
                            @endforelse
                        </ul>
                    </div>
                    <div>
                        <p><span class="badge warning">{{ $en ? 'Moderate' : 'Moderados' }}</span></p>
                        <ul class="split-list">
                            @forelse ($moderateGaps as $item)
                                <li>{{ $item }}</li>
                            @empty
                                <li>{{ $en ? 'No moderate warnings.' : 'Nenhum alerta moderado.' }}</li>
                            @endforelse
                        </ul>
                    </div>
                    <div>
                        <p><span class="badge">{{ $en ? 'Acceptable' : 'Aceitáveis' }}</span></p>
                        <ul class="split-list">
                            @forelse ($acceptableGaps as $item)
                                <li>{{ $item }}</li>
                            @empty
                                <li>{{ $en ? 'No acceptable gaps.' : 'Nenhum gap aceitável.' }}</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </article>

            <article class="card stack">
                <div>
                    <p class="eyebrow">{{ $en ? 'Next moves' : 'Próximos passos' }}</p>
                    <h2>{{ $en ? 'Recommendations' : 'Recomendações' }}</h2>
                </div>
                <ul class="split-list">
                    @forelse ($latestReport->recommendations ?? [] as $item)
                        <li>{{ $item }}</li>
                    @empty
                        <li>{{ $en ? 'Generate a resume to apply the best evidence.' : 'Gere um currículo para aplicar as melhores evidências.' }}</li>
                    @endforelse
                </ul>
            </article>
        </section>

        <section class="card stack-lg" style="margin-top:18px">
            <div class="panel-title">
                <div>
                    <p class="eyebrow">{{ $en ? 'Evidence' : 'Evidências' }}</p>
                    <h2>{{ $en ? 'Evidence map by requirement' : 'Mapa de evidências por requisito' }}</h2>
                    <p>{{ $en ? 'See where your inventory supports each requirement and where it needs reinforcement.' : 'Veja onde seu inventário sustenta cada requisito e onde precisa de reforço.' }}</p>
                </div>
                <div class="actions">
                    <a class="btn secondary" href="{{ route('career.index') }}">{{ $en ? 'Edit inventory' : 'Editar inventário' }}</a>
                    <form method="POST" action="{{ route('jobs.generate-resume', $jobPost) }}" data-loading>
                        @csrf
                        <button class="btn" type="submit" data-loading-text="{{ $en ? 'Generating...' : 'Gerando...' }}">{{ $en ? 'Generate tailored resume' : 'Gerar currículo direcionado' }}</button>
                    </form>
                </div>
            </div>

            <div class="grid grid-3">
                @forelse ($latestReport->evidence_map ?? [] as $skill => $item)
                    @php
                        $status = $item['status'] ?? null;
                        $badgeClass = match ($status) {
                            'missing' => 'danger',
                            'partial' => 'warning',
                            default => '',
                        };
                    @endphp
                    <article class="list-card" style="display:grid;align-items:start">
                        <div class="actions between">
                            <strong>{{ $skill }}</strong>
                            <span class="badge {{ $badgeClass }}">{{ \App\Support\UiText::label('evidence_status', $status) }}</span>
                        </div>
                        @if (! empty($item['evidence']))
                            <p class="list-card-copy">{{ $en ? 'Evidence' : 'Evidências' }}: {{ collect($item['evidence'])->pluck('label')->filter()->implode('; ') ?: '—' }}</p>
                        @else
                            <p class="list-card-copy">{{ $en ? 'Add evidence to the inventory to strengthen this point.' : 'Adicione evidências no inventário para fortalecer este ponto.' }}</p>
                        @endif
                    </article>
                @empty
                    <x-ui.empty-state
                        :title="$en ? 'No evidence map available' : 'Nenhum mapa de evidências disponível'"
                        :description="$en ? 'Run the analysis again after adding inventory items.' : 'Execute a análise novamente depois de adicionar itens ao inventário.'"
                        :cta-href="route('career.index')"
                        :cta-label="$en ? 'Open inventory' : 'Abrir inventário'"
                    />
                @endforelse
            </div>
        </section>
    @endif

    <section class="card stack-lg" style="margin-top:18px">
        <div class="panel-title">
            <div>
                <p class="eyebrow">{{ $en ? 'Outputs' : 'Saídas' }}</p>
                <h2>{{ $en ? 'Resumes for this job' : 'Currículos desta vaga' }}</h2>
                <p>{{ $en ? 'Generated versions stay connected to this job and its match report.' : 'As versões geradas continuam conectadas a esta vaga e ao relatório de match.' }}</p>
            </div>
            <form method="POST" action="{{ route('jobs.generate-resume', $jobPost) }}" data-loading>
                @csrf
                <button class="btn secondary" type="submit" data-loading-text="{{ $en ? 'Generating...' : 'Gerando...' }}">{{ $en ? 'Generate another version' : 'Gerar outra versão' }}</button>
                <p class="loading-hint">{{ $en ? 'Creating resume and checklist...' : 'Criando currículo e checklist...' }}</p>
            </form>
        </div>

        @forelse ($jobPost->resumeVersions as $resume)
            <article class="list-card">
                <div class="list-card-main">
                    <div class="list-card-title">{{ $resume->title }}</div>
                    <p class="list-card-meta">{{ $resume->created_at->format('d/m/Y H:i') }} · <span class="badge">{{ $resume->status }}</span></p>
                </div>
                <span class="actions">
                    <a class="btn secondary" href="{{ route('resumes.show', $resume) }}">{{ $en ? 'Details' : 'Detalhes' }}</a>
                    <a class="btn" href="{{ route('resumes.templates', $resume) }}">{{ $en ? 'Templates' : 'Modelos' }}</a>
                </span>
            </article>
        @empty
            <x-ui.empty-state
                :title="$en ? 'No resume generated for this job' : 'Nenhum currículo gerado para esta vaga'"
                :description="$en ? 'Generate a tailored version after reviewing the match signals.' : 'Gere uma versão direcionada depois de revisar os sinais de match.'"
                :example="$en ? 'Recommended: run analysis before generating.' : 'Recomendado: execute a análise antes de gerar.'"
            />
        @endforelse
    </section>
@endsection
