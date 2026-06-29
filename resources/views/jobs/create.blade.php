@extends('layouts.app')

@section('content')
    @php($en = app()->getLocale() === 'en')

    <x-ui.page-header
        :eyebrow="$en ? 'Analyze job' : 'Analisar vaga'"
        :title="$en ? 'Paste a job post and build a focused match plan' : 'Cole uma vaga e construa um plano de match objetivo'"
        :subtitle="$en ? 'Vyrko structures requirements, compares them to your evidence and prepares the next tailored resume version.' : 'O Vyrko estrutura requisitos, compara com suas evidências e prepara a próxima versão personalizada do currículo.'"
    >
        <x-slot:actions>
            <a class="btn secondary" href="{{ route('jobs.index') }}">{{ $en ? 'Saved jobs' : 'Vagas salvas' }}</a>
        </x-slot:actions>
    </x-ui.page-header>

    <section class="summary-grid">
        <x-ui.metric-card label="1" :value="$en ? 'Paste' : 'Cole'" :meta="$en ? 'Use the complete job description' : 'Use a descrição completa da vaga'" />
        <x-ui.metric-card label="2" :value="$en ? 'Extract' : 'Extraia'" :meta="$en ? 'Requirements, seniority and ATS keywords' : 'Requisitos, senioridade e palavras-chave ATS'" />
        <x-ui.metric-card label="3" :value="$en ? 'Match' : 'Cruze'" :meta="$en ? 'Strong evidence, partial fits and gaps' : 'Evidências fortes, aderências parciais e gaps'" />
        <x-ui.metric-card label="4" :value="$en ? 'Generate' : 'Gere'" tone="success" :meta="$en ? 'Template-ready resume versions' : 'Currículos prontos para modelos'" />
    </section>

    <section class="content-grid wide-aside">
        <form class="card stack-lg" method="POST" action="{{ route('jobs.store') }}" data-loading>
            @csrf

            <div class="panel-title">
                <div>
                    <p class="eyebrow">{{ $en ? 'Job input' : 'Entrada da vaga' }}</p>
                    <h2>{{ $en ? 'Core information' : 'Informações principais' }}</h2>
                    <p>{{ $en ? 'Give the workspace a clear name and keep the original job text intact.' : 'Dê um nome claro ao workspace e mantenha o texto original da vaga intacto.' }}</p>
                </div>
            </div>

            <div class="form-row">
                <div class="field">
                    <label>{{ $en ? 'Internal title' : 'Título interno' }}</label>
                    <input name="title" value="{{ old('title') }}" placeholder="{{ $en ? 'Senior Backend Engineer · fintech' : 'Pessoa Desenvolvedora Backend Sênior · fintech' }}" required>
                    <p class="form-help">{{ $en ? 'Use a title that makes this workspace easy to find later.' : 'Use um título que torne este workspace fácil de encontrar depois.' }}</p>
                </div>
                <div class="field">
                    <label>{{ __('messages.fields.company') }}</label>
                    <input name="company_name" value="{{ old('company_name') }}" placeholder="{{ $en ? 'Company name or recruiter' : 'Nome da empresa ou recrutador' }}">
                    <p class="form-help">{{ $en ? 'Optional, but useful for organizing generated resumes.' : 'Opcional, mas útil para organizar currículos gerados.' }}</p>
                </div>
            </div>

            <div class="form-row">
                <div class="field">
                    <label>{{ $en ? 'Resume language' : 'Idioma do currículo' }}</label>
                    <select name="target_language">
                        <option value="pt_BR" @selected(old('target_language', 'pt_BR') === 'pt_BR')>{{ __('messages.common.portuguese') }}</option>
                        <option value="en" @selected(old('target_language') === 'en')>{{ __('messages.common.english') }}</option>
                    </select>
                    <p class="form-help">{{ $en ? 'Choose the language of the generated resume.' : 'Escolha o idioma do currículo gerado.' }}</p>
                </div>
                <div class="field">
                    <label>{{ $en ? 'Resume type' : 'Tipo de currículo' }}</label>
                    <select name="resume_type">
                        <option value="tech" @selected(old('resume_type', 'tech') === 'tech')>Tech</option>
                        <option value="national_brazil" @selected(old('resume_type') === 'national_brazil')>{{ $en ? 'Brazilian' : 'Nacional Brasil' }}</option>
                        <option value="international" @selected(old('resume_type') === 'international')>{{ $en ? 'International' : 'Internacional' }}</option>
                        <option value="executive" @selected(old('resume_type') === 'executive')>{{ $en ? 'Executive' : 'Executivo' }}</option>
                    </select>
                    <p class="form-help">{{ $en ? 'This tunes language, density and template recommendations.' : 'Isso ajusta linguagem, densidade e recomendações de modelo.' }}</p>
                </div>
            </div>

            <div class="field">
                <label>{{ $en ? 'Full job description' : 'Descrição completa da vaga' }}</label>
                <textarea class="large-textarea" name="job_description" required placeholder="{{ $en ? 'Paste responsibilities, requirements, stack, nice-to-haves and company context...' : 'Cole responsabilidades, requisitos, stack, diferenciais e contexto da empresa...' }}">{{ old('job_description') }}</textarea>
                <p class="form-help">{{ $en ? 'Best results come from complete posts, not short summaries.' : 'Os melhores resultados vêm de vagas completas, não de resumos curtos.' }}</p>
            </div>

            <div class="field">
                <label>{{ __('messages.fields.notes') }}</label>
                <textarea class="compact-textarea" name="notes" placeholder="{{ $en ? 'Optional: recruiter notes, desired emphasis, salary context, interview signals...' : 'Opcional: observações do recrutador, ênfase desejada, contexto salarial, sinais de entrevista...' }}">{{ old('notes') }}</textarea>
                <p class="form-help">{{ $en ? 'Use this for private context that is not part of the original job post.' : 'Use para contexto privado que não faz parte do texto original da vaga.' }}</p>
            </div>

            <div class="actions between">
                <a class="btn secondary" href="{{ route('dashboard') }}">{{ $en ? 'Cancel' : 'Cancelar' }}</a>
                <button class="btn" type="submit" data-loading-text="{{ $en ? 'Creating workspace...' : 'Criando workspace...' }}">{{ $en ? 'Save and open analysis' : 'Salvar e abrir análise' }}</button>
            </div>

            <div class="loading-hint">
                {{ $en ? 'Preparing the analysis workspace...' : 'Preparando o espaço de análise...' }}
                <ul>
                    <li>{{ $en ? 'Reading job description' : 'Lendo descrição da vaga' }}</li>
                    <li>{{ $en ? 'Preparing requirement extraction' : 'Preparando extração de requisitos' }}</li>
                    <li>{{ $en ? 'Opening the report screen' : 'Abrindo tela de relatório' }}</li>
                </ul>
            </div>
        </form>

        <aside class="stack-lg">
            <article class="card stack">
                <div>
                    <p class="eyebrow">{{ $en ? 'Quality checklist' : 'Checklist de qualidade' }}</p>
                    <h2>{{ $en ? 'What makes the analysis useful' : 'O que torna a análise útil' }}</h2>
                </div>
                <ul class="split-list">
                    <li>{{ $en ? 'Responsibilities and outcomes are visible.' : 'Responsabilidades e resultados aparecem no texto.' }}</li>
                    <li>{{ $en ? 'Tools, languages, frameworks and databases are included.' : 'Ferramentas, linguagens, frameworks e bancos estão incluídos.' }}</li>
                    <li>{{ $en ? 'Seniority signals and years of experience are preserved.' : 'Sinais de senioridade e anos de experiência são preservados.' }}</li>
                    <li>{{ $en ? 'Nice-to-haves remain separate from required skills.' : 'Diferenciais continuam separados dos requisitos obrigatórios.' }}</li>
                </ul>
            </article>

            <article class="card stack">
                <div>
                    <p class="eyebrow">{{ $en ? 'Resulting flow' : 'Fluxo resultante' }}</p>
                    <h2>{{ $en ? 'From job post to resume' : 'Da vaga ao currículo' }}</h2>
                </div>
                <div class="stack">
                    <div>
                        <h3>{{ $en ? 'Requirement extraction' : 'Extração de requisitos' }}</h3>
                        <p>{{ $en ? 'Skills, seniority, responsibilities and ATS keywords become structured data.' : 'Habilidades, senioridade, responsabilidades e palavras-chave ATS viram dados estruturados.' }}</p>
                    </div>
                    <div>
                        <h3>{{ $en ? 'Evidence match' : 'Match com evidências' }}</h3>
                        <p>{{ $en ? 'Your inventory is checked against the job, separating strong matches from gaps.' : 'Seu inventário é comparado com a vaga, separando encaixes fortes de gaps.' }}</p>
                    </div>
                    <div>
                        <h3>{{ $en ? 'Template-ready resume' : 'Currículo pronto para modelo' }}</h3>
                        <p>{{ $en ? 'Generate one version and choose ATS Classic, Tech Compact or International Clean.' : 'Gere uma versão e escolha ATS Clássico, Tech Compacto ou Internacional Clean.' }}</p>
                    </div>
                </div>
            </article>
        </aside>
    </section>
@endsection
