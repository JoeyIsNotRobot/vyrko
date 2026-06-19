@extends('layouts.app')

@section('meta_title', 'Consentimento de IA e Dados — Vyrko')
@section('meta_description', 'Entenda como o Vyrko usa dados fornecidos pelo usuário para análises, IA e geração de currículos.')

@section('content')
    <x-ui.page-header eyebrow="Legal" title="Consentimento de IA e Dados" subtitle="Texto claro sobre como seus dados informados podem ser usados para montar inventário, analisar vagas e gerar currículos." />

    <section class="legal-doc card stack-lg">
        <p class="badge warning">Revisão jurídica necessária antes de produção</p>
        @foreach ([
            ['Uso dos dados', 'Autorizo o Vyrko a processar as informações fornecidas por mim para montar meu Inventário de Carreira, analisar vagas e gerar currículos personalizados.'],
            ['Provedores de IA', 'Entendo que dados necessários podem ser enviados ao provedor de IA configurado para gerar análises e sugestões.'],
            ['Dados de terceiros', 'Devo evitar enviar dados de terceiros sem autorização.'],
            ['Controle do usuário', 'Posso revisar, editar ou excluir dados cadastrados no inventário.'],
            ['Limites da IA', 'A IA pode errar. Eu devo revisar currículos, diagnósticos e sugestões antes de usar em candidaturas.'],
            ['Sem promessa de resultado', 'O uso de IA não garante aprovação em ATS, entrevistas ou contratação.'],
        ] as [$title, $copy])
            <article>
                <h2>{{ $title }}</h2>
                <p>{{ $copy }}</p>
            </article>
        @endforeach
    </section>
@endsection
