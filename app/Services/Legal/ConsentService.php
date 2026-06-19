<?php

namespace App\Services\Legal;

use App\Models\User;
use Illuminate\Http\Request;

class ConsentService
{
    /**
     * @return array<string, string>
     */
    public function required(bool $includeSocial = false): array
    {
        $keys = $includeSocial ? config('legal.social_required', []) : config('legal.required', []);

        return collect($keys)
            ->mapWithKeys(fn (string $versionKey, string $type): array => [$type => (string) config("legal.{$versionKey}")])
            ->all();
    }

    public function hasAccepted(User $user, bool $includeSocial = false): bool
    {
        foreach ($this->required($includeSocial) as $type => $version) {
            if (! $user->userConsents()->where(compact('type', 'version'))->exists()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function acceptRequired(User $user, Request $request, bool $includeSocial = false, array $metadata = []): void
    {
        foreach ($this->required($includeSocial) as $type => $version) {
            $user->userConsents()->firstOrCreate(
                compact('type', 'version'),
                [
                    'accepted_at' => now(),
                    'ip_address' => $request->ip(),
                    'user_agent' => substr((string) $request->userAgent(), 0, 2000),
                    'metadata' => $metadata,
                ],
            );
        }
    }
}
