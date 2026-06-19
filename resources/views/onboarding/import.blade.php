@extends('layouts.app')

@section('meta_title', 'Importação inicial — Vyrko')
@section('meta_description', 'Comece enviando um currículo ou colando textos do perfil para montar seu Inventário de Carreira no Vyrko.')

@section('content')
    @php($en = app()->getLocale() === 'en')

    <x-ui.page-header
        :eyebrow="$en ? 'Initial import' : 'Importação inicial'"
        :title="$en ? 'Start with what you already have.' : 'Comece com o que você já tem.'"
        :subtitle="$en ? 'Send a resume or paste profile text. Vyrko prepares a first Career Inventory version for you to review.' : 'Envie um currículo ou cole textos do seu perfil. O Vyrko monta uma primeira versão do seu Inventário de Carreira para você revisar.'"
    >
        <x-slot:actions>
            @if ($hasInventory)
                <a class="btn secondary" href="{{ route('career.index') }}">{{ $en ? 'Open inventory' : 'Abrir inventário' }}</a>
            @endif
            <a class="btn secondary" href="{{ route('onboarding.index') }}">{{ $en ? 'Back' : 'Voltar' }}</a>
        </x-slot:actions>
    </x-ui.page-header>

    <section class="card import-trust">
        <strong>{{ $en ? 'AI organizes information, but does not create new experience.' : 'A IA organiza as informações, mas não cria experiências novas.' }}</strong>
        @if ($hasInventory)
            <p>{{ $en ? 'You already have a Career Inventory. A new import can complement existing data.' : 'Você já tem um Inventário de Carreira. Uma nova importação pode complementar dados existentes.' }}</p>
        @else
            <p>{{ $en ? 'Choose the fastest source. You can edit everything after import.' : 'Escolha a fonte mais rápida. Você poderá editar tudo depois da importação.' }}</p>
        @endif
    </section>

    <section class="grid grid-2 import-choice-grid" data-import-choices>
        <button class="choice-card import-choice" type="button" data-import-target="arquivo">
            <strong>{{ $en ? 'Upload resume' : 'Enviar currículo' }}</strong>
            <span>{{ $en ? 'Use this if you already have PDF, DOCX or TXT with your experience.' : 'Use se você já tem PDF, DOCX ou TXT com suas experiências.' }}</span>
            <ul>
                <li>Extrai experiências, formação e habilidades</li>
                <li>Você revisa tudo depois</li>
                <li>Ideal para começar rápido</li>
            </ul>
            <em>{{ $en ? 'Upload file' : 'Enviar arquivo' }}</em>
        </button>
        <button class="choice-card import-choice" type="button" data-import-target="colar">
            <strong>{{ $en ? 'Paste profile' : 'Colar perfil' }}</strong>
            <span>{{ $en ? 'Use this to reuse LinkedIn text or an older resume.' : 'Use se você quer aproveitar textos do LinkedIn ou de um currículo antigo.' }}</span>
            <ul>
                <li>Cole headline, sobre, experiências e skills</li>
                <li>Bom quando o LinkedIn retorna apenas dados básicos</li>
                <li>Você controla o texto enviado</li>
            </ul>
            <em>{{ $en ? 'Paste information' : 'Colar informações' }}</em>
        </button>
    </section>

    <section id="arquivo" class="import-panel card stack-lg" data-import-panel hidden>
        <div>
            <p class="eyebrow">{{ $en ? 'File import' : 'Importação por arquivo' }}</p>
            <h2>{{ $en ? 'Upload your current resume' : 'Enviar currículo atual' }}</h2>
            <p>{{ $en ? 'Accepted types: PDF, DOCX and TXT. Maximum 5MB.' : 'Tipos aceitos: PDF, DOCX e TXT. Máximo 5MB.' }}</p>
        </div>
        <form class="stack" method="POST" action="{{ route('career.import') }}" enctype="multipart/form-data" data-loading>
            @csrf
            <div class="field">
                <label>{{ __('messages.fields.file') }}</label>
                <input name="resume" type="file" accept=".pdf,.docx,.txt,text/plain,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required>
                <p class="form-help">{{ $en ? 'We extract readable text and prepare inventory items for review.' : 'Extraímos texto legível e preparamos itens do inventário para revisão.' }}</p>
            </div>
            <button class="btn" type="submit" data-loading-text="{{ $en ? 'Reading file...' : 'Lendo arquivo...' }}">{{ $en ? 'Import and build inventory' : 'Importar e montar inventário' }}</button>
            <p class="loading-hint">{{ $en ? 'Reading file · Extracting text · Organizing information · Preparing review' : 'Lendo arquivo · Extraindo texto · Organizando informações · Preparando revisão' }}</p>
        </form>
    </section>

    <section id="colar" class="import-panel card stack-lg" data-import-panel hidden>
        <div>
            <p class="eyebrow">{{ $en ? 'Manual source' : 'Fonte manual' }}</p>
            <h2>{{ $en ? 'Paste profile information' : 'Colar informações do perfil' }}</h2>
            <p>{{ $en ? 'Use clear sections. Raw text is optional and can stay collapsed.' : 'Use seções claras. Texto bruto é opcional e pode ficar recolhido.' }}</p>
        </div>
        <form class="stack" method="POST" action="{{ route('onboarding.import.text') }}" data-loading>
            @csrf
            <div class="field">
                <label>Headline</label>
                <input name="headline" value="{{ old('headline', $profile?->headline) }}" placeholder="{{ $en ? 'Data Analyst · SQL · Power BI' : 'Analista de Dados · SQL · Power BI' }}">
            </div>
            <div class="field">
                <label>{{ $en ? 'About' : 'Sobre' }}</label>
                <textarea class="compact-textarea" name="about" placeholder="{{ $en ? 'Paste your About section...' : 'Cole sua seção Sobre...' }}">{{ old('about', $profile?->about) }}</textarea>
            </div>
            <div class="form-row">
                <div class="field">
                    <label>{{ $en ? 'Experiences' : 'Experiências' }}</label>
                    <textarea class="compact-textarea" name="experiences_text" placeholder="{{ $en ? 'Role, company, period and bullets...' : 'Cargo, empresa, período e bullets...' }}">{{ old('experiences_text', $profile?->experiences_text) }}</textarea>
                </div>
                <div class="field">
                    <label>Skills</label>
                    <textarea class="compact-textarea" name="skills_text" placeholder="Excel, SQL, Power BI, Atendimento, CRM...">{{ old('skills_text', $profile?->skills_text) }}</textarea>
                </div>
            </div>
            <details class="advanced-box">
                <summary>{{ $en ? 'Optional raw text' : 'Texto bruto opcional' }}</summary>
                <div class="field">
                    <label>{{ $en ? 'Extra text' : 'Texto extra' }}</label>
                    <textarea class="compact-textarea" name="raw_text" placeholder="{{ $en ? 'Paste any extra profile text...' : 'Cole qualquer texto extra do perfil...' }}">{{ old('raw_text', $profile?->raw_text) }}</textarea>
                </div>
            </details>
            <button class="btn" type="submit" data-loading-text="{{ $en ? 'Building inventory...' : 'Montando inventário...' }}">{{ $en ? 'Build inventory' : 'Montar inventário' }}</button>
            <p class="loading-hint">{{ $en ? 'Saving source · Organizing information · Preparing review' : 'Salvando fonte · Organizando informações · Preparando revisão' }}</p>
        </form>
    </section>
@endsection
