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
