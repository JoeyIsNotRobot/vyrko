<?php

namespace App\Services\Ai;

class ResumeRenderer
{
    /**
     * @param  array<string, mixed>  $content
     */
    public function toPlainText(array $content): string
    {
        $lines = [];
        $header = $content['header'] ?? [];

        $lines[] = trim((string) ($header['name'] ?? ''));
        $lines[] = trim((string) ($header['headline'] ?? ''));
        $lines[] = trim(implode(' | ', array_filter([
            $header['location'] ?? null,
            $header['email'] ?? null,
            $header['phone'] ?? null,
        ])));
        $lines[] = '';
        $lines[] = 'Resumo';
        $lines[] = (string) ($content['summary'] ?? '');
        $lines[] = '';
        $lines[] = 'Habilidades';

        foreach ($content['skills'] ?? [] as $group) {
            $lines[] = ($group['category'] ?? 'Geral').': '.implode(', ', $group['items'] ?? []);
        }

        $lines[] = '';
        $lines[] = 'Experiencias';

        foreach ($content['experiences'] ?? [] as $experience) {
            $lines[] = trim(($experience['role'] ?? '').' - '.($experience['company'] ?? '').' ('.($experience['period'] ?? '').')');

            foreach ($experience['bullets'] ?? [] as $bullet) {
                $lines[] = '- '.$bullet['text'];
            }
        }

        foreach ([
            'projects' => 'Projetos',
            'education' => 'Formacao',
            'certifications' => 'Certificacoes',
            'languages' => 'Idiomas',
        ] as $key => $label) {
            if (empty($content[$key])) {
                continue;
            }

            $lines[] = '';
            $lines[] = $label;

            foreach ($content[$key] as $item) {
                $lines[] = is_array($item) ? implode(' | ', array_filter($item, 'is_scalar')) : (string) $item;
            }
        }

        return trim(implode(PHP_EOL, array_filter($lines, fn (?string $line): bool => $line !== null)));
    }
}
