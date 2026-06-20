@extends('layouts.app')

@section('content')
    @php
        $en = app()->getLocale() === 'en';
        $currentJobs = $jobs->getCollection();
        $analyzedOnPage = $currentJobs->filter(fn ($job) => filled($job->parsed_requirements))->count();
        $withCompanyOnPage = $currentJobs->filter(fn ($job) => filled($job->company_name))->count();
    @endphp

    <x-ui.page-header
        :eyebrow="$en ? 'Jobs' : 'Vagas'"
        :title="$en ? 'Job analysis workspaces' : 'Workspaces de análise de vagas'"
        :subtitle="$en ? 'Manage saved job descriptions, revisit requirements and generate tailored resumes.' : 'Gerencie descrições salvas, revisite requisitos e gere currículos personalizados.'"
    >
        <x-slot:actions>
            <a class="btn" href="{{ route('jobs.create') }}">{{ $en ? 'New analysis' : 'Nova análise' }}</a>
        </x-slot:actions>
    </x-ui.page-header>

    <section class="summary-grid">
        <x-ui.metric-card :label="$en ? 'Saved jobs' : 'Vagas salvas'" :value="$jobs->total()" :meta="$en ? 'Total workspaces' : 'Total de workspaces'" />
        <x-ui.metric-card :label="$en ? 'Analyzed on page' : 'Analisadas nesta página'" :value="$analyzedOnPage" tone="success" :meta="$en ? 'Already have extracted requirements' : 'Já têm requisitos extraídos'" />
        <x-ui.metric-card :label="$en ? 'With company' : 'Com empresa'" :value="$withCompanyOnPage" :meta="$en ? 'Current page metadata' : 'Metadados da página atual'" />
        <x-ui.metric-card :label="$en ? 'Primary action' : 'Ação principal'" :value="$en ? 'Paste' : 'Colar'" :meta="$en ? 'Start from a full job post' : 'Comece por uma vaga completa'" />
    </section>

    <section class="card stack-lg">
        <div class="panel-title">
            <div>
                <p class="eyebrow">{{ $en ? 'Workspace list' : 'Lista de workspaces' }}</p>
                <h2>{{ $en ? 'Saved analyses' : 'Análises salvas' }}</h2>
                <p>{{ $en ? 'Open a job to inspect seniority, ATS keywords, gaps and generated resumes.' : 'Abra uma vaga para inspecionar senioridade, palavras-chave ATS, gaps e currículos gerados.' }}</p>
            </div>
            <a class="btn secondary" href="{{ route('dashboard') }}">{{ $en ? 'Back to dashboard' : 'Voltar ao dashboard' }}</a>
        </div>

        @forelse ($jobs as $job)
            <article class="list-card">
                <div class="list-card-main">
                    <div class="actions">
                        <div class="list-card-title">{{ $job->title }}</div>
                        <span class="badge {{ $job->parsed_requirements ? '' : 'warning' }}">
                            {{ $job->parsed_requirements ? ($en ? 'Analyzed' : 'Analisada') : ($en ? 'Draft' : 'Rascunho') }}
                        </span>
                    </div>
                    <p class="list-card-meta">
                        {{ $job->company_name ?: ($en ? 'Company not informed' : 'Empresa não informada') }}
                        · {{ $job->created_at->format('d/m/Y') }}
                        · {{ $job->resume_type }}
                        · {{ $job->target_language }}
                    </p>
                    <p class="list-card-copy">
                        {{ $en ? 'Seniority' : 'Senioridade' }}:
                        <span class="badge">{{ $job->parsed_seniority ?: ($en ? 'Not extracted' : 'Não extraída') }}</span>
                    </p>
                </div>
                <a class="btn" href="{{ route('jobs.show', $job) }}">{{ $en ? 'Open workspace' : 'Abrir workspace' }}</a>
            </article>
        @empty
            <x-ui.empty-state
                :title="$en ? 'No jobs saved yet' : 'Nenhum workspace ainda'"
                :description="$en ? 'Create your first workspace by pasting responsibilities, requirements and stack details.' : 'Cada análise de vaga vira um workspace com match e currículo.'"
                :cta-href="route('jobs.create')"
                :cta-label="$en ? 'Analyze first job' : 'Analisar nova vaga'"
                :example="$en ? 'Example: Backend Laravel Engineer with Redis, MySQL and queues.' : 'Exemplo: Backend Laravel com Redis, MySQL e filas.'"
            />
        @endforelse

        {{ $jobs->links() }}
    </section>
@endsection
