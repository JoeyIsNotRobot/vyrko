@extends('layouts.app')

@section('meta_title', 'Vyrko — Currículos personalizados por vaga')
@section('meta_description', 'Analise vagas, identifique gaps e gere currículos personalizados com base em evidências reais do seu perfil.')

@section('content')
    @php
        $en = app()->getLocale() === 'en';
        $examples = [
            ['role' => 'Analista de Dados', 'match' => '82%', 'strong' => ['SQL', 'Power BI', 'Excel'], 'gap' => 'Python avançado'],
            ['role' => 'Product Manager', 'match' => '76%', 'strong' => ['Backlog', 'Métricas', 'Discovery'], 'gap' => 'B2B SaaS'],
            ['role' => 'Designer UX/UI', 'match' => '88%', 'strong' => ['Figma', 'Pesquisa', 'Protótipos'], 'gap' => 'Design system'],
            ['role' => 'Executivo de Vendas', 'match' => '79%', 'strong' => ['CRM', 'Negociação', 'Prospecção'], 'gap' => 'Enterprise sales'],
        ];
        $activeExample = $examples[2];
    @endphp

    <section id="home" class="hero landing-hero" data-section>
        <div class="stack landing-copy">
            <p class="eyebrow">Vyrko</p>
            <h1>{{ $en ? 'Tailored resumes for each job, without inventing experience.' : 'Seu currículo certo, para a vaga certa.' }}</h1>
            <p>{{ $en ? 'Paste a job description, compare it with your trajectory and generate a resume version based on real evidence from your profile.' : 'Importe seu currículo, analise vagas e gere versões personalizadas que passam pelo ATS.' }}</p>
            <div class="actions">
                <a class="btn" href="{{ route('register') }}">{{ $en ? 'Generate my first resume' : 'Gerar meu primeiro currículo' }}</a>
                <a class="btn secondary" href="#como-funciona" data-section-link="como-funciona">{{ $en ? 'See how it works' : 'Ver como funciona' }}</a>
            </div>
            <p class="trust-copy">{{ $en ? 'No approval promise. No invented experience. You review everything before downloading.' : 'Sem promessa de aprovação. Sem experiência inventada. Você revisa tudo antes de baixar.' }}</p>
        </div>

        <div class="product-mockup card stack-lg">
            <div class="actions between">
                <span class="badge">Match {{ $activeExample['match'] }}</span>
                <span class="badge warning">{{ $en ? 'Evidence-based' : 'Baseado em evidências' }}</span>
            </div>
            <h2>{{ $activeExample['role'] }}</h2>
            <div class="grid grid-2">
                <div class="soft-card">
                    <p class="muted">{{ $en ? 'Strong evidence' : 'Evidência forte' }}</p>
                    <strong>{{ implode(' · ', $activeExample['strong']) }}</strong>
                </div>
                <div class="soft-card">
                    <p class="muted">Gap</p>
                    <strong>{{ $activeExample['gap'] }}</strong>
                </div>
            </div>
            <div class="keyword-cloud">
                @foreach (['Excel', 'SQL', 'Figma', 'CRM', 'Power BI', 'Copywriting', 'Testes', 'Inglês', 'Atendimento', 'Negociação'] as $skill)
                    <span class="badge">{{ $skill }}</span>
                @endforeach
            </div>
            <div class="resume-paper compact-paper">
                <h3>{{ $en ? 'Your Name' : 'Seu Nome' }}</h3>
                <p>{{ $activeExample['role'] }} · {{ implode(' · ', $activeExample['strong']) }}</p>
                <p>{{ $en ? 'Concise summary with traceable bullets and a clear gap warning before applying.' : 'Resumo objetivo com bullets rastreáveis e alerta claro de gap antes da candidatura.' }}</p>
            </div>
        </div>
    </section>

    <section class="trust-strip grid grid-4">
        @foreach ([
            $en ? 'AI uses your Career Inventory' : 'A IA usa seu Inventário de Carreira',
            $en ? 'Each version shows evidence' : 'Cada versão mostra evidências',
            $en ? 'Gaps appear before applying' : 'Gaps aparecem antes da candidatura',
            $en ? 'You choose the model before downloading' : 'Você escolhe o modelo antes de baixar',
        ] as $item)
            <article class="card compact-card"><strong>{{ $item }}</strong></article>
        @endforeach
    </section>

    <section id="como-funciona" class="landing-section" data-section>
        <div class="section-heading">
            <p class="eyebrow">{{ $en ? 'How it works' : 'Como funciona' }}</p>
            <h2>{{ $en ? 'From profile to final resume in a few steps.' : 'Do perfil ao currículo final em poucos passos.' }}</h2>
            <p>{{ $en ? 'Vyrko organizes your information, interprets the job and prepares a resume version aligned to what was requested.' : 'O Vyrko organiza suas informações, interpreta a vaga e monta uma versão do currículo alinhada ao que foi pedido.' }}</p>
        </div>
        <div class="grid grid-4">
            @foreach ([
                ['1', 'Crie sua fonte de carreira', 'Importe um currículo, cole seu LinkedIn ou preencha manualmente experiências, habilidades, cursos e projetos.'],
                ['2', 'Cole a vaga', 'O Vyrko identifica requisitos, palavras-chave, senioridade, responsabilidades e sinais importantes da descrição.'],
                ['3', 'Veja o diagnóstico', 'Entenda onde há evidência forte, match parcial e quais pontos não devem ser afirmados.'],
                ['4', 'Gere e revise', 'Escolha um modelo, revise o texto final e baixe uma versão pronta para envio.'],
            ] as [$number, $title, $copy])
                <article class="card step-card">
                    <span class="badge">{{ $number }}</span>
                    <h3>{{ $title }}</h3>
                    <p>{{ $copy }}</p>
                </article>
            @endforeach
        </div>
        <article class="card dont-card">
            <div>
                <p class="eyebrow">{{ $en ? 'What Vyrko does not do' : 'O que o Vyrko não faz' }}</p>
                <h2>{{ $en ? 'Clear limits build trust.' : 'Limites claros aumentam confiança.' }}</h2>
            </div>
            <div class="keyword-cloud">
                @foreach (['Não inventa cargos', 'Não cria certificações falsas', 'Não garante contratação', 'Não acessa LinkedIn por scraping', 'Não envia candidaturas automaticamente'] as $item)
                    <span class="badge danger">{{ $item }}</span>
                @endforeach
            </div>
        </article>
    </section>

    <section id="recursos" class="landing-section" data-section>
        <div class="section-heading">
            <p class="eyebrow">{{ $en ? 'Features' : 'Recursos' }}</p>
            <h2>{{ $en ? 'Everything revolves around your real evidence.' : 'Tudo gira em torno das suas evidências reais.' }}</h2>
            <p>{{ $en ? 'A practical workspace for comparing jobs, reviewing gaps and producing targeted resume versions.' : 'Um espaço prático para comparar vagas, revisar gaps e produzir versões direcionadas do currículo.' }}</p>
        </div>
        <div class="grid grid-3">
            @foreach ([
                ['Inventário de Carreira', 'Uma base única para todas as versões do seu currículo.', 'Centralize experiências, conquistas, habilidades, projetos, formação e idiomas.'],
                ['Análise de vaga', 'Entenda o que a vaga realmente pede.', 'Extraia requisitos obrigatórios, diferenciais, palavras-chave e sinais de senioridade.'],
                ['Match com evidências', 'Veja por que seu perfil combina ou não com a vaga.', 'Cada requisito pode ser ligado a experiências, habilidades, projetos ou conquistas.'],
                ['Gaps reais', 'Saiba o que falta antes de enviar.', 'Identifique pontos críticos, moderados e aceitáveis sem transformar ausência em afirmação falsa.'],
                ['Currículos por modelo', 'Escolha o formato certo para cada candidatura.', 'Gere versões como ATS Classic, Tech Compact ou International Clean.'],
                ['LinkedIn manual', 'Melhore seu posicionamento sem scraping.', 'Cole textos do perfil para receber sugestões de headline, about, skills e consistência.'],
            ] as [$eyebrow, $title, $copy])
                <article class="card feature-card">
                    <p class="eyebrow">{{ $eyebrow }}</p>
                    <h3>{{ $title }}</h3>
                    <p>{{ $copy }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section id="precos" class="landing-section" data-section>
        <div class="section-heading">
            <p class="eyebrow">{{ $en ? 'Pricing' : 'Preços' }}</p>
            <h2>{{ $en ? 'Initial access, clear limits.' : 'Acesso inicial, limites claros.' }}</h2>
            <p>{{ $en ? 'During the MVP, Vyrko is in early access. Some limits may change as the product evolves.' : 'Durante o MVP, o Vyrko está em acesso inicial. Alguns limites podem mudar conforme evoluímos o produto.' }}</p>
        </div>
        <div class="grid grid-3 pricing-grid">
            @foreach ([
                ['Gratuito', 'Para testar o fluxo.', ['1 Inventário de Carreira', 'Análises limitadas de vaga', 'Gerações limitadas de currículo', 'Modelos básicos'], 'Começar grátis'],
                ['Pro', 'Para quem aplica para várias vagas.', ['Mais análises mensais', 'Mais currículos gerados', 'Análise manual de LinkedIn', 'Histórico de versões'], 'Entrar na lista Pro'],
                ['Consultor', 'Para mentores e consultores de carreira.', ['Múltiplos perfis', 'Gestão de clientes', 'Biblioteca de versões', 'Recursos futuros de white-label'], 'Falar sobre acesso'],
            ] as [$name, $description, $items, $cta])
                <article class="card pricing-card">
                    <p class="eyebrow">{{ $name }}</p>
                    <h3>{{ $description }}</h3>
                    <ul class="split-list">
                        @foreach ($items as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                    <a class="btn {{ $name === 'Gratuito' ? '' : 'secondary' }}" href="{{ route('register') }}">{{ $cta }}</a>
                </article>
            @endforeach
        </div>
    </section>
@endsection
