@extends('layouts.app')

@section('content')
    @php($en = app()->getLocale() === 'en')

    <section class="auth-shell">
        <x-auth.value-panel
            :eyebrow="$en ? 'Access' : 'Acesso'"
            :title="$en ? 'Log in to Vyrko' : 'Entre no Vyrko'"
            :subtitle="$en ? 'Tailor your resume for each job without inventing experience. See exactly which information was used.' : 'Adapte seu currículo para cada vaga sem inventar experiências. Veja exatamente quais informações foram usadas.'"
            :bullets="[
                $en ? 'Job match in a few minutes' : 'Match com a vaga em poucos minutos',
                $en ? 'Resume optimized for ATS and recruiters' : 'Currículo otimizado para ATS e recrutadores',
                $en ? 'Traceable evidence from your Career Inventory' : 'Evidências rastreáveis do seu Inventário de Carreira',
            ]"
        />

        <form class="auth-card stack-lg" method="POST" action="{{ route('login') }}" data-loading>
            @csrf
            <div>
                <p class="eyebrow">{{ $en ? 'Secure sign in' : 'Entrada segura' }}</p>
                <h2>{{ $en ? 'Access your account' : 'Acesse sua conta' }}</h2>
                <p>{{ $en ? 'Continue with Google, LinkedIn or email.' : 'Continue com Google, LinkedIn ou e-mail.' }}</p>
            </div>

            <x-auth.social-buttons
                :google-label="$en ? 'Continue with Google' : 'Continuar com Google'"
                :linkedin-label="$en ? 'Continue with LinkedIn' : 'Continuar com LinkedIn'"
            />

            <div class="auth-divider"><span>{{ $en ? 'or' : 'ou' }}</span></div>

            <div class="field">
                <label for="email">E-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="voce@email.com" autocomplete="email" required autofocus>
            </div>
            <div class="field">
                <label for="password">{{ $en ? 'Password' : 'Senha' }}</label>
                <input id="password" name="password" type="password" placeholder="••••••••" autocomplete="current-password" required>
            </div>

            <div class="auth-row">
                <label class="checkbox-line">
                    <input name="remember" type="checkbox" value="1"> {{ $en ? 'Remember me' : 'Manter conectado' }}
                </label>
                <a class="muted-link" href="{{ route('password.request') }}">{{ $en ? 'Forgot password?' : 'Esqueci minha senha' }}</a>
            </div>

            <button class="btn" type="submit" data-loading-text="{{ $en ? 'Signing in...' : 'Entrando...' }}">{{ __('messages.nav.login') }}</button>
            <p class="loading-hint">{{ $en ? 'Validating your credentials...' : 'Validando suas credenciais...' }}</p>

            <p class="auth-switch">
                {{ $en ? 'New here?' : 'Novo por aqui?' }}
                <a href="{{ route('register') }}">{{ $en ? 'Create your Career Inventory' : 'Crie seu Inventário de Carreira' }}</a>
            </p>

            <p class="auth-note">{{ $en ? 'No scraping. No improper automation. You control which information is used.' : 'Sem scraping. Sem automação indevida. Você controla quais informações serão usadas.' }}</p>
        </form>
    </section>
@endsection
