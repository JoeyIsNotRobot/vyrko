<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LegalController extends Controller
{
    public function terms(): View
    {
        return view('legal.terms');
    }

    public function privacy(): View
    {
        return view('legal.privacy');
    }

    public function dataConsent(): View
    {
        return view('legal.data-consent');
    }

    public function socialData(): View
    {
        return view('legal.social-data');
    }
}
