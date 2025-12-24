<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\SeoAudit;
use App\Models\SeoProject;
use App\Jobs\RunSeoAuditJob;
use App\Services\SeRankingClient;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SeoProjectController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $projects = SeoProject::with(['company', 'lastAudit'])
            ->orderBy('created_at', 'desc')
            ->get();

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

    public function create()
    {
        $user = auth()->user();

        $companies = Company::orderBy('name')->get();
        $project   = new SeoProject();

        return view('hub.seo.projects.form', [
            'user'      => $user,
            'project'   => $project,
            'companies' => $companies,
            'isEdit'    => false,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateRequest($request);
        $data['domain'] = $this->normalizeDomain($data['domain']);

        $project = SeoProject::create($data);

        return redirect()
            ->route('support.seo.projects.show', $project)
            ->with('status', 'SEO project aangemaakt. Stap 1: kies het juiste SE Ranking project.');
    }

    public function show(SeoProject $seoProject, SeRankingClient $seranking)
    {
        $user = auth()->user();
        $project = $seoProject->load(['company', 'lastAudit']);

        // Altijd de SE Ranking projecten ophalen voor de dropdown (werkt bij jou)
        $sites = $this->fetchSerankingSites();

        // Als nog niet gekoppeld: probeer 1x automatisch te matchen op domein
        if (!$project->seranking_project_id) {
            $this->autoLinkSerankingSiteIfMatch($project, $sites);
        }

        $siteId = $project->seranking_project_id ? (int) $project->seranking_project_id : null;

        $stat = null;
        $siteEngineId = (int) config('seranking.default_site_engine_id', 1);
        $keywords = [];
        $keywordRows = [];

        if ($siteId) {
            try {
                $engines = $seranking->getProjectSearchEngines($siteId);
                if (is_array($engines) && count($engines) > 0) {
                    $first = $engines[0] ?? [];
                    $siteEngineId = (int) ($first['site_engine_id'] ?? $siteEngineId);
                }

                $stat = $seranking->getProjectStat($siteId);

                $project->update([
                    'visibility_index' => isset($stat['visibility_percent']) ? (float) $stat['visibility_percent'] : $project->visibility_index,
                    'organic_traffic'  => isset($stat['visibility']) ? (int) $stat['visibility'] : $project->organic_traffic,
                    'last_synced_at'   => now(),
                ]);

                $keywords = $seranking->getProjectKeywords($siteId, $siteEngineId);

                $dateFrom = now()->subDays(30)->toDateString();
                $dateTo   = now()->toDateString();

                $positions = $seranking->getPositions(
                    $siteId,
                    $dateFrom,
                    $dateTo,
                    $siteEngineId,
                    true,
                    false
                );

                $keywordRows = $this->mapPositionsToRows($positions);
            } catch (\Throwable $e) {
                logger()->warning('SEO project show: SERanking fetch failed', [
                    'seo_project_id' => $project->id,
                    'site_id' => $siteId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return view('hub.seo.projects.show', [
            'user'        => $user,
            'project'     => $project,

            'serankingSites' => $sites,

            'serankingSiteId' => $siteId,
            'serankingStat'   => $stat,
            'serankingKeywords' => $keywords,
            'serankingKeywordRows' => $keywordRows,
            'serankingSiteEngineId' => $siteEngineId,
        ]);
    }

    public function edit(SeoProject $seoProject)
    {
        $user = auth()->user();
        $companies = Company::orderBy('name')->get();

        return view('hub.seo.projects.form', [
            'user'      => $user,
            'project'   => $seoProject,
            'companies' => $companies,
            'isEdit'    => true,
        ]);
    }

    public function update(Request $request, SeoProject $seoProject)
    {
        $data = $this->validateRequest($request, $seoProject->id);
        $data['domain'] = $this->normalizeDomain($data['domain']);

        $seoProject->update($data);

        return redirect()
            ->route('support.seo.projects.show', $seoProject)
            ->with('status', 'SEO project bijgewerkt.');
    }

    /**
     * Koppelen = gekozen site_id opslaan.
     * Geen POST /sites meer.
     */
    public function connectSeranking(Request $request, SeoProject $seoProject)
    {
        $request->validate([
            'site_id' => ['required', 'integer', 'min:1'],
        ], [
            'site_id.required' => 'Kies een SE Ranking project of vul een site ID in.',
            'site_id.integer'  => 'Ongeldig site ID.',
        ]);

        $seoProject->update([
            'seranking_project_id' => (string) ((int) $request->input('site_id')),
            'last_synced_at' => null,
        ]);

        return redirect()
            ->route('support.seo.projects.show', $seoProject)
            ->with('status', 'SE Ranking gekoppeld. Stap 2: keywords toevoegen.');
    }

    public function syncSeranking(SeoProject $seoProject, SeRankingClient $seranking)
    {
        if (!$seoProject->seranking_project_id) {
            return back()->with('status', 'SE Ranking is nog niet gekoppeld.');
        }

        $siteId = (int) $seoProject->seranking_project_id;

        try {
            $stat = $seranking->getProjectStat($siteId);

            $seoProject->update([
                'visibility_index' => isset($stat['visibility_percent']) ? (float) $stat['visibility_percent'] : $seoProject->visibility_index,
                'organic_traffic'  => isset($stat['visibility']) ? (int) $stat['visibility'] : $seoProject->organic_traffic,
                'last_synced_at'   => now(),
            ]);

            return back()->with('status', 'SE Ranking data bijgewerkt.');
        } catch (\Throwable $e) {
            logger()->warning('SERanking sync failed', [
                'seo_project_id' => $seoProject->id,
                'site_id' => $siteId,
                'error' => $e->getMessage(),
            ]);

            $msg = $this->friendlySerankingError($e, 'SE Ranking data ophalen is mislukt.');
            return back()->with('status', $msg);
        }
    }

    public function addSerankingKeywords(Request $request, SeoProject $seoProject, SeRankingClient $seranking)
    {
        $request->validate([
            'keywords_text' => ['required', 'string', 'min:2'],
        ]);

        if (!$seoProject->seranking_project_id) {
            return back()->with('status', 'Koppel eerst SE Ranking (stap 1).');
        }

        $siteId = (int) $seoProject->seranking_project_id;

        try {
            $engines = $seranking->getProjectSearchEngines($siteId);

            $siteEngineIds = collect(is_array($engines) ? $engines : [])
                ->map(fn ($e) => (int) ($e['site_engine_id'] ?? 0))
                ->filter()
                ->values()
                ->all();

            if (count($siteEngineIds) === 0) {
                return back()->with('status', 'In SE Ranking staat nog geen zoekmachine ingesteld voor dit project. Voeg eerst een zoekmachine toe in SE Ranking.');
            }

            $keywords = $this->explodeLines($request->input('keywords_text'));

            $payload = [];
            foreach ($keywords as $kw) {
                $payload[] = [
                    'keyword' => $kw,
                    'group_id' => null,
                    'target_url' => null,
                    'is_strict' => 0,
                    'comment' => null,
                    'site_engine_ids' => $siteEngineIds,
                ];
            }

            $seranking->addProjectKeywords($siteId, $payload);

            $existing = is_array($seoProject->primary_keywords) ? $seoProject->primary_keywords : [];
            $merged = collect(array_merge($existing, $keywords))
                ->map(fn ($v) => trim((string) $v))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $seoProject->update([
                'primary_keywords' => $merged,
                'last_synced_at' => null,
            ]);

            return back()->with('status', 'Keywords toegevoegd. Start nu een recheck voor de nulmeting.');
        } catch (\Throwable $e) {
            logger()->warning('SERanking add keywords failed', [
                'seo_project_id' => $seoProject->id,
                'site_id' => $siteId,
                'error' => $e->getMessage(),
            ]);

            $msg = $this->friendlySerankingError($e, 'Keywords toevoegen is mislukt.');
            return back()->with('status', $msg);
        }
    }

    public function recheckSeranking(SeoProject $seoProject, SeRankingClient $seranking)
    {
        if (!$seoProject->seranking_project_id) {
            return back()->with('status', 'SE Ranking is nog niet gekoppeld.');
        }

        $siteId = (int) $seoProject->seranking_project_id;

        try {
            $engines = $seranking->getProjectSearchEngines($siteId);
            $engines = is_array($engines) ? $engines : [];

            if (count($engines) === 0) {
                return back()->with('status', 'In SE Ranking staat nog geen zoekmachine ingesteld voor dit project. Voeg eerst een zoekmachine toe in SE Ranking.');
            }

            // Kies engine met meeste keywords (keyword_count zit in response)
            $bestEngine = collect($engines)
                ->sortByDesc(fn ($e) => (int) ($e['keyword_count'] ?? 0))
                ->first() ?? [];

            $siteEngineId = (int) ($bestEngine['site_engine_id'] ?? 0);

            if ($siteEngineId <= 0) {
                return back()->with('status', 'Kon geen geldige SE Ranking zoekmachine vinden voor dit project.');
            }

            $keywordsRaw = $seranking->getProjectKeywords($siteId, $siteEngineId);

            // Normaliseer: sommige endpoints geven een array terug, sommige een wrapper.
            $keywords = [];
            if (is_array($keywordsRaw)) {
                if (array_is_list($keywordsRaw)) {
                    $keywords = $keywordsRaw;
                } elseif (isset($keywordsRaw['keywords']) && is_array($keywordsRaw['keywords'])) {
                    $keywords = $keywordsRaw['keywords'];
                } elseif (isset($keywordsRaw['data']) && is_array($keywordsRaw['data'])) {
                    $keywords = $keywordsRaw['data'];
                }
            }

            $recheckPayload = [];
            foreach ($keywords as $k) {
                $kid = (int) ($k['id'] ?? 0);
                if ($kid <= 0) {
                    continue;
                }

                $recheckPayload[] = [
                    'site_engine_id' => $siteEngineId,
                    'keyword_id' => $kid,
                ];

                // Hou het bewust beperkt
                if (count($recheckPayload) >= 200) {
                    break;
                }
            }

            if (count($recheckPayload) === 0) {
                return back()->with('status', 'Geen keywords gevonden om te rechecken.');
            }

            logger()->info('SERanking recheck: sending payload', [
                'seo_project_id' => $seoProject->id,
                'site_id' => $siteId,
                'site_engine_id' => $siteEngineId,
                'keywords_count' => count($recheckPayload),
                'sample' => array_slice($recheckPayload, 0, 5),
            ]);

            // Docs: POST /api/sites/{site_id}/recheck/ met { "keywords": [...] } :contentReference[oaicite:3]{index=3}
            $res = $seranking->recheck($siteId, ['keywords' => $recheckPayload]);

            $total = (int) ($res['total'] ?? 0);

            return back()->with('status', $total > 0
                ? "Recheck gestart voor {$total} keywords. Ververs straks om de nulmeting te zien."
                : 'Recheck gestart. Ververs straks om de nulmeting te zien.'
            );
        } catch (\Throwable $e) {
            logger()->warning('SERanking recheck failed', [
                'seo_project_id' => $seoProject->id,
                'site_id' => $siteId,
                'error' => $e->getMessage(),
            ]);

            // In docs is de typische 400: Unknown site_engine_id :contentReference[oaicite:4]{index=4}
            $msg = $this->friendlySerankingError($e, 'Recheck starten is mislukt.');
            return back()->with('status', $msg);
        }
    }

    public function startAudit(SeoProject $seoProject)
    {
        $audit = SeoAudit::create([
            'seo_project_id' => $seoProject->id,
            'source' => 'seranking',
            'status' => 'pending',
            'meta' => [
                'settings' => [],
            ],
        ]);

        RunSeoAuditJob::dispatch($audit);

        return redirect()
            ->route('support.seo.projects.show', $seoProject)
            ->with('status', 'Website audit gestart.');
    }

    /**
     * Haalt SE Ranking projecten op via GET /sites.
     */
    protected function fetchSerankingSites(): array
    {
        $key = (string) config('seranking.project_api_key', '');
        $base = rtrim((string) config('seranking.project_base_url', 'https://api4.seranking.com'), '/');

        if (trim($key) === '') {
            return [];
        }

        try {
            $res = Http::withHeaders([
                'Authorization' => 'Token ' . $key,
                'Accept'        => 'application/json',
            ])->get($base . '/sites');

            if ($res->failed()) {
                logger()->warning('SERanking GET /sites failed', [
                    'status' => $res->status(),
                    'body' => $res->body(),
                    'json' => $res->json(),
                ]);
                return [];
            }

            $json = $res->json();
            return is_array($json) ? $json : [];
        } catch (\Throwable $e) {
            logger()->warning('SERanking GET /sites exception', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Auto-link: match domein op sites[].name of sites[].title (case-insensitive).
     */
    protected function autoLinkSerankingSiteIfMatch(SeoProject $project, array $sites): bool
    {
        if ($project->seranking_project_id) {
            return true;
        }

        $needle = strtolower($this->normalizeDomain($project->domain));
        if ($needle === '') {
            return false;
        }

        foreach ($sites as $s) {
            $id = (int) ($s['id'] ?? 0);
            if ($id <= 0) continue;

            $name = strtolower((string) ($s['name'] ?? ''));
            $title = strtolower((string) ($s['title'] ?? ''));

            if (($name !== '' && str_contains($name, $needle)) || ($title !== '' && str_contains($title, $needle))) {
                $project->update([
                    'seranking_project_id' => (string) $id,
                    'last_synced_at' => null,
                ]);
                return true;
            }
        }

        return false;
    }

    protected function validateRequest(Request $request, ?int $projectId = null): array
    {
        return $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'name'       => ['nullable', 'string', 'max:255'],
            'domain'     => ['required', 'string', 'max:255'],
        ], [
            'company_id.required' => 'Kies een klant of bedrijf.',
            'company_id.exists'   => 'Het geselecteerde bedrijf bestaat niet.',
            'domain.required'     => 'Vul een domein in.',
        ]);
    }

    protected function normalizeDomain(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('#^https?://#i', '', $value);
        $value = rtrim($value, '/');
        return $value;
    }

    protected function explodeLines(?string $value): array
    {
        if (!$value) return [];

        return collect(preg_split('/\r\n|\r|\n/', $value))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    protected function mapPositionsToRows(array $positionsResponse): array
    {
        $rows = [];

        foreach ($positionsResponse as $engineBlock) {
            $keywords = $engineBlock['keywords'] ?? [];
            if (!is_array($keywords)) continue;

            foreach ($keywords as $k) {
                $name = (string) ($k['name'] ?? $k['keyword'] ?? '');
                $kid  = (int) ($k['id'] ?? 0);

                $positions = $k['positions'] ?? [];
                $latest = null;
                $prev = null;

                if (is_array($positions) && count($positions) > 0) {
                    $latest = $positions[count($positions) - 1] ?? null;
                    $prev   = $positions[count($positions) - 2] ?? null;
                }

                $latestPos = (int) (($latest['pos'] ?? 0) ?: 0);
                $prevPos   = (int) (($prev['pos'] ?? 0) ?: 0);

                $change = 0;
                if ($prevPos > 0 && $latestPos > 0) {
                    $change = $prevPos - $latestPos;
                }

                $rows[] = [
                    'id' => $kid,
                    'keyword' => $name,
                    'pos' => $latestPos,
                    'change' => $change,
                    'volume' => (int) ($k['volume'] ?? 0),
                    'competition' => (float) ($k['competition'] ?? 0),
                    'cpc' => (float) ($k['suggested_bid'] ?? 0),
                    'landing_page' => $this->extractLatestLandingPage($k),
                ];
            }
        }

        usort($rows, function ($a, $b) {
            $ap = (int) ($a['pos'] ?? 0);
            $bp = (int) ($b['pos'] ?? 0);

            if ($ap === 0 && $bp === 0) return 0;
            if ($ap === 0) return 1;
            if ($bp === 0) return -1;

            return $ap <=> $bp;
        });

        return $rows;
    }

    protected function extractLatestLandingPage(array $keywordBlock): ?string
    {
        $positions = $keywordBlock['positions'] ?? null;
        if (!is_array($positions) || count($positions) === 0) return null;

        $latest = $positions[count($positions) - 1] ?? null;
        if (!is_array($latest)) return null;

        $landingPages = $latest['landing_pages'] ?? null;
        if (!is_array($landingPages) || count($landingPages) === 0) return null;

        $lp = $landingPages[0]['url'] ?? null;
        return $lp ? (string) $lp : null;
    }

    protected function friendlySerankingError(\Throwable $e, string $fallback): string
    {
        if ($e instanceof RequestException && $e->response) {
            $status = $e->response->status();
            $body = trim((string) $e->response->body());

            if ($body !== '') {
                return "{$fallback} (SE Ranking {$status}) {$body}";
            }

            return "{$fallback} (SE Ranking {$status})";
        }

        return $fallback . ' ' . $e->getMessage();
    }
}
