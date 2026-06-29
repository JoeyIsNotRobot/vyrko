@extends('layouts.app')

@section('content')
    @php
        $en = app()->getLocale() === 'en';
        $resumeEn = $resumeVersion->language === 'en';
        $report = $resumeVersion->jobMatchReport;
        $content = $resumeVersion->content;
        $atsScore = $resumeVersion->ats_checklist['score'] ?? $report?->ats_format_score;
        $skillGroups = $content['skills'] ?? [];
        $experiences = $content['experiences'] ?? [];
    @endphp

    <x-ui.page-header
        :eyebrow="$en ? 'Resume' : 'Currículo'"
        :title="$resumeVersion->title"
        :subtitle="$resumeVersion->language.' · '.$resumeVersion->resume_type.' · '.$resumeVersion->status"
    >
        <x-slot:actions>
            <form method="POST" action="{{ route('resumes.run-ats-check', $resumeVersion) }}" data-loading>
                @csrf
                <button class="btn secondary" type="submit" data-loading-text="{{ $en ? 'Updating...' : 'Atualizando...' }}">{{ $en ? 'Update ATS' : 'Atualizar ATS' }}</button>
                <p class="loading-hint">{{ $en ? 'Refreshing checklist...' : 'Atualizando checklist...' }}</p>
            </form>
            @if ($resumeVersion->jobPost)
                <a class="btn secondary" href="{{ route('jobs.show', $resumeVersion->jobPost) }}">{{ $en ? 'Back to job' : 'Voltar à vaga' }}</a>
            @endif
            <a class="btn" href="{{ route('resumes.templates', $resumeVersion) }}">{{ $en ? 'Choose template' : 'Escolher modelo' }}</a>
        </x-slot:actions>
    </x-ui.page-header>

    <section class="summary-grid">
        <x-ui.metric-card :label="$en ? 'Match score' : 'Score de match'" :value="$report?->overall_score ?? '—'" :suffix="$report ? '/100' : null" tone="success" :meta="$en ? 'Fit against the job' : 'Aderência contra a vaga'" />
        <x-ui.metric-card label="ATS" :value="$atsScore ?? '—'" :suffix="$atsScore ? '/100' : null" :meta="$en ? 'Checklist score' : 'Score do checklist'" />
        <x-ui.metric-card :label="$en ? 'Skill groups' : 'Grupos de skills'" :value="count($skillGroups)" :meta="$en ? 'Structured for scanning' : 'Estruturados para leitura rápida'" />
        <x-ui.metric-card :label="$en ? 'Experiences' : 'Experiências'" :value="count($experiences)" :meta="$en ? 'Selected for this version' : 'Selecionadas para esta versão'" />
    </section>

    @if ($report)
        <section class="grid grid-2" style="margin-bottom:18px">
            <article class="card stack">
                <div>
                    <p class="eyebrow">{{ $en ? 'Why it works' : 'Por que funciona' }}</p>
                    <h2>{{ $en ? 'Strengths' : 'Pontos fortes' }}</h2>
                </div>
                <ul class="split-list">
                    @forelse ($report->strengths ?? [] as $item)
                        <li>{{ $item }}</li>
                    @empty
                        <li>{{ $en ? 'No strengths registered in the report.' : 'Nenhum ponto forte registrado no relatório.' }}</li>
                    @endforelse
                </ul>
            </article>
            <article class="card stack">
                <div>
                    <p class="eyebrow">{{ $en ? 'Risks' : 'Riscos' }}</p>
                    <h2>Gaps</h2>
                </div>
                <ul class="split-list">
                    @forelse (array_merge($report->gaps['critical'] ?? [], $report->gaps['acceptable'] ?? []) as $item)
                        <li>{{ $item }}</li>
                    @empty
                        <li>{{ $en ? 'No relevant gaps listed.' : 'Nenhum gap relevante listado.' }}</li>
                    @endforelse
                </ul>
            </article>
        </section>
    @endif

    <section class="card stack-lg" style="margin-bottom:18px">
        <div class="panel-title">
            <div>
                <p class="eyebrow">{{ $en ? 'Export' : 'Exportar' }}</p>
                <h2>{{ $en ? 'Choose a template to print or save as PDF' : 'Escolha um modelo para imprimir ou salvar em PDF' }}</h2>
                <p>{{ $en ? 'Preview formats before opening the browser print dialog.' : 'Visualize os formatos antes de abrir o diálogo de impressão do navegador.' }}</p>
            </div>
            <a class="btn" href="{{ route('resumes.templates', $resumeVersion) }}">{{ $en ? 'See all templates' : 'Ver todos os modelos' }}</a>
        </div>

        <div class="grid grid-3">
            @foreach ($templates as $slug => $template)
                <article class="list-card" style="display:grid;align-items:start">
                    <div>
                        <h3>{{ $template[$en ? 'name_en' : 'name_pt'] }}</h3>
                        <p>{{ $template[$en ? 'description_en' : 'description_pt'] }}</p>
                        <span class="badge">{{ $template[$en ? 'tag_en' : 'tag_pt'] }}</span>
                    </div>
                    <div class="actions">
                        <a class="btn secondary" href="{{ route('resumes.preview', [$resumeVersion, $slug]) }}">{{ $en ? 'Preview' : 'Prévia' }}</a>
                        <a class="btn secondary" href="{{ route('resumes.print', [$resumeVersion, $slug]) }}">{{ $en ? 'Download PDF' : 'Baixar PDF' }}</a>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="content-grid reverse">
        <article class="resume-paper">
            <h2>{{ $content['header']['name'] ?? '' }}</h2>
            <p>{{ $content['header']['headline'] ?? '' }}</p>
            <p class="muted">{{ collect([$content['header']['location'] ?? null, $content['header']['email'] ?? null, $content['header']['phone'] ?? null])->filter()->implode(' · ') }}</p>

            <h3>{{ $resumeEn ? 'Summary' : 'Resumo' }}</h3>
            <p>{{ $content['summary'] ?? '' }}</p>

            <h3>{{ $resumeEn ? 'Skills' : 'Habilidades' }}</h3>
            @forelse ($skillGroups as $group)
                <p><strong>{{ $group['category'] }}</strong>: {{ implode(', ', $group['items'] ?? []) }}</p>
            @empty
                <p>{{ $resumeEn ? 'No skills selected for this version.' : 'Nenhuma habilidade selecionada para esta versão.' }}</p>
            @endforelse

            <h3>{{ $resumeEn ? 'Experience' : 'Experiências' }}</h3>
            @forelse ($experiences as $experience)
                <p><strong>{{ $experience['role'] }}</strong> · {{ $experience['company'] }} · {{ $experience['period'] }}</p>
                <ul>
                    @foreach ($experience['bullets'] ?? [] as $bullet)
                        <li>{{ $bullet['text'] }} <span class="badge">{{ __('messages.resume.evidence_count', ['count' => count($bullet['evidence'] ?? [])]) }}</span></li>
                    @endforeach
                </ul>
            @empty
                <p>{{ $resumeEn ? 'No experiences selected for this version.' : 'Nenhuma experiência selecionada para esta versão.' }}</p>
            @endforelse
        </article>

        <aside class="stack-lg">
            <article class="card stack">
                <div>
                    <p class="eyebrow">{{ $en ? 'Copy' : 'Copiar' }}</p>
                    <h2>{{ __('messages.resume.copyable_text') }}</h2>
                    <p>{{ $en ? 'Use this text for platforms that require plain text fields.' : 'Use este texto em plataformas que exigem campos de texto puro.' }}</p>
                </div>
                <textarea id="plainText" readonly class="large-textarea">{{ $resumeVersion->plain_text }}</textarea>
                <button class="btn" type="button" onclick="navigator.clipboard.writeText(document.getElementById('plainText').value)">{{ __('messages.resume.copy_text') }}</button>
            </article>

            @if ($resumeVersion->cover_letter_text)
                <article class="card stack">
                    <div>
                        <p class="eyebrow">{{ $en ? 'Cover letter' : 'Carta de apresentação' }}</p>
                        <h2>{{ $en ? 'Generated cover letter' : 'Carta de apresentação gerada' }}</h2>
                        <p>{{ $en ? 'Review and adapt before sending.' : 'Revise e adapte antes de enviar.' }}</p>
                    </div>
                    <textarea id="coverLetterText" readonly class="large-textarea" style="min-height:220px">{{ $resumeVersion->cover_letter_text }}</textarea>
                    <button class="btn" type="button" onclick="navigator.clipboard.writeText(document.getElementById('coverLetterText').value)">{{ $en ? 'Copy cover letter' : 'Copiar carta' }}</button>
                </article>
            @endif

            <article class="card stack">
                <div>
                    <p class="eyebrow">ATS</p>
                    <h2>{{ __('messages.resume.ats_checklist') }}</h2>
                </div>
                @if ($resumeVersion->ats_checklist)
                    <div>
                        <span class="badge">Score {{ $resumeVersion->ats_checklist['score'] }}</span>
                    </div>
                    <ul class="split-list">
                        @foreach ($resumeVersion->ats_checklist['items'] ?? [] as $item)
                            <li>{{ \App\Support\UiText::label('ats', $item['key']) }}: {{ \App\Support\UiText::label('ats', $item['status']) }} — {{ $item['message'] }}</li>
                        @endforeach
                    </ul>
                @else
                    <x-ui.empty-state
                        :title="$en ? 'Checklist not run yet' : 'Checklist ainda não executado'"
                        :description="$en ? 'Run the ATS update to refresh formatting and keyword checks.' : 'Execute a atualização ATS para revisar formatação e palavras-chave.'"
                        :example="$en ? 'Useful before exporting as PDF.' : 'Útil antes de exportar em PDF.'"
                    />
                @endif
            </article>

            @if ($report?->evidence_map)
                <article class="card stack">
                    <div>
                        <p class="eyebrow">{{ $en ? 'Evidence' : 'Evidências' }}</p>
                        <h2>{{ $en ? 'Evidence map' : 'Mapa de evidências' }}</h2>
                    </div>
                    <ul class="split-list">
                        @foreach ($report->evidence_map as $skill => $item)
                            <li><strong>{{ $skill }}</strong>: {{ \App\Support\UiText::label('evidence_status', $item['status'] ?? null) }}</li>
                        @endforeach
                    </ul>
                </article>
            @endif
        </aside>
    </section>
@endsection
