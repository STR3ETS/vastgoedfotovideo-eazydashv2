<?php

namespace App\Http\Controllers;

use App\Models\SeoProject;
use App\Models\Company;
use Illuminate\Http\Request;

class SeoProjectController extends Controller
{
    /**
     * Overzicht van alle SEO projecten in de Service Hub.
     */
    public function index()
    {
        $user = auth()->user();

        // Laad company en laatste audit mee voor het overzicht
        $projects = SeoProject::with(['company', 'lastAudit'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Eenvoudige stats voor de kaarten bovenaan
        $totalProjects      = $projects->count();
        $needingAttention   = $projects->where('health_overall', '<', 70)->count();
        $withoutSync        = $projects->whereNull('last_synced_at')->count();

        return view('hub.seo.projects.index', [
            'user'             => $user,
            'projects'         => $projects,
            'totalProjects'    => $totalProjects,
            'needingAttention' => $needingAttention,
            'withoutSync'      => $withoutSync,
        ]);
    }

    /**
     * Formulier: nieuw SEO project.
     */
    public function create()
    {
        $user = auth()->user();

        // Alle bedrijven waar je een SEO traject aan kunt koppelen
        $companies = Company::orderBy('name')->get();

        // Leeg project object voor het formulier (handig voor old values)
        $project = new SeoProject();

        // Lege tekstvelden voor de multiline inputs
        $regionsText        = '';
        $businessGoalsText  = '';
        $primaryKeywordsText = '';
        $mainPagesText      = '';

        return view('hub.seo.projects.form', [
            'user'               => $user,
            'project'            => $project,
            'companies'          => $companies,
            'regionsText'        => $regionsText,
            'businessGoalsText'  => $businessGoalsText,
            'primaryKeywordsText'=> $primaryKeywordsText,
            'mainPagesText'      => $mainPagesText,
            'isEdit'             => false,
        ]);
    }

    /**
     * Opslaan van een nieuw SEO project.
     */
    public function store(Request $request)
    {
        $data = $this->validateRequest($request);

        // Multiline velden omzetten naar arrays
        $data['regions']          = $this->explodeLines($request->input('regions_text'));
        $data['business_goals']   = $this->explodeLines($request->input('business_goals_text'));
        $data['primary_keywords'] = $this->explodeLines($request->input('primary_keywords_text'));
        $data['main_pages']       = $this->parseMainPagesLines($request->input('main_pages_text'));

        $project = SeoProject::create($data);

        return redirect()
            ->route('support.seo.projects.show', $project)
            ->with('status', 'SEO project aangemaakt.');
    }

    /**
     * Detail dashboard van één SEO project.
     */
    public function show(SeoProject $seoProject)
    {
        $user = auth()->user();

        return view('hub.seo.projects.show', [
            'user'    => $user,
            'project' => $seoProject->load(['company', 'lastAudit']),
        ]);
    }

    /**
     * Formulier: bestaand SEO project bewerken.
     */
    public function edit(SeoProject $seoProject)
    {
        $user = auth()->user();

        $companies = Company::orderBy('name')->get();

        // Bestaande arrays omzetten naar multiline tekst
        $regionsText        = is_array($seoProject->regions)
            ? implode("\n", $seoProject->regions)
            : '';

        $businessGoalsText  = is_array($seoProject->business_goals)
            ? implode("\n", $seoProject->business_goals)
            : '';

        $primaryKeywordsText = is_array($seoProject->primary_keywords)
            ? implode("\n", $seoProject->primary_keywords)
            : '';

        // main_pages is bv. [["url" => "/woningontruiming-arnhem","label" => "Arnhem"], ...]
        $mainPagesText = '';
        if (is_array($seoProject->main_pages)) {
            $lines = [];
            foreach ($seoProject->main_pages as $page) {
                $url   = $page['url']   ?? '';
                $label = $page['label'] ?? '';
                if ($url || $label) {
                    // Formaat: /pad | Label
                    $lines[] = trim($url) . ' | ' . trim($label);
                }
            }
            $mainPagesText = implode("\n", $lines);
        }

        return view('hub.seo.projects.form', [
            'user'               => $user,
            'project'            => $seoProject,
            'companies'          => $companies,
            'regionsText'        => $regionsText,
            'businessGoalsText'  => $businessGoalsText,
            'primaryKeywordsText'=> $primaryKeywordsText,
            'mainPagesText'      => $mainPagesText,
            'isEdit'             => true,
        ]);
    }

    /**
     * Opslaan van wijzigingen in een SEO project.
     */
    public function update(Request $request, SeoProject $seoProject)
    {
        $data = $this->validateRequest($request, $seoProject->id);

        $data['regions']          = $this->explodeLines($request->input('regions_text'));
        $data['business_goals']   = $this->explodeLines($request->input('business_goals_text'));
        $data['primary_keywords'] = $this->explodeLines($request->input('primary_keywords_text'));
        $data['main_pages']       = $this->parseMainPagesLines($request->input('main_pages_text'));

        $seoProject->update($data);

        return redirect()
            ->route('support.seo.projects.show', $seoProject)
            ->with('status', 'SEO project bijgewerkt.');
    }

    /**
     * Validatie voor create en update.
     */
    protected function validateRequest(Request $request, ?int $projectId = null): array
    {
        return $request->validate([
            'company_id'          => ['required', 'exists:companies,id'],
            'name'                => ['nullable', 'string', 'max:255'],
            'domain'              => ['required', 'string', 'max:255'],
            'seranking_project_id'=> ['nullable', 'string', 'max:191'],
        ], [
            'company_id.required' => 'Kies een klant of bedrijf.',
            'company_id.exists'   => 'Het geselecteerde bedrijf bestaat niet.',
            'domain.required'     => 'Vul een domein in.',
        ]);
    }

    /**
     * Helper: multiline tekst naar array van strings.
     */
    protected function explodeLines(?string $value): array
    {
        if (!$value) {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', $value))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Helper: main pages tekst naar array van ['url' => ..., 'label' => ...].
     * Verwacht formaat: "/pad | Label" per regel.
     */
    protected function parseMainPagesLines(?string $value): array
    {
        if (!$value) {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $value);
        $pages = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            // Splits op pipe
            $parts = explode('|', $line, 2);
            $url   = trim($parts[0] ?? '');
            $label = trim($parts[1] ?? '');

            if ($url === '' && $label === '') {
                continue;
            }

            $pages[] = [
                'url'   => $url,
                'label' => $label,
            ];
        }

        return $pages;
    }
}
