<?php

namespace App\Http\Controllers;

use App\Services\Import\ResumeImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class OnboardingImportController extends Controller
{
    public function index(Request $request): View
    {
        return view('onboarding.import', [
            'profile' => $request->user()->linkedinProfiles()->latest()->first(),
            'hasInventory' => $request->user()->candidateProfile()->exists()
                || $request->user()->candidateExperiences()->exists()
                || $request->user()->candidateSkills()->exists(),
        ]);
    }

    public function storeText(Request $request, ResumeImportService $resumeImportService): RedirectResponse
    {
        $data = $request->validate([
            'headline' => ['nullable', 'string', 'max:255'],
            'about' => ['nullable', 'string', 'max:5000'],
            'experiences_text' => ['nullable', 'string', 'max:12000'],
            'skills_text' => ['nullable', 'string', 'max:6000'],
            'raw_text' => ['nullable', 'string', 'max:20000'],
        ]);

        if (blank(implode('', $data))) {
            return back()->withErrors([
                'profile' => app()->getLocale() === 'en'
                    ? 'Paste at least one profile section.'
                    : 'Cole ao menos uma seção do perfil.',
            ]);
        }

        $request->user()->linkedinProfiles()->create($data);

        $combinedText = implode("\n\n", array_filter($data));

        try {
            $resumeImportService->importText($request->user(), $combinedText);
        } catch (RuntimeException) {
            // Keep the pasted source even when the heuristic inventory import cannot extract enough structure.
        }

        return redirect()->route('career.index')->with('status', app()->getLocale() === 'en'
            ? 'Profile text saved. Review the inventory and complete missing evidence.'
            : 'Texto do perfil salvo. Revise o inventário e complete evidências faltantes.');
    }
}
