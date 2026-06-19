@extends('layouts.app')

@section('meta_title', 'Política de Privacidade — Vyrko')
@section('meta_description', 'Política inicial de privacidade do Vyrko sobre dados de conta, currículo, IA e integrações sociais.')

@section('content')
    @php($en = app()->getLocale() === 'en')
    <x-ui.page-header eyebrow="Legal" title="{{ $en ? 'Privacy Policy' : 'Política de Privacidade' }}" subtitle="{{ $en ? 'Initial product template. Review with legal counsel before production.' : 'Template inicial de produto. Revise com assessoria jurídica antes de produção.' }}" />

    <section class="legal-doc card stack-lg">
        <p class="badge warning">{{ $en ? 'Legal review required before production' : 'Revisão jurídica necessária antes de produção' }}</p>
        @foreach ([
            ['Dados coletados', 'Podemos coletar nome, e-mail, senha protegida, dados de autenticação, preferências de idioma e registros de uso da plataforma.'],
            ['Dados enviados pelo usuário', 'Currículos, textos de LinkedIn, experiências, habilidades, projetos, formação e idiomas são usados para montar o Inventário de Carreira.'],
            ['Arquivos enviados', 'PDF, DOCX ou TXT podem ser processados para extração de texto e organização do inventário.'],
            ['Google e LinkedIn', 'Usamos apenas dados oficiais retornados pelos provedores, como nome, e-mail, foto e identificador. Não fazemos scraping.'],
            ['Finalidade', 'Tratamos dados para autenticar usuários, montar inventários, analisar vagas, gerar currículos, exibir histórico e melhorar a experiência.'],
            ['Uso de IA', 'Dados fornecidos podem ser enviados ao provedor de IA configurado para análise e geração de respostas.'],
            ['Armazenamento e segurança', 'Aplicamos controles razoáveis de segurança e não exibimos tokens ou segredos na interface.'],
            ['Compartilhamento', 'Dados podem ser processados por provedores de infraestrutura, e-mail, autenticação e IA necessários ao funcionamento do produto.'],
            ['Retenção e exclusão', 'Dados permanecem enquanto a conta existir ou pelo período necessário para obrigações legais e operacionais.'],
            ['Direitos do titular', 'Você pode solicitar acesso, correção, exclusão ou informações sobre tratamento de dados conforme a legislação aplicável.'],
            ['Alterações', 'Esta política pode ser atualizada conforme o produto evoluir.'],
        ] as [$title, $copy])
            <article>
                <h2>{{ $title }}</h2>
                <p>{{ $copy }}</p>
            </article>
        @endforeach
    </section>
@endsection
