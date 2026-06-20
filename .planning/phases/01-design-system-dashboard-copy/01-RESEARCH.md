# Phase 1: Design System + Dashboard + Copy — Research

**Researched:** 2026-06-20
**Domain:** CSS custom properties migration, Blade component authoring, Alpine.js modal, dashboard controller, copy strings
**Confidence:** HIGH

---

<phase_requirements>
## Phase Requirements

| ID | Description | Research Support |
|----|-------------|------------------|
| DESIGN-01 | Sistema de cores atualizado aplicado globalmente — azul #2563EB, positivo #10B981, warning #F59E0B | CSS var rename in `:root` + @theme block; all 56 `var(--vscode-*)` consumers auto-update |
| DESIGN-02 | Footer fixo no rodapé via layout wrapper (min-h-screen flex flex-col / main flex-1) | `app.blade.php` L15/L71/L90 — `.app-shell` needs `flex flex-col`, `<main>` needs `flex-1` |
| DESIGN-03 | Componente de modal de loading universal reutilizável com Alpine.js | Alpine.js NOT yet installed — must add to package.json; new Blade component `components/ui/loading-modal.blade.php` |
| DESIGN-04 | Espaçamento e tipografia padronizados — gap-6, mb-1 entre label e valor | Audit existing cards; `.metric-label` mb gap is implicit via CSS grid, not mb-1 — needs review |
| DASH-01 | Dashboard exibe exatamente 3 cards: Vagas analisadas, Currículos gerados, Completude do inventário | Dashboard has 4 cards (Currículos, Vagas, Melhor match, Gaps). DashboardController missing `$completeness`. Both must change |
| DASH-02 | Card de completude exibe CTA orientador quando completude < 80% | Requires new `$completeness` integer variable in DashboardController + conditional CTA in metric-card component (or in-view logic) |
| COPY-01 | Landing headline: "Seu currículo certo, para a vaga certa." | welcome.blade.php L21 — current headline is different; single line change in PT-BR branch |
| COPY-02 | Textos revisados: Inventário, Templates, Conta, empty states | lang/pt_BR/messages.php `career.title` needs update; dashboard/index.blade.php heading/subtitle; account/index.blade.php title |
| COPY-03 | Campos de área e função com autocomplete datalist, não dropdowns fixos | `professional_area` is already a free-text `<input>` in profile-edit — no dropdown to replace there. Need to audit onboarding views for any `<select>` with fixed area options |
</phase_requirements>

---

## Summary

The project is a Laravel 11 + Blade app with a hand-rolled CSS design system using `--vscode-*` CSS custom properties throughout. The migration is a targeted rename: 11 `--vscode-*` vars declared in `:root` (lines 92–107 of app.css) are consumed by 56 `var(--vscode-*)` references inside app.css itself, plus 7 references scattered across 2 Blade view files (career/index.blade.php inline styles). Renaming the root vars automatically cascades to all consumers.

The Tailwind v4 `@theme` block (lines 46–61) duplicates the color declarations as `--color-vscode-*` for Tailwind utility class access, and the `@layer components` block uses those as Tailwind tokens in `@apply` rules. Both locations need updates. The `tag-chip`, `query-block`, `tags-input` CSS in the LinkedIn section also consumes these vars.

Alpine.js is referenced in the STATE.md as the target for the loading modal but is NOT installed — `package.json` has no Alpine dependency and zero `x-data` usage exists in any Blade view. Installation is a hard prerequisite for DESIGN-03.

The dashboard currently passes 4 variables: `$resumeCount`, `$jobCount`, `$bestReport`, `$missingInventory`. The new 3-card spec requires a `$completeness` integer (0–100) computed from `$missingInventory`. `DashboardController` must be updated to compute and pass it. No existing `completeness` computation exists anywhere in the app.

---

## Architectural Responsibility Map

| Capability | Primary Tier | Secondary Tier | Rationale |
|------------|-------------|----------------|-----------|
| CSS variable migration (DESIGN-01) | CSS (app.css) | Tailwind @theme block | All consumers cascade from `:root` vars; @theme block adds Tailwind token aliases |
| Footer layout (DESIGN-02) | Layout Blade (app.blade.php) | app.css (.app-shell) | Structure change in HTML + add `flex-1` to `<main>` CSS class |
| Loading modal (DESIGN-03) | Blade component + Alpine.js | app.js (if needed) | Modal state is purely client-side; Alpine `x-data` on component handles all state |
| Spacing/typography (DESIGN-04) | app.css | Individual views | `.metric-card` padding, `.metric-label` margin are CSS-layer; `gap-6` is in Blade grids |
| Dashboard 3 cards (DASH-01) | DashboardController (PHP) | dashboard/index.blade.php | Data shape change in controller; view removes 2 cards, adds completeness card |
| Completeness CTA (DASH-02) | dashboard/index.blade.php | DashboardController | Conditional rendering in Blade after controller passes `$completeness` integer |
| Copy strings PT-BR (COPY-01,02) | lang/pt_BR/messages.php | Individual Blade views | Translation file for i18n strings; hardcoded strings changed directly in views |
| Autocomplete fields (COPY-03) | Blade view (profile-edit, onboarding) | — | HTML-only: `<input list="...">` + `<datalist>` — no JS needed |

---

## Standard Stack

### Core (already installed — no new packages needed except Alpine)

| Library | Version | Purpose | Status |
|---------|---------|---------|--------|
| TailwindCSS | v4.x (via `@tailwindcss/vite`) | Utility classes | Installed — uses v4 `@import 'tailwindcss'` syntax, NOT v3 config file |
| Laravel Blade | Built-in | Templating, x-components | Installed |
| Vanilla JS | — | Menu toggle, form loading, AJAX | Already in app.js |
| Alpine.js | NOT INSTALLED | Loading modal reactive state | Must install — see below |

### Alpine.js Installation (required for DESIGN-03)

[ASSUMED] — Alpine.js npm package is `alpinejs`. Verify before installing.

```bash
npm install alpinejs
```

Then in `resources/js/app.js`, add at top:
```js
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();
```

**Note:** The existing app.js uses vanilla JS event delegation — Alpine runs independently alongside it. No conflict risk.

### Tailwind v4 Note

[VERIFIED: vite.config.js] The project uses `@tailwindcss/vite` plugin (v4 integration). There is **no `tailwind.config.js`** — v4 uses `@theme` block inside CSS instead. The existing `@theme` block declares `--color-vscode-*` tokens. These must be updated alongside the `:root` vars.

---

## Package Legitimacy Audit

| Package | Registry | Disposition |
|---------|----------|-------------|
| alpinejs | npm | Pending slopcheck — well-known project (alpinejs/alpine on GitHub) [ASSUMED] |

*slopcheck not run in this session — alpine is a widely-known, established library but tagged [ASSUMED] per protocol. Planner should add a checkpoint before install if strict mode is desired.*

---

## Architecture Patterns

### CSS Variable Migration Pattern

The project has TWO declaration locations and ONE usage location:

**Location 1 — Tailwind @theme block (lines 45–61, app.css):** Used by `@apply` rules in `@layer components`. These use Tailwind's token syntax: `bg-vscode-blue`, `border-vscode-border`, etc.

**Location 2 — `:root` block (lines 89–109, app.css):** Used by `var(--vscode-*)` in all CSS rules below. These are the runtime CSS custom properties consumed by 56 references in app.css and 2 inline `<style>` blocks in Blade views.

**Migration approach (safe — no breakage):**
1. Add new `--color-primary`, `--bg-deep`, etc. vars in `:root` with new hex values
2. Add new token aliases in `@theme` (e.g., `--color-primary: #2563EB`)
3. Update all `var(--vscode-*)` references in app.css to `var(--color-primary)` etc.
4. Update `@apply` rules in `@layer components` to new token names
5. Update the 2 inline styles in `career/index.blade.php`
6. Remove old `--vscode-*` declarations after full migration

**UI-SPEC.md strategy:** Maintain `--vscode-*` as aliases during transition (keep old vars pointing to new values). This is optional — the codebase is small enough for a clean cut.

### .app-shell Footer Fix Pattern (DESIGN-02)

**Current state (app.blade.php L15, L71, L90):**
```html
<div class="app-shell">          <!-- currently: min-height: 100vh; padding-top: 76px -->
    <header class="topbar">...</header>
    <main class="wrap page">...</main>   <!-- no flex-1 -->
    <footer class="wrap footer-links">...</footer>
</div>
```

**Required change:**
- In `app.css`, update `.app-shell` to: `min-height: 100vh; padding-top: 76px; display: flex; flex-direction: column;`
- Add class `flex-1` to `<main>` in `app.blade.php` (or add it via CSS on `.page`)
- Footer already exists with `.footer-links` — no structural change needed

**Risk:** `.app-shell` currently has `min-height: 100vh` in TWO CSS rules (lines 66 and 186). The second (line 186, `:root` section) adds `padding-top: 76px`. Both must be merged/updated consistently.

### Dashboard 3-Card Refactor (DASH-01, DASH-02)

**Controller change required:** `DashboardController::__invoke` must compute `$completeness`:

```php
// Add after $missingInventory computation:
$totalSections = 5; // profile, experiences, skills, achievements, languages
$completeSections = $totalSections - $missingInventory->count();
$completeness = (int) round(($completeSections / $totalSections) * 100);
```

**View change:** `dashboard/index.blade.php` lines 28–33:
- Remove current 4-card `<section class="summary-grid">` with: Currículos gerados, Vagas analisadas, Melhor match, Gaps no inventário
- Replace with 3-card grid using `grid grid-cols-1 sm:grid-cols-3 gap-6`
- Cards: Vagas analisadas, Currículos gerados, Completude do inventário
- Third card: uses `<x-ui.metric-card>` with `$completeness`% value + conditional CTA slot

**metric-card component limitation:** Current `<x-ui.metric-card>` does not support a CTA slot — it only renders `$label`, `$value`, `$suffix`, `$meta`, `$tone`. The completeness card needs a CTA link below the meta. Options:
- A: Extend `metric-card.blade.php` with an optional `$cta` prop
- B: Inline the completeness card in `dashboard/index.blade.php` without using `<x-ui.metric-card>`
- Recommendation: Option B for Phase 1 — avoids changing a shared component used across 6+ views

### Loading Modal Alpine Pattern (DESIGN-03)

Alpine.js modal with ESC/click-outside blocked during operation. Since Alpine is not yet installed, this is a net-new component.

**Component:** `resources/views/components/ui/loading-modal.blade.php`

Pattern — Alpine component with `x-data` on the outer div, exposed via `$dispatch` or `window.loadingModal`:

```html
<div x-data="loadingModal()" x-show="show"
     role="dialog" aria-modal="true" aria-label="Processando..."
     class="fixed inset-0 z-50 backdrop-blur-md bg-black/60 flex items-center justify-center"
     @keydown.escape.window.prevent="if(show && !error) $event.preventDefault()">
```

The `loadingModal()` function is defined in app.js as `Alpine.data('loadingModal', () => ({...}))`.

**Alternative (simpler for Phase 1):** Since no Phase 1 task actually TRIGGERS the modal (imports and job analysis are Phase 2/3), Phase 1 only needs the component to EXIST and be visually complete. Steps and progress can be driven by test data. Full wiring to real operations happens in Phase 2.

### Autocomplete Pattern (COPY-03)

`professional_area` in `career/profile-edit.blade.php` is already a free-text `<input>`. No `<select>` to replace there. Need to check `onboarding/index.blade.php` and `onboarding/import.blade.php` for fixed dropdowns.

```html
<!-- Pattern to use wherever area/role fields exist -->
<input name="professional_area"
       list="area-suggestions"
       placeholder="Ex: Engenharia de Software, Finanças, Marketing...">
<datalist id="area-suggestions">
    <option value="Engenharia de Software">
    <option value="Produto">
    <option value="Design">
    <option value="Dados e Analytics">
    <option value="Marketing">
    <option value="Finanças">
    <option value="Vendas">
    <option value="DevOps / Infraestrutura">
    <option value="Gestão">
</datalist>
```

---

## Codebase Inventory — Files Touched Per Requirement

### DESIGN-01: Color migration
- `resources/css/app.css` — all changes (`:root` vars, `@theme` block, 56 `var()` consumers, LinkedIn section vars)
- `resources/views/career/index.blade.php` — 2 inline style refs (lines 253, 257: `var(--vscode-success)`, `var(--vscode-warning)`)
- `resources/views/resumes/print.blade.php` — line 9: `--vscode-border: #3e3e42` (inline :root override for print context)

### DESIGN-02: Footer layout
- `resources/css/app.css` — `.app-shell` (add flex/flex-col)
- `resources/views/layouts/app.blade.php` — add `flex-1` to `<main class="wrap page">`

### DESIGN-03: Loading modal component
- `resources/js/app.js` — import Alpine, define `Alpine.data('loadingModal', ...)`
- `package.json` — add `alpinejs` dependency
- `resources/views/components/ui/loading-modal.blade.php` — CREATE NEW
- `resources/views/layouts/app.blade.php` — add `<x-ui.loading-modal />` before `@stack('scripts')`

### DESIGN-04: Spacing/typography audit
- `resources/css/app.css` — `.metric-card` padding (currently 17px, spec says 24px/p-6), `.metric-label` mb-1
- Cards across views use `.metric-card` CSS class — CSS change cascades automatically

### DASH-01 + DASH-02: Dashboard 3 cards
- `app/Http/Controllers/DashboardController.php` — add `$completeness` computation, remove `$latestReport`, `$bestReport` if unused
- `resources/views/dashboard/index.blade.php` — replace 4-card summary-grid with 3-card grid

### COPY-01: Landing headline
- `resources/views/welcome.blade.php` — line 21, PT-BR branch: change string

### COPY-02: Page/section copy
- `lang/pt_BR/messages.php` — update `career.title`, `career.subtitle`, `career.eyebrow`
- `resources/views/dashboard/index.blade.php` — heading/subtitle in `<x-ui.page-header>`
- `resources/views/account/index.blade.php` — page title
- `resources/views/dashboard/index.blade.php` — empty state strings (passed to `<x-ui.empty-state>`)
- `resources/views/welcome.blade.php` — sub-headline (line 22)
- `resources/views/resumes/templates.blade.php` — heading "Qual currículo enviar para esta vaga?"
- `resources/views/jobs/index.blade.php` — empty state strings
- `resources/views/resumes/index.blade.php` — empty state strings

### COPY-03: Autocomplete fields
- `resources/views/onboarding/index.blade.php` — audit for fixed `<select>` fields
- `resources/views/onboarding/import.blade.php` — audit for fixed `<select>` fields
- `resources/views/career/profile-edit.blade.php` — `professional_area` already a text input; add datalist

---

## Don't Hand-Roll

| Problem | Don't Build | Use Instead |
|---------|-------------|-------------|
| Reactive modal state | Custom event bus / DOM manipulation | Alpine.js `x-data` — purpose-built for this pattern |
| CSS color token migration | Manual find-replace of hex values | CSS custom property cascade — change root vars, all consumers update |
| Completeness % calculation | Complex inventory scoring service | Simple arithmetic on `$missingInventory->count()` in controller |
| i18n string updates | Hardcoded strings in views | Blade `__('messages.key')` — changes in one lang file propagate everywhere |

---

## Common Pitfalls

### Pitfall 1: Tailwind v4 @theme block vs :root
**What goes wrong:** Updating `:root` vars but forgetting the `@theme` block. `@apply bg-vscode-blue` in `@layer components` reads from `@theme`, not `:root`. Both must be updated.
**Why it happens:** Two separate declaration systems for the same logical tokens.
**How to avoid:** Update `@theme` first (controls `@apply` in components layer), then `:root` (controls `var()` in CSS rules).

### Pitfall 2: .app-shell has two CSS rules
**What goes wrong:** Updating `.app-shell` at line 64 but missing line 186 which overrides `min-height`. The flex layout breaks inconsistently.
**Why it happens:** `.app-shell` is defined twice — once in `@layer components` (Tailwind) and once in the hand-written CSS block.
**How to avoid:** Grep for `.app-shell` before editing; consolidate to one rule or ensure both are updated.

### Pitfall 3: summary-grid used in 10+ views
**What goes wrong:** Changing `.summary-grid` column count in CSS breaks ALL views that use it (linkedin, resumes/show, resumes/index, resumes/templates, career/profile-edit, jobs/show, jobs/index, jobs/create).
**Why it happens:** `.summary-grid` is a shared utility class hardcoded as 4-column grid.
**How to avoid:** Dashboard should NOT use `.summary-grid` for the 3-card layout. Use an inline grid class or a new `.dashboard-stats` class. Leave `.summary-grid` as-is.

### Pitfall 4: Alpine.js not installed — modal renders broken
**What goes wrong:** Creating `loading-modal.blade.php` with `x-data` before Alpine is installed. No errors, but the modal is a static div that can't be shown/hidden.
**Why it happens:** Alpine is a runtime JS dependency.
**How to avoid:** Install Alpine and verify `Alpine.start()` is called before writing the component. Check browser console for `Alpine is not defined`.

### Pitfall 5: metric-card component lacks CTA slot
**What goes wrong:** Trying to use `<x-ui.metric-card>` for the completeness card with a CTA. The component has no slot for this.
**How to avoid:** For the completeness card, write inline HTML in `dashboard/index.blade.php` that matches `.metric-card` CSS classes manually. Do not modify the component.

### Pitfall 6: DashboardController passes $bestReport and $latestReport — view removes them
**What goes wrong:** Removing cards from the view that reference `$bestReport` but keeping the controller passing it. Not a bug but leaves dead variables. Inversely, removing the variable from the controller while the view still references it causes a "Undefined variable" error.
**How to avoid:** Update controller and view in the same task. Remove `$bestReport`, `$latestReport` from controller only if the view no longer references them.

---

## Code Examples

### Alpine.js modal data (app.js)
```js
// Add after Alpine import
Alpine.data('loadingModal', () => ({
    show: false,
    progress: 0,
    currentStep: '',
    steps: [],
    error: null,
    open(steps) {
        this.steps = steps;
        this.currentStep = steps[0] ?? '';
        this.progress = 0;
        this.error = null;
        this.show = true;
    },
    advance(stepIndex) {
        this.currentStep = this.steps[stepIndex] ?? '';
        this.progress = Math.round(((stepIndex + 1) / this.steps.length) * 100);
    },
    succeed() {
        this.progress = 100;
        setTimeout(() => { this.show = false; }, 400);
    },
    fail(message) {
        this.error = message;
    }
}));
```

### Completeness computation (DashboardController)
```php
$totalSections = 5;
$completeness = (int) round((($totalSections - $missingInventory->count()) / $totalSections) * 100);
// Pass to view: 'completeness' => $completeness
```

### Dashboard 3-card grid (dashboard/index.blade.php)
```html
<section class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
    <x-ui.metric-card ... />
    <x-ui.metric-card ... />
    {{-- Completeness card — inline because needs CTA slot --}}
    <article class="metric-card {{ $completeness >= 80 ? 'metric-card-success' : 'metric-card-warning' }}">
        <p class="metric-label">Completude do inventário</p>
        <div class="metric-value">{{ $completeness }}<small>%</small></div>
        <p class="metric-meta">
            @if($completeness < 80)
                <a class="text-sm font-bold text-blue-400 hover:text-blue-300" href="{{ route('career.index') }}">Complete seu perfil →</a>
            @else
                <span class="text-emerald-400">Inventário completo ✓</span>
            @endif
        </p>
    </article>
</section>
```

---

## State of the Art

| Old Approach | Current Approach | Impact on This Phase |
|--------------|------------------|----------------------|
| Tailwind v3 config file | Tailwind v4 `@theme` block in CSS | No `tailwind.config.js` exists — color tokens are in app.css `@theme` block |
| Alpine.js via CDN `<script>` | Alpine.js via npm + Vite import | Must import via app.js, not add a script tag to layout |
| CSS var naming: `--vscode-*` | New naming: `--color-primary`, `--bg-deep`, etc. | Phase 1 migration task |

---

## Assumptions Log

| # | Claim | Section | Risk if Wrong |
|---|-------|---------|---------------|
| A1 | `alpinejs` is the correct npm package name for Alpine.js v3 | Standard Stack | Wrong package name would install wrong/malicious package |
| A2 | COPY-03 autocomplete is limited to `career/profile-edit.blade.php` area field + onboarding views | Codebase Inventory | If other views have fixed selects for area/function, they'd be missed |
| A3 | `$bestReport` and `$latestReport` can be removed from controller once dashboard removes those cards | Codebase Inventory | Other views might inject the dashboard and expect those vars |

---

## Open Questions (RESOLVED)

1. **Does COPY-03 affect onboarding views?**
   - What we know: `professional_area` is a text input in `career/profile-edit.blade.php`. Onboarding views (`onboarding/index.blade.php`, `onboarding/import.blade.php`) were not read.
   - What's unclear: whether onboarding has fixed `<select>` for area/function.
   - RESOLVED: onboarding/index.blade.php confirmed in scope in PLAN-03 Task 2; import.blade.php flagged for verification in same task.

2. **Alpine.js package name confirmation**
   - What we know: package is commonly known as `alpinejs` on npm.
   - What's unclear: not verified via npm registry in this session.
   - RESOLVED: alpinejs package confirmed; supply-chain check (T-01-SC) added to PLAN-01 threat model.

3. **`$bestReport` / `$latestReport` — still needed?**
   - What we know: Dashboard view uses `$latestJobs`, `$latestResumes`, `$resumeCount`, `$jobCount`, `$bestReport` (for "Melhor match" card), `$missingInventory`. After removing the match card, `$bestReport` is unused in the view.
   - Recommendation: Remove from controller pass unless another view or partial uses it. Safe to remove after grep confirms no other reference.
   - RESOLVED: $bestReport and $latestReport removed in PLAN-02 Task 2 steps 3-5.

---

## Environment Availability

| Dependency | Required By | Available | Version |
|------------|------------|-----------|---------|
| Node.js | Vite build / npm install | Yes | v24.14.0 |
| npm | Alpine.js install | Yes | 11.9.0 |
| PHP | Laravel / Artisan | Yes | 8.3.6 |
| Vite | Asset compilation | Yes (package.json) | ^8.0.0 |
| Alpine.js | DESIGN-03 loading modal | NO | Not installed |

**Missing dependencies:**
- `alpinejs` — blocks DESIGN-03 execution. Install as first task of Wave 1.

---

## Validation Architecture

> No dedicated test framework detected. Manual verification protocol applies.

### Per-task verification commands
```bash
# After CSS var migration:
php artisan serve &
# Open browser: localhost:8000 — check topbar/buttons are blue #2563EB not #007ACC

# After footer fix:
# Short page (dashboard empty state) — footer must hug bottom, not float mid-page

# After loading modal:
# Open dashboard → check no Alpine errors in console → dispatch window.loadingModal.open(['Step 1', 'Step 2']) from DevTools

# After dashboard 3 cards:
# Load /dashboard — confirm 3 cards visible, completeness % correct
```

### Manual checklist per requirement
| Req | Verification |
|-----|-------------|
| DESIGN-01 | Inspect `.btn` background in DevTools — should be `#2563EB` gradient, not `#007ACC` |
| DESIGN-02 | On a page with little content, footer must be at viewport bottom (not mid-page) |
| DESIGN-03 | `<x-ui.loading-modal>` renders without errors; Alpine state toggles correctly |
| DESIGN-04 | `.metric-card` has 24px padding (p-6); label and value gap is ≤ 4px |
| DASH-01 | Exactly 3 stat cards on `/dashboard` |
| DASH-02 | When completeness < 80%: amber border + "Complete seu perfil →" link visible |
| COPY-01 | `/` (welcome) shows "Seu currículo certo, para a vaga certa." as h1 |
| COPY-02 | `/career` title is "Seu inventário de carreira"; account page title "Sua conta" |
| COPY-03 | Typing in area field shows datalist suggestions; arbitrary text is accepted |

---

## Sources

### Primary (HIGH confidence — direct codebase inspection)
- `resources/css/app.css` — full read; 56 `var(--vscode-*)` consumers confirmed
- `resources/views/layouts/app.blade.php` — full read; `.app-shell` structure confirmed
- `resources/views/dashboard/index.blade.php` — full read; 4-card grid confirmed
- `app/Http/Controllers/DashboardController.php` — full read; variable inventory confirmed
- `resources/views/components/ui/metric-card.blade.php` — full read; no CTA slot confirmed
- `package.json` — full read; Alpine.js absence confirmed
- `resources/js/app.js` — full read; vanilla JS, no Alpine import
- `lang/pt_BR/messages.php` — full read; all current copy strings confirmed
- `vite.config.js` — Tailwind v4 via `@tailwindcss/vite` confirmed
- `resources/views/career/profile-edit.blade.php` — `professional_area` is already text input (not select)

### Secondary
- `01-UI-SPEC.md` — design contract with all specs (colors, component shapes, copy strings)
- `REQUIREMENTS.md` — requirement definitions

---

## Metadata

**Confidence breakdown:**
- CSS migration scope: HIGH — all 56 consumers identified, file and line ranges known
- Dashboard refactor: HIGH — controller and view fully read, data flow clear
- Alpine.js loading modal: MEDIUM — pattern is standard but Alpine not yet installed; integration untested
- Copy changes: HIGH — all target strings and their file locations confirmed
- COPY-03 (autocomplete): MEDIUM — `career/profile-edit` confirmed; onboarding views not read

**Research date:** 2026-06-20
**Valid until:** 2026-07-20 (stable codebase — no external API volatility)
