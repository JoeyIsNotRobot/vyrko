@extends('layouts.app')

@section('content')
    @php
        $en = app()->getLocale() === 'en';
        $hasProfile = filled($profile?->headline) || filled($profile?->about) || filled($profile?->raw_text);
    @endphp

    <x-ui.page-header
        :eyebrow="$en ? 'Manual LinkedIn' : 'LinkedIn manual'"
        :title="$en ? 'Positioning analysis prepared for Pro' : 'Análise de posicionamento preparada para o Pro'"
        :subtitle="$en ? 'No scraping, no login and no automatic API. Paste profile text manually to receive recommendations.' : 'Sem scraping, sem login e sem API automática. Cole textos manualmente para receber recomendações.'"
    >
        <x-slot:actions>
            <a class="btn secondary" href="{{ route('career.index') }}">{{ $en ? 'Open inventory' : 'Abrir inventário' }}</a>
        </x-slot:actions>
    </x-ui.page-header>

    <section class="summary-grid">
        <x-ui.metric-card :label="$en ? 'Profile text' : 'Texto do perfil'" :value="$hasProfile ? ($en ? 'Ready' : 'Pronto') : ($en ? 'Empty' : 'Vazio')" :tone="$hasProfile ? 'success' : 'warning'" :meta="$en ? 'Manual LinkedIn source' : 'Fonte manual do LinkedIn'" />
        <x-ui.metric-card :label="$en ? 'Reports' : 'Relatórios'" :value="$reports->count()" :meta="$en ? 'Positioning analyses' : 'Análises de posicionamento'" />
        <x-ui.metric-card :label="$en ? 'Current score' : 'Score atual'" :value="$reports->first()?->score ?? '—'" :suffix="$reports->first() ? '/100' : null" :meta="$en ? 'Latest report' : 'Relatório mais recente'" />
        <x-ui.metric-card :label="$en ? 'Input mode' : 'Modo de entrada'" :value="$en ? 'Manual' : 'Manual'" :meta="$en ? 'Safe MVP workflow' : 'Fluxo seguro do MVP'" />
    </section>

    <section class="content-grid wide-aside">
        <form class="card stack-lg" method="POST" action="{{ route('linkedin.profile.store') }}" data-loading>
            @csrf
            <div class="panel-title">
                <div>
                    <p class="eyebrow">{{ $en ? 'Profile source' : 'Fonte do perfil' }}</p>
                    <h2>{{ $en ? 'Paste your LinkedIn content' : 'Cole o conteúdo do LinkedIn' }}</h2>
                    <p>{{ $en ? 'Keep the text close to what recruiters see on your profile.' : 'Mantenha o texto próximo do que recrutadores veem no seu perfil.' }}</p>
                </div>
            </div>

            <div class="field">
                <label>Headline</label>
                <input name="headline" value="{{ old('headline', $profile?->headline) }}" placeholder="{{ $en ? 'Backend Engineer · Laravel · APIs · SaaS' : 'Engenheiro Backend · Laravel · APIs · SaaS' }}">
                <p class="form-help">{{ $en ? 'Short positioning phrase shown below your name.' : 'Frase curta de posicionamento exibida abaixo do seu nome.' }}</p>
            </div>

            <div class="field">
                <label>About</label>
                <textarea class="compact-textarea" name="about" placeholder="{{ $en ? 'Paste your current About section...' : 'Cole sua seção Sobre atual...' }}">{{ old('about', $profile?->about) }}</textarea>
                <p class="form-help">{{ $en ? 'This drives the recommendation for narrative clarity.' : 'Isso orienta a recomendação de clareza narrativa.' }}</p>
            </div>

            <div class="form-row">
                <div class="field">
                    <label>{{ $en ? 'Experiences' : 'Experiências' }}</label>
                    <textarea class="compact-textarea" name="experiences_text" placeholder="{{ $en ? 'Paste roles, companies and main bullets...' : 'Cole cargos, empresas e principais bullets...' }}">{{ old('experiences_text', $profile?->experiences_text) }}</textarea>
                </div>
                <div class="field">
                    <label>Skills</label>
                    <textarea class="compact-textarea" name="skills_text" placeholder="{{ $en ? 'Paste the visible skills list...' : 'Cole a lista de habilidades visível...' }}">{{ old('skills_text', $profile?->skills_text) }}</textarea>
                </div>
            </div>

            <div class="field">
                <label>{{ $en ? 'Optional raw text' : 'Texto bruto opcional' }}</label>
                <textarea class="compact-textarea" name="raw_text" placeholder="{{ $en ? 'Any extra profile text, featured section or recruiter context...' : 'Qualquer texto extra do perfil, seção em destaque ou contexto de recrutador...' }}">{{ old('raw_text', $profile?->raw_text) }}</textarea>
                <p class="form-help">{{ $en ? 'Use this for content that does not fit the structured fields.' : 'Use para conteúdo que não se encaixa nos campos estruturados.' }}</p>
            </div>

            <div class="actions end">
                <button class="btn" type="submit" data-loading-text="{{ $en ? 'Saving...' : 'Salvando...' }}">{{ $en ? 'Save profile text' : 'Salvar texto do perfil' }}</button>
            </div>
            <p class="loading-hint">{{ $en ? 'Saving manual profile source...' : 'Salvando fonte manual do perfil...' }}</p>
        </form>

        <aside class="stack-lg">
            <form class="card stack" method="POST" action="{{ route('linkedin.analyze') }}" data-loading>
                @csrf
                <div>
                    <p class="eyebrow">{{ $en ? 'Analysis' : 'Análise' }}</p>
                    <h2>{{ $en ? 'Analyze positioning' : 'Analisar posicionamento' }}</h2>
                    <p>{{ $en ? 'Set a target role so recommendations are sharper.' : 'Defina um cargo alvo para recomendações mais precisas.' }}</p>
                </div>
                <div class="field">
                    <label>{{ $en ? 'Target role' : 'Cargo alvo' }}</label>
                    <input name="target_role" placeholder="{{ $en ? 'Senior Backend Engineer' : 'Desenvolvedor Backend Sênior' }}">
                    <p class="form-help">{{ $en ? 'Optional, but strongly recommended.' : 'Opcional, mas fortemente recomendado.' }}</p>
                </div>
                <div class="field">
                    <label>{{ __('messages.common.language') }}</label>
                    <select name="target_language">
                        <option value="pt_BR">{{ __('messages.common.portuguese') }}</option>
                        <option value="en">{{ __('messages.common.english') }}</option>
                    </select>
                </div>
                <button class="btn" type="submit" data-loading-text="{{ $en ? 'Analyzing...' : 'Analisando...' }}">{{ $en ? 'Create analysis' : 'Criar análise' }}</button>
                <p class="loading-hint">{{ $en ? 'Reviewing positioning signals...' : 'Revisando sinais de posicionamento...' }}</p>
            </form>

            <article class="card stack">
                <div>
                    <p class="eyebrow">{{ $en ? 'Reports' : 'Relatórios' }}</p>
                    <h2>{{ $en ? 'Positioning history' : 'Histórico de posicionamento' }}</h2>
                </div>

                @forelse ($reports as $report)
                    <div class="list-card" style="display:grid;align-items:start">
                        <div class="actions between">
                            <strong>Score {{ $report->score }}</strong>
                            <span class="badge">{{ $report->target_role ?: ($en ? 'No target' : 'Sem alvo') }}</span>
                        </div>
                        <ul class="split-list">
                            @foreach ($report->recommendations ?? [] as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                @empty
                    <x-ui.empty-state
                        :title="$en ? 'No LinkedIn report yet' : 'Nenhum relatório de LinkedIn ainda'"
                        :description="$en ? 'Save profile text, then create a positioning analysis for a target role.' : 'Salve o texto do perfil e crie uma análise de posicionamento para um cargo alvo.'"
                        :example="$en ? 'Example target: Senior Laravel Backend Engineer.' : 'Exemplo de alvo: Desenvolvedor Backend Laravel Sênior.'"
                    />
                @endforelse
            </article>
        </aside>
    </section>
@endsection
