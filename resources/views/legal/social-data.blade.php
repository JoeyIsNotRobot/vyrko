@extends('layouts.app')

@section('meta_title', 'Uso de dados de Google e LinkedIn — Vyrko')
@section('meta_description', 'Como o Vyrko usa dados oficiais retornados por Google e LinkedIn, sem scraping ou publicação em seu nome.')

@section('content')
    <x-ui.page-header eyebrow="Legal" title="Uso de dados de Google e LinkedIn" subtitle="Entenda quais dados podem ser recebidos e como eles ajudam autenticação, onboarding e controle da conta." />

    <section class="legal-doc card stack-lg">
        <p>Quando você entra com Google ou LinkedIn, usamos os dados retornados pelo provedor para autenticar sua conta e facilitar seu onboarding. Esses dados podem incluir nome, e-mail, foto e identificador da conta.</p>
        <div class="grid grid-2">
            <article class="soft-card">
                <h2>O que usamos</h2>
                <ul class="split-list">
                    <li>Nome e e-mail retornados oficialmente pelo provedor.</li>
                    <li>Foto e identificador da conta, quando disponíveis.</li>
                    <li>Dados básicos para vincular ou desconectar a conta.</li>
                </ul>
            </article>
            <article class="soft-card">
                <h2>O que não fazemos</h2>
                <ul class="split-list">
                    <li>Não publicamos nada em seu nome.</li>
                    <li>Não acessamos mensagens.</li>
                    <li>Não fazemos scraping do LinkedIn.</li>
                    <li>Não prometemos importar perfil completo automaticamente.</li>
                </ul>
            </article>
        </div>
        <article>
            <h2>Limitação do LinkedIn</h2>
            <p>O LinkedIn pode retornar apenas dados básicos, dependendo das permissões disponíveis. Quando isso acontecer, você poderá colar seu perfil manualmente ou importar um currículo.</p>
        </article>
        <article>
            <h2>Controle do usuário</h2>
            <p>Você pode desconectar Google ou LinkedIn na área Minha conta. Para segurança, o Vyrko impede desconectar o último método de login sem criar uma senha ou conectar outro método.</p>
        </article>
    </section>
@endsection
