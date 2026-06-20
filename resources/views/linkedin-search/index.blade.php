@extends('layouts.app')

@section('meta_title', 'LinkedIn Search Builder — Vyrko')
@section('meta_description', 'Gere buscas booleanas prontas para encontrar vagas no LinkedIn. Gratuito, sem IA, sem scraping.')

@section('content')
    @php $en = app()->getLocale() === 'en'; @endphp

    <x-ui.page-header
        :eyebrow="'LinkedIn Search Builder'"
        :title="$en ? 'Ready searches to find better jobs on LinkedIn.' : 'Buscas prontas para encontrar vagas melhores no LinkedIn.'"
        :subtitle="$en ? 'Enter job titles, skills, and preferences. Vyrko builds boolean queries for you to copy and use on LinkedIn.' : 'Informe cargos, habilidades e preferências. O Vyrko monta buscas booleanas para você copiar e pesquisar no LinkedIn.'"
    >
        <x-slot:actions>
            <span class="badge-free">{{ $en ? 'Free · No AI · No scraping' : 'Grátis · Sem IA · Sem scraping' }}</span>
        </x-slot:actions>
    </x-ui.page-header>

    <p class="search-disclaimer" style="margin-bottom: 1.5rem;">
        {{ $en
            ? 'This tool does not access your LinkedIn account. It only builds search strings for you to use manually.'
            : 'Essa ferramenta não acessa sua conta do LinkedIn. Ela apenas monta buscas para você usar manualmente.' }}
    </p>

    <div class="content-grid wide-aside" id="search-layout">

        {{-- ── LEFT: Form ──────────────────────────────────────────────── --}}
        <form class="card stack-lg" id="search-builder-form"
              action="{{ route('linkedin-search.generate') }}"
              method="POST" novalidate>
            @csrf

            <div class="panel-title">
                <div>
                    <p class="eyebrow">{{ $en ? 'Search parameters' : 'Parâmetros da busca' }}</p>
                    <h2>{{ $en ? 'Fill in your preferences' : 'Preencha suas preferências' }}</h2>
                </div>
            </div>

            {{-- Cargo / área --}}
            <div class="field">
                <label for="titles-input">{{ $en ? 'Job title or area' : 'Cargo ou área' }}</label>
                <div class="tags-container" data-tags-container data-field-name="titles">
                    <div class="tags-display" data-tags-display></div>
                    <input class="tags-input" id="titles-input" type="text" data-tags-input
                           placeholder="{{ $en ? 'e.g. Backend Developer, Product Manager' : 'Ex: Backend Developer, Product Manager' }}"
                           autocomplete="off">
                    <div data-tags-hidden></div>
                </div>
                <p class="form-help">{{ $en ? 'Add role variations to broaden the search.' : 'Adicione variações do cargo para ampliar a busca.' }}</p>
            </div>

            {{-- Habilidades --}}
            <div class="field">
                <label for="skills-input">{{ $en ? 'Key skills' : 'Habilidades principais' }}</label>
                <div class="tags-container" data-tags-container data-field-name="skills">
                    <div class="tags-display" data-tags-display></div>
                    <input class="tags-input" id="skills-input" type="text" data-tags-input
                           placeholder="{{ $en ? 'e.g. PHP, Laravel, MySQL, Docker' : 'Ex: PHP, Laravel, MySQL, Docker' }}"
                           autocomplete="off">
                    <div data-tags-hidden></div>
                </div>
                <p class="form-help">{{ $en ? 'Use technologies, tools, or skills that appear in desired jobs.' : 'Use tecnologias, ferramentas ou competências que aparecem nas vagas desejadas.' }}</p>
            </div>

            {{-- Senioridade --}}
            <div class="field">
                <label>{{ $en ? 'Seniority' : 'Senioridade' }}</label>
                <div class="checkbox-group">
                    @foreach([
                        ['value' => 'Estágio',      'label' => $en ? 'Intern'       : 'Estágio'],
                        ['value' => 'Júnior',       'label' => $en ? 'Junior'       : 'Júnior'],
                        ['value' => 'Pleno',        'label' => $en ? 'Mid-level'    : 'Pleno'],
                        ['value' => 'Sênior',       'label' => $en ? 'Senior'       : 'Sênior'],
                        ['value' => 'Lead',         'label' => 'Lead'],
                        ['value' => 'Especialista', 'label' => $en ? 'Specialist'   : 'Especialista'],
                        ['value' => 'Coordenação',  'label' => $en ? 'Coordinator'  : 'Coordenação'],
                        ['value' => 'Gerência',     'label' => $en ? 'Manager'      : 'Gerência'],
                    ] as $opt)
                        <label class="checkbox-label">
                            <input type="checkbox" name="seniorities[]" value="{{ $opt['value'] }}">
                            {{ $opt['label'] }}
                        </label>
                    @endforeach
                </div>
                <p class="form-help">{{ $en ? 'Leave blank to not filter by seniority.' : 'Deixe em branco para não filtrar por senioridade.' }}</p>
            </div>

            {{-- Modelo de trabalho --}}
            <div class="field">
                <label>{{ $en ? 'Work model' : 'Modelo de trabalho' }}</label>
                <div class="checkbox-group">
                    @foreach([
                        ['value' => 'Remoto',        'label' => $en ? 'Remote'        : 'Remoto'],
                        ['value' => 'Híbrido',       'label' => $en ? 'Hybrid'        : 'Híbrido'],
                        ['value' => 'Presencial',    'label' => $en ? 'On-site'       : 'Presencial'],
                        ['value' => 'Internacional', 'label' => $en ? 'International' : 'Internacional'],
                        ['value' => 'Freelance',     'label' => 'Freelance'],
                        ['value' => 'PJ',            'label' => 'PJ'],
                        ['value' => 'CLT',           'label' => 'CLT'],
                        ['value' => 'Contrato',      'label' => $en ? 'Contract'      : 'Contrato'],
                    ] as $opt)
                        <label class="checkbox-label">
                            <input type="checkbox" name="work_modes[]" value="{{ $opt['value'] }}">
                            {{ $opt['label'] }}
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Localização --}}
            <div class="field">
                <label for="locations-input">{{ $en ? 'Location' : 'Localização' }}</label>
                <div class="tags-container" data-tags-container data-field-name="locations">
                    <div class="tags-display" data-tags-display></div>
                    <input class="tags-input" id="locations-input" type="text" data-tags-input
                           placeholder="{{ $en ? 'e.g. Brazil, São Paulo, Portugal, Europe' : 'Ex: Brasil, São Paulo, Portugal, Europe' }}"
                           autocomplete="off">
                    <div data-tags-hidden></div>
                </div>
            </div>

            {{-- Idioma --}}
            <div class="field">
                <label for="language">{{ $en ? 'Job language' : 'Idioma das vagas' }}</label>
                <select name="language" id="language">
                    <option value="both" selected>{{ $en ? 'Both (PT + EN)' : 'Ambos (PT + EN)' }}</option>
                    <option value="pt">{{ $en ? 'Portuguese' : 'Português' }}</option>
                    <option value="en">{{ $en ? 'English' : 'Inglês' }}</option>
                </select>
            </div>

            {{-- Termos a evitar --}}
            <div class="field">
                <label for="excluded-input">{{ $en ? 'Terms to avoid' : 'Termos para evitar' }}</label>
                <div class="tags-container" data-tags-container data-field-name="excluded">
                    <div class="tags-display" data-tags-display></div>
                    <input class="tags-input" id="excluded-input" type="text" data-tags-input
                           placeholder="{{ $en ? 'e.g. unpaid, on-site, telemarketing' : 'Ex: estágio, presencial, telemarketing' }}"
                           autocomplete="off">
                    <div data-tags-hidden></div>
                </div>
                <p class="form-help">{{ $en ? 'Removes results that do not make sense for you.' : 'Remove resultados que não fazem sentido para você.' }}</p>
            </div>

            {{-- Nicho --}}
            <div class="field">
                <label for="niche">{{ $en ? 'Area (optional)' : 'Área (opcional)' }}</label>
                <select name="niche" id="niche">
                    <option value="">{{ $en ? 'Select...' : 'Selecione...' }}</option>
                    @foreach([
                        'tecnologia'    => $en ? 'Technology'      : 'Tecnologia',
                        'dados'         => $en ? 'Data'            : 'Dados',
                        'produto'       => $en ? 'Product'         : 'Produto',
                        'design'        => 'Design',
                        'marketing'     => 'Marketing',
                        'vendas'        => $en ? 'Sales'           : 'Vendas',
                        'administrativo'=> $en ? 'Administrative'  : 'Administrativo',
                        'financeiro'    => $en ? 'Finance'         : 'Financeiro',
                        'rh'            => $en ? 'HR'              : 'RH',
                        'suporte'       => $en ? 'Support'         : 'Suporte',
                    ] as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <p class="form-help">{{ $en ? 'Helps expand synonyms for the broad search.' : 'Ajuda a expandir sinônimos na busca ampla.' }}</p>
            </div>

            <div id="form-error" class="alert error" style="display:none;"></div>

            <div class="actions">
                <button class="btn" type="submit" data-submit-btn>
                    {{ $en ? 'Generate searches' : 'Gerar buscas' }}
                </button>
            </div>
        </form>

        {{-- ── RIGHT: Results ──────────────────────────────────────────── --}}
        <div id="results-panel">
            <x-ui.empty-state
                :title="$en ? 'Your searches will appear here.' : 'Suas buscas aparecerão aqui.'"
                :description="$en
                    ? 'Fill in a job title or skill and click Generate searches to get at least 3 ready queries.'
                    : 'Preencha um cargo ou habilidade e clique em Gerar buscas para receber ao menos 3 queries prontas.'"
                :cta="null"
            />
        </div>
    </div>

    {{-- ── Tips ───────────────────────────────────────────────────────── --}}
    <section class="card stack" style="margin-top: 2rem;">
        <p class="eyebrow">{{ $en ? 'How to use it better' : 'Como usar melhor' }}</p>
        <h3>{{ $en ? 'Tips for getting the best results' : 'Dicas para obter melhores resultados' }}</h3>
        <ol class="stack" style="padding-left: 1.2rem;">
            @foreach($en ? [
                'Start with the broad search to explore the market.',
                'Use the balanced search to find jobs with good fit.',
                'Use the precise search when you want very specific opportunities.',
                'If bad results appear, add terms to "avoid".',
                'If there are too few results, remove some required skills.',
            ] : [
                'Comece pela busca ampla para explorar o mercado.',
                'Use a busca equilibrada para encontrar vagas com boa aderência.',
                'Use a busca precisa quando quiser filtrar oportunidades muito específicas.',
                'Se aparecerem vagas ruins, adicione termos em "evitar".',
                'Se houver poucos resultados, remova algumas habilidades obrigatórias.',
            ] as $tip)
                <li><p>{{ $tip }}</p></li>
            @endforeach
        </ol>
        <p class="search-disclaimer">
            {{ $en
                ? 'LinkedIn may interpret searches differently depending on the area, language, and filters applied. Adjust the query based on results. Vyrko does not collect LinkedIn results or submit applications automatically.'
                : 'O LinkedIn pode interpretar buscas de forma diferente dependendo da área, idioma e filtros aplicados. Ajuste a query conforme os resultados. O Vyrko não coleta resultados do LinkedIn e não envia candidaturas automaticamente.' }}
        </p>
    </section>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    // ── Tag input ──────────────────────────────────────────────────────
    function initTagsContainer(container) {
        const textInput   = container.querySelector('[data-tags-input]');
        const display     = container.querySelector('[data-tags-display]');
        const hiddenWrap  = container.querySelector('[data-tags-hidden]');
        const fieldName   = container.dataset.fieldName;
        const tags        = [];

        function renderTags() {
            display.innerHTML = '';
            hiddenWrap.innerHTML = '';

            tags.forEach(function (tag, i) {
                // chip
                var chip = document.createElement('span');
                chip.className = 'tag-chip';

                var tagText = document.createTextNode(tag + ' ');
                var removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.dataset.idx = i;
                removeBtn.setAttribute('aria-label', 'Remover ' + tag);
                removeBtn.textContent = '×';

                chip.appendChild(tagText);
                chip.appendChild(removeBtn);
                display.appendChild(chip);

                // hidden input
                var inp = document.createElement('input');
                inp.type  = 'hidden';
                inp.name  = fieldName + '[]';
                inp.value = tag;
                hiddenWrap.appendChild(inp);
            });
        }

        function addTag(value) {
            const clean = value.trim().replace(/,+$/, '').trim();
            if (!clean) return;
            const lower = clean.toLowerCase();
            if (tags.some(function (t) { return t.toLowerCase() === lower; })) return;
            tags.push(clean);
            renderTags();
        }

        textInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                addTag(textInput.value);
                textInput.value = '';
            }
        });

        display.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-idx]');
            if (!btn) return;
            tags.splice(parseInt(btn.dataset.idx, 10), 1);
            renderTags();
        });

        // Flush text input on form submit so user doesn't lose a typed-but-not-confirmed tag
        container.closest('form').addEventListener('submit', function () {
            if (textInput.value.trim()) {
                addTag(textInput.value);
                textInput.value = '';
            }
        });
    }

    document.querySelectorAll('[data-tags-container]').forEach(initTagsContainer);

    // ── Form submission ────────────────────────────────────────────────
    var form       = document.getElementById('search-builder-form');
    var panel      = document.getElementById('results-panel');
    var submitBtn  = form.querySelector('[data-submit-btn]');
    var formError  = document.getElementById('form-error');

    var typeLabels = {
        broad:     '{{ $en ? "Broad" : "Ampla" }}',
        balanced:  '{{ $en ? "Balanced" : "Equilibrada" }}',
        precise:   '{{ $en ? "Precise" : "Precisa" }}',
        recruiter: '{{ $en ? "Recruiters" : "Recrutadores" }}',
    };

    var copyLabel   = '{{ $en ? "Copy query"  : "Copiar query" }}';
    var copiedLabel = '{{ $en ? "Copied!"     : "Copiado!" }}';
    var jobsLabel   = '{{ $en ? "Open in LinkedIn Jobs"  : "Abrir no LinkedIn Jobs" }}';
    var postsLabel  = '{{ $en ? "LinkedIn Posts" : "LinkedIn Posts" }}';

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        formError.style.display = 'none';

        var original = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = '{{ $en ? "Generating..." : "Gerando..." }}';

        try {
            var resp = await fetch(form.action, {
                method:  'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept':       'application/json',
                },
                body: new FormData(form),
            });

            var json = await resp.json();

            if (!resp.ok) {
                var errors = json.errors || {};
                var first  = Object.values(errors).flat()[0] || '{{ $en ? "Error generating searches." : "Erro ao gerar buscas." }}';
                formError.textContent   = first;
                formError.style.display = '';
                return;
            }

            renderResults(json.queries);

        } catch (err) {
            formError.textContent   = '{{ $en ? "Connection error. Try again." : "Erro de conexão. Tente novamente." }}';
            formError.style.display = '';
        } finally {
            submitBtn.disabled    = false;
            submitBtn.textContent = original;
        }
    });

    function renderResults(queries) {
        panel.innerHTML = queries.map(renderCard).join('');

        panel.querySelectorAll('[data-copy-btn]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var text = btn.closest('[data-query-card]').querySelector('[data-query-text]').textContent;
                navigator.clipboard.writeText(text).then(function () {
                    var orig = btn.textContent;
                    btn.textContent = copiedLabel;
                    setTimeout(function () { btn.textContent = orig; }, 2000);
                }).catch(function () {
                    // fallback for older browsers
                    var ta = document.createElement('textarea');
                    ta.value = text;
                    ta.style.position = 'fixed';
                    ta.style.opacity  = '0';
                    document.body.appendChild(ta);
                    ta.select();
                    document.execCommand('copy');
                    document.body.removeChild(ta);
                    btn.textContent = copiedLabel;
                    setTimeout(function () { btn.textContent = copyLabel; }, 2000);
                });
            });
        });
    }

    function renderCard(q) {
        var warning = q.warning
            ? '<p class="query-warning">' + escHtml(q.warning) + '</p>'
            : '';

        return '<article class="card stack" data-query-card style="margin-bottom:1rem;">'
            + '<div class="query-card-header">'
            + '<span class="badge">' + escHtml(typeLabels[q.type] || q.type) + '</span>'
            + '<strong>' + escHtml(q.label) + '</strong>'
            + '</div>'
            + '<p class="muted">' + escHtml(q.objective) + '</p>'
            + '<pre class="query-block" data-query-text>' + escHtml(q.query) + '</pre>'
            + warning
            + '<div class="query-actions">'
            + '<button class="btn secondary" type="button" data-copy-btn>' + copyLabel + '</button>'
            + '<a class="btn secondary" href="' + escAttr(q.linkedinJobsUrl) + '" target="_blank" rel="noopener noreferrer">' + jobsLabel + '</a>'
            + '<a class="btn ghost" href="' + escAttr(q.linkedinPostsUrl) + '" target="_blank" rel="noopener noreferrer">' + postsLabel + '</a>'
            + '</div>'
            + '<p class="form-help">💡 ' + escHtml(q.tip) + '</p>'
            + '</article>';
    }

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function escAttr(str) {
        return String(str).replace(/"/g, '&quot;');
    }
}());
</script>
@endpush
