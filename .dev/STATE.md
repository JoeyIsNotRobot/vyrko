# Estado — Vyrko

- **Fase atual:** nenhuma (roadmap recém-definido)
- **Próximo passo:** `/plan-phase 1`

## Contexto rápido
- MVP Laravel funcional clonado e mapeado (gap analysis em 2026-06-19).
- Alvo desta rodada: beta fechado → produção. Billing parqueado (Fase 6, pós-validação).
- Roadmap e padrões definidos em `ROADMAP.md` / `STANDARDS.md`.

## Decisões abertas
- **Fase 2 — lib de PDF:** geração server-side confirmada (D-04); Browsershot (fiel, +Chromium) vs dompdf (puro PHP) fica para o `/plan-phase 2`, após ver o CSS real dos 3 templates.
- **Fase 5 — hospedagem do beta:** indefinida; precisa suportar Chromium **se** a lib de PDF for Browsershot.
- **Fase 5 — error tracking:** indefinido (tendência: Sentry).
- **Fase 6 — gateway de pagamento:** indefinido (tendência: PIX/Mercado Pago). Parqueado até validação.
- Registro durável e detalhado em `DECISIONS.md`.

## Blockers
- Ambiente local ainda não validado: falta `composer install`, `.env`, e Docker/Sail ou PHP local para rodar a app e os testes.

## Decisões tomadas
Registro durável em `DECISIONS.md`. Resumo:
- Camada `.dev/` versionada junto ao repo.
- Roadmap em 6 fases; billing parqueado (Fase 6, pós-validação).
- Execução começa pela Fase 1 (Fundação & CI).
- **Git:** trabalho direto na `main` (sem PR; CI roda no push).
- **PDF:** geração server-side no backend na Fase 2 — encerra o `window.print()` atual.
- **DOCX:** PHPWord · **CI:** GitHub Actions (test + pint) · **IA beta:** Gemini único · **FakeAiClient** determinístico.
