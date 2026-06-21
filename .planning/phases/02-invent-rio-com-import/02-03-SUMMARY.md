---
phase: 02-invent-rio-com-import
plan: "03"
subsystem: ai-prompt-and-badge
tags: [gemini, prompt-engineering, badge, tdd, worktree-classmap]
dependency_graph:
  requires: [02-01]
  provides: [typed-ai-schemas, via-ia-badge, worktree-psr4-fix]
  affects:
    - app/Services/Ai/GeminiAiClient.php
    - app/Services/Ai/FakeAiClient.php
    - app/Http/Controllers/ResumeImportController.php
    - resources/views/career/partials/inventory-item.blade.php
    - resources/views/career/index.blade.php
    - tests/bootstrap.php
    - tests/Feature/VyrkoMvpTest.php
tech_stack:
  added: []
  patterns: [typed-json-schema-prompt, flash-badge, composer-classmap-override]
key_files:
  created: []
  modified:
    - app/Services/Ai/GeminiAiClient.php
    - app/Services/Ai/FakeAiClient.php
    - app/Http/Controllers/ResumeImportController.php
    - resources/views/career/partials/inventory-item.blade.php
    - resources/views/career/index.blade.php
    - tests/bootstrap.php
    - tests/Feature/VyrkoMvpTest.php
decisions:
  - typed-sub-schemas: replaced 4 vague array schemas with typed object arrays in resumeImportParsePrompt so Gemini receives explicit field names and types
  - flash-boolean-badge: used session()->flash('just_imported', true) instead of per-ID tracking — simpler, server-controlled, expires after 1 request
  - worktree-classmap-override: used Composer ClassLoader::addClassMap() to prepend worktree app/ files over main-repo classmap entries so PHPUnit loads worktree PHP classes correctly
metrics:
  duration: ~35min
  completed: 2026-06-21
  tasks_completed: 2
  files_changed: 7
---

# Phase 02 Plan 03: Typed AI Sub-schemas and Via-IA Badge

Sub-schemas tipados para os 4 arrays vagos do prompt Gemini (educations, certifications, languages, projects) e badge "VIA IA" em itens importados via flash session.

## Tasks Completed

| # | Name | Commit | Files |
|---|------|--------|-------|
| 1 | Teste RED — campo-a-campo mapping | 253a4c6 | tests/Feature/VyrkoMvpTest.php |
| 2 | Sub-schemas tipados + badge via IA + fix worktree classmap | f2666eb | GeminiAiClient.php, FakeAiClient.php, ResumeImportController.php, inventory-item.blade.php, index.blade.php, tests/bootstrap.php |

## What Was Built

**GeminiAiClient — sub-schemas tipados:**

`resumeImportParsePrompt()` substituiu os 4 arrays vagos por schemas de objeto tipados:
- `educations`: `institution`, `degree`, `field_of_study`, `start_date`, `end_date`
- `certifications`: `name`, `issuer`, `issued_at`, `expires_at`, `credential_url`
- `languages`: `language`, `proficiency` (enum: Nativo|Fluente|Avançado|Intermediário|Básico), `notes`
- `projects`: `name`, `description`, `url`, `start_date`, `end_date`, `technologies`

Instrução anti-inferência atualizada com 4 regras explícitas numeradas.

**Badge "VIA IA":**
- `ResumeImportController`: `session()->flash('just_imported', true)` após import bem-sucedido
- `inventory-item.blade.php`: `<span class="import-badge" aria-label="...">VIA IA</span>` condicionado em `session('just_imported')`
- `career/index.blade.php`: CSS `.import-badge` (11px, weight 700, azul, pill)

**FakeAiClient — parsedCertifications():**
Novo método parseia seção CERTIFICAÇÕES do texto TXT (formato `nome|emissor|data|`) para suportar o teste de mapeamento campo-a-campo.

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 3 - Blocking] Composer classmap override no worktree PHPUnit**

- **Found during:** Task 2 (GREEN não passava)
- **Issue:** O `tests/bootstrap.php` anterior usava `addPsr4()` e `setPsr4()` para redirecionar `App\` para o worktree, mas o Composer usa classmap com prioridade sobre PSR-4. A classe `FakeAiClient` estava listada no `vendor/composer/autoload_classmap.php` com o path do main repo, então PSR-4 overrides não tinham efeito.
- **Fix:** Trocado para `Composer\Autoload\ClassLoader::addClassMap()` que itera recursivamente o `app/` do worktree e injeta os paths do worktree no classmap. `addClassMap` usa `array_merge`, então entradas adicionadas por último sobrepõem entradas anteriores.
- **Files modified:** `tests/bootstrap.php`
- **Commit:** f2666eb

## Verification Results

| Check | Expected | Result |
|-------|----------|--------|
| `grep -c "institution" GeminiAiClient.php` | >= 1 | 1 |
| `grep -c "issuer" GeminiAiClient.php` | >= 1 | 1 |
| `grep -c "proficiency" GeminiAiClient.php` | >= 1 | 2 |
| `grep -c "import-badge\|VIA IA" inventory-item.blade.php` | >= 1 | 1 |
| `php artisan test --filter=test_importacao_mapeia_campos_campo_a_campo` | PASS | PASS |
| Suite completa worktree (41 testes) | 41 PASS | 41 PASS |
| Suite main repo (40 testes, sem novo teste) | 40 PASS | 40 PASS |
| Regressão Wave 1 (`card_importacao`, `form_antigo`) | 2 PASS | 2 PASS |

## TDD Gate Compliance

- RED gate: commit `253a4c6` — `test_importacao_mapeia_campos_campo_a_campo` falhava com "The table is empty" (candidate_certifications)
- GREEN gate: commit `f2666eb` — teste passa com 5 assertions

## Known Stubs

Nenhum.

## Threat Surface Scan

Nenhuma nova superfície além do threat model do plano:
- T-02-07: sub-schemas tipados forçam campos específicos no Gemini — aceitável, service já descarta campos desconhecidos
- T-02-08: badge via flash session server-side — sem vetor de injeção

## Self-Check: PASSED

- [x] `institution` presente em GeminiAiClient.php
- [x] `issuer` presente em GeminiAiClient.php
- [x] `proficiency` presente em GeminiAiClient.php
- [x] `VIA IA` e `import-badge` presentes em inventory-item.blade.php
- [x] Commit 253a4c6 existe (RED)
- [x] Commit f2666eb existe (GREEN)
- [x] 41/41 testes passando no worktree
