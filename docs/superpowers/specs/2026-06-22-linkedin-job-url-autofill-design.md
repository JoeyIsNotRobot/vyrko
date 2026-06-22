# Design: LinkedIn Job URL Auto-fill no jobs/create

**Data:** 2026-06-22
**Fase relacionada:** Phase 3 — Workspace de Vagas (JOB-01)
**Status:** Aprovado

---

## Problema

Usuário copia URL de uma vaga no LinkedIn (formatos `?currentJobId=123` ou `/jobs/view/123/`), acessa `jobs/create` e precisa colar manualmente título, empresa e descrição. Processo repetitivo e sujeito a erro.

## Solução

Campo "Link da vaga" no topo do formulário. Ao colar URL do LinkedIn, o sistema extrai o `jobId`, faz fetch via endpoint backend protegido, parseia o JSON-LD retornado pelo LinkedIn e preenche automaticamente título, empresa e descrição.

---

## Arquitetura

### Fluxo

```
Cole URL → Alpine extrai jobId (numérico)
         → POST /jobs/fetch-linkedin {job_id}
         → Backend: Http::get linkedin.com/jobs-guest/api/jobPosting/{jobId}
         → Parseia JSON-LD embutido no HTML
         → Retorna {title, company, description}
         → Alpine preenche campos + badge "Vaga detectada"
```

### Fallback

Se fetch falhar (vaga privada, IP bloqueado, ID inválido):
- Alerta inline sutil: "Não foi possível buscar a vaga. Preencha manualmente."
- Campos liberados — usuário continua normalmente
- `linkedin_url` ainda é salvo no submit como referência

---

## Componentes

### 1. Frontend — `resources/views/jobs/create.blade.php`

Campo `linkedin_url` adicionado **antes** do `div.form-row` atual (topo do form).

```blade
<div class="field" x-data="linkedinFetch()" @paste.prevent="onPaste($event)" @input.debounce.400ms="onInput($event.target.value)">
    <label>Link da vaga (LinkedIn)</label>
    <div class="input-with-status">
        <input name="linkedin_url" x-model="url" placeholder="https://www.linkedin.com/jobs/view/..." autocomplete="off">
        <span x-show="status === 'loading'" class="field-spinner">...</span>
        <span x-show="status === 'success'" class="field-badge success">Vaga detectada</span>
    </div>
    <p class="form-help" x-show="status === 'error'" x-text="errorMsg" role="alert"></p>
    <p class="form-help" x-show="status !== 'error'">Opcional. Cole o link para preencher os campos automaticamente.</p>
</div>
```

Alpine component `linkedinFetch()` em `resources/js/app.js`:

```js
Alpine.data('linkedinFetch', () => ({
    url: '',
    status: 'idle', // idle | loading | success | error
    errorMsg: '',

    extractJobId(url) {
        try {
            const u = new URL(url);
            if (!u.hostname.includes('linkedin.com')) return null;
            const fromParam = u.searchParams.get('currentJobId');
            if (fromParam && /^\d+$/.test(fromParam)) return fromParam;
            const match = u.pathname.match(/\/jobs\/view\/(\d+)/);
            return match ? match[1] : null;
        } catch { return null; }
    },

    async fetchJob(jobId) {
        this.status = 'loading';
        try {
            const res = await fetch('/jobs/fetch-linkedin', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: JSON.stringify({ job_id: jobId }),
            });
            if (!res.ok) throw new Error();
            const data = await res.json();
            document.querySelector('[name=title]').value = data.title ?? '';
            document.querySelector('[name=company_name]').value = data.company ?? '';
            document.querySelector('[name=job_description]').value = data.description ?? '';
            this.status = 'success';
        } catch {
            this.status = 'error';
            this.errorMsg = 'Não foi possível buscar a vaga. Preencha manualmente.';
        }
    },

    onInput(val) {
        const jobId = this.extractJobId(val);
        if (jobId) this.fetchJob(jobId);
    },

    onPaste(event) {
        const text = (event.clipboardData || window.clipboardData).getData('text');
        this.url = text;
        const jobId = this.extractJobId(text);
        if (jobId) this.fetchJob(jobId);
    },
}));
```

### 2. Backend — `app/Http/Controllers/JobFetchController.php`

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class JobFetchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'job_id' => ['required', 'string', 'regex:/^\d{1,20}$/'],
        ]);

        $jobId = $request->validated('job_id');

        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 Chrome/124.0 Safari/537.36',
            'Accept-Language' => 'pt-BR,pt;q=0.9,en;q=0.8',
        ])->timeout(10)->get("https://www.linkedin.com/jobs-guest/jobs/api/jobPosting/{$jobId}");

        if (! $response->successful()) {
            return response()->json(['error' => 'not_found'], 422);
        }

        $jsonLd = $this->extractJsonLd($response->body());

        if (! $jsonLd) {
            return response()->json(['error' => 'parse_failed'], 422);
        }

        return response()->json([
            'title' => $jsonLd['title'] ?? null,
            'company' => $jsonLd['hiringOrganization']['name'] ?? null,
            'description' => isset($jsonLd['description'])
                ? trim(strip_tags($jsonLd['description']))
                : null,
        ]);
    }

    private function extractJsonLd(string $html): ?array
    {
        if (! preg_match('/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/si', $html, $matches)) {
            return null;
        }

        $decoded = json_decode($matches[1], true);

        if (! is_array($decoded) || ($decoded['@type'] ?? null) !== 'JobPosting') {
            return null;
        }

        return $decoded;
    }
}
```

### 3. Rota — `routes/web.php`

```php
Route::post('/jobs/fetch-linkedin', JobFetchController::class)
    ->middleware(['auth', 'throttle:20,1'])
    ->name('jobs.fetch-linkedin');
```

### 4. Migration

```php
$table->string('linkedin_url', 500)->nullable()->after('notes');
```

### 5. Model — `app/Models/JobPost.php`

Adicionar `'linkedin_url'` ao `$fillable`.

### 6. Request — `app/Http/Requests/Jobs/JobPostRequest.php`

```php
'linkedin_url' => ['nullable', 'string', 'url', 'max:500', 'regex:/linkedin\.com\/jobs/'],
```

---

## Segurança

| Risco | Mitigação |
|---|---|
| SSRF via URL arbitrária | `job_id` aceita apenas dígitos — nunca URL como parâmetro |
| Abuso do endpoint | Rate limit 20 req/min por usuário autenticado |
| XSS via descrição | `strip_tags()` antes de retornar; Alpine usa `value =` (não innerHTML) |
| Timeout em requisição lenta | `->timeout(10)` no Http facade |
| URL inválida salva no DB | Regra `url` + regex linkedin no `JobPostRequest` |

---

## Estados de UI

| Status | Visual |
|---|---|
| `idle` | Campo normal, form-help padrão |
| `loading` | Spinner ao lado do input, campos title/company/description ficam readonly |
| `success` | Badge "Vaga detectada" verde, campos preenchidos e editáveis |
| `error` | Alerta inline em vermelho sutil, campos editáveis manualmente |

---

## Fora de escopo

- Vagas privadas (requerem login — fallback gracioso)
- Outros formatos de URL de emprego (Glassdoor, Indeed, etc.)
- Auto-detecção de idioma da vaga
- Remoção do campo "Tipo de currículo" (será tratado na Phase 3 completa)
