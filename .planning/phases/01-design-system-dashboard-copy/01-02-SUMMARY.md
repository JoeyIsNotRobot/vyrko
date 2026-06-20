---
phase: 01-design-system-dashboard-copy
plan: 02
subsystem: ui
tags: [tailwindcss, blade, laravel, flex-layout, dashboard, copy]

requires:
  - phase: 01-design-system-dashboard-copy/01-PLAN-01
    provides: Design system canônico com CSS vars e .metric-card com spacing correto

provides:
  - Footer fixo no rodapé da viewport via flex flex-col no .app-shell e flex-1 no main
  - Dashboard com 3 cards (Vagas analisadas, Currículos gerados, Completude do inventário)
  - $completeness calculado server-side no DashboardController (0-100)
  - CTA "Complete seu perfil →" condicional quando completeness < 80%
  - Copy strings PT-BR corretas nas 5 telas principais + empty states orientadores

affects:
  - 01-design-system-dashboard-copy/01-PLAN-03 (usa layout flex e copy estabelecidos)

tech-stack:
  added: []
  patterns:
    - "Footer fixo: flex flex-col no container + flex-1 no main — sem JS, sem position:fixed"
    - "Completude: (totalSections - missing.count()) / totalSections * 100 — denominador constante, sem risco de divisão por zero"
    - "metric-card usa {{ }} (escaped) — não aceita HTML em :meta; card de completude renderizado inline para suportar slot CTA"

key-files:
  created: []
  modified:
    - resources/views/layouts/app.blade.php
    - resources/css/app.css
    - app/Http/Controllers/DashboardController.php
    - resources/views/dashboard/index.blade.php
    - resources/views/welcome.blade.php
    - lang/pt_BR/messages.php
    - resources/views/account/index.blade.php
    - resources/views/resumes/templates.blade.php
    - resources/views/jobs/index.blade.php
    - resources/views/resumes/index.blade.php

key-decisions:
  - "metric-card não aceita HTML em :meta (usa {{ }} escaped) — card de completude renderizado inline para suportar link CTA"
  - "bestReport e latestReport removidos do DashboardController — não mais usados após substituição dos 4 cards por 3"
  - "$bestScore removido do @php block do dashboard — apenas $missingLabels permanece pois é usado na seção Próxima melhor ação"
  - "Links 'Ver todas/Ver todos' dos cards 1 e 2 usam texto plano (não HTML) já que metric-card escapa o meta"

patterns-established:
  - "Card inline em Blade quando componente não suporta slot CTA: usar <article class='metric-card ...'> diretamente"
  - "Flex sticky footer: .app-shell { flex flex-col } + main { flex-1 } — funciona sem modificar .footer-links"

requirements-completed: [DESIGN-02, DASH-01, DASH-02, COPY-01, COPY-02]

duration: 25min
completed: 2026-06-20
---

# Phase 01 Plan 02: Footer + Dashboard 3 Cards + Copy Strings Summary

**Footer fixo via flex layout; dashboard substituído por 3 cards com completeness server-side; copy PT-BR correto em 5 telas + 4 empty states orientadores**

## Performance

- **Duration:** 25 min
- **Started:** 2026-06-20T22:00:00Z
- **Completed:** 2026-06-20T22:25:00Z
- **Tasks:** 4
- **Files modified:** 10

## Accomplishments

- `.app-shell` recebe `flex flex-col min-h-screen` (HTML + @layer CSS + regra direta) e `main` recebe `flex-1` — footer gruda no rodapé independente do tamanho do conteúdo
- `DashboardController` calcula `$completeness` (0-100) via `missingInventory->count()` com denominador constante 5; `bestReport`/`latestReport` removidos
- `dashboard/index` substitui `section.summary-grid` de 4 cards por grid 3 colunas; card de completude inline com CTA condicional `route('career.index')`
- Copy PT-BR atualizado: `welcome` h1/sub-headline, `messages.php` career.title/subtitle, `account` título, `dashboard` subtitle
- 4 empty states com strings orientadoras da UI-SPEC: dashboard vagas, dashboard currículos, lista vagas, lista currículos

## Task Commits

1. **Task 1: Footer fixo — flex layout em .app-shell e main** - `5718822` (feat)
2. **Task 2: Dashboard — 3 cards com $completeness e CTA condicional** - `9dd3104` (feat)
3. **Task 3: Copy strings — landing, inventário, conta, dashboard subtitle** - `7196437` (feat)
4. **Task 4: Copy COPY-02 gap — heading de Templates e empty states orientadores** - `cf47acf` (feat)

## Files Created/Modified

- `resources/views/layouts/app.blade.php` — app-shell com flex flex-col min-h-screen; main com flex-1
- `resources/css/app.css` — @layer .app-shell com flex no @apply; regra direta com display:flex + flex-direction:column
- `app/Http/Controllers/DashboardController.php` — $completeness calculado; bestReport/latestReport removidos
- `resources/views/dashboard/index.blade.php` — 3 cards; subtitle atualizado; empty states orientadores
- `resources/views/welcome.blade.php` — h1 e sub-headline PT-BR atualizados
- `lang/pt_BR/messages.php` — career.title e career.subtitle atualizados
- `resources/views/account/index.blade.php` — título PT-BR → 'Sua conta'
- `resources/views/resumes/templates.blade.php` — título PT-BR → 'Qual currículo enviar para esta vaga?'
- `resources/views/jobs/index.blade.php` — empty state → 'Nenhum workspace ainda'
- `resources/views/resumes/index.blade.php` — empty state → 'Nenhum currículo nesta vaga'

## Decisions Made

- `metric-card` usa `{{ }}` (escaped) — não pode receber HTML em `:meta`. Card de completude renderizado inline para suportar o link CTA condicional. Cards 1 e 2 usam texto plano na prop meta.
- `bestReport` e `latestReport` removidos do controller — não mais referenciados em nenhuma view após a substituição dos cards.
- `$bestScore` removido do `@php` block do dashboard — apenas `$missingLabels` mantido pois ainda é usado na seção "Próxima melhor ação".

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 1 - Bug] Links HTML em :meta do metric-card adaptados para texto plano**
- **Found during:** Task 2 (leitura do metric-card.blade.php antes de editar)
- **Issue:** O plano especificava passar HTML com `<a>` tags na prop `:meta` dos cards 1 e 2, mas `metric-card` renderiza `$meta` via `{{ }}` (escaped), exibindo HTML como texto literal
- **Fix:** Substituídos por texto simples ('Ver todas as vagas →' / 'Ver todos os currículos →') sem HTML. O link CTA foi mantido apenas no card inline de completude, que tem controle total do HTML.
- **Files modified:** resources/views/dashboard/index.blade.php
- **Verification:** Componente verificado antes de editar — `{{ $meta }}` confirmado na linha 13
- **Committed in:** 9dd3104 (Task 2 commit)

---

**Total deviations:** 1 auto-fixed (1 bug — HTML em campo escaped)
**Impact on plan:** Fix necessário para evitar HTML literal no DOM. Sem perda funcional — links "Ver todas/Ver todos" já existem nos panel-title das seções abaixo no mesmo dashboard.

## Issues Encountered

Nenhum. Build Vite retornou exit 0 em todos os checks intermediários e na verificação final (121ms).

## User Setup Required

Nenhum — sem serviços externos nem variáveis de ambiente adicionais.

## Next Phase Readiness

- Layout com footer fixo ativo em todas as páginas
- Dashboard com 3 cards e completeness pronto para Wave 3 (loading modal)
- Copy PT-BR correto em todas as 5 telas principais
- Bloqueadores: nenhum

## Self-Check

- [x] `resources/views/layouts/app.blade.php` contém `class="wrap page flex-1"` na tag main
- [x] `resources/css/app.css` contém `flex-direction: column` na regra .app-shell
- [x] `app/Http/Controllers/DashboardController.php` contém `$completeness = (int) round(`
- [x] `resources/views/dashboard/index.blade.php` contém `grid grid-cols-1 sm:grid-cols-3 gap-6`
- [x] `resources/views/welcome.blade.php` contém `Seu currículo certo, para a vaga certa.`
- [x] `lang/pt_BR/messages.php` contém `Seu inventário de carreira`
- [x] `resources/views/account/index.blade.php` contém `Sua conta`
- [x] `resources/views/resumes/templates.blade.php` contém `Qual currículo enviar para esta vaga?`
- [x] `resources/views/dashboard/index.blade.php` contém `Nenhuma vaga analisada ainda`
- [x] `resources/views/jobs/index.blade.php` contém `Nenhum workspace ainda`
- [x] `resources/views/resumes/index.blade.php` contém `Nenhum currículo nesta vaga`
- [x] Commits 5718822, 9dd3104, 7196437, cf47acf existem no git log
- [x] `npm run build` retornou exit 0 (build limpo, 121ms)

## Self-Check: PASSED

---
*Phase: 01-design-system-dashboard-copy*
*Completed: 2026-06-20*
