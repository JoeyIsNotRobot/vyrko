<?php

namespace App\Http\Controllers;

use App\Http\Requests\Linkedin\LinkedinProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LinkedinProfileController extends Controller
{
    public function index(Request $request): View
    {
        return view('linkedin.index', [
            'profile' => $request->user()->linkedinProfiles()->latest()->first(),
            'reports' => $request->user()->linkedinAnalysisReports()->latest()->get(),
        ]);
    }

    public function store(LinkedinProfileRequest $request): RedirectResponse
    {
        $request->user()->linkedinProfiles()->create($request->validated());

        return redirect()->route('linkedin.index')->with('status', 'Perfil manual do LinkedIn salvo.');
    }
}
