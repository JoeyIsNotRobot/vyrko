<?php

namespace App\Services\Import;

use App\Models\CandidateExperience;
use App\Models\CandidateSkill;
use App\Models\User;
use App\Services\Ai\AiClient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Process\Process;
use ZipArchive;

class ResumeImportService
{
    public function __construct(private readonly AiClient $aiClient) {}

    public function importUploadedFile(User $user, UploadedFile $file): void
    {
        $text = $this->extractText($file);
        $this->importText($user, $text);
    }

    public function importText(User $user, string $text): void
    {
        $normalized = $this->normalizeText($text);

        if ($normalized === '') {
            throw new RuntimeException('O currículo enviado não possui texto suficiente para importação.');
        }

        try {
            $parsed = $this->aiClient->completeJson('resume_import_parse', [
                'resume_text' => $normalized,
            ]);
        } catch (InvalidArgumentException $exception) {
            throw new RuntimeException($exception->getMessage(), previous: $exception);
        }

        DB::transaction(function () use ($user, $parsed): void {
            $this->persistProfile($user, $parsed['profile'] ?? []);
            $this->persistSkills($user, $parsed['skills'] ?? []);
            $this->persistExperiences($user, $parsed['experiences'] ?? []);
            $this->persistProjects($user, $parsed['projects'] ?? []);
            $this->persistEducations($user, $parsed['educations'] ?? []);
            $this->persistCertifications($user, $parsed['certifications'] ?? []);
            $this->persistLanguages($user, $parsed['languages'] ?? []);
            $this->persistAchievements($user, null, $parsed['achievements'] ?? []);
        });
    }

    private function extractText(UploadedFile $file): string
    {
        $extension = Str::lower($file->getClientOriginalExtension());

        if ($extension === 'txt') {
            return (string) file_get_contents($file->getRealPath());
        }

        if ($extension === 'docx') {
            return $this->extractDocxText($file);
        }

        // if ($extension === 'pdf') {
        //     return $this->extractPdfText($file);
        // }

        $process = new Process(['pdftotext', '-layout', (string) $file->getRealPath(), '-']);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException('Não foi possível extrair texto do PDF enviado.');
        }

        return $process->getOutput();
    }

    private function extractDocxText(UploadedFile $file): string
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Não foi possível ler DOCX neste ambiente. Envie PDF ou TXT.');
        }

        $zip = new ZipArchive;

        if ($zip->open((string) $file->getRealPath()) !== true) {
            throw new RuntimeException('Não foi possível abrir o DOCX enviado.');
        }

        $parts = [];

        foreach (['word/document.xml', 'word/header1.xml', 'word/footer1.xml'] as $entry) {
            $contents = $zip->getFromName($entry);

            if ($contents !== false) {
                $parts[] = $contents;
            }
        }

        $zip->close();

        if ($parts === []) {
            throw new RuntimeException('Não foi possível extrair texto do DOCX enviado.');
        }

        return html_entity_decode(strip_tags(str_replace(['</w:p>', '</w:tr>'], "\n", implode("\n", $parts))));
    }

    private function persistProfile(User $user, mixed $profile): void
    {
        $profile = is_array($profile) ? $profile : [];
        [$fallbackFirstName, $fallbackLastName] = $this->splitName($user->name);

        $user->candidateProfile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'first_name' => $this->text($profile['first_name'] ?? null, $fallbackFirstName),
                'last_name' => $this->text($profile['last_name'] ?? null, $fallbackLastName),
                'headline' => $this->nullableText($profile['headline'] ?? null),
                'location_city' => $this->nullableText($profile['location_city'] ?? null),
                'location_state' => $this->nullableText($profile['location_state'] ?? null),
                'location_country' => $this->nullableText($profile['location_country'] ?? null),
                'email' => $this->nullableText($profile['email'] ?? null) ?: $user->email,
                'phone' => $this->nullableText($profile['phone'] ?? null),
                'linkedin_url' => $this->url($profile['linkedin_url'] ?? null),
                'github_url' => $this->url($profile['github_url'] ?? null),
                'portfolio_url' => $this->url($profile['portfolio_url'] ?? null),
                'summary' => $this->nullableText($profile['summary'] ?? null),
                'target_role' => $this->nullableText($profile['target_role'] ?? null),
                'target_seniority' => $this->nullableText($profile['target_seniority'] ?? null),
                'preferred_language' => in_array(($profile['preferred_language'] ?? null), ['pt_BR', 'en'], true) ? $profile['preferred_language'] : 'pt_BR',
                'onboarding_source' => 'resume_import',
            ],
        );
    }

    private function persistSkills(User $user, mixed $skills): void
    {
        foreach ($this->arrayItems($skills) as $skill) {
            $name = $this->nullableText($skill['name'] ?? null);

            if (! $name) {
                continue;
            }

            $category = (string) ($skill['category'] ?? 'other');
            $category = in_array($category, CandidateSkill::CATEGORIES, true) ? $category : 'other';

            $user->candidateSkills()->updateOrCreate(
                ['name' => $name],
                [
                    'category' => $category,
                    'proficiency_level' => $this->nullableText($skill['proficiency_level'] ?? null),
                    'years_of_experience' => $this->intOrNull($skill['years_of_experience'] ?? null),
                    'evidence_notes' => $this->nullableText($skill['evidence_notes'] ?? null) ?: 'Importado do currículo enviado.',
                ],
            );
        }
    }

    private function persistExperiences(User $user, mixed $experiences): void
    {
        foreach ($this->arrayItems($experiences) as $index => $data) {
            $company = $this->nullableText($data['company_name'] ?? null);
            $role = $this->nullableText($data['role_title'] ?? null);

            if (! $company || ! $role) {
                continue;
            }

            $experience = $user->candidateExperiences()->updateOrCreate(
                [
                    'company_name' => $company,
                    'role_title' => $role,
                    'start_date' => $this->date($data['start_date'] ?? null),
                ],
                [
                    'employment_type' => $this->nullableText($data['employment_type'] ?? null),
                    'location' => $this->nullableText($data['location'] ?? null),
                    'end_date' => $this->date($data['end_date'] ?? null),
                    'is_current' => (bool) ($data['is_current'] ?? false),
                    'description' => $this->nullableText($data['description'] ?? null),
                    'responsibilities' => $this->stringList($data['responsibilities'] ?? []),
                    'technologies' => $this->stringList($data['technologies'] ?? []),
                    'sort_order' => $index + 1,
                ],
            );

            $this->persistAchievements($user, $experience, $data['achievements'] ?? []);
        }
    }

    private function persistProjects(User $user, mixed $projects): void
    {
        foreach ($this->arrayItems($projects) as $data) {
            $name = $this->nullableText($data['name'] ?? null);

            if (! $name) {
                continue;
            }

            $user->candidateProjects()->updateOrCreate(
                ['name' => $name],
                [
                    'description' => $this->nullableText($data['description'] ?? null),
                    'role' => $this->nullableText($data['role'] ?? null),
                    'technologies' => $this->stringList($data['technologies'] ?? []),
                    'url' => $this->url($data['url'] ?? null),
                    'repository_url' => $this->url($data['repository_url'] ?? null),
                    'start_date' => $this->date($data['start_date'] ?? null),
                    'end_date' => $this->date($data['end_date'] ?? null),
                    'is_current' => (bool) ($data['is_current'] ?? false),
                    'highlights' => $this->stringList($data['highlights'] ?? []),
                ],
            );
        }
    }

    private function persistEducations(User $user, mixed $educations): void
    {
        foreach ($this->arrayItems($educations) as $data) {
            $institution = $this->nullableText($data['institution'] ?? null);
            $degree = $this->nullableText($data['degree'] ?? null);

            if (! $institution || ! $degree) {
                continue;
            }

            $user->candidateEducations()->updateOrCreate(
                ['institution' => $institution, 'degree' => $degree],
                [
                    'field_of_study' => $this->nullableText($data['field_of_study'] ?? null),
                    'start_date' => $this->date($data['start_date'] ?? null),
                    'end_date' => $this->date($data['end_date'] ?? null),
                    'is_current' => (bool) ($data['is_current'] ?? false),
                    'description' => $this->nullableText($data['description'] ?? null),
                ],
            );
        }
    }

    private function persistCertifications(User $user, mixed $certifications): void
    {
        foreach ($this->arrayItems($certifications) as $data) {
            $name = $this->nullableText($data['name'] ?? null);

            if (! $name) {
                continue;
            }

            $user->candidateCertifications()->updateOrCreate(
                ['name' => $name],
                [
                    'issuer' => $this->nullableText($data['issuer'] ?? null),
                    'issued_at' => $this->date($data['issued_at'] ?? null),
                    'expires_at' => $this->date($data['expires_at'] ?? null),
                    'credential_url' => $this->url($data['credential_url'] ?? null),
                    'description' => $this->nullableText($data['description'] ?? null),
                ],
            );
        }
    }

    private function persistLanguages(User $user, mixed $languages): void
    {
        foreach ($this->arrayItems($languages) as $data) {
            $language = $this->nullableText($data['language'] ?? null);
            $proficiency = $this->nullableText($data['proficiency'] ?? null);

            if (! $language || ! $proficiency) {
                continue;
            }

            $user->candidateLanguages()->updateOrCreate(
                ['language' => $language],
                [
                    'proficiency' => $proficiency,
                    'notes' => $this->nullableText($data['notes'] ?? null),
                ],
            );
        }
    }

    private function persistAchievements(User $user, ?CandidateExperience $experience, mixed $achievements): void
    {
        foreach ($this->arrayItems($achievements) as $index => $achievement) {
            $title = $this->nullableText($achievement['title'] ?? null);
            $description = $this->nullableText($achievement['description'] ?? null);

            if (! $title || ! $description) {
                continue;
            }

            $user->candidateAchievements()->updateOrCreate(
                [
                    'candidate_experience_id' => $experience?->id,
                    'title' => $title,
                ],
                [
                    'description' => $description,
                    'impact_metric' => $this->nullableText($achievement['impact_metric'] ?? null),
                    'evidence_tags' => $this->stringList($achievement['evidence_tags'] ?? []),
                    'sort_order' => $index + 1,
                ],
            );
        }
    }

    private function normalizeText(string $text): string
    {
        return trim(preg_replace("/[ \t]+/", ' ', str_replace("\r", '', $text)) ?? $text);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitName(string $name): array
    {
        $name = trim(mb_convert_case(Str::lower($name), MB_CASE_TITLE, 'UTF-8'));
        $parts = preg_split('/\s+/', $name) ?: [$name];

        return [
            $parts[0] ?? $name,
            trim(implode(' ', array_slice($parts, 1))) ?: '',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function arrayItems(mixed $items): array
    {
        return collect(is_array($items) ? $items : [])
            ->filter(fn (mixed $item): bool => is_array($item))
            ->values()
            ->all();
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
            ->unique(fn (string $item): string => Str::lower($item))
            ->values()
            ->all();
    }

    private function text(mixed $value, string $fallback): string
    {
        return $this->nullableText($value) ?: $fallback;
    }

    private function nullableText(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function intOrNull(mixed $value): ?int
    {
        if (! is_numeric($value)) {
            return null;
        }

        return max(0, (int) $value);
    }

    private function date(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : null;
    }

    private function url(mixed $value): ?string
    {
        $value = $this->nullableText($value);

        if (! $value) {
            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        return 'https://'.ltrim($value, '/');
    }
}
