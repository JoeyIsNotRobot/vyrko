<?php

namespace App\Support;

use Illuminate\Support\Str;

class UiText
{
    public static function label(string $group, ?string $key): string
    {
        if ($key === null || $key === '') {
            return '—';
        }

        $translation = __("messages.{$group}.{$key}");

        if ($translation !== "messages.{$group}.{$key}") {
            return $translation;
        }

        return Str::headline($key);
    }
}
