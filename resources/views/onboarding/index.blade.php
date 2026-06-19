@extends('layouts.app')

@section('content')
    @php($en = app()->getLocale() === 'en')

    <x-ui.page-header
        :eyebrow="$en ? 'Quick onboarding' : 'Onboarding rápido'"
        :title="$en ? 'Build a usable Career Inventory fast' : 'Crie um Inventário de Carreira útil rapidamente'"
        :subtitle="$en ? 'Choose how to start, define your target role and continue with resume import, LinkedIn or guided manual entry.' : 'Escolha como começar, defina seu cargo alvo e continue com importação de currículo, LinkedIn ou preenchimento guiado.'"
    >
        <x-slot:actions>
            <a class="btn secondary" href="{{ route('dashboard') }}">{{ $en ? 'Skip for now' : 'Pular por enquanto' }}</a>
        </x-slot:actions>
    </x-ui.page-header>

    <form class="stack-lg" method="POST" action="{{ route('onboarding.store') }}" data-loading>
        @csrf

        <section class="card stack-lg">
            <div>
                <p class="eyebrow">{{ $en ? 'Step 1' : 'Passo 1' }}</p>
                <h2>{{ $en ? 'Choose how to start' : 'Escolha como começar' }}</h2>
                <p>{{ $en ? 'You can change this later. Vyrko never invents experience.' : 'Você pode mudar depois. O Vyrko nunca inventa experiências.' }}</p>
            </div>

            <div class="choice-grid">
                @foreach ([
                    'resume' => [$en ? 'Import resume' : 'Importar currículo', $en ? 'Upload PDF, DOCX or TXT to fill your inventory.' : 'Envie PDF, DOCX ou TXT para preencher seu inventário.', $en ? 'Import file' : 'Importar arquivo'],
                    'linkedin' => [$en ? 'Connect LinkedIn' : 'Conectar LinkedIn', $en ? 'Use basic LinkedIn data and complete the rest manually.' : 'Use dados básicos do LinkedIn e complete o restante manualmente.', $en ? 'Connect LinkedIn' : 'Conectar LinkedIn'],
                    'paste' => [$en ? 'Paste profile' : 'Colar perfil', $en ? 'Paste LinkedIn or current resume text.' : 'Cole conteúdo do LinkedIn ou currículo atual.', $en ? 'Paste text' : 'Colar texto'],
                    'manual' => [$en ? 'Fill manually' : 'Preencher manualmente', $en ? 'Start from zero with guided forms.' : 'Comece do zero com formulário guiado.', $en ? 'Fill now' : 'Preencher agora'],
                ] as $value => [$title, $description, $cta])
                    <label class="choice-card">
                        <input type="radio" name="source" value="{{ $value }}" @checked(old('source', 'resume') === $value)>
                        <strong>{{ $title }}</strong>
                        <span>{{ $description }}</span>
                        <em>{{ $cta }}</em>
                    </label>
                @endforeach
            </div>
        </section>

        <section class="content-grid">
            <article class="card stack-lg">
                <div>
                    <p class="eyebrow">{{ $en ? 'Step 2' : 'Passo 2' }}</p>
                    <h2>{{ $en ? 'Target role' : 'Cargo alvo' }}</h2>
                    <p>{{ $en ? 'These fields guide resume tone, language and prioritization.' : 'Esses campos orientam tom, idioma e priorização dos currículos.' }}</p>
                </div>

                <div class="form-row">
                    <div class="field">
                        <label>{{ $en ? 'Target role' : 'Cargo alvo' }}</label>
                        <input name="target_role" value="{{ old('target_role', $profile?->target_role) }}" placeholder="{{ $en ? 'Senior Backend Engineer' : 'Desenvolvedor Backend Sênior' }}">
                    </div>
                    <div class="field">
                        <label>{{ $en ? 'Seniority' : 'Senioridade' }}</label>
                        <input name="target_seniority" value="{{ old('target_seniority', $profile?->target_seniority) }}" placeholder="{{ $en ? 'Senior' : 'Sênior' }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="field">
                        <label>{{ $en ? 'Main application country' : 'País principal de candidatura' }}</label>
                        <input name="target_country" value="{{ old('target_country', $profile?->location_country) }}" placeholder="{{ $en ? 'Brazil, United States, Portugal...' : 'Brasil, Estados Unidos, Portugal...' }}">
                    </div>
                    <div class="field">
                        <label>{{ $en ? 'Main language' : 'Idioma principal' }}</label>
                        <select name="preferred_language">
                            <option value="pt_BR" @selected(old('preferred_language', $profile?->preferred_language ?? 'pt_BR') === 'pt_BR')>{{ __('messages.common.portuguese') }}</option>
                            <option value="en" @selected(old('preferred_language', $profile?->preferred_language) === 'en')>{{ __('messages.common.english') }}</option>
                        </select>
                    </div>
                </div>

                <div class="field">
                    <label>{{ $en ? 'Professional area' : 'Área profissional' }}</label>
                    <input name="professional_area" value="{{ old('professional_area', $profile?->professional_area) }}" placeholder="{{ $en ? 'Backend, Data, Product, DevOps...' : 'Backend, Dados, Produto, DevOps...' }}">
                </div>
            </article>

            <aside class="card stack-lg">
                <div>
                    <p class="eyebrow">{{ $en ? 'Step 3' : 'Passo 3' }}</p>
                    <h2>{{ $en ? 'Confirm next steps' : 'Confirme os próximos passos' }}</h2>
                    <p>{{ $en ? 'Your first inventory version will be based on the path you choose.' : 'A primeira versão do inventário será baseada no caminho escolhido.' }}</p>
                </div>

                <ul class="split-list">
                    <li>{{ $en ? 'Name' : 'Nome' }}: {{ auth()->user()->name }}</li>
                    <li>{{ $en ? 'Email' : 'E-mail' }}: {{ auth()->user()->email }}</li>
                    <li>{{ $en ? 'You can review everything before generating a resume.' : 'Você poderá revisar tudo antes de gerar um currículo.' }}</li>
                </ul>

                <button class="btn" type="submit" data-loading-text="{{ $en ? 'Creating inventory...' : 'Criando inventário...' }}">{{ $en ? 'Create my Inventory' : 'Criar meu Inventário' }}</button>
                <p class="loading-hint">{{ $en ? 'Preparing your onboarding path...' : 'Preparando seu caminho de onboarding...' }}</p>
            </aside>
        </section>
    </form>
@endsection
