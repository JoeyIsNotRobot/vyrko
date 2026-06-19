<?php

namespace App\Services\LinkedInSearch;

class LinkedInBooleanQueryBuilder
{
    /** @var array<string, string[]> */
    private const WORK_MODE_MAP = [
        'remoto'         => ['Remote', 'Remoto'],
        'híbrido'        => ['Hybrid', 'Híbrido'],
        'hibrido'        => ['Hybrid', 'Híbrido'],
        'presencial'     => ['On-site', 'Presencial'],
        'internacional'  => ['Worldwide', 'Anywhere', 'Global'],
        'international'  => ['Worldwide', 'Anywhere', 'Global'],
        'freelance'      => ['Freelance', 'Contractor'],
        'pj'             => ['PJ', 'Pessoa Jurídica'],
        'clt'            => ['CLT'],
        'contrato'       => ['Contract', 'Contrato'],
    ];

    private const LANG_WORK_PT  = ['Remoto', 'Híbrido', 'Oportunidade'];
    private const LANG_WORK_EN  = ['Remote', 'Hybrid', 'Worldwide', 'Anywhere'];
    private const HIRING_PT     = ['vaga', 'oportunidade', 'estamos contratando', 'hiring'];
    private const HIRING_EN     = ['hiring', 'we are hiring', 'job opening', 'opportunity'];

    private const SENIORITY_MAP = [
        'sênior'       => 'Senior', 'senior'       => 'Senior',
        'pleno'        => 'Mid-level', 'mid'        => 'Mid-level',
        'júnior'       => 'Junior', 'junior'       => 'Junior',
        'lead'         => 'Lead',
        'especialista' => 'Specialist',
        'gerência'     => 'Manager', 'gerencia'     => 'Manager',
        'coordenação'  => 'Coordinator', 'coordenacao'  => 'Coordinator',
        'estágio'      => 'Intern', 'estagio'      => 'Intern',
    ];

    /** @return LinkedInQuery[] */
    public function build(LinkedInSearchInput $input): array
    {
        $queries = [
            $this->buildBroad($input),
            $this->buildBalanced($input),
            $this->buildPrecise($input),
        ];

        if ($this->wantsRemoteOrInternational($input->workModes)) {
            $queries[] = $this->buildRecruiter($input);
        }

        return $queries;
    }

    private function buildBroad(LinkedInSearchInput $input): LinkedInQuery
    {
        $synonyms = [];
        foreach ($input->titles as $title) {
            $synonyms = array_merge($synonyms, SynonymMap::forTitle($title));
        }
        if ($input->niche) {
            $synonyms = array_merge($synonyms, SynonymMap::forNiche($input->niche));
        }

        $titles = $this->normalize(array_merge($input->titles, $synonyms));
        $skills = $this->normalize($input->skills);
        $work   = $this->normalize(array_merge(
            $this->expandWorkModes($input->workModes),
            $this->langWorkTerms($input->language),
            $input->locations,
        ));

        $groups = array_filter([
            $titles ? $this->orGroup($titles) : null,
            $skills ? $this->orGroup($skills) : null,
            $work   ? $this->orGroup($work)   : null,
        ]);
        $queryStr = $this->andGroups(array_values($groups));
        $queryStr = $this->appendNot($queryStr, $input->excludedTerms);

        return new LinkedInQuery(
            type: 'broad',
            label: 'Busca Ampla',
            objective: 'Boa para explorar o mercado com o maior volume de oportunidades.',
            query: $queryStr,
            linkedinJobsUrl: $this->jobsUrl($queryStr),
            linkedinPostsUrl: $this->postsUrl($queryStr),
            tip: 'Comece por aqui para ter uma visão geral do mercado. Os resultados serão mais variados.',
            warning: null,
        );
    }

    private function buildBalanced(LinkedInSearchInput $input): LinkedInQuery
    {
        $titles = $this->normalize($input->titles);
        $skills = $this->normalize(array_slice($input->skills, 0, 3));
        $work   = $this->normalize(array_merge(
            $this->expandWorkModes($input->workModes),
            $input->locations,
        ));

        $groups = array_filter([
            $titles ? $this->orGroup($titles) : null,
            $skills ? $this->orGroup($skills) : null,
            $work   ? $this->orGroup($work)   : null,
        ]);
        $queryStr = $this->andGroups(array_values($groups));
        $queryStr = $this->appendNot($queryStr, $input->excludedTerms);

        return new LinkedInQuery(
            type: 'balanced',
            label: 'Busca Equilibrada',
            objective: 'Equilibra volume e precisão para encontrar vagas com boa aderência.',
            query: $queryStr,
            linkedinJobsUrl: $this->jobsUrl($queryStr),
            linkedinPostsUrl: $this->postsUrl($queryStr),
            tip: 'Boa escolha para o dia a dia. Combina seus termos principais sem abrir demais os resultados.',
            warning: null,
        );
    }

    private function buildPrecise(LinkedInSearchInput $input): LinkedInQuery
    {
        $seniority = $this->primarySeniority($input->seniorities);
        $titles    = $this->normalize(
            array_map(fn($t) => $seniority ? "$seniority $t" : $t, $input->titles)
        );
        $topSkills = $this->normalize(array_slice($input->skills, 0, 3));
        $work      = $this->normalize($this->expandWorkModes($input->workModes));

        $groups = [];
        if ($titles) {
            $groups[] = $this->orGroup($titles);
        }
        if ($topSkills) {
            // Skills connected by AND (not OR) for higher precision
            $groups[] = '(' . implode(' AND ', array_map(fn($s) => '"' . $s . '"', $topSkills)) . ')';
        }
        if ($work) {
            $groups[] = $this->orGroup($work);
        }

        $queryStr = $this->andGroups($groups);
        $queryStr = $this->appendNot($queryStr, $input->excludedTerms);

        $andCount = substr_count($queryStr, ' AND ');
        $warning  = $andCount >= 4
            ? 'Essa busca pode retornar poucos resultados. Tente remover uma habilidade se o volume for escasso.'
            : null;

        return new LinkedInQuery(
            type: 'precise',
            label: 'Busca Precisa',
            objective: 'Para encontrar vagas muito aderentes ao seu perfil específico.',
            query: $queryStr,
            linkedinJobsUrl: $this->jobsUrl($queryStr),
            linkedinPostsUrl: $this->postsUrl($queryStr),
            tip: 'Use quando quiser filtrar vagas bem específicas. Se retornar poucos resultados, tente a busca equilibrada.',
            warning: $warning,
        );
    }

    private function buildRecruiter(LinkedInSearchInput $input): LinkedInQuery
    {
        $hiring = match ($input->language) {
            'pt'    => self::HIRING_PT,
            'en'    => self::HIRING_EN,
            default => array_merge(self::HIRING_PT, self::HIRING_EN),
        };
        $skills = $this->normalize(array_slice($input->skills, 0, 2));
        $work   = $this->normalize($this->expandWorkModes($input->workModes));

        $groups = array_filter([
            $this->orGroup($hiring),
            $skills ? $this->orGroup($skills) : null,
            $work   ? $this->orGroup($work)   : null,
        ]);
        $queryStr = $this->andGroups(array_values($groups));

        return new LinkedInQuery(
            type: 'recruiter',
            label: 'Busca por Recrutadores',
            objective: 'Encontra posts de recrutadores divulgando vagas diretamente no feed do LinkedIn.',
            query: $queryStr,
            linkedinJobsUrl: $this->jobsUrl($queryStr),
            linkedinPostsUrl: $this->postsUrl($queryStr),
            tip: 'Use o botão "Abrir no LinkedIn Posts" para buscar no feed, não em vagas. Recrutadores costumam postar oportunidades informalmente.',
            warning: null,
        );
    }

    // ── Primitives ──────────────────────────────────────────────────────────

    /** @param string[] $terms */
    private function orGroup(array $terms): string
    {
        $quoted = array_map(fn($t) => '"' . str_replace('"', "'", $t) . '"', $terms);
        return '(' . implode(' OR ', $quoted) . ')';
    }

    /** @param string[] $groups */
    private function andGroups(array $groups): string
    {
        return implode(' AND ', $groups);
    }

    /**
     * @param  string[] $terms
     * @return string[]
     */
    private function normalize(array $terms): array
    {
        $seen   = [];
        $result = [];
        foreach ($terms as $term) {
            $clean = trim((string) preg_replace('/\s+/', ' ', $term));
            if ($clean === '') {
                continue;
            }
            $key = mb_strtolower($clean);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $result[]   = $clean;
        }
        return $result;
    }

    /**
     * @param  string[] $workModes
     * @return string[]
     */
    private function expandWorkModes(array $workModes): array
    {
        $terms = [];
        foreach ($workModes as $mode) {
            $key   = mb_strtolower(trim($mode));
            $terms = array_merge($terms, self::WORK_MODE_MAP[$key] ?? [$mode]);
        }
        return array_unique($terms);
    }

    /** @param string[] $workModes */
    private function wantsRemoteOrInternational(array $workModes): bool
    {
        $remote = ['remoto', 'internacional', 'international', 'remote'];
        foreach ($workModes as $mode) {
            if (in_array(mb_strtolower(trim($mode)), $remote, true)) {
                return true;
            }
        }
        return false;
    }

    /** @return string[] */
    private function langWorkTerms(string $language): array
    {
        return match ($language) {
            'pt'    => self::LANG_WORK_PT,
            'en'    => self::LANG_WORK_EN,
            default => array_merge(self::LANG_WORK_PT, self::LANG_WORK_EN),
        };
    }

    /** @param string[] $seniorities */
    private function primarySeniority(array $seniorities): string
    {
        foreach ($seniorities as $s) {
            $key = mb_strtolower(trim($s));
            if (isset(self::SENIORITY_MAP[$key])) {
                return self::SENIORITY_MAP[$key];
            }
        }
        return '';
    }

    /** @param string[] $excluded */
    private function appendNot(string $query, array $excluded): string
    {
        $terms = $this->normalize($excluded);
        if (empty($terms)) {
            return $query;
        }
        return $query . ' NOT ' . $this->orGroup($terms);
    }

    private function jobsUrl(string $query): string
    {
        return 'https://www.linkedin.com/jobs/search/?keywords=' . urlencode($query);
    }

    private function postsUrl(string $query): string
    {
        return 'https://www.linkedin.com/search/results/content/?keywords=' . urlencode($query);
    }
}
