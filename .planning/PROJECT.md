# Vyrko

## What This Is

Vyrko é uma plataforma web para profissionais que buscam emprego gerarem currículos precisos e personalizados por vaga. O usuário monta um inventário de carreira com sua experiência real, cola a descrição de uma vaga, e a IA analisa o match, aponta gaps e gera o currículo certo para aquela oportunidade. Público primário: devs brasileiros. Secundário: qualquer profissional que precise passar por triagem ATS.

## Core Value

Gerar o currículo certo para a vaga certa usando evidências reais do inventário do usuário — sem inventar experiências.

## Current Milestone: v1.0 UI/UX Overhaul + Monetização MVP

**Goal:** Transformar o Vyrko de prototype funcional em produto com UX profissional e modelo de cobrança validável em beta.

**Target features:**
- Design system unificado (paleta, footer sticky, modal de loading universal)
- Dashboard enxuto (3 cards orientadores)
- Inventário com import PDF/DOCX/TXT e modal de progresso
- Workspace de vagas com fluxo guiado, tratamento de erros de IA, badge de adequação
- LinkedIn QueryBuilder v2 com campos universais e URLs corretas
- Copy/slogans revisados em todas as telas
- Modelo de planos (Free/Avulso/Sprint/Pro) com Google obrigatório no free + Turnstile anti-bot

## Requirements

### Validated

- ✓ Inventário de carreira com campos estruturados (perfil, experiências, habilidades, projetos, educação, idiomas, certificações)
- ✓ Importação de currículo via TXT com parsing por IA (Gemini)
- ✓ Workspace de análise de vagas com match score e gap analysis
- ✓ Geração de currículo em 3 templates (ATS Clássico, Tech Compacto, Internacional Clean)
- ✓ Export PDF via window.print() (temporário — substituído em Fase 2 do roadmap legado)
- ✓ LinkedIn QueryBuilder básico com busca booleana
- ✓ OAuth Google e LinkedIn (social_accounts)
- ✓ UsageLimiter + ai_credits_balance no backend
- ✓ AiRun log de uso de IA

### Active

- [ ] Design system com paleta #2563EB/#10B981/#F59E0B
- [ ] Footer sticky em todas as páginas
- [ ] Modal de loading universal com barra de progresso por steps
- [ ] Dashboard reduzido a 3 cards orientadores
- [ ] Import PDF/DOCX no inventário
- [ ] Fluxo guiado de análise de vaga (card de próximo passo, modal de análise)
- [ ] Tratamento de erros de IA (503, prompt inválido, descrição < 100 chars)
- [ ] Badge de adequação pós-análise
- [ ] Recomendação de template + descrições enriquecidas dos 3 templates
- [ ] LinkedIn QueryBuilder v2 (chips, campos universais, URLs corretas)
- [ ] Copy/slogans revisados em todas as telas
- [ ] Planos Free/Avulso/Sprint/Pro mapeados ao UsageLimiter
- [ ] Google obrigatório para uso de IA no plano Free
- [ ] Cloudflare Turnstile anti-bot no fluxo de IA free
- [ ] Tela de preços com CTAs

### Out of Scope

| Feature | Reason |
|---------|--------|
| Stripe / pagamento internacional | Parqueado — Mercado Pago primeiro para validação BR |
| Export DOCX | Coberto no roadmap legado Fase 2 — não duplicar aqui |
| CI/GitHub Actions | Roadmap legado Fase 1 — não duplicar aqui |
| LGPD completo | Roadmap legado Fase 3 — não duplicar aqui |
| Mobile app | Web-first; mobile é v2+ |
| Scraping automático de vaga via URL | MVP: campo de URL com fetch básico; scraping headless é fase futura |

## Context

- Stack: Laravel 11 + Blade + Vite + Alpine.js + TailwindCSS + SQLite (dev) → MySQL (prod)
- IA: Gemini 2.5 Flash via HTTP com retry existente; FakeAiClient para testes
- PDF atual: window.print() — funcional mas inconsistente; substituição server-side pendente
- Auth: Laravel Socialite com Google e LinkedIn já implementados
- Backend de limites: UsageLimiter + ai_credits_balance + AiRun já existem
- Roadmap legado (.dev/ROADMAP.md): fases 1-6 ainda pendentes; este milestone foca em UX/produto prioritariamente
- Todas as views são Blade com componentes Alpine.js; CSS via TailwindCSS
- Idioma padrão da interface: PT-BR com suporte a EN

## Constraints

- **Stack**: Laravel/Blade/Tailwind — sem trocar por React/Vue; JS inline via Alpine.js
- **IA**: Gemini único no beta; sem multi-provider agora
- **Pagamento**: Mercado Pago apenas (PIX + cartão BR) — Stripe parqueado
- **Anti-bot**: Cloudflare Turnstile (free tier para volume baixo de beta)
- **Solo dev**: sem cerimônia de PR; trabalho direto na main; CI roda no push

## Key Decisions

| Decision | Rationale | Outcome |
|----------|-----------|---------|
| Google obrigatório no plano Free | Barreira leve anti-abuso sem paywall total; OAuth já implementado | — Pending |
| Turnstile (Cloudflare) vs hCaptcha | Turnstile free, menor fricção para usuário | — Pending |
| Mercado Pago primeiro | Brasil é mercado alvo; PIX reduz abandono de checkout | — Pending |
| QueryBuilder sempre gratuito | Lead gen / retenção; sem custo de IA (só front-end) | — Pending |
| Modal loading universal como componente Blade | Reutilizável, sem duplicação; Alpine.js gerencia estado | — Pending |
| PDF server-side (Browsershot vs dompdf) | window.print() inconsistente; decisão de lib fica no plan-phase da Fase 2 legada | — Pending |

## Evolution

This document evolves at phase transitions and milestone boundaries.

**After each phase transition** (via `/gsd-transition`):
1. Requirements invalidated? → Move to Out of Scope with reason
2. Requirements validated? → Move to Validated with phase reference
3. New requirements emerged? → Add to Active
4. Decisions to log? → Add to Key Decisions
5. "What This Is" still accurate? → Update if drifted

**After each milestone** (via `/gsd:complete-milestone`):
1. Full review of all sections
2. Core Value check — still the right priority?
3. Audit Out of Scope — reasons still valid?
4. Update Context with current state

---
*Last updated: 2026-06-20 after milestone v1.0 initialization*
