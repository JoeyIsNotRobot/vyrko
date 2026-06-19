<?php

namespace App\Services\Ai;

use App\Models\ResumeVersion;

class AtsChecklistAnalyzer
{
    public function __construct(private readonly AiClient $aiClient) {}

    /**
     * @return array<string, mixed>
     */
    public function analyze(ResumeVersion $resumeVersion): array
    {
        $content = $resumeVersion->content;
        $plainText = $resumeVersion->plain_text ?? '';

        $result = $this->aiClient->completeJson('ats_checklist', [
            'target_language' => $resumeVersion->language,
            'resume_type' => $resumeVersion->resume_type,
            'content' => $content,
            'plain_text' => $plainText,
        ]);

        $checklist = $this->sanitizeChecklist($result, $content);

        $resumeVersion->forceFill(['ats_checklist' => $checklist])->save();

        return $checklist;
    }

    /**
     * @param  array<string, mixed>  $result
     * @param  array<string, mixed>  $content
     * @return array<string, mixed>
     */
    private function sanitizeChecklist(array $result, array $content): array
    {
        $items = collect($result['items'] ?? [])
            ->filter(fn (mixed $item): bool => is_array($item))
            ->map(fn (array $item): array => $this->item(
                (string) ($item['key'] ?? 'check'),
                (string) ($item['status'] ?? 'warning'),
                (string) ($item['message'] ?? '')
            ))
            ->filter(fn (array $item): bool => $item['message'] !== '')
            ->values()
            ->all();

        if ($items === []) {
            $items = [
                $this->item('has_standard_sections', isset($content['header'], $content['summary'], $content['skills'], $content['experiences']) ? 'passed' : 'warning', 'Seções principais verificadas.'),
            ];
        }

        return [
            'score' => max(0, min(100, (int) ($result['score'] ?? 0))),
            'items' => $items,
            'file_format_recommendation' => is_scalar($result['file_format_recommendation'] ?? null)
                ? trim((string) $result['file_format_recommendation'])
                : '',
            'warnings' => $this->stringList($result['warnings'] ?? []),
            'recommendations' => $this->stringList($result['recommendations'] ?? []),
        ];
    }

    /**
     * @return array{key: string, status: string, message: string}
     */
    private function item(string $key, string $status, string $message): array
    {
        $status = in_array($status, ['passed', 'warning', 'failed'], true) ? $status : 'warning';

        return compact('key', 'status', 'message');
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
}
