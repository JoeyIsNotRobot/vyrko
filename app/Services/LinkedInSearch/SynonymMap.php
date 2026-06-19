<?php

namespace App\Services\LinkedInSearch;

class SynonymMap
{
    /** @var array<string, string[]> */
    private const TITLE_SYNONYMS = [
        'backend'   => ['Backend Developer', 'Backend Engineer', 'Software Engineer', 'API Developer'],
        'frontend'  => ['Frontend Developer', 'Frontend Engineer', 'React Developer', 'UI Developer'],
        'fullstack' => ['Full Stack Developer', 'Fullstack Engineer', 'Software Engineer'],
        'full stack'=> ['Full Stack Developer', 'Fullstack Engineer', 'Software Engineer'],
        'data'      => ['Data Analyst', 'BI Analyst', 'Analytics Analyst', 'Data Engineer'],
        'dados'     => ['Analista de Dados', 'Data Analyst', 'BI Analyst', 'Data Engineer'],
        'product'   => ['Product Manager', 'Product Owner', 'Product Analyst', 'PM'],
        'produto'   => ['Product Manager', 'Product Owner', 'Product Analyst', 'Gerente de Produto'],
        'design'    => ['UX Designer', 'UI Designer', 'Product Designer', 'UX/UI Designer'],
        'marketing' => ['Marketing Analyst', 'Performance Marketing', 'Growth Analyst', 'Social Media'],
        'sales'     => ['Sales Development Representative', 'SDR', 'Account Executive', 'Sales Executive'],
        'vendas'    => ['Executivo de Vendas', 'SDR', 'Account Executive', 'Closer'],
        'admin'     => ['Administrative Assistant', 'Office Assistant', 'Backoffice Analyst', 'Operations Assistant'],
        'qa'        => ['QA Engineer', 'Quality Assurance Engineer', 'Test Engineer', 'SDET'],
        'devops'    => ['DevOps Engineer', 'SRE', 'Infrastructure Engineer', 'Cloud Engineer'],
        'mobile'    => ['Mobile Developer', 'iOS Developer', 'Android Developer', 'React Native Developer'],
        'suporte'   => ['Customer Support', 'Support Analyst', 'Help Desk', 'Technical Support'],
        'rh'        => ['Analista de RH', 'HR Analyst', 'People & Culture', 'Recrutamento e Seleção'],
        'financeiro'=> ['Analista Financeiro', 'Financial Analyst', 'Controladoria', 'Analista Contábil'],
    ];

    /** @var array<string, string[]> */
    private const NICHE_SYNONYMS = [
        'tecnologia'    => ['Software Engineer', 'Backend Developer', 'Tech Lead', 'Engineering Manager'],
        'dados'         => ['Data Analyst', 'BI Analyst', 'Data Engineer', 'Analytics Engineer'],
        'produto'       => ['Product Manager', 'Product Owner', 'Product Analyst'],
        'design'        => ['UX Designer', 'UI Designer', 'Product Designer'],
        'marketing'     => ['Marketing Analyst', 'Growth Analyst', 'Performance Marketing'],
        'vendas'        => ['Sales Development Representative', 'SDR', 'Account Executive'],
        'administrativo'=> ['Administrative Assistant', 'Backoffice Analyst', 'Operations Assistant'],
        'financeiro'    => ['Financial Analyst', 'Analista Financeiro', 'Controladoria'],
        'rh'            => ['HR Analyst', 'Analista de RH', 'People & Culture'],
        'suporte'       => ['Customer Support', 'Support Analyst', 'Customer Success'],
    ];

    /** @return string[] */
    public static function forTitle(string $title): array
    {
        $lower = mb_strtolower($title);
        foreach (self::TITLE_SYNONYMS as $keyword => $synonyms) {
            if (str_contains($lower, $keyword)) {
                return $synonyms;
            }
        }
        return [];
    }

    /** @return string[] */
    public static function forNiche(string $niche): array
    {
        $lower = mb_strtolower(trim($niche));
        return self::NICHE_SYNONYMS[$lower] ?? [];
    }
}
