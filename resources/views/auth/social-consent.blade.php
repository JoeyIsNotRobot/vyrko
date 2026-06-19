@extends('layouts.app')

@section('meta_title', 'Consentimento OAuth — Vyrko')
@section('meta_description', 'Autorize termos e uso de dados sociais antes de continuar com Google ou LinkedIn.')

@section('content')
    @php($en = app()->getLocale() === 'en')
    <section class="auth-shell single-auth">
        <form class="auth-card stack-lg" method="POST" action="{{ route('auth.social.consent.store', $provider) }}" data-loading>
            @csrf
            <p class="eyebrow">{{ $providerLabel }}</p>
            <h1>{{ $en ? 'Before continuing' : 'Antes de continuar' }}</h1>
            <p>{{ $en ? "To continue with {$providerLabel}, accept how Vyrko uses account, social and AI data." : "Para continuar com {$providerLabel}, aceite como o Vyrko usa dados de conta, sociais e IA." }}</p>

            <div class="consent-box stack">
                <label class="checkbox-line"><input name="terms_of_use" type="checkbox" value="1" required> <span>Aceito os <a href="{{ route('legal.terms') }}" target="_blank">Termos de Uso</a>.</span></label>
                <label class="checkbox-line"><input name="privacy_policy" type="checkbox" value="1" required> <span>Aceito a <a href="{{ route('legal.privacy') }}" target="_blank">Política de Privacidade</a>.</span></label>
                <label class="checkbox-line"><input name="ai_data_processing" type="checkbox" value="1" required> <span>Autorizo o processamento de dados para IA e geração de análises.</span></label>
                <label class="checkbox-line"><input name="social_data_usage" type="checkbox" value="1" required> <span>Entendo o <a href="{{ route('legal.social-data') }}" target="_blank">uso de dados de Google e LinkedIn</a>, sem scraping e sem publicação em meu nome.</span></label>
            </div>

            <button class="btn" type="submit" data-loading-text="{{ $en ? 'Continuing...' : 'Continuando...' }}">{{ $en ? 'Continue with ' : 'Continuar com ' }}{{ $providerLabel }}</button>
        </form>
    </section>
@endsection
