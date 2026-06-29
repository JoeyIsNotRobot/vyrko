# Vyrko

> AI-powered resume tailoring platform — matches your real experience to each job, no fabrication.

Vyrko is a full-stack SaaS application where job seekers build a structured career inventory and use AI to analyze job postings, score their fit, identify gaps, and generate ATS-optimized resumes tailored to each opportunity.

---

## Why I Built This

The standard job-search advice — "tailor your resume to every job" — breaks down in practice because most tools either invent experience that doesn't exist or make you copy-paste manually. Vyrko solves this by treating your career as a structured inventory and letting AI do the matching, not the fabricating.

---

## Features

### Career Inventory
Structured profile with experiences, projects, skills, education, certifications, languages, and achievements. Import via TXT/PDF/DOCX with AI-assisted parsing.

### Job Analysis Pipeline
Paste a job URL or description → AI extracts structured requirements → generates a full match report with:
- **Multi-dimensional scoring**: overall, technical, experience, seniority, keyword, ATS format, human readability
- **Evidence map**: each requirement linked to specific inventory items (or flagged as missing)
- **Gap analysis**: critical vs. acceptable gaps, actionable recommendations

### Resume Generation
Generates ATS-optimized resumes from your inventory — using only evidence you actually have. Three templates: ATS Classic, Tech Compact, International Clean. Export via PDF.

### ATS Checklist
AI-generated checklist per resume version, stored in the database, flagging formatting and keyword issues before submission.

### LinkedIn Tools
- **Profile Analysis**: paste your LinkedIn text → AI scores it, rewrites headline/about section, lists strengths and improvement areas
- **Boolean Search Builder**: generates optimized LinkedIn search queries for any target role

### Authentication & Onboarding
OAuth via Google and LinkedIn (Laravel Socialite). Consent-based social data collection, email verification, guided onboarding flow.

### Usage Limits
Credit system with per-feature limits, usage logging (`AiRun`), and a foundation for paid plans (Free / Pay-per-use / Sprint / Pro).

---

## Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.3 · Laravel 13 |
| Frontend | Blade · Alpine.js · Tailwind CSS 4 · Vite |
| AI | Google Gemini 2.5 Flash (structured JSON output, temperature 0.2) |
| Database | MySQL 8.4 · Redis |
| Auth | Laravel Socialite (Google + LinkedIn OAuth) |
| Infrastructure | Docker via Laravel Sail |
| Fonts | Geist Sans · JetBrains Mono |

---

## Architecture Highlights

- **Service layer** for all AI workflows (`JobMatchAnalyzer`, `ResumeGenerator`, `AtsChecklistAnalyzer`, `LinkedinAnalysisController`) — controllers stay thin
- **Interface-based AI client** (`AiClient`) with a real `GeminiAiClient` and a `FakeAiClient` for tests — swappable provider without touching business logic
- **`CareerInventoryFormatter`** normalizes the user's inventory into a structured payload consumed by every AI prompt
- **`UsageLimiter`** enforces per-feature credit limits before any AI call; usage logged to `usage_logs` with metadata
- Retry logic on Gemini HTTP calls (2 retries, 500ms delay); structured JSON response enforced via `responseMimeType`
- Policies via `Gate::authorize` on all resource mutations; signed URLs for email change confirmation
- i18n: PT-BR default, EN supported, locale switchable per request

---

## Running Locally

```bash
composer install
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

Set `AI_API_KEY` in `.env` to a Gemini API key.  
App: http://localhost · Mailpit: http://localhost:8025

Seed creates a dev user when `SEED_DEV_USER=true`:

```
dev@vyrko.test / password
```

## Testing

```bash
./vendor/bin/sail artisan test
./vendor/bin/pint --test
```

---

## Status

Active development. MVP feature-complete; current focus: design system unification, guided job-analysis flow, and monetization layer (Mercado Pago integration).
