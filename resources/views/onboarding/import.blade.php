@extends('layouts.app')

@section('content')
    @php($en = app()->getLocale() === 'en')

    <x-ui.page-header
        :eyebrow="$en ? 'Initial import' : 'Importação inicial'"
        :title="$en ? 'Import or paste your career source' : 'Importe ou cole sua fonte de carreira'"
        :subtitle="$en ? 'These details become the base of your Career Inventory. AI can improve writing, but will not invent experience.' : 'Essas informações serão usadas como base do Inventário. A IA pode melhorar a escrita, mas não vai inventar experiências.'"
    >
        <x-slot:actions>
            <a class="btn secondary" href="{{ route('onboarding.index') }}">{{ $en ? 'Back to onboarding' : 'Voltar ao onboarding' }}</a>
            <a class="btn" href="{{ route('career.index') }}">{{ $en ? 'Open inventory' : 'Abrir inventário' }}</a>
        </x-slot:actions>
    </x-ui.page-header>

    <section class="grid grid-2">
        <form id="arquivo" class="card stack-lg" method="POST" action="{{ route('career.import') }}" enctype="multipart/form-data" data-loading>
            @csrf
            <div>
                <p class="eyebrow">{{ $en ? 'File import' : 'Importação por arquivo' }}</p>
                <h2>{{ $en ? 'Upload resume' : 'Enviar currículo' }}</h2>
                <p>{{ $en ? 'Supports PDF, DOCX and TXT. Keep the file truthful and review extracted data afterward.' : 'Suporta PDF, DOCX e TXT. Mantenha o arquivo verdadeiro e revise os dados extraídos depois.' }}</p>
            </div>
            <div class="field">
                <label>{{ __('messages.fields.file') }}</label>
                <input name="resume" type="file" accept=".pdf,.docx,.txt,text/plain,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required>
                <p class="form-help">{{ $en ? 'Maximum 5MB. DOCX support extracts readable document text.' : 'Máximo 5MB. O suporte DOCX extrai texto legível do documento.' }}</p>
                @error('resume')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
            <button class="btn" type="submit" data-loading-text="{{ $en ? 'Importing...' : 'Importando...' }}">{{ __('messages.actions.import') }}</button>
            <p class="loading-hint">{{ $en ? 'Extracting resume text and filling the inventory...' : 'Extraindo texto do currículo e preenchendo o inventário...' }}</p>
        </form>

        <form id="colar" class="card stack-lg" method="POST" action="{{ route('onboarding.import.text') }}" data-loading>
            @csrf
            <div>
                <p class="eyebrow">{{ $en ? 'Manual profile' : 'Perfil manual' }}</p>
                <h2>{{ $en ? 'Paste LinkedIn or resume text' : 'Cole LinkedIn ou currículo' }}</h2>
                <p>{{ $en ? 'Use this when LinkedIn returns only basic account data.' : 'Use quando o LinkedIn retornar apenas dados básicos da conta.' }}</p>
            </div>

            <div class="field">
                <label>Headline</label>
                <input name="headline" value="{{ old('headline', $profile?->headline) }}" placeholder="{{ $en ? 'Backend Engineer · Laravel · SaaS' : 'Engenheiro Backend · Laravel · SaaS' }}">
            </div>
            <div class="field">
                <label>About</label>
                <textarea class="compact-textarea" name="about" placeholder="{{ $en ? 'Paste your About section...' : 'Cole sua seção Sobre...' }}">{{ old('about', $profile?->about) }}</textarea>
            </div>
            <div class="form-row">
                <div class="field">
                    <label>{{ $en ? 'Experiences' : 'Experiências' }}</label>
                    <textarea class="compact-textarea" name="experiences_text" placeholder="{{ $en ? 'Paste roles and bullets...' : 'Cole cargos e bullets...' }}">{{ old('experiences_text', $profile?->experiences_text) }}</textarea>
                </div>
                <div class="field">
                    <label>Skills</label>
                    <textarea class="compact-textarea" name="skills_text" placeholder="{{ $en ? 'Paste skill list...' : 'Cole a lista de habilidades...' }}">{{ old('skills_text', $profile?->skills_text) }}</textarea>
                </div>
            </div>
            <div class="field">
                <label>{{ $en ? 'Optional raw text' : 'Texto bruto opcional' }}</label>
                <textarea class="compact-textarea" name="raw_text" placeholder="{{ $en ? 'Paste any extra profile text...' : 'Cole qualquer texto extra do perfil...' }}">{{ old('raw_text', $profile?->raw_text) }}</textarea>
            </div>

            <button class="btn" type="submit" data-loading-text="{{ $en ? 'Saving...' : 'Salvando...' }}">{{ $en ? 'Save and build inventory' : 'Salvar e montar inventário' }}</button>
            <p class="loading-hint">{{ $en ? 'Saving manual profile source...' : 'Salvando fonte manual do perfil...' }}</p>
        </form>
    </section>
@endsection
