---
phase: 2
slug: invent-rio-com-import
status: approved
reviewed_at: 2026-06-21
shadcn_initialized: false
preset: none
created: 2026-06-21
inherits: phases/01-design-system-dashboard-copy/01-UI-SPEC.md
---

# Phase 2 — UI Design Contract
# Inventário com Import

> Contrato visual e de interação para Phase 2: seção de importação de currículo no topo do inventário.
> Herda 100% do design system da Phase 1. Especifica exclusivamente os novos componentes: área de upload, modal de progresso com steps de import, estados de sucesso/erro pós-importação e feedback inline nos campos do inventário.
> Gerado por gsd-ui-researcher.

---

## Design System

| Property | Value |
|----------|-------|
| Tool | none — Blade + Tailwind utility classes + CSS inline (padrão Phase 1) |
| Preset | not applicable |
| Component library | Blade components custom em `resources/views/components/` |
| Icon library | SVG inline — sem biblioteca externa |
| Font | Geist Sans (400, 700) + JetBrains Mono (400, 700) — herdados da Phase 1 |

> Stack: Laravel 11 + Blade + TailwindCSS v4 + Alpine.js v3. Zero React/Vue/Livewire.
> Loading modal já existe como `<x-ui.loading-modal />` incluído globalmente em `app.blade.php` (entregue na Phase 1, Plan 03, commit 7f92c43).

---

## Spacing Scale

Herdado integralmente da Phase 1. Nenhum novo token introduzido.

| Token | Value | Tailwind class | Usage nesta fase |
|-------|-------|----------------|-----------------|
| xs | 4px | gap-1 / p-1 | Separadores inline no banner de tipos de arquivo |
| sm | 8px | gap-2 / p-2 | Gap entre ícones de tipo de arquivo e label |
| md | 16px | gap-4 / p-4 | Espaçamento interno da drop zone |
| lg | 24px | gap-6 / p-6 | Padding interno do card de importação |
| xl | 32px | gap-8 | Separação entre card de importação e summary grid |
| card | 24px | p-6 | Padding interno uniforme do card de importação |

**Exceções específicas desta fase:**
- Drop zone: altura mínima `min-h-[120px]` para área de toque adequada (acima dos 44px mínimos de touch target)
- Botão de upload: `min-h-[44px]` obrigatório — touch target mínimo
- Gap entre step indicators no modal: `gap-3` (12px) — compacto para caber 4 steps em max-w-md

---

## Typography

Herdada integralmente da Phase 1. Referência rápida para os elementos novos desta fase:

| Tier | Size | Weight | Font | Uso nesta fase |
|------|------|--------|------|----------------|
| Tier 2 — Heading | `clamp(20px, 2.5vw, 28px)` | 700 | Geist Sans | Título do card de importação: "Importe seu currículo" |
| Tier 3 — Body | 16px | 400 | Geist Sans | Subtítulo da drop zone, texto de instrução |
| Tier 4 — Small | 13px | 400 | Geist Sans | Labels de tipo de arquivo (PDF, DOCX, TXT), step text no modal, mensagens de erro inline |
| Tier 4 — Small bold | 13px | 700 | Geist Sans | Eyebrow do card de importação, badge "Novo" se aplicável |

Regra: apenas pesos 400 e 700. Zero exceções.

---

## Color

Herdada integralmente da Phase 1. Mapeamento dos novos elementos:

| Role | Hex | CSS Var | Uso nesta fase |
|------|-----|---------|----------------|
| Dominant (60%) | `#0f1117` | `--bg-deep` | Background da página, fora do card |
| Secondary (30%) | `#1e293b` | `--bg-surface` / `--card-bg` | Fundo do card de importação, drop zone inativa |
| Accent (10%) | `#2563EB` | `--color-primary` | Border da drop zone em hover/drag-over, botão primário "Importar currículo", barra de progresso do modal |
| Success | `#10B981` | `--color-success` | Estado de sucesso pós-importação (border, ícone check, texto) |
| Warning | `#F59E0B` | `--color-warning` | Aviso sobre sobrescrição de dados existentes (se aplicável) |
| Danger | `#EF4444` | `--color-danger` | Estado de erro pós-importação (border, ícone, texto de erro) |
| Border padrão | `#334155` | `--border` | Border do card e drop zone em estado normal |
| Text primary | `#F1F5F9` | `--color-text` | Título, label de arquivo selecionado |
| Text secondary | `#94A3B8` | `--color-muted` | Instrução da drop zone, tipos aceitos |

**Accent reservado APENAS para (herdado + adições desta fase):**
- Botões primários (`.btn` / `btn primary`)
- Border e glow da drop zone em estado `drag-over` ou `file-selected`
- Barra de progresso do loading modal (gradiente blue-600 → emerald-500)
- Focus ring em input file
- Step indicator ativo no modal

**Accent NÃO usado em:** label "PDF DOCX TXT", texto de instrução, backgrounds de seção, border em estado neutro da drop zone.

---

## Componentes desta fase

### 1. Card de Importação (INV-01)

**Posição:** Acima do `.career-summary-grid` na página `career/index.blade.php`, dentro de `#career-items`.
**Visibilidade:** Sempre visível — não colapsável, não opcional, destaque máximo.

**Estrutura do card:**

```
[card: bg-slate-800, border-slate-700, rounded-xl, p-6]
  [eyebrow: "IMPORTAR CURRÍCULO" — uppercase, 13px, peso 700, color-muted]
  [h2: "Importe seu currículo" — Tier 2, peso 700, color-text]
  [p: "Suba PDF, DOCX ou TXT e a IA preenche o inventário campo a campo." — 16px, 400, color-muted]
  [drop-zone]
  [badges de formato: PDF | DOCX | TXT]
  [botão: "Importar currículo"]
```

**Layout do card no grid da página:**
- Desktop (≥1220px): ocupa toda a largura disponível acima do `.career-summary-grid` (full-width block, não na sidebar direita)
- Mobile: idem, full-width

> Nota importante: o código atual em `inventory-list.blade.php` tem o form de importação dentro da sidebar direita (`.career-quality-column`). Esta fase move a seção para o topo como card full-width — o form existente é substituído, não expandido.

---

### 2. Drop Zone (INV-01)

**Implementação:** `<label>` wrapping `<input type="file">` hidden — comportamento nativo de click + drop via Alpine.js.

**Estados visuais:**

| Estado | Border | Background | Texto |
|--------|--------|------------|-------|
| Neutro (idle) | `1px dashed #334155` (--border) | `rgba(30,41,59,0.4)` | "Arraste seu arquivo aqui ou clique para selecionar" |
| Hover (mouse) | `1px dashed rgba(37,99,235,.5)` | `rgba(37,99,235,.05)` | idem |
| Drag-over | `2px dashed #2563EB` | `rgba(37,99,235,.10)` | "Solte o arquivo aqui" |
| File selected | `1px solid #2563EB` | `rgba(37,99,235,.08)` | Nome do arquivo + tamanho |
| Error | `1px solid #EF4444` | `rgba(239,68,68,.08)` | Mensagem de erro (ver Copywriting) |

**Dimensões:** `min-h-[120px]`, `border-radius: 16px`, `padding: 24px`, `display: flex; align-items: center; justify-content: center; text-align: center`.

**Ícone central:** SVG de upload (seta para cima com underline), 32px, `color-muted` em idle, `--color-primary` em drag-over.

**Input oculto:** `accept=".pdf,.docx,.txt"` — herda o accept já definido no form existente.

**Badges de formato:** Row de 3 chips abaixo da drop zone.

```
[chip: bg-slate-700, border-slate-600, rounded-full, px-3 py-1, 13px, 400]
  "PDF" | "DOCX" | "TXT"
```

Gap entre chips: `gap-2` (8px). Cor do texto: `--color-muted`. Sem ícones nos chips — texto puro.

**Touch target:** O `<label>` inteiro é clicável — drop zone já é o target de 120px mínimo.

---

### 3. Loading Modal — Steps de Importação (INV-02)

**Componente:** `<x-ui.loading-modal />` já incluído globalmente — não recriar.

**Alpine.data já registrado:** `Alpine.data('loadingModal', ...)` em `resources/js/app.js` (commit b6ff5ea).

**Invocação via evento:**

```javascript
window.dispatchEvent(new CustomEvent('open-loading-modal', {
    detail: {
        steps: ['Lendo documento', 'Extraindo dados', 'Organizando perfil', 'Concluído']
    }
}));
```

**Steps e timing visual:**

| Step index | Label | % progresso |
|------------|-------|------------|
| 0 | "Lendo documento" | 0% → 25% |
| 1 | "Extraindo dados" | 25% → 60% |
| 2 | "Organizando perfil" | 60% → 90% |
| 3 | "Concluído" | 90% → 100% |

**Progresso:** Interpolação linear entre steps. Cada step avança `progress` via `Alpine.store` ou evento customizado — o executor deve usar a API de `advance(stepIndex)` já definida no `loadingModal` Alpine.data.

**Visual do modal (herdado da Phase 1 — não alterar):**

| Elemento | Spec |
|----------|------|
| Backdrop | `fixed inset-0 z-50 backdrop-blur-md bg-black/60` |
| Card | `bg-slate-800 rounded-xl p-8 w-full max-w-md mx-4 shadow-2xl border border-slate-700` |
| Progress track | `h-2 rounded-full bg-slate-700 w-full mt-4` |
| Progress fill | `h-2 rounded-full bg-gradient-to-r from-blue-600 to-emerald-500 transition-all duration-500` |
| Step text | `text-sm text-slate-400 mt-3 text-center` — via `x-text="currentStep"` |
| Título | `text-white font-bold text-lg text-center` — "Processando seu currículo" |
| ESC durante operação | Bloqueado — `@keydown.escape.window.prevent` (já implementado) |
| Click fora | Bloqueado — sem dismiss (já implementado) |

**Estado de erro no modal:**

```
[banner vermelho: bg-red-500/10, border border-red-500/30, rounded-lg, p-4]
  [ícone: SVG X-circle, 20px, text-red-400]
  [texto: mensagem de erro — 14px, text-red-300]
  [botão: "Tentar novamente" — btn secondary, min-h-44px]
```

O botão "Tentar novamente" faz `error = null; progress = 0` e re-submete o form via JS.

---

### 4. Banner de Sucesso Pós-Importação (INV-01, INV-02)

Exibido logo abaixo do card de importação após importação bem-sucedida. Não substitui o card de importação — coexiste.

**Estrutura:**

```
[banner: bg-emerald-500/10, border border-emerald-500/30, rounded-xl, p-4, flex gap-3]
  [ícone: SVG check-circle, 20px, text-emerald-400, flex-shrink-0]
  [div]
    [strong: "Importação concluída" — 14px, 700, text-emerald-300]
    [p: "Seu inventário foi atualizado. Revise os campos abaixo." — 13px, 400, color-muted]
```

**Comportamento:** Aparece via Alpine `x-show` com transição `x-transition` (fade 300ms). Não tem botão de fechar — desaparece automaticamente após 8 segundos ou ao navegar para outra seção do inventário.

---

### 5. Feedback de Campo Preenchido pelo Import (INV-03)

Após importação bem-sucedida, cada campo do inventário que recebeu dados da IA exibe um micro-indicador visual.

**Onde aparece:** Nos `.career-item` de cada seção (experiences, skills, education, etc.) que tiveram itens criados pelo import.

**Implementação:** Badge `data-import-source="ai"` no item recém-criado.

**Visual do badge:**

```
[span: "via IA" — 11px, 700, text-blue-300, bg-blue-500/10, border border-blue-500/20,
       rounded-full, px-2 py-0.5, uppercase, letter-spacing: 0.06em]
```

Posição: Alinhado à direita dentro do `.career-item-meta` row, usando `display: flex; justify-content: space-between`.

**Persistência:** Badge visível apenas na sessão de revisão pós-importação (não persiste após page reload — implementado via localStorage com TTL de 30min ou via flash session do Laravel).

---

## Copywriting Contract (PT-BR)

### CTA primário

| Contexto | Label |
|----------|-------|
| Botão de submit do form de importação (idle) | "Importar currículo" |
| Botão de submit do form (com arquivo selecionado) | "Importar currículo" (sem mudança — clareza sobre ação) |
| Botão "Tentar novamente" no modal de erro | "Tentar novamente" |

### Eyebrow do card

"IMPORTAR CURRÍCULO" — uppercase, sem artigo.

### Títulos e instruções

| Elemento | Copy |
|----------|------|
| Título do card (h2) | "Importe seu currículo" |
| Subtítulo do card | "Suba PDF, DOCX ou TXT e a IA preenche o inventário campo a campo." |
| Instrução da drop zone (idle) | "Arraste seu arquivo aqui ou clique para selecionar" |
| Instrução da drop zone (drag-over) | "Solte o arquivo aqui" |
| Label de arquivo selecionado | "{nome-do-arquivo} · {tamanho}" |
| Título do loading modal | "Processando seu currículo" |

### Steps do modal (exatos — não alterar)

1. "Lendo documento"
2. "Extraindo dados"
3. "Organizando perfil"
4. "Concluído"

### Empty state do card de importação

O card de importação não tem empty state — é sempre exibido como CTA proativo.

### Estados de erro

| Contexto | Copy |
|----------|------|
| Arquivo de tipo inválido (não PDF/DOCX/TXT) | "Formato não suportado. Use um arquivo PDF, DOCX ou TXT." |
| Arquivo muito grande (> limite do servidor) | "Arquivo muito grande. O tamanho máximo é {X}MB." |
| Erro de leitura do documento (IA não conseguiu extrair) | "Não foi possível ler o arquivo. Verifique se o PDF não está protegido por senha e tente novamente." |
| Erro genérico de importação | "Algo deu errado ao processar o arquivo. Tente novamente em alguns instantes." |
| Erro 503 / IA sobrecarregada | "Serviço de IA sobrecarregado. Aguarde alguns minutos e tente novamente." |

Todos os erros aparecem no banner vermelho dentro do modal (via `error` state do `loadingModal`). Erros de validação de arquivo (tipo/tamanho) podem aparecer diretamente na drop zone antes de submeter.

### Estado de sucesso

| Elemento | Copy |
|----------|------|
| Título do banner de sucesso | "Importação concluída" |
| Corpo do banner | "Seu inventário foi atualizado. Revise os campos abaixo." |
| Step final do modal | "Concluído" |

### Ações destrutivas desta fase

Nenhuma ação destrutiva irreversível nesta fase. A importação sobrescreve/cria campos — mas os dados originais (se houver) permanecem pois a IA adiciona itens, não apaga registros existentes. Se o comportamento for de merge aditivo (recomendado), nenhum confirm necessário. Se o executor decidir limpar seções antes de preencher, adicionar aviso inline no card (não dialog): "A importação adicionará itens ao seu inventário existente."

---

## Interações e Fluxo Completo

### Fluxo happy path

```
1. Usuário chega na página career/index
2. Vê card de importação no topo (acima da summary grid)
3. Arrasta arquivo ou clica → seleciona PDF/DOCX/TXT
4. Drop zone exibe nome do arquivo + tamanho
5. Clica "Importar currículo"
6. Form submete via fetch/XHR (sem page reload)
7. window.dispatchEvent('open-loading-modal', { steps: [...] }) acionado
8. Modal abre: step 1 "Lendo documento" (0% → 25%)
9. Backend responde com progresso: step 2 "Extraindo dados" (25% → 60%)
10. Step 3 "Organizando perfil" (60% → 90%)
11. Step 4 "Concluído" (90% → 100%) → modal fecha após 400ms
12. Banner de sucesso aparece abaixo do card de importação
13. Seção "Perfil" do inventário (ou seção com mais dados novos) fica ativa
14. Items com badge "via IA" visíveis para revisão
```

**Nota de implementação:** O backend pode usar SSE (Server-Sent Events) ou polling para avançar os steps. Se usar resposta única ao final, o executor deve fazer animação temporal simulando os 4 steps com intervalos fixos (ex: 800ms entre steps) enquanto aguarda o response.

### Fluxo de erro

```
1-5. (igual ao happy path)
6. Form submete
7. Modal abre, animação inicia
8. Resposta de erro retorna
9. modal.fail("mensagem de erro") acionado
10. Modal exibe banner vermelho com mensagem + botão "Tentar novamente"
11. ESC ainda bloqueado durante error state? → DESBLOQUEADO no error state (usuário pode fechar)
12. "Tentar novamente" → fecha modal, usuário pode selecionar novo arquivo
```

**Comportamento de ESC no error state:** ESC liberado quando `error !== null` — usuário não deve ficar preso. O `@keydown.escape.window.prevent` deve ser condicional: `@keydown.escape.window="if(!error) $event.preventDefault()"`.

### Responsividade

| Breakpoint | Comportamento |
|------------|---------------|
| ≥1220px | Card de importação full-width acima da summary grid; drop zone ocupa ~50% da largura do card; badges de formato alinhados à esquerda |
| 860–1219px | Card full-width; drop zone full-width; badges centralizados |
| <860px | Card full-width; drop zone full-width com min-h-[120px]; badges em row wrappável |

---

## Accessibility

Herdado da Phase 1 + adições desta fase:

| Requisito | Spec |
|-----------|------|
| Drop zone acessível por teclado | `<label>` wrapping input é focalizável; Enter/Space abre seletor de arquivo |
| Input file | `aria-label="Selecionar arquivo de currículo"` |
| Drop zone | `role="button"` + `aria-label="Área de upload — arraste ou clique para selecionar"` |
| Loading modal | `role="dialog"` + `aria-modal="true"` + `aria-label="Processando seu currículo"` (herdado) |
| Banner de sucesso | `role="status"` + `aria-live="polite"` |
| Banner de erro no modal | `role="alert"` + `aria-live="assertive"` |
| Badge "via IA" | `aria-label="Preenchido pela IA durante importação"` |
| Contraste: `#10B981` sobre `#1e293b` | ratio ~4.6:1 — PASSA AA (herdado) |
| Contraste: `#EF4444` sobre `#1e293b` | ratio ~5.1:1 — PASSA AA |
| Touch targets | `min-h-[44px]` em todos os botões; drop zone `min-h-[120px]` excede o mínimo |
| Drag-and-drop | Não é o único método — click também funciona (não depende de mouse) |

---

## Arquivos a criar/modificar

| Ação | Arquivo | Nota |
|------|---------|------|
| Modificar | `resources/views/career/index.blade.php` | Adicionar card de importação acima de `#career-items` |
| Modificar | `resources/views/career/partials/inventory-list.blade.php` | Remover form antigo da sidebar direita (`.career-quality-column`) |
| Criar | `resources/views/components/ui/import-card.blade.php` | Componente do card de importação (se o executor optar por componentizar) — opcional |
| Modificar | `resources/js/app.js` | Lógica de drag-and-drop + fetch submit + eventos do modal |
| Modificar | CSS em `index.blade.php` (`@push('styles')`) | Estilos da drop zone e banner de sucesso |

> O executor pode optar por inlinar o card em `index.blade.php` diretamente em vez de criar um Blade component, dado que o projeto usa este padrão para outros elementos da página.

---

## Registry Safety

| Registry | Blocks Used | Safety Gate |
|----------|-------------|-------------|
| shadcn official | nenhum — projeto não usa shadcn | not applicable |
| Terceiros | nenhum | not applicable |

Stack é 100% Blade + Tailwind + Alpine sem registries de componente externos.

---

## Checker Sign-Off

- [ ] Dimension 1 Copywriting: PASS
- [ ] Dimension 2 Visuals: PASS
- [ ] Dimension 3 Color: PASS
- [ ] Dimension 4 Typography: PASS
- [ ] Dimension 5 Spacing: PASS
- [ ] Dimension 6 Registry Safety: PASS

**Approval:** pending
