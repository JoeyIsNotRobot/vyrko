@extends('layouts.app')

@section('meta_title', 'Criar conta — Vyrko')
@section('meta_description', 'Crie sua fonte de carreira no Vyrko e revise seus dados antes de gerar currículos personalizados.')

@section('content')
    @php($en = app()->getLocale() === 'en')

    <section class="auth-shell">
        <x-auth.value-panel
            eyebrow="Vyrko"
            :title="$en ? 'Create your career source in Vyrko' : 'Crie sua fonte de carreira no Vyrko'"
            :subtitle="$en ? 'Start by importing a resume, connecting an account or filling information manually. You can review everything before generating any resume.' : 'Comece importando um currículo, conectando uma conta ou preenchendo manualmente. Você pode revisar tudo antes de gerar qualquer currículo.'"
            :bullets="[
                $en ? 'Import PDF, DOCX or TXT when you already have a resume' : 'Importe PDF, DOCX ou TXT quando já tiver um currículo',
                $en ? 'Use Google only for official provider data' : 'Use Google apenas com dados oficiais do provedor',
                $en ? 'AI supports writing, but does not invent experience' : 'A IA apoia a escrita, mas não inventa experiências',
            ]"
        />

        <form class="auth-card stack-lg" method="POST" action="{{ route('register') }}" data-loading>
            @csrf
            <div>
                <p class="eyebrow">{{ $en ? 'New account' : 'Nova conta' }}</p>
                <h2>{{ $en ? 'Create your account' : 'Criar conta' }}</h2>
                <p>{{ $en ? 'Create your access and confirm your email before connecting accounts or generating resumes.' : 'Crie seu acesso e confirme seu e-mail antes de conectar contas ou gerar currículos.' }}</p>
            </div>

            <x-auth.social-buttons
                :google-label="$en ? 'Create account with Google' : 'Criar conta com Google'"
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

            <div class="consent-box stack">
                <label class="checkbox-line">
                    <input name="terms_of_use" type="checkbox" value="1" required @checked(old('terms_of_use'))>
                    <span>Li e aceito os <a href="{{ route('legal.terms') }}" target="_blank">Termos de Uso</a>.</span>
                </label>
                <label class="checkbox-line">
                    <input name="privacy_policy" type="checkbox" value="1" required @checked(old('privacy_policy'))>
                    <span>Li e aceito a <a href="{{ route('legal.privacy') }}" target="_blank">Política de Privacidade</a>.</span>
                </label>
                <label class="checkbox-line">
                    <input name="ai_data_processing" type="checkbox" value="1" required @checked(old('ai_data_processing'))>
                    <span>Autorizo o Vyrko a processar as informações fornecidas para montar meu Inventário de Carreira, analisar vagas e gerar currículos personalizados.</span>
                </label>
            </div>

            <button class="btn" type="submit" data-loading-text="{{ $en ? 'Creating account...' : 'Criando conta...' }}">{{ $en ? 'Create account with email' : 'Criar conta com e-mail' }}</button>
            <p class="loading-hint">{{ $en ? 'Creating your account and sending verification email...' : 'Criando sua conta e enviando confirmação de e-mail...' }}</p>

            <p class="auth-switch">
                {{ $en ? 'Already have an account?' : 'Já tem conta?' }}
                <a href="{{ route('login') }}">{{ $en ? 'Sign in' : 'Entrar' }}</a>
            </p>
        </form>
    </section>
@endsection
