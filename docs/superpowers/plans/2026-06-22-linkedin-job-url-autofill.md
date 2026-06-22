# LinkedIn Job URL Auto-fill Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** When a user pastes a LinkedIn job URL on `jobs/create`, the system extracts the job ID, fetches structured data from LinkedIn's guest API, and auto-fills title, company, and description fields.

**Architecture:** Frontend Alpine component detects LinkedIn URL on paste/input, extracts numeric `jobId`, POSTs to `/jobs/fetch-linkedin` (auth + rate-limited). Backend fetches `linkedin.com/jobs-guest/jobs/api/jobPosting/{jobId}`, parses JSON-LD embedded in HTML, returns `{title, company, description}`. Frontend fills form fields.

**Tech Stack:** Laravel 11, PHP 8.3, Alpine.js v3, Laravel HTTP facade, PHPUnit

## Global Constraints

- Laravel validation: `job_id` must be numeric-only string (SSRF prevention — never pass raw URLs to backend)
- `strip_tags()` on description before returning (JSON-LD description is HTML)
- Timeout: 10 seconds on LinkedIn HTTP request
- Rate limit: 20 requests/minute per authenticated user
- `linkedin_url` stored in DB as reference even if auto-fill was not triggered
- Alpine component registered BEFORE `Alpine.start()` in `app.js`

---

### Task 1: Migration + Model + FormRequest

**Files:**
- Create: `database/migrations/2026_06_22_000000_add_linkedin_url_to_job_posts.php`
- Modify: `app/Models/JobPost.php` — add `linkedin_url` to `$fillable`
- Modify: `app/Http/Requests/Jobs/JobPostRequest.php` — add `linkedin_url` validation rule
- Modify: `tests/Feature/VyrkoMvpTest.php` — add `test_linkedin_url_is_stored_with_job_post`

**Interfaces:**
- Produces: `job_posts.linkedin_url` nullable string column, accessible via `JobPost::$fillable`

- [ ] **Step 1: Write the failing test**

Add to `tests/Feature/VyrkoMvpTest.php` inside the class (after existing tests):

```php
public function test_linkedin_url_is_stored_with_job_post(): void
{
    $user = \App\Models\User::factory()->create();
    $user->candidateProfile()->create(['full_name' => 'Test User']);

    $this->actingAs($user)
        ->post(route('jobs.store'), [
            'title' => 'Senior Engineer',
            'company_name' => 'Acme',
            'job_description' => str_repeat('x', 100),
            'target_language' => 'pt_BR',
            'resume_type' => 'tech',
            'linkedin_url' => 'https://www.linkedin.com/jobs/view/4393083468/',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('job_posts', [
        'linkedin_url' => 'https://www.linkedin.com/jobs/view/4393083468/',
    ]);
}
```

- [ ] **Step 2: Run test to verify it fails**

```bash
php artisan test --filter=test_linkedin_url_is_stored_with_job_post
```

Expected: FAIL — column `linkedin_url` does not exist.

- [ ] **Step 3: Create migration**

Create `database/migrations/2026_06_22_000000_add_linkedin_url_to_job_posts.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_posts', function (Blueprint $table): void {
            $table->string('linkedin_url', 500)->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('job_posts', function (Blueprint $table): void {
            $table->dropColumn('linkedin_url');
        });
    }
};
```

- [ ] **Step 4: Add to model fillable**

In `app/Models/JobPost.php`, add `'linkedin_url'` to the `$fillable` array:

```php
protected $fillable = [
    'user_id',
    'title',
    'company_name',
    'job_description',
    'target_language',
    'resume_type',
    'notes',
    'linkedin_url',
    'parsed_requirements',
    'parsed_keywords',
    'parsed_responsibilities',
    'parsed_seniority',
];
```

- [ ] **Step 5: Add validation rule to FormRequest**

In `app/Http/Requests/Jobs/JobPostRequest.php`, add to `rules()`:

```php
'linkedin_url' => ['nullable', 'string', 'url', 'max:500', 'regex:/linkedin\.com\/jobs/'],
```

Full `rules()` after change:

```php
public function rules(): array
{
    return [
        'title' => ['required', 'string', 'max:180'],
        'company_name' => ['nullable', 'string', 'max:180'],
        'job_description' => ['required', 'string', 'min:20', 'max:30000'],
        'target_language' => ['required', 'in:pt_BR,en'],
        'resume_type' => ['required', 'in:national_brazil,international,tech,executive'],
        'notes' => ['nullable', 'string', 'max:4000'],
        'linkedin_url' => ['nullable', 'string', 'url', 'max:500', 'regex:/linkedin\.com\/jobs/'],
    ];
}
```

- [ ] **Step 6: Run migration and test**

```bash
php artisan migrate
php artisan test --filter=test_linkedin_url_is_stored_with_job_post
```

Expected: PASS.

- [ ] **Step 7: Run full suite**

```bash
php artisan test --no-coverage
```

Expected: all tests green.

- [ ] **Step 8: Commit**

```bash
git add database/migrations/2026_06_22_000000_add_linkedin_url_to_job_posts.php \
        app/Models/JobPost.php \
        app/Http/Requests/Jobs/JobPostRequest.php \
        tests/Feature/VyrkoMvpTest.php
git commit -m "feat: add linkedin_url column to job_posts + model + request rule"
```

---

### Task 2: JobFetchController + Route

**Files:**
- Create: `app/Http/Controllers/JobFetchController.php`
- Create: `tests/Feature/JobFetchControllerTest.php`
- Modify: `routes/web.php` — add POST route + import

**Interfaces:**
- Consumes: `job_posts.linkedin_url` column from Task 1 (no runtime dependency — just structural)
- Produces: `POST /jobs/fetch-linkedin` → `200 {title, company, description}` or `422 {error}`

- [ ] **Step 1: Create test file**

Create `tests/Feature/JobFetchControllerTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class JobFetchControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_requires_authentication(): void
    {
        $this->postJson('/jobs/fetch-linkedin', ['job_id' => '1234567890'])
            ->assertStatus(401);
    }

    public function test_rejects_non_numeric_job_id(): void
    {
        $this->actingAs($this->user)
            ->postJson('/jobs/fetch-linkedin', ['job_id' => 'abc123'])
            ->assertStatus(422);
    }

    public function test_rejects_missing_job_id(): void
    {
        $this->actingAs($this->user)
            ->postJson('/jobs/fetch-linkedin', [])
            ->assertStatus(422);
    }

    public function test_returns_422_when_linkedin_returns_non_200(): void
    {
        Http::fake([
            'linkedin.com/*' => Http::response('', 404),
        ]);

        $this->actingAs($this->user)
            ->postJson('/jobs/fetch-linkedin', ['job_id' => '1234567890'])
            ->assertStatus(422)
            ->assertJson(['error' => 'not_found']);
    }

    public function test_returns_422_when_no_json_ld_in_response(): void
    {
        Http::fake([
            'linkedin.com/*' => Http::response('<html><body>No structured data here</body></html>', 200),
        ]);

        $this->actingAs($this->user)
            ->postJson('/jobs/fetch-linkedin', ['job_id' => '1234567890'])
            ->assertStatus(422)
            ->assertJson(['error' => 'parse_failed']);
    }

    public function test_returns_422_when_json_ld_type_is_not_job_posting(): void
    {
        $jsonLd = json_encode(['@type' => 'Organization', 'name' => 'Acme']);
        $html = "<html><head><script type=\"application/ld+json\">{$jsonLd}</script></head></html>";

        Http::fake([
            'linkedin.com/*' => Http::response($html, 200),
        ]);

        $this->actingAs($this->user)
            ->postJson('/jobs/fetch-linkedin', ['job_id' => '1234567890'])
            ->assertStatus(422)
            ->assertJson(['error' => 'parse_failed']);
    }

    public function test_returns_title_company_and_stripped_description(): void
    {
        $jsonLd = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'JobPosting',
            'title' => 'Senior Backend Engineer',
            'hiringOrganization' => ['@type' => 'Organization', 'name' => 'Acme Corp'],
            'description' => '<p>We need a <strong>PHP</strong> developer.</p><ul><li>Laravel</li></ul>',
        ]);
        $html = "<html><head><script type=\"application/ld+json\">{$jsonLd}</script></head></html>";

        Http::fake([
            'linkedin.com/*' => Http::response($html, 200),
        ]);

        $this->actingAs($this->user)
            ->postJson('/jobs/fetch-linkedin', ['job_id' => '4393083468'])
            ->assertStatus(200)
            ->assertJson([
                'title' => 'Senior Backend Engineer',
                'company' => 'Acme Corp',
                'description' => 'We need a PHP developer.Laravel',
            ]);
    }

    public function test_handles_missing_optional_fields_gracefully(): void
    {
        $jsonLd = json_encode([
            '@type' => 'JobPosting',
            'title' => 'Backend Engineer',
        ]);
        $html = "<html><head><script type=\"application/ld+json\">{$jsonLd}</script></head></html>";

        Http::fake([
            'linkedin.com/*' => Http::response($html, 200),
        ]);

        $this->actingAs($this->user)
            ->postJson('/jobs/fetch-linkedin', ['job_id' => '1234567890'])
            ->assertStatus(200)
            ->assertJson([
                'title' => 'Backend Engineer',
                'company' => null,
                'description' => null,
            ]);
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
php artisan test --filter=JobFetchControllerTest
```

Expected: FAIL — route not found (404 or RouteNotFoundException).

- [ ] **Step 3: Create controller**

Create `app/Http/Controllers/JobFetchController.php`:

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

        $jobId = $request->input('job_id');

        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'Accept-Language' => 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
        ])->timeout(10)->get("https://www.linkedin.com/jobs-guest/jobs/api/jobPosting/{$jobId}");

        if (! $response->successful()) {
            return response()->json(['error' => 'not_found'], 422);
        }

        $jsonLd = $this->extractJsonLd($response->body());

        if (! $jsonLd) {
            return response()->json(['error' => 'parse_failed'], 422);
        }

        $rawDescription = $jsonLd['description'] ?? null;

        return response()->json([
            'title' => isset($jsonLd['title']) ? trim((string) $jsonLd['title']) : null,
            'company' => isset($jsonLd['hiringOrganization']['name'])
                ? trim((string) $jsonLd['hiringOrganization']['name'])
                : null,
            'description' => $rawDescription !== null
                ? trim(strip_tags((string) $rawDescription))
                : null,
        ]);
    }

    private function extractJsonLd(string $html): ?array
    {
        if (! preg_match(
            '/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/si',
            $html,
            $matches
        )) {
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

- [ ] **Step 4: Add route**

In `routes/web.php`, add import at top with other controller imports:

```php
use App\Http\Controllers\JobFetchController;
```

Add route inside the authenticated middleware group, next to other jobs routes (after line with `Route::resource('jobs', ...)`):

```php
Route::post('/jobs/fetch-linkedin', JobFetchController::class)
    ->middleware('throttle:20,1')
    ->name('jobs.fetch-linkedin');
```

- [ ] **Step 5: Run tests to verify they pass**

```bash
php artisan test --filter=JobFetchControllerTest
```

Expected: all 8 tests PASS.

- [ ] **Step 6: Run full suite**

```bash
php artisan test --no-coverage
```

Expected: all tests green.

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/JobFetchController.php \
        tests/Feature/JobFetchControllerTest.php \
        routes/web.php
git commit -m "feat: JobFetchController parses LinkedIn guest API for job details"
```

---

### Task 3: Alpine Component + View

**Files:**
- Modify: `resources/js/app.js` — add `Alpine.data('linkedinFetch', ...)` before `Alpine.start()`
- Modify: `resources/views/jobs/create.blade.php` — add URL field at top of form

**Interfaces:**
- Consumes: `POST /jobs/fetch-linkedin` from Task 2
- Consumes: `Alpine.data('loadingModal', ...)` already registered at line 4 — insert after it, before `Alpine.start()` at line 30

- [ ] **Step 1: Add Alpine component to app.js**

In `resources/js/app.js`, insert after the closing `}));` of `loadingModal` (line 28) and before `Alpine.start()` (line 30):

```js
Alpine.data('linkedinFetch', () => ({
    url: '',
    status: 'idle', // idle | loading | success | error
    errorMsg: '',

    extractJobId(url) {
        try {
            const u = new URL(url);
            if (! u.hostname.includes('linkedin.com')) return null;
            const fromParam = u.searchParams.get('currentJobId');
            if (fromParam && /^\d+$/.test(fromParam)) return fromParam;
            const match = u.pathname.match(/\/jobs\/view\/(\d+)/);
            return match ? match[1] : null;
        } catch {
            return null;
        }
    },

    async fetchJob(jobId) {
        this.status = 'loading';
        try {
            const res = await fetch('/jobs/fetch-linkedin', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ job_id: jobId }),
            });
            if (! res.ok) throw new Error();
            const data = await res.json();
            if (data.title) document.querySelector('[name="title"]').value = data.title;
            if (data.company) document.querySelector('[name="company_name"]').value = data.company;
            if (data.description) document.querySelector('[name="job_description"]').value = data.description;
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

- [ ] **Step 2: Add URL field to create.blade.php**

In `resources/views/jobs/create.blade.php`, inside the `<form>` element, add the URL field block as the very first child (before `<div class="panel-title">`):

```blade
<div x-data="linkedinFetch" class="field">
    <label>{{ $en ? 'LinkedIn job URL' : 'Link da vaga (LinkedIn)' }}</label>
    <input
        name="linkedin_url"
        type="url"
        x-model="url"
        @paste.prevent="onPaste($event)"
        @input.debounce.400ms="onInput($event.target.value)"
        placeholder="https://www.linkedin.com/jobs/view/..."
        autocomplete="off"
    >
    <p class="form-help" x-show="status === 'idle' || status === 'success'">
        <template x-if="status === 'success'">
            <span>&#10003; {{ $en ? 'Job detected — fields filled below.' : 'Vaga detectada — campos preenchidos abaixo.' }}</span>
        </template>
        <template x-if="status !== 'success'">
            <span>{{ $en ? 'Optional. Paste the LinkedIn URL to auto-fill the fields below.' : 'Opcional. Cole o link do LinkedIn para preencher os campos abaixo automaticamente.' }}</span>
        </template>
    </p>
    <p class="form-help" x-show="status === 'loading'">{{ $en ? 'Fetching job...' : 'Buscando vaga...' }}</p>
    <p class="form-help" x-show="status === 'error'" x-text="errorMsg" role="alert"></p>
</div>

<hr>
```

- [ ] **Step 3: Build assets**

```bash
npm run build
```

Expected: build completes without errors. Check output includes `linkedinFetch` by inspecting `public/build/assets/app-*.js` or running `grep -l "linkedinFetch" public/build/assets/`.

- [ ] **Step 4: Manual test — happy path**

Start the dev server and navigate to `http://localhost/jobs/create`.

1. Paste URL `https://www.linkedin.com/jobs/collections/recommended/?currentJobId=4393083468` into "Link da vaga" field
2. Observe: "Buscando vaga..." appears momentarily
3. Observe: "✓ Vaga detectada — campos preenchidos abaixo." appears
4. Verify title, company name, and description fields are populated

- [ ] **Step 5: Manual test — fallback path**

1. Paste URL `https://www.linkedin.com/jobs/view/9999999999/` (invalid/private job)
2. Observe: "Não foi possível buscar a vaga. Preencha manualmente." appears
3. Verify all form fields remain editable

- [ ] **Step 6: Manual test — non-LinkedIn URL**

1. Type `https://www.google.com` into the field
2. Observe: nothing happens (no fetch triggered), form-help text stays as hint

- [ ] **Step 7: Manual test — submit with URL**

1. Fill URL field (or skip it)
2. Fill remaining required fields
3. Submit form
4. Verify no validation errors, redirects to job show page

- [ ] **Step 8: Run full suite**

```bash
php artisan test --no-coverage
```

Expected: all tests green.

- [ ] **Step 9: Commit**

```bash
git add resources/js/app.js \
        resources/views/jobs/create.blade.php
git commit -m "feat: LinkedIn URL auto-fill on jobs/create — Alpine + view"
```
