@extends('layouts.app')

@section('content')
    @php($en = app()->getLocale() === 'en')

    <section class="auth-shell">
        <x-auth.value-panel
            :eyebrow="$en ? 'Recovery' : 'Recuperação'"
            :title="$en ? 'Reset your password' : 'Redefina sua senha'"
            :subtitle="$en ? 'Receive a secure reset link and get back to your resume workflow.' : 'Receba um link seguro de redefinição e volte ao seu fluxo de currículos.'"
            :bullets="[
                $en ? 'No provider tokens are exposed' : 'Nenhum token de provedor é exposto',
                $en ? 'You stay in control of account access' : 'Você mantém controle do acesso',
                $en ? 'Social sign-in remains available if connected' : 'Entrada social continua disponível se conectada',
            ]"
        />

        <form class="auth-card stack-lg" method="POST" action="{{ route('password.email') }}" data-loading>
            @csrf
            <div>
                <p class="eyebrow">{{ $en ? 'Password' : 'Senha' }}</p>
                <h2>{{ $en ? 'Send reset link' : 'Enviar link de redefinição' }}</h2>
                <p>{{ $en ? 'Enter your account email and check your inbox.' : 'Informe o e-mail da conta e confira sua caixa de entrada.' }}</p>
            </div>
            <div class="field">
                <label for="email">E-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="voce@email.com" autocomplete="email" required autofocus>
            </div>
            <button class="btn" type="submit" data-loading-text="{{ $en ? 'Sending...' : 'Enviando...' }}">{{ $en ? 'Send reset link' : 'Enviar link' }}</button>
            <a class="muted-link" href="{{ route('login') }}">{{ $en ? 'Back to sign in' : 'Voltar ao login' }}</a>
        </form>
    </section>
@endsection
