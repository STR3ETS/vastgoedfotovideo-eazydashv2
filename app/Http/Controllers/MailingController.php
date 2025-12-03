<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MailTemplate;

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

    public function nieuwsbriefTemplates(Request $request)
    {
        $user = auth()->user();

        $templates = MailTemplate::where('category', 'nieuwsbrief')
            ->orderByDesc('created_at')
            ->get();

        $activeTemplate = null;

        // AJAX: alleen detail-fragment + URL teruggeven
        if ($request->ajax()) {
            $templateId = $request->get('template');

            $activeTemplate = $templates->firstWhere('id', $templateId);

            if (! $activeTemplate) {
                return response()->json(['message' => 'Template niet gevonden'], 404);
            }

            $url = route('support.marketing.mailing.nieuwsbriefTemplates', [
                'template' => $activeTemplate->id,
            ]);

            return response()->json([
                'id'          => $activeTemplate->id,
                'name'        => $activeTemplate->name,
                'url'         => $url,
                'detail_html' => view('hub.marketing.mailing.templates.partials.detail', [
                    'activeTemplate' => $activeTemplate,
                ])->render(),
            ]);
        }

        // Normale page load
        if ($request->filled('template')) {
            $activeTemplate = $templates->firstWhere('id', $request->get('template'));
        }

        return view('hub.marketing.mailing.templates.nieuwsbrief-templates', compact(
            'user',
            'templates',
            'activeTemplate'
        ));
    }
    public function quickCreateNieuwsbriefTemplate(Request $request)
    {
        $count = MailTemplate::where('category', 'nieuwsbrief')->count() + 1;

        $template = MailTemplate::create([
            'name'     => 'Nieuwsbrief template ' . $count,
            'category' => 'nieuwsbrief',
            'html'     => '',
        ]);

        $url = route('support.marketing.mailing.nieuwsbriefTemplates', [
            'template' => $template->id,
        ]);

        // AJAX: JSON terug
        if ($request->ajax()) {
            return response()->json([
                'id'          => $template->id,
                'name'        => $template->name,
                'url'         => $url,
                'detail_html' => view('hub.marketing.mailing.templates.partials.detail', [
                    'activeTemplate' => $template,
                ])->render(),
            ]);
        }

        // Fallback: normale redirect
        return redirect($url);
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

    public function updateNieuwsbriefTemplate(Request $request, MailTemplate $template)
    {
        // Voorheen: $html = $request->input('html', '');
        // Probleem: als 'html' ontbreekt, is dit toch null in jouw situatie

        $html = $request->input('html') ?? '';

        $template->update([
            'html' => $html,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'status'  => 'ok',
                'message' => 'Template opgeslagen.',
            ]);
        }

        return back()->with('status', 'Template opgeslagen.');
    }
}
