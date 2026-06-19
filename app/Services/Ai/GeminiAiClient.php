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
- Em currículos e LinkedIn, reescreva com clareza, mas preserve a verdade factual.
- Retorne arrays vazios quando uma informação não existir.
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
        return $this->jsonPrompt('Compare a vaga com o inventário e gere um relatório de match. Use IDs de evidência somente quando eles existirem no inventário enviado.', [
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
        return $this->jsonPrompt('Gere um currículo direcionado para a vaga usando apenas o inventário e o mapa de evidências aprovadas. Não inclua tecnologias com status missing.', [
            'schema' => [
                'title' => 'string',
                'content' => [
                    'header' => [
                        'name' => 'string',
                        'headline' => 'string',
                        'location' => 'string',
                        'email' => 'string',
                        'phone' => 'string',
                        'links' => ['string'],
                    ],
                    'summary' => 'string',
                    'skills' => [
                        ['category' => 'string', 'items' => ['string']],
                    ],
                    'experiences' => [
                        [
                            'company' => 'string',
                            'role' => 'string',
                            'period' => 'string',
                            'bullets' => [
                                ['text' => 'string', 'evidence' => [['type' => 'string', 'id' => 'integer']]],
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
        ]);
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
        return $this->jsonPrompt('Extraia dados estruturados do currículo enviado. Não complete lacunas por inferência; se o texto não disser, use null ou array vazio.', [
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
                'projects' => ['array'],
                'educations' => ['array'],
                'certifications' => ['array'],
                'languages' => ['array'],
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
