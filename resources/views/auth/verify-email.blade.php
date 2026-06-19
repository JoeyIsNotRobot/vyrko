@extends('layouts.app')

@section('meta_title', 'Confirme seu e-mail — Vyrko')
@section('meta_description', 'Confirme seu e-mail para liberar importação, análise de vagas, geração de currículos e conexões OAuth.')

@section('content')
    @php($en = app()->getLocale() === 'en')
    <section class="auth-shell single-auth">
        <article class="auth-card stack-lg">
            <p class="eyebrow">{{ $en ? 'Email verification' : 'Confirmação de e-mail' }}</p>
            <h1>{{ $en ? 'Confirm your email' : 'Confirme seu e-mail' }}</h1>
            <p>{{ $en ? "We sent a confirmation link to {$email}. Confirm before generating resumes or connecting accounts." : "Enviamos um link de confirmação para {$email}. Confirme antes de gerar currículos ou conectar contas." }}</p>

            <div class="actions">
                <form method="POST" action="{{ route('verification.send') }}" data-loading>
                    @csrf
                    <button class="btn" type="submit" data-loading-text="{{ $en ? 'Sending...' : 'Enviando...' }}">{{ $en ? 'Resend email' : 'Reenviar e-mail' }}</button>
                </form>
                <a class="btn secondary" href="{{ route('account.index') }}">{{ $en ? 'Change email' : 'Alterar e-mail' }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn secondary" type="submit">{{ $en ? 'Sign out' : 'Sair' }}</button>
                </form>
            </div>
            <p class="auth-note">{{ $en ? 'Main features stay blocked until your email is confirmed.' : 'As funcionalidades principais ficam bloqueadas até a confirmação do e-mail.' }}</p>
        </article>
    </section>
@endsection
