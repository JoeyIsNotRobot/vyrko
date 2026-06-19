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
            <span class="badge">Match 86%</span>
            <span class="badge warning">{{ app()->getLocale() === 'en' ? 'No fake experience' : 'Sem inventar experiências' }}</span>
        </div>
        <h2>{{ app()->getLocale() === 'en' ? 'Laravel Backend role' : 'Vaga Backend Laravel' }}</h2>
        <div class="progress"><span style="width: 86%"></span></div>
        <ul class="split-list">
            <li>ATS checklist · 92/100</li>
            <li>{{ app()->getLocale() === 'en' ? 'Traceable evidence from your inventory' : 'Evidências rastreáveis do seu inventário' }}</li>
        </ul>
    </div>

    <div class="auth-trust-row">
        <span>{{ app()->getLocale() === 'en' ? 'No scraping' : 'Sem scraping' }}</span>
        <span>{{ app()->getLocale() === 'en' ? 'You control the data' : 'Você controla os dados' }}</span>
        <span>{{ app()->getLocale() === 'en' ? 'Review before generating' : 'Revise antes de gerar' }}</span>
    </div>
</aside>
