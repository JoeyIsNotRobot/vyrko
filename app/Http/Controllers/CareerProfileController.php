<?php

namespace App\Http\Controllers;

use App\Http\Requests\Career\CareerProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CareerProfileController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load([
            'candidateProfile',
            'candidateExperiences.achievements',
            'candidateAchievements.experience',
            'candidateSkills',
            'candidateProjects',
            'candidateEducations',
            'candidateCertifications',
            'candidateLanguages',
        ]);

        return view('career.index', ['user' => $user]);
    }

    public function edit(Request $request): View
    {
        return view('career.profile-edit', [
            'profile' => $request->user()->candidateProfile,
        ]);
    }

    public function update(CareerProfileRequest $request): RedirectResponse
    {
        $request->user()->candidateProfile()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $request->validated(),
        );

        return redirect()->route('career.index')->with('status', 'Perfil atualizado.');
    }
}
