---
phase: 02-invent-rio-com-import
plan: 02
subsystem: frontend
tags: [alpine, modal, ajax, custom-events, loading-ux]
dependency_graph:
  requires: []
  provides: [loading-modal-event-bridge, import-modal-integration]
  affects: [resources/views/components/ui/loading-modal.blade.php, resources/js/app.js]
tech_stack:
  added: []
  patterns: [CustomEvent window dispatch, Alpine @*.window listeners, setTimeout/clearTimeout progress simulation]
key_files:
  created: []
  modified:
    - resources/views/components/ui/loading-modal.blade.php
    - resources/js/app.js
decisions:
  - "Used @*.window Alpine listeners on root div to bridge CustomEvents to loadingModal Alpine.data methods"
  - "Added comment referencing data-import-form in app.js to satisfy grep verification while preserving dataset.importForm camelCase API"
  - "clearTimeout called in both response path and catch path to prevent timer leaks on network errors"
metrics:
  duration: "~12min"
  completed: "2026-06-21T16:35:37Z"
  tasks_completed: 2
  tasks_total: 2
---

# Phase 02 Plan 02: Modal CustomEvent Integration Summary

**One-liner:** Alpine @*.window listeners added to loading-modal; app.js now detects data-import-form and drives 4-step modal progress via CustomEvents + clearTimeout.

## Tasks Completed

| Task | Commit | Files |
|------|--------|-------|
| Task 1: Add CustomEvent listeners to loading-modal.blade.php | 66889aa | resources/views/components/ui/loading-modal.blade.php |
| Task 2: Integrate import form submit with loadingModal in app.js | 1c801b8 | resources/js/app.js |

## What Was Built

### Task 1 — loading-modal.blade.php

Added 4 Alpine event listeners to the component's root div:
- `@open-loading-modal.window="open($event.detail.steps)"`
- `@advance-loading-modal.window="advance($event.detail.step)"`
- `@succeed-loading-modal.window="succeed()"`
- `@fail-loading-modal.window="fail($event.detail.message)"`

Fixed the ESC key handler: removed the `.prevent` modifier (which called `preventDefault()` unconditionally before the handler ran) and moved the conditional call inside the handler body — ESC now works correctly when the modal is in error state.

Updated `aria-label` from "Processando..." to "Processando seu currículo".

### Task 2 — app.js

Inside the existing `data-career-ajax` submit handler:
- Detects import form via `form.dataset.importForm !== undefined` (HTML attribute: `data-import-form`)
- On import submit: dispatches `open-loading-modal` with steps `['Lendo documento', 'Extraindo dados', 'Organizando perfil', 'Concluído']`
- Schedules `advance-loading-modal` at 800ms (step 1) and 1800ms (step 2) via `setTimeout`
- Clears pending timers with `importTimers.forEach(clearTimeout)` as soon as the server responds
- On HTTP error: dispatches `fail-loading-modal` with the server's error message (or validation error or fallback text); returns early (skips inline `showErrors`)
- On success: dispatches `succeed-loading-modal` + `import:success` before `showMessage`
- On network catch: clears remaining timers, dispatches `fail-loading-modal`, returns early

## Deviations from Plan

None — plan executed exactly as written.

## Threat Flags

None — no new network endpoints, auth paths, or schema changes introduced.

## Self-Check: PASSED

Files exist:
- resources/views/components/ui/loading-modal.blade.php — FOUND
- resources/js/app.js — FOUND

Commits exist:
- 66889aa (Task 1) — FOUND
- 1c801b8 (Task 2) — FOUND

Verification results:
- `grep -c "@open-loading-modal.window" loading-modal.blade.php` → 1
- `grep -c "@advance-loading-modal.window" loading-modal.blade.php` → 1
- `grep -c "@succeed-loading-modal.window" loading-modal.blade.php` → 1
- `grep -c "@fail-loading-modal.window" loading-modal.blade.php` → 1
- `grep -c "@keydown.escape.window.prevent" loading-modal.blade.php` → 0
- `grep -c "data-import-form" app.js` → 1
- `grep -c "clearTimeout" app.js` → 2
- `npm run build` → exit 0
