<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, ['pt_BR', 'en'], true), 404);

        session(['locale' => $locale]);
        app()->setLocale($locale);

        return back();
    }
}
