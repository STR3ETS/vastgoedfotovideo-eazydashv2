<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MailingController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        return view('hub.marketing.mailing.index', compact('user'));
    }
    public function nieuwsbrievenIndex()
    {
        $user = auth()->user();
        return view('hub.marketing.mailing.nieuwsbrieven.index', compact('user'));
    }
    public function templatesIndex()
    {
        $user = auth()->user();
        return view('hub.marketing.mailing.templates.index', compact('user'));
    }
    public function nieuwsbriefTemplates()
    {
        $user = auth()->user();
        return view('hub.marketing.mailing.templates.nieuwsbrief-templates', compact('user'));
    }
    public function actieAanbodTemplates()
    {
        $user = auth()->user();
        return view('hub.marketing.mailing.templates.actie-aanbod-templates', compact('user'));
    }
    public function OnboardingOpvolgTemplates()
    {
        $user = auth()->user();
        return view('hub.marketing.mailing.templates.onboarding-opvolg-templates', compact('user'));
    }
    public function campagnesIndex()
    {
        $user = auth()->user();
        return view('hub.marketing.mailing.campagnes.index', compact('user'));
    }
}
