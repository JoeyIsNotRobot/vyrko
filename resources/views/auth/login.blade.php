@extends('layouts.app')

@section('meta_title', 'Entrar no Vyrko')
@section('meta_description', 'Entre no Vyrko para analisar vagas, revisar seu inventário e gerar currículos com base em evidências.')

@section('content')
    @php($en = app()->getLocale() === 'en')

    <section class="auth-shell">
        <x-auth.value-panel
            :eyebrow="$en ? 'Access' : 'Acesso'"
            :title="$en ? 'Log in to Vyrko' : 'Entre no Vyrko'"
            :subtitle="$en ? 'Continue where you left off: analyze jobs, review your inventory and generate resume versions based on evidence.' : 'Continue de onde parou: analise vagas, revise seu inventário e gere versões do currículo com base em evidências.'"
            :bullets="[
                $en ? 'Compare your trajectory with job requirements' : 'Compare sua trajetória com os requisitos da vaga',
                $en ? 'Identify gaps before applying' : 'Identifique gaps antes de se candidatar',
                $en ? 'Review every version before downloading' : 'Revise cada versão antes de baixar',
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

            <button class="btn" type="submit" data-loading-text="{{ $en ? 'Signing in...' : 'Entrando...' }}">{{ $en ? 'Sign in with email' : 'Entrar com e-mail' }}</button>
            <p class="loading-hint">{{ $en ? 'Validating your credentials...' : 'Validando suas credenciais...' }}</p>

            <p class="auth-switch">
                {{ $en ? 'New here?' : 'Novo por aqui?' }}
                <a href="{{ route('register') }}">{{ $en ? 'Create your Career Inventory' : 'Crie seu Inventário de Carreira' }}</a>
            </p>

            <p class="auth-note">{{ $en ? 'No scraping. You control which information is used.' : 'Sem scraping. Você controla as informações usadas.' }}</p>
        </form>
    </section>
@endsection
