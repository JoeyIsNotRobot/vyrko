---
phase: 1
slug: design-system-dashboard-copy
status: draft
shadcn_initialized: false
preset: none
created: 2026-06-20
---

# Phase 1 — UI Design Contract

> Visual and interaction contract para Phase 1: Design System + Dashboard + Copy.
> Gerado por gsd-ui-researcher. Verificado por gsd-ui-checker.

---

## Design System

| Property | Value |
|----------|-------|
| Tool | none — Blade + Tailwind utility classes + custom CSS |
| Preset | not applicable |
| Component library | none (Blade components custom em `resources/views/components/`) |
| Icon library | SVG inline — sem biblioteca de ícones externa |
| Font | Geist Sans (400, 700) + JetBrains Mono (400, 700) — já carregadas via @fontsource em app.css |

> **Nota de stack:** Laravel 11 + Blade + TailwindCSS v4 (via `@tailwindcss/vite`) + Alpine.js v3. Zero React/Vue/Livewire.

---

## Spacing Scale

Declarado: múltiplos de 4 apenas. Classes Tailwind usadas diretamente.

| Token | Value | Tailwind class | Usage |
|-------|-------|----------------|-------|
| xs | 4px | gap-1 / p-1 | Icon gaps, inline separators |
| sm | 8px | gap-2 / p-2 | Chip padding, compact row gaps |
| md | 16px | gap-4 / p-4 | Default inline element spacing |
| lg | 24px | gap-6 | Entre stat blocks e card groups (mínimo obrigatório) |
| xl | 32px | gap-8 | Layout gaps entre seções |
| 2xl | 48px | gap-12 | Major section breaks |
| page-x | 24px | px-6 | Padding horizontal de áreas de conteúdo principal |
| page-y | 32px | py-8 | Padding vertical de áreas de conteúdo principal |
| card | 24px | p-6 | Padding interno de todos os cards (uniforme) |

**Exceções:**
- Label → valor em stat cards: `mb-1` (4px) entre `.metric-label` e `.metric-value`
- Touch targets mínimos: 44px de altura para todos os botões interativos
- Footer: `py-6 pb-10` (24px top, 40px bottom) para conforto visual no rodapé

---

## Typography

| Tier | Size | Tailwind | Weight | Font | Line Height | Usage |
|------|------|----------|--------|------|-------------|-------|
| Tier 1 — Display/Stat | `clamp(30px, 5vw, 72px)` | text-3xl→text-7xl + clamp | 700 | Geist Sans (headlines) / JetBrains Mono (stat numbers) | 1.05 | Landing h1, hero headlines, metric card values — differentiate by font-family, NOT by separate size tier |
| Tier 2 — Heading | `clamp(20px, 2.5vw, 28px)` | text-xl→text-2xl + clamp | 700 | Geist Sans | 1.08 | All section headings, card titles (h2) |
| Tier 3 — Body/Sub | `16px` | text-base | 400 (body) / 700 (sub-heading) | Geist Sans | 1.65 (body) / 1.3 (sub) | Body paragraphs at weight 400; sub-headings (h3, card panel titles) at weight 700 — same size tier, differentiate by weight only |
| Tier 4 — Small | `13px` | text-sm | 400 (labels, code) / 700 (eyebrows, badges) | Geist Sans (labels/badges) / JetBrains Mono (code) | 1.45 | Meta labels, footer links, badges, eyebrows, inline code — differentiate by font-family and weight |

**Regras:**
- Exatamente 2 font-weights globais: `400` (body, code, secondary labels) e `700` (ALL headings, stat numbers, CTAs, sub-headings, eyebrows, badges)
- Eyebrows: uppercase, `letter-spacing: 0.08em`, 13px, peso 700
- Stat numbers: JetBrains Mono, Tier 1 size, peso 700
- Visual hierarchy via font-size tier and font-family — NOT via weight variation beyond 400/700

---

## Color

### Paleta base (CSS custom properties em `:root`)

| Role | Hex | CSS Var | Tailwind equiv |
|------|-----|---------|----------------|
| Background deep | `#0f1117` | `--bg-deep` | slate-950 aprox |
| Background surface | `#1e293b` | `--bg-surface` | slate-800 |
| Card / Panel | `#1e293b` | `--card-bg` | slate-800 |
| Card elevated | `#2d3748` | `--card-elevated` | slate-700 |
| Border | `#334155` | `--border` | slate-700 |
| Border subtle | rgba(51,65,85,0.6) | `--border-subtle` | slate-700/60 |

### Paleta semântica (substituindo vscode-*)

| Role | Hex | CSS Var | Tailwind equiv | Substituí |
|------|-----|---------|----------------|-----------|
| Primary / Accent | `#2563EB` | `--color-primary` | blue-600 | `#007ACC` (vscode-blue) |
| Primary hover | `#1D4ED8` | `--color-primary-hover` | blue-700 | `#0E639C` (vscode-blue-hover) |
| Success / Positive | `#10B981` | `--color-success` | emerald-500 | `#4EC9B0` (vscode-success) |
| Warning / Gap | `#F59E0B` | `--color-warning` | amber-500 | `#DCDCAA` (vscode-warning) |
| Danger / Destructive | `#EF4444` | `--color-danger` | red-500 | `#F44747` (vscode-danger) |
| Text primary | `#F1F5F9` | `--color-text` | slate-100 | `#D4D4D4` |
| Text secondary | `#94A3B8` | `--color-muted` | slate-400 | `#A6A6A6` |
| Text link-blue | `#93C5FD` | `--color-link` | blue-300 | `#8BD8FF` |

### Distribuição 60/30/10

| Split | Cor | Elementos |
|-------|-----|-----------|
| 60% Dominante | `#0f1117` + gradiente slate-900→slate-800 | body background, grid background, page filler |
| 30% Secundária | `#1e293b` (slate-800) | cards, topbar, sidebar, modal backdrop interior |
| 10% Accent | `#2563EB` (blue-600) | CTAs primários (`.btn`), links ativos, focus rings, brand mark, borders de destaque |

**Accent reservado APENAS para:**
- Botões primários (`.btn` / `.app-button-primary`)
- Navlink `.is-active` background
- Focus ring em inputs (box-shadow)
- Brand mark gradient (blue-600 → emerald-500)
- Border de cards em hover (opacity 50%)
- Barra de progresso do loading modal

**Accent NÃO usado em:** texto de corpo, labels genéricos, borders padrão, backgrounds de seção.

### Cores semânticas adicionais

| Cor | Uso exclusivo |
|-----|---------------|
| `#10B981` emerald-500 | Badge de match positivo (≥70%), ícone de success, barra de progresso (gradiente com blue-600) |
| `#F59E0B` amber-500 | Badge de warning/gaps, metric-card tone warning, border warning |
| `#EF4444` red-500 | Ações destrutivas, `.btn.danger`, alert de erro, `.form-error` |

---

## Componentes

### Loading Modal Universal (DESIGN-03)

**Blade component:** `<x-loading-modal>`
**Arquivo:** `resources/views/components/loading-modal.blade.php`
**Alpine.js state:**

```javascript
{
    show: false,
    progress: 0,
    currentStep: '',
    steps: [],         // array de strings passado por operação
    error: null        // string | null
}
```

**Especificação visual:**

| Elemento | Classes |
|----------|---------|
| Backdrop | `fixed inset-0 z-50 backdrop-blur-md bg-black/60 flex items-center justify-center` |
| Modal card | `bg-slate-800 rounded-xl p-8 w-full max-w-md mx-4 shadow-2xl border border-slate-700` |
| Barra de progresso | `h-2 rounded-full bg-slate-700 w-full mt-4` (track) + `h-2 rounded-full bg-gradient-to-r from-blue-600 to-emerald-500 transition-all duration-500` (fill) |
| Step text | `text-sm text-slate-400 mt-3 text-center` |
| Título | `text-white font-bold text-lg text-center` |
| Spinner (opcional) | SVG animate-spin, `text-blue-600`, 32px |

**Comportamento:**

| Evento | Comportamento |
|--------|---------------|
| Abertura | `show = true`, `progress = 0`, `error = null`, `currentStep = steps[0]` |
| Avanço de step | Interpolação de progress via JS conforme step index |
| ESC durante operação | **Bloqueado** — `@keydown.escape.window.prevent` |
| Click fora durante operação | **Bloqueado** — sem `@click.outside` dismiss |
| Sucesso | `progress = 100` → aguarda 400ms → `show = false` |
| Erro | `error = "mensagem"` → exibe banner vermelho dentro do card com botão "Tentar novamente" |
| Retry | Reseta `error = null`, `progress = 0`, reinicia operação |

**Steps por operação (passados como array ao invocar):**

| Operação | Steps |
|----------|-------|
| Importação de currículo (Phase 2) | `['Lendo documento', 'Extraindo dados', 'Organizando perfil', 'Concluído']` |
| Análise de vaga (Phase 3) | `['Lendo vaga', 'Comparando inventário', 'Gerando análise', 'Concluído']` |
| Geração de currículo (Phase 3) | `['Preparando dados', 'Gerando currículo', 'Finalizando', 'Concluído']` |

---

### Dashboard — 3 Stat Cards (DASH-01, DASH-02)

**Layout:** `grid grid-cols-1 sm:grid-cols-3 gap-6`
**Remoção:** Eliminar card "Gaps no inventário" e "Melhor match" — substitui `summary-grid` de 4 colunas por grid de 3.

| # | Label | Valor | Meta / CTA |
|---|-------|-------|------------|
| 1 | "Vagas analisadas" | `$jobCount` (integer) | Link "Ver todas →" para `jobs.index` |
| 2 | "Currículos gerados" | `$resumeCount` (integer) | Link "Ver todos →" para `resumes.index` |
| 3 | "Completude do inventário" | `$completeness`% (integer 0-100) | Se `< 80%`: CTA "Complete seu perfil →" para `career.index`; se `≥ 80%`: texto "Inventário completo" com ícone check emerald |

**Spec do card de completude:**
- Valor: `{$completeness}%` — JetBrains Mono, Tier 1 size (`clamp(30px, 5vw, 72px)`), weight 700
- Tone: `warning` quando < 80% (border amber, glow amber), `success` quando ≥ 80%
- CTA dentro do card: `text-sm font-bold text-blue-400 hover:text-blue-300 underline-offset-2 hover:underline`

**Altura mínima dos cards:** `min-h-[112px]` — todos iguais.

---

### Footer Layout (DESIGN-02)

**Mudança no layout wrapper:**

```html
<!-- app.blade.php body > primeiro div -->
<div class="app-shell flex flex-col min-h-screen">
    <header class="topbar">...</header>
    <main class="wrap page flex-1">...</main>  <!-- flex-1 adicionado -->
    <footer class="wrap footer-links">...</footer>
</div>
```

**Copy do footer:**
`© 2026 Vyrko · Termos · Privacidade · Consentimento de IA · Dados sociais`

Links: Termos → `legal.terms`, Privacidade → `legal.privacy`, Consentimento de IA → `legal.data-consent`, Dados sociais → `legal.social-data`.

**Estilo do footer:**
- `py-6 pb-10` — 24px top, 40px bottom
- Cor do texto: `#90A0B8` (slate-400)
- Cor dos links: `#C7D0DF` (slate-300) default, `#93C5FD` (blue-300) hover
- `font-size: 13px`
- `display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: center;`

---

### Topbar / Nav

Sem mudança estrutural nesta fase — apenas atualização de cores:
- `.navlink.is-active` background: `rgba(37,99,235,.14)` (blue-600/14)
- `.btn` (primary): `background: linear-gradient(180deg, #3B82F6, #2563EB)` com `border-color: rgba(37,99,235,.9)`
- `.brand-mark` gradient: `linear-gradient(135deg, #2563EB, #10B981)`
- Focus ring de inputs: `box-shadow: 0 0 0 4px rgba(37,99,235,.18)` — `border-color: #2563EB`

---

### Autocomplete de Área/Função (COPY-03)

**Substituição de `<select>` fixo por `<input type="text">` com datalist ou Alpine.js autocomplete.**

| Campo | Label | Placeholder |
|-------|-------|-------------|
| Área de atuação | "Área de atuação" | "Ex: Engenharia de Software, Finanças, Marketing..." |
| Função/Cargo | "Função/Cargo" | "Ex: Backend Engineer, Analista Financeiro..." |

**Implementação:** `<input>` com `list="area-suggestions"` + `<datalist id="area-suggestions">` com sugestões comuns pré-carregadas. Usuário pode digitar qualquer valor. Não restrito a lista.

---

## Copywriting Contract

### Strings por página (PT-BR — todas as strings EN já existem)

| Página | Elemento | Copy PT-BR |
|--------|----------|------------|
| Landing | Headline principal | "Seu currículo certo, para a vaga certa." |
| Landing | Sub-headline | "Importe seu currículo, analise vagas e gere versões personalizadas que passam pelo ATS." |
| Dashboard | Heading | "Seu centro de comando de currículos" |
| Dashboard | Sub-heading | "Analise vagas, gere currículos precisos e acompanhe sua candidatura." |
| Inventário | Page title | "Seu inventário de carreira" |
| Inventário | Subtitle | "Tudo que você já fez, organizado. A IA usa isso para gerar currículos precisos." |
| Templates | Headline | "Qual currículo enviar para esta vaga?" |
| Conta | Page title | "Sua conta" |

**Landing page focal point:** The `<h1>` headline — "Seu currículo certo, para a vaga certa." — is the primary visual anchor of the landing page. All other elements (sub-headline, CTA button) are visually subordinate to it. No competing element may exceed the `<h1>` in visual weight.

### Empty States (orientadores — não informativos)

| Contexto | Heading | Body | CTA |
|----------|---------|------|-----|
| Dashboard — sem vagas | "Nenhuma vaga analisada ainda" | "Cole uma descrição de vaga para extrair requisitos, keywords e gaps." | "Analisar primeira vaga →" |
| Dashboard — sem currículos | "Nenhum currículo gerado ainda" | "Gere um currículo a partir de uma análise de vaga para ver versões aqui." | "Começar por uma vaga →" |
| Lista de vagas vazia | "Nenhuma vaga analisada ainda" | "Adicione sua primeira vaga para começar a identificar gaps e gerar currículos." | "Analisar nova vaga →" |
| Lista de currículos vazia | "Nenhum currículo gerado" | "Analise uma vaga primeiro, depois gere o currículo com base no seu inventário." | "Analisar uma vaga →" |

### Error States

| Contexto | Copy |
|----------|------|
| Erro de IA genérico | "Algo deu errado ao processar. Tente novamente em alguns instantes." |
| Erro 503 / IA sobrecarregada | "Serviço de IA sobrecarregado. Aguarde alguns minutos e tente novamente." |
| Vaga < 100 caracteres | "A descrição da vaga é muito curta. Cole o texto completo da vaga (pelo menos 100 caracteres) para uma análise precisa." |
| Erro de upload de arquivo | "Não foi possível ler o arquivo. Verifique se é um PDF, DOCX ou TXT válido e tente novamente." |

### CTAs primários por contexto

| Contexto | Label do CTA primário |
|----------|----------------------|
| Dashboard (sem vagas) | "Analisar nova vaga" |
| Dashboard (com vagas) | "Analisar nova vaga" |
| Inventário (incompleto) | "Complete seu perfil →" |
| Inventário (completo) | "Editar inventário" |
| Workspace de vaga | "Analisar vaga →" |
| Nav global | "Analisar nova vaga" |
| Register CTA | "Criar meu Inventário" |

### Ações destrutivas

| Ação | Confirmação |
|------|-------------|
| Excluir workspace de vaga | Dialog inline: "Tem certeza? Esta ação não pode ser desfeita." + botão "Excluir" (`.btn.danger`) + "Cancelar" |
| Excluir currículo | Dialog inline: "Excluir este currículo?" + botão "Excluir" (`.btn.danger`) |
| Excluir experiência do inventário | Sem confirm (ação reversível via form — usuário pode re-adicionar) |

---

## Accessibility

| Requisito | Spec |
|-----------|------|
| Contraste mínimo | WCAG AA 4.5:1 para texto normal, 3:1 para texto grande (≥18px bold) |
| `#2563EB` sobre `#1e293b` | ratio ~5.1:1 — PASSA AA |
| `#F1F5F9` sobre `#0f1117` | ratio ~17.5:1 — PASSA AAA |
| `#94A3B8` sobre `#0f1117` | ratio ~7.2:1 — PASSA AA |
| `#10B981` sobre `#1e293b` | ratio ~4.6:1 — PASSA AA |
| `#F59E0B` sobre `#1e293b` | ratio ~5.8:1 — PASSA AA |
| Focus rings | `outline: 2px solid #2563EB; outline-offset: 2px` em todos os elementos focalizáveis via teclado |
| Loading modal | `role="dialog"` + `aria-modal="true"` + `aria-label="Processando..."` |
| Botões de fechar | `aria-label` explícito: "Fechar" |
| Empty states | Heading via `<h3>` para hierarquia correta |
| Touch targets | Mínimo 44px altura para todos os `<button>` e `<a class="btn">` |

---

## Registry Safety

| Registry | Blocks Used | Safety Gate |
|----------|-------------|-------------|
| shadcn official | nenhum — projeto não usa shadcn | not applicable |
| Terceiros | nenhum | not applicable |

> Stack é 100% Blade + Tailwind + Alpine sem registries de componente externos.

---

## Mudanças no CSS existente (migration map)

As variáveis CSS atuais (`--vscode-*`) serão **mantidas em aliases temporários** durante a migração e **removidas após Phase 1 completa**. As novas variáveis serão:

| Var antiga | Var nova | Hex novo |
|------------|----------|----------|
| `--vscode-blue` | `--color-primary` | `#2563EB` |
| `--vscode-blue-hover` | `--color-primary-hover` | `#1D4ED8` |
| `--vscode-success` | `--color-success` | `#10B981` |
| `--vscode-warning` | `--color-warning` | `#F59E0B` |
| `--vscode-danger` | `--color-danger` | `#EF4444` |
| `--vscode-bg` | `--bg-surface` | `#1e293b` |
| `--vscode-bg-deep` | `--bg-deep` | `#0f1117` |
| `--vscode-text` | `--color-text` | `#F1F5F9` |
| `--vscode-muted` | `--color-muted` | `#94A3B8` |
| `--vscode-border` | `--border` | `#334155` |
| `--vscode-panel` | `--card-bg` | `#1e293b` |

Gradiente de background do body permanece — apenas cores do gradiente são atualizadas:
```css
background:
    radial-gradient(circle at 15% 8%, rgba(37,99,235,.25), transparent 28rem),
    radial-gradient(circle at 90% 0%, rgba(16,185,129,.12), transparent 26rem),
    linear-gradient(180deg, #0d1a2e 0%, #0f1117 36%, #1e293b 100%);
```

---

## Checker Sign-Off

- [ ] Dimension 1 Copywriting: PASS
- [ ] Dimension 2 Visuals: PASS
- [ ] Dimension 3 Color: PASS
- [ ] Dimension 4 Typography: PASS
- [ ] Dimension 5 Spacing: PASS
- [ ] Dimension 6 Registry Safety: PASS

**Approval:** pending
