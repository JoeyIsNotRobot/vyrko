@extends('layouts.app')

@section('content')
    @php
        $en = app()->getLocale() === 'en';
        $currentResumes = $resumes->getCollection();
        $readyOnPage = $currentResumes->filter(fn ($resume) => $resume->status === 'ready')->count();
        $withJobOnPage = $currentResumes->filter(fn ($resume) => filled($resume->job_post_id))->count();
    @endphp

    <x-ui.page-header
        :eyebrow="$en ? 'Resumes' : 'Currículos'"
        :title="$en ? 'Generated resume library' : 'Biblioteca de currículos gerados'"
        :subtitle="$en ? 'Every version keeps structured content, copyable text, ATS checklist and template options.' : 'Cada versão mantém conteúdo estruturado, texto copiável, checklist ATS e opções de modelo.'"
    >
        <x-slot:actions>
            <a class="btn" href="{{ route('jobs.create') }}">{{ $en ? 'Analyze new job' : 'Analisar nova vaga' }}</a>
        </x-slot:actions>
    </x-ui.page-header>

    <section class="summary-grid">
        <x-ui.metric-card :label="$en ? 'Total versions' : 'Total de versões'" :value="$resumes->total()" :meta="$en ? 'Generated resumes' : 'Currículos gerados'" />
        <x-ui.metric-card :label="$en ? 'Ready on page' : 'Prontos nesta página'" :value="$readyOnPage" tone="success" :meta="$en ? 'Available to print or copy' : 'Disponíveis para imprimir ou copiar'" />
        <x-ui.metric-card :label="$en ? 'Linked to jobs' : 'Ligados a vagas'" :value="$withJobOnPage" :meta="$en ? 'Current page versions' : 'Versões da página atual'" />
        <x-ui.metric-card :label="$en ? 'Best next step' : 'Melhor próximo passo'" :value="$en ? 'Template' : 'Modelo'" :meta="$en ? 'Choose layout and export' : 'Escolha layout e exporte'" />
    </section>

    <section class="card stack-lg">
        <div class="panel-title">
            <div>
                <p class="eyebrow">{{ $en ? 'Versions' : 'Versões' }}</p>
                <h2>{{ $en ? 'Generated outputs' : 'Saídas geradas' }}</h2>
                <p>{{ $en ? 'Open details to inspect evidence, or go straight to templates for preview and PDF.' : 'Abra detalhes para inspecionar evidências, ou vá direto aos modelos para prévia e PDF.' }}</p>
            </div>
        </div>

        @forelse ($resumes as $resume)
            <article class="list-card">
                <div class="list-card-main">
                    <div class="actions">
                        <div class="list-card-title">{{ $resume->title }}</div>
                        <span class="badge">{{ $resume->status }}</span>
                    </div>
                    <p class="list-card-meta">
                        {{ $resume->jobPost?->title ?: ($en ? 'No linked job' : 'Sem vaga vinculada') }}
                        · {{ $resume->created_at->format('d/m/Y H:i') }}
                        · {{ $resume->language }}
                        · {{ $resume->resume_type }}
                    </p>
                </div>
                <span class="actions">
                    <a class="btn secondary" href="{{ route('resumes.show', $resume) }}">{{ $en ? 'Details' : 'Detalhes' }}</a>
                    <a class="btn" href="{{ route('resumes.templates', $resume) }}">{{ $en ? 'Templates' : 'Modelos' }}</a>
                </span>
            </article>
        @empty
            <x-ui.empty-state
                :title="$en ? 'No generated resumes' : 'Nenhum currículo nesta vaga'"
                :description="$en ? 'Start with a job analysis, then generate a tailored resume version from the match report.' : 'Gere o currículo certo para esta vaga em segundos.'"
                :cta-href="route('jobs.create')"
                :cta-label="$en ? 'Analyze a job' : 'Gerar currículo →'"
                :example="$en ? 'Flow: paste job → inspect match → generate resume → choose template.' : 'Fluxo: cole a vaga → revise o match → gere o currículo → escolha o modelo.'"
            />
        @endforelse

        {{ $resumes->links() }}
    </section>
@endsection
