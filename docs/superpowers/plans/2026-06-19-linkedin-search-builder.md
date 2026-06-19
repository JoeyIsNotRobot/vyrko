# LinkedIn Search Builder — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a free-tier, zero-AI boolean query generator at `/linkedin-search-builder` that accepts job titles, skills, and preferences and returns 3–4 ready-to-use LinkedIn search strings.

**Architecture:** DTO (`LinkedInSearchInput`) carries validated form data into a deterministic service (`LinkedInBooleanQueryBuilder`) that returns an array of `LinkedInQuery` value objects. A thin controller validates via `LinkedInSearchRequest`, calls the service, and returns JSON consumed by ~80 lines of vanilla JS on the page.

**Tech Stack:** Laravel 11, Blade, Tailwind v4 (custom CSS classes, existing patterns), PHPUnit, vanilla JS (fetch + clipboard API), JetBrains Mono for query display.

## Global Constraints

- Zero AI calls — no `AiClient`, no Gemini, no OpenAI, no Ollama injected anywhere in this feature
- Zero usage credits consumed — `UsageLimiter` is never called
- Route middleware: `auth` only (no `verified`)
- No new Composer or NPM dependencies
- Follow existing CSS class patterns (`.card`, `.btn`, `.field`, `.badge`, `.stack`, `.muted`) — add only minimal new classes in `app.css`
- All copy in Portuguese (primary) — same bilingual pattern as other views if `$en` variable needed
- Controllers stay thin — all logic lives in the service
- Tests use `RefreshDatabase` + `$this->withoutVite()` + `actingAs()` pattern from `VyrkoMvpTest`

---

## File Map

| File | Status | Responsibility |
|------|--------|----------------|
| `app/Services/LinkedInSearch/LinkedInQuery.php` | Create | Value object — one query result with all fields |
| `app/Services/LinkedInSearch/LinkedInSearchInput.php` | Create | DTO — typed input from validated request |
| `app/Services/LinkedInSearch/SynonymMap.php` | Create | Static synonym lookup, zero I/O |
| `app/Services/LinkedInSearch/LinkedInBooleanQueryBuilder.php` | Create | Core service — builds all query variants |
| `app/Http/Requests/LinkedInSearch/LinkedInSearchRequest.php` | Create | Validation + "at least one title or skill" rule |
| `app/Http/Controllers/LinkedInSearchController.php` | Create | Thin controller — index view + generate JSON |
| `resources/views/linkedin-search/index.blade.php` | Create | Full page: form, empty state, cards, tips, JS |
| `resources/css/app.css` | Modify | Add tag-chip, query-block, checkbox-group classes |
| `resources/views/layouts/app.blade.php` | Modify | Add Search Builder nav link in auth nav |
| `routes/web.php` | Modify | Add GET+POST `/linkedin-search-builder` under `auth` |
| `tests/Unit/LinkedInBooleanQueryBuilderTest.php` | Create | 11 unit tests for the builder service |
| `tests/Feature/LinkedInSearchControllerTest.php` | Create | 6 feature tests for HTTP layer |

---

## Task 1: Data Structures (LinkedInQuery + LinkedInSearchInput + SynonymMap)

**Files:**
- Create: `app/Services/LinkedInSearch/LinkedInQuery.php`
- Create: `app/Services/LinkedInSearch/LinkedInSearchInput.php`
- Create: `app/Services/LinkedInSearch/SynonymMap.php`

**Interfaces:**
- Produces: `LinkedInQuery` readonly class with public fields used by builder and controller
- Produces: `LinkedInSearchInput::fromArray(array)` used by controller
- Produces: `SynonymMap::forTitle(string): string[]` and `SynonymMap::forNiche(string): string[]` used by builder

---

- [ ] **Step 1: Create `LinkedInQuery` value object**

Create `app/Services/LinkedInSearch/LinkedInQuery.php`:

```php
<?php

namespace App\Services\LinkedInSearch;

class LinkedInQuery
{
    public function __construct(
        public readonly string $type,
        public readonly string $label,
        public readonly string $objective,
        public readonly string $query,
        public readonly string $linkedinJobsUrl,
        public readonly string $linkedinPostsUrl,
        public readonly string $tip,
        public readonly ?string $warning = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'type'            => $this->type,
            'label'           => $this->label,
            'objective'       => $this->objective,
            'query'           => $this->query,
            'linkedinJobsUrl' => $this->linkedinJobsUrl,
            'linkedinPostsUrl'=> $this->linkedinPostsUrl,
            'tip'             => $this->tip,
            'warning'         => $this->warning,
        ];
    }
}
```

- [ ] **Step 2: Create `LinkedInSearchInput` DTO**

Create `app/Services/LinkedInSearch/LinkedInSearchInput.php`:

```php
<?php

namespace App\Services\LinkedInSearch;

class LinkedInSearchInput
{
    /**
     * @param string[] $titles
     * @param string[] $skills
     * @param string[] $seniorities
     * @param string[] $workModes
     * @param string[] $locations
     * @param string[] $excludedTerms
     */
    public function __construct(
        public readonly array $titles,
        public readonly array $skills,
        public readonly array $seniorities,
        public readonly array $workModes,
        public readonly array $locations,
        public readonly string $language,
        public readonly array $excludedTerms,
        public readonly ?string $niche = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            titles:        array_values(array_filter((array) ($data['titles'] ?? []))),
            skills:        array_values(array_filter((array) ($data['skills'] ?? []))),
            seniorities:   array_values(array_filter((array) ($data['seniorities'] ?? []))),
            workModes:     array_values(array_filter((array) ($data['work_modes'] ?? []))),
            locations:     array_values(array_filter((array) ($data['locations'] ?? []))),
            language:      (string) ($data['language'] ?? 'both'),
            excludedTerms: array_values(array_filter((array) ($data['excluded'] ?? []))),
            niche:         isset($data['niche']) && $data['niche'] !== '' ? (string) $data['niche'] : null,
        );
    }
}
```

- [ ] **Step 3: Create `SynonymMap`**

Create `app/Services/LinkedInSearch/SynonymMap.php`:

```php
<?php

namespace App\Services\LinkedInSearch;

class SynonymMap
{
    /** @var array<string, string[]> */
    private const TITLE_SYNONYMS = [
        'backend'   => ['Backend Developer', 'Backend Engineer', 'Software Engineer', 'API Developer'],
        'frontend'  => ['Frontend Developer', 'Frontend Engineer', 'React Developer', 'UI Developer'],
        'fullstack' => ['Full Stack Developer', 'Fullstack Engineer', 'Software Engineer'],
        'full stack'=> ['Full Stack Developer', 'Fullstack Engineer', 'Software Engineer'],
        'data'      => ['Data Analyst', 'BI Analyst', 'Analytics Analyst', 'Data Engineer'],
        'dados'     => ['Analista de Dados', 'Data Analyst', 'BI Analyst', 'Data Engineer'],
        'product'   => ['Product Manager', 'Product Owner', 'Product Analyst', 'PM'],
        'produto'   => ['Product Manager', 'Product Owner', 'Product Analyst', 'Gerente de Produto'],
        'design'    => ['UX Designer', 'UI Designer', 'Product Designer', 'UX/UI Designer'],
        'marketing' => ['Marketing Analyst', 'Performance Marketing', 'Growth Analyst', 'Social Media'],
        'sales'     => ['Sales Development Representative', 'SDR', 'Account Executive', 'Sales Executive'],
        'vendas'    => ['Executivo de Vendas', 'SDR', 'Account Executive', 'Closer'],
        'admin'     => ['Administrative Assistant', 'Office Assistant', 'Backoffice Analyst', 'Operations Assistant'],
        'qa'        => ['QA Engineer', 'Quality Assurance Engineer', 'Test Engineer', 'SDET'],
        'devops'    => ['DevOps Engineer', 'SRE', 'Infrastructure Engineer', 'Cloud Engineer'],
        'mobile'    => ['Mobile Developer', 'iOS Developer', 'Android Developer', 'React Native Developer'],
        'suporte'   => ['Customer Support', 'Support Analyst', 'Help Desk', 'Technical Support'],
        'rh'        => ['Analista de RH', 'HR Analyst', 'People & Culture', 'Recrutamento e Seleção'],
        'financeiro'=> ['Analista Financeiro', 'Financial Analyst', 'Controladoria', 'Analista Contábil'],
    ];

    /** @var array<string, string[]> */
    private const NICHE_SYNONYMS = [
        'tecnologia'    => ['Software Engineer', 'Backend Developer', 'Tech Lead', 'Engineering Manager'],
        'dados'         => ['Data Analyst', 'BI Analyst', 'Data Engineer', 'Analytics Engineer'],
        'produto'       => ['Product Manager', 'Product Owner', 'Product Analyst'],
        'design'        => ['UX Designer', 'UI Designer', 'Product Designer'],
        'marketing'     => ['Marketing Analyst', 'Growth Analyst', 'Performance Marketing'],
        'vendas'        => ['Sales Development Representative', 'SDR', 'Account Executive'],
        'administrativo'=> ['Administrative Assistant', 'Backoffice Analyst', 'Operations Assistant'],
        'financeiro'    => ['Financial Analyst', 'Analista Financeiro', 'Controladoria'],
        'rh'            => ['HR Analyst', 'Analista de RH', 'People & Culture'],
        'suporte'       => ['Customer Support', 'Support Analyst', 'Customer Success'],
    ];

    /** @return string[] */
    public static function forTitle(string $title): array
    {
        $lower = mb_strtolower($title);
        foreach (self::TITLE_SYNONYMS as $keyword => $synonyms) {
            if (str_contains($lower, $keyword)) {
                return $synonyms;
            }
        }
        return [];
    }

    /** @return string[] */
    public static function forNiche(string $niche): array
    {
        $lower = mb_strtolower(trim($niche));
        return self::NICHE_SYNONYMS[$lower] ?? [];
    }
}
```

- [ ] **Step 4: Commit**

```bash
git add app/Services/LinkedInSearch/
git commit -m "feat: add LinkedInQuery value object, LinkedInSearchInput DTO, and SynonymMap"
```

---

## Task 2: LinkedInBooleanQueryBuilder Service (TDD)

**Files:**
- Create: `tests/Unit/LinkedInBooleanQueryBuilderTest.php`
- Create: `app/Services/LinkedInSearch/LinkedInBooleanQueryBuilder.php`

**Interfaces:**
- Consumes: `LinkedInSearchInput`, `LinkedInQuery`, `SynonymMap` from Task 1
- Produces: `LinkedInBooleanQueryBuilder::build(LinkedInSearchInput): LinkedInQuery[]`

---

- [ ] **Step 1: Write all unit tests (they will fail)**

Create `tests/Unit/LinkedInBooleanQueryBuilderTest.php`:

```php
<?php

namespace Tests\Unit;

use App\Services\LinkedInSearch\LinkedInBooleanQueryBuilder;
use App\Services\LinkedInSearch\LinkedInSearchInput;
use PHPUnit\Framework\TestCase;

class LinkedInBooleanQueryBuilderTest extends TestCase
{
    private LinkedInBooleanQueryBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new LinkedInBooleanQueryBuilder();
    }

    private function input(array $overrides = []): LinkedInSearchInput
    {
        return new LinkedInSearchInput(
            titles:        $overrides['titles'] ?? ['Backend Developer'],
            skills:        $overrides['skills'] ?? ['PHP', 'Laravel'],
            seniorities:   $overrides['seniorities'] ?? [],
            workModes:     $overrides['workModes'] ?? [],
            locations:     $overrides['locations'] ?? [],
            language:      $overrides['language'] ?? 'en',
            excludedTerms: $overrides['excludedTerms'] ?? [],
            niche:         $overrides['niche'] ?? null,
        );
    }

    // --- Test 1: broad query contains all provided titles ---
    public function test_broad_query_contains_all_titles(): void
    {
        $queries = $this->builder->build($this->input([
            'titles' => ['Backend Developer', 'PHP Developer'],
        ]));

        $broad = collect($queries)->firstWhere('type', 'broad');

        $this->assertNotNull($broad);
        $this->assertStringContainsString('Backend Developer', $broad->query);
        $this->assertStringContainsString('PHP Developer', $broad->query);
    }

    // --- Test 2: balanced query uses only first 3 skills ---
    public function test_balanced_query_uses_top_3_skills_only(): void
    {
        $queries = $this->builder->build($this->input([
            'skills' => ['PHP', 'Laravel', 'MySQL', 'Redis', 'Docker'],
        ]));

        $balanced = collect($queries)->firstWhere('type', 'balanced');

        $this->assertNotNull($balanced);
        $this->assertStringContainsString('PHP', $balanced->query);
        $this->assertStringContainsString('Laravel', $balanced->query);
        $this->assertStringContainsString('MySQL', $balanced->query);
        $this->assertStringNotContainsString('Redis', $balanced->query);
        $this->assertStringNotContainsString('Docker', $balanced->query);
    }

    // --- Test 3: precise query uses AND between top skills ---
    public function test_precise_query_uses_and_between_skills(): void
    {
        $queries = $this->builder->build($this->input([
            'skills' => ['PHP', 'MySQL', 'Docker'],
            'seniorities' => [],
        ]));

        $precise = collect($queries)->firstWhere('type', 'precise');

        $this->assertNotNull($precise);
        $this->assertStringContainsString('"PHP" AND "MySQL"', $precise->query);
    }

    // --- Test 4: NOT clause applied for excluded terms ---
    public function test_not_clause_applied_with_excluded_terms(): void
    {
        $queries = $this->builder->build($this->input([
            'excludedTerms' => ['WordPress', 'Magento'],
        ]));

        foreach ($queries as $query) {
            $this->assertStringContainsString('NOT', $query->query);
            $this->assertStringContainsString('WordPress', $query->query);
            $this->assertStringContainsString('Magento', $query->query);
        }
    }

    // --- Test 5: duplicate terms removed case-insensitively ---
    public function test_removes_duplicate_terms_case_insensitively(): void
    {
        $queries = $this->builder->build($this->input([
            'titles' => ['PHP Developer', 'PHP Developer', 'php developer'],
            'skills' => ['PHP', 'php'],
        ]));

        $broad = collect($queries)->firstWhere('type', 'broad');

        $this->assertNotNull($broad);
        $this->assertEquals(1, substr_count($broad->query, 'PHP Developer'));
        $this->assertEquals(1, substr_count($broad->query, '"PHP"'));
    }

    // --- Test 6: compound terms wrapped in double quotes ---
    public function test_compound_terms_wrapped_in_double_quotes(): void
    {
        $queries = $this->builder->build($this->input([
            'titles' => ['Backend Developer'],
            'skills' => ['APIs REST'],
        ]));

        $broad = collect($queries)->firstWhere('type', 'broad');

        $this->assertNotNull($broad);
        $this->assertStringContainsString('"Backend Developer"', $broad->query);
        $this->assertStringContainsString('"APIs REST"', $broad->query);
    }

    // --- Test 7: builder has no AI dependency ---
    public function test_builder_requires_no_ai_dependency(): void
    {
        $reflection  = new \ReflectionClass(LinkedInBooleanQueryBuilder::class);
        $constructor = $reflection->getConstructor();

        $this->assertTrue(
            $constructor === null || count($constructor->getParameters()) === 0,
            'Builder must have no constructor dependencies (no AI injection)'
        );
    }

    // --- Test 8: returns at least 3 queries ---
    public function test_returns_at_least_3_queries(): void
    {
        $queries = $this->builder->build($this->input());

        $this->assertGreaterThanOrEqual(3, count($queries));
    }

    // --- Test 9: LinkedIn Jobs URL is properly encoded ---
    public function test_linkedin_jobs_url_is_encoded_and_correct_base(): void
    {
        $queries = $this->builder->build($this->input());

        foreach ($queries as $query) {
            $this->assertStringStartsWith(
                'https://www.linkedin.com/jobs/search/?keywords=',
                $query->linkedinJobsUrl
            );
            $this->assertStringNotContainsString(' ', $query->linkedinJobsUrl);
        }
    }

    // --- Test 10: recruiter query generated when remote in workModes ---
    public function test_recruiter_query_generated_when_remote_work_mode(): void
    {
        $queries = $this->builder->build($this->input([
            'workModes' => ['Remoto'],
            'language'  => 'both',
        ]));

        $types = array_column(array_map(fn($q) => $q->toArray(), $queries), 'type');

        $this->assertContains('recruiter', $types);
    }

    // --- Test 11: recruiter query NOT generated without remote/international ---
    public function test_recruiter_query_not_generated_without_remote_mode(): void
    {
        $queries = $this->builder->build($this->input([
            'workModes' => ['Presencial', 'CLT'],
        ]));

        $types = array_column(array_map(fn($q) => $q->toArray(), $queries), 'type');

        $this->assertNotContains('recruiter', $types);
    }
}
```

- [ ] **Step 2: Run tests to confirm they fail**

```bash
cd "/home/joey/Documentos/Personal Projects/vyrko" && php artisan test tests/Unit/LinkedInBooleanQueryBuilderTest.php
```

Expected: multiple failures — class not found.

- [ ] **Step 3: Implement `LinkedInBooleanQueryBuilder`**

Create `app/Services/LinkedInSearch/LinkedInBooleanQueryBuilder.php`:

```php
<?php

namespace App\Services\LinkedInSearch;

class LinkedInBooleanQueryBuilder
{
    /** @var array<string, string[]> */
    private const WORK_MODE_MAP = [
        'remoto'         => ['Remote', 'Remoto'],
        'híbrido'        => ['Hybrid', 'Híbrido'],
        'hibrido'        => ['Hybrid', 'Híbrido'],
        'presencial'     => ['On-site', 'Presencial'],
        'internacional'  => ['Worldwide', 'Anywhere', 'Global'],
        'international'  => ['Worldwide', 'Anywhere', 'Global'],
        'freelance'      => ['Freelance', 'Contractor'],
        'pj'             => ['PJ', 'Pessoa Jurídica'],
        'clt'            => ['CLT'],
        'contrato'       => ['Contract', 'Contrato'],
    ];

    private const LANG_WORK_PT  = ['Remoto', 'Híbrido', 'Oportunidade'];
    private const LANG_WORK_EN  = ['Remote', 'Hybrid', 'Worldwide', 'Anywhere'];
    private const HIRING_PT     = ['vaga', 'oportunidade', 'estamos contratando', 'hiring'];
    private const HIRING_EN     = ['hiring', 'we are hiring', 'job opening', 'opportunity'];

    private const SENIORITY_MAP = [
        'sênior'      => 'Senior', 'senior'      => 'Senior',
        'pleno'       => 'Mid-level', 'mid'       => 'Mid-level',
        'júnior'      => 'Junior', 'junior'      => 'Junior',
        'lead'        => 'Lead',
        'especialista'=> 'Specialist',
        'gerência'    => 'Manager', 'gerencia'    => 'Manager',
        'coordenação' => 'Coordinator', 'coordenacao' => 'Coordinator',
        'estágio'     => 'Intern', 'estagio'     => 'Intern',
    ];

    /** @return LinkedInQuery[] */
    public function build(LinkedInSearchInput $input): array
    {
        $queries = [
            $this->buildBroad($input),
            $this->buildBalanced($input),
            $this->buildPrecise($input),
        ];

        if ($this->wantsRemoteOrInternational($input->workModes)) {
            $queries[] = $this->buildRecruiter($input);
        }

        return $queries;
    }

    private function buildBroad(LinkedInSearchInput $input): LinkedInQuery
    {
        $synonyms = [];
        foreach ($input->titles as $title) {
            $synonyms = array_merge($synonyms, SynonymMap::forTitle($title));
        }
        if ($input->niche) {
            $synonyms = array_merge($synonyms, SynonymMap::forNiche($input->niche));
        }

        $titles        = $this->normalize(array_merge($input->titles, $synonyms));
        $skills        = $this->normalize($input->skills);
        $work          = $this->normalize(array_merge(
            $this->expandWorkModes($input->workModes),
            $this->langWorkTerms($input->language),
            $input->locations,
        ));

        $groups = array_filter([$titles ? $this->orGroup($titles) : null,
                                $skills ? $this->orGroup($skills) : null,
                                $work   ? $this->orGroup($work)   : null]);
        $queryStr = $this->andGroups(array_values($groups));
        $queryStr = $this->appendNot($queryStr, $input->excludedTerms);

        return new LinkedInQuery(
            type: 'broad',
            label: 'Busca Ampla',
            objective: 'Boa para explorar o mercado com o maior volume de oportunidades.',
            query: $queryStr,
            linkedinJobsUrl: $this->jobsUrl($queryStr),
            linkedinPostsUrl: $this->postsUrl($queryStr),
            tip: 'Comece por aqui para ter uma visão geral do mercado. Os resultados serão mais variados.',
            warning: null,
        );
    }

    private function buildBalanced(LinkedInSearchInput $input): LinkedInQuery
    {
        $titles  = $this->normalize($input->titles);
        $skills  = $this->normalize(array_slice($input->skills, 0, 3));
        $work    = $this->normalize(array_merge(
            $this->expandWorkModes($input->workModes),
            $input->locations,
        ));

        $groups = array_filter([$titles ? $this->orGroup($titles) : null,
                                $skills ? $this->orGroup($skills) : null,
                                $work   ? $this->orGroup($work)   : null]);
        $queryStr = $this->andGroups(array_values($groups));
        $queryStr = $this->appendNot($queryStr, $input->excludedTerms);

        return new LinkedInQuery(
            type: 'balanced',
            label: 'Busca Equilibrada',
            objective: 'Equilibra volume e precisão para encontrar vagas com boa aderência.',
            query: $queryStr,
            linkedinJobsUrl: $this->jobsUrl($queryStr),
            linkedinPostsUrl: $this->postsUrl($queryStr),
            tip: 'Boa escolha para o dia a dia. Combina seus termos principais sem abrir demais os resultados.',
            warning: null,
        );
    }

    private function buildPrecise(LinkedInSearchInput $input): LinkedInQuery
    {
        $seniority = $this->primarySeniority($input->seniorities);
        $titles    = $this->normalize(
            array_map(fn($t) => $seniority ? "$seniority $t" : $t, $input->titles)
        );
        $topSkills = $this->normalize(array_slice($input->skills, 0, 3));
        $work      = $this->normalize($this->expandWorkModes($input->workModes));

        $groups = [];
        if ($titles) {
            $groups[] = $this->orGroup($titles);
        }
        if ($topSkills) {
            // Skills connected by AND (not OR) for higher precision
            $groups[] = '(' . implode(' AND ', array_map(fn($s) => '"' . $s . '"', $topSkills)) . ')';
        }
        if ($work) {
            $groups[] = $this->orGroup($work);
        }

        $queryStr = $this->andGroups($groups);
        $queryStr = $this->appendNot($queryStr, $input->excludedTerms);

        $andCount = substr_count($queryStr, ' AND ');
        $warning  = $andCount >= 4
            ? 'Essa busca pode retornar poucos resultados. Tente remover uma habilidade se o volume for escasso.'
            : null;

        return new LinkedInQuery(
            type: 'precise',
            label: 'Busca Precisa',
            objective: 'Para encontrar vagas muito aderentes ao seu perfil específico.',
            query: $queryStr,
            linkedinJobsUrl: $this->jobsUrl($queryStr),
            linkedinPostsUrl: $this->postsUrl($queryStr),
            tip: 'Use quando quiser filtrar vagas bem específicas. Se retornar poucos resultados, tente a busca equilibrada.',
            warning: $warning,
        );
    }

    private function buildRecruiter(LinkedInSearchInput $input): LinkedInQuery
    {
        $hiring  = match ($input->language) {
            'pt'    => self::HIRING_PT,
            'en'    => self::HIRING_EN,
            default => array_merge(self::HIRING_PT, self::HIRING_EN),
        };
        $skills  = $this->normalize(array_slice($input->skills, 0, 2));
        $work    = $this->normalize($this->expandWorkModes($input->workModes));

        $groups = array_filter([$this->orGroup($hiring),
                                $skills ? $this->orGroup($skills) : null,
                                $work   ? $this->orGroup($work)   : null]);
        $queryStr = $this->andGroups(array_values($groups));

        return new LinkedInQuery(
            type: 'recruiter',
            label: 'Busca por Recrutadores',
            objective: 'Encontra posts de recrutadores divulgando vagas diretamente no feed do LinkedIn.',
            query: $queryStr,
            linkedinJobsUrl: $this->jobsUrl($queryStr),
            linkedinPostsUrl: $this->postsUrl($queryStr),
            tip: 'Use o botão "Abrir no LinkedIn Posts" para buscar no feed, não em vagas. Recrutadores costumam postar oportunidades informalmente.',
            warning: null,
        );
    }

    // ── Primitives ──────────────────────────────────────────────────────────

    /** @param string[] $terms */
    private function orGroup(array $terms): string
    {
        $quoted = array_map(fn($t) => '"' . str_replace('"', "'", $t) . '"', $terms);
        return '(' . implode(' OR ', $quoted) . ')';
    }

    /** @param string[] $groups */
    private function andGroups(array $groups): string
    {
        return implode(' AND ', $groups);
    }

    /**
     * @param  string[] $terms
     * @return string[]
     */
    private function normalize(array $terms): array
    {
        $seen   = [];
        $result = [];
        foreach ($terms as $term) {
            $clean = trim((string) preg_replace('/\s+/', ' ', $term));
            if ($clean === '') {
                continue;
            }
            $key = mb_strtolower($clean);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $result[]   = $clean;
        }
        return $result;
    }

    /**
     * @param  string[] $workModes
     * @return string[]
     */
    private function expandWorkModes(array $workModes): array
    {
        $terms = [];
        foreach ($workModes as $mode) {
            $key   = mb_strtolower(trim($mode));
            $terms = array_merge($terms, self::WORK_MODE_MAP[$key] ?? [$mode]);
        }
        return array_unique($terms);
    }

    /** @param string[] $workModes */
    private function wantsRemoteOrInternational(array $workModes): bool
    {
        $remote = ['remoto', 'internacional', 'international', 'remote'];
        foreach ($workModes as $mode) {
            if (in_array(mb_strtolower(trim($mode)), $remote, true)) {
                return true;
            }
        }
        return false;
    }

    /** @return string[] */
    private function langWorkTerms(string $language): array
    {
        return match ($language) {
            'pt'    => self::LANG_WORK_PT,
            'en'    => self::LANG_WORK_EN,
            default => array_merge(self::LANG_WORK_PT, self::LANG_WORK_EN),
        };
    }

    /** @param string[] $seniorities */
    private function primarySeniority(array $seniorities): string
    {
        foreach ($seniorities as $s) {
            $key = mb_strtolower(trim($s));
            if (isset(self::SENIORITY_MAP[$key])) {
                return self::SENIORITY_MAP[$key];
            }
        }
        return '';
    }

    /** @param string[] $excluded */
    private function appendNot(string $query, array $excluded): string
    {
        $terms = $this->normalize($excluded);
        if (empty($terms)) {
            return $query;
        }
        return $query . ' NOT ' . $this->orGroup($terms);
    }

    private function jobsUrl(string $query): string
    {
        return 'https://www.linkedin.com/jobs/search/?keywords=' . urlencode($query);
    }

    private function postsUrl(string $query): string
    {
        return 'https://www.linkedin.com/search/results/content/?keywords=' . urlencode($query);
    }
}
```

- [ ] **Step 4: Run unit tests — all must pass**

```bash
cd "/home/joey/Documentos/Personal Projects/vyrko" && php artisan test tests/Unit/LinkedInBooleanQueryBuilderTest.php
```

Expected: 11 tests, 11 passed.

- [ ] **Step 5: Commit**

```bash
git add app/Services/LinkedInSearch/LinkedInBooleanQueryBuilder.php tests/Unit/LinkedInBooleanQueryBuilderTest.php
git commit -m "feat: implement LinkedInBooleanQueryBuilder with 11 unit tests"
```

---

## Task 3: HTTP Layer — Request, Controller, Routes (TDD)

**Files:**
- Create: `tests/Feature/LinkedInSearchControllerTest.php`
- Create: `app/Http/Requests/LinkedInSearch/LinkedInSearchRequest.php`
- Create: `app/Http/Controllers/LinkedInSearchController.php`
- Modify: `routes/web.php`

**Interfaces:**
- Consumes: `LinkedInBooleanQueryBuilder::build()`, `LinkedInSearchInput::fromArray()` from Tasks 1–2
- Produces: route names `linkedin-search.index` (GET) and `linkedin-search.generate` (POST)

---

- [ ] **Step 1: Write feature tests (they will fail — routes don't exist yet)**

Create `tests/Feature/LinkedInSearchControllerTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LinkedInSearchControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_nao_autenticado_redireciona_para_login(): void
    {
        $this->get(route('linkedin-search.index'))
            ->assertRedirect(route('login'));
    }

    public function test_usuario_logado_sem_email_verificado_acessa_pagina(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $this->actingAs($user)
            ->get(route('linkedin-search.index'))
            ->assertOk();
    }

    public function test_usuario_gratuito_pode_usar_feature(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('linkedin-search.generate'), [
                'titles'      => ['Backend Developer'],
                'skills'      => ['PHP', 'Laravel'],
                'seniorities' => [],
                'work_modes'  => ['Remoto'],
                'locations'   => [],
                'language'    => 'both',
                'excluded'    => [],
            ])
            ->assertOk()
            ->assertJsonStructure(['queries']);
    }

    public function test_post_sem_cargo_nem_habilidade_retorna_422(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('linkedin-search.generate'), [
                'titles'   => [],
                'skills'   => [],
                'language' => 'pt',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['titles']);
    }

    public function test_post_valido_retorna_ao_menos_3_queries(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('linkedin-search.generate'), [
                'titles'      => ['Product Manager'],
                'skills'      => ['SaaS', 'Roadmap'],
                'seniorities' => [],
                'work_modes'  => ['Remoto'],
                'locations'   => ['Brazil'],
                'language'    => 'en',
                'excluded'    => [],
            ])
            ->assertOk();

        $this->assertGreaterThanOrEqual(3, count($response->json('queries')));
    }

    public function test_cada_query_retornada_tem_campos_obrigatorios(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('linkedin-search.generate'), [
                'titles'   => ['Developer'],
                'skills'   => ['PHP'],
                'language' => 'en',
            ])
            ->assertOk();

        foreach ($response->json('queries') as $query) {
            $this->assertArrayHasKey('type', $query);
            $this->assertArrayHasKey('label', $query);
            $this->assertArrayHasKey('objective', $query);
            $this->assertArrayHasKey('query', $query);
            $this->assertArrayHasKey('linkedinJobsUrl', $query);
            $this->assertArrayHasKey('linkedinPostsUrl', $query);
            $this->assertArrayHasKey('tip', $query);
        }
    }
}
```

- [ ] **Step 2: Run tests — confirm all 6 fail**

```bash
cd "/home/joey/Documentos/Personal Projects/vyrko" && php artisan test tests/Feature/LinkedInSearchControllerTest.php
```

Expected: route not found / class not found errors.

- [ ] **Step 3: Create `LinkedInSearchRequest`**

Create `app/Http/Requests/LinkedInSearch/LinkedInSearchRequest.php`:

```php
<?php

namespace App\Http\Requests\LinkedInSearch;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LinkedInSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'titles'        => ['array', 'max:10'],
            'titles.*'      => ['string', 'max:80'],
            'skills'        => ['array', 'max:15'],
            'skills.*'      => ['string', 'max:80'],
            'seniorities'   => ['array'],
            'seniorities.*' => ['string', 'max:50'],
            'work_modes'    => ['array'],
            'work_modes.*'  => ['string', 'max:50'],
            'locations'     => ['array', 'max:8'],
            'locations.*'   => ['string', 'max:80'],
            'language'      => ['required', Rule::in(['pt', 'en', 'both'])],
            'excluded'      => ['array', 'max:10'],
            'excluded.*'    => ['string', 'max:80'],
            'niche'         => ['nullable', 'string', 'max:50'],
        ];
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $v): void {
            $titles = array_filter((array) $this->input('titles', []));
            $skills = array_filter((array) $this->input('skills', []));

            if (empty($titles) && empty($skills)) {
                $v->errors()->add('titles', 'Adicione pelo menos um cargo ou uma habilidade.');
            }
        });
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'language.required' => 'Selecione o idioma das vagas.',
            'language.in'       => 'Idioma inválido.',
        ];
    }
}
```

- [ ] **Step 4: Create `LinkedInSearchController`**

Create `app/Http/Controllers/LinkedInSearchController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\LinkedInSearch\LinkedInSearchRequest;
use App\Services\LinkedInSearch\LinkedInBooleanQueryBuilder;
use App\Services\LinkedInSearch\LinkedInSearchInput;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class LinkedInSearchController extends Controller
{
    public function index(): View
    {
        return view('linkedin-search.index');
    }

    public function generate(
        LinkedInSearchRequest $request,
        LinkedInBooleanQueryBuilder $builder,
    ): JsonResponse {
        $input   = LinkedInSearchInput::fromArray($request->validated());
        $queries = $builder->build($input);

        return response()->json([
            'queries' => array_map(fn($q) => $q->toArray(), $queries),
        ]);
    }
}
```

- [ ] **Step 5: Add routes to `routes/web.php`**

In `routes/web.php`, add inside the existing `Route::middleware('auth')->group(function (): void {` block (after the `account.*` routes, before the closing brace):

```php
use App\Http\Controllers\LinkedInSearchController;

// Add this import at the top of the file with the other use statements:
// use App\Http\Controllers\LinkedInSearchController;

// Add these two routes inside the auth middleware group:
Route::get('/linkedin-search-builder', [LinkedInSearchController::class, 'index'])
    ->name('linkedin-search.index');
Route::post('/linkedin-search-builder', [LinkedInSearchController::class, 'generate'])
    ->name('linkedin-search.generate');
```

- [ ] **Step 6: Run feature tests — all 6 must pass**

```bash
cd "/home/joey/Documentos/Personal Projects/vyrko" && php artisan test tests/Feature/LinkedInSearchControllerTest.php
```

Expected: 6 tests, 6 passed.

- [ ] **Step 7: Confirm existing tests still pass**

```bash
cd "/home/joey/Documentos/Personal Projects/vyrko" && php artisan test tests/Feature/VyrkoMvpTest.php
```

Expected: all existing tests pass (no regressions).

- [ ] **Step 8: Commit**

```bash
git add app/Http/Requests/LinkedInSearch/ app/Http/Controllers/LinkedInSearchController.php routes/web.php tests/Feature/LinkedInSearchControllerTest.php
git commit -m "feat: add LinkedInSearchController, FormRequest, and routes with feature tests"
```

---

## Task 4: View — Page, Form, Cards, JS

**Files:**
- Create: `resources/views/linkedin-search/index.blade.php`
- Modify: `resources/css/app.css`

**Interfaces:**
- Consumes: route names `linkedin-search.index`, `linkedin-search.generate` from Task 3
- Produces: working page at `/linkedin-search-builder` with form, empty state, result cards, and copy buttons

---

- [ ] **Step 1: Add CSS classes to `app.css`**

In `resources/css/app.css`, append the following after the last existing CSS block (before any closing `@layer` if present — if no `@layer`, just append at the end):

```css
/* ── LinkedIn Search Builder ─────────────────────────────────── */
.tags-container {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.tags-display {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    min-height: 28px;
}
.tag-chip {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: var(--vscode-panel);
    border: 1px solid var(--vscode-border);
    border-radius: 9999px;
    padding: 2px 10px;
    font-size: 0.8rem;
    color: var(--vscode-text);
}
.tag-chip button {
    background: none;
    border: none;
    color: var(--vscode-muted);
    cursor: pointer;
    padding: 0;
    line-height: 1;
    font-size: 1rem;
}
.tag-chip button:hover {
    color: var(--vscode-danger);
}
.tags-input {
    background: var(--vscode-bg);
    border: 1px solid var(--vscode-border);
    border-radius: 8px;
    color: var(--vscode-text);
    padding: 8px 12px;
    font-size: 0.9rem;
    outline: none;
    width: 100%;
    box-sizing: border-box;
}
.tags-input:focus {
    border-color: var(--vscode-blue);
}
.checkbox-group {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}
.checkbox-label {
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    font-size: 0.9rem;
    color: var(--vscode-text);
}
.query-block {
    font-family: var(--font-mono);
    font-size: 0.82rem;
    background: var(--vscode-bg);
    border: 1px solid var(--vscode-border);
    border-radius: 8px;
    padding: 12px 14px;
    white-space: pre-wrap;
    word-break: break-word;
    color: var(--vscode-success);
    line-height: 1.6;
    margin: 0;
}
.query-card-header {
    display: flex;
    align-items: center;
    gap: 10px;
}
.query-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.query-warning {
    font-size: 0.82rem;
    color: var(--vscode-warning);
    background: rgba(220, 220, 170, 0.08);
    border: 1px solid rgba(220, 220, 170, 0.2);
    border-radius: 6px;
    padding: 6px 10px;
    margin: 0;
}
.badge-free {
    font-size: 0.7rem;
    font-weight: 700;
    background: rgba(78, 201, 176, 0.15);
    color: var(--vscode-success);
    border: 1px solid rgba(78, 201, 176, 0.3);
    border-radius: 9999px;
    padding: 2px 8px;
    letter-spacing: 0.03em;
}
.search-disclaimer {
    font-size: 0.78rem;
    color: var(--vscode-muted);
    line-height: 1.5;
}
@media (max-width: 768px) {
    .query-actions {
        flex-direction: column;
    }
    .query-actions a,
    .query-actions button {
        width: 100%;
        text-align: center;
    }
}
```

- [ ] **Step 2: Create the view**

Create `resources/views/linkedin-search/index.blade.php`:

```blade
@extends('layouts.app')

@section('meta_title', 'LinkedIn Search Builder — Vyrko')
@section('meta_description', 'Gere buscas booleanas prontas para encontrar vagas no LinkedIn. Gratuito, sem IA, sem scraping.')

@section('content')
    @php $en = app()->getLocale() === 'en'; @endphp

    <x-ui.page-header
        :eyebrow="'LinkedIn Search Builder'"
        :title="$en ? 'Ready searches to find better jobs on LinkedIn.' : 'Buscas prontas para encontrar vagas melhores no LinkedIn.'"
        :subtitle="$en ? 'Enter job titles, skills, and preferences. Vyrko builds boolean queries for you to copy and use on LinkedIn.' : 'Informe cargos, habilidades e preferências. O Vyrko monta buscas booleanas para você copiar e pesquisar no LinkedIn.'"
    >
        <x-slot:actions>
            <span class="badge-free">{{ $en ? 'Free · No AI · No scraping' : 'Grátis · Sem IA · Sem scraping' }}</span>
        </x-slot:actions>
    </x-ui.page-header>

    <p class="search-disclaimer" style="margin-bottom: 1.5rem;">
        {{ $en
            ? 'This tool does not access your LinkedIn account. It only builds search strings for you to use manually.'
            : 'Essa ferramenta não acessa sua conta do LinkedIn. Ela apenas monta buscas para você usar manualmente.' }}
    </p>

    <div class="content-grid wide-aside" id="search-layout">

        {{-- ── LEFT: Form ──────────────────────────────────────────────── --}}
        <form class="card stack-lg" id="search-builder-form"
              action="{{ route('linkedin-search.generate') }}"
              method="POST" novalidate>
            @csrf

            <div class="panel-title">
                <div>
                    <p class="eyebrow">{{ $en ? 'Search parameters' : 'Parâmetros da busca' }}</p>
                    <h2>{{ $en ? 'Fill in your preferences' : 'Preencha suas preferências' }}</h2>
                </div>
            </div>

            {{-- Cargo / área --}}
            <div class="field">
                <label for="titles-input">{{ $en ? 'Job title or area' : 'Cargo ou área' }}</label>
                <div class="tags-container" data-tags-container data-field-name="titles">
                    <div class="tags-display" data-tags-display></div>
                    <input class="tags-input" id="titles-input" type="text" data-tags-input
                           placeholder="{{ $en ? 'e.g. Backend Developer, Product Manager' : 'Ex: Backend Developer, Product Manager' }}"
                           autocomplete="off">
                    <div data-tags-hidden></div>
                </div>
                <p class="form-help">{{ $en ? 'Add role variations to broaden the search.' : 'Adicione variações do cargo para ampliar a busca.' }}</p>
            </div>

            {{-- Habilidades --}}
            <div class="field">
                <label for="skills-input">{{ $en ? 'Key skills' : 'Habilidades principais' }}</label>
                <div class="tags-container" data-tags-container data-field-name="skills">
                    <div class="tags-display" data-tags-display></div>
                    <input class="tags-input" id="skills-input" type="text" data-tags-input
                           placeholder="{{ $en ? 'e.g. PHP, Laravel, MySQL, Docker' : 'Ex: PHP, Laravel, MySQL, Docker' }}"
                           autocomplete="off">
                    <div data-tags-hidden></div>
                </div>
                <p class="form-help">{{ $en ? 'Use technologies, tools, or skills that appear in desired jobs.' : 'Use tecnologias, ferramentas ou competências que aparecem nas vagas desejadas.' }}</p>
            </div>

            {{-- Senioridade --}}
            <div class="field">
                <label>{{ $en ? 'Seniority' : 'Senioridade' }}</label>
                <div class="checkbox-group">
                    @foreach([
                        ['value' => 'Estágio',      'label' => $en ? 'Intern'       : 'Estágio'],
                        ['value' => 'Júnior',       'label' => $en ? 'Junior'       : 'Júnior'],
                        ['value' => 'Pleno',        'label' => $en ? 'Mid-level'    : 'Pleno'],
                        ['value' => 'Sênior',       'label' => $en ? 'Senior'       : 'Sênior'],
                        ['value' => 'Lead',         'label' => 'Lead'],
                        ['value' => 'Especialista', 'label' => $en ? 'Specialist'   : 'Especialista'],
                        ['value' => 'Coordenação',  'label' => $en ? 'Coordinator'  : 'Coordenação'],
                        ['value' => 'Gerência',     'label' => $en ? 'Manager'      : 'Gerência'],
                    ] as $opt)
                        <label class="checkbox-label">
                            <input type="checkbox" name="seniorities[]" value="{{ $opt['value'] }}">
                            {{ $opt['label'] }}
                        </label>
                    @endforeach
                </div>
                <p class="form-help">{{ $en ? 'Leave blank to not filter by seniority.' : 'Deixe em branco para não filtrar por senioridade.' }}</p>
            </div>

            {{-- Modelo de trabalho --}}
            <div class="field">
                <label>{{ $en ? 'Work model' : 'Modelo de trabalho' }}</label>
                <div class="checkbox-group">
                    @foreach([
                        ['value' => 'Remoto',        'label' => $en ? 'Remote'        : 'Remoto'],
                        ['value' => 'Híbrido',       'label' => $en ? 'Hybrid'        : 'Híbrido'],
                        ['value' => 'Presencial',    'label' => $en ? 'On-site'       : 'Presencial'],
                        ['value' => 'Internacional', 'label' => $en ? 'International' : 'Internacional'],
                        ['value' => 'Freelance',     'label' => 'Freelance'],
                        ['value' => 'PJ',            'label' => 'PJ'],
                        ['value' => 'CLT',           'label' => 'CLT'],
                        ['value' => 'Contrato',      'label' => $en ? 'Contract'      : 'Contrato'],
                    ] as $opt)
                        <label class="checkbox-label">
                            <input type="checkbox" name="work_modes[]" value="{{ $opt['value'] }}">
                            {{ $opt['label'] }}
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Localização --}}
            <div class="field">
                <label for="locations-input">{{ $en ? 'Location' : 'Localização' }}</label>
                <div class="tags-container" data-tags-container data-field-name="locations">
                    <div class="tags-display" data-tags-display></div>
                    <input class="tags-input" id="locations-input" type="text" data-tags-input
                           placeholder="{{ $en ? 'e.g. Brazil, São Paulo, Portugal, Europe' : 'Ex: Brasil, São Paulo, Portugal, Europe' }}"
                           autocomplete="off">
                    <div data-tags-hidden></div>
                </div>
            </div>

            {{-- Idioma --}}
            <div class="field">
                <label for="language">{{ $en ? 'Job language' : 'Idioma das vagas' }}</label>
                <select name="language" id="language">
                    <option value="both" selected>{{ $en ? 'Both (PT + EN)' : 'Ambos (PT + EN)' }}</option>
                    <option value="pt">{{ $en ? 'Portuguese' : 'Português' }}</option>
                    <option value="en">{{ $en ? 'English' : 'Inglês' }}</option>
                </select>
            </div>

            {{-- Termos a evitar --}}
            <div class="field">
                <label for="excluded-input">{{ $en ? 'Terms to avoid' : 'Termos para evitar' }}</label>
                <div class="tags-container" data-tags-container data-field-name="excluded">
                    <div class="tags-display" data-tags-display></div>
                    <input class="tags-input" id="excluded-input" type="text" data-tags-input
                           placeholder="{{ $en ? 'e.g. unpaid, on-site, telemarketing' : 'Ex: estágio, presencial, telemarketing' }}"
                           autocomplete="off">
                    <div data-tags-hidden></div>
                </div>
                <p class="form-help">{{ $en ? 'Removes results that do not make sense for you.' : 'Remove resultados que não fazem sentido para você.' }}</p>
            </div>

            {{-- Nicho --}}
            <div class="field">
                <label for="niche">{{ $en ? 'Area (optional)' : 'Área (opcional)' }}</label>
                <select name="niche" id="niche">
                    <option value="">{{ $en ? 'Select...' : 'Selecione...' }}</option>
                    @foreach([
                        'tecnologia'    => $en ? 'Technology'      : 'Tecnologia',
                        'dados'         => $en ? 'Data'            : 'Dados',
                        'produto'       => $en ? 'Product'         : 'Produto',
                        'design'        => 'Design',
                        'marketing'     => 'Marketing',
                        'vendas'        => $en ? 'Sales'           : 'Vendas',
                        'administrativo'=> $en ? 'Administrative'  : 'Administrativo',
                        'financeiro'    => $en ? 'Finance'         : 'Financeiro',
                        'rh'            => $en ? 'HR'              : 'RH',
                        'suporte'       => $en ? 'Support'         : 'Suporte',
                    ] as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <p class="form-help">{{ $en ? 'Helps expand synonyms for the broad search.' : 'Ajuda a expandir sinônimos na busca ampla.' }}</p>
            </div>

            <div id="form-error" class="alert error" style="display:none;"></div>

            <div class="actions">
                <button class="btn" type="submit" data-submit-btn>
                    {{ $en ? 'Generate searches' : 'Gerar buscas' }}
                </button>
            </div>
        </form>

        {{-- ── RIGHT: Results ──────────────────────────────────────────── --}}
        <div id="results-panel">
            <x-ui.empty-state
                :title="$en ? 'Your searches will appear here.' : 'Suas buscas aparecerão aqui.'"
                :description="$en
                    ? 'Fill in a job title or skill and click Generate searches to get at least 3 ready queries.'
                    : 'Preencha um cargo ou habilidade e clique em Gerar buscas para receber ao menos 3 queries prontas.'"
                :cta="null"
            />
        </div>
    </div>

    {{-- ── Tips ───────────────────────────────────────────────────────── --}}
    <section class="card stack" style="margin-top: 2rem;">
        <p class="eyebrow">{{ $en ? 'How to use it better' : 'Como usar melhor' }}</p>
        <h3>{{ $en ? 'Tips for getting the best results' : 'Dicas para obter melhores resultados' }}</h3>
        <ol class="stack" style="padding-left: 1.2rem;">
            @foreach($en ? [
                'Start with the broad search to explore the market.',
                'Use the balanced search to find jobs with good fit.',
                'Use the precise search when you want very specific opportunities.',
                'If bad results appear, add terms to "avoid".',
                'If there are too few results, remove some required skills.',
            ] : [
                'Comece pela busca ampla para explorar o mercado.',
                'Use a busca equilibrada para encontrar vagas com boa aderência.',
                'Use a busca precisa quando quiser filtrar oportunidades muito específicas.',
                'Se aparecerem vagas ruins, adicione termos em "evitar".',
                'Se houver poucos resultados, remova algumas habilidades obrigatórias.',
            ] as $tip)
                <li><p>{{ $tip }}</p></li>
            @endforeach
        </ol>
        <p class="search-disclaimer">
            {{ $en
                ? 'LinkedIn may interpret searches differently depending on the area, language, and filters applied. Adjust the query based on results. Vyrko does not collect LinkedIn results or submit applications automatically.'
                : 'O LinkedIn pode interpretar buscas de forma diferente dependendo da área, idioma e filtros aplicados. Ajuste a query conforme os resultados. O Vyrko não coleta resultados do LinkedIn e não envia candidaturas automaticamente.' }}
        </p>
    </section>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    // ── Tag input ──────────────────────────────────────────────────────
    function initTagsContainer(container) {
        const textInput   = container.querySelector('[data-tags-input]');
        const display     = container.querySelector('[data-tags-display]');
        const hiddenWrap  = container.querySelector('[data-tags-hidden]');
        const fieldName   = container.dataset.fieldName;
        const tags        = [];

        function renderTags() {
            display.innerHTML = '';
            hiddenWrap.innerHTML = '';

            tags.forEach(function (tag, i) {
                // chip
                const chip = document.createElement('span');
                chip.className = 'tag-chip';
                chip.innerHTML = tag + ' <button type="button" data-idx="' + i + '" aria-label="Remover ' + tag + '">×</button>';
                display.appendChild(chip);

                // hidden input
                const inp = document.createElement('input');
                inp.type  = 'hidden';
                inp.name  = fieldName + '[]';
                inp.value = tag;
                hiddenWrap.appendChild(inp);
            });
        }

        function addTag(value) {
            const clean = value.trim().replace(/,+$/, '').trim();
            if (!clean) return;
            const lower = clean.toLowerCase();
            if (tags.some(function (t) { return t.toLowerCase() === lower; })) return;
            tags.push(clean);
            renderTags();
        }

        textInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                addTag(textInput.value);
                textInput.value = '';
            }
        });

        display.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-idx]');
            if (!btn) return;
            tags.splice(parseInt(btn.dataset.idx, 10), 1);
            renderTags();
        });

        // Flush text input on form submit so user doesn't lose a typed-but-not-confirmed tag
        container.closest('form').addEventListener('submit', function () {
            if (textInput.value.trim()) {
                addTag(textInput.value);
                textInput.value = '';
            }
        });
    }

    document.querySelectorAll('[data-tags-container]').forEach(initTagsContainer);

    // ── Form submission ────────────────────────────────────────────────
    var form       = document.getElementById('search-builder-form');
    var panel      = document.getElementById('results-panel');
    var submitBtn  = form.querySelector('[data-submit-btn]');
    var formError  = document.getElementById('form-error');

    var typeLabels = {
        broad:     '{{ $en ? "Broad" : "Ampla" }}',
        balanced:  '{{ $en ? "Balanced" : "Equilibrada" }}',
        precise:   '{{ $en ? "Precise" : "Precisa" }}',
        recruiter: '{{ $en ? "Recruiters" : "Recrutadores" }}',
    };

    var copyLabel   = '{{ $en ? "Copy query"  : "Copiar query" }}';
    var copiedLabel = '{{ $en ? "Copied!"     : "Copiado!" }}';
    var jobsLabel   = '{{ $en ? "Open in LinkedIn Jobs"  : "Abrir no LinkedIn Jobs" }}';
    var postsLabel  = '{{ $en ? "LinkedIn Posts" : "LinkedIn Posts" }}';

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        formError.style.display = 'none';

        var original = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = '{{ $en ? "Generating..." : "Gerando..." }}';

        try {
            var resp = await fetch(form.action, {
                method:  'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept':       'application/json',
                },
                body: new FormData(form),
            });

            var json = await resp.json();

            if (!resp.ok) {
                var errors = json.errors || {};
                var first  = Object.values(errors).flat()[0] || '{{ $en ? "Error generating searches." : "Erro ao gerar buscas." }}';
                formError.textContent   = first;
                formError.style.display = '';
                return;
            }

            renderResults(json.queries);

        } catch (err) {
            formError.textContent   = '{{ $en ? "Connection error. Try again." : "Erro de conexão. Tente novamente." }}';
            formError.style.display = '';
        } finally {
            submitBtn.disabled    = false;
            submitBtn.textContent = original;
        }
    });

    function renderResults(queries) {
        panel.innerHTML = queries.map(renderCard).join('');

        panel.querySelectorAll('[data-copy-btn]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var text = btn.closest('[data-query-card]').querySelector('[data-query-text]').textContent;
                navigator.clipboard.writeText(text).then(function () {
                    var orig = btn.textContent;
                    btn.textContent = copiedLabel;
                    setTimeout(function () { btn.textContent = orig; }, 2000);
                }).catch(function () {
                    // fallback for older browsers
                    var ta = document.createElement('textarea');
                    ta.value = text;
                    ta.style.position = 'fixed';
                    ta.style.opacity  = '0';
                    document.body.appendChild(ta);
                    ta.select();
                    document.execCommand('copy');
                    document.body.removeChild(ta);
                    btn.textContent = copiedLabel;
                    setTimeout(function () { btn.textContent = copyLabel; }, 2000);
                });
            });
        });
    }

    function renderCard(q) {
        var warning = q.warning
            ? '<p class="query-warning">' + escHtml(q.warning) + '</p>'
            : '';

        return '<article class="card stack" data-query-card style="margin-bottom:1rem;">'
            + '<div class="query-card-header">'
            + '<span class="badge">' + escHtml(typeLabels[q.type] || q.type) + '</span>'
            + '<strong>' + escHtml(q.label) + '</strong>'
            + '</div>'
            + '<p class="muted">' + escHtml(q.objective) + '</p>'
            + '<pre class="query-block" data-query-text>' + escHtml(q.query) + '</pre>'
            + warning
            + '<div class="query-actions">'
            + '<button class="btn secondary" type="button" data-copy-btn>' + copyLabel + '</button>'
            + '<a class="btn secondary" href="' + escAttr(q.linkedinJobsUrl) + '" target="_blank" rel="noopener noreferrer">' + jobsLabel + '</a>'
            + '<a class="btn ghost" href="' + escAttr(q.linkedinPostsUrl) + '" target="_blank" rel="noopener noreferrer">' + postsLabel + '</a>'
            + '</div>'
            + '<p class="form-help">💡 ' + escHtml(q.tip) + '</p>'
            + '</article>';
    }

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function escAttr(str) {
        return String(str).replace(/"/g, '&quot;');
    }
}());
</script>
@endpush
```

- [ ] **Step 3: Confirm page loads correctly**

```bash
cd "/home/joey/Documentos/Personal Projects/vyrko" && php artisan route:list | grep linkedin-search
```

Expected: two entries — `GET linkedin-search-builder` and `POST linkedin-search-builder`.

- [ ] **Step 4: Commit**

```bash
git add resources/views/linkedin-search/ resources/css/app.css
git commit -m "feat: add LinkedIn Search Builder view with tags input and result cards"
```

---

## Task 5: Navigation Link

**Files:**
- Modify: `resources/views/layouts/app.blade.php`

**Interfaces:**
- Consumes: route name `linkedin-search.index` from Task 3

---

- [ ] **Step 1: Add nav link in authenticated nav**

In `resources/views/layouts/app.blade.php`, find this block:

```blade
<a class="navlink @if(request()->routeIs('linkedin.*')) is-active @endif" href="{{ route('linkedin.index') }}">{{ __('messages.nav.linkedin') }}</a>
```

Replace it with:

```blade
<a class="navlink @if(request()->routeIs('linkedin.*')) is-active @endif" href="{{ route('linkedin.index') }}">{{ __('messages.nav.linkedin') }}</a>
<a class="navlink @if(request()->routeIs('linkedin-search.*')) is-active @endif" href="{{ route('linkedin-search.index') }}">
    {{ app()->getLocale() === 'en' ? 'Search Builder' : 'Search Builder' }}
    <span class="badge-free" style="margin-left:4px;">{{ app()->getLocale() === 'en' ? 'Free' : 'Grátis' }}</span>
</a>
```

- [ ] **Step 2: Run full test suite to confirm no regressions**

```bash
cd "/home/joey/Documentos/Personal Projects/vyrko" && php artisan test
```

Expected: all tests pass (unit + feature, old and new).

- [ ] **Step 3: Commit**

```bash
git add resources/views/layouts/app.blade.php
git commit -m "feat: add LinkedIn Search Builder link to authenticated nav"
```

---

## Self-Review Against Spec

**Spec coverage check:**

| Spec requirement | Covered |
|-----------------|---------|
| Route `/linkedin-search-builder` | ✅ Task 3 |
| No AI / no credits | ✅ Controller calls no AI service; no UsageLimiter |
| At least 3 queries (broad, balanced, precise) | ✅ Builder always returns 3+; tested |
| Query 4 (recruiter) when remote | ✅ Builder + test 10 |
| Deterministic service `LinkedInBooleanQueryBuilder` | ✅ Task 2 |
| OR grouping | ✅ `orGroup()` primitive |
| AND grouping | ✅ `andGroups()` primitive |
| NOT for excluded terms | ✅ `appendNot()` + test 4 |
| Quotes around terms | ✅ `orGroup()` + test 6 |
| Synonyms via `SynonymMap` | ✅ Task 1, used in `buildBroad()` |
| Language expansion (pt/en/both) | ✅ `langWorkTerms()` + HIRING constants |
| Deduplicate terms | ✅ `normalize()` + test 5 |
| LinkedIn Jobs URL | ✅ `jobsUrl()` + test 9 |
| LinkedIn Posts URL | ✅ `postsUrl()` |
| Copy button | ✅ JS clipboard in view |
| Tags input for multi-value fields | ✅ vanilla JS tags widget |
| Warning on overly precise query | ✅ `buildPrecise()` AND-count check |
| Login required (no verified) | ✅ middleware `auth` only; test 2 confirms unverified user passes |
| Free tier — no plan gate | ✅ test 3 confirms free user access |
| Responsive mobile | ✅ CSS `@media` in `app.css` |
| No new Composer/NPM deps | ✅ zero new dependencies |
| Existing tests pass | ✅ Task 3 Step 7 + Task 5 Step 2 |
| Nav link | ✅ Task 5 |

**Placeholder scan:** None found — all steps have complete code.

**Type consistency:** `LinkedInSearchInput` fields match `fromArray()` key mapping (`work_modes` → `workModes`). `LinkedInQuery::toArray()` keys match JS `renderCard()` property access. `LinkedInBooleanQueryBuilder::build()` signature matches controller call. ✅
