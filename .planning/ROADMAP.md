# Roadmap: Vyrko — v1.0 UI/UX Overhaul + Monetização MVP

## Overview

Transforma o Vyrko de prototype funcional em produto com UX profissional e modelo de cobrança validável em beta. Começa pelo design system e foundation visual que todas as fases dependem, avança por inventário e workspace de vagas como fluxo central da plataforma, refina o LinkedIn QueryBuilder como ferramenta de geração de leads gratuita, e finaliza com monetização que porta os limites de backend já existentes para uma experiência de produto real.

## Phases

**Phase Numbering:**
- Integer phases (1, 2, 3): Planned milestone work
- Decimal phases (2.1, 2.2): Urgent insertions (marked with INSERTED)

Decimal phases appear between their surrounding integers in numeric order.

- [x] **Phase 1: Design System + Dashboard + Copy** - Fundação visual unificada, dashboard orientador e textos revisados em todas as telas
- [ ] **Phase 2: Inventário com Import** - Importação de PDF/DOCX no inventário com modal de progresso e prompt de IA revisado
- [ ] **Phase 3: Workspace de Vagas** - Fluxo guiado completo de análise de vaga com tratamento de erros, badge de adequação e recomendação de template
- [ ] **Phase 4: LinkedIn QueryBuilder v2** - QueryBuilder com chips, campos universais e URLs corretas para Jobs e Posts
- [ ] **Phase 5: Monetização MVP** - Planos mapeados ao UsageLimiter, Google obrigatório no Free, Turnstile anti-bot e tela de preços

## Phase Details

### Phase 1: Design System + Dashboard + Copy
**Goal**: Toda a aplicação exibe visual consistente com o design system definido, o dashboard orienta o usuário com 3 cards, e os textos de todas as telas comunicam o produto corretamente
**Depends on**: Nothing (first phase)
**Requirements**: DESIGN-01, DESIGN-02, DESIGN-03, DESIGN-04, DASH-01, DASH-02, COPY-01, COPY-02, COPY-03
**Success Criteria** (what must be TRUE):
  1. Qualquer tela da aplicação exibe as cores primárias azul #2563EB, positivo #10B981, warning #F59E0B sem cores avulsas inconsistentes
  2. O footer aparece fixo no rodapé em todas as páginas sem sobrepor conteúdo
  3. O modal de loading universal pode ser invocado em qualquer operação com barra de progresso animada e steps configuráveis por operação
  4. O dashboard exibe exatamente 3 cards (Vagas analisadas, Currículos gerados, Completude do inventário) com CTA orientador quando completude < 80%
  5. A landing exibe "Seu currículo certo, para a vaga certa." e os campos de área/função usam autocomplete em vez de dropdowns fixos de tech
**Plans**: 3 plans
Plans:
- [x] 01-PLAN-01.md — Alpine.js install + migração completa de CSS vars (--vscode-* → --color-primary/etc.) + spacing metric-card
- [x] 01-PLAN-02.md — Footer flex layout + dashboard 3 cards + $completeness controller + copy strings 4 telas
- [x] 01-PLAN-03.md — Loading modal component Alpine + datalist autocomplete área/função
**UI hint**: yes

### Phase 2: Inventário com Import
**Goal**: O usuário pode importar seu currículo em PDF, DOCX ou TXT com feedback visual de progresso, e a IA extrai os dados campo a campo sem inferências genéricas
**Depends on**: Phase 1
**Requirements**: INV-01, INV-02, INV-03
**Success Criteria** (what must be TRUE):
  1. O topo da página de inventário exibe seção de importação com destaque, aceitando PDF, DOCX e TXT explicitamente
  2. Ao importar, o modal de loading mostra os steps "Lendo documento → Extraindo dados → Organizando perfil → Concluído" com barra de progresso animada
  3. Após importação, os campos do inventário são preenchidos com os dados reais do documento (nome, cargo, empresa, responsabilidades, habilidades, educação, idiomas com nível, certificações) sem dados inventados ou genéricos
**Plans**: 3 plans
Plans:
- [ ] 02-01-PLAN.md — Card de importação Alpine no topo (drop zone + drag-drop) + remoção do form da sidebar
- [ ] 02-02-PLAN.md — Listeners CustomEvent no loading-modal + integração AJAX data-import-form com modal steps
- [ ] 02-03-PLAN.md — Sub-schemas tipados no resumeImportParsePrompt + badge "via IA" via flash session
**UI hint**: yes

### Phase 3: Workspace de Vagas
**Goal**: O usuário tem um fluxo guiado completo desde colar a descrição da vaga até gerar currículo, com tratamento de erros amigável, badge de adequação e recomendação inteligente de template
**Depends on**: Phase 1
**Requirements**: JOB-01, JOB-02, JOB-03, JOB-04, JOB-05, JOB-06, JOB-07, JOB-08, JOB-09
**Success Criteria** (what must be TRUE):
  1. A lista de workspaces é chamada "Workspace de vagas" e o formulário de criação não exibe campo "Tipo de currículo"; o formulário inclui campo "Link da vaga" que faz fetch automático da descrição ao colar URL válida
  2. Ao colar descrição da vaga, um card guia exibe título detectado, badge "Pendente análise" e botão "Analisar vaga →"; tentativa de analisar texto com menos de 100 caracteres é bloqueada com alerta
  3. Durante análise, o modal de loading exibe steps "Lendo vaga → Comparando inventário → Gerando análise"; erros de IA exibem mensagem amigável com opção de retry em vez de stack trace
  4. Pós-análise, um badge exibe "Esta vaga é adequada para você" (match ≥ 70%) ou "Atenção: gaps críticos detectados" (match < 50%)
  5. O seletor de template exibe 3 opções com descrições enriquecidas e recomendação da IA de qual usar para aquela vaga específica
**Plans**: TBD
**UI hint**: yes

### Phase 4: LinkedIn QueryBuilder v2
**Goal**: O LinkedIn QueryBuilder exibe chips visuais para tags, inclui campos universais de setor/tipo de empresa/função e gera URLs corretas para LinkedIn Jobs e LinkedIn Posts
**Depends on**: Phase 1
**Requirements**: QUERY-01, QUERY-02, QUERY-03, QUERY-04
**Success Criteria** (what must be TRUE):
  1. A página é chamada "LinkedIn QueryBuilder" com subtítulo "Monte buscas booleanas precisas e abra direto no LinkedIn."
  2. Tags são exibidas como chips com botão X de remoção; usuário adiciona tags pressionando Enter ou vírgula
  3. O formulário inclui campos Setor/Indústria, Tipo de empresa e Função/Área além da stack técnica (opcional)
  4. Dois botões distintos abrem a query em LinkedIn Jobs (`/jobs/search/?keywords=QUERY`) e LinkedIn Posts (`/search/results/content/?keywords=QUERY`) com a query boolean encodada corretamente
**Plans**: TBD
**UI hint**: yes

### Phase 5: Monetização MVP
**Goal**: Os planos Free/Avulso/Sprint/Pro estão mapeados ao UsageLimiter existente, usuários Free sem Google vinculado são redirecionados para OAuth antes de usar IA, Turnstile anti-bot está ativo no fluxo Free, e a tela de preços exibe planos com CTAs
**Depends on**: Phase 3
**Requirements**: MONO-01, MONO-02, MONO-03, MONO-04, MONO-05
**Success Criteria** (what must be TRUE):
  1. Usuário do plano Free sem conta Google vinculada vê CTA de vinculação OAuth em vez do botão de ação de IA
  2. Usuário do plano Free que usa IA passa por verificação Cloudflare Turnstile periodicamente antes da operação ser processada
  3. Os 4 planos (Free: 2 análises/mês + 1 PDF; Avulso: R$12/análise; Sprint: R$49/mês + 15 análises + DOCX; Pro: R$99/mês + ilimitado) estão refletidos nos limites do UsageLimiter
  4. A tela de preços exibe os 4 planos com features comparadas, preços e CTAs de upgrade
  5. O LinkedIn QueryBuilder funciona para qualquer usuário sem exigir plano pago ou Google vinculado
**Plans**: TBD

## Progress

**Execution Order:**
Phases execute in numeric order: 1 → 2 → 3 → 4 → 5

| Phase | Plans Complete | Status | Completed |
|-------|----------------|--------|-----------|
| 1. Design System + Dashboard + Copy | 3/3 | Complete | 2026-06-20 |
| 2. Inventário com Import | 0/3 | Not started | - |
| 3. Workspace de Vagas | TBD | Not started | - |
| 4. LinkedIn QueryBuilder v2 | TBD | Not started | - |
| 5. Monetização MVP | TBD | Not started | - |
