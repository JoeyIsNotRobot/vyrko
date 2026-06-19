<?php

namespace App\Services\LinkedInSearch;

class LinkedInQuery
{
    public function __construct(
        public readonly string $type,
        public readonly string $label,
        public readonly string $objective,
        public readonly string $query,
        public readonly string $linkedinJobsUrl,
        public readonly string $linkedinPostsUrl,
        public readonly string $tip,
        public readonly ?string $warning = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'type'            => $this->type,
            'label'           => $this->label,
            'objective'       => $this->objective,
            'query'           => $this->query,
            'linkedinJobsUrl' => $this->linkedinJobsUrl,
            'linkedinPostsUrl'=> $this->linkedinPostsUrl,
            'tip'             => $this->tip,
            'warning'         => $this->warning,
        ];
    }
}
