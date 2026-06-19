# Roadmap — Vyrko

**Alvo desta rodada:** beta fechado → caminho para produção.
**Fora do escopo imediato:** cobrança real (ver Fase 6, parqueada — validar antes, conforme pesquisa de mercado).
**Base:** MVP Laravel funcional (inventário → vaga → match com evidências → currículo). Gap analysis em `2026-06-19`.

## Definição de Pronto (global)

Ao fim de cada fase: o incremento **roda e é verificável isoladamente**, `php artisan test` + `pint --test` passam, texto em PT/EN, nenhum dado inventado (evidência rastreável), CI verde. Ver `STANDARDS.md`.

## Fases

### Fase 1 — Fundação de Engenharia & Confiabilidade da IA  `pendente`
- **Objetivo:** estabelecer o piso de qualidade para evoluir sem regressões.
- **Escopo:** CI no GitHub Actions (`php artisan test` + `pint --test` a cada push); hardening da camada de IA (timeout, JSON inválido e falha do Gemini viram erro tratado e amigável, sem quebrar o fluxo); completar o `FakeAiClient` (hoje com stubs vazios em resume/ats/linkedin/import) para testes determinísticos de todas as 6 features.
- **Entregável:** pipeline verde a cada push; ao simular falha da IA, o usuário vê erro tratado e a app não quebra; todas as features cobertas por teste com Fake real.
- **Depende de:** —

### Fase 2 — Export Profissional de Currículo  `pendente`
- **Objetivo:** entregar o artefato final que o usuário vem buscar.
- **Escopo:** geração real de **PDF** (lib backend, ex. Browsershot/dompdf) para os 3 templates ATS-safe; **DOCX** (PHPWord); botão "copiar texto" (campo `plain_text` já existe); rotas de download com `Content-Disposition`.
- **Entregável:** usuário baixa o currículo gerado em PDF e DOCX, nos 3 templates, e copia o texto puro.
- **Depende de:** Fase 1

### Fase 3 — LGPD & Privacidade  `pendente`
- **Objetivo:** conformidade legal mínima para operar com usuários reais no Brasil.
- **Escopo:** exclusão de conta + cascata de todos os dados; exportação de dados do usuário (DSAR, JSON portável); criptografia dos tokens OAuth em `social_accounts`; política de privacidade + consentimento explícito.
- **Entregável:** usuário exclui a conta e baixa seus dados; tokens armazenados criptografados; consentimento registrado.
- **Depende de:** Fase 1

### Fase 4 — Ativação & Onboarding  `pendente`
- **Objetivo:** levar o usuário ao primeiro currículo gerado com o mínimo de atrito (meta da pesquisa: % alto completa inventário e gera 1 match).
- **Escopo:** onboarding guiado (import → inventário → primeira vaga → primeiro match); estados vazios que orientam; landing de convite para o beta com as mensagens da pesquisa ("sem inventar sua experiência", ATS BR/Gupy, internacional); instrumentação básica de funil.
- **Entregável:** um novo usuário convidado chega da landing ao primeiro currículo gerado num fluxo guiado, e o funil é medido.
- **Depende de:** Fase 2

### Fase 5 — Segurança & Deploy de Produção  `pendente`
- **Objetivo:** colocar o beta no ar de forma estável, segura e observável.
- **Escopo:** testes de segurança/autorização (CSRF, XSS, acesso cruzado), rate-limiting nas rotas de IA; error tracking (ex. Sentry) e logs estruturados; pipeline/processo de deploy + backup de banco; checklist de secrets/env.
- **Entregável:** app no ar acessível aos usuários do beta, monitorada, com deploy reproduzível e backup.
- **Depende de:** Fases 1–4

### Fase 6 — Monetização & Planos  `parqueada (pós-validação)`
- **Objetivo:** transformar os limites estáticos num produto cobrável.
- **Escopo:** gateway de pagamento (PIX/Mercado Pago ou Stripe — a definir); planos (free/avulso/sprint/pro) mapeados ao `UsageLimiter` + `ai_credits_balance` já existentes; compra de créditos, upgrade, webhook; tela de preços.
- **Entregável:** usuário paga e tem limites/créditos atualizados automaticamente.
- **Depende de:** Fases 1, 3 — **só iniciar após sinais de validação** (pesquisa de mercado recomenda validar pagamento antes de construir billing completo).
