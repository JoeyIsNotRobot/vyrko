@extends('layouts.app')

@section('content')
    @php
        $en = app()->getLocale() === 'en';
        $accounts = $user->socialAccounts->keyBy('provider');
        $linkedin = $accounts->get('linkedin');
        $google = $accounts->get('google');
    @endphp

    <x-ui.page-header
        :eyebrow="$en ? 'Account' : 'Conta'"
        :title="$en ? 'Social connections' : 'Conexões sociais'"
        :subtitle="$en ? 'Connect Google or LinkedIn for faster access. Vyrko only stores official provider data and never scrapes profiles.' : 'Conecte Google ou LinkedIn para acesso mais rápido. O Vyrko salva apenas dados oficiais do provedor e nunca faz scraping.'"
    >
        <x-slot:actions>
            <a class="btn secondary" href="{{ route('dashboard') }}">{{ $en ? 'Dashboard' : 'Dashboard' }}</a>
        </x-slot:actions>
    </x-ui.page-header>

    <section class="summary-grid">
        <x-ui.metric-card :label="$en ? 'Connected providers' : 'Provedores conectados'" :value="$accounts->count()" :meta="$en ? 'Active social sign-in methods' : 'Métodos sociais ativos'" />
        <x-ui.metric-card label="Google" :value="$google ? 'OK' : '—'" :tone="$google ? 'success' : 'warning'" :meta="$en ? 'Fast account access' : 'Acesso rápido à conta'" />
        <x-ui.metric-card label="LinkedIn" :value="$linkedin ? 'OK' : '—'" :tone="$linkedin ? 'success' : 'warning'" :meta="$en ? 'Basic profile data only' : 'Apenas dados básicos de perfil'" />
        <x-ui.metric-card :label="$en ? 'Data promise' : 'Promessa de dados'" :value="$en ? 'No scrape' : 'Sem scrape'" :meta="$en ? 'You control what is used' : 'Você controla o que será usado'" />
    </section>

    <section class="grid grid-2">
        <article class="card stack-lg">
            <div class="actions between">
                <div>
                    <p class="eyebrow">Google</p>
                    <h2>{{ $google ? ($en ? 'Google connected' : 'Google conectado') : ($en ? 'Connect Google' : 'Conectar Google') }}</h2>
                    <p>{{ $en ? 'Use Google for fast and secure sign-in.' : 'Use Google para entrada rápida e segura.' }}</p>
                </div>
                <span class="badge {{ $google ? '' : 'warning' }}">{{ $google ? ($en ? 'Connected' : 'Conectado') : ($en ? 'Not connected' : 'Não conectado') }}</span>
            </div>

            @if ($google)
                <div class="connection-profile">
                    @if ($google->avatar_url)
                        <img src="{{ $google->avatar_url }}" alt="" loading="lazy">
                    @endif
                    <div>
                        <strong>{{ $google->name ?: $user->name }}</strong>
                        <p>{{ $google->email ?: $user->email }}</p>
                        <p class="muted">{{ $en ? 'Connected at' : 'Conectado em' }} {{ $google->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
                <div class="actions">
                    <a class="btn secondary" href="{{ route('auth.social.redirect', 'google') }}" data-loading-link data-loading-text="{{ $en ? 'Updating...' : 'Atualizando...' }}">{{ $en ? 'Update basic data' : 'Atualizar dados básicos' }}</a>
                    <form method="POST" action="{{ route('account.social.disconnect', 'google') }}">
                        @csrf
                        <button class="btn danger" type="submit">{{ $en ? 'Disconnect' : 'Desconectar' }}</button>
                    </form>
                </div>
            @else
                <a class="btn" href="{{ route('auth.social.redirect', 'google') }}" data-loading-link data-loading-text="{{ $en ? 'Connecting to Google...' : 'Conectando ao Google...' }}">{{ $en ? 'Connect Google' : 'Conectar Google' }}</a>
            @endif
        </article>

        <article class="card stack-lg">
            <div class="actions between">
                <div>
                    <p class="eyebrow">LinkedIn</p>
                    <h2>{{ $linkedin ? ($en ? 'LinkedIn connected' : 'LinkedIn conectado') : ($en ? 'Connect LinkedIn' : 'Conectar LinkedIn') }}</h2>
                    <p>{{ $en ? 'Connect LinkedIn to simplify access. Complete profile data depends on available permissions.' : 'Conecte seu LinkedIn para facilitar o acesso. Dados completos dependem das permissões disponíveis.' }}</p>
                </div>
                <span class="badge {{ $linkedin ? '' : 'warning' }}">{{ $linkedin ? ($en ? 'Connected' : 'Conectado') : ($en ? 'Not connected' : 'Não conectado') }}</span>
            </div>

            @if ($linkedin)
                <div class="connection-profile">
                    @if ($linkedin->avatar_url)
                        <img src="{{ $linkedin->avatar_url }}" alt="" loading="lazy">
                    @endif
                    <div>
                        <strong>{{ $linkedin->name ?: $user->name }}</strong>
                        <p>{{ $linkedin->email ?: ($en ? 'Email not returned' : 'E-mail não retornado') }}</p>
                        <p class="muted">{{ $en ? 'Connected at' : 'Conectado em' }} {{ $linkedin->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>

                <div class="empty-state">
                    <div>
                        <h3>{{ $en ? 'LinkedIn returned basic data only' : 'O LinkedIn retornou apenas dados básicos' }}</h3>
                        <p>{{ $en ? 'To complete experiences, skills and summary, import a resume or paste profile text manually.' : 'Para completar experiências, skills e resumo profissional, importe um currículo ou cole o texto do perfil manualmente.' }}</p>
                        <p class="empty-example">{{ $en ? 'No scraping. No improper automation.' : 'Sem scraping. Sem automação indevida.' }}</p>
                    </div>
                </div>

                <div class="actions">
                    <a class="btn secondary" href="{{ route('auth.social.redirect', 'linkedin') }}" data-loading-link data-loading-text="{{ $en ? 'Updating...' : 'Atualizando...' }}">{{ $en ? 'Update basic data' : 'Atualizar dados básicos' }}</a>
                    <a class="btn secondary" href="{{ route('onboarding.import') }}#arquivo">{{ $en ? 'Import resume' : 'Importar currículo' }}</a>
                    <a class="btn secondary" href="{{ route('onboarding.import') }}#colar">{{ $en ? 'Paste profile manually' : 'Colar perfil manualmente' }}</a>
                    <a class="btn" href="{{ route('career.index') }}">{{ $en ? 'Fill inventory' : 'Preencher Inventário' }}</a>
                    <form method="POST" action="{{ route('account.social.disconnect', 'linkedin') }}">
                        @csrf
                        <button class="btn danger" type="submit">{{ $en ? 'Disconnect' : 'Desconectar' }}</button>
                    </form>
                </div>
            @else
                <x-ui.empty-state
                    :title="$en ? 'LinkedIn not connected' : 'LinkedIn não conectado'"
                    :description="$en ? 'Connect LinkedIn to simplify onboarding. If LinkedIn does not release full profile data, you can paste it manually.' : 'Conecte seu LinkedIn para facilitar o onboarding. Caso o LinkedIn não libere todos os dados, você poderá colar manualmente.'"
                    :example="$en ? 'No scraping. You control which information is used.' : 'Sem scraping. Você controla quais informações serão usadas.'"
                />
                <a class="btn" href="{{ route('auth.social.redirect', 'linkedin') }}" data-loading-link data-loading-text="{{ $en ? 'Connecting to LinkedIn...' : 'Conectando ao LinkedIn...' }}">{{ $en ? 'Connect LinkedIn' : 'Conectar LinkedIn' }}</a>
            @endif
        </article>
    </section>
@endsection
