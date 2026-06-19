@props([
    'eyebrow',
    'title',
    'subtitle',
    'bullets' => [],
])

<aside class="auth-value">
    <p class="eyebrow">{{ $eyebrow }}</p>
    <h1>{{ $title }}</h1>
    <p>{{ $subtitle }}</p>

    <ul class="trust-list">
        @foreach ($bullets as $bullet)
            <li>{{ $bullet }}</li>
        @endforeach
    </ul>

    <div class="mini-preview">
        <div class="actions between">
            <span class="badge">Match 84%</span>
            <span class="badge warning">{{ app()->getLocale() === 'en' ? 'Review required' : 'Revisão obrigatória' }}</span>
        </div>
        <h2>{{ app()->getLocale() === 'en' ? 'Data Analyst role' : 'Vaga Analista de Dados' }}</h2>
        <div class="keyword-cloud">
            <span class="badge">SQL</span>
            <span class="badge">Power BI</span>
            <span class="badge">Excel</span>
            <span class="badge warning">Python gap</span>
        </div>
        <ul class="split-list">
            <li>{{ app()->getLocale() === 'en' ? 'Traceable evidence from your inventory' : 'Evidências rastreáveis do seu inventário' }}</li>
            <li>{{ app()->getLocale() === 'en' ? 'No fake courses, roles or certifications' : 'Sem cursos, cargos ou certificações falsas' }}</li>
        </ul>
    </div>

    <div class="auth-trust-row">
        <span>{{ app()->getLocale() === 'en' ? 'No scraping' : 'Sem scraping' }}</span>
        <span>{{ app()->getLocale() === 'en' ? 'You control the data' : 'Você controla os dados' }}</span>
        <span>{{ app()->getLocale() === 'en' ? 'Review before download' : 'Revise antes de baixar' }}</span>
    </div>
</aside>
