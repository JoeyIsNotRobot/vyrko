<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function index(Request $request): View
    {
        return view('onboarding.index', [
            'profile' => $request->user()->candidateProfile,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'source' => ['required', 'in:resume,linkedin,paste,manual'],
            'target_role' => ['nullable', 'string', 'max:160'],
            'target_seniority' => ['nullable', 'string', 'max:80'],
            'target_country' => ['nullable', 'string', 'max:120'],
            'preferred_language' => ['required', 'in:pt_BR,en'],
            'professional_area' => ['nullable', 'string', 'max:120'],
        ]);

        $user = $request->user();
        $nameParts = preg_split('/\s+/', trim($user->name)) ?: [$user->name];

        $user->candidateProfile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'first_name' => $user->candidateProfile?->first_name ?: ($nameParts[0] ?? $user->name),
                'last_name' => $user->candidateProfile?->last_name ?: trim(implode(' ', array_slice($nameParts, 1))),
                'email' => $user->candidateProfile?->email ?: $user->email,
                'target_role' => $data['target_role'],
                'target_seniority' => $data['target_seniority'],
                'location_country' => $data['target_country'],
                'preferred_language' => $data['preferred_language'],
                'professional_area' => $data['professional_area'],
                'onboarding_source' => $data['source'],
            ],
        );

        $user->forceFill(['onboarding_completed_at' => now()])->save();

        return match ($data['source']) {
            'resume' => redirect()->route('onboarding.import')->withFragment('arquivo'),
            'linkedin' => redirect()->route('auth.social.redirect', 'linkedin'),
            'paste' => redirect()->route('onboarding.import')->withFragment('colar'),
            default => redirect()->route('career.index')->with('status', app()->getLocale() === 'en'
                ? 'Onboarding saved. Complete your inventory when ready.'
                : 'Onboarding salvo. Complete seu inventário quando quiser.'),
        };
    }
}
