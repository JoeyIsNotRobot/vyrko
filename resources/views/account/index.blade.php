@extends('layouts.app')

@section('meta_title', 'Minha conta — Vyrko')
@section('meta_description', 'Gerencie perfil, e-mail, senha, Google, LinkedIn, privacidade e consentimentos no Vyrko.')

@section('content')
    @php
        $en = app()->getLocale() === 'en';
        $accounts = $user->socialAccounts->keyBy('provider');
        $linkedin = $accounts->get('linkedin');
        $google = $accounts->get('google');
        $consents = $user->userConsents->sortByDesc('accepted_at')->groupBy('type');
    @endphp

    <x-ui.page-header
        :eyebrow="$en ? 'My account' : 'Minha conta'"
        :title="$en ? 'Your account' : 'Sua conta'"
        :subtitle="$en ? 'Update your basic profile, manage sign-in methods and review privacy links without exposing internal tokens.' : 'Atualize perfil básico, gerencie métodos de entrada e revise links de privacidade sem expor tokens internos.'"
    >
        <x-slot:actions>
            <a class="btn" href="{{ route('jobs.create') }}">{{ $en ? 'Analyze a job' : 'Analisar uma vaga' }}</a>
        </x-slot:actions>
    </x-ui.page-header>

    <section class="summary-grid">
        <x-ui.metric-card :label="$en ? 'Email status' : 'Status do e-mail'" :value="$user->hasVerifiedEmail() ? ($en ? 'Verified' : 'Verificado') : ($en ? 'Pending' : 'Pendente')" :tone="$user->hasVerifiedEmail() ? 'success' : 'warning'" :meta="$user->pending_email ? ($en ? 'Change pending' : 'Troca pendente') : $user->email" />
        <x-ui.metric-card :label="$en ? 'Connected providers' : 'Provedores conectados'" :value="$accounts->count()" :meta="$en ? 'Google and LinkedIn' : 'Google e LinkedIn'" />
        <x-ui.metric-card :label="$en ? 'Password login' : 'Login por senha'" :value="$user->hasPasswordLogin() ? 'OK' : '—'" :tone="$user->hasPasswordLogin() ? 'success' : 'warning'" :meta="$user->hasPasswordLogin() ? ($en ? 'Enabled' : 'Ativo') : ($en ? 'Create one before disconnecting all social accounts' : 'Crie antes de desconectar todas as contas sociais')" />
        <x-ui.metric-card :label="$en ? 'Data policy' : 'Política de dados'" :value="$en ? 'No scrape' : 'Sem scrape'" :meta="$en ? 'Official provider data only' : 'Apenas dados oficiais dos provedores'" />
    </section>

    <section class="content-grid">
        <div class="stack-lg">
            <article class="card stack-lg">
                <div>
                    <p class="eyebrow">{{ $en ? 'Basic profile' : 'Perfil básico' }}</p>
                    <h2>{{ $en ? 'Name and email' : 'Nome e e-mail' }}</h2>
                    <p>{{ $en ? 'Keep your account identity current. Email changes require confirmation.' : 'Mantenha sua identidade de conta atualizada. Alterações de e-mail exigem confirmação.' }}</p>
                </div>

                <form class="stack" method="POST" action="{{ route('account.profile.update') }}" data-loading>
                    @csrf
                    @method('PUT')
                    <div class="field">
                        <label>{{ $en ? 'Name' : 'Nome' }}</label>
                        <input name="name" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <button class="btn secondary" type="submit" data-loading-text="{{ $en ? 'Saving...' : 'Salvando...' }}">{{ $en ? 'Update name' : 'Atualizar nome' }}</button>
                </form>

                <form class="stack" method="POST" action="{{ route('account.email.update') }}" data-loading>
                    @csrf
                    @method('PUT')
                    <div class="form-row">
                        <div class="field">
                            <label>{{ $en ? 'New email' : 'Novo e-mail' }}</label>
                            <input name="email" type="email" value="{{ old('email', $user->pending_email ?: $user->email) }}" required>
                            @if ($user->pending_email)
                                <p class="form-help">{{ $en ? 'Pending confirmation:' : 'Pendente de confirmação:' }} {{ $user->pending_email }}</p>
                            @endif
                        </div>
                        @if ($user->hasPasswordLogin())
                            <div class="field">
                                <label>{{ $en ? 'Current password' : 'Senha atual' }}</label>
                                <input name="current_password" type="password" autocomplete="current-password">
                                <p class="form-help">{{ $en ? 'Required to request email change.' : 'Obrigatória para solicitar troca de e-mail.' }}</p>
                            </div>
                        @endif
                    </div>
                    <button class="btn secondary" type="submit" data-loading-text="{{ $en ? 'Sending confirmation...' : 'Enviando confirmação...' }}">{{ $en ? 'Send confirmation to new email' : 'Enviar confirmação para novo e-mail' }}</button>
                </form>

                @unless ($user->hasVerifiedEmail())
                    <form method="POST" action="{{ route('account.email.resend') }}">
                        @csrf
                        <button class="btn" type="submit">{{ $en ? 'Resend verification email' : 'Reenviar confirmação de e-mail' }}</button>
                    </form>
                @endunless
            </article>

            <article class="card stack-lg">
                <div>
                    <p class="eyebrow">{{ $en ? 'Password' : 'Senha' }}</p>
                    <h2>{{ $user->hasPasswordLogin() ? ($en ? 'Change password' : 'Alterar senha') : ($en ? 'Create a password' : 'Criar uma senha') }}</h2>
                    <p>{{ $en ? 'A password keeps access possible even if you disconnect social providers.' : 'Uma senha mantém o acesso possível mesmo se você desconectar provedores sociais.' }}</p>
                </div>
                <form class="stack" method="POST" action="{{ route('account.password.update') }}" data-loading>
                    @csrf
                    @method('PUT')
                    <div class="form-row">
                        @if ($user->hasPasswordLogin())
                            <div class="field">
                                <label>{{ $en ? 'Current password' : 'Senha atual' }}</label>
                                <input name="current_password" type="password" autocomplete="current-password">
                            </div>
                        @endif
                        <div class="field">
                            <label>{{ $en ? 'New password' : 'Nova senha' }}</label>
                            <input name="password" type="password" autocomplete="new-password" required>
                        </div>
                        <div class="field">
                            <label>{{ $en ? 'Confirm new password' : 'Confirmar nova senha' }}</label>
                            <input name="password_confirmation" type="password" autocomplete="new-password" required>
                        </div>
                    </div>
                    <button class="btn secondary" type="submit" data-loading-text="{{ $en ? 'Updating...' : 'Atualizando...' }}">{{ $user->hasPasswordLogin() ? ($en ? 'Update password' : 'Alterar senha') : ($en ? 'Create password' : 'Criar senha') }}</button>
                </form>
            </article>
        </div>

        <aside class="stack-lg">
            @foreach ([['google', 'Google', $google], ['linkedin', 'LinkedIn', $linkedin]] as [$provider, $label, $account])
                <article class="card stack-lg">
                    <div class="actions between">
                        <div>
                            <p class="eyebrow">{{ $label }}</p>
                            <h2>{{ $account ? ($label.' conectado') : ('Conectar '.$label) }}</h2>
                            <p>{{ $provider === 'linkedin' ? 'Dados completos dependem das permissões oficiais disponíveis. Sem scraping.' : 'Use autenticação oficial para acesso rápido.' }}</p>
                        </div>
                        <span class="badge {{ $account ? '' : 'warning' }}">{{ $account ? 'Conectado' : 'Não conectado' }}</span>
                    </div>

                    @if ($account)
                        <div class="connection-profile">
                            @if ($account->avatar_url && str_starts_with($account->avatar_url, 'https://'))
                                <img src="{{ $account->avatar_url }}" alt="" loading="lazy">
                            @endif
                            <div>
                                <strong>{{ $account->name ?: $user->name }}</strong>
                                <p>{{ $account->email ?: 'E-mail não retornado' }}</p>
                                <p class="muted">Conectado em {{ $account->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="actions">
                            <a class="btn secondary" href="{{ route('auth.social.redirect', $provider) }}" data-loading-link data-loading-text="Atualizando...">Atualizar dados básicos</a>
                            <form method="POST" action="{{ route('account.social.disconnect', $provider) }}">
                                @csrf
                                <button class="btn danger" type="submit">Desconectar</button>
                            </form>
                        </div>
                    @else
                        <a class="btn" href="{{ route('auth.social.redirect', $provider) }}" data-loading-link data-loading-text="Conectando...">Conectar {{ $label }}</a>
                    @endif
                </article>
            @endforeach

            <article class="card stack-lg">
                <p class="eyebrow">{{ $en ? 'Data and privacy' : 'Dados e privacidade' }}</p>
                <h2>{{ $en ? 'Your data, your control' : 'Seus dados, seu controle' }}</h2>
                <ul class="split-list">
                    <li><a href="{{ route('legal.terms') }}">Termos de Uso</a></li>
                    <li><a href="{{ route('legal.privacy') }}">Política de Privacidade</a></li>
                    <li><a href="{{ route('legal.data-consent') }}">Consentimento de IA e Dados</a></li>
                    <li><a href="{{ route('legal.social-data') }}">Uso de dados de Google e LinkedIn</a></li>
                    <li>{{ $en ? 'Download my data: coming soon' : 'Baixar meus dados: em breve' }}</li>
                </ul>
                <button class="btn danger" type="button" disabled>{{ $en ? 'Delete account — coming soon' : 'Excluir conta — em breve' }}</button>
            </article>

            <article class="card stack-lg">
                <p class="eyebrow">{{ $en ? 'Consent history' : 'Histórico de consentimentos' }}</p>
                @forelse ($consents as $type => $items)
                    <div class="list-card">
                        <div>
                            <strong>{{ str_replace('_', ' ', $type) }}</strong>
                            <p class="muted">v{{ $items->first()->version }} · {{ $items->first()->accepted_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                @empty
                    <x-ui.empty-state title="Sem consentimentos registrados" description="Os aceites aparecerão aqui após cadastro ou conexão social." example="terms_of_use · privacy_policy · ai_data_processing" />
                @endforelse
            </article>
        </aside>
    </section>
@endsection
