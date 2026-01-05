<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function index(Request $request)
    {
        return view('hub.onboarding.index');
    }
}
