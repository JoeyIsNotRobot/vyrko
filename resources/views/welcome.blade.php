@extends('layouts.app')

@section('content')
    @php($en = app()->getLocale() === 'en')

    <section class="hero" style="align-items:center; min-height: calc(100vh - 150px);">
        <div class="stack" style="max-width:760px">
            <p class="eyebrow">Vyrko</p>
            <h1>{{ $en ? 'Tailored resumes for each job, without inventing experience.' : 'Currículos personalizados para cada vaga, sem inventar experiências.' }}</h1>
            <p>{{ $en ? 'Paste a job description, see your match, identify gaps and generate a resume optimized for ATS and recruiters.' : 'Cole a descrição da vaga, veja seu match, identifique gaps e gere um currículo otimizado para ATS e recrutadores.' }}</p>
            <div class="actions">
                <a class="btn" href="{{ route('register') }}">{{ $en ? 'Generate my first resume' : 'Gerar meu primeiro currículo' }}</a>
                <a class="btn secondary" href="#como-funciona">{{ $en ? 'See how it works' : 'Ver como funciona' }}</a>
            </div>
            <p class="muted" style="font-size:14px">{{ $en ? 'Vyrko improves resume clarity and fit, but does not invent experience or guarantee approval in selection processes.' : 'O Vyrko melhora a clareza e aderência do seu currículo, mas não inventa experiências nem garante aprovação em processos seletivos.' }}</p>
        </div>

        <div class="card stack" style="min-width:min(440px,100%)">
            <div class="actions" style="justify-content:space-between">
                <span class="badge">Match 86%</span>
                <span class="muted">ATS · Tech</span>
            </div>
            <h2>{{ $en ? 'Backend Laravel role' : 'Vaga Backend Laravel' }}</h2>
            <div class="grid grid-2">
                <div class="card" style="box-shadow:none">
                    <p class="muted">{{ $en ? 'Strong evidence' : 'Evidência forte' }}</p>
                    <strong>Laravel · APIs REST · MySQL</strong>
                </div>
                <div class="card" style="box-shadow:none">
                    <p class="muted">Gap</p>
                    <strong>AWS</strong>
                </div>
            </div>
            <div>
                <div class="actions" style="justify-content:space-between"><span>ATS</span><span>92%</span></div>
                <div class="progress"><span style="width:92%"></span></div>
            </div>
            <div class="resume-paper" style="padding:18px">
                <h3>{{ $en ? 'Your Name' : 'Seu Nome' }}</h3>
                <p>Back-End Software Engineer · PHP · Laravel · SaaS</p>
                <p>{{ $en ? 'Clear summary, technical stack and bullets backed by traceable evidence.' : 'Resumo objetivo, stack técnica e bullets com evidências rastreáveis.' }}</p>
            </div>
        </div>
    </section>

    <section id="como-funciona" class="grid grid-3" style="margin-top:24px">
        @foreach ([
            ['1', $en ? 'Create your Career Inventory' : 'Crie seu Inventário de Carreira', $en ? 'Store real experiences, skills and achievements.' : 'Cadastre experiências, habilidades e conquistas reais.'],
            ['2', $en ? 'Paste the job' : 'Cole a vaga', $en ? 'Extract requirements, keywords, seniority and signals.' : 'Extraia requisitos, palavras-chave, senioridade e sinais.'],
            ['3', $en ? 'Generate and download' : 'Gere e baixe', $en ? 'Choose ATS Classic, Tech Compact or International Clean.' : 'Escolha ATS Classic, Tech Compact ou International Clean.'],
        ] as [$number, $title, $copy])
            <article class="card">
                <span class="badge">{{ $number }}</span>
                <h2 style="margin-top:14px">{{ $title }}</h2>
                <p>{{ $copy }}</p>
            </article>
        @endforeach
    </section>

    <section id="recursos" style="margin-top:22px">
        <div class="hero">
            <div>
                <p class="eyebrow">{{ $en ? 'Features' : 'Recursos' }}</p>
                <h2>{{ $en ? 'Built for fast, evidence-based applications.' : 'Feito para candidaturas rápidas e baseadas em evidências.' }}</h2>
            </div>
        </div>
        <div class="grid grid-3">
            @foreach ([
                'Otimização ATS',
                'Leitura rápida para recrutadores',
                'Gaps reais',
                'Evidências rastreáveis',
                'Currículo em PT-BR e inglês',
                'Modelos nacionais e internacionais',
            ] as $feature)
                <article class="card"><strong>{{ $feature }}</strong></article>
            @endforeach
        </div>
    </section>

    <section id="precos" class="card" style="margin-top:22px">
        <div class="actions" style="justify-content:space-between">
            <div>
                <p class="eyebrow">{{ $en ? 'Pricing' : 'Preços' }}</p>
                <h2>{{ $en ? 'MVP access with free plan and Pro-ready limits.' : 'Acesso MVP com plano gratuito e limites prontos para Pro.' }}</h2>
                <p>{{ $en ? 'Payment integration is intentionally not enabled yet.' : 'Integração de pagamento ainda não está ativada de propósito.' }}</p>
            </div>
            <a class="btn" href="{{ route('register') }}">{{ $en ? 'Start now' : 'Começar agora' }}</a>
        </div>
    </section>
@endsection
