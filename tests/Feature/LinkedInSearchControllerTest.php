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
