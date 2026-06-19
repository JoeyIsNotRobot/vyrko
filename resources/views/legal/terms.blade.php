@extends('layouts.app')

@section('meta_title', 'Termos de Uso — Vyrko')
@section('meta_description', 'Termos iniciais de uso do Vyrko, incluindo responsabilidades do usuário e limites do serviço.')

@section('content')
    @php($en = app()->getLocale() === 'en')
    <x-ui.page-header eyebrow="Legal" title="{{ $en ? 'Terms of Use' : 'Termos de Uso' }}" subtitle="{{ $en ? 'Initial product template. Review with legal counsel before production.' : 'Template inicial de produto. Revise com assessoria jurídica antes de produção.' }}" />

    <section class="legal-doc card stack-lg">
        <p class="badge warning">{{ $en ? 'Legal review required before production' : 'Revisão jurídica necessária antes de produção' }}</p>
        @foreach ([
            ['Descrição do serviço', 'O Vyrko ajuda usuários a organizar informações profissionais, analisar vagas e gerar versões de currículo baseadas em dados fornecidos pelo próprio usuário.'],
            ['Conta do usuário', 'Você é responsável por manter suas credenciais seguras e por revisar as informações cadastradas na plataforma.'],
            ['Responsabilidades do usuário', 'Você é responsável pela veracidade das informações fornecidas. Não use o Vyrko para criar experiências, cargos, certificações ou métricas falsas.'],
            ['Uso aceitável', 'Não utilize a plataforma para fraudes, scraping, automação indevida, violação de direitos de terceiros ou envio de dados sem autorização.'],
            ['Ausência de garantia', 'O Vyrko não garante entrevistas, aprovação em ATS, contratação ou qualquer resultado específico em processos seletivos.'],
            ['Limitações do sistema', 'A IA pode errar, omitir contexto ou sugerir textos que precisam de revisão humana. Revise tudo antes de usar.'],
            ['Planos e pagamentos', 'Durante o MVP, recursos e limites podem mudar. Pagamentos reais só devem ser considerados ativos quando houver checkout funcional e transparente.'],
            ['Conteúdo do usuário', 'As informações profissionais continuam sendo suas. Você concede permissão para processá-las dentro do serviço enquanto mantiver sua conta.'],
            ['Suspensão e alterações', 'Contas podem ser suspensas em caso de abuso. Estes termos podem ser atualizados, com nova versão quando necessário.'],
            ['Contato', 'Para dúvidas sobre os termos, use o canal de contato indicado pela equipe do Vyrko.'],
        ] as [$title, $copy])
            <article>
                <h2>{{ $title }}</h2>
                <p>{{ $copy }}</p>
            </article>
        @endforeach
    </section>
@endsection
