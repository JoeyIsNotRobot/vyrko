@extends('layouts.app')

@section('content')
    @php($en = app()->getLocale() === 'en')

    <section class="auth-shell">
        <x-auth.value-panel
            eyebrow="MVP"
            :title="$en ? 'Create your Career Inventory' : 'Crie seu Inventário de Carreira'"
            :subtitle="$en ? 'Start with Google, LinkedIn or email. Then import your resume or paste your profile to generate tailored resumes.' : 'Comece com Google, LinkedIn ou e-mail. Depois importe seu currículo ou cole seu perfil para gerar currículos personalizados.'"
            :bullets="[
                $en ? 'Test the core flow before completing every detail' : 'Teste o fluxo básico antes de completar todo o perfil',
                $en ? 'Use resume import, LinkedIn connection or guided forms' : 'Use importação de currículo, LinkedIn ou formulários guiados',
                $en ? 'AI improves writing without inventing experience' : 'A IA melhora a escrita sem inventar experiências',
            ]"
        />

        <form class="auth-card stack-lg" method="POST" action="{{ route('register') }}" data-loading>
            @csrf
            <div>
                <p class="eyebrow">{{ $en ? 'Start now' : 'Comece agora' }}</p>
                <h2>{{ $en ? 'Create account' : 'Criar conta' }}</h2>
                <p>{{ $en ? 'Create your account and continue to quick onboarding.' : 'Crie sua conta e continue para o onboarding rápido.' }}</p>
            </div>

            <x-auth.social-buttons
                :google-label="$en ? 'Sign up with Google' : 'Cadastrar com Google'"
                :linkedin-label="$en ? 'Sign up with LinkedIn' : 'Cadastrar com LinkedIn'"
            />

            <div class="auth-divider"><span>{{ $en ? 'or' : 'ou' }}</span></div>

            <div class="field">
                <label for="name">{{ __('messages.fields.name') }}</label>
                <input id="name" name="name" value="{{ old('name') }}" placeholder="{{ $en ? 'Your full name' : 'Seu nome completo' }}" autocomplete="name" required>
            </div>
            <div class="field">
                <label for="email">E-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="voce@email.com" autocomplete="email" required>
            </div>
            <div class="form-row">
                <div class="field">
                    <label for="password">{{ $en ? 'Password' : 'Senha' }}</label>
                    <input id="password" name="password" type="password" placeholder="••••••••" autocomplete="new-password" required>
                </div>
                <div class="field">
                    <label for="password_confirmation">{{ $en ? 'Confirm password' : 'Confirmar senha' }}</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" placeholder="••••••••" autocomplete="new-password" required>
                </div>
            </div>

            <label class="checkbox-line">
                <input name="terms" type="checkbox" value="1" required>
                {{ $en ? 'I agree to use Vyrko without adding false experience and accept the basic terms.' : 'Concordo em usar o Vyrko sem adicionar experiências falsas e aceito os termos básicos.' }}
            </label>

            <button class="btn" type="submit" data-loading-text="{{ $en ? 'Creating account...' : 'Criando conta...' }}">{{ $en ? 'Create account' : 'Criar conta' }}</button>
            <p class="loading-hint">{{ $en ? 'Creating your workspace...' : 'Criando seu workspace...' }}</p>

            <p class="auth-switch">
                {{ $en ? 'Already have an account?' : 'Já tem conta?' }}
                <a href="{{ route('login') }}">{{ $en ? 'Sign in' : 'Entrar' }}</a>
            </p>

            <p class="auth-note">{{ $en ? 'You can test the basic flow before completing the full profile.' : 'Você poderá testar o fluxo básico antes de completar todo o perfil.' }}</p>
        </form>
    </section>
@endsection
