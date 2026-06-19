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
