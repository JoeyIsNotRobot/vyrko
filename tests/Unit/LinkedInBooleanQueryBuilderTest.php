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
