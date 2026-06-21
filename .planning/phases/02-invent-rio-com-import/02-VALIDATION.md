---
phase: 2
slug: invent-rio-com-import
status: draft
nyquist_compliant: false
wave_0_complete: false
created: 2026-06-21
---

# Phase 2 — Validation Strategy

> Per-phase validation contract for feedback sampling during execution.

---

## Test Infrastructure

| Property | Value |
|----------|-------|
| **Framework** | PHPUnit (Laravel) |
| **Config file** | phpunit.xml |
| **Quick run command** | `php artisan test --filter=Import` |
| **Full suite command** | `php artisan test --no-coverage` |
| **Estimated runtime** | ~5 seconds |

---

## Sampling Rate

- **After every task commit:** Run `php artisan test --filter=Import`
- **After every plan wave:** Run `php artisan test --no-coverage`
- **Before `/gsd:verify-work`:** Full suite must be green
- **Max feedback latency:** 10 seconds

---

## Per-Task Verification Map

| Task ID | Plan | Wave | Requirement | Secure Behavior | Test Type | Automated Command | Status |
|---------|------|------|-------------|-----------------|-----------|-------------------|--------|
| 02-01-01 | 01 | 1 | INV-01 | Import card renders at top with data-import-form | feature | `php artisan test --filter=test_card_importacao_aparece_no_topo` | ⬜ pending |
| 02-01-02 | 01 | 1 | INV-01 | Old sidebar import form removed | feature | `php artisan test --filter=test_form_antigo_nao_aparece_na_sidebar` | ⬜ pending |
| 02-02-01 | 02 | 1 | INV-02 | Modal CustomEvent listeners wired | manual | Browser DevTools: open-loading-modal fires on submit | ⬜ pending |
| 02-02-02 | 02 | 1 | INV-02 | AJAX handler fires 4 modal steps + clearTimeout | manual | Browser: modal shows 4 steps, no dangling timers | ⬜ pending |
| 02-03-01 | 03 | 2 | INV-03 | AI prompt maps institution/issuer/proficiency | feature | `php artisan test --filter=test_importacao_mapeia_campos_campo_a_campo` | ⬜ pending |

*Status: ⬜ pending · ✅ green · ❌ red · ⚠️ flaky*

---

## Wave 0 Requirements

Existing test infrastructure covers all phase requirements. No new framework installation needed.
Tests are added to the existing `tests/Feature/VyrkoMvpTest.php` class — not new files.

- [ ] `test_card_importacao_aparece_no_topo` in `VyrkoMvpTest` — card renders in top, form has data-import-form + enctype
- [ ] `test_form_antigo_nao_aparece_na_sidebar` in `VyrkoMvpTest` — old sidebar import article removed
- [ ] `test_importacao_mapeia_campos_campo_a_campo` in `VyrkoMvpTest` — AI prompt fields map institution/issuer/proficiency

---

## Manual-Only Verifications

| Behavior | Requirement | Why Manual | Test Instructions |
|----------|-------------|------------|-------------------|
| Loading modal shows correct step sequence | INV-02 | Alpine.js UI state — not testable via PHPUnit | Upload file, verify modal shows "Lendo documento → Extraindo dados → Organizando perfil → Concluído" in order |
| Imported fields populate inventory form | INV-03 | Requires real Gemini API call | Upload real PDF, verify fields are populated with document content (not generic values) |
| Badge "via IA" appears on imported items | INV-01 | Session-scoped UI — no PHPUnit hook | After import, verify badge visible on imported sections |

---

## Validation Sign-Off

- [ ] All tasks have `<automated>` verify or Wave 0 dependencies
- [ ] Sampling continuity: no 3 consecutive tasks without automated verify
- [ ] Wave 0 covers all MISSING references
- [ ] No watch-mode flags
- [ ] Feedback latency < 10s
- [ ] `nyquist_compliant: true` set in frontmatter

**Approval:** pending
