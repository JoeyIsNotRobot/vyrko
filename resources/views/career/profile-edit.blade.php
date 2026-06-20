@extends('layouts.app')

@section('content')
    @php
        $en = app()->getLocale() === 'en';
        $profileCompleteness = collect([
            filled($profile?->first_name) && filled($profile?->last_name),
            filled($profile?->headline) || filled($profile?->target_role),
            filled($profile?->summary),
            filled($profile?->email),
            filled($profile?->linkedin_url) || filled($profile?->github_url) || filled($profile?->portfolio_url),
        ])->filter()->count();
    @endphp

    <x-ui.page-header
        :eyebrow="__('messages.career.profile')"
        :title="$en ? 'Candidate profile' : 'Perfil do candidato'"
        :subtitle="$en ? 'This identity powers resume headers, summaries and positioning across generated versions.' : 'Essa identidade alimenta cabeçalhos, resumos e posicionamento nas versões geradas.'"
    >
        <x-slot:actions>
            <a class="btn secondary" href="{{ route('career.index') }}">{{ $en ? 'Back to inventory' : 'Voltar ao inventário' }}</a>
        </x-slot:actions>
    </x-ui.page-header>

    <section class="summary-grid">
        <x-ui.metric-card :label="$en ? 'Profile blocks' : 'Blocos do perfil'" :value="$profileCompleteness.'/5'" :meta="$en ? 'Core data completed' : 'Dados principais preenchidos'" />
        <x-ui.metric-card :label="$en ? 'Preferred language' : 'Idioma preferido'" :value="$profile?->preferred_language ?: 'pt_BR'" :meta="$en ? 'Default resume language' : 'Idioma padrão do currículo'" />
        <x-ui.metric-card :label="$en ? 'Target role' : 'Cargo alvo'" :value="$profile?->target_role ?: '—'" :meta="$en ? 'Used for positioning' : 'Usado no posicionamento'" />
        <x-ui.metric-card :label="$en ? 'Contact' : 'Contato'" :value="$profile?->email ? 'OK' : '—'" :tone="$profile?->email ? 'success' : 'warning'" :meta="$en ? 'Resume header readiness' : 'Pronto para cabeçalho'" />
    </section>

    <section class="content-grid wide-aside">
        <form class="card stack-lg" method="POST" action="{{ route('career.profile.update') }}" data-loading>
            @csrf
            @method('PUT')

            <div class="panel-title">
                <div>
                    <p class="eyebrow">{{ $en ? 'Identity' : 'Identidade' }}</p>
                    <h2>{{ $en ? 'Personal and positioning data' : 'Dados pessoais e posicionamento' }}</h2>
                    <p>{{ $en ? 'Keep this concise and reusable across applications.' : 'Mantenha estes dados concisos e reutilizáveis nas candidaturas.' }}</p>
                </div>
            </div>

            <div class="form-row">
                <div class="field">
                    <label>{{ __('messages.fields.name') }}</label>
                    <input name="first_name" value="{{ old('first_name', $profile?->first_name) }}" placeholder="{{ $en ? 'First name' : 'Nome' }}" required>
                </div>
                <div class="field">
                    <label>{{ $en ? 'Last name' : 'Sobrenome' }}</label>
                    <input name="last_name" value="{{ old('last_name', $profile?->last_name) }}" placeholder="{{ $en ? 'Last name' : 'Sobrenome' }}" required>
                </div>
            </div>

            <div class="field">
                <label>Headline</label>
                <input name="headline" value="{{ old('headline', $profile?->headline) }}" placeholder="{{ $en ? 'Backend Software Engineer · PHP · Laravel · SaaS' : 'Engenheiro de Software Backend · PHP · Laravel · SaaS' }}">
                <p class="form-help">{{ $en ? 'Short phrase used near your name in resume headers.' : 'Frase curta usada próxima ao seu nome nos cabeçalhos dos currículos.' }}</p>
            </div>

            <div class="form-row">
                <div class="field">
                    <label>{{ $en ? 'City' : 'Cidade' }}</label>
                    <input name="location_city" value="{{ old('location_city', $profile?->location_city) }}" placeholder="São Paulo">
                </div>
                <div class="field">
                    <label>{{ $en ? 'State' : 'Estado' }}</label>
                    <input name="location_state" value="{{ old('location_state', $profile?->location_state) }}" placeholder="SP">
                </div>
            </div>

            <div class="form-row">
                <div class="field">
                    <label>{{ $en ? 'Country' : 'País' }}</label>
                    <input name="location_country" value="{{ old('location_country', $profile?->location_country) }}" placeholder="{{ $en ? 'Brazil' : 'Brasil' }}">
                </div>
                <div class="field">
                    <label>{{ $en ? 'Professional e-mail' : 'E-mail profissional' }}</label>
                    <input name="email" type="email" value="{{ old('email', $profile?->email) }}" placeholder="voce@email.com">
                </div>
            </div>

            <div class="form-row">
                <div class="field">
                    <label>{{ $en ? 'Phone' : 'Telefone' }}</label>
                    <input name="phone" value="{{ old('phone', $profile?->phone) }}" placeholder="+55 11 99999-9999">
                </div>
                <div class="field">
                    <label>{{ $en ? 'Preferred language' : 'Idioma preferido' }}</label>
                    <select name="preferred_language">
                        <option value="pt_BR" @selected(old('preferred_language', $profile?->preferred_language ?? 'pt_BR') === 'pt_BR')>{{ __('messages.common.portuguese') }}</option>
                        <option value="en" @selected(old('preferred_language', $profile?->preferred_language) === 'en')>{{ __('messages.common.english') }}</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="field">
                    <label>LinkedIn</label>
                    <input name="linkedin_url" value="{{ old('linkedin_url', $profile?->linkedin_url) }}" placeholder="https://linkedin.com/in/seu-perfil">
                </div>
                <div class="field">
                    <label>GitHub</label>
                    <input name="github_url" value="{{ old('github_url', $profile?->github_url) }}" placeholder="https://github.com/seu-usuario">
                </div>
            </div>

            <div class="field">
                <label>{{ $en ? 'Portfolio' : 'Portfólio' }}</label>
                <input name="portfolio_url" value="{{ old('portfolio_url', $profile?->portfolio_url) }}" placeholder="https://seuportfolio.com">
            </div>

            <div class="field">
                <label>{{ $en ? 'Base summary' : 'Resumo base' }}</label>
                <textarea class="large-textarea" name="summary" placeholder="{{ $en ? 'Write a reusable professional summary grounded in real experience...' : 'Escreva um resumo profissional reutilizável baseado em experiências reais...' }}">{{ old('summary', $profile?->summary) }}</textarea>
                <p class="form-help">{{ $en ? 'The generator adapts this summary to each job; do not overfit it to one company.' : 'O gerador adapta este resumo para cada vaga; não otimize demais para uma única empresa.' }}</p>
            </div>

            <div class="form-row">
                <div class="field">
                    <label>{{ $en ? 'Target role' : 'Cargo alvo' }}</label>
                    <input name="target_role" value="{{ old('target_role', $profile?->target_role) }}" placeholder="{{ $en ? 'Senior Backend Engineer' : 'Desenvolvedor Backend Sênior' }}">
                </div>
                <div class="field">
                    <label>{{ $en ? 'Target seniority' : 'Senioridade alvo' }}</label>
                    <input name="target_seniority" value="{{ old('target_seniority', $profile?->target_seniority) }}" placeholder="{{ $en ? 'Senior' : 'Sênior' }}">
                </div>
            </div>

            <div class="field">
                <label>{{ $en ? 'Professional area' : 'Área profissional' }}</label>
                <input name="professional_area" list="area-suggestions" value="{{ old('professional_area', $profile?->professional_area) }}" placeholder="{{ $en ? 'Ex: Software Engineering, Finance, Marketing...' : 'Ex: Engenharia de Software, Finanças, Marketing...' }}">
                <datalist id="area-suggestions">
                    <option value="Engenharia de Software">
                    <option value="Produto">
                    <option value="Design">
                    <option value="Dados e Analytics">
                    <option value="Marketing">
                    <option value="Finanças">
                    <option value="Vendas">
                    <option value="DevOps / Infraestrutura">
                    <option value="Gestão">
                    <option value="Direito">
                    <option value="RH e Pessoas">
                    <option value="Operações">
                </datalist>
            </div>

            <div class="actions between">
                <a class="btn secondary" href="{{ route('career.index') }}">{{ $en ? 'Cancel' : 'Cancelar' }}</a>
                <button class="btn" type="submit" data-loading-text="{{ $en ? 'Saving...' : 'Salvando...' }}">{{ $en ? 'Save profile' : 'Salvar perfil' }}</button>
            </div>
            <p class="loading-hint">{{ $en ? 'Saving profile data...' : 'Salvando dados do perfil...' }}</p>
        </form>

        <aside class="stack-lg">
            <article class="card stack">
                <div>
                    <p class="eyebrow">{{ $en ? 'Profile quality' : 'Qualidade do perfil' }}</p>
                    <h2>{{ $profileCompleteness * 20 }}%</h2>
                    <p>{{ $en ? 'A complete profile improves headers, summary generation and recruiter readability.' : 'Um perfil completo melhora cabeçalhos, geração de resumo e leitura por recrutadores.' }}</p>
                </div>
                <div class="progress"><span style="width: {{ $profileCompleteness * 20 }}%"></span></div>
            </article>

            <article class="card stack">
                <div>
                    <p class="eyebrow">{{ $en ? 'Writing guide' : 'Guia de escrita' }}</p>
                    <h2>{{ $en ? 'Keep it premium and precise' : 'Mantenha premium e preciso' }}</h2>
                </div>
                <ul class="split-list">
                    <li>{{ $en ? 'Use concrete scope: products, systems, APIs, SaaS, data or teams.' : 'Use escopo concreto: produtos, sistemas, APIs, SaaS, dados ou times.' }}</li>
                    <li>{{ $en ? 'Avoid generic claims without evidence.' : 'Evite afirmações genéricas sem evidência.' }}</li>
                    <li>{{ $en ? 'Let achievements carry metrics; keep profile summary clean.' : 'Deixe métricas nas conquistas; mantenha o resumo limpo.' }}</li>
                    <li>{{ $en ? 'Prefer professional links that support recruiter validation.' : 'Prefira links profissionais que ajudem validação por recrutadores.' }}</li>
                </ul>
            </article>
        </aside>
    </section>
@endsection
