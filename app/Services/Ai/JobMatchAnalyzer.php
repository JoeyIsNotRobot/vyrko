<?php

namespace App\Services\Ai;

use App\Models\JobMatchReport;
use App\Models\JobPost;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class JobMatchAnalyzer
{
    public function __construct(
        private readonly CareerInventoryFormatter $formatter,
        private readonly AiClient $aiClient,
    ) {}

    public function analyze(User $user, JobPost $jobPost): JobMatchReport
    {
        $inventory = $this->formatter->forUser($user);
        $requirements = $jobPost->parsed_requirements ?: [];
        $result = $this->aiClient->completeJson('job_match_report', [
            'job' => $jobPost->only(['title', 'company_name', 'job_description', 'target_language', 'resume_type', 'notes']),
            'requirements' => $requirements,
            'inventory' => $inventory,
        ]);

        $evidenceMap = $this->sanitizeEvidenceMap($result['evidence_map'] ?? [], $requirements, $inventory);
        $missing = collect($evidenceMap)
            ->filter(fn (array $item): bool => ($item['status'] ?? 'missing') === 'missing')
            ->keys()
            ->values();

        return JobMatchReport::create([
            'user_id' => $user->id,
            'job_post_id' => $jobPost->id,
            'overall_score' => $this->score($result, 'overall_score', 0),
            'technical_score' => $this->score($result, 'technical_score', $this->scoreEvidence(collect($evidenceMap))),
            'experience_score' => $this->score($result, 'experience_score', 0),
            'seniority_score' => $this->score($result, 'seniority_score', 0),
            'keyword_score' => $this->score($result, 'keyword_score', $this->scoreEvidence(collect($evidenceMap))),
            'ats_format_score' => $this->score($result, 'ats_format_score', 0),
            'human_readability_score' => $this->score($result, 'human_readability_score', 0),
            'strengths' => $this->stringList($result['strengths'] ?? []),
            'gaps' => [
                'critical' => $this->stringList($result['gaps']['critical'] ?? $missing->values()->all()),
                'acceptable' => $this->stringList($result['gaps']['acceptable'] ?? []),
            ],
            'warnings' => $this->stringList($result['warnings'] ?? []),
            'recommendations' => $this->stringList($result['recommendations'] ?? []),
            'evidence_map' => $evidenceMap,
        ]);
    }

    /**
     * @param  array<string, mixed>  $inventory
     * @param  array<string, mixed>  $requirements
     * @param  array<string, mixed>  $rawEvidenceMap
     * @return array<string, array{status: string, evidence: array<int, array<string, mixed>>}>
     */
    private function sanitizeEvidenceMap(array $rawEvidenceMap, array $requirements, array $inventory): array
    {
        $requirementsToCheck = collect($requirements['required_skills'] ?? [])
            ->merge($requirements['ats_keywords'] ?? $requirements['keywords'] ?? [])
            ->filter()
            ->unique(fn (mixed $item): string => Str::lower((string) $item));

        $allowedEvidence = $this->allowedEvidence($inventory);
        $sanitized = [];

        foreach ($rawEvidenceMap as $requirement => $item) {
            if (! is_string($requirement) || ! is_array($item)) {
                continue;
            }

            $evidence = collect($item['evidence'] ?? [])
                ->filter(fn (mixed $entry): bool => is_array($entry))
                ->map(function (array $entry) use ($allowedEvidence): ?array {
                    $type = (string) ($entry['type'] ?? '');
                    $id = (int) ($entry['id'] ?? 0);
                    $key = "{$type}:{$id}";

                    if (! isset($allowedEvidence[$key])) {
                        return null;
                    }

                    return [
                        'type' => $type,
                        'id' => $id,
                        'label' => $allowedEvidence[$key],
                    ];
                })
                ->filter()
                ->values()
                ->all();

            $status = (string) ($item['status'] ?? 'missing');
            $status = in_array($status, ['strong_match', 'medium_match', 'partial', 'missing'], true) ? $status : 'missing';

            if ($evidence === []) {
                $status = 'missing';
            }

            $sanitized[$requirement] = [
                'status' => $status,
                'evidence' => $evidence,
            ];
        }

        foreach ($requirementsToCheck as $requirement) {
            $key = (string) $requirement;

            if (! array_key_exists($key, $sanitized)) {
                $sanitized[$key] = [
                    'status' => 'missing',
                    'evidence' => [],
                ];
            }
        }

        return $sanitized;
    }

    /**
     * @param  Collection<int|string, array<string, mixed>>  $evidenceItems
     */
    private function scoreEvidence(Collection $evidenceItems): int
    {
        if ($evidenceItems->isEmpty()) {
            return 0;
        }

        $score = $evidenceItems->avg(fn (array $item): int => match ($item['status']) {
            'strong_match' => 100,
            'medium_match' => 65,
            'partial' => 35,
            default => 0,
        });

        return $this->clamp((int) round($score));
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function score(array $result, string $key, int $default): int
    {
        return $this->clamp((int) ($result[$key] ?? $default));
    }

    private function clamp(int $score): int
    {
        return max(0, min(100, $score));
    }

    /**
     * @return array<int, string>
     */
    private function stringList(mixed $items): array
    {
        return collect(is_array($items) ? $items : [])
            ->filter(fn (mixed $item): bool => is_scalar($item))
            ->map(fn (mixed $item): string => trim((string) $item))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $inventory
     * @return array<string, string>
     */
    private function allowedEvidence(array $inventory): array
    {
        $map = [];
        $sources = [
            'skill' => ['items' => $inventory['skills'] ?? [], 'label' => fn (array $item): string => (string) ($item['name'] ?? '')],
            'experience' => ['items' => $inventory['experiences'] ?? [], 'label' => fn (array $item): string => trim(($item['role_title'] ?? '').' · '.($item['company_name'] ?? ''))],
            'achievement' => ['items' => $inventory['achievements'] ?? [], 'label' => fn (array $item): string => (string) ($item['title'] ?? '')],
            'project' => ['items' => $inventory['projects'] ?? [], 'label' => fn (array $item): string => (string) ($item['name'] ?? '')],
            'education' => ['items' => $inventory['educations'] ?? [], 'label' => fn (array $item): string => (string) ($item['institution'] ?? '')],
            'certification' => ['items' => $inventory['certifications'] ?? [], 'label' => fn (array $item): string => (string) ($item['name'] ?? '')],
            'language' => ['items' => $inventory['languages'] ?? [], 'label' => fn (array $item): string => (string) ($item['language'] ?? '')],
        ];

        foreach ($sources as $type => $source) {
            foreach ($source['items'] as $item) {
                if (! is_array($item) || ! isset($item['id'])) {
                    continue;
                }

                $map["{$type}:{$item['id']}"] = $source['label']($item);
            }
        }

        return $map;
    }
}
