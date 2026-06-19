# Registro de Decisões — Vyrko

Log durável de decisões de engenharia/produto do projeto. Estado efêmero (fase atual, próximo passo, blockers) fica em `STATE.md`. Decisões arquiteturais de longo prazo podem ser promovidas ao vault (`60 Decisions/`) via `/vault-save`.

Data de referência deste lote: **2026-06-19**.

## Decisões tomadas

| # | Decisão | Razão | Afeta |
|---|---|---|---|
| D-01 | **Alvo:** beta fechado → caminho para produção | Validar uso/conversão antes de operar comercialmente (pesquisa de mercado) | Escopo geral |
| D-02 | **Billing parqueado** (Fase 6, pós-validação) | Pesquisa recomenda validar pagamento (landing+concierge) antes de construir billing | Fase 6 |
| D-03 | **Git: trabalho direto na `main`** | Dev solo; CI roda no push e serve de gate. Sem cerimônia de PR | Processo |
| D-04 | **PDF: geração server-side no backend** | `window.print()` atual é inconsistente entre navegadores, não armazenável/enviável, e some em mobile. Ver detalhe abaixo | Fase 2 |
| D-05 | **DOCX: PHPWord** | Única lib madura de escrita DOCX em PHP | Fase 2 |
| D-06 | **CI: GitHub Actions** rodando `php artisan test` (SQLite in-memory) + `pint --test` no push | Repo já no GitHub; testes já usam SQLite+`AI_PROVIDER=fake` | Fase 1 |
| D-07 | **`FakeAiClient` completado** com respostas determinísticas (sair dos stubs vazios) | Permitir feature tests reais das 6 features de IA | Fase 1 |
| D-08 | **Hardening da IA:** exceção de domínio + mensagem i18n + retry existente | Falha do Gemini deve virar erro tratado, não quebrar o fluxo | Fase 1 |
| D-09 | **Provider de IA no beta: Gemini `gemini-2.5-flash` único** | Já implementado e com custo logado em `AiRun` | — |
| D-10 | **Camada `.dev/` versionada** junto ao repo | Estado operacional vive com o código | Processo |

### Detalhe — D-04 (PDF server-side)

Conflito resolvido pelo código (fonte de verdade): o "Baixar PDF" de hoje é `window.print()` em [print.blade.php:76](../resources/views/resumes/print.blade.php), o controller `print()` retorna uma View ([ResumeVersionController.php:101](../app/Http/Controllers/ResumeVersionController.php)), e não há lib de PDF no `composer.json`. O `pdftotext` existente serve para **importar** CV, não gerar. Decisão: o backend passa a produzir o arquivo PDF. A escolha da lib (**Browsershot** fiel/+Chromium vs **dompdf** puro-PHP/simples) fica para o `/plan-phase 2`, após inspecionar o CSS real dos 3 templates.

## Decisões adiadas (atreladas à fase)

| Tema | Tendência | Decidir em |
|---|---|---|
| Lib de PDF (Browsershot vs dompdf) | Browsershot, se o CSS dos templates exigir flex/grid | `/plan-phase 2` |
| Versionamento de termos / consentimento LGPD | Checkbox no registro + versão dos termos | `/plan-phase 3` |
| Hospedagem do beta | Deve suportar Chromium **se** PDF=Browsershot | Fase 5 |
| Error tracking | Sentry | Fase 5 |
| Gateway de pagamento | PIX/Mercado Pago (foco Brasil) | Fase 6 |
