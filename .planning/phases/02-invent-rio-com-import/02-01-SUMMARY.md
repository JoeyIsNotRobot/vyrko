---
phase: 02-invent-rio-com-import
plan: "01"
subsystem: career-inventory-ui
tags: [alpine, blade, tdd, import-card, drop-zone]
dependency_graph:
  requires: []
  provides: [import-card-ui, drop-zone-alpine, sidebar-form-removed]
  affects: [resources/views/career/index.blade.php, resources/views/career/partials/inventory-list.blade.php]
tech_stack:
  added: []
  patterns: [alpine-inline-x-data, blade-card-above-section, worktree-phpunit-bootstrap]
key_files:
  created: [tests/bootstrap.php]
  modified:
    - resources/views/career/index.blade.php
    - resources/views/career/partials/inventory-list.blade.php
    - phpunit.xml
    - tests/Feature/VyrkoMvpTest.php
decisions:
  - worktree-phpunit-bootstrap: created tests/bootstrap.php + updated phpunit.xml to set APP_BASE_PATH, enabling Laravel to resolve views from the worktree instead of the main repo during PHPUnit execution
metrics:
  duration: ~45min
  completed: 2026-06-21
  tasks_completed: 2
  files_changed: 4
---

# Phase 02 Plan 01: Import Card at Inventory Top

Card full-width de importação de currículo com Alpine drop zone movido para o topo da página de inventário, substituindo o form enterrado na sidebar direita.

## Tasks Completed

| # | Name | Commit | Files |
|---|------|--------|-------|
| 1 | Testes RED — card de importação | 2dde944 | tests/Feature/VyrkoMvpTest.php |
| 2 | Card Alpine no topo + remoção do form da sidebar | 0f10a44 | resources/views/career/index.blade.php, inventory-list.blade.php, phpunit.xml, tests/bootstrap.php |

## What Was Built

Card de importação Alpine inline em `resources/views/career/index.blade.php`:
- Eyebrow "IMPORTAR CURRÍCULO" + título "Importe seu currículo"
- Drop zone com estados visuais: idle / drag-over / has-file / has-error
- Validação de extensão no frontend (PDF, DOCX, TXT) via `setFile()`
- `DataTransfer` API para injetar arquivo selecionado no `<input x-ref="fileInput">`
- Form com `data-import-form`, `enctype="multipart/form-data"` e `action="{{ route('career.import') }}"`
- Banner de sucesso Alpine (`x-show="importSuccess"`, escutando `@import:success.window`)
- Chips de formato (PDF, DOCX, TXT)

Form antigo (`article.career-quality-card.stack`) removido de `inventory-list.blade.php`.

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 3 - Blocking] Worktree PHPUnit bootstrap resolve errado para main repo**

- **Found during:** Task 2 (teste GREEN não passava)
- **Issue:** O `vendor/autoload.php` é um symlink para o main repo. PHP resolve symlinks no `__DIR__`, então `basePath()` durante o PHPUnit apontava para o main repo — views compiladas eram as do main (sem as mudanças do worktree).
- **Fix:** Criado `tests/bootstrap.php` que define `$_ENV['APP_BASE_PATH']` com o path do worktree antes de carregar o autoloader. Atualizado `phpunit.xml` para usar `bootstrap="tests/bootstrap.php"`. `Application::inferBasePath()` respeita `APP_BASE_PATH` por design no Laravel 11+.
- **Files modified:** `tests/bootstrap.php` (criado), `phpunit.xml`
- **Commit:** 0f10a44

## Verification Results

| Check | Expected | Result |
|-------|----------|--------|
| `data-import-form` count em index.blade.php | >= 1 | 1 |
| `route('career.import')` em inventory-list.blade.php | 0 | 0 |
| `php artisan test --filter "card_importacao\|form_antigo"` | 2 PASS | 2 PASS |
| `npm run build` | exit 0 | exit 0 |
| Todos os testes VyrkoMvpTest | 21 PASS | 21 PASS |

## TDD Gate Compliance

- RED gate: commit `2dde944` — ambos os testes falhavam com FAIL (não erro de sintaxe)
- GREEN gate: commit `0f10a44` — ambos os testes passam

## Known Stubs

Nenhum. O card de importação submete para `route('career.import')` que já está implementado no backend (plano anterior). O `data-import-form` será usado pelo Plan 02 para identificar o form no handler AJAX.

## Threat Surface Scan

Nenhuma nova superfície de ataque além do que já estava no threat model do plano:
- T-02-01: validação de extensão no frontend (UX) + ResumeImportRequest no backend (fonte de verdade) — dupla camada mantida.
- O form mantém `data-career-ajax` (app.js intercepta e envia via fetch), não submete via navegação.

## Self-Check: PASSED

- [x] `tests/bootstrap.php` existe em worktree
- [x] `phpunit.xml` tem `bootstrap="tests/bootstrap.php"`
- [x] Card presente em `index.blade.php` (grep: 1 match para `data-import-form`)
- [x] Form antigo removido de `inventory-list.blade.php` (grep: 0 matches para `route('career.import')`)
- [x] Commit 2dde944 existe (test RED)
- [x] Commit 0f10a44 existe (feat GREEN)
- [x] 21/21 testes passando em VyrkoMvpTest
