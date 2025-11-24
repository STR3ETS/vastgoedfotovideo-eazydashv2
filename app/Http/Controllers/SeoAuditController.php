<?php

namespace App\Http\Controllers;

use App\Jobs\RunSeoAuditJob;
use App\Models\Company;
use App\Models\SeoAudit;
use App\Services\SeoAuditInsightsService;
use App\Services\SeoAuditTaskPlanService;
use Illuminate\Http\Request;

class SeoAuditController extends Controller
{
    /**
     * Kleine helper om te zorgen dat alleen admins en medewerkers
     * bij de SEO audit kunnen.
     */
    protected function ensureAuthorized($user): void
    {
        if (! $user || ! in_array($user->rol, ['admin', 'medewerker'], true)) {
            abort(403);
        }
    }

    /**
     * Overzicht van SEO audits voor alle klanten.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $this->ensureAuthorized($user);

        $companyId  = $request->query('company_id');
        $status     = $request->query('status');
        $selectedId = $request->query('audit_id');

        $query = SeoAudit::with(['company', 'user'])
            ->latest('created_at');

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        // paginate + filters in de URL houden
        $audits = $query->paginate(20)->appends($request->query());

        // Geselecteerde audit bepalen (voor bijv. default selectie in de lijst)
        $selectedAudit = null;
        if ($selectedId) {
            // Proberen te vinden in de huidige pagina
            $selectedAudit = $audits->firstWhere('id', (int) $selectedId);

            // Als die niet in deze pagina zit, fallback naar eerste van de pagina
            if (! $selectedAudit && $audits->count()) {
                $selectedAudit = $audits->first();
            }
        } else {
            // Geen specifieke selectie: neem de eerste audit uit de lijst (indien aanwezig)
            $selectedAudit = $audits->first();
        }

        $companies = Company::orderBy('name')->get();

        $filters = [
            'company_id' => $companyId,
            'status'     => $status,
        ];

        return view('hub.seo.index', [
            'user'          => $user,
            'audits'        => $audits,
            'companies'     => $companies,
            'filters'       => $filters,
            'selectedAudit' => $selectedAudit,
        ]);
    }

    /**
     * Nieuwe audit starten voor een klant.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $this->ensureAuthorized($user);

        $data = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'domain'     => ['nullable', 'string', 'max:255'],
            'type'       => ['required', 'in:full,technical,keywords,backlinks'],
            'locale'     => ['nullable', 'string', 'max:10'], // bv. nl-NL
            'country'    => ['nullable', 'string', 'max:2'],  // bv. NL
        ]);

        $company = Company::findOrFail($data['company_id']);

        $domain = $data['domain'] ?: ($company->website ?? null);
        if (! $domain) {
            return back()
                ->withErrors(['domain' => 'Geen domein bekend voor deze klant. Vul een domein in.'])
                ->withInput();
        }

        $meta = [
            'locale'  => $data['locale'] ?? 'nl-NL',
            'country' => strtoupper($data['country'] ?? 'NL'),
            'settings'=> config('seranking.default_audit_settings', []),
        ];

        $audit = SeoAudit::create([
            'company_id' => $company->id,
            'user_id'    => $user->id,
            'domain'     => $domain,
            'type'       => $data['type'],
            'status'     => 'pending',
            'meta'       => $meta,
        ]);

        RunSeoAuditJob::dispatch($audit);

        return redirect()
            ->route('support.seo-audit.index', [
                'company_id' => $company->id,
                'audit_id'   => $audit->id,
            ])
            ->with('status', 'SEO audit is gestart voor ' . $domain . '. Dit kan even duren.');
    }

    /**
     * Detailpagina van één audit met slimme inzichten en acties.
     */
    public function show(
        Request $request,
        SeoAudit $seoAudit,
        SeoAuditInsightsService $insightsService
    ) {
        $user = $request->user();
        $this->ensureAuthorized($user);

        $insights   = $insightsService->buildInsights($seoAudit);
        $summary    = $insights['summary'] ?? [];
        $quickWins  = $insights['quick_wins'] ?? [];
        $actions    = $insights['recommended_actions'] ?? [];
        $rawIssues  = $insights['raw_issues'] ?? [];
        $rawReport  = $insights['raw_report'] ?? [];
        $domainProps = data_get($rawReport, 'domain_props', []);

        $aiPlan = $seoAudit->ai_plan ?? null;

        return view('hub.seo.show', [
            'user'        => $user,
            'audit'       => $seoAudit,
            'summary'     => $summary,
            'domainProps' => $domainProps,
            'quickWins'   => $quickWins,
            'actions'     => $actions,
            'rawIssues'   => $rawIssues,
            'rawReport'   => $rawReport,
            'aiPlan'      => $aiPlan,
        ]);
    }

    /**
     * AI takenplan genereren of verversen op basis van een audit.
     */
    public function generatePlan(
        Request $request,
        SeoAudit $seoAudit,
        SeoAuditInsightsService $insightsService,
        SeoAuditTaskPlanService $taskPlanService
    ) {
        $user = $request->user();
        $this->ensureAuthorized($user);

        // Insights opnieuw ophalen zodat we actuele quick wins en acties hebben
        $insights  = $insightsService->buildInsights($seoAudit);
        $summary   = $insights['summary'] ?? [];
        $quickWins = $insights['quick_wins'] ?? [];
        $actions   = $insights['recommended_actions'] ?? [];

        try {
            $plan = $taskPlanService->generatePlan($seoAudit, $summary, $quickWins, $actions);

            $seoAudit->ai_plan = [
                'generated_at' => now()->toIso8601String(),
                'model'        => config('openai.model', 'gpt-4o-mini'),
                'plan'         => $plan,
            ];
            $seoAudit->save();

            return redirect()
                ->route('support.seo-audit.show', $seoAudit)
                ->with('status', 'AI takenplan is succesvol gegenereerd.');
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('support.seo-audit.show', $seoAudit)
                ->withErrors([
                    'ai' => 'AI takenplan genereren is niet gelukt: ' . $e->getMessage(),
                ]);
        }
    }

    /**
     * Simpele status endpoint (optioneel voor HTMX of polling).
     */
    public function status(Request $request, SeoAudit $seoAudit)
    {
        $this->ensureAuthorized($request->user());

        return response()->json([
            'id'            => $seoAudit->id,
            'status'        => $seoAudit->status,
            'overall_score' => $seoAudit->overall_score,
            'started_at'    => optional($seoAudit->started_at)->toDateTimeString(),
            'finished_at'   => optional($seoAudit->finished_at)->toDateTimeString(),
        ]);
    }

    /**
     * JSON rapport downloaden, handig als je het wilt bewaren of delen.
     */
    public function downloadJson(Request $request, SeoAudit $seoAudit)
    {
        $this->ensureAuthorized($request->user());

        $meta   = $seoAudit->meta ?? [];
        $report = data_get($meta, 'seranking.report', data_get($meta, 'report', $meta));

        $fileName = 'seo-audit-' . preg_replace('/[^a-z0-9\-]+/i', '-', $seoAudit->domain) . '-' . $seoAudit->id . '.json';

        return response()->streamDownload(function () use ($report) {
            echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }, $fileName, [
            'Content-Type' => 'application/json',
        ]);
    }
}
