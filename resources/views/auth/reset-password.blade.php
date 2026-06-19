@extends('layouts.app')

@section('content')
    @php($en = app()->getLocale() === 'en')

    <section class="auth-shell">
        <x-auth.value-panel
            :eyebrow="$en ? 'Recovery' : 'Recuperação'"
            :title="$en ? 'Create a new password' : 'Crie uma nova senha'"
            :subtitle="$en ? 'Set a strong password to keep email sign-in active.' : 'Defina uma senha forte para manter o login por e-mail ativo.'"
            :bullets="[
                $en ? 'Keeps social providers optional' : 'Mantém provedores sociais opcionais',
                $en ? 'Required before disconnecting the last social account' : 'Necessário antes de desconectar a última conta social',
                $en ? 'Use a unique password' : 'Use uma senha única',
            ]"
        />

        <form class="auth-card stack-lg" method="POST" action="{{ route('password.store') }}" data-loading>
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div>
                <p class="eyebrow">{{ $en ? 'Password' : 'Senha' }}</p>
                <h2>{{ $en ? 'Reset access' : 'Redefinir acesso' }}</h2>
            </div>
            <div class="field">
                <label for="email">E-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email', $request->email) }}" autocomplete="email" required>
            </div>
            <div class="field">
                <label for="password">{{ $en ? 'New password' : 'Nova senha' }}</label>
                <input id="password" name="password" type="password" autocomplete="new-password" required>
            </div>
            <div class="field">
                <label for="password_confirmation">{{ $en ? 'Confirm password' : 'Confirmar senha' }}</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
            </div>
            <button class="btn" type="submit" data-loading-text="{{ $en ? 'Saving...' : 'Salvando...' }}">{{ $en ? 'Save password' : 'Salvar senha' }}</button>
        </form>
    </section>
@endsection
