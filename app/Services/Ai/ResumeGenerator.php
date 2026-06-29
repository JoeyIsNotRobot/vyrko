<?php

namespace App\Services\Ai;

use App\Models\JobMatchReport;
use App\Models\JobPost;
use App\Models\ResumeVersion;
use App\Models\User;
use Illuminate\Support\Str;

class ResumeGenerator
{
    public function __construct(
        private readonly CareerInventoryFormatter $formatter,
        private readonly ResumeRenderer $renderer,
        private readonly AiClient $aiClient,
    ) {}

    public function generate(User $user, JobPost $jobPost, JobMatchReport $report, bool $includeCoverLetter = false): ResumeVersion
    {
        $inventory = $this->formatter->forUser($user);
        $profile = $inventory['profile'] ?? [];
        $evidenceMap = collect($report->evidence_map ?? []);

        $result = $this->aiClient->completeJson('resume_generate', [
            'job' => $jobPost->only(['title', 'company_name', 'job_description', 'target_language', 'resume_type', 'notes']),
            'requirements' => $jobPost->parsed_requirements ?? [],
            'match_report' => [
                'scores' => [
                    'overall' => $report->overall_score,
                    'technical' => $report->technical_score,
                    'experience' => $report->experience_score,
                    'seniority' => $report->seniority_score,
                    'keyword' => $report->keyword_score,
                ],
                'strengths' => $report->strengths ?? [],
                'gaps' => $report->gaps ?? [],
                'warnings' => $report->warnings ?? [],
                'recommendations' => $report->recommendations ?? [],
                'evidence_map' => $report->evidence_map ?? [],
            ],
            'inventory' => $inventory,
            'target_language' => $jobPost->target_language,
            'resume_type' => $jobPost->resume_type,
            'include_cover_letter' => $includeCoverLetter,
        ]);

        $content = $this->sanitizeContent($result['content'] ?? [], $inventory, $profile, $user, $evidenceMap);

        $coverLetterText = $includeCoverLetter && is_string($result['cover_letter_text'] ?? null) && trim((string) $result['cover_letter_text']) !== ''
            ? trim((string) $result['cover_letter_text'])
            : null;

        return ResumeVersion::create([
            'user_id' => $user->id,
            'job_post_id' => $jobPost->id,
            'job_match_report_id' => $report->id,
            'title' => is_string($result['title'] ?? null) && trim($result['title']) !== ''
                ? trim($result['title'])
                : "Currículo para {$jobPost->title}",
            'language' => $jobPost->target_language,
            'resume_type' => $jobPost->resume_type,
            'content' => $content,
            'plain_text' => $this->renderer->toPlainText($content),
            'cover_letter_text' => $coverLetterText,
            'status' => 'generated',
        ]);
    }

    /**
     * @param  array<string, mixed>  $content
     * @param  array<string, mixed>  $inventory
     * @param  array<string, mixed>|null  $profile
     */
    private function sanitizeContent(mixed $content, array $inventory, ?array $profile, User $user, mixed $evidenceMap): array
    {
        $content = is_array($content) ? $content : [];
        $allowedSkillNames = $this->allowedSkillNames($inventory, $evidenceMap);

        return [
            'header' => $this->header($content['header'] ?? [], $profile ?? [], $user),
            'summary' => $this->text($content['summary'] ?? null, (string) ($profile['summary'] ?? '')),
            'skills' => $this->skills($content['skills'] ?? [], $inventory, $allowedSkillNames),
            'experiences' => $this->experiences($content['experiences'] ?? [], $inventory),
            'projects' => $this->projects($content['projects'] ?? [], $inventory),
            'education' => $this->education($content['education'] ?? [], $inventory),
            'certifications' => $this->certifications($content['certifications'] ?? [], $inventory),
            'languages' => $this->languages($content['languages'] ?? [], $inventory),
        ];
    }

    /**
     * @param  array<string, mixed>  $header
     * @param  array<string, mixed>  $profile
     */
    private function header(mixed $header, array $profile, User $user): array
    {
        $header = is_array($header) ? $header : [];
        $links = collect($header['links'] ?? [])
            ->merge(array_filter([
                $profile['linkedin_url'] ?? null,
                $profile['github_url'] ?? null,
                $profile['portfolio_url'] ?? null,
            ]))
            ->filter(fn (mixed $item): bool => is_scalar($item) && trim((string) $item) !== '')
            ->map(fn (mixed $item): string => trim((string) $item))
            ->unique()
            ->values()
            ->all();

        return [
            'name' => $this->text($header['name'] ?? null, trim(($profile['first_name'] ?? $user->name).' '.($profile['last_name'] ?? ''))),
            'headline' => $this->text($header['headline'] ?? null, (string) ($profile['headline'] ?? $profile['target_role'] ?? '')),
            'location' => $this->text($header['location'] ?? null, trim(implode(', ', array_filter([
                $profile['location_city'] ?? null,
                $profile['location_state'] ?? null,
                $profile['location_country'] ?? null,
            ])))),
            'email' => $this->text($header['email'] ?? null, (string) ($profile['email'] ?? $user->email)),
            'phone' => $this->text($header['phone'] ?? null, (string) ($profile['phone'] ?? '')),
            'links' => $links,
        ];
    }

    /**
     * @param  array<string, mixed>  $inventory
     */
    private function allowedSkillNames(array $inventory, mixed $evidenceMap): array
    {
        $evidenceMap = collect(is_iterable($evidenceMap) ? $evidenceMap : []);
        $allowedFromReport = $evidenceMap
            ->filter(fn (mixed $item): bool => is_array($item) && ($item['status'] ?? 'missing') !== 'missing')
            ->keys()
            ->map(fn (mixed $skill): string => Str::lower((string) $skill));

        return collect($inventory['skills'] ?? [])
            ->filter(fn (mixed $skill): bool => is_array($skill))
            ->pluck('name')
            ->filter()
            ->map(fn (mixed $skill): string => (string) $skill)
            ->filter(fn (string $skill): bool => $allowedFromReport->contains(Str::lower($skill)))
            ->map(fn (string $skill): string => Str::lower($skill))
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $allowedSkillNames
     * @param  array<string, mixed>  $inventory
     */
    private function skills(mixed $skills, array $inventory, array $allowedSkillNames): array
    {
        $knownSkills = collect($inventory['skills'] ?? [])
            ->filter(fn (mixed $skill): bool => is_array($skill))
            ->keyBy(fn (array $skill): string => Str::lower((string) ($skill['name'] ?? '')));

        $groups = collect(is_array($skills) ? $skills : [])
            ->filter(fn (mixed $group): bool => is_array($group))
            ->map(function (array $group) use ($allowedSkillNames, $knownSkills): array {
                $items = collect($group['items'] ?? [])
                    ->filter(fn (mixed $item): bool => is_scalar($item))
                    ->map(fn (mixed $item): string => trim((string) $item))
                    ->filter(fn (string $item): bool => in_array(Str::lower($item), $allowedSkillNames, true) && $knownSkills->has(Str::lower($item)))
                    ->unique(fn (string $item): string => Str::lower($item))
                    ->values()
                    ->all();

                return [
                    'category' => $this->text($group['category'] ?? null, 'Geral'),
                    'items' => $items,
                ];
            })
            ->filter(fn (array $group): bool => $group['items'] !== [])
            ->values()
            ->all();

        if ($groups !== [] || $allowedSkillNames === []) {
            return $groups;
        }

        return $knownSkills
            ->filter(fn (array $skill, string $name): bool => in_array($name, $allowedSkillNames, true))
            ->groupBy(fn (array $skill): string => (string) ($skill['category'] ?? 'other'))
            ->map(fn ($items, string $category): array => [
                'category' => $category,
                'items' => $items->pluck('name')->values()->all(),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $inventory
     */
    private function experiences(mixed $experiences, array $inventory): array
    {
        $knownExperiences = collect($inventory['experiences'] ?? [])
            ->filter(fn (mixed $experience): bool => is_array($experience));

        $validEvidence = $this->validEvidenceLookup($inventory);

        $generated = collect(is_array($experiences) ? $experiences : [])
            ->filter(fn (mixed $experience): bool => is_array($experience))
            ->map(function (array $experience) use ($knownExperiences, $validEvidence): ?array {
                $match = $knownExperiences->first(fn (array $known): bool => $this->sameText($known['company_name'] ?? '', $experience['company'] ?? '')
                    && $this->sameText($known['role_title'] ?? '', $experience['role'] ?? ''));

                if (! $match) {
                    return null;
                }

                $bullets = collect($experience['bullets'] ?? [])
                    ->filter(fn (mixed $bullet): bool => is_array($bullet))
                    ->map(function (array $bullet) use ($validEvidence): ?array {
                        $evidence = $this->filterEvidence($bullet['evidence'] ?? [], $validEvidence);

                        if ($evidence === []) {
                            return null;
                        }

                        return [
                            'text' => $this->text($bullet['text'] ?? null, ''),
                            'evidence' => $evidence,
                        ];
                    })
                    ->filter(fn (?array $bullet): bool => $bullet !== null && $bullet['text'] !== '')
                    ->values()
                    ->all();

                return [
                    'company' => $match['company_name'],
                    'role' => $match['role_title'],
                    'period' => $this->period($match),
                    'bullets' => $bullets,
                ];
            })
            ->filter()
            ->values()
            ->all();

        if ($generated !== []) {
            return $generated;
        }

        return $this->fallbackExperiences($inventory);
    }

    /**
     * @param  array<string, mixed>  $inventory
     * @return array<int, array<string, mixed>>
     */
    private function fallbackExperiences(array $inventory): array
    {
        return collect($inventory['experiences'] ?? [])
            ->filter(fn (mixed $experience): bool => is_array($experience))
            ->map(function (array $experience) use ($inventory): array {
                $achievements = collect($inventory['achievements'] ?? [])
                    ->where('candidate_experience_id', $experience['id'] ?? null)
                    ->values();

                $bullets = $achievements->map(fn (array $achievement): array => [
                    'text' => trim(($achievement['description'] ?? '').($achievement['impact_metric'] ? ' Impacto: '.$achievement['impact_metric'].'.' : '')),
                    'evidence' => [['type' => 'achievement', 'id' => $achievement['id']]],
                ])->filter(fn (array $bullet): bool => $bullet['text'] !== '')->values()->all();

                if ($bullets === []) {
                    $bullets = collect($experience['responsibilities'] ?? [])
                        ->filter()
                        ->take(3)
                        ->map(fn (string $text): array => [
                            'text' => $text,
                            'evidence' => [['type' => 'experience', 'id' => $experience['id']]],
                        ])
                        ->values()
                        ->all();
                }

                if ($bullets === [] && ! empty($experience['description'])) {
                    $bullets[] = [
                        'text' => $experience['description'],
                        'evidence' => [['type' => 'experience', 'id' => $experience['id']]],
                    ];
                }

                return [
                    'company' => $experience['company_name'],
                    'role' => $experience['role_title'],
                    'period' => $this->period($experience),
                    'bullets' => $bullets,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $inventory
     */
    private function projects(mixed $projects, array $inventory): array
    {
        return $this->filterNamedItems($projects, $inventory['projects'] ?? [], 'name', fn (array $item): array => [
            'name' => $item['name'],
            'description' => $item['description'] ?? '',
            'role' => $item['role'] ?? '',
            'technologies' => $item['technologies'] ?? [],
            'highlights' => $item['highlights'] ?? [],
        ]);
    }

    /**
     * @param  array<string, mixed>  $inventory
     */
    private function education(mixed $education, array $inventory): array
    {
        return $this->filterNamedItems($education, $inventory['educations'] ?? [], 'institution', fn (array $item): array => [
            'institution' => $item['institution'],
            'degree' => $item['degree'],
            'field' => $item['field_of_study'] ?? '',
        ]);
    }

    /**
     * @param  array<string, mixed>  $inventory
     */
    private function certifications(mixed $certifications, array $inventory): array
    {
        return $this->filterNamedItems($certifications, $inventory['certifications'] ?? [], 'name', fn (array $item): array => [
            'name' => $item['name'],
            'issuer' => $item['issuer'] ?? '',
        ]);
    }

    /**
     * @param  array<string, mixed>  $inventory
     */
    private function languages(mixed $languages, array $inventory): array
    {
        return $this->filterNamedItems($languages, $inventory['languages'] ?? [], 'language', fn (array $item): array => [
            'language' => $item['language'],
            'proficiency' => $item['proficiency'],
        ]);
    }

    /**
     * @param  array<int, mixed>  $knownItems
     * @return array<int, array<string, mixed>>
     */
    private function filterNamedItems(mixed $generatedItems, array $knownItems, string $key, callable $mapper): array
    {
        $known = collect($knownItems)
            ->filter(fn (mixed $item): bool => is_array($item))
            ->keyBy(fn (array $item): string => Str::lower((string) ($item[$key] ?? '')));

        $generated = collect(is_array($generatedItems) ? $generatedItems : [])
            ->filter(fn (mixed $item): bool => is_array($item))
            ->map(function (array $item) use ($known, $key, $mapper): ?array {
                $match = $known->get(Str::lower((string) ($item[$key] ?? $item['name'] ?? '')));

                return $match ? $mapper($match) : null;
            })
            ->filter()
            ->values()
            ->all();

        return $generated !== []
            ? $generated
            : $known->values()->map(fn (array $item): array => $mapper($item))->values()->all();
    }

    /**
     * @param  array<string, mixed>  $inventory
     * @return array<string, true>
     */
    private function validEvidenceLookup(array $inventory): array
    {
        $lookup = [];

        foreach ([
            'skill' => $inventory['skills'] ?? [],
            'experience' => $inventory['experiences'] ?? [],
            'achievement' => $inventory['achievements'] ?? [],
            'project' => $inventory['projects'] ?? [],
            'education' => $inventory['educations'] ?? [],
            'certification' => $inventory['certifications'] ?? [],
            'language' => $inventory['languages'] ?? [],
        ] as $type => $items) {
            foreach ($items as $item) {
                if (is_array($item) && isset($item['id'])) {
                    $lookup["{$type}:{$item['id']}"] = true;
                }
            }
        }

        return $lookup;
    }

    /**
     * @param  array<string, true>  $validEvidence
     * @return array<int, array{type: string, id: int}>
     */
    private function filterEvidence(mixed $evidenceItems, array $validEvidence): array
    {
        return collect(is_array($evidenceItems) ? $evidenceItems : [])
            ->filter(fn (mixed $entry): bool => is_array($entry))
            ->map(function (array $entry) use ($validEvidence): ?array {
                $type = (string) ($entry['type'] ?? '');
                $id = (int) ($entry['id'] ?? 0);

                return isset($validEvidence["{$type}:{$id}"])
                    ? ['type' => $type, 'id' => $id]
                    : null;
            })
            ->filter()
            ->values()
            ->all();
    }

    private function text(mixed $value, string $fallback): string
    {
        if (! is_scalar($value)) {
            return $fallback;
        }

        $value = trim((string) $value);

        return $value !== '' ? $value : $fallback;
    }

    private function sameText(mixed $first, mixed $second): bool
    {
        return Str::lower(trim((string) $first)) === Str::lower(trim((string) $second));
    }

    /**
     * @param  array<string, mixed>  $experience
     */
    private function period(array $experience): string
    {
        $start = isset($experience['start_date']) ? substr((string) $experience['start_date'], 0, 7) : '';
        $end = $experience['is_current'] ? 'Atual' : (isset($experience['end_date']) ? substr((string) $experience['end_date'], 0, 7) : '');

        return trim("{$start} - {$end}", ' -');
    }
}
