---
phase: 01-design-system-dashboard-copy
reviewed: 2026-06-21T00:00:00Z
depth: standard
files_reviewed: 16
files_reviewed_list:
  - app/Http/Controllers/DashboardController.php
  - lang/pt_BR/messages.php
  - resources/css/app.css
  - resources/js/app.js
  - resources/views/account/index.blade.php
  - resources/views/career/index.blade.php
  - resources/views/career/profile-edit.blade.php
  - resources/views/components/ui/loading-modal.blade.php
  - resources/views/dashboard/index.blade.php
  - resources/views/jobs/index.blade.php
  - resources/views/layouts/app.blade.php
  - resources/views/onboarding/index.blade.php
  - resources/views/resumes/index.blade.php
  - resources/views/resumes/print.blade.php
  - resources/views/resumes/templates.blade.php
  - resources/views/welcome.blade.php
findings:
  critical: 3
  warning: 5
  info: 3
  total: 11
status: issues_found
---

# Phase 01: Code Review Report

**Reviewed:** 2026-06-21
**Depth:** standard
**Files Reviewed:** 16
**Status:** issues_found

## Summary

Reviewed the design system, dashboard, and copy layer: one PHP controller, one language file, the global CSS, the global JS bundle, and twelve Blade views. The CSS and translation file are clean. The PHP controller is correct and minimal. Issues concentrate in `resources/js/app.js` (XSS via `innerHTML` and a missing JSON-parse guard) and in several Blade views (unguarded avatar URL in an `<img src>`, clipboard API with no error boundary, and a modal escape-key handler with reversed logic).

---

## Critical Issues

### CR-01: Server error messages injected into DOM via innerHTML without sanitization

**File:** `resources/js/app.js:92-93`

**Issue:** `showErrors` interpolates server-controlled strings directly into `innerHTML`. When the server returns a `payload.errors` array or `payload.message` string, each item is dropped into a template literal as `<li>${item}</li>` and written with `box.innerHTML`. If the server (or a compromised intermediary) returns a string containing HTML, it executes in the user's browser. The same `showErrors` path is also called at line 161 with `error.message` from a caught `fetch` exception — in practice a network error message is not attacker-controlled, but the `payload.errors` path definitely is.

Additionally, `config.reviewDataText` is sourced from `window.Vyrko.reviewDataText`, which is set from `__('messages.common.review_data')`. If the translation value ever contains HTML it would also execute because it is interpolated directly into the `innerHTML` string at line 93.

**Fix:**
```javascript
const showErrors = (errors) => {
    const box = errorBox();
    if (!box) return;

    const list = Array.isArray(errors) ? errors : Object.values(errors || {}).flat();

    // Build DOM nodes — never use innerHTML for server content
    box.replaceChildren();
    const heading = document.createElement('strong');
    heading.textContent = config.reviewDataText || 'Revise os dados:';
    const ul = document.createElement('ul');
    list.forEach((item) => {
        const li = document.createElement('li');
        li.textContent = item;
        ul.appendChild(li);
    });
    box.appendChild(heading);
    box.appendChild(ul);
    box.hidden = false;
    box.scrollIntoView({ behavior: 'smooth', block: 'center' });
};
```

---

### CR-02: Unvalidated avatar URL written into img src attribute

**File:** `resources/views/account/index.blade.php:126`

**Issue:** `$account->avatar_url` is stored during OAuth and written directly into an `<img src>` tag with `{{ $account->avatar_url }}` (Blade HTML-encodes for attribute context, which prevents HTML injection). However the value could be a `javascript:` URI or a `data:` URI sourced from the OAuth provider payload or a database row modified after the fact. A `javascript:` URL in `src` does not execute in modern browsers, but `<img src="javascript:...">` could execute in old WebKit/IE contexts. More concretely, an attacker with database write access (SSRF, etc.) could set a value pointing to an internal IP, causing the user's browser to make requests to internal infrastructure when the account page loads.

**Fix:** Validate that the URL is absolute HTTP/S at storage time in the OAuth handler. As a defence-in-depth measure in the view, guard with a starts-with check:
```blade
@if ($account->avatar_url && str_starts_with($account->avatar_url, 'https://'))
    <img src="{{ $account->avatar_url }}" alt="" loading="lazy">
@endif
```

---

### CR-03: response.json() called unconditionally — crashes on non-JSON error responses

**File:** `resources/js/app.js:142-145`

**Issue:** `response.json()` is called before checking `response.ok`. If the server returns a 500 or 419 (CSRF expired) with an HTML body, `response.json()` throws a `SyntaxError`. That exception is caught by the outer `catch` block at line 160, which calls `showErrors([error.message])` — displaying a raw JS exception message to the user ("Unexpected token '<'") rather than a meaningful error. More importantly, the form stays in the `is-loading` state if `json()` throws before the `finally` block runs — actually the `finally` does run, so the spinner resets, but the form body never gets restored if `originalText` was not set before the throw path. The real bug is that any non-JSON 4xx/5xx response (including CSRF expiry redirect) produces a confusing user-facing error.

**Fix:**
```javascript
const text = await response.text();
let payload;
try {
    payload = JSON.parse(text);
} catch {
    payload = { message: response.status === 419
        ? 'Sessão expirada. Recarregue a página.'
        : `Erro do servidor (${response.status}).` };
}

if (!response.ok) {
    showErrors(payload.errors || [payload.message || 'Erro']);
    return;
}
```

---

## Warnings

### WR-01: Clipboard write has no error handling — silently fails on HTTP or denied permission

**File:** `resources/views/resumes/templates.blade.php:51`

**Issue:** `navigator.clipboard.writeText(...)` is called inline via `onclick`. The Clipboard API requires a secure context (HTTPS) and explicit permission. On HTTP origins or when the user denies permission, `writeText` returns a rejected Promise that is never caught. The user sees nothing — no error, no fallback. In staging/dev environments running on HTTP this button is silently broken.

**Fix:**
```html
<button class="btn secondary" type="button"
    onclick="navigator.clipboard.writeText(document.getElementById('resumePlainText').value)
        .catch(() => alert('Não foi possível copiar. Selecione o texto manualmente.'))">
    {{ $en ? 'Copy text' : 'Copiar texto' }}
</button>
```
Or, better, extract to a named function in `app.js` that also provides a `<textarea select + execCommand` fallback for insecure contexts.

---

### WR-02: Loading modal Escape-key handler logic is inverted

**File:** `resources/views/components/ui/loading-modal.blade.php:10`

**Issue:** The AlpineJS handler is:
```
@keydown.escape.window.prevent="if (show && !error) $event.preventDefault()"
```
The `.prevent` modifier already calls `preventDefault()` unconditionally before the expression runs. The inline `$event.preventDefault()` inside the condition is therefore a no-op (prevent is already called). The intent appears to be: "block Escape while loading, but allow it (dismiss) when an error is shown." The current code prevents Escape in all cases. When `error` is set, the user cannot close the modal with Escape despite the dialog being in an error state where closing is expected.

**Fix:** Remove the `.prevent` Alpine modifier and handle it exclusively in the expression:
```html
@keydown.escape.window="if (show && !error) $event.preventDefault(); else if (show && error) { error = null; progress = 0; show = false; }"
```

---

### WR-03: data-loading forms have no double-submit guard

**File:** `resources/js/app.js:64-73`

**Issue:** The `submit` event listener adds `is-loading` to the form and replaces button text, but does not prevent the form from submitting a second time if the user clicks again before the page unloads (e.g., on a slow connection). The `is-loading` class sets `pointer-events: none` on the form via CSS, which works for mouse clicks. However keyboard users (Enter on a focused button) and forms that receive submit events from outside the button can still re-trigger. The form is never explicitly disabled or the event not prevented on the second fire.

This affects all `form[data-loading]` elements throughout the app (account profile, email, password update forms).

**Fix:**
```javascript
document.addEventListener('submit', (event) => {
    const form = event.target.closest('form[data-loading]');
    if (!form) return;
    if (form.classList.contains('is-loading')) {
        event.preventDefault();  // block second submission
        return;
    }
    form.classList.add('is-loading');
    // ...rest of handler
});
```

---

### WR-04: DashboardController fires 7 separate queries with no eager-loading

**File:** `app/Http/Controllers/DashboardController.php:13-33`

**Issue:** The controller issues five individual `count()` queries (lines 16-19, 26-27) plus two additional queries for `latestResume` and `latestResumes`, and a further `with('jobPost')` subquery — seven total roundtrips in a single page load, all operating on the same authenticated user. The `$user->candidateProfile` access at line 13 is an additional implicit query if not already loaded.

While performance is out of v1 scope, the correctness issue is that `resumeCount` (line 26) and `latestResumes` (line 32) run two separate queries against `resumeVersions()` on the same base query builder. If a resume is inserted between these two calls (race condition), `resumeCount` and `count($latestResumes)` can disagree. For a dashboard this is cosmetic, but for any future billing/limit check derived from count queries this pattern is unsafe.

**Fix:** Replace bare `count()` calls with loaded collection counts where the data is already being fetched:
```php
$latestResumes = $user->resumeVersions()->with('jobPost')->latest()->limit(5)->get();
$resumeCount   = $user->resumeVersions()->count(); // still separate, but document the race
```
At minimum, load the profile relationship eagerly:
```php
$user->load('candidateProfile');
```

---

### WR-05: career/index.blade.php calls activate() twice on initial load

**File:** `resources/views/career/index.blade.php:385-386`

**Issue:** The script registers a `DOMContentLoaded` listener at line 385 and then immediately calls `activate(activeSection)` at line 386 outside any listener. Because the script is in a `@push('scripts')` block placed before `</body>`, the DOM is already parsed when the script runs, so `DOMContentLoaded` may or may not have fired depending on the browser and script injection order. In practice the script tag is injected after the DOM, so `DOMContentLoaded` fires synchronously upon `addEventListener`, causing `activate` to run twice on every page load. This doubles DOM queries and class mutations on initialisation.

**Fix:** Remove the bare `activate(activeSection)` at line 386 and keep only the `DOMContentLoaded` listener, or replace the listener with a direct call:
```javascript
document.addEventListener('DOMContentLoaded', () => activate(activeSection));
// Remove line 386: activate(activeSection);
```

---

## Info

### IN-01: avatar img has empty alt attribute — screen readers skip without description

**File:** `resources/views/account/index.blade.php:126`

**Issue:** `<img src="..." alt="" loading="lazy">` — an empty `alt` is correct for decorative images, but this is a user profile photo which conveys the connected account's identity. Screen readers will skip it entirely, leaving blind users with no indication that a photo is shown.

**Fix:**
```blade
<img src="{{ $account->avatar_url }}" alt="{{ $account->name ?: $user->name }}" loading="lazy">
```

---

### IN-02: Inline `onclick` with direct `window.print()` call skips any print preparation

**File:** `resources/views/resumes/print.blade.php:78`

**Issue:** `onclick="window.print()"` triggers the print dialog immediately. If any assets (fonts, partial templates) are still loading when the button is clicked shortly after page render, the PDF may capture an incomplete layout. There is no `window.onload` guard or `document.fonts.ready` wait.

**Fix:** Wrap in a `document.fonts.ready` promise:
```html
<button class="btn" type="button"
    onclick="document.fonts.ready.then(() => window.print())">
    {{ $en ? 'Download / Save PDF' : 'Baixar / Salvar PDF' }}
</button>
```

---

### IN-03: `data-locale-select` navigates to select.value without validating the URL

**File:** `resources/js/app.js:42-46`

**Issue:** The change handler reads `select.value` directly and assigns it to `window.location.href`. The select values are rendered by the server via `route('locale.switch', ...)`, so in practice the values are safe Laravel route URLs. However, if the select's `<option value>` is ever modified client-side (e.g., by a browser extension or a reflected-XSS payload elsewhere), an attacker could redirect the user to an arbitrary URL. This is a low-risk open-redirect surface but worth noting.

**Fix:** Validate the URL before navigating:
```javascript
document.addEventListener('change', (event) => {
    const select = event.target.closest('[data-locale-select]');
    if (!select || !select.value) return;

    try {
        const url = new URL(select.value, window.location.origin);
        if (url.origin === window.location.origin) {
            window.location.href = url.href;
        }
    } catch {
        // invalid URL, do nothing
    }
});
```

---

_Reviewed: 2026-06-21_
_Reviewer: Claude (gsd-code-reviewer)_
_Depth: standard_
