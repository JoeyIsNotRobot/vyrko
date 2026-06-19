<?php

namespace App\Services\Ai;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FakeAiClient implements AiClient
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function completeJson(string $feature, array $payload): array
    {
        return match ($feature) {
            'job_post_parse' => $this->parseJobPost($payload),
            'job_match_report' => $this->matchReport($payload),
            'resume_generate' => $this->resume($payload),
            'ats_checklist' => $this->atsChecklist($payload),
            'linkedin_analysis' => $this->linkedinAnalysis($payload),
            'resume_import_parse' => $this->resumeImport($payload),
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function parseJobPost(array $payload): array
    {
        $description = (string) ($payload['job_description'] ?? '');
        $text = Str::lower($description);

        $skills = [
            'PHP',
            'Laravel',
            'MySQL',
            'Redis',
            'Docker',
            'REST APIs',
            'PHPUnit',
            'Pest',
            'AWS',
            'Vue.js',
            'React',
            'JavaScript',
            'CI/CD',
            'Git',
            'Tailwind CSS',
        ];

        $found = collect($skills)
            ->filter(fn (string $skill): bool => str_contains($text, Str::lower($skill)) || str_contains($text, Str::lower(str_replace(['.', '/'], '', $skill))))
            ->values();

        if ($found->isEmpty() && str_word_count($description) > 8) {
            $found = collect(['Laravel', 'PHP', 'MySQL']);
        }

        $seniority = 'unknown';

        foreach ([
            'lead' => ['lead', 'tech lead', 'lider', 'líder'],
            'senior' => ['senior', 'sênior', 'sr'],
            'mid' => ['pleno', 'mid-level', 'mid level'],
            'junior' => ['junior', 'júnior', 'jr'],
        ] as $level => $needles) {
            if (collect($needles)->contains(fn (string $needle): bool => str_contains($text, $needle))) {
                $seniority = $level;
                break;
            }
        }

        $preferred = collect(['Redis', 'Docker', 'AWS', 'CI/CD', 'Vue.js', 'React'])
            ->filter(fn (string $skill): bool => $found->doesntContain($skill) && str_contains($text, Str::lower($skill)))
            ->values();

        return [
            'job_title' => (string) ($payload['title'] ?? 'Vaga analisada'),
            'company_name' => $payload['company_name'] ?? null,
            'seniority' => $seniority,
            'required_skills' => $found->values()->all(),
            'preferred_skills' => $preferred->values()->all(),
            'responsibilities' => [
                'Desenvolver e manter funcionalidades alinhadas aos requisitos da vaga.',
                'Colaborar com produto e engenharia para entregar software confiável.',
                'Escrever código testável e manter boa qualidade técnica.',
            ],
            'soft_skills' => collect(['comunicação', 'colaboração', 'autonomia'])
                ->filter(fn (string $skill): bool => str_contains($text, Str::lower($skill)))
                ->values()
                ->all(),
            'keywords' => $found->merge($preferred)->unique()->values()->all(),
            'ats_keywords' => $found->merge(['APIs', 'testes', 'performance'])->unique()->values()->all(),
            'language_requirements' => str_contains($text, 'inglês') || str_contains($text, 'english') ? ['Inglês'] : [],
            'education_requirements' => str_contains($text, 'graduação') || str_contains($text, 'bacharel') ? ['Formação superior'] : [],
            'hidden_signals' => [
                'Valoriza evidências concretas de entrega.',
                'Evitar mencionar tecnologias sem prova no inventário.',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function matchReport(array $payload): array
    {
        $requirements = $payload['requirements'] ?? [];
        $inventory = $payload['inventory'] ?? [];
        $requiredSkills = collect($requirements['required_skills'] ?? []);
        $keywords = collect($requirements['ats_keywords'] ?? $requirements['keywords'] ?? []);

        $evidenceMap = $requiredSkills
            ->merge($keywords)
            ->filter()
            ->unique(fn (mixed $skill): string => Str::lower((string) $skill))
            ->mapWithKeys(fn (mixed $skill): array => [(string) $skill => $this->evidenceFor((string) $skill, $inventory)])
            ->all();

        $technicalScore = $this->scoreEvidence(collect($evidenceMap));
        $missing = collect($evidenceMap)->filter(fn (array $item): bool => $item['status'] === 'missing')->keys()->values();

        return [
            'overall_score' => $technicalScore,
            'technical_score' => $technicalScore,
            'experience_score' => count($inventory['experiences'] ?? []) > 0 ? 75 : 20,
            'seniority_score' => 70,
            'keyword_score' => $technicalScore,
            'ats_format_score' => 80,
            'human_readability_score' => 80,
            'strengths' => collect($evidenceMap)
                ->reject(fn (array $item): bool => $item['status'] === 'missing')
                ->keys()
                ->map(fn (string $skill): string => "{$skill}: evidência encontrada no inventário.")
                ->values()
                ->all(),
            'gaps' => [
                'critical' => $missing->intersect($requiredSkills)->values()->all(),
                'acceptable' => $missing->diff($requiredSkills)->values()->all(),
            ],
            'warnings' => $missing->map(fn (string $skill): string => "Sem evidência para {$skill}.")->all(),
            'recommendations' => ['Use somente evidências existentes no inventário.'],
            'evidence_map' => $evidenceMap,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function resume(array $payload): array
    {
        $inventory = $payload['inventory'] ?? [];
        $profile = $inventory['profile'] ?? [];
        $evidenceMap = collect($payload['match_report']['evidence_map'] ?? []);
        $allowedSkills = $evidenceMap
            ->filter(fn (array $item): bool => ($item['status'] ?? 'missing') !== 'missing')
            ->keys()
            ->map(fn (string $skill): string => Str::lower($skill));

        $skills = collect($inventory['skills'] ?? [])
            ->filter(fn (array $skill): bool => $allowedSkills->contains(Str::lower((string) ($skill['name'] ?? ''))))
            ->groupBy('category')
            ->map(fn ($items, string $category): array => [
                'category' => $category,
                'items' => $items->pluck('name')->values()->all(),
            ])
            ->values()
            ->all();

        $experiences = collect($inventory['experiences'] ?? [])
            ->map(fn (array $experience): array => [
                'company' => $experience['company_name'] ?? '',
                'role' => $experience['role_title'] ?? '',
                'period' => trim(substr((string) ($experience['start_date'] ?? ''), 0, 7).' - '.(($experience['is_current'] ?? false) ? 'Atual' : substr((string) ($experience['end_date'] ?? ''), 0, 7)), ' -'),
                'bullets' => collect($experience['responsibilities'] ?? [])
                    ->whenEmpty(fn ($items) => collect(array_filter([$experience['description'] ?? null])))
                    ->take(3)
                    ->map(fn (string $text): array => [
                        'text' => $text,
                        'evidence' => [['type' => 'experience', 'id' => $experience['id']]],
                    ])
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();

        return [
            'title' => 'Currículo para '.($payload['job']['title'] ?? 'vaga'),
            'content' => [
                'header' => [
                    'name' => trim(($profile['first_name'] ?? '').' '.($profile['last_name'] ?? '')) ?: 'Profissional',
                    'headline' => $profile['headline'] ?? $profile['target_role'] ?? '',
                    'location' => trim(implode(', ', array_filter([
                        $profile['location_city'] ?? null,
                        $profile['location_state'] ?? null,
                        $profile['location_country'] ?? null,
                    ]))),
                    'email' => $profile['email'] ?? '',
                    'phone' => $profile['phone'] ?? '',
                    'links' => array_values(array_filter([
                        $profile['linkedin_url'] ?? null,
                        $profile['github_url'] ?? null,
                        $profile['portfolio_url'] ?? null,
                    ])),
                ],
                'summary' => $profile['summary'] ?? '',
                'skills' => $skills,
                'experiences' => $experiences,
                'projects' => [],
                'education' => [],
                'certifications' => [],
                'languages' => [],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function atsChecklist(array $payload): array
    {
        $content = $payload['content'] ?? [];

        return [
            'score' => 86,
            'items' => [
                ['key' => 'layout_one_column', 'status' => 'passed', 'message' => 'Estrutura textual em coluna única.'],
                ['key' => 'has_standard_sections', 'status' => isset($content['header'], $content['summary'], $content['skills'], $content['experiences']) ? 'passed' : 'warning', 'message' => 'Seções padrão verificadas.'],
                ['key' => 'no_tables', 'status' => 'passed', 'message' => 'Sem tabelas no conteúdo gerado.'],
            ],
            'file_format_recommendation' => 'Use PDF simples ou DOCX sem tabelas.',
            'warnings' => [],
            'recommendations' => ['Mantenha seções padrão e palavras-chave com evidência.'],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function linkedinAnalysis(array $payload): array
    {
        $profile = $payload['linkedin_profile'] ?? [];

        return [
            'score' => 70,
            'strengths' => ['Perfil com base textual disponível para análise.'],
            'weaknesses' => ['Adicionar evidências mensuráveis quando existirem no inventário.'],
            'recommendations' => ['Ajustar headline e about ao cargo-alvo informado.'],
            'rewritten_headline' => $profile['headline'] ?? null,
            'rewritten_about' => $profile['about'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function resumeImport(array $payload): array
    {
        $text = (string) ($payload['resume_text'] ?? '');
        $lines = collect(explode("\n", $text))
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->values();
        [$firstName, $lastName] = $this->splitName($lines->first() ?: 'Profissional');

        return [
            'profile' => [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'headline' => $lines->get(1),
                'location_city' => str_contains($text, 'Arapongas') ? 'Arapongas' : null,
                'location_state' => str_contains($text, 'PR') ? 'PR' : null,
                'location_country' => str_contains($text, 'Brasil') ? 'Brasil' : null,
                'email' => null,
                'phone' => null,
                'linkedin_url' => preg_match('/LinkedIn:\s*([^|\n]+)/i', $text, $linkedin) ? trim($linkedin[1]) : null,
                'github_url' => null,
                'portfolio_url' => null,
                'summary' => trim(Str::between($text, 'RESUMO PROFISSIONAL', 'COMPETÊNCIAS TÉCNICAS')) ?: null,
                'target_role' => null,
                'target_seniority' => null,
                'preferred_language' => 'pt_BR',
            ],
            'skills' => $this->parsedSkills($text),
            'experiences' => $this->parsedExperiences($text),
            'projects' => [],
            'educations' => $this->parsedEducations($text),
            'certifications' => [],
            'languages' => $this->parsedLanguages($text),
            'achievements' => [],
        ];
    }

    /**
     * @param  array<string, mixed>  $inventory
     * @return array{status: string, evidence: array<int, array<string, mixed>>}
     */
    private function evidenceFor(string $skill, array $inventory): array
    {
        $needle = Str::lower($skill);
        $evidence = [];
        $strong = false;

        foreach ($inventory['skills'] ?? [] as $item) {
            if ($this->contains($item['name'] ?? '', $needle) || $this->contains($item['evidence_notes'] ?? '', $needle)) {
                $strong = true;
                $evidence[] = [
                    'type' => 'skill',
                    'id' => $item['id'],
                    'label' => $item['name'],
                ];
            }
        }

        foreach ($inventory['experiences'] ?? [] as $item) {
            $text = trim(implode(' ', $item['technologies'] ?? []).' '.implode(' ', $item['responsibilities'] ?? []).' '.($item['description'] ?? ''));

            if ($this->contains($text, $needle)) {
                $evidence[] = [
                    'type' => 'experience',
                    'id' => $item['id'],
                    'label' => trim(($item['role_title'] ?? '').' · '.($item['company_name'] ?? '')),
                ];
            }
        }

        foreach ($inventory['achievements'] ?? [] as $item) {
            if ($this->contains("{$item['title']} {$item['description']} {$item['impact_metric']} ".implode(' ', $item['evidence_tags'] ?? []), $needle)) {
                $evidence[] = [
                    'type' => 'achievement',
                    'id' => $item['id'],
                    'label' => $item['title'],
                ];
            }
        }

        foreach ($inventory['projects'] ?? [] as $item) {
            if ($this->contains("{$item['name']} {$item['description']} ".implode(' ', $item['technologies'] ?? []).' '.implode(' ', $item['highlights'] ?? []), $needle)) {
                $evidence[] = [
                    'type' => 'project',
                    'id' => $item['id'],
                    'label' => $item['name'],
                ];
            }
        }

        if ($evidence === []) {
            return ['status' => 'missing', 'evidence' => []];
        }

        return [
            'status' => $strong ? 'strong_match' : 'medium_match',
            'evidence' => $evidence,
        ];
    }

    private function scoreEvidence(Collection $evidenceItems): int
    {
        if ($evidenceItems->isEmpty()) {
            return 0;
        }

        return (int) round($evidenceItems->avg(fn (array $item): int => match ($item['status']) {
            'strong_match' => 100,
            'medium_match' => 65,
            default => 0,
        }));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function parsedSkills(string $text): array
    {
        $section = Str::between($text, 'COMPETÊNCIAS TÉCNICAS', 'EXPERIÊNCIA PROFISSIONAL');
        $map = [
            'Backend:' => 'backend',
            'APIs e integrações:' => 'backend',
            'Banco e performance:' => 'database',
            'Qualidade:' => 'testing',
            'Ferramentas:' => 'tool',
        ];

        return collect($map)
            ->flatMap(function (string $category, string $prefix) use ($section): array {
                if (! preg_match('/'.preg_quote($prefix, '/').'\s*(.+?)(?=\n[A-ZÁÉÍÓÚÂÊÔÃÕÇ][^\n:]+:|\z)/su', $section, $match)) {
                    return [];
                }

                return collect(preg_split('/[,;\n]/', $match[1]) ?: [])
                    ->map(fn (string $skill): string => trim($skill, " \t\n\r\0\x0B."))
                    ->filter(fn (string $skill): bool => mb_strlen($skill) > 1)
                    ->map(fn (string $skill): array => [
                        'name' => $skill,
                        'category' => $category,
                        'proficiency_level' => null,
                        'years_of_experience' => null,
                        'evidence_notes' => 'Importado do currículo enviado.',
                    ])
                    ->all();
            })
            ->unique(fn (array $skill): string => Str::lower($skill['name']))
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function parsedExperiences(string $text): array
    {
        return collect(explode("\n", Str::between($text, 'EXPERIÊNCIA PROFISSIONAL', 'FORMAÇÃO E IDIOMAS')))
            ->map(fn (string $line): array => array_map('trim', explode('|', $line)))
            ->filter(fn (array $parts): bool => count($parts) >= 3)
            ->values()
            ->map(fn (array $parts, int $index): array => [
                'company_name' => $parts[0],
                'role_title' => $parts[1],
                'employment_type' => null,
                'location' => null,
                'start_date' => $this->dateFromPeriod($parts[2], true),
                'end_date' => $this->dateFromPeriod($parts[2], false),
                'is_current' => str_contains(Str::lower($parts[2]), 'atual'),
                'description' => null,
                'responsibilities' => [],
                'technologies' => [],
                'achievements' => [],
                'sort_order' => $index + 1,
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function parsedEducations(string $text): array
    {
        $section = Str::between($text, 'FORMAÇÃO E IDIOMAS', 'TEXT');

        return collect(explode("\n", $section ?: Str::after($text, 'FORMAÇÃO E IDIOMAS')))
            ->map(fn (string $line): array => array_map('trim', explode('|', $line)))
            ->filter(fn (array $parts): bool => count($parts) >= 2 && ! str_contains($parts[0], ':'))
            ->map(fn (array $parts): array => [
                'institution' => $parts[0],
                'degree' => $parts[1],
                'field_of_study' => null,
                'start_date' => $this->dateFromPeriod($parts[2] ?? '', true),
                'end_date' => $this->dateFromPeriod($parts[2] ?? '', false),
                'is_current' => isset($parts[2]) && str_contains(Str::lower($parts[2]), 'atual'),
                'description' => null,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function parsedLanguages(string $text): array
    {
        $languages = [];

        foreach (['Português', 'Inglês', 'Espanhol', 'Francês'] as $language) {
            if (preg_match('/'.$language.':\s*([^|\n]+)/iu', $text, $match)) {
                $languages[] = [
                    'language' => $language,
                    'proficiency' => trim($match[1]),
                    'notes' => null,
                ];
            }
        }

        return $languages;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitName(string $name): array
    {
        $name = trim(mb_convert_case(Str::lower($name), MB_CASE_TITLE, 'UTF-8'));
        $parts = preg_split('/\s+/', $name) ?: [$name];

        return [$parts[0] ?? $name, trim(implode(' ', array_slice($parts, 1))) ?: ''];
    }

    private function dateFromPeriod(string $period, bool $start): ?string
    {
        $parts = preg_split('/\s+-\s+/', Str::lower($period)) ?: [];
        $date = $parts[$start ? 0 : 1] ?? null;

        if (! $date || str_contains($date, 'atual')) {
            return null;
        }

        $months = [
            'jan' => '01',
            'fev' => '02',
            'mar' => '03',
            'abr' => '04',
            'mai' => '05',
            'jun' => '06',
            'jul' => '07',
            'ago' => '08',
            'set' => '09',
            'out' => '10',
            'nov' => '11',
            'dez' => '12',
        ];

        if (! preg_match('/([a-zç]{3})\/(\d{4})/u', $date, $match)) {
            return null;
        }

        $month = $months[$match[1]] ?? null;

        return $month ? "{$match[2]}-{$month}-01" : null;
    }

    private function contains(?string $haystack, string $needle): bool
    {
        return str_contains(Str::lower((string) $haystack), $needle);
    }
}
