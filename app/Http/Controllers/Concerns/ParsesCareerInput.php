<?php

namespace App\Http\Controllers\Concerns;

trait ParsesCareerInput
{
    /**
     * @return array<int, string>
     */
    protected function listFrom(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map('trim', $value)));
        }

        if (! is_string($value)) {
            return [];
        }

        return collect(preg_split('/[\r\n,]+/', $value) ?: [])
            ->map(fn (string $item): string => trim($item))
            ->filter()
            ->values()
            ->all();
    }
}
