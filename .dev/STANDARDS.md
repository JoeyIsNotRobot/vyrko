# Padrões de Engenharia — Vyrko

> Estado operacional vivo do repo. Decisões de longo prazo vão pro vault (`/vault-save`); estado de trabalho fica aqui. Não duplicar.

## Stack

- **Backend:** PHP 8.3 · Laravel 13
- **Persistência:** MySQL 8.4 · Redis (cache/queue) · SQLite in-memory (testes)
- **IA:** Google Gemini (`gemini-2.5-flash`, via `AI_MODEL`) — provider selecionável por `AI_PROVIDER` (`gemini` | `fake`)
- **Auth social:** Laravel Socialite + `socialiteproviders/linkedin`
- **Frontend:** Blade + Vite + Tailwind CSS
- **Dev/infra:** Laravel Sail (Docker), Mailpit
- **Qualidade:** Pint (lint/format), PHPUnit (testes), Pail (logs)

## Arquitetura

MVC do Laravel + **camada de Services** para regra de negócio e integração de IA.

- **Controllers finos:** orquestram, não contêm regra de IA nem persistência complexa.
- **Services** (`app/Services/`): toda lógica de IA (`Ai/`) e import (`Import/`).
- **Portas & adapters na IA:** `AiClient` (interface) → `GeminiAiClient` (real) / `FakeAiClient` (testes). Trocar provider não toca no domínio.
- **Validação:** sempre em FormRequests (`app/Http/Requests/`).
- **Autorização:** Policies (`OwnedResourcePolicy`) — todo recurso é isolado por `user_id`.
- **Auditoria de IA:** toda chamada registra `AiRun` (tokens, custo, status) e consumo registra `UsageLog`.

## Convenções

- **Lint/format:** PSR-12, validado por `./vendor/bin/pint --test`. Nada entra fora do padrão.
- **i18n obrigatório:** todo texto visível ao usuário via `__('messages.*')` com chave em `lang/pt_BR` **e** `lang/en`. Proibido hardcode de texto de UI.
- **Nomenclatura:** entidades de inventário `Candidate*`; vaga `JobPost`; match `JobMatchReport`; currículo `ResumeVersion`. Services nomeados pela ação (`JobMatchAnalyzer`, `ResumeGenerator`).
- **Estrutura de pastas:** seguir a já existente — não criar camadas novas sem registrar decisão.
- **Migrations:** toda entidade nova tem migration + factory. Deletes em cascata por `user_id`.

## Testes

- **Framework:** PHPUnit. Feature tests com SQLite in-memory e `AI_PROVIDER=fake`.
- **"Testado" =** feature test cobrindo o fluxo ponta-a-ponta, com `FakeAiClient` retornando resposta **determinística** (não stub vazio).
- **Regra:** todo fluxo novo nasce com feature test. Bug corrigido nasce com teste que o reproduz.
- **Comandos:** `php artisan test` · `./vendor/bin/pint --test`.

## Definition of Done (toda tarefa/fase)

- [ ] `php artisan test` verde
- [ ] `./vendor/bin/pint --test` limpo
- [ ] Texto de UI em PT-BR **e** EN
- [ ] **Regra de ouro do produto:** nenhuma informação inventada — todo dado gerado é rastreável a uma evidência do inventário
- [ ] Entidade nova → migration + factory + teste
- [ ] CI verde (a partir da Fase 1)
- [ ] Arquivos sensíveis (`.env`, secrets) intocados
- [ ] STATE.md atualizado ao pausar/fechar a fase
