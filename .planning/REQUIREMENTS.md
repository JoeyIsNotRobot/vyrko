# Requirements: Vyrko

**Defined:** 2026-06-20
**Core Value:** Gerar o currículo certo para a vaga certa usando evidências reais do inventário do usuário.

## v1 Requirements

### Design System (DESIGN)

- [ ] **DESIGN-01**: Sistema de cores atualizado aplicado globalmente — azul #2563EB, positivo #10B981, warning #F59E0B
- [ ] **DESIGN-02**: Footer fixo no rodapé de todas as páginas via layout wrapper (min-h-screen flex flex-col / main flex-1)
- [ ] **DESIGN-03**: Componente de modal de loading universal reutilizável com backdrop blur, barra de progresso animada, steps dinâmicos por operação, bloqueio de interação e fechamento apenas após conclusão ou erro
- [ ] **DESIGN-04**: Espaçamento e tipografia padronizados em todos os cards e listas (gap-6 entre blocos, mb-1 entre label e valor)

### Dashboard (DASH)

- [ ] **DASH-01**: Dashboard exibe exatamente 3 cards: Vagas analisadas, Currículos gerados, Completude do inventário (%)
- [ ] **DASH-02**: Card de completude do inventário exibe CTA orientador quando completude < 80%

### Inventário (INV)

- [ ] **INV-01**: Seção de importação de currículo exibida no topo da página com destaque, aceitando PDF, DOCX e TXT explicitamente
- [ ] **INV-02**: Importação exibe modal de loading com barra de progresso por etapa: "Lendo documento → Extraindo dados → Organizando perfil → Concluído"
- [ ] **INV-03**: Prompt de IA para importação mapeia campo a campo (nome, cargo atual, empresa/cargo/período/responsabilidades, habilidades técnicas, educação, idiomas com nível, certificações) sem inferências genéricas

### Workspace de Vagas (JOB)

- [ ] **JOB-01**: Lista de workspaces renomeada para "Workspace de vagas"; campo "Tipo de currículo" (tech/nacional/executivo/internacional) removido do formulário de criação
- [ ] **JOB-02**: Formulário de vaga inclui campo "Link da vaga" (LinkedIn, Glassdoor, etc.) com fetch automático da descrição ao colar URL válida
- [ ] **JOB-03**: Após colar descrição da vaga, exibir card guia: título detectado, badge de status "Pendente análise" e CTA "Analisar vaga →"
- [ ] **JOB-04**: Análise de vaga exibe modal de loading com steps: "Lendo vaga → Comparando inventário → Gerando análise"
- [ ] **JOB-05**: Sistema valida descrição de vaga antes de processar: bloqueia e alerta quando texto < 100 caracteres
- [ ] **JOB-06**: Erros de IA tratados com mensagens amigáveis: 503 → "Serviço de IA sobrecarregado, tente em alguns minutos" + botão retry; prompt inválido → mensagem de orientação
- [ ] **JOB-07**: Badge de adequação exibido pós-análise: "Esta vaga é adequada para você" (match ≥ 70%) ou "Atenção: gaps críticos detectados" (match < 50%)
- [ ] **JOB-08**: Seletor de template exibe 3 opções com descrições enriquecidas e recomendação da IA de qual usar para aquela vaga específica
- [ ] **JOB-09**: Prompts de IA para análise de vaga revisados: instruções rígidas de extração de requisitos ATS, skills obrigatórias vs. desejáveis, checklist de qualidade estruturado

### LinkedIn QueryBuilder (QUERY)

- [ ] **QUERY-01**: Página renomeada de "Search Builder Grátis" para "LinkedIn QueryBuilder" com subtítulo "Monte buscas booleanas precisas e abra direto no LinkedIn."
- [ ] **QUERY-02**: Input de tags substituído por chips visíveis com botão X de remoção e placeholder "Digite e pressione Enter ou vírgula"
- [ ] **QUERY-03**: Formulário inclui campos universais: Setor/Indústria, Tipo de empresa (startup/corp/remota), Função/Área — além de stack técnica (opcional)
- [ ] **QUERY-04**: Dois botões distintos de abertura: "Abrir no LinkedIn Jobs" (URL: `/jobs/search/?keywords=QUERY`) e "Abrir no LinkedIn Posts" (URL: `/search/results/content/?keywords=QUERY`) com query boolean encodada corretamente

### Nomenclaturas e Copy (COPY)

- [ ] **COPY-01**: Landing headline atualizado para "Seu currículo certo, para a vaga certa."
- [ ] **COPY-02**: Textos de todas as telas revisados: Inventário ("Seu inventário de carreira"), Templates ("Qual currículo enviar para esta vaga?"), Conta ("Sua conta"), empty states orientadores
- [ ] **COPY-03**: Campos de área de atuação e função com autocomplete (não dropdowns fixos com opções só de tech)

### Monetização (MONO)

- [ ] **MONO-01**: Plano Free requer conta Google vinculada (OAuth) para ativar uso de features de IA; sem Google vinculado, CTA de vinculação é exibido em vez da ação de IA
- [ ] **MONO-02**: Cloudflare Turnstile integrado no fluxo de uso de IA do plano Free (verificação anti-bot periódica)
- [ ] **MONO-03**: Estrutura de 4 planos mapeada ao UsageLimiter existente: Free (2 análises/mês, 1 PDF), Avulso (R$12/análise, créditos), Sprint (R$49/mês, 15 análises + DOCX), Pro (R$99/mês, ilimitado)
- [ ] **MONO-04**: Tela de preços exibindo os 4 planos com features, preços e CTAs de upgrade
- [ ] **MONO-05**: LinkedIn QueryBuilder marcado como sempre gratuito, sem exigência de plano ou Google vinculado

## v2 Requirements

### Pagamento Real

- **PAY-01**: Integração Mercado Pago para compra de créditos avulsos (PIX + cartão)
- **PAY-02**: Webhook de confirmação de pagamento atualiza ai_credits_balance
- **PAY-03**: Fluxo de upgrade de plano (Free → Sprint → Pro)
- **PAY-04**: Tela de histórico de cobranças

### Export Avançado

- **EXP-01**: Export DOCX com PHPWord (coberto no roadmap legado Fase 2)
- **EXP-02**: Export PDF server-side com Browsershot ou dompdf (coberto no roadmap legado Fase 2)

### Onboarding

- **ONB-01**: Fluxo guiado de onboarding para primeiro currículo (coberto no roadmap legado Fase 4)

## Out of Scope

| Feature | Reason |
|---------|--------|
| Stripe / pagamento internacional | Parqueado — Mercado Pago BR primeiro para validação |
| Integração Mercado Pago real (fluxo de pagamento) | v2 — validar conversão antes de construir billing completo |
| Export DOCX | Roadmap legado Fase 2 — não duplicar esforço aqui |
| CI/GitHub Actions | Roadmap legado Fase 1 — não duplicar aqui |
| LGPD completo (exclusão, DSAR) | Roadmap legado Fase 3 |
| Mobile app | Web-first; mobile é v2+ |
| Scraping headless de URL de vaga | MVP: fetch básico de URL; scraping headless é Fase futura |
| Multi-provider de IA (OpenAI, Anthropic) | Gemini único no beta |

## Traceability

| Requirement | Phase | Status |
|-------------|-------|--------|
| DESIGN-01 | Phase 1 | Pending |
| DESIGN-02 | Phase 1 | Pending |
| DESIGN-03 | Phase 1 | Pending |
| DESIGN-04 | Phase 1 | Pending |
| DASH-01 | Phase 1 | Pending |
| DASH-02 | Phase 1 | Pending |
| COPY-01 | Phase 1 | Pending |
| COPY-02 | Phase 1 | Pending |
| COPY-03 | Phase 1 | Pending |
| INV-01 | Phase 2 | Pending |
| INV-02 | Phase 2 | Pending |
| INV-03 | Phase 2 | Pending |
| JOB-01 | Phase 3 | Pending |
| JOB-02 | Phase 3 | Pending |
| JOB-03 | Phase 3 | Pending |
| JOB-04 | Phase 3 | Pending |
| JOB-05 | Phase 3 | Pending |
| JOB-06 | Phase 3 | Pending |
| JOB-07 | Phase 3 | Pending |
| JOB-08 | Phase 3 | Pending |
| JOB-09 | Phase 3 | Pending |
| QUERY-01 | Phase 4 | Pending |
| QUERY-02 | Phase 4 | Pending |
| QUERY-03 | Phase 4 | Pending |
| QUERY-04 | Phase 4 | Pending |
| MONO-01 | Phase 5 | Pending |
| MONO-02 | Phase 5 | Pending |
| MONO-03 | Phase 5 | Pending |
| MONO-04 | Phase 5 | Pending |
| MONO-05 | Phase 5 | Pending |

**Coverage:**
- v1 requirements: 30 total
- Mapped to phases: 30
- Unmapped: 0 ✓

---
*Requirements defined: 2026-06-20*
*Last updated: 2026-06-20 after milestone v1.0 initialization*
