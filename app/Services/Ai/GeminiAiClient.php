<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use InvalidArgumentException;
use JsonException;

class GeminiAiClient implements AiClient
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function completeJson(string $feature, array $payload): array
    {
        $apiKey = (string) config('ai.api_key');

        if ($apiKey === '') {
            throw new InvalidArgumentException('Configure AI_API_KEY no .env para usar o Gemini.');
        }

        $model = (string) config('ai.model', 'gemini-2.5-flash');
        $endpoint = rtrim((string) config('ai.endpoint', 'https://generativelanguage.googleapis.com/v1beta'), '/');
        $url = "{$endpoint}/models/{$model}:generateContent?key=".urlencode($apiKey);
        $prompt = $this->promptFor($feature, $payload);

        try {
            $response = Http::timeout((int) config('ai.timeout', 60))
                ->retry(2, 500)
                ->acceptJson()
                ->asJson()
                ->post($url, [
                    'systemInstruction' => [
                        'parts' => [
                            ['text' => $this->systemInstruction()],
                        ],
                    ],
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.2,
                        'responseMimeType' => 'application/json',
                    ],
                ]);
        } catch (ConnectionException $exception) {
            throw new InvalidArgumentException('Não foi possível conectar ao Gemini. Tente novamente em instantes.', previous: $exception);
        }

        if ($response->failed()) {
            $message = (string) Arr::get($response->json(), 'error.message', 'O Gemini não conseguiu processar a solicitação.');

            throw new InvalidArgumentException($message);
        }

        $text = $this->extractText($response->json());

        try {
            $decoded = json_decode($this->stripCodeFence($text), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException('O Gemini retornou uma resposta em formato inválido.', previous: $exception);
        }

        if (! is_array($decoded)) {
            throw new InvalidArgumentException('O Gemini retornou uma resposta vazia.');
        }

        return $decoded;
    }

    private function systemInstruction(): string
    {
        return <<<'PROMPT'
Você é o motor de IA do Vyrko, um SaaS para gerar currículos personalizados por vaga.
Regras absolutas:
- Responda somente JSON válido, sem Markdown e sem comentários.
- Nunca invente experiências, empresas, cargos, datas, cursos, certificações, números, métricas ou tecnologias.
- Use apenas os dados enviados no payload.
- Quando faltar evidência, marque como ausente em vez de preencher por suposição.
- Em currículos e textos gerados, reescreva com clareza, mas preserve a verdade factual.
- Retorne arrays vazios quando uma informação não existir.
- IDIOMA: Todo texto gerado (summary, bullets, carta de apresentação, recomendações) deve estar no idioma definido por target_language no payload. Se target_language for "pt_BR", escreva em português brasileiro. Se for "en", escreva em inglês. Nunca misture idiomas no mesmo output.
PROMPT;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function promptFor(string $feature, array $payload): string
    {
        return match ($feature) {
            'job_post_parse' => $this->jobPostParsePrompt($payload),
            'job_match_report' => $this->jobMatchReportPrompt($payload),
            'resume_generate' => $this->resumeGeneratePrompt($payload),
            'ats_checklist' => $this->atsChecklistPrompt($payload),
            'linkedin_analysis' => $this->linkedinAnalysisPrompt($payload),
            'resume_import_parse' => $this->resumeImportParsePrompt($payload),
            default => throw new InvalidArgumentException("Feature de IA não suportada: {$feature}."),
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function jobPostParsePrompt(array $payload): string
    {
        return $this->jsonPrompt('Analise a vaga e extraia requisitos reais do texto.', [
            'schema' => [
                'job_title' => 'string',
                'company_name' => 'string|null',
                'seniority' => 'junior|mid|senior|lead|unknown',
                'required_skills' => ['string'],
                'preferred_skills' => ['string'],
                'responsibilities' => ['string'],
                'soft_skills' => ['string'],
                'keywords' => ['string'],
                'ats_keywords' => ['string'],
                'language_requirements' => ['string'],
                'education_requirements' => ['string'],
                'hidden_signals' => ['string'],
            ],
            'payload' => $payload,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function jobMatchReportPrompt(array $payload): string
    {
        return $this->jsonPrompt(
            <<<'TASK'
Compare a vaga com o inventário do candidato e gere um relatório de match detalhado.

REGRAS DE MATCHING — leia com atenção:

1. MATCHING SEMÂNTICO, NÃO LITERAL
   - "PHP 8", "PHP 8.x", "PHP 8.5" → match se o inventário tiver "PHP" (versão é subset)
   - "RESTful API" e "RESTful APIs" são o mesmo requisito
   - "Node.js" = "NodeJS", "Postgres" = "PostgreSQL", "K8s" = "Kubernetes", etc.
   - Trate variações de nomenclatura, siglas e versões como equivalentes

2. INFERÊNCIA A PARTIR DE EXPERIÊNCIAS
   - Procure evidências em TODOS os campos: skills, experience.responsibilities, experience.technologies, experience.description, achievements
   - Se uma experiência menciona "otimização de queries", "índices", "cache" → conta como evidência para "Database Optimization", "Query Optimization", "Caching"
   - Se o candidato trabalhou com "Laravel" → implica PHP, MVC, backend engineering
   - Se tem experiência construindo APIs REST → conta como evidência para "API Development", "RESTful API", "Backend Engineering"
   - Use o bom senso de um recrutador técnico experiente

3. CLASSIFICAÇÃO DE STATUS
   - strong_match: evidência direta e explícita no inventário (skill nomeada, cargo, responsabilidade direta)
   - medium_match: evidência clara por inferência técnica (framework implica linguagem, responsabilidades implicam skill)
   - partial: evidência fraca ou apenas menção superficial
   - missing: genuinamente sem evidência — não use "missing" para algo que claramente está demonstrado nas experiências

4. EVIDÊNCIAS — IDs OBRIGATÓRIOS
   - Para cada match, referencie os IDs exatos dos itens do inventário enviado (skill.id, experience.id, etc.)
   - Não invente IDs; use apenas IDs que existem no payload.inventory
   - Um item pode ter múltiplas evidências de tipos diferentes (skill + experience)
TASK,
            [
            'allowed_evidence_statuses' => ['strong_match', 'medium_match', 'partial', 'missing'],
            'schema' => [
                'overall_score' => 'integer 0-100',
                'technical_score' => 'integer 0-100',
                'experience_score' => 'integer 0-100',
                'seniority_score' => 'integer 0-100',
                'keyword_score' => 'integer 0-100',
                'ats_format_score' => 'integer 0-100',
                'human_readability_score' => 'integer 0-100',
                'strengths' => ['string'],
                'gaps' => [
                    'critical' => ['string'],
                    'acceptable' => ['string'],
                ],
                'warnings' => ['string'],
                'recommendations' => ['string'],
                'evidence_map' => [
                    'Nome do requisito ou keyword' => [
                        'status' => 'strong_match|medium_match|partial|missing',
                        'evidence' => [
                            ['type' => 'skill|experience|achievement|project|education|certification|language', 'id' => 'integer', 'label' => 'string'],
                        ],
                    ],
                ],
            ],
            'payload' => $payload,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resumeGeneratePrompt(array $payload): string
    {
        $coverLetterInstruction = ($payload['include_cover_letter'] ?? false)
            ? 'Se include_cover_letter for true, gere também uma carta de apresentação personalizada em cover_letter_text. A carta deve ter 3 parágrafos: (1) conexão entre o candidato e a vaga, (2) evidências mais fortes que justificam a candidatura, (3) call-to-action. Escreva no mesmo idioma definido por target_language.'
            : 'include_cover_letter é false — retorne cover_letter_text como null.';

        return $this->jsonPrompt(
            'Gere um currículo direcionado para a vaga usando apenas o inventário e o mapa de evidências aprovadas. Priorize e destaque os pontos fortes do candidato identificados em match_report.strengths e evidence_map com status strong_match ou medium_match. Não inclua tecnologias com status missing. IDIOMA OBRIGATÓRIO: escreva summary, todos os bullets de experiences, títulos de seções e qualquer texto gerado no idioma definido em target_language. Se target_language for "pt_BR", TODOS os textos devem ser em português brasileiro — mesmo que o inventário esteja em inglês, reescreva o conteúdo no idioma correto. Se for "en", escreva em inglês. Nomes de empresas, cargos e tecnologias permanecem como estão no inventário. '.$coverLetterInstruction,
            [
                'schema' => [
                    'title' => 'string',
                    'cover_letter_text' => 'string|null',
                    'content' => [
                        'header' => [
                            'name' => 'string',
                            'headline' => 'string',
                            'location' => 'string',
                            'email' => 'string',
                            'phone' => 'string',
                            'links' => ['string'],
                        ],
                        'summary' => 'string — destaque os pontos mais fortes em relação à vaga no primeiro parágrafo',
                        'skills' => [
                            ['category' => 'string', 'items' => ['string — ordene por relevância para a vaga']],
                        ],
                        'experiences' => [
                            [
                                'company' => 'string',
                                'role' => 'string',
                                'period' => 'string',
                                'bullets' => [
                                    ['text' => 'string — priorize bullets com evidências strong_match', 'evidence' => [['type' => 'string', 'id' => 'integer']]],
                                ],
                            ],
                        ],
                        'projects' => ['array'],
                        'education' => ['array'],
                        'certifications' => ['array'],
                        'languages' => ['array'],
                    ],
                ],
                'payload' => $payload,
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function atsChecklistPrompt(array $payload): string
    {
        return $this->jsonPrompt('Avalie o currículo para ATS e leitura humana. Use mensagens em português quando target_language for pt_BR.', [
            'schema' => [
                'score' => 'integer 0-100',
                'items' => [
                    ['key' => 'string_snake_case', 'status' => 'passed|warning|failed', 'message' => 'string'],
                ],
                'file_format_recommendation' => 'string',
                'warnings' => ['string'],
                'recommendations' => ['string'],
            ],
            'payload' => $payload,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function linkedinAnalysisPrompt(array $payload): string
    {
        return $this->jsonPrompt('Analise o perfil do LinkedIn e sugira melhorias com base no inventário. Reescreva headline e about sem inventar fatos.', [
            'schema' => [
                'score' => 'integer 0-100',
                'strengths' => ['string'],
                'weaknesses' => ['string'],
                'recommendations' => ['string'],
                'rewritten_headline' => 'string|null',
                'rewritten_about' => 'string|null',
            ],
            'payload' => $payload,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resumeImportParsePrompt(array $payload): string
    {
        return $this->jsonPrompt('Extraia dados estruturados do currículo enviado. Regras estritas: (1) Não complete lacunas por inferência — se o texto não disser explicitamente, use null ou array vazio. (2) Não invente dados de contato, datas ou empresas. (3) Não combine campos de seções diferentes. (4) Cada item de array representa exatamente um registro encontrado no texto.', [
            'date_rule' => 'Datas devem vir em YYYY-MM-DD quando houver mês/ano suficiente; caso contrário null.',
            'schema' => [
                'profile' => [
                    'first_name' => 'string|null',
                    'last_name' => 'string|null',
                    'headline' => 'string|null',
                    'location_city' => 'string|null',
                    'location_state' => 'string|null',
                    'location_country' => 'string|null',
                    'email' => 'string|null',
                    'phone' => 'string|null',
                    'linkedin_url' => 'string|null',
                    'github_url' => 'string|null',
                    'portfolio_url' => 'string|null',
                    'summary' => 'string|null',
                    'target_role' => 'string|null',
                    'target_seniority' => 'string|null',
                    'preferred_language' => 'pt_BR|en',
                ],
                'skills' => [
                    ['name' => 'string', 'category' => 'backend|frontend|database|devops|cloud|testing|soft_skill|language|tool|other', 'proficiency_level' => 'string|null', 'years_of_experience' => 'integer|null', 'evidence_notes' => 'string|null'],
                ],
                'experiences' => [
                    ['company_name' => 'string', 'role_title' => 'string', 'employment_type' => 'string|null', 'location' => 'string|null', 'start_date' => 'YYYY-MM-DD|null', 'end_date' => 'YYYY-MM-DD|null', 'is_current' => 'boolean', 'description' => 'string|null', 'responsibilities' => ['string'], 'technologies' => ['string'], 'achievements' => [['title' => 'string', 'description' => 'string', 'impact_metric' => 'string|null', 'evidence_tags' => ['string']]]],
                ],
                'projects' => [
                    ['name' => 'string', 'description' => 'string|null', 'url' => 'string|null', 'start_date' => 'YYYY-MM-DD|null', 'end_date' => 'YYYY-MM-DD|null', 'technologies' => ['string']],
                ],
                'educations' => [
                    ['institution' => 'string', 'degree' => 'string|null', 'field_of_study' => 'string|null', 'start_date' => 'YYYY-MM-DD|null', 'end_date' => 'YYYY-MM-DD|null'],
                ],
                'certifications' => [
                    ['name' => 'string', 'issuer' => 'string|null', 'issued_at' => 'YYYY-MM-DD|null', 'expires_at' => 'YYYY-MM-DD|null', 'credential_url' => 'string|null'],
                ],
                'languages' => [
                    ['language' => 'string', 'proficiency' => 'Nativo|Fluente|Avançado|Intermediário|Básico', 'notes' => 'string|null'],
                ],
                'achievements' => ['array'],
            ],
            'payload' => $payload,
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function jsonPrompt(string $task, array $context): string
    {
        return $task.PHP_EOL.PHP_EOL.'Retorne exatamente um objeto JSON seguindo o schema solicitado.'.PHP_EOL.PHP_EOL.$this->encode($context);
    }

    /**
     * @param  array<string, mixed>  $response
     */
    private function extractText(array $response): string
    {
        $parts = Arr::get($response, 'candidates.0.content.parts', []);

        if (! is_array($parts)) {
            return '';
        }

        return collect($parts)
            ->pluck('text')
            ->filter()
            ->implode("\n");
    }

    private function stripCodeFence(string $text): string
    {
        $text = trim($text);

        if (Str::startsWith($text, '```')) {
            $text = preg_replace('/^```(?:json)?\s*/i', '', $text) ?? $text;
            $text = preg_replace('/\s*```$/', '', $text) ?? $text;
        }

        return trim($text);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function encode(array $payload): string
    {
        try {
            return json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException('Não foi possível preparar o payload para IA.', previous: $exception);
        }
    }
}
