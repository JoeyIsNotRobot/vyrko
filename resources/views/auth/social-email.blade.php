@extends('layouts.app')

@section('content')
    @php($en = app()->getLocale() === 'en')
    @php($providerLabel = $provider === 'linkedin' ? 'LinkedIn' : 'Google')

    <section class="auth-shell">
        <x-auth.value-panel
            :eyebrow="$providerLabel"
            :title="$en ? 'Confirm your email' : 'Confirme seu e-mail'"
            :subtitle="$en ? 'The provider did not return an email address. Add one to finish secure account setup.' : 'O provedor não retornou um e-mail. Adicione um para finalizar a conta com segurança.'"
            :bullets="[
                $en ? 'We only save official provider data' : 'Salvamos apenas dados oficiais do provedor',
                $en ? 'Tokens are never shown in the interface' : 'Tokens nunca aparecem na interface',
                $en ? 'You can review your data after sign-in' : 'Você pode revisar seus dados depois de entrar',
            ]"
        />

        <form class="auth-card stack-lg" method="POST" action="{{ route('auth.social.email.store', $provider) }}" data-loading>
            @csrf
            <div>
                <p class="eyebrow">{{ $en ? 'Missing email' : 'E-mail ausente' }}</p>
                <h2>{{ $en ? 'Finish social sign-in' : 'Finalize a entrada social' }}</h2>
                <p>{{ $en ? 'Use an email you control. If it already exists, sign in first to link the provider.' : 'Use um e-mail que você controla. Se ele já existir, entre primeiro para vincular o provedor.' }}</p>
            </div>

            <div class="field">
                <label for="email">E-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="voce@email.com" autocomplete="email" required autofocus>
            </div>

            <button class="btn" type="submit" data-loading-text="{{ $en ? 'Finishing...' : 'Finalizando...' }}">{{ $en ? 'Continue' : 'Continuar' }}</button>
        </form>
    </section>
@endsection
